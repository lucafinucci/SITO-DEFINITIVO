@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Setup Test KPI Dashboard Admin
echo ========================================
echo.
echo Questo script configurera i dati di test
echo per la dashboard KPI admin
echo.
pause

REM Verifica che XAMPP sia avviato
echo [1/3] Verifica MySQL...
"C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 --protocol=TCP -e "SELECT 'MySQL OK' AS status;" 2>nul
if %errorlevel% neq 0 (
    echo [ERRORE] MySQL non risponde. Avvia XAMPP prima di continuare.
    pause
    exit /b 1
)
echo OK MySQL attivo

REM Verifica database
echo.
echo [2/3] Verifica database finch_ai_clienti...
"C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 --protocol=TCP -e "USE finch_ai_clienti; SELECT 'Database OK' AS status;" 2>nul
if %errorlevel% neq 0 (
    echo [ERRORE] Database finch_ai_clienti non trovato.
    echo Crea prima il database con gli script in /database
    pause
    exit /b 1
)
echo OK Database trovato

REM Esegui script setup
echo.
echo [3/3] Eseguo script di setup...
"C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 --protocol=TCP finch_ai_clienti < "%~dp0database\setup_test_kpi_dashboard.sql"

if %errorlevel% neq 0 (
    echo [ERRORE] Errore durante l'esecuzione dello script SQL
    pause
    exit /b 1
)

echo.
echo ========================================
echo Setup completato con successo!
echo ========================================
echo.
echo Sono stati creati:
echo - 1 utente admin
echo - 5 clienti di test con Document Intelligence
echo - Dati di utilizzo per gli ultimi 3 mesi
echo.
echo ========================================
echo CREDENZIALI ADMIN
echo ========================================
echo Email:    admin@finch-ai.it
echo Password: password
echo.
echo ========================================
echo PROSSIMI PASSI
echo ========================================
echo.
echo 1. Apri browser: http://localhost/area-clienti/login.php
echo 2. Login con le credenziali admin sopra
echo 3. Vai a: http://localhost/area-clienti/admin/gestione-servizi.php
echo.
echo ========================================
echo CONFIGURAZIONE
echo ========================================
echo.
echo Ricordati di configurare il token in:
echo - area-clienti\api\admin-kpi-clienti.php (riga 88)
echo - area-clienti\api\mock-kpi-webapp.php (riga 16)
echo.
echo Token suggerito: test_token_locale_123456
echo.
pause
