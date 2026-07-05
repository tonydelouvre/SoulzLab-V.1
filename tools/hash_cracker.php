<?php
require '../config.php';
$active_menu = 'hash_cracker';

$result_data = null;
$target_hash = '';
$algo = 'md5';

// Default wordlist mini agar Anda bisa langsung tes
$default_wordlist = "admin\n123456\npassword\nroot\nadmin123\nqwerty\nsoulzlab\nhacker\nrahasia\ntest";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_hash = trim($_POST['target_hash']);
    $algo = $_POST['algo'];
    $wordlist_content = trim($_POST['wordlist']);
    
    if (!empty($target_hash) && !empty($wordlist_content)) {
        // Buat file wordlist temporary di folder tools/
        $temp_file = 'temp_wordlist_' . time() . '.txt';
        file_put_contents($temp_file, $wordlist_content);
        
        // Amankan parameter
        $safe_hash = escapeshellarg($target_hash);
        $safe_algo = escapeshellarg($algo);
        $safe_file = escapeshellarg($temp_file);
        
        // Eksekusi mesin Python
        $command = "python ../core_scripts/hash_cracker_engine.py $safe_hash $safe_algo $safe_file";
        $output = shell_exec($command);
        
        // Hapus file temporary setelah selesai agar server bersih
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        
        if ($output) {
            $result_data = json_decode($output, true);
            
            // Catat di log jika berhasil dipecahkan
            if (isset($result_data['status']) && $result_data['status'] === 'success' && $result_data['found'] === true) {
                $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
                $stmt->execute([$target_hash, 'Hash Cracked', $output]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dictionary Hash Cracker - SoulzLab</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tool-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .tool-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .card-panel {
            background-color: var(--bg-card);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
        }
        .form-group textarea {
            height: 200px;
            resize: vertical;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
        }
        .btn-submit {
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            font-family: 'Inter', sans-serif;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: var(--accent-hover);
        }

        /* Result Panel */
        .result-panel {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .status-box {
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }
        .status-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
        }
        .status-fail {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
        }
        .icon-huge {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        .status-success .icon-huge { color: #10b981; }
        .status-fail .icon-huge { color: #ef4444; }
        
        .cracked-word {
            font-family: 'Fira Code', monospace;
            font-size: 2rem;
            color: #39d353;
            font-weight: 800;
            margin: 15px 0;
            background-color: #0d1117;
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid #10b981;
        }
        .stats-text {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-family: 'Fira Code', monospace;
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Dictionary Hash Cracker</h1>
                <p>Pecahkan hash MD5, SHA-1, dan SHA-256 menggunakan serangan kamus (Dictionary Attack).</p>
            </div>
        </header>

        <section class="tool-grid">
            <div class="card-panel">
                <h3 style="color: var(--accent); margin-bottom: 20px; font-size: 1.2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                    <i class="fa-solid fa-hammer"></i> Attack Configuration
                </h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Target Hash</label>
                        <input type="text" name="target_hash" placeholder="Masukkan hash..." required value="<?php echo empty($target_hash) ? 'a3809930fca56bc4bba46c5bb5d2cf5f' : htmlspecialchars($target_hash); ?>">
                    </div>
                    <div class="form-group">
                        <label>Algoritma</label>
                        <select name="algo">
                            <option value="md5" <?php echo ($algo == 'md5') ? 'selected' : ''; ?>>MD5</option>
                            <option value="sha1" <?php echo ($algo == 'sha1') ? 'selected' : ''; ?>>SHA-1</option>
                            <option value="sha256" <?php echo ($algo == 'sha256') ? 'selected' : ''; ?>>SHA-256</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Wordlist (Satu kata per baris)</label>
                        <textarea name="wordlist" required><?php echo isset($_POST['wordlist']) ? htmlspecialchars($_POST['wordlist']) : $default_wordlist; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit"><i class="fa-solid fa-fire"></i> Mulai Serangan</button>
                </form>
            </div>

            <div class="result-panel">
                <?php if ($result_data): ?>
                    <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                        
                        <?php if ($result_data['found']): ?>
                            <div class="status-box status-success">
                                <div class="icon-huge"><i class="fa-solid fa-unlock"></i></div>
                                <h2>HASH CRACKED!</h2>
                                <p style="color: var(--text-muted); margin-top:10px;">Plaintext berhasil ditemukan.</p>
                                
                                <div class="cracked-word">
                                    <?php echo htmlspecialchars($result_data['word']); ?>
                                </div>
                                
                                <div class="stats-text">
                                    Mengeksekusi <strong><?php echo $result_data['attempts']; ?></strong> tebakan.
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="status-box status-fail">
                                <div class="icon-huge"><i class="fa-solid fa-lock"></i></div>
                                <h2 style="color: #ef4444;">GAGAL DIPECAHKAN</h2>
                                <p style="color: var(--text-muted); margin-top:10px; margin-bottom: 15px;">Kata sandi tidak ada di dalam wordlist Anda.</p>
                                
                                <div class="stats-text">
                                    Telah mencoba <strong><?php echo $result_data['attempts']; ?></strong> kombinasi kata tanpa hasil.
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="card-panel" style="border-color: #ef4444;">
                            <h3 style="color: #ef4444;"><i class="fa-solid fa-circle-xmark"></i> Error</h3>
                            <p style="color: var(--text-muted); font-family: 'Fira Code', monospace;">
                                <?php echo htmlspecialchars($result_data['message'] ?? 'Unknown error occurred.'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card-panel" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-user-secret" style="font-size: 4rem; margin-bottom: 20px; color: #30363d;"></i>
                        <p>Menunggu target hash...</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php include '../includes/footer.php'; ?>
    </main>

    <script>
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');

        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
</body>
</html>