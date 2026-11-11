@echo off
rem Menjalankan Expense Tracker di Windows.
rem   serve.bat         menjalankan server di http://localhost:8000
rem   serve.bat test    menjalankan tes backend
rem Bila php.ini belum mengaktifkan pdo_sqlite dan mbstring, keduanya dimuat lewat opsi baris perintah.
cd /d "%~dp0"

set "PHPEXE="
for /f "delims=" %%i in ('where php 2^>nul') do (
    if not defined PHPEXE set "PHPEXE=%%i"
)
if not defined PHPEXE goto :nophp

"%PHPEXE%" -r "exit(extension_loaded('pdo_sqlite') && extension_loaded('mbstring') ? 0 : 1);"
if not errorlevel 1 goto :plain

for %%i in ("%PHPEXE%") do set "PHPDIR=%%~dpi"
if not exist "%PHPDIR%ext\php_pdo_sqlite.dll" goto :nodll
if not exist "%PHPDIR%ext\php_mbstring.dll" goto :nodll
echo php.ini belum mengaktifkan pdo_sqlite dan mbstring. Keduanya dimuat lewat opsi baris perintah.
if /i "%~1"=="test" goto :testext
echo Membuka http://localhost:8000  (tekan Ctrl+C untuk berhenti)
"%PHPEXE%" -d "extension_dir=%PHPDIR%ext" -d extension=pdo_sqlite -d extension=mbstring -S localhost:8000 router.php
exit /b %errorlevel%

:testext
"%PHPEXE%" -d "extension_dir=%PHPDIR%ext" -d extension=pdo_sqlite -d extension=mbstring tests\run.php
exit /b %errorlevel%

:plain
if /i "%~1"=="test" goto :testplain
echo Membuka http://localhost:8000  (tekan Ctrl+C untuk berhenti)
"%PHPEXE%" -S localhost:8000 router.php
exit /b %errorlevel%

:testplain
"%PHPEXE%" tests\run.php
exit /b %errorlevel%

:nophp
echo PHP tidak ditemukan. Pastikan folder PHP ada di PATH.
exit /b 1

:nodll
echo php_pdo_sqlite.dll atau php_mbstring.dll tidak ada di "%PHPDIR%ext".
echo Pakai PHP versi zip dari windows.php.net, atau aktifkan ekstensi di php.ini.
exit /b 1
