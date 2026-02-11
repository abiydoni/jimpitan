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
            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Manajemen Role</h1>
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
                <h2 class="text-xl font-bold dark:text-white">Daftar Role User</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola level akses pengguna</p>
            </div>
            <button onclick="openModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 flex items-center gap-2 transition-all active:scale-95">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Tambah Role</span>
            </button>
        </div>

        <!-- Role List -->
        <div class="space-y-1">
            <?php foreach ($roles as $r): ?>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-2 border border-slate-100 dark:border-slate-700 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-slate-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-base">
                            <?= strtoupper(substr($r['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-sm">
                                <?= $r['remark'] ?>
                            </h3>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-1.5 py-0.5 rounded text-[9px] font-mono text-slate-500 dark:text-slate-400 lowercase">
                                    id: <?= $r['id'] ?>
                                </span>
                                <span class="bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider">
                                    <?= $r['name'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1.5">
                        <button onclick="editRole(<?= htmlspecialchars(json_encode($r)) ?>)" class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white flex items-center justify-center transition-all">
                            <i class="fas fa-edit text-[10px]"></i>
                        </button>
                        
                        <?php 
                        $protected = ['s_admin', 'admin', 'user', 'pengurus', 'warga'];
                        if (!in_array($r['name'], $protected)): 
                        ?>
                            <button onclick="deleteRole(<?= $r['id'] ?>, '<?= $r['remark'] ?>')" class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-slate-700 text-rose-500 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-600 flex items-center justify-center transition-all">
                                <i class="fas fa-trash-alt text-[10px]"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Modal Form -->
    <div id="roleModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-sm rounded-[2rem] shadow-2xl transform transition-all scale-95 opacity-0 animate__animated animate__zoomIn animate__faster" id="modalContent">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Role</h3>
                        <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="roleForm" onsubmit="handleFormSubmit(event)" class="space-y-4">
                        <input type="hidden" name="id" id="formId">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nama Tampilan (Remark)</label>
                            <input type="text" name="remark" id="formRemark" required 
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold placeholder-slate-300" placeholder="Ex: Bendahara">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Kode Role (System)</label>
                            <input type="text" name="name" id="formName" required 
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm placeholder-slate-300 font-mono" placeholder="Ex: bendahara">
                            <p class="text-[10px] text-slate-400 mt-1 ml-1">Gunakan huruf kecil, tanpa spasi (gunakan underscores).</p>
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
        const modal = document.getElementById('roleModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('roleForm');
        const modalContent = document.getElementById('modalContent');

        function openModal() {
            modalTitle.innerText = 'Tambah Role';
            form.reset();
            document.getElementById('formId').value = '';
            
            // Enable Inputs
            document.getElementById('formName').readOnly = false;
            document.getElementById('formName').classList.remove('opacity-60');

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

        function editRole(data) {
            modalTitle.innerText = 'Edit Role';
            document.getElementById('formId').value = data.id;
            document.getElementById('formName').value = data.name;
            document.getElementById('formRemark').value = data.remark;
            
            // Protect Core Role codes
            const protected = ['s_admin', 'admin', 'user', 'pengurus', 'warga'];
            if(protected.includes(data.name)) {
                document.getElementById('formName').readOnly = true;
                document.getElementById('formName').classList.add('opacity-60');
            } else {
                document.getElementById('formName').readOnly = false;
                document.getElementById('formName').classList.remove('opacity-60');
            }
            
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        // CRUD
        async function handleFormSubmit(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const id = formData.get('id');
            const url = id ? '/role/update' : '/role/store';

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
                     if(json.errors) msg += '<br>' + JSON.stringify(json.errors);
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

        async function deleteRole(id, name) {
            const result = await Swal.fire({
                title: 'Hapus Role?',
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
                    
                    const res = await fetch('/role/delete', {
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
