import sys
import json
import urllib.request
import urllib.parse
import re

def lookup_mac(mac_address):
    # Membersihkan input agar hanya menyisakan format MAC yang valid
    mac_clean = urllib.parse.quote(mac_address.strip())
    url = f"https://api.macvendors.com/{mac_clean}"
    
    try:
        # API macvendors membatasi request per detik, timeout diset ke 5 detik
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        response = urllib.request.urlopen(req, timeout=5)
        vendor = response.read().decode('utf-8')
        
        return {
            "status": "success", 
            "mac": mac_address, 
            "vendor": vendor
        }
    except urllib.error.HTTPError as e:
        if e.code == 404:
            return {"status": "fail", "message": "Vendor tidak ditemukan. Pastikan format MAC Address benar (contoh: 00:1A:2B:3C:4D:5E)."}
        elif e.code == 429:
            return {"status": "fail", "message": "Terlalu banyak request ke API. Tunggu sekitar 1-2 detik."}
        return {"status": "fail", "message": f"API Error: {e.code}"}
    except Exception as e:
        return {"status": "fail", "message": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "MAC Address tidak diberikan"}))
        sys.exit(1)
    
    mac_input = sys.argv[1]
    result = lookup_mac(mac_input)
    print(json.dumps(result))