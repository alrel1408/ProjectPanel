# AlrelShop VPS Auto Installer

Script installer otomatis untuk VPS yang terintegrasi dengan panel AlrelShop (panel.alrelshop.my.id).

## 🚀 Fitur Utama

- **Auto Installation**: Instalasi VPS otomatis dengan satu perintah
- **Panel Integration**: Tersinkron dengan panel.alrelshop.my.id 
- **Service Monitoring**: Monitoring real-time semua service VPS
- **Database Safe**: Tidak mengubah database existing 'dbpanelnew'
- **Multi Service Support**: Support SSH, OpenVPN, Xray, Shadowsocks, BadVPN, dll

## 📋 Service yang Didukung

### SSH Services
- SSH TUN (Port 22, 80, 443)
- SSH UDP (All ports)
- Dropbear (Port 109, 143, 443)
- SSH Websocket SSL (Port 443)
- SSH Websocket (Port 80)

### OpenVPN Services
- OpenVPN SSL (Port 443)
- OpenVPN TCP (Port 443, 1194)
- OpenVPN UDP (Port 2200)
- OpenVPN Websocket SSL (Port 443)

### Xray Services
- Xray Vmess TLS (Port 443)
- Xray Vmess gRPC (Port 443)
- Xray Vmess None TLS (Port 80)
- Xray Vless TLS (Port 443)
- Xray Vless gRPC (Port 443)
- Xray Vless None TLS (Port 80)
- Xray Trojan gRPC (Port 443)
- Xray Trojan WS (Port 443)
- Xray Shadowsocks WS (Port 443)
- Xray Shadowsocks gRPC (Port 443)

### Other Services
- Nginx Webserver (Port 80, 81, 443)
- HAProxy Loadbalancer (Port 80, 443)
- BadVPN UDP Gateway (Port 7100, 7200, 7300)
- Cron Service

## 🛠 Instalasi

### 1. Install di VPS Baru

```bash
wget -O installer.sh https://raw.githubusercontent.com/alrel1408/ProjectPanel/main/installer.sh && chmod +x installer.sh && ./installer.sh
```

### 2. Informasi yang Diperlukan

Saat instalasi, Anda akan diminta memasukkan:
- **Panel URL**: https://panel.alrelshop.my.id
- **API Key**: API key dari panel Anda
- **Server Name**: Nama server (contoh: VPS-SG-01)

### 3. Setelah Instalasi

Server akan otomatis:
- Terdaftar di panel Anda
- Mengirim status monitoring setiap menit
- Siap menerima perintah dari panel

## 📊 Panel Integration

### Database yang Digunakan
- **Database**: `dbpanelnew` (existing, tidak diubah)
- **Tables Baru**: Hanya menambahkan tabel VPS tanpa mengubah yang ada
  - `vps_servers`: Data server VPS
  - `vps_accounts`: Akun yang dibuat di VPS
  - `vps_api_logs`: Log API calls
  - `vps_commands`: Queue perintah untuk VPS

### API Endpoints
- `POST /api/vps-server/add`: Register server baru
- `POST /api/vps-server/status`: Update status server
- `GET /api/vps-server/commands`: Ambil perintah untuk server
- `POST /api/vps-server/account`: Notifikasi akun dibuat
- `GET /api/vps-server/list`: List semua server

## 🔧 Command yang Tersedia

### Di VPS
```bash
# Akses menu utama VPS
menu

# Kelola koneksi panel
panel-manage

# Monitor service
service-monitor status

# Restart service tertentu
service-monitor restart ssh
service-monitor restart all
```

### Di Panel
- Dashboard monitoring real-time
- Manajemen server
- Create/delete akun
- Restart services
- View logs

## 🔒 Keamanan

- API key authentication
- Encrypted communication
- Service isolation
- Log semua aktivitas

## 📁 Struktur Project

```
vps-installer/
├── installer.sh              # Main installer script
├── api-handler.sh            # API handler untuk VPS
├── service-monitor.sh        # Service monitoring script
├── panel/
│   ├── config/
│   │   ├── existing-database.php    # Konfigurasi database existing
│   │   └── database.php             # Konfigurasi database baru
│   ├── api/
│   │   ├── vps-server.php          # API untuk VPS management
│   │   └── server.php              # API umum
│   ├── dashboard.php               # Dashboard panel
│   └── install.php                 # Panel installer
└── README.md
```

## 🚨 Penting!

- **Database Safety**: Script ini TIDAK akan mengubah database existing 'dbpanelnew'
- **Service Compatibility**: Compatible dengan service VPS AlrelShop yang sudah ada
- **Auto Updates**: Script akan mengupdate status setiap menit
- **No Menu Redirect**: Setelah install tidak langsung redirect ke menu VPS

## 📞 Support

Untuk bantuan lebih lanjut:
- **Website**: panel.alrelshop.my.id
- **Telegram**: @alrelshop
- **WhatsApp**: +6282285851668

## 📝 License

© 2024 AlrelShop. All rights reserved.

---

**Note**: Script ini dibuat khusus untuk integrasi dengan panel AlrelShop dan menggunakan database existing tanpa mengubah struktur yang sudah ada.
