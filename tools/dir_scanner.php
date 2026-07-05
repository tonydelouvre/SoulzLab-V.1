<?php
require '../config.php';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    $safe_target = escapeshellarg($target);
    
    $command = "python ../core_scripts/dir_bruteforce_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'Directory Bruteforce', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directory Bruteforcer - SoulzLab</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
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
        .scan-stats {
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
        }
        .dir-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .dir-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #0d1117;
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: 0.2s;
        }
        .dir-item:hover {
            border-color: var(--accent);
        }
        .dir-url {
            font-family: 'Fira Code', monospace;
            color: #c9d1d9;
        }
        .dir-url a {
            color: inherit;
            text-decoration: none;
        }
        .dir-url a:hover {
            color: var(--accent);
            text-decoration: underline;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        .status-200 {
            background-color: rgba(16, 185, 129, 0.2);
            color: #39d353;
            border: 1px solid #39d353;
        }
        .status-403, .status-401 {
            background-color: rgba(217, 119, 6, 0.2);
            color: #fbbf24;
            border: 1px solid #fbbf24;
        }
        .no-results {
            text-align: center;
            padding: 30px;
            color: var(--text-muted);
            font-style: italic;
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php 
        $active_menu = 'dir_scanner';
        include '../includes/sidebar.php'; 
    ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Directory Bruteforcer</h1>
                <p>Temukan panel admin, file konfigurasi, dan direktori tersembunyi.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Masukkan URL Target (contoh: target.com atau http://target.com)" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-bolt"></i> Bruteforce</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    
                    <div class="scan-stats">
                        <i class="fa-solid fa-bullseye" style="color: var(--accent);"></i> Target: <strong><?php echo htmlspecialchars($result_data['target']); ?></strong> <br>
                        <i class="fa-solid fa-list-ol" style="color: var(--accent);"></i> Total Payload Scanned: <strong><?php echo $result_data['total_scanned']; ?></strong>
                    </div>

                    <div class="dir-list">
                        <?php if (count($result_data['found']) > 0): ?>
                            <?php foreach ($result_data['found'] as $dir): ?>
                                <?php 
                                    $statusClass = 'status-200';
                                    if (in_array($dir['status'], [401, 403])) {
                                        $statusClass = 'status-403';
                                    }
                                ?>
                                <div class="dir-item">
                                    <div class="dir-url">
                                        <i class="fa-solid fa-link" style="color: #8b949e;"></i> 
                                        <a href="<?php echo htmlspecialchars($dir['url']); ?>" target="_blank">
                                            /<?php echo htmlspecialchars($dir['path']); ?>
                                        </a>
                                    </div>
                                    <div class="status-badge <?php echo $statusClass; ?>">
                                        HTTP <?php echo $dir['status']; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-results">
                                <i class="fa-solid fa-ghost" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                                Tidak ada direktori umum yang ditemukan dari wordlist dasar.
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="no-results" style="color: #ef4444;">
                        <i class="fa-solid fa-triangle-exclamation"></i> 
                        Gagal memindai: <?php echo htmlspecialchars($result_data['message'] ?? 'Unknown error'); ?>
                    </div>
                <?php endif; ?>
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