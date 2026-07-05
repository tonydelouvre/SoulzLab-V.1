import sys
import json
import urllib.request
import urllib.error
import concurrent.futures

WORDLIST = [
    "admin", "login", "dashboard", "wp-admin", "wp-login.php",
    "robots.txt", "sitemap.xml", ".git", ".env", "api",
    "test", "backup", "config", "phpinfo.php", "setup.php",
    "css", "js", "images", "assets", "includes", "db", "sql"
]

def check_url(base_url, path):
    target_url = f"{base_url.rstrip('/')}/{path}"
    try:
        req = urllib.request.Request(target_url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
        response = urllib.request.urlopen(req, timeout=5)
        return path, response.getcode(), target_url
    except urllib.error.HTTPError as e:
        if e.code in [401, 403]:
            return path, e.code, target_url
        return path, None, target_url
    except Exception:
        return path, None, target_url

def start_bruteforce(target):
    if not target.startswith("http://") and not target.startswith("https://"):
        target = "http://" + target

    found_dirs = []
    
    with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
        futures = [executor.submit(check_url, target, word) for word in WORDLIST]
        for future in concurrent.futures.as_completed(futures):
            path, status, full_url = future.result()
            if status:
                found_dirs.append({
                    "path": path, 
                    "status": status,
                    "url": full_url
                })
                
    found_dirs.sort(key=lambda x: x['status'])
    
    return {
        "status": "success",
        "target": target,
        "total_scanned": len(WORDLIST),
        "found": found_dirs
    }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Target URL tidak diberikan"}))
        sys.exit(1)
    
    target_input = sys.argv[1]
    result = start_bruteforce(target_input)
    print(json.dumps(result))