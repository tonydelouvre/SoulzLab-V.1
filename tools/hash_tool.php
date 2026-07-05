<?php
require '../config.php';
$active_menu = 'hash_tool';

$analyze_input = '';
$analysis_result = [];

$generate_input = '';
$generate_result = [];

// Logika Analyzer
if (isset($_POST['action']) && $_POST['action'] == 'analyze') {
    $analyze_input = trim($_POST['analyze_input']);
    $length = strlen($analyze_input);
    
    // Menebak hash berdasarkan panjang karakter
    if (preg_match('~^[a-f0-9]+$~i', $analyze_input)) {
        if ($length == 32) {
            $analysis_result[] = "MD5";
            $analysis_result[] = "MD4";
            $analysis_result[] = "NTLM (Windows)";
        } elseif ($length == 40) {
            $analysis_result[] = "SHA-1";
            $analysis_result[] = "MySQL5";
        } elseif ($length == 56) {
            $analysis_result[] = "SHA-224";
        } elseif ($length == 64) {
            $analysis_result[] = "SHA-256";
            $analysis_result[] = "GOST";
        } elseif ($length == 96) {
            $analysis_result[] = "SHA-384";
        } elseif ($length == 128) {
            $analysis_result[] = "SHA-512";
            $analysis_result[] = "Whirlpool";
        } else {
            $analysis_result[] = "Format Hexadecimal tidak dikenali sebagai Hash standar.";
        }
    } elseif (preg_match('~^\$2[aby]\$[0-9]{2}\$[./A-Za-z0-9]{53}$~', $analyze_input)) {
        $analysis_result[] = "Bcrypt";
    } elseif (preg_match('~^\$1\$.{1,8}\$.{22}$~', $analyze_input)) {
        $analysis_result[] = "MD5 Crypt (Unix)";
    } elseif (preg_match('~^\$6\$.{1,16}\$.{86}$~', $analyze_input)) {
        $analysis_result[] = "SHA-512 Crypt (Unix)";
    } else {
        $analysis_result[] = "Tipe hash tidak diketahui atau memiliki format khusus/Salted.";
    }
}

// Logika Generator
if (isset($_POST['action']) && $_POST['action'] == 'generate') {
    $generate_input = trim($_POST['generate_input']);
    if (!empty($generate_input)) {
        $generate_result = [
            'MD5' => md5($generate_input),
            'SHA-1' => sha1($generate_input),
            'SHA-256' => hash('sha256', $generate_input),
            'SHA-512' => hash('sha512', $generate_input),
            'Bcrypt' => password_hash($generate_input, PASSWORD_BCRYPT)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash Generator & Analyzer - SoulzLab</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tool-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .tool-grid {
                grid-template-columns: 1fr;
            }
        }
        .card-panel {
            background-color: var(--bg-card);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .card-panel h3 {
            color: var(--accent);
            margin-bottom: 20px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
            margin-bottom: 10px;
        }
        .input-group input:focus {
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
            width: 100%;
        }
        .btn-action:hover {
            background-color: var(--accent-hover);
        }
        .result-box {
            margin-top: 20px;
            background-color: #0d1117;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
        }
        .hash-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .hash-list li {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed var(--border-color);
        }
        .hash-list li:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .hash-type {
            color: #8b949e;
            font-weight: bold;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .hash-value {
            font-family: 'Fira Code', monospace;
            color: #39d353;
            word-break: break-all;
            font-size: 0.9rem;
        }
        .guess-badge {
            display: inline-block;
            background-color: rgba(16, 185, 129, 0.1);
            color: #39d353;
            padding: 5px 12px;
            border-radius: 20px;
            font-family: 'Fira Code', monospace;
            font-size: 0.9rem;
            border: 1px solid var(--accent);
            margin: 5px;
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
                <h1>Hash Tool</h1>
                <p>Identifikasi tipe hash yang tidak diketahui atau buat hash baru untuk eksploitasi.</p>
            </div>
        </header>

        <section class="tool-grid">
            <div class="card-panel">
                <h3><i class="fa-solid fa-microscope"></i> Hash Analyzer</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="analyze">
                    <div class="input-group">
                        <input type="text" name="analyze_input" placeholder="Paste hash misterius di sini..." required value="<?php echo htmlspecialchars($analyze_input); ?>">
                    </div>
                    <button type="submit" class="btn-action"><i class="fa-solid fa-magnifying-glass"></i> Identifikasi Tipe</button>
                </form>

                <?php if (!empty($analyze_input) && isset($_POST['action']) && $_POST['action'] == 'analyze'): ?>
                    <div class="result-box">
                        <div style="color: var(--text-muted); margin-bottom: 15px; font-size: 0.9rem;">
                            Panjang Hash: <strong><?php echo strlen($analyze_input); ?> karakter</strong>
                        </div>
                        <div>
                            <span style="display:block; margin-bottom:10px; color:#c9d1d9;">Kemungkinan Tipe Hash:</span>
                            <?php foreach ($analysis_result as $guess): ?>
                                <span class="guess-badge"><?php echo htmlspecialchars($guess); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-panel">
                <h3><i class="fa-solid fa-fingerprint"></i> Hash Generator</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="generate">
                    <div class="input-group">
                        <input type="text" name="generate_input" placeholder="Masukkan password (Cleartext)..." required value="<?php echo htmlspecialchars($generate_input); ?>">
                    </div>
                    <button type="submit" class="btn-action"><i class="fa-solid fa-bolt"></i> Generate Hash</button>
                </form>

                <?php if (!empty($generate_result)): ?>
                    <div class="result-box">
                        <ul class="hash-list">
                            <?php foreach ($generate_result as $type => $hash): ?>
                                <li>
                                    <span class="hash-type"><?php echo $type; ?></span>
                                    <span class="hash-value"><?php echo $hash; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
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