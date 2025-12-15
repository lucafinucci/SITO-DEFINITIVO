@echo off
echo ========================================
echo   RESTORE DATABASE - Area Clienti
echo ========================================
echo.

set BACKUP_DIR=C:\Users\oneno\Desktop\SITO\database\backups
set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=finch_ai_clienti

echo Cerco ultimo backup...
echo.

for /f "delims=" %%f in ('dir /b /o-d "%BACKUP_DIR%\*.sql" 2^>nul') do (
    set LAST_BACKUP=%%f
    goto :found
)

:found
if not defined LAST_BACKUP (
    echo [ERRORE] Nessun backup trovato in %BACKUP_DIR%
    echo.
    echo Esegui prima BACKUP_DB.bat
    pause
    exit /b 1
)

echo Ultimo backup trovato: %LAST_BACKUP%
echo.
echo Ripristino database...
echo.

"%MYSQL_PATH%\mysql.exe" -u root %DB_NAME% < "%BACKUP_DIR%\%LAST_BACKUP%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Database ripristinato!
    echo.
    echo Ora puoi usare l'Area Clienti normalmente.
) else (
    echo.
    echo [ERRORE] Ripristino fallito!
    echo Verifica che MySQL sia avviato in XAMPP.
)

echo.
pause
