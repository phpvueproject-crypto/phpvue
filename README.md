docker run --rm -v D:\work\phpvue\Amdr:/backup --network host -e PGPASSWORD=itriacs postgres:13 pg_restore -h 127.0.0.1 -U postgres -d CDMO -v /backup/cdmo.dump



docker compose up -d

composer install

# 會把套件安裝到 node_modules 並更新 package-lock（若你想要 local）
npm install --save-dev laravel-echo-server --legacy-peer-deps

npx laravel-echo-server start

php artisan serve --port=8081


D:\work\phpvue\Amdr>nvm -v
1.1.12
nvm install 12.22.12

nvm use 12.22.12
