<?php
require '../config.php';
$active_menu = 'sqli_tester';

$result_data = null;
$target = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['target'])) {
    $target = trim($_POST['target']);
    $safe_target = escapeshellarg($target);
    
    $command = "python ../core_scripts/sqli_tester_engine.py " . $safe_target;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$target, 'SQLi Tester', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Tester - SoulzLab</title>
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
            margin-bottom: 10px;
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
        .help-text {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 25px;
            font-family: 'Fira Code', monospace;
        }
        
        /* Box Status */
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-family: 'Fira Code', monospace;
        }
        .status-safe {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid #39d353;
            color: #39d353;
        }
        .status-vuln {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
        }
        
        /* Tabel Payload */
        .vuln-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .vuln-table th, .vuln-table td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid rgba(239, 68, 68, 0.3);
        }
        .vuln-table th {
            color: #fca5a5;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .vuln-table td {
            font-family: 'Fira Code', monospace;
        }
        .db-badge {
            background-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
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
                <h1>SQL Injection (SQLi) Tester</h1>
                <p>Uji kerentanan parameter URL terhadap serangan injeksi database (Error-based).</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="target" placeholder="Contoh: http://target.com/page.php?id=1" required value="<?php echo htmlspecialchars($target); ?>">
                    <button type="submit" class="btn-scan"><i class="fa-solid fa-fire-flame-curved"></i> Inject</button>
                </div>
                <div class="help-text">
                    <i class="fa-solid fa-circle-info"></i> Pastikan URL memiliki parameter (misal: ?id=1, ?cat=2) agar payload dapat disuntikkan.
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    
                    <?php if (count($result_data['vulnerabilities']) > 0): ?>
                        <div class="status-box status-vuln">
                            <h3 style="margin-bottom: 10px;"><i class="fa-solid fa-triangle-exclamation"></i> VULNERABILITY DETECTED!</h3>
                            <p>Target merespons dengan pesan error database. Kemungkinan besar rentan terhadap SQL Injection.</p>
                            
                            <table class="vuln-table">
                                <thead>
                                    <tr>
                                        <th>Payload Berhasil</th>
                                        <th>Identifikasi Database</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result_data['vulnerabilities'] as $vuln): ?>
                                        <tr>
                                            <td style="color: #fff;"><?php echo htmlspecialchars($vuln['payload']); ?></td>
                                            <td><span class="db-badge"><?php echo htmlspecialchars($vuln['db_type']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="status-box status-safe">
                            <h3 style="margin-bottom: 10px;"><i class="fa-solid fa-shield-check"></i> Target Tampak Aman (Error-based)</h3>
                            <p>Telah mencoba <strong><?php echo $result_data['total_tested']; ?></strong> payload, namun server tidak memunculkan pesan error database standar. (Catatan: Target mungkin masih rentan terhadap Blind SQLi).</p>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="status-box status-vuln" style="background-color: rgba(255,255,255,0.05); border-color: var(--border-color); color: var(--text-muted);">
                        <i class="fa-solid fa-circle-xmark"></i> Gagal mengeksekusi tes: <?php echo htmlspecialchars($result_data['message'] ?? 'Unknown error'); ?>
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