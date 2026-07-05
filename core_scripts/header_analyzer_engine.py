import sys
import json
import urllib.request
import urllib.error

def analyze_headers(url):
    if not url.startswith("http://") and not url.startswith("https://"):
        url = "http://" + url
    
    try:
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        response = urllib.request.urlopen(req, timeout=10)
        
        # Ambil raw headers
        headers = dict(response.info())
        
        # Daftar Security Headers yang krusial untuk dicek
        security_headers = {
            "Strict-Transport-Security": "Missing (Rentan terhadap serangan Man-in-the-Middle)",
            "X-Frame-Options": "Missing (Rentan terhadap serangan Clickjacking)",
            "X-XSS-Protection": "Missing (Kurangnya perlindungan XSS bawaan browser)",
            "X-Content-Type-Options": "Missing (Rentan terhadap eksploitasi MIME-sniffing)",
            "Content-Security-Policy": "Missing (Sangat rentan terhadap XSS dan injeksi data)"
        }
        
        analysis = {
            "raw_headers": headers,
            "security_checks": {}
        }
        
        # Evaluasi header target
        for sh, msg in security_headers.items():
            found = False
            for h_key in headers.keys():
                if h_key.lower() == sh.lower():
                    analysis["security_checks"][sh] = {"status": "Present", "value": headers[h_key]}
                    found = True
                    break
            if not found:
                analysis["security_checks"][sh] = {"status": "Missing", "message": msg}
                
        return {"status": "success", "target": url, "analysis": analysis}
        
    except Exception as e:
        return {"status": "fail", "message": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Target tidak diberikan"}))
        sys.exit(1)
    
    target_input = sys.argv[1]
    result = analyze_headers(target_input)
    print(json.dumps(result))