@echo off
echo ================================================
echo ACCESSO INTERATTIVO A MYSQL
echo ================================================
echo.
echo Ti sto aprendo la console MySQL...
echo Una volta dentro, copia e incolla questi comandi:
echo.
echo 1. CREATE DATABASE finch_ai_clienti;
echo 2. USE finch_ai_clienti;
echo 3. SOURCE C:/Users/oneno/Desktop/SITO/database/ESEGUI_IN_PHPMYADMIN.sql
echo 4. EXIT;
echo.
echo ================================================
echo.
pause

cd /d "C:\xampp\mysql\bin"
mysql --protocol=TCP --host=127.0.0.1 --port=3306 -u root
