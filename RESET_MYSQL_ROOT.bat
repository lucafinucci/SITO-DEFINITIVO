@echo off
echo ================================================
echo RESET PASSWORD E PERMESSI ROOT MYSQL
echo ================================================
echo.
echo Questo script resetta completamente root MySQL
echo.
pause

cd /d "C:\xampp\mysql\bin"

echo Step 1: Stop MySQL se in esecuzione...
taskkill /F /IM mysqld.exe 2>nul

echo Step 2: Avvio MySQL in modalita sicura (skip-grant-tables)...
start /B mysqld --skip-grant-tables --skip-networking

timeout /t 5

echo Step 3: Reset password e permessi root...
mysql -u root mysql -e "FLUSH PRIVILEGES; ALTER USER 'root'@'localhost' IDENTIFIED BY ''; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'root'@'::1' IDENTIFIED BY '' WITH GRANT OPTION; FLUSH PRIVILEGES;"

if %errorlevel% == 0 (
    echo SUCCESS! Permessi aggiornati!
) else (
    echo ERRORE durante l'aggiornamento
)

echo Step 4: Stop MySQL modalita sicura...
taskkill /F /IM mysqld.exe 2>nul

echo.
echo Ora riavvia MySQL normalmente da XAMPP Control Panel
echo e riprova gli script!
echo.
pause
