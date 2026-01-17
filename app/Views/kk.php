<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data KK - <?= $profil['nama'] ?? 'Jimpitan App' ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Tom Select (Searchable Dropdown) -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <!-- QRCode.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .swal2-container {
            z-index: 2000 !important;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300 pb-6">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" onclick="window.showLoader()" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text">Data KK & Kode QR</h1>
        </div>
        <div class="flex items-center space-x-3">
             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
             
             <?php if($canManage): ?>
             <button onclick="openAddModal()" class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30 active:scale-95 transition-all">
                <i class="fas fa-plus"></i>
             </button>
             <?php endif; ?>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <!-- Search -->
        <div class="mb-6 animate__animated animate__fadeIn">
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" id="searchInput" placeholder="Cari nama atau kode ID..." 
                       class="w-full pl-11 pr-12 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl shadow-sm text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                <button id="clearSearch" class="hidden absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>

        <!-- List Data -->
        <div id="kkGrid" class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
            <?php foreach($dataKK as $k): ?>
                <div class="kk-card bg-white dark:bg-slate-800 rounded-xl px-4 py-2 flex items-center justify-between shadow-sm animate__animated animate__fadeInUp border border-slate-100 dark:border-slate-800 hover:shadow-md transition-all"
                     data-name="<?= strtolower($k['kk_name']) ?>"
                     data-code="<?= strtolower($k['code_id']) ?>">
                    
                    <div class="flex items-center space-x-3 overflow-hidden flex-1">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-lg shadow-emerald-500/20">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <div class="overflow-hidden min-w-0">
                            <h4 class="font-bold text-slate-800 dark:text-white truncate text-sm"><?= $k['kk_name'] ?></h4>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="text-[10px] text-slate-500 bg-slate-100 dark:bg-slate-700/50 px-1.5 py-0.5 rounded font-mono">
                                    <?= $k['code_id'] ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if($canManage): ?>
                    <div class="flex items-center gap-1 shrink-0 ml-2">
                        <button onclick="showQR('<?= $k['code_id'] ?>', '<?= $k['kk_name'] ?>')" class="w-8 h-8 rounded-lg text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 flex items-center justify-center transition-colors">
                            <i class="fas fa-qrcode text-xs"></i>
                        </button>
                        <button onclick='openEditModal(<?= json_encode($k) ?>)' class="w-8 h-8 rounded-lg text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 flex items-center justify-center transition-colors">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="confirmDelete(<?= $k['id'] ?>, '<?= $k['kk_name'] ?>')" class="w-8 h-8 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-20">
            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                <i class="fas fa-folder-open text-4xl"></i>
            </div>
            <p class="text-slate-500">Data KK tidak ditemukan.</p>
        </div>
    </main>

    <!-- Modal Form -->
    <div id="kkModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 sm:p-8 animate__animated animate__zoomIn animate__faster">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalTitle" class="text-2xl font-bold text-slate-800 dark:text-white">Tambah KK</h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="kkForm" class="space-y-4">
                <input type="hidden" name="id" id="kkId">
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">NIKK (Pilih dari Data Warga)</label>
                    <select name="nikk" id="nikk" required class="w-full">
                        <option value="">Cari NIKK atau Nama...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nama KK / Warga (Otomatis)</label>
                    <input type="text" name="kk_name" id="kkName" required readonly
                           class="w-full px-4 py-3.5 bg-slate-200 dark:bg-slate-700/50 text-slate-500 border-none rounded-2xl text-sm focus:ring-0 cursor-not-allowed transition-all">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Kode QR / ID Unik (Otomatis sama dengan NIKK)</label>
                    <div class="relative">
                        <input type="text" name="code_id" id="codeId" required readonly
                               class="w-full px-4 py-3.5 bg-slate-200 dark:bg-slate-700/50 text-slate-500 border-none rounded-2xl text-sm focus:ring-0 cursor-not-allowed font-mono uppercase">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all mt-6">
                    Simpan Data
                </button>
            </form>
        </div>
    </div>

    <!-- QR Modal -->
    <div id="qrModal" class="fixed inset-0 z-[1200] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-md" onclick="closeQRModal()"></div>
        <div class="relative w-full max-w-sm bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-8 animate__animated animate__zoomIn animate__faster text-center">
            
            <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1">Kode QR Warga</h3>
            <p id="qrName" class="text-sm text-slate-500 dark:text-slate-400 mb-6 font-medium"></p>

            <div class="flex justify-center mb-6">
                <div id="qrcode" class="p-4 bg-white rounded-2xl shadow-inner border border-slate-100"></div>
            </div>

            <div class="flex space-x-3">
                 <button onclick="downloadQR()" class="flex-1 py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center space-x-2">
                    <i class="fas fa-download"></i>
                    <span>Simpan</span>
                </button>
                <button onclick="closeQRModal()" class="w-14 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold rounded-xl hover:bg-slate-200 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>



    <script>
        // Theme
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };

        // Modal State
        const modal = document.getElementById('kkModal');
        const form = document.getElementById('kkForm');
        let isEdit = false;
        let tSelect;

        // Initialize Tom Select with AJAX
        document.addEventListener('DOMContentLoaded', () => {
             tSelect = new TomSelect('#nikk', {
                valueField: 'value',
                labelField: 'text',
                searchField: 'text',
                create: false,
                placeholder: 'Ketik NIKK atau Nama...',
                maxItems: 1,
                load: function(query, callback) {
                    if (!query.length) return callback();
                    fetch('/kk/search?q=' + encodeURIComponent(query), { skipLoader: true })
                        .then(response => response.json())
                        .then(json => {
                            callback(json);
                        }).catch(() => {
                            callback();
                        });
                },
                render: {
                    option: function(item, escape) {
                        return `<div>
                                <span class="font-bold block">${escape(item.value)}</span>
                                <span class="text-xs text-slate-500">${escape(item.nama)}</span>
                            </div>`;
                    },
                    item: function(item, escape) {
                        return `<div>${escape(item.value)} - ${escape(item.nama)}</div>`;
                    }
                }
            });
            
            // Sync logic for Tom Select change
            tSelect.on('change', function(value) {
                if(value) {
                    document.getElementById('codeId').value = value;
                    
                    // Auto fill Name from selected option data
                    if (this.options[value]) {
                        document.getElementById('kkName').value = this.options[value].nama;
                    }
                } else {
                    document.getElementById('codeId').value = '';
                    document.getElementById('kkName').value = '';
                }
            });
        });

        function openAddModal() {
            isEdit = false;
            document.getElementById('modalTitle').innerText = 'Tambah KK';
            form.reset();
            tSelect.clear();
            tSelect.clearOptions(); // Clear previous search results
            document.getElementById('kkId').value = '';
            modal.classList.remove('hidden');
        }

        function openEditModal(data) {
            isEdit = true;
            document.getElementById('modalTitle').innerText = 'Edit KK';
            modal.classList.remove('hidden');
            
            document.getElementById('kkId').value = data.id;
            document.getElementById('kkName').value = data.kk_name;
            
            // Pre-fill Tom Select for Edit
            // Since data is remote, we must manually add the option first
            if(data.nikk) {
                tSelect.addOption({
                    value: data.nikk,
                    text: data.nikk + ' - ' + data.kk_name, // Fallback name
                    nama: data.kk_name
                });
                tSelect.setValue(data.nikk);
            }
            
            document.getElementById('codeId').value = data.code_id;
        }


        // Config
        const appProfileName = "<?= $profil['nama'] ?? 'JIMPITAN Warga' ?>";
        
        // QR Logic
        const qrModal = document.getElementById('qrModal');
        let currentQRName = '';
        let currentQRText = '';

        function closeModal() {
            modal.classList.add('hidden');
        }

        function showQR(code, name) {
            document.getElementById('qrName').innerText = name;
            currentQRName = name;
            currentQRText = code;
            
            const container = document.getElementById('qrcode');
            container.innerHTML = ''; // Clear previous
            
            if (!code) return Swal.fire('Error', 'Kode ID kosong', 'warning');

            new QRCode(container, {
                text: code,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });

            qrModal.classList.remove('hidden');
        }

        function closeQRModal() {
            qrModal.classList.add('hidden');
        }

        function downloadQR() {
            // Show loading
            Swal.fire({
                title: 'Memproses Gambar...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Small delay to allow UI to render loading state
            setTimeout(() => {
                try {
                    const qrImg = document.querySelector('#qrcode img');
                    if (!qrImg || !qrImg.src) {
                        throw new Error('Gambar QR Code belum siap. Coba lagi.');
                    }

                    // Dimensions - Reduced for Mobile Stability
                    const scale = 1.0; // Standard Res (400x600) prevents memory crash on mobile
                    const cardWidth = 400 * scale;
                    const cardHeight = 600 * scale;

                    const canvas = document.createElement('canvas');
                    canvas.width = cardWidth;
                    canvas.height = cardHeight;
                    const ctx = canvas.getContext('2d');
                    
                    // Apply scale
                    ctx.scale(scale, scale);

                    // 1. Background White
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, 400, 600);

                    // 2. Header (Gradient Indigo)
                    const grd = ctx.createLinearGradient(0, 0, 400, 0);
                    grd.addColorStop(0, '#4338ca'); // Indigo 700
                    grd.addColorStop(1, '#6366f1'); // Indigo 500
                    ctx.fillStyle = grd;
                    ctx.fillRect(0, 0, 400, 100);

                    // Header Arc (Decoration)
                    ctx.fillStyle = '#ffffff';
                    ctx.beginPath();
                    ctx.moveTo(0, 100);
                    ctx.quadraticCurveTo(200, 120, 400, 100);
                    ctx.lineTo(400, 130);
                    ctx.lineTo(0, 130);
                    ctx.fill();

                    // Header Title
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 22px "Segoe UI", sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText(appProfileName, 200, 60);

                    // 3. Nama KK
                    ctx.fillStyle = '#1e293b'; // Slate 800
                    ctx.font = 'bold 26px "Segoe UI", sans-serif';
                    // Handle long names simple ellipsis loop
                    let name = currentQRName;
                    if (name.length > 25) name = name.substring(0, 24) + '...';
                    ctx.fillText(name, 200, 160);

                    // 4. QR Code Container & Image
                    const qrSize = 250;
                    const qrY = 190;
                    
                    // Shadow box for QR
                    ctx.shadowColor = "rgba(0, 0, 0, 0.1)";
                    ctx.shadowBlur = 15;
                    ctx.fillStyle = "white";
                    ctx.roundRect((400 - qrSize)/2 - 10, qrY - 10, qrSize + 20, qrSize + 20, 15);
                    ctx.fill();
                    ctx.shadowColor = "transparent";
                    
                    // Border
                    ctx.strokeStyle = '#e2e8f0'; 
                    ctx.lineWidth = 2;
                    ctx.stroke();

                    // Image
                    ctx.drawImage(qrImg, (400 - qrSize) / 2, qrY, qrSize, qrSize);

                    // 5. Code ID
                    ctx.fillStyle = '#64748b'; // Slate 500
                    ctx.font = 'bold 24px monospace';
                    ctx.fillText(currentQRText, 200, 490);

                    // 6. Footer
                    ctx.fillStyle = '#94a3b8'; // Slate 400
                    ctx.font = 'italic 14px "Segoe UI", sans-serif';
                    ctx.fillText('Scan kartu ini untuk pembayaran Jimpitan', 200, 560);

                    // Download Logic (toDataURL is stable)
                    const dataUrl = canvas.toDataURL('image/png', 0.9); // 0.9 quality
                    const link = document.createElement('a');
                    link.download = `KartuJimpitan_${currentQRName.replace(/[^a-z0-9]/gi, '_')}.png`;
                    link.href = dataUrl;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                     
                    Swal.close(); // Success

                } catch (error) {
                    console.error("Premium Card Error:", error);
                    
                    // Fallback: Download Raw QR (Classic Mode)
                    try {
                        let downloadSrc = '';
                        
                        // Check for Image
                        const rawImg = document.querySelector('#qrcode img');
                        if(rawImg && rawImg.src) {
                            downloadSrc = rawImg.src;
                        } else {
                            // Check for Canvas (QRCode.js often uses this)
                            const rawCanvas = document.querySelector('#qrcode canvas');
                            if(rawCanvas) {
                                downloadSrc = rawCanvas.toDataURL("image/png");
                            }
                        }

                        if(downloadSrc) {
                            const link = document.createElement('a');
                            link.download = `QR_Simple_${currentQRName.replace(/[^a-z0-9]/gi, '_')}.png`;
                            link.href = downloadSrc;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            Swal.close();
                            
                            // Optional: Notify user they got the simple version
                            setTimeout(() => Swal.fire('Info', 'HP tidak mendukung Kartu Premium, mendownload QR standar.', 'info'), 500);
                            return;
                        }
                    } catch(e) {
                        console.error("Fallback Error:", e);
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal menyimpan gambar. Error: ' + error.message
                    });
                }
            }, 500); // 500ms delay
        }

        // Sync NIKK to Code ID (Handled in TomSelect event now)

        function generateRandomCode() {
            // Deprecated/Unused
        }

        // Form Submit
        form.onsubmit = async (e) => {
            e.preventDefault();
            const url = isEdit ? '/kk/update' : '/kk/store';
            const formData = new FormData(form);

            try {
                const response = await fetch(url, { method: 'POST', body: formData });
                const res = await response.json();

                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        };

        // Delete Logic
        async function confirmDelete(id, name) {
            const result = await Swal.fire({
                title: 'Hapus Data KK?',
                text: `Data ${name} akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);
                try {
                    const response = await fetch('/kk/delete', { method: 'POST', body: formData });
                    const res = await response.json();
                    if (res.status === 'success') location.reload();
                    else Swal.fire('Gagal', res.message, 'error');
                } catch (e) {
                    Swal.fire('Error', 'Sistem bermasalah', 'error');
                }
            }
        }

        // Search Logic
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.kk-card');
        const emptyState = document.getElementById('emptyState');

        function performSearch(q) {
            let hasResult = false;
            cards.forEach(card => {
                const name = card.dataset.name;
                const code = card.dataset.code;
                if (name.includes(q) || code.includes(q)) {
                    card.style.display = 'flex';
                    hasResult = true;
                } else {
                    card.style.display = 'none';
                }
            });
            emptyState.style.display = hasResult ? 'none' : 'block';
            
            if (q.length > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        searchInput.oninput = (e) => performSearch(e.target.value.toLowerCase());
        clearBtn.onclick = () => {
            searchInput.value = '';
            performSearch('');
            searchInput.focus();
        };
    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
