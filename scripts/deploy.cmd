@echo off
rem vkr-blog on the Windows server. All logic lives in deploy.ps1.
rem Double-click: menu (release / status / rollback / cleanup).
rem With switches: unattended release, e.g. scripts\deploy.cmd -SkipBackup -SkipPull
setlocal

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy.ps1" %*
set "code=%errorlevel%"

if not "%code%"=="0" (
    echo.
    echo Deploy failed. Read the messages above.
)

rem Pause only when the file was double-clicked, so the window stays open
echo %cmdcmdline% | find /i "%~nx0" >nul
if not errorlevel 1 pause

exit /b %code%
