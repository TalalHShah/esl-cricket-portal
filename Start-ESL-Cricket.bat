@echo off
title ESL Cricket Portal - Launcher
echo ============================================
echo   E-Sports League - Cricket - Starting up
echo ============================================
echo.

echo Starting Apache and MySQL (XAMPP)...
call C:\xampp\ctlscript.bat START

echo Waiting for the server to come online...
timeout /t 5 /nobreak >nul

echo Opening the site in your browser...
start "" "http://localhost/ESL-Cricket-Portal/public/"

echo.
echo Done. You can close this window, or leave it open -
echo closing it will NOT stop the server.
echo.
pause
