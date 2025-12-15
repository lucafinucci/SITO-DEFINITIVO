@echo off
echo ================================================
echo SETUP DATABASE FINCH-AI AREA CLIENTI
echo ================================================
echo.
echo Creazione database e importazione dati...
echo.

cd /d "C:\xampp\mysql\bin"

mysql --protocol=TCP --host=127.0.0.1 --port=3306 -u root < "C:\Users\oneno\Desktop\SITO\database\ESEGUI_IN_PHPMYADMIN.sql"

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
) else (
    echo.
    echo ================================================
    echo ERRORE durante la creazione del database
    echo ================================================
    echo.
    echo Verifica che MySQL sia avviato in XAMPP
    echo.
)

pause
