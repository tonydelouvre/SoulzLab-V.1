<?php
if (!isset($active_menu)) { $active_menu = ''; }

// Logika cerdas untuk mendeteksi kategori mana yang harus otomatis terbuka
$cat_recon = in_array($active_menu, ['whois', 'subdomain']) ? 'active' : '';
$cat_scan = in_array($active_menu, ['port_scanner', 'dir_scanner']) ? 'active' : '';
$cat_vuln = in_array($active_menu, ['header_analyzer', 'sqli_tester', 'xss_payloads', 'cve_searcher']) ? 'active' : '';
$cat_crypto = ($active_menu == 'hash_tool') ? 'active' : '';
$cat_util = ($active_menu == 'encoder') ? 'active' : '';
?>
<aside class="sidebar" id="sidebar">
    <div class="brand">
        <i class="fa-solid fa-shield-halved"></i>
        <h2>SoulzLab</h2>
    </div>
    
    <nav class="menu">
        <a href="<?php echo $base_url; ?>index.php" class="<?php echo ($active_menu == 'dashboard') ? 'active' : ''; ?>">
            <i class="fa-solid fa-house"></i> Dashboard
        </a>
        
        <button class="dropdown-btn <?php echo $cat_recon; ?>">
            <span><i class="fa-solid fa-eye left-icon"></i> Recon</span>
            <i class="fa-solid fa-chevron-down arrow"></i>
        </button>
        <div class="dropdown-container <?php echo $cat_recon; ?>">
            <a href="<?php echo $base_url; ?>tools/whois.php" class="<?php echo ($active_menu == 'whois') ? 'active' : ''; ?>"><i class="fa-solid fa-globe"></i> Whois & IP</a>
            <a href="<?php echo $base_url; ?>tools/subdomain.php" class="<?php echo ($active_menu == 'subdomain') ? 'active' : ''; ?>"><i class="fa-solid fa-sitemap"></i> Subdomain Scanner</a>
            <a href="<?php echo $base_url; ?>tools/dns_enum.php" class="<?php echo ($active_menu == 'dns_enum') ? 'active' : ''; ?>"><i class="fa-solid fa-server"></i> DNS Enumerator</a>
            <a href="<?php echo $base_url; ?>tools/mac_lookup.php" class="<?php echo ($active_menu == 'mac_lookup') ? 'active' : ''; ?>"><i class="fa-solid fa-microchip"></i> MAC Lookup</a>
        </div>

        <button class="dropdown-btn <?php echo $cat_scan; ?>">
            <span><i class="fa-solid fa-tower-broadcast left-icon"></i> Scanning</span>
            <i class="fa-solid fa-chevron-down arrow"></i>
        </button>
        <div class="dropdown-container <?php echo $cat_scan; ?>">
            <a href="<?php echo $base_url; ?>tools/port_scanner.php" class="<?php echo ($active_menu == 'port_scanner') ? 'active' : ''; ?>"><i class="fa-solid fa-network-wired"></i> Port Scanner</a>
            <a href="<?php echo $base_url; ?>tools/dir_scanner.php" class="<?php echo ($active_menu == 'dir_scanner') ? 'active' : ''; ?>"><i class="fa-solid fa-folder-tree"></i> Directory Bruteforce</a>
        </div>

        <button class="dropdown-btn <?php echo $cat_vuln; ?>">
            <span><i class="fa-solid fa-bug left-icon"></i> Exploit</span>
            <i class="fa-solid fa-chevron-down arrow"></i>
        </button>
        <div class="dropdown-container <?php echo $cat_vuln; ?>">
            <a href="<?php echo $base_url; ?>tools/header_analyzer.php" class="<?php echo ($active_menu == 'header_analyzer') ? 'active' : ''; ?>"><i class="fa-solid fa-server"></i> Header Analyzer</a>
            <a href="<?php echo $base_url; ?>tools/sqli_tester.php" class="<?php echo ($active_menu == 'sqli_tester') ? 'active' : ''; ?>"><i class="fa-solid fa-database"></i> SQLi Tester</a>
            <a href="<?php echo $base_url; ?>tools/xss_payloads.php" class="<?php echo ($active_menu == 'xss_payloads') ? 'active' : ''; ?>"><i class="fa-solid fa-code-compare"></i> XSS Generator</a>
            <a href="<?php echo $base_url; ?>tools/cve_searcher.php" class="<?php echo ($active_menu == 'cve_searcher') ? 'active' : ''; ?>"><i class="fa-solid fa-book-skull"></i> CVE Searcher</a>
        </div>

        <button class="dropdown-btn <?php echo $cat_crypto; ?>">
            <span><i class="fa-solid fa-key left-icon"></i> Crypto</span>
            <i class="fa-solid fa-chevron-down arrow"></i>
        </button>
        <div class="dropdown-container <?php echo $cat_crypto; ?>">
            <a href="<?php echo $base_url; ?>tools/hash_tool.php" class="<?php echo ($active_menu == 'hash_tool') ? 'active' : ''; ?>"><i class="fa-solid fa-fingerprint"></i> Hash Tool</a>
            <a href="<?php echo $base_url; ?>tools/hash_cracker.php" class="<?php echo ($active_menu == 'hash_cracker') ? 'active' : ''; ?>"><i class="fa-solid fa-unlock-keyhole"></i> Hash Cracker</a>
        </div>

        <button class="dropdown-btn <?php echo $cat_util; ?>">
            <span><i class="fa-solid fa-toolbox left-icon"></i> Utilities</span>
            <i class="fa-solid fa-chevron-down arrow"></i>
        </button>
        <div class="dropdown-container <?php echo $cat_util; ?>">
            <a href="<?php echo $base_url; ?>tools/encoder.php" class="<?php echo ($active_menu == 'encoder') ? 'active' : ''; ?>"><i class="fa-solid fa-file-code"></i> Data Encoder</a>
            <a href="<?php echo $base_url; ?>tools/payload_manager.php" class="<?php echo ($active_menu == 'payload_manager') ? 'active' : ''; ?>"><i class="fa-solid fa-clipboard-list"></i> Script Manager</a>
            <a href="<?php echo $base_url; ?>tools/report_tracker.php" class="<?php echo ($active_menu == 'report_tracker') ? 'active' : ''; ?>"><i class="fa-solid fa-file-pdf"></i> Report Tracker</a>
        </div>
    </nav>
</aside>

<script>
    // Script murni untuk mengaktifkan animasi Dropdown di Sidebar
    document.addEventListener("DOMContentLoaded", function() {
        var dropdowns = document.getElementsByClassName("dropdown-btn");
        for (var i = 0; i < dropdowns.length; i++) {
            dropdowns[i].addEventListener("click", function() {
                // Toggle warna aktif di tombol
                this.classList.toggle("active");
                
                // Toggle animasi buka/tutup konten dropdown di bawahnya
                var dropdownContent = this.nextElementSibling;
                if (dropdownContent.style.maxHeight) {
                    dropdownContent.style.maxHeight = null;
                    dropdownContent.classList.remove("active");
                } else {
                    dropdownContent.style.maxHeight = dropdownContent.scrollHeight + "px";
                    dropdownContent.classList.add("active");
                }
            });
            
            // Set max-height untuk dropdown yang sedang aktif saat halaman dimuat
            if(dropdowns[i].classList.contains('active')) {
                var activeContent = dropdowns[i].nextElementSibling;
                activeContent.style.maxHeight = activeContent.scrollHeight + "px";
            }
        }
    });
</script>