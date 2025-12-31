@echo off
setlocal enabledelayedexpansion

echo ========================================
echo Setup Completo KPI - Con Creazione DB
echo ========================================
echo.
pause

REM Verifica MySQL
echo [1/4] Verifica MySQL...
"C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 --protocol=TCP -e "SELECT 1;" 2>nul
if %errorlevel% neq 0 (
    echo [ERRORE] MySQL non risponde. Avvia XAMPP Control Panel e avvia MySQL.
    pause
    exit /b 1
)
echo OK MySQL attivo

REM Crea database se non esiste
echo.
echo [2/4] Crea database finch_ai_clienti...
"C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 --protocol=TCP -e "CREATE DATABASE IF NOT EXISTS finch_ai_clienti DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %errorlevel% neq 0 (
    echo [ERRORE] Impossibile creare il database
    pause
    exit /b 1
)
echo OK Database creato/esistente

REM Verifica se esistono le tabelle base
echo.
echo [3/4] Verifica tabelle base...
"C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 --protocol=TCP finch_ai_clienti -e "SHOW TABLES LIKE 'utenti';" 2>nul | find "utenti" >nul
if %errorlevel% neq 0 (
    echo [ATTENZIONE] Tabella utenti non trovata.
    echo Devi prima eseguire lo schema base del database.
    echo.
    echo Esegui prima: database\schema.sql o database\init.php
    echo.
    pause
    exit /b 1
)
echo OK Tabelle base presenti

REM Esegui script setup KPI
echo.
echo [4/4] Setup dati test KPI...
"C:\xampp\mysql\bin\mysql.exe" -u root -h 127.0.0.1 --protocol=TCP finch_ai_clienti < "%~dp0database\setup_test_kpi_dashboard.sql" 2>nul
if %errorlevel% neq 0 (
    echo [ERRORE] Errore durante setup KPI
    pause
    exit /b 1
)

echo.
echo ========================================
echo Setup completato!
echo ========================================
echo.
echo Creati:
echo - 1 admin
echo - 5 clienti con Document Intelligence
echo - Dati utilizzo ultimi 3 mesi
echo.
echo ========================================
echo LOGIN ADMIN
echo ========================================
echo Email:    admin@finch-ai.it
echo Password: password
echo.
echo URL: http://localhost/area-clienti/login.php
echo Poi vai su Gestione Servizi
echo.
pause
