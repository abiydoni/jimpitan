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
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300 pb-20">

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Manajemen Pengurus</h1>
        </div>
        <button id="themeToggle" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        
        <!-- Action Bar -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold dark:text-white">Daftar Pengurus</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola jabatan dan akses menu</p>
            </div>
            <button onclick="openModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 flex items-center gap-2 transition-all active:scale-95">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Tambah Pengurus</span>
            </button>
        </div>

        <!-- Role List -->
        <div class="space-y-1">
            <?php foreach ($pengurus as $p): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-3 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-slate-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-lg">
                            <?= strtoupper(substr($p['nama_pengurus'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-base">
                                <?= $p['nama_pengurus'] ?>
                            </h3>
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <span>ID: <?= $p['id'] ?></span>
                                <?php if(!empty($p['kode_tarif'])): ?>
                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                    <span class="font-mono text-indigo-500 dark:text-indigo-400"><?= $p['kode_tarif'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button onclick="editPengurus(<?= $p['id'] ?>)" class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white flex items-center justify-center transition-all">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        
                        <button onclick="deletePengurus(<?= $p['id'] ?>, '<?= $p['nama_pengurus'] ?>')" class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-slate-700 text-rose-500 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-600 flex items-center justify-center transition-all">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Modal Form -->
    <div id="pengurusModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-[2rem] shadow-2xl transform transition-all scale-95 opacity-0 animate__animated animate__zoomIn animate__faster" id="modalContent">
                <div class="p-6 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Pengurus</h3>
                        <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="pengurusForm" onsubmit="handleFormSubmit(event)" class="space-y-6">
                        <input type="hidden" name="id" id="formId">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nama Jabatan</label>
                            <input type="text" name="nama_pengurus" id="formNama" required 
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold placeholder-slate-300" placeholder="Ex: Bendahara">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Kode Tarif Utama / Default (Opsional)</label>
                            <div class="relative">
                                <select name="kode_tarif" id="formKodeTarif" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold appearance-none cursor-pointer">
                                    <option value="">-- Tidak Ada --</option>
                                    <?php foreach($tarifs as $t): ?>
                                        <option value="<?= $t['kode_tarif'] ?>"><?= $t['nama_tarif'] ?> (<?= $t['kode_tarif'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-slate-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1 ml-1">Jika dipilih, user akan langsung diarahkan ke tarif ini saat membuka menu tagihan/iuran.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 ml-1">Akses Menu</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1 custom-scrollbar">
                                <?php foreach($menus as $m): ?>
                                    <div class="relative flex flex-col p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 transition-all group hover:bg-indigo-50 dark:hover:bg-slate-800">
                                        <label class="flex items-center cursor-pointer mb-2">
                                            <input type="checkbox" name="menus[]" value="<?= $m['kode'] ?>" class="peer w-4 h-4 text-indigo-600 bg-slate-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" onchange="toggleAccess(this, 'access_<?= $m['kode'] ?>')">
                                            <span class="ml-2 text-sm text-slate-700 dark:text-slate-300 font-medium group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                                <?= $m['nama'] ?>
                                            </span>
                                        </label>
                                        
                                        <?php 
                                            // Check if this is an "Iuran" or "Tagihan" menu
                                            $isIuran = (stripos($m['nama'], 'iuran') !== false || stripos($m['nama'], 'tagihan') !== false); 
                                        ?>
                                        
                                        <!-- Access Level Selector & Tariff -->
                                        <div id="access_<?= $m['kode'] ?>" class="hidden px-2 mt-2 space-y-3 border-t border-slate-100 dark:border-slate-700 pt-2 text-sm">
                                            
                                            <!-- Level Access -->
                                            <div class="flex items-center space-x-3">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="access_types[<?= $m['kode'] ?>]" value="full" checked class="text-indigo-600 focus:ring-indigo-500 w-3 h-3">
                                                    <span class="ml-1.5 text-xs text-slate-600 dark:text-slate-400">Full</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="access_types[<?= $m['kode'] ?>]" value="view" class="text-indigo-600 focus:ring-indigo-500 w-3 h-3">
                                                    <span class="ml-1.5 text-xs text-slate-600 dark:text-slate-400">View Only</span>
                                                </label>
                                            </div>

                                            <!-- Tariff Access (Only for Iuran) -->
                                            <?php if ($isIuran): ?>
                                            <div class="bg-indigo-50 dark:bg-slate-800 rounded-lg p-3">
                                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase">Akses Jenis Iuran:</p>
                                                <div class="grid grid-cols-1 gap-2 max-h-40 overflow-y-auto custom-scrollbar">
                                                    <?php foreach($tarifs as $t): ?>
                                                        <label class="flex items-center">
                                                            <input type="checkbox" name="akses_tarif[<?= $m['kode'] ?>][]" value="<?= $t['id'] ?>" class="w-3 h-3 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                                            <span class="ml-2 text-xs text-slate-700 dark:text-slate-300"><?= $t['nama_tarif'] ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 transition-all active:scale-[0.98]">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>
    <?= $this->include('partials/submit_guard') ?>

    <script>
        // Modal Logic
        const modal = document.getElementById('pengurusModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('pengurusForm');
        const modalContent = document.getElementById('modalContent');
        // Select all Checkboxes 
        // Note: We'll query them dynamically if needed, or use static list
        // const checkboxes = document.querySelectorAll('input[name="menus[]"]');

        function toggleAccess(checkbox, targetId) {
            const el = document.getElementById(targetId);
            if(checkbox.checked) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        }

        function openModal() {
            modalTitle.innerText = 'Tambah Pengurus';
            form.reset();
            document.getElementById('formId').value = '';
            document.getElementById('formKodeTarif').value = '';
            
            // Reset UI for checkboxes
            document.querySelectorAll('input[name="menus[]"]').forEach(cb => {
                cb.checked = false;
                // Get the access div
                const targetId = 'access_' + cb.value;
                document.getElementById(targetId).classList.add('hidden');
                // Reset radio to Full
                const fullRadio = document.querySelector(`input[name="access_types[${cb.value}]"][value="full"]`);
                if(fullRadio) fullRadio.checked = true;

                // Reset tariff checkboxes
                document.querySelectorAll(`input[name="akses_tarif[${cb.value}][]"]`).forEach(tcb => tcb.checked = false);
            });

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('opacity-100', 'scale-100');
            modalContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        async function editPengurus(id) {
            try {
                window.showLoader();
                const res = await fetch('/pengurus/get/' + id);
                const json = await res.json();
                
                if (json.status === 'success') {
                    modalTitle.innerText = 'Edit Pengurus';
                    document.getElementById('formId').value = json.data.id;
                    document.getElementById('formNama').value = json.data.nama_pengurus;
                    document.getElementById('formKodeTarif').value = json.data.kode_tarif || '';
                    
                    // Reset checks
                    document.querySelectorAll('input[name="menus[]"]').forEach(cb => {
                        cb.checked = false;
                        document.getElementById('access_' + cb.value).classList.add('hidden');
                    });
                    
                    // Apply checks and Access Types
                    const assigned = json.assigned_menus; // Array of objects {kode_menu, tipe_akses, ...}
                    
                    assigned.forEach(item => {
                       const cb = document.querySelector(`input[name="menus[]"][value="${item.kode_menu}"]`);
                       if(cb) {
                           cb.checked = true;
                           // Show access options
                           document.getElementById('access_' + item.kode_menu).classList.remove('hidden');
                           
                        // Set Radio
                           const type = item.tipe_akses || 'full';
                           const radio = document.querySelector(`input[name="access_types[${item.kode_menu}]"][value="${type}"]`);
                           if(radio) radio.checked = true;

                           // Set Tariffs
                           if(item.akses_tarif) {
                               const tarifIds = item.akses_tarif.split(',');
                               tarifIds.forEach(tid => {
                                   const tcb = document.querySelector(`input[name="akses_tarif[${item.kode_menu}][]"][value="${tid}"]`);
                                   if(tcb) tcb.checked = true;
                               });
                           }
                       }
                    });

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modalContent.classList.remove('opacity-0', 'scale-95');
                        modalContent.classList.add('opacity-100', 'scale-100');
                    }, 10);
                } else {
                    Swal.fire('Error', json.message, 'error');
                }
            } catch (err) {
                 Swal.fire('Error', 'Gagal memuat data pengurus', 'error');
            } finally {
                window.hideLoader();
            }
        }

        // CRUD
        async function handleFormSubmit(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const id = formData.get('id');
            const url = id ? '/pengurus/update' : '/pengurus/store';

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
                     let msg = json.message;
                    Swal.fire('Gagal', msg, 'error');
                    if(window.resetSubmitButtons) window.resetSubmitButtons();
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                if(window.resetSubmitButtons) window.resetSubmitButtons();
            } finally {
                window.hideLoader();
            }
        }

        async function deletePengurus(id, name) {
            const result = await Swal.fire({
                title: 'Hapus Pengurus?',
                text: `Anda akan menghapus "${name}".`,
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
                    
                    const res = await fetch('/pengurus/delete', {
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
        
        // Theme
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.onclick = () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            };
        }
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
</body>
</html>
