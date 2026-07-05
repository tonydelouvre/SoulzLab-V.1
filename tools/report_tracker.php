<?php
require '../config.php';
$active_menu = 'report_tracker';

$search_target = '';
$query = "SELECT * FROM targets ORDER BY id DESC";
$params = [];

// Fitur Filter/Pencarian Target
if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['search'])) {
    $search_target = trim($_GET['search']);
    $query = "SELECT * FROM targets WHERE target_name LIKE ? ORDER BY id DESC";
    $params = ["%$search_target%"];
}

$reports = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data laporan: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target & Report Tracker - SoulzLab</title>
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
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .search-bar {
            display: flex;
            gap: 10px;
        }
        .search-bar input {
            padding: 10px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            width: 250px;
        }
        .search-bar input:focus {
            outline: none;
            border-color: var(--accent);
        }
        .btn-action {
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-action:hover {
            background-color: var(--accent-hover);
        }
        .btn-print {
            background-color: #30363d;
            color: #c9d1d9;
            border: 1px solid var(--border-color);
        }
        .btn-print:hover {
            background-color: #8b949e;
            color: #0d1117;
        }

        /* Table Styles */
        .report-table-wrapper {
            overflow-x: auto;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .report-table th, .report-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .report-table th {
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 800;
            background-color: rgba(255, 255, 255, 0.02);
        }
        .report-table td {
            font-family: 'Fira Code', monospace;
        }
        .report-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
        }
        .target-name {
            color: #39d353;
            font-weight: bold;
        }
        .scan-type {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--accent);
            padding: 6px 12px; /* Padding sedikit dilebarkan agar teks bernapas */
            border-radius: 4px;
            font-size: 0.85rem;
            border: 1px solid var(--accent);
            white-space: nowrap; /* KUNCI: Mencegah teks terlipat ke bawah */
            display: inline-block;
            text-align: center;
        }
        
        /* Tambahan opsional: Agar isi tabel rata tengah secara vertikal */
        .report-table td {
            font-family: 'Fira Code', monospace;
            vertical-align: middle; 
        }

        .scan-result-preview {
            max-width: 350px;
            white-space: nowrap; /* Teks memanjang ke kanan */
            overflow-x: auto; /* Memunculkan scrollbar horizontal */
            color: var(--text-muted);
            font-size: 0.85rem;
            background-color: rgba(255, 255, 255, 0.02);
            padding: 8px 12px;
            border-radius: 6px;
            display: block; /* Penting agar max-width dan overflow bekerja di dalam tabel */
        }

        /* Desain Scrollbar Khusus untuk Kolom JSON */
        .scan-result-preview::-webkit-scrollbar {
            height: 6px;
        }
        .scan-result-preview::-webkit-scrollbar-track {
            background: transparent;
        }
        .scan-result-preview::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }
        .scan-result-preview::-webkit-scrollbar-thumb:hover {
            background: var(--accent);
        }

        /* Print Media Query: Menyembunyikan UI yang tidak perlu saat di-print ke PDF */
        @media print {
            body { background-color: white; color: black; }
            .sidebar, .mobile-toggle, .top-header, .search-bar, .btn-print { display: none !important; }
            .main-content { padding: 0; width: 100%; }
            .tool-container { border: none; padding: 0; background-color: transparent; }
            .report-table th { color: black; background-color: #f0f0f0; border-bottom: 2px solid #000; }
            .report-table td { border-bottom: 1px solid #ccc; color: black; }
            .target-name { color: #000; }
            .scan-type { border: 1px solid #000; color: #000; background: none; }
            /* Memaksa background color tampil di PDF jika didukung browser */
            * { -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
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
                <h1>Target & Report Tracker</h1>
                <p>Log komprehensif dari seluruh aktivitas pengintaian dan eksploitasi Anda.</p>
            </div>
        </header>

        <section class="tool-container">
            <div class="report-header">
                <form method="GET" action="" class="search-bar">
                    <input type="text" name="search" placeholder="Cari IP atau Domain..." value="<?php echo htmlspecialchars($search_target); ?>">
                    <button type="submit" class="btn-action"><i class="fa-solid fa-filter"></i> Filter</button>
                    <?php if (!empty($search_target)): ?>
                        <a href="report_tracker.php" class="btn-action" style="background-color: transparent; border: 1px solid var(--border-color); color: var(--text-muted);"><i class="fa-solid fa-xmark"></i> Clear</a>
                    <?php endif; ?>
                </form>
                
                <button onclick="window.print()" class="btn-action btn-print"><i class="fa-solid fa-file-pdf"></i> Cetak / Ekspor PDF</button>
            </div>

            <div class="report-table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Waktu (Timestamp)</th>
                            <th>Target Info</th>
                            <th>Modul Scan</th>
                            <th>Ringkasan Hasil (Raw JSON)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($reports) > 0): ?>
                            <?php foreach ($reports as $row): ?>
                                <tr>
                                    <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td class="target-name"><?php echo htmlspecialchars($row['target_name']); ?></td>
                                    <td><span class="scan-type"><?php echo htmlspecialchars($row['scan_type']); ?></span></td>
                                    <td class="scan-result-preview" title="<?php echo htmlspecialchars($row['scan_result']); ?>">
                                        <?php echo htmlspecialchars($row['scan_result']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    Tidak ada data jejak scan ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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