@echo off
rem Lango: one click to run the site (XAMPP Apache + MySQL) and the app (Expo), then test on a phone.
rem Double-click this file, or run it from the repository root. Close the Expo window (Ctrl+C) to stop the app.
setlocal
cd /d "%~dp0"

echo.
echo ============================================================
echo  Lango  -  website + API (XAMPP) and the Lango App (Expo)
echo ============================================================

rem 1. The website and the API come from XAMPP: start MySQL and Apache if they are not running.
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if errorlevel 1 (
  echo [1/3] Starting MySQL...
  start "XAMPP MySQL" /MIN cmd /c "C:\xampp\mysql_start.bat"
) else (
  echo [1/3] MySQL is running.
)
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
if errorlevel 1 (
  echo [2/3] Starting Apache...
  start "XAMPP Apache" /MIN cmd /c "C:\xampp\apache_start.bat"
  timeout /t 6 /nobreak >nul
) else (
  echo [2/3] Apache is running.
)

rem 2. Show this computer's address: the phone must be on the same Wi-Fi and open http://THAT-ADDRESS/langoculturalinstitution/api/v1/ping
echo.
echo This computer's address on your network (use the IPv4 line):
ipconfig | findstr /R /C:"IPv4"
echo.
echo On the phone, first open  http://YOUR-IPV4/langoculturalinstitution/api/v1/ping  in its browser. If it shows "status":"ok",
echo open Expo Go and scan the QR code below. If not, allow Apache and Node.js through Windows Firewall (Private networks).
echo.

rem 3. The app. The QR code appears below; press "a" for an Android emulator or "w" for the browser.
echo [3/3] Starting the Lango App...
cd app
if not exist node_modules (
  echo Installing the app's packages first...
  call npm.cmd install --no-fund --no-audit
)
if not exist .env copy .env.example .env >nul
call npx.cmd expo start
