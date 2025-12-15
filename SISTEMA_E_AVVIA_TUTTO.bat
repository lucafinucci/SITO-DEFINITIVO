@echo off
chcp 65001 >nul
echo ╔══════════════════════════════════════════════════════════════╗
echo ║    SISTEMA AREA CLIENTI - AVVIO COMPLETO                    ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.

REM ============================================================
REM STEP 1: Ferma MySQL
REM ============================================================
echo [1/6] Fermando MySQL esistente...
taskkill /F /IM mysqld.exe 2>nul
taskkill /F /IM mysql.exe 2>nul
timeout /t 2 /nobreak >nul
echo ✓ Processi MySQL fermati
echo.

REM ============================================================
REM STEP 2: Pulisci file temporanei
REM ============================================================
echo [2/6] Pulizia file temporanei MySQL...
del "C:\xampp\mysql\data\*.pid" 2>nul
del "C:\xampp\mysql\data\ibtmp1" 2>nul
del "C:\xampp\mysql\data\*.err" 2>nul
echo ✓ File temporanei eliminati
echo.

REM ============================================================
REM STEP 3: Verifica configurazione my.ini
REM ============================================================
echo [3/6] Verifica configurazione my.ini...
findstr /C:"key_buffer_size" "C:\xampp\mysql\bin\my.ini" >nul
if %errorlevel% equ 0 (
    echo ✓ my.ini corretto
) else (
    echo ⚠ my.ini potrebbe avere problemi
)
findstr /C:"bind-address=127.0.0.1" "C:\xampp\mysql\bin\my.ini" >nul
if %errorlevel% equ 0 (
    echo ✓ bind-address configurato
) else (
    echo ℹ bind-address non trovato (ok)
)
echo.

REM ============================================================
REM STEP 4: Avvia MySQL
REM ============================================================
echo [4/6] Avvio MySQL...
echo.
echo ⚠ IMPORTANTE: NON CHIUDERE QUESTA FINESTRA!
echo Se la chiudi, MySQL si fermerà.
echo.
cd C:\xampp\mysql\bin
start "MySQL Server" /B mysqld.exe --bind-address=127.0.0.1 --console

REM ============================================================
REM STEP 5: Attendi avvio
REM ============================================================
echo [5/6] Attesa avvio MySQL (20 secondi)...
echo.
for /L %%i in (1,1,20) do (
    <nul set /p "=."
    timeout /t 1 /nobreak >nul
)
echo.
echo.

REM ============================================================
REM STEP 6: Verifica
REM ============================================================
echo [6/6] Verifica stato MySQL...
echo.

netstat -ano | findstr ":3306" >nul
if %errorlevel% equ 0 (
    echo ╔══════════════════════════════════════════════════════════════╗
    echo ║               ✅ MySQL AVVIATO CORRETTAMENTE!                ║
    echo ╚══════════════════════════════════════════════════════════════╝
    echo.
    echo Porta 3306: LISTENING
    echo.
    echo ┌──────────────────────────────────────────────────────────────┐
    echo │ PROSSIMI PASSI:                                              │
    echo └──────────────────────────────────────────────────────────────┘
    echo.
    echo 1. Apri browser e vai a:
    echo    http://localhost/phpmyadmin
    echo.
    echo 2. Verifica database "finch_ai_clienti" esiste
    echo    Se NON esiste, importalo da: database/schema.sql
    echo.
    echo 3. Testa area clienti:
    echo    http://localhost/area-clienti/login.php
    echo.
    echo 4. Credenziali demo:
    echo    Email: demo@finch-ai.it
    echo    Password: Demo123!
    echo.
    echo ┌──────────────────────────────────────────────────────────────┐
    echo │ IMPORTANTE:                                                  │
    echo └──────────────────────────────────────────────────────────────┘
    echo ⚠ NON CHIUDERE questa finestra
    echo ⚠ Se la chiudi, MySQL si ferma
    echo.
    echo Per fermare MySQL: Ctrl+C in questa finestra
    echo.
) else (
    echo ╔══════════════════════════════════════════════════════════════╗
    echo ║               ❌ MySQL NON SI È AVVIATO                      ║
    echo ╚══════════════════════════════════════════════════════════════╝
    echo.
    echo Possibili cause:
    echo - Porta 3306 già occupata
    echo - Errore nel file my.ini
    echo - Permessi insufficienti
    echo.
    echo Soluzioni:
    echo 1. Esegui questo script come AMMINISTRATORE
    echo    Click destro → "Esegui come amministratore"
    echo.
    echo 2. Verifica porta 3306:
    echo    netstat -ano ^| findstr ":3306"
    echo.
    echo 3. Leggi log errori:
    echo    C:\xampp\mysql\data\mysql_error.log
    echo.
    echo 4. Oppure avvia da XAMPP Control Panel
    echo    (come amministratore)
    echo.
)

echo.
echo ════════════════════════════════════════════════════════════════
echo.
pause
