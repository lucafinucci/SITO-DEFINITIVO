@echo off
echo ========================================
echo FIX MySQL XAMPP - SOLUZIONE IMMEDIATA
echo ========================================
echo.

REM Ferma MySQL se in esecuzione
echo [1/5] Fermando MySQL...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 /nobreak >nul

REM Rimuovi file lock
echo [2/5] Rimuovendo file lock...
del "C:\xampp\mysql\data\*.err" 2>nul
del "C:\xampp\mysql\data\*.pid" 2>nul
del "C:\xampp\mysql\data\ibtmp1" 2>nul

REM Backup my.ini
echo [3/5] Backup configurazione...
copy "C:\xampp\mysql\bin\my.ini" "C:\xampp\mysql\bin\my.ini.backup" >nul 2>&1

REM Correggi my.ini
echo [4/5] Correggendo my.ini...
powershell -Command "(Get-Content 'C:\xampp\mysql\bin\my.ini') -replace 'skip-networking', '#skip-networking' | Set-Content 'C:\xampp\mysql\bin\my.ini'"

REM Avvia MySQL
echo [5/5] Avviando MySQL...
cd C:\xampp
start mysql_start.bat

echo.
echo ========================================
echo FATTO! Attendi 10 secondi...
echo ========================================
timeout /t 10

REM Verifica
netstat -ano | findstr ":3306"
if %errorlevel% equ 0 (
    echo.
    echo ✅ MySQL AVVIATO CORRETTAMENTE sulla porta 3306!
    echo.
    echo Ora puoi:
    echo 1. Aprire phpMyAdmin: http://localhost/phpmyadmin
    echo 2. Testare area clienti: http://localhost/area-clienti/login.php
) else (
    echo.
    echo ❌ MySQL non si è avviato. Prova SOLUZIONE MANUALE sotto.
)

echo.
pause
