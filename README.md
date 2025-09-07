D:\work\phpvue\Amdr\
├── 🐳 docker-compose.yml          # 主要 Docker 編排配置
├── 🐳 Dockerfile                  # 開發環境映像檔
├── 🐳 Dockerfile.production       # 生產環境映像檔
├── 🚫 .dockerignore               # Docker 忽略檔案
├── 🖥️  setup-windows.bat           # Windows 完整安裝腳本
├── 🐧 setup-linux.sh              # Linux 完整安裝腳本
├── ⚡ quick-start.bat             # Windows 快速啟動腳本
├── ⚡ quick-start.sh              # Linux 快速啟動腳本
└── 📁 docker/
    ├── 📁 nginx/
    │   └── 📄 default.conf         # Nginx 配置
    └── 📁 php/
        └── 📄 local.ini            # PHP 配置


🚀 立即開始使用
方法 1：完整安裝 (首次使用)

# Windows
setup-windows.bat

# Linux
chmod +x setup-linux.sh
./setup-linux.sh


方法 2：快速啟動 (已安裝過)
Windows
quick-start.bat

# Linux
chmod +x quick-start.sh
./quick-start.sh



docker run --rm -v D:\work\phpvue\Amdr:/backup --network host -e PGPASSWORD=itriacs postgres:13 pg_restore -h 127.0.0.1 -U postgres -d CDMO -v /backup/cdmo.dump

