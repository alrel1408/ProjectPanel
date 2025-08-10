# Deployment Guide - AlrelShop VPS Panel

Panduan lengkap untuk deploy project panel VPS ke GitHub dan mengintegrasikannya dengan panel.alrelshop.my.id

## 📋 Pre-Deployment Checklist

### 1. Persiapan Repository GitHub
```bash
# Di local machine atau server
cd /www/wwwroot/vps-installer
git init
git add .
git commit -m "Initial commit: AlrelShop VPS Panel Installer"
git branch -M main
git remote add origin https://github.com/alrel1408/ProjectPanel.git
git push -u origin main
```

### 2. Struktur Files yang akan di-upload
```
ProjectPanel/
├── installer.sh              # Main installer untuk VPS
├── api-handler.sh            # API handler untuk VPS commands
├── service-monitor.sh        # Service monitoring script
├── panel/
│   ├── config/
│   │   ├── existing-database.php    # Database config untuk dbpanelnew
│   │   └── database.php             # Database config alternatif
│   ├── api/
│   │   ├── vps-server.php          # VPS API endpoints
│   │   └── server.php              # General API endpoints
│   ├── dashboard.php               # Panel dashboard
│   └── install.php                 # Panel installer
├── README.md                       # Documentation
├── DEPLOYMENT.md                   # This file
└── .gitignore                     # Git ignore rules
```

## 🚀 Deployment Steps

### Step 1: Upload ke GitHub
1. Pastikan semua file sudah ter-commit
2. Push ke repository `alrel1408/ProjectPanel`
3. Verifikasi semua file ter-upload dengan benar

### Step 2: Setup di Panel Server (panel.alrelshop.my.id)

#### A. Upload Panel Files
```bash
# Di server panel.alrelshop.my.id
cd /path/to/web/directory
wget https://github.com/alrel1408/ProjectPanel/archive/main.zip
unzip main.zip
cp -r ProjectPanel-main/panel/* ./
rm -rf ProjectPanel-main main.zip
```

#### B. Setup Database Integration
```bash
# Jalankan setup database (AMAN - tidak mengubah dbpanelnew)
php config/existing-database.php
```

#### C. Set Permissions
```bash
chmod 755 panel/
chmod 644 panel/*.php
chmod 644 panel/api/*.php
chmod 644 panel/config/*.php
```

### Step 3: Update Panel URLs
Pastikan di file-file panel sudah menggunakan URL yang benar:
- Panel URL: `https://panel.alrelshop.my.id`
- API Base URL: `https://panel.alrelshop.my.id/api/vps-server.php`

### Step 4: Test Installation Command
```bash
# Test command yang akan diberikan ke customer
wget -O installer.sh https://raw.githubusercontent.com/alrel1408/ProjectPanel/main/installer.sh && chmod +x installer.sh && ./installer.sh
```

## 🔧 Configuration

### Database Configuration
File: `panel/config/existing-database.php`
```php
private $host = 'localhost';
private $db_name = 'dbpanelnew';        // Database existing
private $username = 'dbpanelnew';       // Username existing
private $password = '';                 // Set password sesuai
```

### API Endpoints
- `POST /api/vps-server/add` - Register VPS baru
- `POST /api/vps-server/status` - Update status VPS
- `GET /api/vps-server/commands` - Get commands untuk VPS
- `POST /api/vps-server/account` - Notif account created
- `GET /api/vps-server/list` - List semua VPS

## 🔒 Security Notes

1. **API Key**: Setiap VPS memiliki unique API key
2. **Database**: Hanya menambah tabel baru, tidak mengubah existing
3. **Access Control**: API dilindungi dengan validasi key
4. **Logging**: Semua API calls ter-log

## 📊 Monitoring Features

### Real-time Status Monitoring
- CPU, Memory, Disk usage
- Service status (SSH, OpenVPN, Xray, dll)
- Active users count
- Network statistics

### Supported Services
- SSH/Dropbear (Multi port)
- OpenVPN (SSL/TCP/UDP)
- Xray (Vmess/Vless/Trojan/Shadowsocks)
- BadVPN UDP Gateway
- Nginx/HAProxy

## 🛠 Troubleshooting

### Common Issues
1. **Database Connection**: Pastikan credentials dbpanelnew benar
2. **API Access**: Cek API key dan URL endpoint
3. **File Permissions**: Pastikan PHP bisa write ke log files
4. **Service Detection**: Cek systemctl dan service names

### Debug Commands
```bash
# Test database connection
php -r "include 'config/existing-database.php'; var_dump(testExistingConnection());"

# Test API endpoint
curl -X GET "https://panel.alrelshop.my.id/api/vps-server/list"

# Check logs
tail -f /var/log/apache2/error.log
```

## 📞 Support & Contact

- **Panel**: https://panel.alrelshop.my.id
- **GitHub**: https://github.com/alrel1408/ProjectPanel
- **Telegram**: @alrelshop
- **WhatsApp**: +6282285851668

---

**Important**: Project ini dibuat khusus untuk integrasi dengan panel.alrelshop.my.id dan menggunakan database existing 'dbpanelnew' tanpa mengubah struktur yang sudah ada.
