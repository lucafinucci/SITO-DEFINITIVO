@echo off
echo ========================================
echo Avvio MySQL in modalita console
echo ========================================
echo.
echo Questo ti mostra TUTTI i messaggi di MySQL
echo Lascia aperta questa finestra!
echo.
echo Premi Ctrl+C per fermare MySQL
echo ========================================
echo.

cd C:\xampp\mysql\bin

echo Avvio mysqld...
echo.

mysqld.exe --console --bind-address=127.0.0.1

pause
