<#
.SYNOPSIS
    Бэкап БД и загруженных файлов vkr-blog на Windows-сервере (Docker Desktop).

.DESCRIPTION
    Снимает дамп PostgreSQL из контейнера db и архив тома storage из контейнера app.

    Контейнер сам пакует данные в свой /tmp, скрипт забирает готовый файл через
    docker compose cp. Так через границу Windows/Linux не идёт ни пайп, ни бинарный
    поток: PowerShell испортил бы байты, а WSL-дистриб может смотреть на другой
    демон Docker, чем Docker Desktop (тогда compose в контейнерах «не запущен»).

    Старые архивы (db-*, storage-*, deploy-*.log) удаляются по дате изменения.

.PARAMETER BackupDir
    Куда складывать архивы. По умолчанию backups в папке проекта.

.PARAMETER RetentionDays
    Сколько дней хранить архивы. По умолчанию 14.

.EXAMPLE
    .\scripts\backup.ps1

.EXAMPLE
    .\scripts\backup.ps1 -BackupDir D:\vkr-backups -RetentionDays 30
#>
param(
    [string]$BackupDir = "backups",
    [int]$RetentionDays = 14
)

$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

function Fail([string]$Text) {
    Write-Host ""
    Write-Host "ОШИБКА: $Text" -ForegroundColor Red
    exit 1
}

function Invoke-Docker([string[]]$Arguments) {
    & docker @Arguments
    if ($LASTEXITCODE -ne 0) {
        Fail "команда завершилась с кодом $LASTEXITCODE`: docker $($Arguments -join ' ')"
    }
}

function Assert-Gzip([string]$Path) {
    if (-not (Test-Path $Path)) {
        Fail "файл не создан: $Path"
    }

    $file = Get-Item $Path
    if ($file.Length -lt 64) {
        Fail "архив подозрительно мал ($($file.Length) Б), похоже на пустой: $Path"
    }

    $stream = [System.IO.File]::OpenRead($Path)
    try {
        $magic = New-Object byte[] 2
        [void]$stream.Read($magic, 0, 2)
    }
    finally {
        $stream.Close()
    }

    if ($magic[0] -ne 0x1F -or $magic[1] -ne 0x8B) {
        Fail "это не gzip: $Path"
    }
}

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Fail "docker не найден в PATH. Запустите Docker Desktop и повторите."
}

# compose version проверяет только плагин, поэтому спрашиваем демон
$serverVersion = & docker info --format "{{.ServerVersion}}" 2>$null
if ($LASTEXITCODE -ne 0 -or -not "$serverVersion".Trim()) {
    Fail "Docker не отвечает. Запустите Docker Desktop и повторите."
}

foreach ($service in @("db", "app")) {
    $containerId = & docker compose ps -q $service
    if ($LASTEXITCODE -ne 0) {
        Fail "docker compose ps не отвечает в $root"
    }
    if (-not "$containerId".Trim()) {
        Fail "контейнер $service не запущен. Сначала: docker compose up -d"
    }
}

if ([System.IO.Path]::IsPathRooted($BackupDir)) {
    $dir = $BackupDir
}
else {
    $dir = Join-Path $root $BackupDir
}
New-Item -ItemType Directory -Force -Path $dir | Out-Null

$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$dbName = "db-$stamp.sql.gz"
$dbTarget = Join-Path $dir $dbName
$dbSql = "/tmp/vkr-db-$stamp.sql"

Write-Host "БД → $dbTarget"
# Дамп сначала в файл и только после успешного pg_dump — в gzip: иначе ошибка
# pg_dump спрячется за кодом gzip, и получится «валидный» пустой архив.
$dbCommand = 'pg_dump -U $POSTGRES_USER -d $POSTGRES_DB > ' + $dbSql +
    ' && gzip -c ' + $dbSql + ' > /tmp/' + $dbName +
    ' && rm -f ' + $dbSql
Invoke-Docker @("compose", "exec", "-T", "db", "sh", "-c", $dbCommand)
Invoke-Docker @("compose", "cp", "db:/tmp/$dbName", $dbTarget)
& docker compose exec -T db rm -f "/tmp/$dbName" *> $null
Assert-Gzip $dbTarget

$storageName = "storage-$stamp.tar.gz"
$storageTarget = Join-Path $dir $storageName

Write-Host "Файлы → $storageTarget"
$storageCommand = 'tar czf /tmp/' + $storageName + ' -C /var/www/storage .' +
    ' && gzip -t /tmp/' + $storageName
Invoke-Docker @("compose", "exec", "-T", "app", "sh", "-c", $storageCommand)
Invoke-Docker @("compose", "cp", "app:/tmp/$storageName", $storageTarget)
& docker compose exec -T app rm -f "/tmp/$storageName" *> $null
Assert-Gzip $storageTarget

$limit = (Get-Date).AddDays(-$RetentionDays)
Get-ChildItem -Path $dir -File |
    Where-Object { $_.LastWriteTime -lt $limit } |
    Remove-Item -Force

$kept = @(Get-ChildItem -Path $dir -File).Count
Write-Host "Готово. Файлов в $dir`: $kept"
