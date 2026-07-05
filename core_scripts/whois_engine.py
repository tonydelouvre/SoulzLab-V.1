import sys
import json
import urllib.request
import socket

def get_target_info(target):
    try:
        # Resolve domain ke IP
        ip = socket.gethostbyname(target)
        
        # Ambil data geolocation
        url = f"http://ip-api.com/json/{ip}"
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        
        with urllib.request.urlopen(req) as response:
            data = json.loads(response.read().decode())
            data['resolved_ip'] = ip
            return data
            
    except Exception as e:
        return {"status": "fail", "message": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Target tidak diberikan"}))
        sys.exit(1)
    
    target_input = sys.argv[1]
    result = get_target_info(target_input)
    
    # Output berupa JSON agar mudah diparsing oleh PHP
    print(json.dumps(result))