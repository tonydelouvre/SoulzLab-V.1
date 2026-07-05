<?php
require '../config.php';
$active_menu = 'mac_lookup';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    $safe_target = escapeshellarg($target);
    
    $command = "python ../core_scripts/mac_lookup_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'MAC Vendor Lookup', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAC Address Vendor Lookup - SoulzLab</title>
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
            margin-bottom: 25px;
        }
        .input-group input {
            flex: 1;
            padding: 12px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
            text-transform: uppercase;
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

        /* Hardware Card ID */
        .hardware-card {
            background-color: #0d1117;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 30px;
            position: relative;
            overflow: hidden;
        }
        .hardware-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background-color: var(--accent);
        }
        .hw-icon {
            font-size: 3.5rem;
            color: #30363d;
        }
        .hw-info h3 {
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .hw-vendor {
            font-size: 1.5rem;
            color: #39d353;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .hw-mac {
            font-family: 'Fira Code', monospace;
            background-color: rgba(255,255,255,0.05);
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ef4444;
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
                <h1>MAC Vendor Lookup</h1>
                <p>Identifikasi nama pabrikan (vendor) perangkat target melalui MAC Address (OUI).</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Contoh: FC:FB:FB:01:FA:21" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-magnifying-glass"></i> Identifikasi</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    
                    <div class="hardware-card">
                        <div class="hw-icon">
                            <i class="fa-solid fa-server"></i>
                        </div>
                        <div class="hw-info">
                            <h3>Pabrikan Perangkat (Vendor)</h3>
                            <div class="hw-vendor"><?php echo htmlspecialchars($result_data['vendor']); ?></div>
                            <div class="hw-mac"><i class="fa-solid fa-network-wired" style="color: var(--text-muted); margin-right: 5px;"></i> <?php echo htmlspecialchars($result_data['mac']); ?></div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="alert-error">
                        <i class="fa-solid fa-triangle-exclamation"></i> 
                        <?php echo htmlspecialchars($result_data['message'] ?? 'Unknown error'); ?>
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