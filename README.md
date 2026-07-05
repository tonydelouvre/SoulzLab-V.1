# 💀 SoulzLab - Advanced Penetration Testing Toolkit (V1)

![SoulzLab Version](https://img.shields.io/badge/Version-1.0-brightgreen.svg)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)
![Python Version](https://img.shields.io/badge/Python-3.8%2B-yellow.svg)
![Database](https://img.shields.io/badge/Database-MySQL-orange.svg)
![License](https://img.shields.io/badge/License-MIT-purple.svg)

**SoulzLab** adalah platform *Command Center* keamanan siber berbasis web yang dirancang khusus untuk para *Penetration Tester*, *Ethical Hacker*, dan *Security Researcher*. Sistem ini menggabungkan antarmuka UI/UX modern (Dark Mode) menggunakan PHP murni dengan kekuatan pemrosesan *backend* (API & Multithreading) menggunakan Python.

---

## 🚀 Fitur Utama (15 Tools Terintegrasi)

SoulzLab V1 dilengkapi dengan 15 modul eksploitasi dan pengintaian yang terbagi dalam 5 fase utama:

### 👁️ Reconnaissance (Pengintaian)
1. **Whois & IP Lookup:** Ekstraksi data kepemilikan domain dan geolokasi IP target.
2. **Subdomain Scanner:** Enumerasi subdomain siluman menggunakan log *Certificate Transparency* (OSINT).
3. **DNS Record Enumerator:** Pemetaan infrastruktur melalui ekstraksi record A, AAAA, MX, NS, dan TXT.
4. **MAC Vendor Lookup:** Identifikasi pabrikan perangkat keras melalui MAC Address (OUI).

### 📡 Scanning & Enumeration
5. **Port Scanner:** Pemindai port *multi-threaded* super cepat untuk mencari celah masuk layanan.
6. **Directory Bruteforcer (DirBuster):** Pencari *endpoint*, panel admin, dan file `.env` yang disembunyikan.

### 🐛 Vulnerability & Exploit
7. **HTTP Header Analyzer:** Audit miskonfigurasi server (Clickjacking, HSTS, XSS Protection).
8. **SQLi Tester:** Pendeteksi kerentanan SQL Injection otomatis (Error-Based) pada parameter URL.
9. **XSS Payload Generator:** Gudang *payload* dinamis untuk *WAF Bypass* dan *Blind XSS*.
10. **CVE Intel Searcher:** Tarikan data intelijen kerentanan *real-time* dari *National Vulnerability Database* (NIST).

### 🔑 Cryptography
11. **Hash Analyzer & Generator:** Alat identifikasi jenis algoritma *hash* misterius dan pembuat *hash* massal.
12. **Dictionary Hash Cracker:** Pemecah *password* (MD5, SHA-1, SHA-256) menggunakan serangan kamus (*Wordlist*).

### 🧰 Utilities & Management
13. **Data Encoder & Decoder:** Manipulasi *payload* eksploitasi (Base64, URL, Hex).
14. **Payload & Script Manager:** *Cheat sheet digital* untuk menyimpan *script Reverse Shell* atau LPE favorit Anda.
15. **Target & Report Tracker:** Sistem *logging* sentral yang otomatis mencatat seluruh hasil *scan* dan dapat diekspor menjadi laporan PDF.

---

## 🛠️ Arsitektur & Teknologi

* **Frontend:** HTML5, CSS3 (Custom Variables, Flexbox/Grid), Vanilla JavaScript, Font-Awesome 6.
* **Backend UI & Controller:** PHP (PDO, Shell Exec).
* **Core Engine:** Python 3 (Native libraries: `socket`, `urllib`, `concurrent.futures`, `hashlib` — **Zero external PIP dependencies!**).
* **Database:** MySQL / MariaDB.

---

## ⚙️ Panduan Instalasi (Localhost)

1. **Clone Repository**
   Buka terminal di dalam direktori server lokal Anda (misal: `htdocs` untuk XAMPP atau `/var/www/html` untuk Linux):
   ```bash
   git clone [https://github.com/USERNAME/SoulzLab.git](https://github.com/USERNAME/SoulzLab.git)
   cd SoulzLab
   ```

2. **Setup Database**
   * Buka `phpMyAdmin` atau *console* MySQL Anda.
   * Buat database baru bernama `db_soulzlab`.
   * Jalankan kueri SQL berikut untuk membuat tabel yang dibutuhkan:
     ```sql
     CREATE TABLE targets (
         id INT AUTO_INCREMENT PRIMARY KEY,
         target_name VARCHAR(255) NOT NULL,
         scan_type VARCHAR(50) NOT NULL,
         scan_result JSON,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );

     CREATE TABLE payloads (
         id INT AUTO_INCREMENT PRIMARY KEY,
         title VARCHAR(255) NOT NULL,
         category VARCHAR(100) NOT NULL,
         content TEXT NOT NULL,
         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
     );
     ```

3. **Konfigurasi Sistem**
   Buka file `config.php` di *root folder* dan sesuaikan dengan *environment* Anda:
   ```php
   $base_url = 'http://localhost/SoulzLab/'; // Sesuaikan dengan nama folder clone Anda
   $host = '127.0.0.1';
   $db   = 'db_soulzlab';
   $user = 'root'; // User database
   $pass = '';     // Password database
   ```

4. **Jalankan SoulzLab**
   Buka browser dan akses URL: `http://localhost/SoulzLab/`. Boom! *Command Center* Anda siap digunakan.

---

## ⚠️ Disclaimer (Peringatan Penting)
*Toolkit* ini dirancang **HANYA untuk tujuan edukasi dan pengujian keamanan yang legal** (Sistem/Jaringan yang Anda miliki sendiri atau memiliki izin tertulis). Segala bentuk penyalahgunaan *tools* ini untuk merusak, menyerang, atau mengeksploitasi sistem pihak lain adalah tanggung jawab penuh pengguna akhir. **Think Before You Type.**

---
*Developed with 💀 by Tony De Louvre - 2026*
