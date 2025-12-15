@echo off
echo ================================================
echo FIX MYSQL + SETUP DATABASE FINCH-AI
echo ================================================
echo.
echo Step 1: Configurazione client MySQL...
echo.

REM Crea file di configurazione temporaneo
echo [client] > "%TEMP%\my_temp.cnf"
echo protocol=TCP >> "%TEMP%\my_temp.cnf"
echo host=127.0.0.1 >> "%TEMP%\my_temp.cnf"
echo port=3306 >> "%TEMP%\my_temp.cnf"
echo user=root >> "%TEMP%\my_temp.cnf"
echo password= >> "%TEMP%\my_temp.cnf"

echo File di configurazione creato.
echo.
echo Step 2: Test connessione...
echo.

cd /d "C:\xampp\mysql\bin"

mysql --defaults-file="%TEMP%\my_temp.cnf" -e "SELECT VERSION() AS Version, DATABASE() AS CurrentDB;"

if %errorlevel% NEQ 0 (
    echo.
    echo ERRORE: Impossibile connettersi a MySQL
    echo.
    echo Proviamo metodo alternativo...
    echo.

    REM Prova senza config file ma con skip-ssl
    mysql -h 127.0.0.1 -P 3306 --skip-ssl -u root -e "SELECT VERSION();"

    if %errorlevel% NEQ 0 (
        echo.
        echo ================================================
        echo ERRORE CRITICO
        echo ================================================
        echo.
        echo MySQL non risponde su nessun protocollo.
        echo.
        echo SOLUZIONE:
        echo 1. Apri XAMPP Control Panel
        echo 2. Stop MySQL
        echo 3. Config MySQL (pulsante accanto)
        echo 4. Cerca 'bind-address' e commenta la riga
        echo 5. Start MySQL
        echo 6. Riprova questo script
        echo.
        del "%TEMP%\my_temp.cnf"
        pause
        exit /b 1
    )
)

echo.
echo Connessione riuscita!
echo.
echo Step 3: Importazione database...
echo.

mysql --defaults-file="%TEMP%\my_temp.cnf" < "C:\Users\oneno\Desktop\SITO\database\ESEGUI_IN_PHPMYADMIN.sql"

if %errorlevel% == 0 (
    echo.
    echo ================================================
    echo SUCCESS! Database creato con successo!
    echo ================================================
    echo.
    echo Database: finch_ai_clienti
    echo Tabelle: 7 tabelle create
    echo Utenti: 3 utenti demo inseriti
    echo.
    echo CREDENZIALI DI TEST:
    echo Email: demo@finch-ai.it
    echo Password: Demo123!
    echo.
    echo PROSSIMO PASSO:
    echo Apri il browser e vai a:
    echo http://localhost/area-clienti/login.php
    echo.

    REM Verifica tabelle create
    echo Verifica tabelle create:
    mysql --defaults-file="%TEMP%\my_temp.cnf" finch_ai_clienti -e "SHOW TABLES;"
    echo.
) else (
    echo.
    echo ================================================
    echo ERRORE durante importazione
    echo ================================================
    echo.
)

del "%TEMP%\my_temp.cnf"
pause
