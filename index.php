<?php
require 'config.php';
$active_menu = 'dashboard'; // Variabel untuk memberitahu sidebar menu mana yang aktif

$total_targets = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM targets");
    $total_targets = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_targets = "Error";
}

$recent_scans = [];
try {
    $stmt = $pdo->query("SELECT target_name, scan_type, created_at FROM targets ORDER BY id DESC LIMIT 5");
    $recent_scans = $stmt->fetchAll();
} catch (PDOException $e) {
}

// Menghitung jumlah tool aktif secara dinamis dari folder tools/
$active_tools_count = 0;
$tool_files = glob("tools/*.php");
if ($tool_files) {
    $active_tools_count = count($tool_files);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoulzLab - Advanced Pentest Toolkit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-section { margin-top: 40px; background-color: var(--bg-card); padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); }
        .dashboard-section h2 { font-size: 1.2rem; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .activity-table { width: 100%; border-collapse: collapse; }
        .activity-table th, .activity-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        .activity-table th { color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; }
        .activity-table td { font-family: 'Fira Code', monospace; font-size: 0.9rem; }
        .activity-table tr:hover td { background-color: rgba(255, 255, 255, 0.02); }
        .badge-type { background-color: rgba(16, 185, 129, 0.1); color: var(--accent); padding: 5px 10px; border-radius: 6px; font-size: 0.8rem; border: 1px solid var(--accent); }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Dashboard Overview</h1>
                <p>Welcome back, Administrator.</p>
            </div>
            <div class="user-profile">
                <i class="fa-solid fa-user-astronaut"></i>
            </div>
        </header>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-crosshairs"></i></div>
                <div class="stat-info">
                    <h3>Total Scans / Targets</h3>
                    <p><?php echo $total_targets; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-toolbox"></i></div>
                <div class="stat-info">
                    <h3>Active Tools</h3>
                    <p><?php echo $active_tools_count; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-server"></i></div>
                <div class="stat-info">
                    <h3>System Status</h3>
                    <p style="color: #39d353; font-size: 1.2rem; margin-top: 10px;">ONLINE</p>
                </div>
            </div>
        </section>

        <section class="dashboard-section">
            <h2><i class="fa-solid fa-clock-rotate-left" style="color: var(--accent);"></i> Recent Scan Activity</h2>
            <?php if (count($recent_scans) > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Target</th>
                                <th>Scan Type</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_scans as $scan): ?>
                                <tr>
                                    <td style="color: #c9d1d9;"><?php echo htmlspecialchars($scan['target_name']); ?></td>
                                    <td><span class="badge-type"><?php echo htmlspecialchars($scan['scan_type']); ?></span></td>
                                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($scan['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); font-style: italic; margin-top: 10px;">Belum ada aktivitas scan yang tercatat di database.</p>
            <?php endif; ?>
        </section>

        <?php include 'includes/footer.php'; ?>

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