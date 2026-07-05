<?php
require '../config.php';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    $safe_target = escapeshellarg($target);
    
    $command = "python ../core_scripts/port_scanner_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'Port Scanner', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Port Scanner - SoulzLab</title>
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
        .scan-info {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
        }
        .scan-info span {
            color: var(--accent);
            font-weight: bold;
        }
        .ports-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .port-badge {
            background-color: rgba(16, 185, 129, 0.1);
            color: #39d353;
            border: 1px solid var(--accent);
            padding: 15px 25px;
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
            font-size: 1.2rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
        }
        .port-badge i {
            font-size: 1rem;
        }
        .no-ports {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ef4444;
            font-family: 'Fira Code', monospace;
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php 
        $active_menu = 'port_scanner';
        include '../includes/sidebar.php'; 
    ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Port Scanner</h1>
                <p>Identifikasi layanan dan celah masuk yang terbuka pada target.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Masukkan Target IP atau Domain (contoh: 127.0.0.1)" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-radar"></i> Scan Ports</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    
                    <div class="scan-info">
                        Target IP: <span><?php echo htmlspecialchars($result_data['ip']); ?></span> | 
                        Memindai <span><?php echo $result_data['scanned_count']; ?></span> Port Umum Teratas.
                    </div>

                    <div class="ports-container">
                        <?php if (count($result_data['open_ports']) > 0): ?>
                            <?php foreach ($result_data['open_ports'] as $port): ?>
                                <div class="port-badge">
                                    <i class="fa-solid fa-door-open"></i> <?php echo $port; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-ports">
                                <i class="fa-solid fa-shield-virus"></i> Tidak ada port umum yang terbuka dari daftar pemindaian. Target kemungkinan dilindungi firewall secara ketat.
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="no-ports">
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