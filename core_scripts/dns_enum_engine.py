import sys
import json
import urllib.request

def enumerate_dns(domain):
    # Membersihkan input jika user memasukkan http/https
    domain = domain.replace("http://", "").replace("https://", "").split("/")[0]
    
    records = {}
    # Jenis record DNS yang akan kita buru
    record_types = ['A', 'AAAA', 'MX', 'TXT', 'NS', 'CNAME', 'SOA']
    total_found = 0

    for rtype in record_types:
        url = f"https://dns.google/resolve?name={domain}&type={rtype}"
        try:
            req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'})
            response = urllib.request.urlopen(req, timeout=5)
            data = json.loads(response.read().decode('utf-8'))

            if 'Answer' in data:
                answers = []
                for ans in data['Answer']:
                    # Hapus tanda kutip ganda berlebih pada TXT record
                    clean_data = ans['data'].strip('"')
                    answers.append(clean_data)
                
                records[rtype] = answers
                total_found += len(answers)
        except Exception:
            continue

    if total_found > 0:
        return {"status": "success", "domain": domain, "records": records, "total": total_found}
    else:
        return {"status": "fail", "message": "Tidak ada record DNS yang ditemukan atau domain offline."}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"status": "error", "message": "Domain tidak diberikan"}))
        sys.exit(1)
    
    target_input = sys.argv[1]
    result = enumerate_dns(target_input)
    print(json.dumps(result))