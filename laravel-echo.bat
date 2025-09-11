@echo off
if exist "laravel-echo-server.lock" del /f /q "laravel-echo-server.lock"
npx laravel-echo-server start
exit
