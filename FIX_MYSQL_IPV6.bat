@echo off
echo ========================================
echo FIX MySQL - Disabilita IPv6
echo ========================================
echo.

REM Ferma MySQL
echo [1/3] Fermando MySQL...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 /nobreak >nul

REM Modifica my.ini per usare solo IPv4
echo [2/3] Configurando MySQL per IPv4...

powershell -Command "$content = Get-Content 'C:\xampp\mysql\bin\my.ini'; if ($content -notmatch 'bind-address') { Add-Content 'C:\xampp\mysql\bin\my.ini' "`n# Force IPv4`nbind-address = 127.0.0.1" }"

echo ✓ Configurazione aggiornata

REM Pulisci e riavvia
echo [3/3] Pulizia e riavvio...
del "C:\xampp\mysql\data\*.pid" 2>nul
del "C:\xampp\mysql\data\ibtmp1" 2>nul

echo.
echo ========================================
echo FATTO!
echo ========================================
echo.
echo Ora MySQL usera' solo IPv4 (127.0.0.1)
echo.
echo PROSSIMO PASSO:
echo 1. Apri XAMPP Control Panel come AMMINISTRATORE
echo 2. Click START su MySQL
echo 3. Verifica che compaia "MySQL/MariaDB running on port 3306"
echo.
pause
