@echo off

echo This script will open your browser at localhost:3434 and then try to run in-built php test server at the same address
echo
pause
start http://localhost:3434
php -S localhost:3434
pause
exit