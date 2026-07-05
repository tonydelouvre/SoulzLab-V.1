<?php
require '../config.php';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    $safe_target = escapeshellarg($target);
    
    $command = "python ../core_scripts/header_analyzer_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'Header Analyzer', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTTP Header Analyzer - SoulzLab</title>
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
        
        /* Grid Layout for Headers */
        .header-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .header-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .panel {
            background-color: #0d1117;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
        }
        .panel h3 {
            color: var(--accent);
            margin-bottom: 15px;
            font-size: 1.1rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }
        
        /* Raw Headers Table */
        .raw-table {
            width: 100%;
            border-collapse: collapse;
        }
        .raw-table td {
            padding: 8px 0;
            border-bottom: 1px dashed var(--border-color);
            font-family: 'Fira Code', monospace;
            font-size: 0.85rem;
            word-break: break-all;
        }
        .raw-table td:first-child {
            color: #8b949e;
            width: 40%;
            font-weight: bold;
        }
        .raw-table td:last-child {
            color: #c9d1d9;
        }

        /* Security Check Items */
        .sec-item {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.02);
            border-left: 4px solid var(--border-color);
        }
        .sec-item.present {
            border-left-color: #39d353;
        }
        .sec-item.missing {
            border-left-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.05);
        }
        .sec-name {
            font-family: 'Fira Code', monospace;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        .sec-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .status-badge {
            float: right;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .bg-success { background-color: rgba(16, 185, 129, 0.2); color: #39d353; }
        .bg-danger { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php 
        $active_menu = 'header_analyzer';
        include '../includes/sidebar.php'; 
    ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>HTTP Header Analyzer</h1>
                <p>Identifikasi miskonfigurasi server dan hilangnya Security Headers pada target.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Masukkan URL Target (contoh: https://target.com)" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-magnifying-glass-chart"></i> Analyze</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    
                    <div class="header-grid">
                        <div class="panel">
                            <h3><i class="fa-solid fa-shield-virus"></i> Security Configuration Check</h3>
                            <?php foreach ($result_data['analysis']['security_checks'] as $header_name => $check): ?>
                                <?php $isPresent = ($check['status'] === 'Present'); ?>
                                <div class="sec-item <?php echo $isPresent ? 'present' : 'missing'; ?>">
                                    <span class="status-badge <?php echo $isPresent ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $check['status']; ?>
                                    </span>
                                    <span class="sec-name"><?php echo htmlspecialchars($header_name); ?></span>
                                    <div class="sec-desc">
                                        <?php 
                                            if ($isPresent) {
                                                echo "<span style='color:#39d353;'>Terpasang: </span> " . htmlspecialchars($check['value']);
                                            } else {
                                                echo htmlspecialchars($check['message']);
                                            }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="panel">
                            <h3><i class="fa-solid fa-list-ul"></i> Raw HTTP Headers</h3>
                            <table class="raw-table">
                                <tbody>
                                    <?php foreach ($result_data['analysis']['raw_headers'] as $key => $value): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($key); ?></td>
                                            <td><?php echo htmlspecialchars($value); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="panel" style="border-left: 4px solid #ef4444; margin-top:20px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i> 
                        <span style="color:#ef4444;">Gagal menganalisis:</span> <?php echo htmlspecialchars($result_data['message'] ?? 'Unknown error'); ?>
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