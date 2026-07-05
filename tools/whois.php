<?php
require '../config.php';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    
    // Keamanan: sanitasi input sebelum masuk ke shell CLI
    $safe_target = escapeshellarg($target);
    
    // Eksekusi script python. 
    // Catatan: Ubah 'python' menjadi 'python3' jika Anda menggunakan Linux/Mac
    $command = "python ../core_scripts/whois_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        // Simpan riwayat pencarian ke database jika berhasil
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'Whois/IP Lookup', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whois & IP - SoulzLab</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Tambahan CSS khusus form dan hasil */
        .tool-container {
            background-color: var(--bg-card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .input-group input {
            flex: 1;
            padding: 12px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--accent);
        }
        .btn-scan {
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            padding: 0 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-scan:hover {
            background-color: var(--accent-hover);
        }
        .result-box {
            background-color: #0d1117;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
            font-family: 'Fira Code', monospace;
            white-space: pre-wrap;
            color: #39d353;
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php 
        $active_menu = 'whois';
        include '../includes/sidebar.php'; 
    ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Whois & IP Lookup</h1>
                <p>Kumpulkan informasi geolokasi dan ISP dari target.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Masukkan Domain atau IP (contoh: google.com)" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-magnifying-glass"></i> Scan</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <div class="result-box">
                    <?php 
                    if (isset($result_data['status']) && $result_data['status'] === 'success') {
                        echo "Target        : " . htmlspecialchars($target) . "\n";
                        echo "Resolved IP   : " . htmlspecialchars($result_data['resolved_ip']) . "\n";
                        echo "Country       : " . htmlspecialchars($result_data['country']) . "\n";
                        echo "City          : " . htmlspecialchars($result_data['city']) . "\n";
                        echo "ISP           : " . htmlspecialchars($result_data['isp']) . "\n";
                        echo "Organization  : " . htmlspecialchars($result_data['org']) . "\n";
                    } else {
                        echo "Pencarian Gagal atau Domain/IP tidak ditemukan.\n";
                        if (isset($result_data['message'])) {
                            echo "Error: " . htmlspecialchars($result_data['message']);
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
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