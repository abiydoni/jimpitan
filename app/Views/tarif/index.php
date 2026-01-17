<!DOCTYPE html>
<html lang="id">
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
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
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
         body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
         .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
         .dark .glass { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); }
         /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #475569; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300 pb-20">

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Manajemen Tarif</h1>
        </div>
        <button id="themeToggle" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        
        <!-- Action Bar -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold dark:text-white">Daftar Tarif</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola jenis iuran dan tarif warga</p>
            </div>
            <button onclick="openModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 flex items-center gap-2 transition-all active:scale-95">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Tambah Tarif</span>
            </button>
        </div>

        <!-- Tariff Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            <?php foreach ($tariffs as $t): ?>
                <div class="group bg-white dark:bg-slate-800 rounded-xl p-2.5 border border-slate-100 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 shadow-sm hover:shadow-md relative overflow-hidden">
                    
                    <!-- Background Decoration -->
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-50 dark:bg-indigo-900/20 rounded-full blur-2xl group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 transition-colors"></div>

                    <div class="relative z-10 flex justify-between items-start">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-lg text-indigo-600 dark:text-indigo-400 shadow-sm border border-slate-100 dark:border-slate-600">
                                <ion-icon name="<?= $t['icon'] ?>" class="text-xl"></ion-icon>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-800 dark:text-white leading-tight"><?= $t['nama_tarif'] ?></h3>
                                <p class="text-[9px] font-mono text-slate-400 uppercase tracking-wide leading-tight mt-0.5">
                                    <?= $t['kode_tarif'] ?> • 
                                    <?php 
                                        $methods = [0 => 'Nonaktif', 1 => 'Bulanan', 2 => 'Tahunan', 3 => 'Sekali'];
                                        echo $methods[$t['metode']] ?? 'Unknown';
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pl-2">
                             <button onclick="editTarif(<?= htmlspecialchars(json_encode($t)) ?>)" class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white flex items-center justify-center transition-all shadow-sm" title="Edit">
                                <i class="fas fa-edit text-[10px]"></i>
                            </button>
                            <button onclick="deleteTarif(<?= $t['id'] ?>, '<?= $t['nama_tarif'] ?>')" class="w-6 h-6 rounded-md bg-rose-50 dark:bg-slate-700 text-rose-500 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-600 flex items-center justify-center transition-all shadow-sm" title="Hapus">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-2.5 flex items-center justify-between border-t border-slate-50 dark:border-slate-700/50 pt-2">
                        <div class="flex items-baseline gap-1">
                             <p class="text-[9px] text-slate-400 font-bold uppercase">Rp</p>
                             <p class="text-base font-black text-slate-700 dark:text-slate-200">
                                <?= number_format($t['tarif'], 0, ',', '.') ?>
                             </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer scale-75 origin-right">
                            <input type="checkbox" value="" class="sr-only peer" onchange="toggleStatus(<?= $t['id'] ?>, this.checked)" <?= $t['status'] == 1 ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Modal Form -->
    <div id="tarifModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl transform transition-all scale-95 opacity-0 animate__animated animate__zoomIn animate__faster" id="modalContent">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 rounded-t-2xl">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Tarif</h3>
                    <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="tarifForm" onsubmit="handleFormSubmit(event)" class="p-6 space-y-4">
                    <input type="hidden" name="id" id="formId">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">Kode Tarif</label>
                            <input type="text" name="kode_tarif" id="formKode" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-bold font-mono" placeholder="Ex: JIM01">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">Icon (Ionicons Name)</label>
                            <div class="relative">
                                <input type="text" name="icon" id="formIcon" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm" placeholder="Ex: cube-outline" oninput="previewIcon(this.value)">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xl">
                                    <ion-icon name="star" id="iconPreview" class="text-slate-400"></ion-icon>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">Nama Tarif</label>
                        <input type="text" name="nama_tarif" id="formNama" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold" placeholder="Nama Iuran">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">Nominal (Rp)</label>
                            <input type="number" name="tarif" id="formNominal" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-bold" placeholder="0">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">Metode Bayar</label>
                            <select name="metode" id="formMetode" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold cursor-pointer appearance-none">
                                <option value="1">Bulanan (Rutin)</option>
                                <option value="2">Tahunan</option>
                                <option value="3">Satu Kali (Insidentil)</option>
                                <option value="0">Nonaktif / Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-[0.98]">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

    <script>
        // --- UI Logic ---
        


        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.onclick = () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            };
        }
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');

        // Modal Logic
        const modal = document.getElementById('tarifModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('tarifForm');
        const modalContent = document.getElementById('modalContent');

        function openModal() {
            modalTitle.innerText = 'Tambah Tarif';
            form.reset();
            document.getElementById('formId').value = '';
            
            // Kode Tarif Writable
            const kodeInput = document.getElementById('formKode');
            kodeInput.readOnly = false;
            kodeInput.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'cursor-not-allowed', 'opacity-60');
            kodeInput.classList.add('bg-slate-50', 'dark:bg-slate-900');

            previewIcon('');
            
            modal.classList.remove('hidden');
            // Animation In
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeModal() {
            // Animation Out
            modalContent.classList.remove('opacity-100', 'scale-100');
            modalContent.classList.add('opacity-0', 'scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        function editTarif(data) {
            modalTitle.innerText = 'Edit Tarif';
            document.getElementById('formId').value = data.id;
            
            // Kode Tarif Readonly
            const kodeInput = document.getElementById('formKode');
            kodeInput.value = data.kode_tarif;
            kodeInput.readOnly = true;
            kodeInput.classList.remove('bg-slate-50', 'dark:bg-slate-900');
            kodeInput.classList.add('bg-slate-100', 'dark:bg-slate-800', 'cursor-not-allowed', 'opacity-60');

            document.getElementById('formNama').value = data.nama_tarif;
            document.getElementById('formIcon').value = data.icon;
            document.getElementById('formNominal').value = data.tarif;
            document.getElementById('formMetode').value = data.metode;
            previewIcon(data.icon);
            
            modal.classList.remove('hidden');
             // Animation In
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function previewIcon(val) {
            const i = document.getElementById('iconPreview');
            // Reset logic for ion-icon: just change the name attribute
            if (val && val.trim() !== '') {
                i.setAttribute('name', val);
                i.classList.remove('text-slate-400');
                i.classList.add('text-indigo-500');
            } else {
                i.setAttribute('name', 'star'); // Default
                i.classList.remove('text-indigo-500');
                i.classList.add('text-slate-400');
            }
        }

        // --- CRUD Logic ---

        async function handleFormSubmit(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const id = formData.get('id');
            const url = id ? '/tarif/update' : '/tarif/store';

            try {
                window.showLoader();
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();

                if (json.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: json.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: json.message + (json.errors ? '<br><small>' + JSON.stringify(json.errors) + '</small>' : '')
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            } finally {
                window.hideLoader();
            }
        }

        async function deleteTarif(id, name) {
            const result = await Swal.fire({
                title: 'Hapus Tarif?',
                text: `Anda akan menghapus "${name}". Data yang sudah ada mungkin terpengaruh.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                try {
                    window.showLoader();
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    const res = await fetch('/tarif/delete', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await res.json();

                    if (json.status === 'success') {
                        Swal.fire('Terhapus!', json.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', json.message, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                } finally {
                    window.hideLoader();
                }
            }
        }

        async function toggleStatus(id, checked) {
            try {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status', checked ? 1 : 0);
                
                // Silent update (skip global loader if possible, or use minimal feedback)
                // Using standard fetch here, user sees toggle animation immediately
                const res = await fetch('/tarif/toggleStatus', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    skipLoader: true
                });
                // Optional: handle error by reverting toggle
            } catch (err) {
                console.error(err);
            }
        }
    </script>
</body>
</html>
