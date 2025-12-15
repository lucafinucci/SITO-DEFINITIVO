@echo off
echo ========================================
echo AVVIO MySQL - Configurazione Corretta
echo ========================================
echo.

REM Ferma eventuali processi MySQL
echo [1/4] Fermando processi MySQL esistenti...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 /nobreak >nul

REM Pulisci file temporanei
echo [2/4] Pulizia file temporanei...
del "C:\xampp\mysql\data\*.pid" 2>nul
del "C:\xampp\mysql\data\ibtmp1" 2>nul
del "C:\xampp\mysql\data\*.err" 2>nul
echo ✓ File puliti

REM Avvia MySQL
echo [3/4] Avvio MySQL...
cd C:\xampp\mysql\bin
start /B mysqld.exe --bind-address=127.0.0.1 --console

REM Attendi avvio
echo [4/4] Attesa avvio (15 secondi)...
timeout /t 15 /nobreak >nul

REM Verifica
echo.
echo ========================================
echo VERIFICA:
echo ========================================
netstat -ano | findstr ":3306"

if %errorlevel% equ 0 (
    echo.
    echo ✅ MySQL AVVIATO CORRETTAMENTE!
    echo.
    echo Porta 3306 in ascolto
    echo.
    echo Ora puoi:
    echo 1. Aprire phpMyAdmin: http://localhost/phpmyadmin
    echo 2. Testare area clienti: http://localhost/area-clienti/login.php
    echo.
    echo ⚠️ NON CHIUDERE QUESTA FINESTRA!
    echo Se la chiudi, MySQL si ferma.
) else (
    echo.
    echo ❌ MySQL non si è avviato
    echo.
    echo Controlla il log sopra per errori.
    echo Oppure avvia XAMPP Control Panel come Amministratore.
)

echo.
pause
