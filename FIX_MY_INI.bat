@echo off
echo ========================================
echo FIX my.ini - Ripristino Configurazione
echo ========================================
echo.

REM Ferma MySQL
echo [1/4] Fermando MySQL...
taskkill /F /IM mysqld.exe 2>nul
timeout /t 2 /nobreak >nul

REM Backup my.ini attuale
echo [2/4] Backup my.ini...
copy "C:\xampp\mysql\bin\my.ini" "C:\xampp\mysql\bin\my.ini.broken" >nul 2>&1

REM Ripristina my.ini originale
echo [3/4] Ripristino my.ini originale...
if exist "C:\xampp\mysql\bin\my.ini.backup" (
    copy "C:\xampp\mysql\bin\my.ini.backup" "C:\xampp\mysql\bin\my.ini" >nul
    echo ✓ Ripristinato da backup
) else (
    echo Creando my.ini corretto...

    echo [mysqld] > "C:\xampp\mysql\bin\my.ini"
    echo port=3306 >> "C:\xampp\mysql\bin\my.ini"
    echo socket="C:/xampp/mysql/mysql.sock" >> "C:\xampp\mysql\bin\my.ini"
    echo basedir="C:/xampp/mysql" >> "C:\xampp\mysql\bin\my.ini"
    echo tmpdir="C:/xampp/tmp" >> "C:\xampp\mysql\bin\my.ini"
    echo datadir="C:/xampp/mysql/data" >> "C:\xampp\mysql\bin\my.ini"
    echo key_buffer_size=16M >> "C:\xampp\mysql\bin\my.ini"
    echo max_allowed_packet=1M >> "C:\xampp\mysql\bin\my.ini"
    echo sort_buffer_size=512K >> "C:\xampp\mysql\bin\my.ini"
    echo net_buffer_length=8K >> "C:\xampp\mysql\bin\my.ini"
    echo read_buffer_size=256K >> "C:\xampp\mysql\bin\my.ini"
    echo read_rnd_buffer_size=512K >> "C:\xampp\mysql\bin\my.ini"
    echo myisam_sort_buffer_size=8M >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_data_home_dir="C:/xampp/mysql/data" >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_data_file_path=ibdata1:10M:autoextend >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_log_group_home_dir="C:/xampp/mysql/data" >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_buffer_pool_size=16M >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_log_file_size=5M >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_log_buffer_size=8M >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_flush_log_at_trx_commit=1 >> "C:\xampp\mysql\bin\my.ini"
    echo innodb_lock_wait_timeout=50 >> "C:\xampp\mysql\bin\my.ini"

    echo ✓ my.ini creato
)

REM Pulisci file temporanei
echo [4/4] Pulizia file temporanei...
del "C:\xampp\mysql\data\*.err" 2>nul
del "C:\xampp\mysql\data\*.pid" 2>nul
del "C:\xampp\mysql\data\ibtmp1" 2>nul

echo.
echo ========================================
echo CONFIGURAZIONE RIPRISTINATA!
echo ========================================
echo.
echo Ora:
echo 1. Apri XAMPP Control Panel come AMMINISTRATORE
echo 2. Click START su MySQL
echo 3. Attendi 10 secondi
echo.
pause
