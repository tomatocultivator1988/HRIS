@echo off
REM Update Employee Deductions with Correct Philippine Rates
REM Double-click this file to run

echo ========================================
echo Update Employee Deductions
echo ========================================
echo.
echo WARNING: This will modify your database!
echo.
set /p confirm="Are you sure you want to continue? (Y/N): "

if /i "%confirm%" NEQ "Y" (
    echo.
    echo Operation cancelled.
    echo.
    pause
    exit
)

echo.
echo Running update...
echo.

C:\xampp\php\php.exe "%~dp0update_correct_deductions.php"

echo.
echo ========================================
echo Press any key to exit...
pause > nul
