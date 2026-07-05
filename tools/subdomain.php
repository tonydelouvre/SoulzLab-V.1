<?php
require '../config.php';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    $safe_target = escapeshellarg($target);
    
    $command = "python ../core_scripts/subdomain_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'Subdomain Scanner', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subdomain Scanner - SoulzLab</title>
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
        .stats-badge {
            display: inline-block;
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--accent);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid var(--accent);
        }
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        .subdomain-card {
            background-color: #0d1117;
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid var(--accent);
            font-family: 'Fira Code', monospace;
            font-size: 0.85rem;
            color: #39d353;
            word-break: break-all;
        }
        .error-box {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ef4444;
            font-family: 'Fira Code', monospace;
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php 
        $active_menu = 'subdomain';
        include '../includes/sidebar.php'; 
    ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Subdomain Scanner</h1>
                <p>Ekstraksi subdomain target menggunakan Certificate Transparency logs.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Masukkan Domain Utama (contoh: google.com)" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-sitemap"></i> Enumeration</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    <div class="stats-badge">
                        <i class="fa-solid fa-check-circle"></i> Ditemukan <?php echo $result_data['total']; ?> Subdomain
                    </div>
                    <div class="results-grid">
                        <?php foreach ($result_data['subdomains'] as $sub): ?>
                            <div class="subdomain-card">
                                <i class="fa-solid fa-link" style="color: #8b949e; margin-right: 8px;"></i>
                                <?php echo htmlspecialchars($sub); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="error-box">
                        <i class="fa-solid fa-triangle-exclamation"></i> 
                        Gagal melakukan pemindaian. Pastikan koneksi internet stabil.
                        <br><br>
                        Detail: <?php echo htmlspecialchars($result_data['message'] ?? 'Unknown error'); ?>
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