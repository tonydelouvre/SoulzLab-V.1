import sys
import json
import hashlib
import os

def crack_hash(target_hash, algo, wordlist_path):
    target_hash = target_hash.lower()
    attempts = 0
    
    if not os.path.exists(wordlist_path):
        return {"status": "error", "message": "File wordlist tidak ditemukan."}

    try:
        with open(wordlist_path, 'r', encoding='utf-8', errors='ignore') as f:
            for line in f:
                word = line.strip()
                if not word:
                    continue
                    
                attempts += 1
                
                # Proses hashing berdasarkan algoritma
                if algo == 'md5':
                    hashed = hashlib.md5(word.encode()).hexdigest()
                elif algo == 'sha1':
                    hashed = hashlib.sha1(word.encode()).hexdigest()
                elif algo == 'sha256':
                    hashed = hashlib.sha256(word.encode()).hexdigest()
                else:
                    return {"status": "error", "message": "Algoritma tidak didukung."}
                    
                # Jika cocok, hentikan pencarian!
                if hashed == target_hash:
                    return {
                        "status": "success",
                        "found": True,
                        "word": word,
                        "hash": target_hash,
                        "attempts": attempts
                    }
        
        # Jika loop selesai tapi tidak ada yang cocok
        return {
            "status": "success",
            "found": False,
            "hash": target_hash,
            "attempts": attempts
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 4:
        print(json.dumps({"status": "error", "message": "Parameter tidak lengkap. Butuh: <hash> <algo> <wordlist_path>"}))
        sys.exit(1)
    
    target = sys.argv[1]
    algorithm = sys.argv[2]
    wordlist = sys.argv[3]
    
    result = crack_hash(target, algorithm, wordlist)
    print(json.dumps(result))