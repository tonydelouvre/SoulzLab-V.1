<?php
require '../config.php';
$active_menu = 'payload_manager';

$message = '';

// Handle Tambah Payload (CREATE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $content = trim($_POST['content']);
    
    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO payloads (title, category, content) VALUES (?, ?, ?)");
            $stmt->execute([$title, $category, $content]);
            $message = "<div class='alert-success'><i class='fa-solid fa-check'></i> Script berhasil disimpan!</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert-error'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Handle Hapus Payload (DELETE)
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM payloads WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: payload_manager.php");
        exit();
    } catch (PDOException $e) {
        $message = "<div class='alert-error'>Error saat menghapus.</div>";
    }
}

// Mengambil Data Payload (READ)
$saved_payloads = [];
try {
    $stmt = $pdo->query("SELECT * FROM payloads ORDER BY id DESC");
    $saved_payloads = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = "<div class='alert-error'>Error mengambil data: " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payload & Script Manager - SoulzLab</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tool-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
            margin-top: 20px;
        }
        @media (max-width: 992px) {
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
        
        /* Form Styling */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
        }
        .form-group textarea {
            font-family: 'Fira Code', monospace;
            height: 150px;
            resize: vertical;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--accent);
        }
        .btn-submit {
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }
        .btn-submit:hover {
            background-color: var(--accent-hover);
        }
        
        /* List Styling */
        .payload-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-height: 700px;
            overflow-y: auto;
            padding-right: 10px;
        }
        .payload-item {
            background-color: #0d1117;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
        }
        .payload-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .payload-title {
            font-weight: 600;
            color: #c9d1d9;
            font-size: 1.1rem;
        }
        .badge-cat {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--accent);
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
            border: 1px solid var(--accent);
            margin-left: 10px;
        }
        .payload-code {
            background-color: var(--bg-dark);
            padding: 15px;
            border-radius: 6px;
            font-family: 'Fira Code', monospace;
            color: #39d353;
            font-size: 0.9rem;
            word-break: break-all;
            white-space: pre-wrap;
            border-left: 3px solid var(--accent);
            margin-bottom: 10px;
        }
        .payload-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-act {
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            border: 1px solid var(--border-color);
            background-color: rgba(255,255,255,0.05);
            color: var(--text-main);
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-copy:hover { border-color: var(--accent); color: var(--accent); }
        .btn-del:hover { border-color: #ef4444; color: #ef4444; }

        .alert-success { background-color: rgba(16, 185, 129, 0.2); color: #10b981; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #10b981; }
        .alert-error { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #ef4444; }
        
        .toast-msg {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--accent);
            color: var(--bg-dark);
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            display: none;
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
            z-index: 1000;
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
                <h1>Payload & Script Manager</h1>
                <p>Simpan dan kelola cheat sheet script, reverse shell, atau perintah favorit Anda.</p>
            </div>
        </header>

        <?php if (!empty($message)) echo $message; ?>

        <section class="tool-grid">
            <div class="card-panel">
                <h3><i class="fa-solid fa-plus"></i> Tambah Script Baru</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Nama / Judul Script</label>
                        <input type="text" name="title" placeholder="Contoh: Bash Reverse Shell" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category" required>
                            <option value="Reverse Shell">Reverse Shell</option>
                            <option value="Privilege Escalation">Privilege Escalation</option>
                            <option value="Enumeration">Enumeration (Recon)</option>
                            <option value="Web Exploit">Web Exploit</option>
                            <option value="Misc">Lain-lain</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Isi Kode / Payload</label>
                        <textarea name="content" placeholder="Ketik atau paste kode Anda di sini..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fa-solid fa-save"></i> Simpan Script</button>
                </form>
            </div>

            <div class="card-panel">
                <h3><i class="fa-solid fa-book-bookmark"></i> Gudang Script Tersimpan</h3>
                
                <?php if (count($saved_payloads) > 0): ?>
                    <div class="payload-list">
                        <?php foreach ($saved_payloads as $pl): ?>
                            <div class="payload-item">
                                <div class="payload-header">
                                    <div class="payload-title">
                                        <?php echo htmlspecialchars($pl['title']); ?>
                                        <span class="badge-cat"><?php echo htmlspecialchars($pl['category']); ?></span>
                                    </div>
                                </div>
                                <div class="payload-code" id="code_<?php echo $pl['id']; ?>"><?php echo htmlspecialchars($pl['content']); ?></div>
                                
                                <div class="payload-actions">
                                    <button class="btn-act btn-copy" onclick="copyCode('code_<?php echo $pl['id']; ?>')">
                                        <i class="fa-regular fa-copy"></i> Copy
                                    </button>
                                    <a href="?delete=<?php echo $pl['id']; ?>" class="btn-act btn-del" onclick="return confirm('Yakin ingin menghapus script ini?');">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-muted); padding: 40px;">
                        <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; color: #30363d;"></i>
                        <p>Gudang penyimpanan kosong.<br>Tambahkan script pertama Anda!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php include '../includes/footer.php'; ?>
    </main>

    <div id="toast" class="toast-msg"><i class="fa-solid fa-check"></i> Script disalin ke clipboard!</div>

    <script>
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');

        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        function copyCode(elementId) {
            const textToCopy = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                const toast = document.getElementById('toast');
                toast.style.display = 'block';
                setTimeout(() => { toast.style.display = 'none'; }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin text: ', err);
            });
        }
    </script>
</body>
</html>