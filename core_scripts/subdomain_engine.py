import sys
import json
import urllib.request
import urllib.parse

def get_subdomains(domain):
    try:
        url = f"https://crt.sh/?q=%25.{domain}&output=json"
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        
        with urllib.request.urlopen(req, timeout=15) as response:
            data = json.loads(response.read().decode())
            
            subdomains = set()
            for entry in data:
                name_value = entry.get('name_value', '')
                for name in name_value.split('\n'):
                    if name.endswith(domain) and not name.startswith('*'):
                        subdomains.add(name)
                        
            return {
                "status": "success", 
                "domain": domain, 
                "total": len(subdomains),
                "subdomains": list(subdomains)
            }
    except Exception as e:
        return {"status": "fail", "message": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Target tidak diberikan"}))
        sys.exit(1)
    
    target_input = sys.argv[1]
    result = get_subdomains(target_input)
    print(json.dumps(result))