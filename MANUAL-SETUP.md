# Amdr Laravel Project - 手動安裝指令

## 1. 檢查 Docker 是否可用
docker --version
docker compose version
# 或
docker-compose --version

## 2. 創建環境檔案
copy .env.example .env

## 3. 建置 Docker 容器
docker compose build
# 或
docker-compose build

## 4. 啟動資料庫服務
docker compose up -d mysql redis rabbitmq
# 或
docker-compose up -d mysql redis rabbitmq

## 5. 等待 30 秒讓 MySQL 啟動

## 6. 安裝 PHP 依賴
docker compose run --rm app composer install
# 或
docker-compose run --rm app composer install

## 7. 生成 Laravel 密鑰
docker compose run --rm app php artisan key:generate
# 或
docker-compose run --rm app php artisan key:generate

## 8. 安裝 Node.js 依賴
docker compose run --rm node npm install
# 或
docker-compose run --rm node npm install

## 9. 啟動所有服務
docker compose up -d
# 或
docker-compose up -d

## 10. 檢查服務狀態
docker compose ps
# 或
docker-compose ps

## 11. 訪問應用程式
# 瀏覽器開啟: http://localhost:8081

## 常用指令
docker compose logs -f           # 查看日誌
docker compose down             # 停止服務
docker compose exec app bash   # 進入容器