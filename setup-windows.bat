@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

:: =========================================
echo ========================================
echo   Amdr Laravel Project Setup (Windows)
echo ========================================

:: -----------------------------
:: 判斷環境參數，默認 dev
set "ENVIRONMENT=%1"
if "%ENVIRONMENT%"=="" set "ENVIRONMENT=dev"
echo Using environment: %ENVIRONMENT%
echo.

:: -----------------------------
:: 檢查 Docker
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Docker is not installed or not in PATH
    echo Please install Docker Desktop from https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

:: 檢查 Docker Compose
docker-compose --version >nul 2>&1
if %errorlevel% equ 0 (
    set "COMPOSE_CMD=docker-compose"
) else (
    docker compose version >nul 2>&1
    if %errorlevel% equ 0 (
        set "COMPOSE_CMD=docker compose"
    ) else (
        echo ERROR: Docker Compose is not available
        echo Please make sure Docker Desktop is running
        pause
        exit /b 1
    )
)

echo Found Docker Compose: %COMPOSE_CMD%
echo.

:: -----------------------------
:: 1. 建立 .env
echo [1/6] Creating environment file...
if not exist ".env" (
    if exist ".env.example" (
        copy .env.example .env >nul
        echo   Environment file created
    ) else (
        echo   ERROR: .env.example not found
        pause
        exit /b 1
    )
) else (
    echo   Environment file already exists
)
echo.

:: -----------------------------
:: 2. Build Docker containers
echo [2/6] Building Docker containers...
echo This may take a while on first run...
%COMPOSE_CMD% build --no-cache
if %errorlevel% neq 0 (
    echo   ERROR: Failed to build containers
    pause
    exit /b 1
)
echo   Containers built successfully
echo.

:: -----------------------------
:: 3. Start database/cache
echo [3/6] Starting database and cache services...
%COMPOSE_CMD% up -d postgres  redis rabbitmq
if %errorlevel% neq 0 (
    echo   ERROR: Failed to start services
    pause
    exit /b 1
)
echo   Database and cache services started
echo.

:: -----------------------------
:: 4. Wait for postgres
echo [4/6] Waiting for PostgreSQL to be ready...
echo Please wait 40 seconds...
timeout /t 40 /nobreak >nul
echo   PostgreSQL should be ready now
echo.

:: -----------------------------
:: 5. Install PHP dependencies
echo [5/6] Installing PHP dependencies...
if "%ENVIRONMENT%"=="dev" (
    echo Installing dev dependencies...
    %COMPOSE_CMD% run --rm app composer install --no-interaction
) else (
    echo Installing prod dependencies...
    %COMPOSE_CMD% run --rm app composer install --no-dev --optimize-autoloader --no-interaction
)
if %errorlevel% neq 0 (
    echo   ERROR: Failed to install PHP dependencies
    pause
    exit /b 1
)

echo Generating application key...
%COMPOSE_CMD% run --rm app php artisan key:generate --no-interaction
if %errorlevel% neq 0 (
    echo   ERROR: Failed to generate app key
    pause
    exit /b 1
)

echo Caching configuration...
%COMPOSE_CMD% run --rm app php artisan config:cache
%COMPOSE_CMD% run --rm app php artisan route:cache
%COMPOSE_CMD% run --rm app php artisan view:cache
echo   Laravel setup completed
echo.

:: -----------------------------
:: 6. Install Node dependencies & start services
echo [6/6] Installing Node.js dependencies and starting all services...
%COMPOSE_CMD% run --rm node npm install
if %errorlevel% neq 0 (
    echo   ERROR: Failed to install Node dependencies
    pause
    exit /b 1
)

echo Starting all services...
%COMPOSE_CMD% up -d
if %errorlevel% neq 0 (
    echo   ERROR: Failed to start all services
    pause
    exit /b 1
)
echo   All services started
echo.

:: -----------------------------
echo ========================================
echo   🎉 Setup Complete!
echo ========================================
echo.
echo Your application is now running on:
echo   Main App: http://localhost:8081
echo   PostgreSQL: localhost:3307 (root/root)
echo   Redis: localhost:6380 (password: qagv)
echo   RabbitMQ Management: http://localhost:15673 (guest/guest)
echo   Laravel Echo Server: http://localhost:6002
echo.
echo Useful commands:
echo   %COMPOSE_CMD% logs -f          (View live logs)
echo   %COMPOSE_CMD% ps               (Check service status)
echo   %COMPOSE_CMD% down             (Stop all services)
echo   %COMPOSE_CMD% exec app bash    (Enter app container)
echo.
echo Next steps:
echo   1. Visit http://localhost:8081 to see your application
echo   2. Run migrations if needed: %COMPOSE_CMD% exec app php artisan migrate
echo   3. Create sample data: %COMPOSE_CMD% exec app php artisan db:seed
echo.
pause


@REM docker-compose up -d app nginx
@REM docker-compose up -d
@REM docker-compose down -v  

@REM docker compose exec app bash
@REM tail -f storage/logs/laravel.log
