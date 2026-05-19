@echo off
:: Check for administrative permissions
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Menjalankan sebagai Administrator...
    echo.
    echo Mengaktifkan layanan MySQL80...
    net start MySQL80
    echo.
    echo Selesai! Layanan MySQL80 telah diaktifkan.
    echo Silakan coba login kembali di browser.
) else (
    echo ========================================================
    echo ERROR: Perintah ini HARUS dijalankan sebagai Administrator!
    echo ========================================================
    echo.
    echo Cara menjalankan:
    echo 1. Klik kanan pada file 'start_mysql.bat' ini.
    echo 2. Pilih 'Run as Administrator' (Jalankan sebagai Administrator).
    echo.
)
pause
