<?php
require '../config.php';

$input_text = '';
$output_text = '';
$operation = 'base64_encode';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_text = $_POST['input_text'] ?? '';
    $operation = $_POST['operation'] ?? 'base64_encode';

    if (!empty($input_text)) {
        try {
            switch ($operation) {
                case 'base64_encode':
                    $output_text = base64_encode($input_text);
                    break;
                case 'base64_decode':
                    $output_text = base64_decode($input_text);
                    break;
                case 'url_encode':
                    $output_text = urlencode($input_text);
                    break;
                case 'url_decode':
                    $output_text = urldecode($input_text);
                    break;
                case 'hex_encode':
                    $output_text = bin2hex($input_text);
                    break;
                case 'hex_decode':
                    // Validasi string hex sebelum decode agar tidak error
                    if (ctype_xdigit(str_replace(["\n", "\r", " "], "", $input_text))) {
                        $output_text = hex2bin(str_replace(["\n", "\r", " "], "", $input_text));
                    } else {
                        $output_text = "Error: Input bukan format Hexadecimal yang valid.";
                    }
                    break;
            }
        } catch (Exception $e) {
            $output_text = "Error: Terjadi kesalahan saat memproses data.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Encoder/Decoder - SoulzLab</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tool-container {
            background-color: var(--bg-card);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .tool-container {
                grid-template-columns: 1fr;
            }
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        textarea {
            width: 100%;
            height: 200px;
            padding: 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
            resize: vertical;
        }
        textarea:focus {
            outline: none;
            border-color: var(--accent);
        }
        select {
            padding: 12px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
        }
        .btn-process {
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-process:hover {
            background-color: var(--accent-hover);
        }
        .output-area {
            background-color: #0d1117;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
            font-family: 'Fira Code', monospace;
            color: #39d353;
            height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <?php 
        $active_menu = 'encoder';
        include '../includes/sidebar.php'; 
    ?>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h1>Data Encoder & Decoder</h1>
                <p>Manipulasi teks dan payload untuk keperluan eksploitasi atau analisis.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="" class="form-group">
                <label for="input_text"><i class="fa-solid fa-keyboard"></i> Input Text:</label>
                <textarea name="input_text" id="input_text" placeholder="Masukkan teks atau payload di sini..." required><?php echo htmlspecialchars($input_text); ?></textarea>
                
                <select name="operation">
                    <option value="base64_encode" <?php echo ($operation == 'base64_encode') ? 'selected' : ''; ?>>Base64 Encode</option>
                    <option value="base64_decode" <?php echo ($operation == 'base64_decode') ? 'selected' : ''; ?>>Base64 Decode</option>
                    <option value="url_encode" <?php echo ($operation == 'url_encode') ? 'selected' : ''; ?>>URL Encode</option>
                    <option value="url_decode" <?php echo ($operation == 'url_decode') ? 'selected' : ''; ?>>URL Decode</option>
                    <option value="hex_encode" <?php echo ($operation == 'hex_encode') ? 'selected' : ''; ?>>Hex Encode</option>
                    <option value="hex_decode" <?php echo ($operation == 'hex_decode') ? 'selected' : ''; ?>>Hex Decode</option>
                </select>
                
                <button type="submit" class="btn-process"><i class="fa-solid fa-bolt"></i> Eksekusi</button>
            </form>

            <div class="form-group">
                <label><i class="fa-solid fa-terminal"></i> Output Result:</label>
                <div class="output-area"><?php echo htmlspecialchars($output_text); ?></div>
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