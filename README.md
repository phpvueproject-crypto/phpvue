docker run --rm -v D:\work\phpvue\Amdr:/backup --network host -e PGPASSWORD=itriacs postgres:13 pg_restore -h 127.0.0.1 -U postgres -d CDMO -v /backup/cdmo.dump



docker compose up -d

composer install

# 會把套件安裝到 
npm install --save-dev laravel-echo-server --legacy-peer-deps

npx laravel-echo-server start

php artisan serve --port=8081


D:\work\phpvue\Amdr>nvm -v
1.1.12
nvm install 12.22.12

nvm use 12.22.12


常見命令
•	取得相依套件：
composer install
npm install
•	產生 app key：
php artisan key:generate
•	建資料庫遷移與 seeder：
php artisan migrate
php artisan db:seed
# 或一次完成（視情況）
php artisan migrate --seed
•	啟動本地開發伺服器與資產編譯：
php artisan serve       # 後端 server（127.0.0.1:8000）
npm run dev             # 前端即時編譯 (Vite or Mix)
•	常用快取/最佳化：
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
注意：config:cache 與 route:cache 在開發階段可能造成你看不到 .env 或 route 的即時變更，請在部署完成後使用。
•	建立 storage 的公開連結（若需要）
php artisan storage:link
•	重新產生 autoload（若手動新增 class）：
composer dump-autoload
