import sys
import json
import urllib.request
import urllib.parse

def search_cve(keyword):
    # Menggunakan NVD API v2 dari NIST
    encoded_kw = urllib.parse.quote(keyword)
    # Dibatasi 5 hasil terbaru agar proses cepat tanpa API Key
    url = f"https://services.nvd.nist.gov/rest/json/cves/2.0?keywordSearch={encoded_kw}&resultsPerPage=5"
    
    try:
        # Timeout diset sedikit lama (15 detik) karena API publik NIST terkadang antre
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        response = urllib.request.urlopen(req, timeout=15)
        data = json.loads(response.read().decode('utf-8'))
        
        results = []
        for vuln in data.get('vulnerabilities', []):
            cve = vuln.get('cve', {})
            cve_id = cve.get('id', 'Unknown')
            
            # Mengambil deskripsi berbahasa Inggris
            descriptions = cve.get('descriptions', [])
            desc = next((d['value'] for d in descriptions if d['lang'] == 'en'), 'Tidak ada deskripsi.')
            
            # Mengambil Skor CVSS (V3.1, V3.0, atau V2)
            metrics = cve.get('metrics', {})
            cvss = "N/A"
            severity = "UNKNOWN"
            
            if 'cvssMetricV31' in metrics:
                cvss = metrics['cvssMetricV31'][0]['cvssData']['baseScore']
                severity = metrics['cvssMetricV31'][0]['cvssData']['baseSeverity']
            elif 'cvssMetricV30' in metrics:
                cvss = metrics['cvssMetricV30'][0]['cvssData']['baseScore']
                severity = metrics['cvssMetricV30'][0]['cvssData']['baseSeverity']
            elif 'cvssMetricV2' in metrics:
                cvss = metrics['cvssMetricV2'][0]['cvssData']['baseScore']
                severity = metrics['cvssMetricV2'][0]['baseSeverity']
                
            results.append({
                "id": cve_id,
                "description": desc,
                "cvss": cvss,
                "severity": severity
            })
            
        return {"status": "success", "keyword": keyword, "total": len(results), "data": results}
        
    except urllib.error.HTTPError as e:
        return {"status": "fail", "message": f"API Error: {e.code} (Sistem NVD mungkin sedang membatasi akses publik. Coba beberapa saat lagi)."}
    except Exception as e:
        return {"status": "fail", "message": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Keyword pencarian tidak diberikan"}))
        sys.exit(1)
    
    keyword_input = sys.argv[1]
    result = search_cve(keyword_input)
    print(json.dumps(result))