<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { dark: '#0f172a' }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); }
        .gradient-text { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .swal2-container { z-index: 2000 !important; }
        
        /* Tom Select Customization */
        .ts-control { border-radius: 1rem; padding: 0.8rem 1rem; border: none; background-color: #f8fafc; box-shadow: none; }
        .dark .ts-control { background-color: #1e293b; color: white; }
        .ts-dropdown { border-radius: 1rem; overflow: hidden; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .dark .ts-dropdown { background-color: #1e293b; color: white; }
        .dark .ts-dropdown .option { color: #cbd5e1; }
        .dark .ts-dropdown .active { background-color: #334155; color: white; }
        .ts-wrapper.focus .ts-control { box-shadow: 0 0 0 2px #6366f1; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm mb-6">
        <div class="flex items-center space-x-3">
            <a href="<?= base_url('/') ?>" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold gradient-text">Input Manual</h1>
        </div>
        <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
    </nav>

    <main class="max-w-md mx-auto px-4">
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 sm:p-8 shadow-xl border border-slate-100 dark:border-slate-700 animate__animated animate__fadeInUp">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-3 text-indigo-600 dark:text-indigo-400">
                    <i class="fas fa-keyboard text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Form Manual</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Gunakan fitur ini jika Scan QR gagal</p>
            </div>

            <form id="manualForm" class="space-y-4">
                <!-- Warga Search -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Cari Warga</label>
                    <select id="code_id" name="code_id" placeholder="Ketik Nama atau NIKK..." required></select>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tanggal Jimpitan</label>
                    <input type="date" name="jimpitan_date" id="jimpitan_date" value="<?= date('Y-m-d') ?>" required
                           class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white dark:[color-scheme:dark]">
                </div>

                <!-- Alasan -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Keterangan / Alasan (Wajib)</label>
                    <textarea name="alasan" id="alasan" rows="2" placeholder="Contoh: QR Code Rusak" required
                              class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center space-x-2">
                        <i class="fas fa-paper-plane text-sm"></i>
                        <span>Simpan Data</span>
                    </button>
                    <!-- <a href="/" class="block text-center mt-4 text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 font-medium transition-colors">
                        Kembali ke Menu Utama
                    </a> -->
                </div>
            </form>
        </div>
    </main>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };

        // Tom Select Initialization
        let tSelect;
        document.addEventListener('DOMContentLoaded', () => {
             tSelect = new TomSelect('#code_id', {
                valueField: 'value',
                labelField: 'nama', // Using 'nama' for cleaner display in dropdown
                searchField: ['nama', 'value'],
                create: false,
                placeholder: 'Ketik Nama Warga...',
                maxItems: 1,
                load: function(query, callback) {
                    if (!query.length) return callback();
                    fetch('/scan/search_target?q=' + encodeURIComponent(query), { skipLoader: true })
                        .then(response => response.json())
                        .then(json => {
                            callback(json);
                        }).catch(() => {
                            callback();
                        });
                },
                render: {
                    option: function(item, escape) {
                        return `<div class="py-1">
                                <span class="font-bold block text-sm">${escape(item.nama)}</span>
                                <span class="text-xs text-slate-500 block">${escape(item.value)}</span>
                            </div>`;
                    },
                    item: function(item, escape) {
                        return `<div title="${escape(item.value)}">${escape(item.nama)}</div>`;
                    }
                }
            });
        });

        // Form Submit
        const form = document.getElementById('manualForm');
        form.onsubmit = async (e) => {
            e.preventDefault();

            // 1. Basic Validation
            const codeId = document.getElementById('code_id').value;
            const date = document.getElementById('jimpitan_date').value;
            const alasan = document.getElementById('alasan').value;

            if(!codeId) {
                Swal.fire('Perhatian', 'Silakan pilih warga terlebih dahulu', 'warning');
                return;
            }

            if(!alasan.trim()) {
                 Swal.fire('Perhatian', 'Alasan / Keterangan wajib diisi.', 'warning');
                 return;
            }

            // 2. Confirmation
            // Get selected name for nicer message
            const selectedOption = tSelect.options[codeId];
            const wargaName = selectedOption ? selectedOption.nama : 'Warga Terpilih';

            // Format Date to Indonesia
            const dateObj = new Date(date);
            const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const dateIndo = new Intl.DateTimeFormat('id-ID', options).format(dateObj);

            const result = await Swal.fire({
                title: 'Konfirmasi Simpan',
                html: `Simpan jimpitan manual untuk <b>${wargaName}</b> pada tanggal<br><b class="text-indigo-600 text-lg">${dateIndo}</b>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            // 3. Process
            const formData = new FormData(form);
            
            // Show Loading
            Swal.fire({
                title: 'Menyimpan...',
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch('/scan/storeManual', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    // Redirect back to Home
                    window.location.href = '/';
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        };
    </script>
    <style>

    

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
