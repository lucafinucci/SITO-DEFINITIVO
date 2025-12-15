@echo off
chcp 65001 >nul
echo ╔══════════════════════════════════════════════════════════════╗
echo ║    AVVIO RAPIDO - DOPO REINSTALLAZIONE XAMPP                ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Usa questo script DOPO aver reinstallato XAMPP.
echo.
pause
echo.

REM ============================================================
REM STEP 1: Verifica XAMPP installato
REM ============================================================
echo [1/5] Verifica installazione XAMPP...
if exist "C:\xampp\mysql\bin\mysqld.exe" (
    echo ✓ XAMPP trovato
) else (
    echo ❌ XAMPP non trovato!
    echo.
    echo Installa XAMPP da: https://www.apachefriends.org/
    echo Poi riesegui questo script.
    pause
    exit /b 1
)
echo.

REM ============================================================
REM STEP 2: Verifica MySQL configurato
REM ============================================================
echo [2/5] Verifica configurazione MySQL...
if exist "C:\xampp\mysql\bin\my.ini" (
    echo ✓ my.ini trovato
) else (
    echo ℹ my.ini non trovato, creo configurazione base...

    echo [mysqld] > "C:\xampp\mysql\bin\my.ini"
    echo port=3306 >> "C:\xampp\mysql\bin\my.ini"
    echo socket="C:/xampp/mysql/mysql.sock" >> "C:\xampp\mysql\bin\my.ini"
    echo basedir="C:/xampp/mysql" >> "C:\xampp\mysql\bin\my.ini"
    echo datadir="C:/xampp/mysql/data" >> "C:\xampp\mysql\bin\my.ini"
    echo bind-address=127.0.0.1 >> "C:\xampp\mysql\bin\my.ini"
    echo key_buffer_size=16M >> "C:\xampp\mysql\bin\my.ini"
    echo max_allowed_packet=16M >> "C:\xampp\mysql\bin\my.ini"

    echo ✓ my.ini creato
)
echo.

REM ============================================================
REM STEP 3: Avvia MySQL
REM ============================================================
echo [3/5] Avvio MySQL...
cd C:\xampp\mysql\bin
start "MySQL Server" /B mysqld.exe --console

echo Attesa avvio (20 secondi)...
for /L %%i in (1,1,20) do (
    <nul set /p "=."
    timeout /t 1 /nobreak >nul
)
echo.
echo.

REM ============================================================
REM STEP 4: Verifica MySQL
REM ============================================================
echo [4/5] Verifica MySQL...
netstat -ano | findstr ":3306" >nul
if %errorlevel% equ 0 (
    echo ✓ MySQL ATTIVO sulla porta 3306
    echo.
) else (
    echo ❌ MySQL NON attivo
    echo.
    echo Verifica errori in: C:\xampp\mysql\data\mysql_error.log
    echo.
    pause
    exit /b 1
)

REM ============================================================
REM STEP 5: Ripristina database
REM ============================================================
echo [5/5] Ripristino database...
echo.

if exist "C:\backup_mysql_emergency\finch_ai_clienti" (
    echo ℹ Backup trovato in: C:\backup_mysql_emergency\
    echo.
    echo OPZIONE 1: Ripristino automatico (copia cartella)
    echo    xcopy "C:\backup_mysql_emergency\finch_ai_clienti" "C:\xampp\mysql\data\finch_ai_clienti\" /E /I /Y
    echo.
    echo OPZIONE 2: Importa via phpMyAdmin
    echo    1. Apri: http://localhost/phpmyadmin
    echo    2. Crea database: finch_ai_clienti
    echo    3. Importa: database\schema.sql
    echo    4. Importa: database\seed.sql
    echo.
    choice /C 12 /M "Scegli opzione (1=Auto, 2=Manuale):"

    if errorlevel 2 goto manual
    if errorlevel 1 goto auto

    :auto
    echo.
    echo Ripristino automatico...
    taskkill /F /IM mysqld.exe 2>nul
    timeout /t 3 /nobreak >nul

    xcopy "C:\backup_mysql_emergency\finch_ai_clienti" "C:\xampp\mysql\data\finch_ai_clienti\" /E /I /Y

    if %errorlevel% equ 0 (
        echo ✓ Database ripristinato
        echo.
        echo Riavvio MySQL...
        cd C:\xampp\mysql\bin
        start "MySQL Server" /B mysqld.exe --console
        timeout /t 10 /nobreak >nul
    ) else (
        echo ❌ Errore ripristino
        echo Usa OPZIONE 2 (Manuale)
    )
    goto end

    :manual
    echo.
    echo Apri phpMyAdmin e importa manualmente.
    echo.
    start http://localhost/phpmyadmin
    echo.
    pause
    goto end

) else (
    echo ℹ Nessun backup trovato in C:\backup_mysql_emergency\
    echo.
    echo Importa database manualmente:
    echo 1. Apri: http://localhost/phpmyadmin
    echo 2. Crea database: finch_ai_clienti
    echo 3. Importa: database\schema.sql
    echo 4. Importa: database\seed.sql
    echo.
    start http://localhost/phpmyadmin
    echo.
    pause
)

:end
echo.
echo ╔══════════════════════════════════════════════════════════════╗
echo ║                  ✅ SETUP COMPLETATO!                        ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo Verifica area clienti:
echo → http://localhost/area-clienti/login.php
echo.
echo Credenziali demo:
echo   Email: demo@finch-ai.it
echo   Password: Demo123!
echo.
echo ⚠️ NON CHIUDERE questa finestra (MySQL attivo qui)
echo.
pause
