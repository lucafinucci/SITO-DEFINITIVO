@echo off
echo ========================================
echo   BACKUP DATABASE - Area Clienti
echo ========================================
echo.

set BACKUP_DIR=C:\Users\oneno\Desktop\SITO\database\backups
set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=finch_ai_clienti
set DATE=%date:~6,4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set DATE=%DATE: =0%

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo Creazione backup: %DB_NAME%_%DATE%.sql
echo.

"%MYSQL_PATH%\mysqldump.exe" -u root %DB_NAME% > "%BACKUP_DIR%\%DB_NAME%_%DATE%.sql"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Backup completato!
    echo File: %BACKUP_DIR%\%DB_NAME%_%DATE%.sql
    echo.
    echo Puoi chiudere XAMPP tranquillamente.
    echo Domani esegui RESTORE_DB.bat per ripristinare.
) else (
    echo.
    echo [ERRORE] Backup fallito!
    echo Verifica che MySQL sia avviato in XAMPP.
)

echo.
pause
