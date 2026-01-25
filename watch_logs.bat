@echo off
cd "C:\xampp\htdocs\Laravel\eltalardemartinez"
echo Monitoreando logs de Laravel...
echo Presiona Ctrl+C para detener
echo.
powershell -Command "Get-Content storage\logs\laravel.log -Wait -Tail 50"
