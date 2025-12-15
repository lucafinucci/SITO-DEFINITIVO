@echo off
echo ================================================
echo TEST CONNESSIONE MYSQL
echo ================================================
echo.
echo Tentativo connessione a 127.0.0.1:3306...
echo.

cd /d "C:\xampp\mysql\bin"

mysql --protocol=TCP --host=127.0.0.1 --port=3306 -u root -e "SELECT VERSION(); SHOW DATABASES;"

if %errorlevel% == 0 (
    echo.
    echo ================================================
    echo CONNESSIONE RIUSCITA!
    echo ================================================
    echo.
    echo MySQL e' raggiungibile su 127.0.0.1
    echo Ora puoi eseguire SETUP_DATABASE.bat
    echo.
) else (
    echo.
    echo ================================================
    echo ERRORE DI CONNESSIONE
    echo ================================================
    echo.
    echo MySQL non risponde su 127.0.0.1:3306
    echo.
    echo POSSIBILI CAUSE:
    echo 1. MySQL non e' avviato in XAMPP
    echo 2. Firewall blocca la porta 3306
    echo 3. MySQL configurato male
    echo.
    echo SOLUZIONE:
    echo Apri XAMPP Control Panel e:
    echo 1. Stop MySQL
    echo 2. Start MySQL
    echo 3. Riprova questo script
    echo.
)

pause
