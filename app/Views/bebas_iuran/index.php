<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= $profil['nama'] ?? 'Jimpitan App' ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.default.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

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
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); }
        /* TomSelect Dropdown Width */
        .ts-wrapper .ts-dropdown { width: 100% !important; left: 0 !important; right: 0 !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300 font-sans">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Bebas Iuran</h1>
        </div>
        <div class="flex items-center gap-2">
            <button id="themeToggle" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
            </button>
            <button onclick="openModal()" class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg hover:bg-indigo-700 transition-all">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <!-- Search & Grid -->
        <div class="mb-6 animate__animated animate__fadeIn">
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" id="searchInput" placeholder="Cari warga..." 
                       class="w-full pl-11 pr-12 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl shadow-sm text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
            </div>
        </div>

        <div id="exemptGrid" class="space-y-6">
            <?php if(empty($exemptions)): ?>
                <div class="text-center py-20 animate__animated animate__fadeIn">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fas fa-inbox text-4xl"></i>
                    </div>
                    <p class="text-slate-500">Belum ada data pengecualian.</p>
                </div>
            <?php else: ?>
                <?php 
                // Group by Tariff Name
                $grouped = [];
                foreach($exemptions as $ex) {
                    $grouped[$ex['nama_tarif']][] = $ex;
                }
                ksort($grouped);
                ?>

                <?php foreach($grouped as $tarifName => $items): ?>
                    <div class="animate__animated animate__fadeIn">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3 pl-1 border-l-4 border-indigo-500 ml-1">
                            <?= $tarifName ?> 
                            <span class="text-xs font-normal text-slate-400 normal-case ml-1">(<?= count($items) ?> Warga)</span>
                        </h3>
                        <div class="space-y-2">
                            <?php $no = 1; foreach($items as $item): ?>
                            <div class="exempt-card glass rounded-xl p-2.5 flex items-center justify-between group hover:bg-white dark:hover:bg-slate-800 transition-all border border-transparent hover:border-indigo-500/20"
                                 data-name="<?= strtolower($item['kk_name']) ?>"
                                 data-nikk="<?= strtolower($item['nikk']) ?>">
                                
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center font-bold text-xs font-mono shrink-0">
                                        <?= $no++ ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-800 dark:text-white truncate text-sm leading-tight"><?= $item['kk_name'] ?></h4>
                                        <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                            <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400"><?= $item['nikk'] ?></span>
                                        </div>
                                    </div>
                                </div>

                                <button onclick="deleteItem(<?= $item['id'] ?>)" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-700/50 text-slate-400 hover:bg-rose-100 hover:text-rose-500 dark:hover:bg-rose-900/30 dark:hover:text-rose-400 transition-all flex items-center justify-center shrink-0 ml-2">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal -->
    <div id="modal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 animate__animated animate__zoomIn animate__faster">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Tambah Data</h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="form" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Cari Warga</label>
                    <select id="selectWarga" name="nikk" placeholder="Ketik nama atau NIKK..." required></select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jenis Tarif</label>
                    <select name="kode_tarif" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-white appearance-none" required>
                        <option value="">-- Pilih Tarif --</option>
                        <?php foreach($tariffs as $t): ?>
                            <option value="<?= $t['kode_tarif'] ?>"><?= $t['nama_tarif'] ?> (Rp <?= number_format($t['tarif'],0,',','.') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg hover:bg-indigo-700 transition-all mt-4">
                    Simpan
                </button>
            </form>
        </div>
    </div>

    <script>
        let tsWarga;

        document.addEventListener('DOMContentLoaded', () => {
            tsWarga = new TomSelect('#selectWarga', {
                valueField: 'nikk',
                labelField: 'kk_name',
                searchField: ['kk_name', 'nikk'],
                load: function(query, callback) {
                    if (!query.length) return callback();
                    fetch('/bebas-iuran/search-warga?q=' + encodeURIComponent(query), { skipLoader: true })
                        .then(response => response.json())
                        .then(json => callback(json)).catch(()=>{ callback() });
                },
                render: {
                    option: function(item, escape) {
                        return `<div class="py-2 px-3 hover:bg-indigo-50 dark:hover:bg-slate-700">
                                <span class="font-bold block text-sm text-slate-700 dark:text-slate-200">${escape(item.kk_name)}</span>
                                <span class="text-xs text-slate-400 block mt-0.5">${escape(item.nikk)}</span>
                            </div>`;
                    },
                    item: function(item, escape) {
                        return `<div class="flex items-center gap-2">
                                <span class="truncate">${escape(item.kk_name)}</span>
                                <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">${escape(item.nikk)}</span>
                            </div>`;
                    }
                }
            });
        });

        const modal = document.getElementById('modal');
        function openModal() { modal.classList.remove('hidden'); if(tsWarga) tsWarga.clear(); }
        function closeModal() { modal.classList.add('hidden'); }

        document.getElementById('form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const res = await fetch('/bebas-iuran/store', { method: 'POST', body: formData });
                const json = await res.json();
                if (json.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', showConfirmButton: false, timer: 1000 }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', json.message, 'error');
                }
            } catch (err) { Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'); }
        };

        function deleteItem(id) {
            Swal.fire({
                title: 'Hapus?',
                text: "Data pengecualian akan dihapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                confirmButtonText: 'Ya, Hapus'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const fd = new FormData(); fd.append('id', id);
                    const res = await fetch('/bebas-iuran/delete', { method: 'POST', body: fd });
                    const json = await res.json();
                    if (json.status === 'success') location.reload();
                    else Swal.fire('Gagal', json.message, 'error');
                }
            });
        }

        // Search Logic
        const searchInput = document.getElementById('searchInput');
        const cards = document.querySelectorAll('.exempt-card');
        const grid = document.getElementById('exemptGrid');

        if(searchInput) {
            searchInput.oninput = (e) => {
                const q = e.target.value.toLowerCase();
                let hasVisible = false;
                
                cards.forEach(card => {
                    const name = card.dataset.name;
                    const nikk = card.dataset.nikk;
                    if(name.includes(q) || nikk.includes(q)) {
                        card.style.display = 'flex';
                        hasVisible = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
            };
        }

        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        if (themeToggle) {
            themeToggle.onclick = () => {
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            };
        }

        // Sync Theme Across Tabs
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                if (e.newValue === 'dark') html.classList.add('dark');
                else html.classList.remove('dark');
            }
        });
    </script>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
