<#
.SYNOPSIS
    Обслуживание прод-контейнера vkr-blog на Windows-сервере.

.DESCRIPTION
    Без параметров показывает меню: полный релиз, статус и логи, откат на предыдущий
    образ, очистка старых образов. С параметрами сразу выполняет релиз — так его можно
    звать из планировщика.

    Порядок релиза — как в docs/home-hosting.md §8: бэкап -> git pull --ff-only ->
    docker compose build app -> up -d -> проверка /up. На первой ошибке останавливается;
    контейнер до перезапуска не трогается, сайт остаётся на прежней версии.

    Перед сборкой текущий образ помечается как vkr-blog/app:previous — с него можно
    откатиться пунктом меню «Откат» без пересборки.

.PARAMETER SkipBackup
    Не снимать дамп БД и архив файлов (только если свежая копия уже есть).

.PARAMETER SkipPull
    Не делать git pull (пересобрать тот код, что лежит в папке).

.PARAMETER BackupDir
    Куда складывать бэкап. По умолчанию backups в папке проекта,
    scripts/backup.sh удаляет копии старше 14 дней.

.PARAMETER HealthUrl
    Адрес проверки после перезапуска. По умолчанию http://127.0.0.1:8080/up.

.EXAMPLE
    .\scripts\deploy.cmd

.EXAMPLE
    .\scripts\deploy.cmd -SkipBackup -BackupDir /mnt/d/vkr-backups
#>
param(
    [switch]$SkipBackup,
    [switch]$SkipPull,
    [string]$BackupDir = "backups",
    [string]$HealthUrl = "http://127.0.0.1:8080/up"
)

$ErrorActionPreference = "Stop"

# Прод собирается только из этой ветки (см. AGENTS.md, раздел про деплой)
$ProdBranch = "redesign/underground"
# Имена образов из docker-compose.yml: current — то, что собирает build app
$Image = "vkr-blog/app:latest"
$PrevImage = "vkr-blog/app:previous"

$root = Split-Path -Parent $PSScriptRoot
$script:LogFile = $null

function Write-Log([string]$Text) {
    if (-not $script:LogFile) {
        return
    }
    $line = "{0} {1}" -f (Get-Date -Format "yyyy-MM-dd HH:mm:ss"), $Text
    Add-Content -Path $script:LogFile -Value $line -Encoding UTF8
}

function Start-DeployLog([string]$Kind) {
    $dir = Join-Path $root "backups"
    New-Item -ItemType Directory -Force -Path $dir | Out-Null
    $stamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $script:LogFile = Join-Path $dir "deploy-$stamp.log"
    Write-Log "=== ${Kind}: $root"
}

function Write-Step([string]$Text) {
    Write-Host ""
    Write-Host "==> $Text" -ForegroundColor Cyan
    Write-Log $Text
}

function Stop-Deploy([string]$Text) {
    Write-Host ""
    Write-Host "ОШИБКА: $Text" -ForegroundColor Red
    Write-Log "ОШИБКА: $Text"
    if ($script:LogFile) {
        Write-Host "Лог: $script:LogFile" -ForegroundColor DarkGray
    }
    exit 1
}

function Invoke-Checked([string]$Exe, [string[]]$Arguments) {
    Write-Log ("> $Exe $($Arguments -join ' ')")
    & $Exe @Arguments
    if ($LASTEXITCODE -ne 0) {
        Stop-Deploy "команда завершилась с кодом $LASTEXITCODE`: $Exe $($Arguments -join ' ')"
    }
}

function Assert-Docker {
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        Stop-Deploy "docker не найден в PATH. Запустите Docker Desktop и повторите."
    }
    Invoke-Checked "docker" @("compose", "version")
}

function Assert-ProdBranch {
    if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
        Stop-Deploy "git не найден в PATH."
    }

    $currentBranch = & git rev-parse --abbrev-ref HEAD
    if ($LASTEXITCODE -ne 0) {
        Stop-Deploy "Папка не похожа на git-репозиторий: $root"
    }
    $currentBranch = "$currentBranch".Trim()

    if ($currentBranch -ne $ProdBranch) {
        Stop-Deploy "Сейчас ветка '$currentBranch', а прод собирается из '$ProdBranch'. Переключитесь: git checkout $ProdBranch"
    }

    $changes = & git status --porcelain
    if ($changes) {
        Write-Host $changes
        Stop-Deploy "В рабочей копии есть незакоммиченные изменения — сначала уберите их (git stash или git checkout -- .)."
    }
}

function Invoke-Backup {
    Write-Step "Бэкап БД и загруженных файлов"
    if (-not (Get-Command wsl -ErrorAction SilentlyContinue)) {
        Stop-Deploy "wsl не найден, а бэкап идёт через scripts/backup.sh под WSL. Если копия уже есть — запустите с -SkipBackup."
    }

    $wslRoot = & wsl wslpath -a $root
    if ($LASTEXITCODE -ne 0 -or -not $wslRoot) {
        Stop-Deploy "Не удалось получить путь WSL для '$root'."
    }
    $wslRoot = "$wslRoot".Trim()

    Invoke-Checked "wsl" @("-e", "sh", "-c", "cd '$wslRoot' && BACKUP_DIR='$BackupDir' sh scripts/backup.sh")
}

function Wait-Health {
    Write-Step "Жду ответа приложения: $HealthUrl"
    $deadline = (Get-Date).AddSeconds(240)
    $healthy = $false

    while ((Get-Date) -lt $deadline) {
        try {
            $response = Invoke-WebRequest -Uri $HealthUrl -UseBasicParsing -TimeoutSec 5
            if ($response.StatusCode -eq 200) {
                $healthy = $true
                break
            }
        }
        catch {
            Start-Sleep -Seconds 4
        }
    }

    if (-not $healthy) {
        & docker compose ps
        Stop-Deploy "Приложение не ответило за 4 минуты. Смотрите логи: docker compose logs --tail=100 app"
    }

    Write-Log "health: $HealthUrl — 200"
}

function Invoke-Cleanup {
    param([switch]$AfterRelease)

    if (-not $AfterRelease) {
        Start-DeployLog "cleanup"
        Assert-Docker
    }

    Write-Step "Убираю висячие образы и кеш сборки старше недели"

    & docker image prune -f
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  образы почистить не удалось — не критично" -ForegroundColor Yellow
        Write-Log "image prune: ошибка"
    }

    & docker builder prune -f --filter "until=168h"
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  кеш сборки почистить не удалось — не критично" -ForegroundColor Yellow
        Write-Log "builder prune: ошибка"
    }

    Write-Log "очистка завершена"
    if (-not $AfterRelease) {
        Write-Host ""
        Write-Host "Готово." -ForegroundColor Green
    }
}

function Show-Status {
    Assert-Docker

    Write-Host ""
    Write-Host "Контейнеры" -ForegroundColor Cyan
    & docker compose ps

    Write-Host ""
    Write-Host "Что выкачено" -ForegroundColor Cyan
    if (Get-Command git -ErrorAction SilentlyContinue) {
        (& git log -1 --format="%h %s — %cd" --date=format:"%d.%m.%Y %H:%M").Trim()
    }

    & docker image inspect $Image --format "Образ app: создан {{.Created}}" 2>$null
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Образ $Image не найден — контейнер ещё не собирали."
    }

    $drive = (Get-Item $root).PSDrive
    $freeGb = [math]::Round($drive.Free / 1GB, 1)
    Write-Host "Свободно на $($drive.Name): $freeGb ГБ"

    Write-Host ""
    Write-Host "Последние 50 строк логов app" -ForegroundColor Cyan
    & docker compose logs --tail=50 app
}

function Invoke-Rollback {
    Start-DeployLog "rollback"
    Assert-Docker

    Write-Host ""
    Write-Host "vkr-blog: откат на предыдущий образ" -ForegroundColor Green

    & docker image inspect $PrevImage *> $null
    if ($LASTEXITCODE -ne 0) {
        Stop-Deploy "Предыдущего образа нет ($PrevImage). Откат доступен после первого релиза этим скриптом."
    }

    $created = (& docker image inspect $PrevImage --format "{{.Created}}").Trim()
    Write-Host "Предыдущий образ: $created"
    Write-Host "Код вернётся назад, миграции БД — нет: если в релизе была миграция, база останется новой." -ForegroundColor Yellow

    $answer = (Read-Host "Вернуть его? [y/N]").Trim().ToLower()
    if ($answer -ne "y" -and $answer -ne "д" -and $answer -ne "да") {
        Write-Host "Откат отменён."
        Write-Log "откат отменён"
        return
    }

    Invoke-Checked "docker" @("tag", $PrevImage, $Image)

    Write-Step "Перезапускаю контейнер на прежнем образе"
    Invoke-Checked "docker" @("compose", "up", "-d")

    Wait-Health

    Write-Host ""
    Write-Host "Откат выполнен. Вернуться к новой версии — пункт 1 (полный релиз)." -ForegroundColor Green
    Write-Log "откат выполнен"
    Write-Host "Лог: $script:LogFile" -ForegroundColor DarkGray
}

function Invoke-Release {
    Start-DeployLog "release"

    Write-Host "vkr-blog: релиз с пересборкой контейнера" -ForegroundColor Green
    Write-Host "Проект: $root"

    Assert-Docker
    Assert-ProdBranch

    if ($SkipBackup) {
        Write-Host "Бэкап пропущен (-SkipBackup)" -ForegroundColor Yellow
        Write-Log "бэкап пропущен"
    }
    else {
        Invoke-Backup
    }

    if ($SkipPull) {
        Write-Host "git pull пропущен (-SkipPull)" -ForegroundColor Yellow
        Write-Log "git pull пропущен"
    }
    else {
        Write-Step "Обновляю код: git pull --ff-only"
        Invoke-Checked "git" @("pull", "--ff-only")
    }

    # Текущий образ — точка возврата. Если его ещё нет (первый релиз), просто идём дальше
    & docker image inspect $Image *> $null
    if ($LASTEXITCODE -eq 0) {
        Write-Step "Запоминаю текущий образ как previous"
        Invoke-Checked "docker" @("tag", $Image, $PrevImage)
    }

    Write-Step "Собираю образ app"
    Invoke-Checked "docker" @("compose", "build", "app")

    Write-Step "Перезапускаю контейнер"
    Invoke-Checked "docker" @("compose", "up", "-d")

    Wait-Health

    Invoke-Cleanup -AfterRelease

    $commit = (& git log -1 --format="%h %s").Trim()
    Write-Host ""
    Write-Host "Готово: $commit" -ForegroundColor Green
    & docker compose ps
    Write-Log "готово: $commit"
    Write-Host "Лог: $script:LogFile" -ForegroundColor DarkGray
}

function Show-Menu {
    Write-Host ""
    Write-Host "vkr-blog: обслуживание прода" -ForegroundColor Green
    Write-Host "  1 — Полный релиз: бэкап, git pull, сборка, перезапуск"
    Write-Host "  2 — Статус и логи"
    Write-Host "  3 — Откат на предыдущий образ"
    Write-Host "  4 — Очистка старых образов"
    Write-Host "  0 — Выход"

    $choice = (Read-Host "Пункт").Trim()

    switch ($choice) {
        "1" { Invoke-Release }
        "2" { Show-Status }
        "3" { Invoke-Rollback }
        "4" { Invoke-Cleanup }
        "0" { exit 0 }
        default { Stop-Deploy "Нет такого пункта: '$choice'" }
    }
}

Set-Location $root

if ($PSBoundParameters.Count -eq 0) {
    Show-Menu
}
else {
    Invoke-Release
}
