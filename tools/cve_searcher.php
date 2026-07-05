<?php
require '../config.php';
$active_menu = 'cve_searcher';

$result_data = null;
$keyword = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['keyword'])) {
    $keyword = trim($_POST['keyword']);
    $safe_keyword = escapeshellarg($keyword);
    
    $command = "python ../core_scripts/cve_search_engine.py " . $safe_keyword;
    $output = shell_exec($command);
    
    if ($output) {
        $result_data = json_decode($output, true);
        
        if (isset($result_data['status']) && $result_data['status'] === 'success') {
            $stmt = $pdo->prepare("INSERT INTO targets (target_name, scan_type, scan_result) VALUES (?, ?, ?)");
            $stmt->execute([$keyword, 'CVE Search', $output]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CVE Searcher - SoulzLab</title>
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
        .btn-search {
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            padding: 0 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-search:hover {
            background-color: var(--accent-hover);
        }

        /* Result Cards */
        .cve-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .cve-card {
            background-color: #0d1117;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .cve-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .cve-id {
            font-family: 'Fira Code', monospace;
            font-size: 1.2rem;
            color: #c9d1d9;
            font-weight: bold;
        }
        .cve-id a {
            color: inherit;
            text-decoration: none;
        }
        .cve-id a:hover {
            color: var(--accent);
        }
        
        /* Dynamic Badges */
        .cvss-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 800;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .sev-CRITICAL, .sev-HIGH { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
        .sev-MEDIUM { background-color: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b; }
        .sev-LOW { background-color: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
        .sev-UNKNOWN { background-color: rgba(100, 116, 139, 0.2); color: #94a3b8; border: 1px solid #64748b; }

        .cve-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
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
                <h1>CVE Intel Searcher</h1>
                <p>Tarik informasi kerentanan (CVE) publik langsung dari database NIST Amerika Serikat.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" name="keyword" placeholder="Masukkan teknologi (contoh: wordpress, apache, windows 10)..." required value="<?php echo htmlspecialchars($keyword); ?>">
                    <button type="submit" class="btn-search"><i class="fa-solid fa-satellite-dish"></i> Intel Search</button>
                </div>
            </form>

            <?php if ($result_data): ?>
                <?php if (isset($result_data['status']) && $result_data['status'] === 'success'): ?>
                    
                    <p style="margin-bottom: 20px; color: var(--text-muted);">
                        <i class="fa-solid fa-check"></i> Menemukan <strong><?php echo $result_data['total']; ?></strong> kerentanan relevan terbaru untuk: <span style="color:var(--accent); font-family:'Fira Code';"><?php echo htmlspecialchars($result_data['keyword']); ?></span>
                    </p>

                    <div class="cve-list">
                        <?php if (count($result_data['data']) > 0): ?>
                            <?php foreach ($result_data['data'] as $cve): ?>
                                <?php $sevClass = 'sev-' . strtoupper($cve['severity']); ?>
                                <div class="cve-card">
                                    <div class="cve-header">
                                        <div class="cve-id">
                                            <i class="fa-solid fa-bug" style="color: var(--text-muted); font-size: 0.9em; margin-right:5px;"></i>
                                            <a href="https://nvd.nist.gov/vuln/detail/<?php echo htmlspecialchars($cve['id']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($cve['id']); ?>
                                            </a>
                                        </div>
                                        <div class="cvss-badge <?php echo $sevClass; ?>">
                                            <?php echo htmlspecialchars($cve['severity']); ?> (<?php echo htmlspecialchars($cve['cvss']); ?>)
                                        </div>
                                    </div>
                                    <div class="cve-desc">
                                        <?php echo htmlspecialchars($cve['description']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert-error" style="border-color: var(--accent); color: var(--accent); background-color: rgba(16, 185, 129, 0.1);">
                                <i class="fa-solid fa-circle-info"></i> Tidak ada data CVE terbaru ditemukan untuk kata kunci tersebut.
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="alert-error">
                        <i class="fa-solid fa-triangle-exclamation"></i> 
                        Pencarian gagal: <?php echo htmlspecialchars($result_data['message'] ?? 'Unknown error'); ?>
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