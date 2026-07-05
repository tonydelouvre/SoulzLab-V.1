<?php
require '../config.php';
$active_menu = 'dns_enum';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    $safe_target = escapeshellarg($target);
    
    $command = "python ../core_scripts/dns_enum_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'DNS Enumerator', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DNS Record Enumerator - SoulzLab</title>
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

        /* Result Styles */
        .record-group {
            margin-bottom: 20px;
            background-color: #0d1117;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }
        .record-header {
            background-color: rgba(255,255,255,0.02);
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .record-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .record-list li {
            padding: 12px 20px;
            border-bottom: 1px dashed var(--border-color);
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
            color: #c9d1d9;
            word-break: break-all;
        }
        .record-list li:last-child {
            border-bottom: none;
        }
        
        /* Badges for record types */
        .badge-dns {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-family: 'Fira Code', monospace;
            color: #fff;
        }
        .bg-A { background-color: #059669; }
        .bg-AAAA { background-color: #047857; }
        .bg-MX { background-color: #2563eb; }
        .bg-TXT { background-color: #d97706; }
        .bg-NS { background-color: #7c3aed; }
        .bg-CNAME { background-color: #db2777; }
        .bg-SOA { background-color: #475569; }
        
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
                <h1>DNS Record Enumerator</h1>
                <p>Ekstrak A, MX, NS, dan TXT record untuk memetakan infrastruktur target.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Masukkan Domain (contoh: target.com)" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-satellite"></i> Enumerate</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    
                    <div style="margin-bottom: 20px; color: var(--text-muted); font-family: 'Fira Code', monospace;">
                        <i class="fa-solid fa-circle-check" style="color: var(--accent);"></i> 
                        Berhasil mengekstrak <strong><?php echo $result_data['total']; ?></strong> record DNS untuk: <span style="color:var(--accent);"><?php echo htmlspecialchars($result_data['domain']); ?></span>
                    </div>

                    <div class="records-container">
                        <?php foreach ($result_data['records'] as $type => $records): ?>
                            <div class="record-group">
                                <div class="record-header">
                                    <span class="badge-dns bg-<?php echo $type; ?>"><?php echo $type; ?> RECORD</span>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">(<?php echo count($records); ?> ditemukan)</span>
                                </div>
                                <ul class="record-list">
                                    <?php foreach ($records as $val): ?>
                                        <li><?php echo htmlspecialchars($val); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
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