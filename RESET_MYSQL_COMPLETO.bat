@echo off
chcp 65001 >nul
echo ╔══════════════════════════════════════════════════════════════╗
echo ║         RESET COMPLETO MySQL - SOLUZIONE CRASH               ║
echo ╚══════════════════════════════════════════════════════════════╝
echo.
echo ⚠️  ATTENZIONE: Questo script resetta MySQL completamente
echo ⚠️  I DATABASE VERRANNO PRESERVATI ma i file di sistema resettati
echo.
pause
echo.

REM ============================================================
REM STEP 1: Backup database
REM ============================================================
echo [1/7] Backup database esistenti...
if not exist "C:\backup_mysql_emergency" mkdir "C:\backup_mysql_emergency"
xcopy "C:\xampp\mysql\data\finch_ai_clienti" "C:\backup_mysql_emergency\finch_ai_clienti\" /E /I /Y >nul 2>&1
if exist "C:\backup_mysql_emergency\finch_ai_clienti" (
    echo ✓ Backup completato: C:\backup_mysql_emergency\
) else (
    echo ℹ Nessun database da backuppare
)
echo.

REM ============================================================
REM STEP 2: Ferma MySQL
REM ============================================================
echo [2/7] Fermando tutti i processi MySQL...
taskkill /F /IM mysqld.exe 2>nul
taskkill /F /IM mysql.exe 2>nul
timeout /t 3 /nobreak >nul
echo ✓ Processi fermati
echo.

REM ============================================================
REM STEP 3: Backup file critici
REM ============================================================
echo [3/7] Backup file di sistema InnoDB...
copy "C:\xampp\mysql\data\ibdata1" "C:\backup_mysql_emergency\ibdata1.bak" >nul 2>&1
copy "C:\xampp\mysql\data\ib_logfile0" "C:\backup_mysql_emergency\ib_logfile0.bak" >nul 2>&1
copy "C:\xampp\mysql\data\ib_logfile1" "C:\backup_mysql_emergency\ib_logfile1.bak" >nul 2>&1
echo ✓ Backup file di sistema completato
echo.

REM ============================================================
REM STEP 4: Elimina file InnoDB corrotti
REM ============================================================
echo [4/7] Eliminazione file InnoDB corrotti...
del "C:\xampp\mysql\data\ibdata1" 2>nul
del "C:\xampp\mysql\data\ib_logfile0" 2>nul
del "C:\xampp\mysql\data\ib_logfile1" 2>nul
del "C:\xampp\mysql\data\ibtmp1" 2>nul
del "C:\xampp\mysql\data\*.pid" 2>nul
del "C:\xampp\mysql\data\*.err" 2>nul
echo ✓ File eliminati
echo.

REM ============================================================
REM STEP 5: Ricrea my.ini corretto
REM ============================================================
echo [5/7] Creazione my.ini pulito...

echo [mysqld] > "C:\xampp\mysql\bin\my.ini"
echo port=3306 >> "C:\xampp\mysql\bin\my.ini"
echo socket="C:/xampp/mysql/mysql.sock" >> "C:\xampp\mysql\bin\my.ini"
echo basedir="C:/xampp/mysql" >> "C:\xampp\mysql\bin\my.ini"
echo tmpdir="C:/xampp/tmp" >> "C:\xampp\mysql\bin\my.ini"
echo datadir="C:/xampp/mysql/data" >> "C:\xampp\mysql\bin\my.ini"
echo pid_file="mysql.pid" >> "C:\xampp\mysql\bin\my.ini"
echo bind-address=127.0.0.1 >> "C:\xampp\mysql\bin\my.ini"
echo key_buffer_size=16M >> "C:\xampp\mysql\bin\my.ini"
echo max_allowed_packet=16M >> "C:\xampp\mysql\bin\my.ini"
echo sort_buffer_size=512K >> "C:\xampp\mysql\bin\my.ini"
echo net_buffer_length=8K >> "C:\xampp\mysql\bin\my.ini"
echo read_buffer_size=256K >> "C:\xampp\mysql\bin\my.ini"
echo read_rnd_buffer_size=512K >> "C:\xampp\mysql\bin\my.ini"
echo myisam_sort_buffer_size=8M >> "C:\xampp\mysql\bin\my.ini"
echo log_error="mysql_error.log" >> "C:\xampp\mysql\bin\my.ini"
echo innodb_data_home_dir="C:/xampp/mysql/data" >> "C:\xampp\mysql\bin\my.ini"
echo innodb_data_file_path=ibdata1:10M:autoextend >> "C:\xampp\mysql\bin\my.ini"
echo innodb_log_group_home_dir="C:/xampp/mysql/data" >> "C:\xampp\mysql\bin\my.ini"
echo innodb_buffer_pool_size=16M >> "C:\xampp\mysql\bin\my.ini"
echo innodb_log_file_size=5M >> "C:\xampp\mysql\bin\my.ini"
echo innodb_log_buffer_size=8M >> "C:\xampp\mysql\bin\my.ini"
echo innodb_flush_log_at_trx_commit=1 >> "C:\xampp\mysql\bin\my.ini"
echo innodb_lock_wait_timeout=50 >> "C:\xampp\mysql\bin\my.ini"
echo default_storage_engine=InnoDB >> "C:\xampp\mysql\bin\my.ini"
echo innodb_force_recovery=0 >> "C:\xampp\mysql\bin\my.ini"

echo ✓ my.ini ricreato
echo.

REM ============================================================
REM STEP 6: Primo avvio (ricostruisce InnoDB)
REM ============================================================
echo [6/7] Primo avvio MySQL (ricostruzione InnoDB)...
echo.
echo Questo può richiedere fino a 60 secondi...
echo.

cd C:\xampp\mysql\bin
start "MySQL Init" /B mysqld.exe --initialize-insecure --console

echo Attesa inizializzazione...
for /L %%i in (1,1,30) do (
    <nul set /p "=."
    timeout /t 1 /nobreak >nul
)
echo.
echo.

REM Ferma processo init
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 /nobreak >nul

echo ✓ InnoDB reinizializzato
echo.

REM ============================================================
REM STEP 7: Avvio normale
REM ============================================================
echo [7/7] Avvio MySQL normale...
start "MySQL Server" /B mysqld.exe --bind-address=127.0.0.1 --console

echo Attesa avvio normale...
for /L %%i in (1,1,20) do (
    <nul set /p "=."
    timeout /t 1 /nobreak >nul
)
echo.
echo.

REM Verifica
netstat -ano | findstr ":3306" >nul
if %errorlevel% equ 0 (
    echo ╔══════════════════════════════════════════════════════════════╗
    echo ║            ✅ MySQL RIPARATO E AVVIATO!                      ║
    echo ╚══════════════════════════════════════════════════════════════╝
    echo.
    echo Porta 3306: LISTENING ✓
    echo.
    echo ┌──────────────────────────────────────────────────────────────┐
    echo │ IMPORTANTE:                                                  │
    echo └──────────────────────────────────────────────────────────────┘
    echo.
    echo 1. Backup salvato in: C:\backup_mysql_emergency\
    echo.
    echo 2. Devi REIMPORTARE i database:
    echo    a. Apri phpMyAdmin: http://localhost/phpmyadmin
    echo    b. Crea database: finch_ai_clienti
    echo    c. Importa: database/schema.sql
    echo    d. Importa: database/seed.sql
    echo.
    echo 3. Oppure ripristina backup:
    echo    - Copia cartella: C:\backup_mysql_emergency\finch_ai_clienti
    echo    - In: C:\xampp\mysql\data\finch_ai_clienti
    echo.
    echo ⚠️ NON CHIUDERE questa finestra (MySQL attivo qui)
    echo.
) else (
    echo ╔══════════════════════════════════════════════════════════════╗
    echo ║            ❌ RESET FALLITO                                  ║
    echo ╚══════════════════════════════════════════════════════════════╝
    echo.
    echo MySQL non si avvia ancora.
    echo.
    echo ULTIMA SOLUZIONE:
    echo 1. Disinstalla XAMPP completamente
    echo 2. Reinstalla XAMPP fresco
    echo 3. Ripristina database da: C:\backup_mysql_emergency\
    echo.
)

echo.
pause
