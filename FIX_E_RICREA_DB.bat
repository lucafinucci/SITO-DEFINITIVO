@echo off
echo ========================================
echo   FIX PERMISSIONS E RICREA DATABASE
echo ========================================
echo.

cd /d "%~dp0"

echo [1/3] Fix permessi root MariaDB...
echo.

"C:\xampp\mysql\bin\mysql.exe" -u root --skip-grant-tables -e "FLUSH PRIVILEGES; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION; FLUSH PRIVILEGES;"

if %ERRORLEVEL% NEQ 0 (
    echo [ERRORE] Fix permessi fallito
    echo.
    echo Provo metodo alternativo...
    echo.

    rem Ferma MySQL
    net stop MySQL
    timeout /t 2 /nobreak >nul

    rem Avvia MySQL con skip-grant-tables
    start "" "C:\xampp\mysql\bin\mysqld.exe" --skip-grant-tables --standalone
    timeout /t 5 /nobreak >nul

    rem Fix permessi
    "C:\xampp\mysql\bin\mysql.exe" -e "FLUSH PRIVILEGES; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION; FLUSH PRIVILEGES;"

    rem Riavvia MySQL normale
    taskkill /F /IM mysqld.exe >nul 2>&1
    timeout /t 2 /nobreak >nul
    net start MySQL
    timeout /t 3 /nobreak >nul
)

echo.
echo [2/3] Elimino e ricreo database...
echo.

"C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS finch_ai_clienti; CREATE DATABASE finch_ai_clienti CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE finch_ai_clienti;"

if %ERRORLEVEL% EQU 0 (
    echo [OK] Database creato
) else (
    echo [ERRORE] Creazione database fallita
    pause
    exit /b 1
)

echo.
echo [3/3] Importo schema e dati...
echo.

"C:\xampp\mysql\bin\mysql.exe" -u root finch_ai_clienti < "database\schema.sql"

if %ERRORLEVEL% EQU 0 (
    echo [OK] Tabelle create
) else (
    echo [ERRORE] Import schema fallito
)

rem Crea utente demo
"C:\xampp\mysql\bin\mysql.exe" -u root finch_ai_clienti -e "INSERT INTO utenti (email, password_hash, nome, cognome, azienda, attivo) VALUES ('demo@finch-ai.it', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo', 'User', 'Demo Company', TRUE);"

"C:\xampp\mysql\bin\mysql.exe" -u root finch_ai_clienti -e "INSERT INTO servizi (nome, descrizione, codice, prezzo_mensile, attivo) VALUES ('Document Intelligence', 'Analisi automatica documenti con AI', 'DOC_INTEL', 299.00, TRUE);"

"C:\xampp\mysql\bin\mysql.exe" -u root finch_ai_clienti -e "INSERT INTO utenti_servizi (user_id, servizio_id, data_attivazione, stato) VALUES (1, 1, CURDATE(), 'attivo');"

echo.
echo ========================================
echo   COMPLETATO!
echo ========================================
echo.
echo Database: finch_ai_clienti
echo Utente demo: demo@finch-ai.it
echo Password: password
echo.
echo Vai su: http://localhost/SITO/area-clienti/login.php
echo.
pause
