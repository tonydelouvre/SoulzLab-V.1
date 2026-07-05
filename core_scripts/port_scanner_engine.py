import sys
import json
import socket
import concurrent.futures

COMMON_PORTS = [21, 22, 23, 25, 53, 80, 110, 111, 135, 139, 143, 443, 445, 993, 995, 1723, 3306, 3389, 5900, 8080]

def scan_port(ip, port):
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(1.0)
        result = sock.connect_ex((ip, port))
        sock.close()
        return port, result == 0
    except:
        return port, False

def start_scan(target):
    open_ports = []
    try:
        target_ip = socket.gethostbyname(target)
    except socket.gaierror:
        return {"status": "fail", "message": "Host tidak dapat di-resolve (Domain tidak valid atau offline)"}

    with concurrent.futures.ThreadPoolExecutor(max_workers=20) as executor:
        futures = [executor.submit(scan_port, target_ip, port) for port in COMMON_PORTS]
        for future in concurrent.futures.as_completed(futures):
            port, is_open = future.result()
            if is_open:
                open_ports.append(port)
    
    open_ports.sort()
    return {
        "status": "success",
        "target": target,
        "ip": target_ip,
        "scanned_count": len(COMMON_PORTS),
        "open_ports": open_ports
    }

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Target tidak diberikan"}))
        sys.exit(1)
    
    target_input = sys.argv[1]
    result = start_scan(target_input)
    print(json.dumps(result))