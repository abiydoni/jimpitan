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
            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Manajemen Menu</h1>
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
                <h2 class="text-xl font-bold dark:text-white">Daftar Menu</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Atur navigasi dan hak akses aplikasi</p>
            </div>
            <button onclick="openModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 flex items-center gap-2 transition-all active:scale-95">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Tambah Menu</span>
            </button>
        </div>

        <!-- Menu Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            <?php foreach ($menus as $m): ?>
                <div class="group bg-white dark:bg-slate-800 rounded-xl p-2.5 border border-slate-100 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 shadow-sm hover:shadow-md relative overflow-hidden">
                    
                    <!-- Background Decoration -->
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-50 dark:bg-indigo-900/20 rounded-full blur-2xl group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40 transition-colors"></div>

                    <div class="relative z-10 flex justify-between items-start">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-700 flex items-center justify-center text-lg text-indigo-600 dark:text-indigo-400 shadow-sm border border-slate-100 dark:border-slate-600">
                                <!-- Support both FontAwesome and Ionicons conditionally or just render class/name -->
                                <?php $icon = $m['ikon'] ?? 'cube-outline'; // Fallback ?>
                                <?php if(strpos($icon, 'fa-') !== false): ?>
                                    <i class="<?= $icon ?>"></i>
                                <?php else: ?>
                                    <ion-icon name="<?= $icon ?>" class="text-xl"></ion-icon>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-800 dark:text-white leading-tight"><?= $m['nama'] ?></h3>
                                <p class="text-[9px] font-mono text-slate-400 tracking-wide leading-tight mt-0.5 truncate max-w-[150px]">
                                    <?= $m['alamat_url'] ?? '#' ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pl-2">
                             <button onclick="editMenu(<?= htmlspecialchars(json_encode($m)) ?>)" class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white flex items-center justify-center transition-all shadow-sm" title="Edit">
                                <i class="fas fa-edit text-[10px]"></i>
                            </button>
                            <?php if (strtolower($m['nama']) !== 'setting menu' && $m['alamat_url'] !== '/menu'): ?>
                                <button onclick="deleteMenu(<?= $m['kode'] ?>, '<?= $m['nama'] ?>')" class="w-6 h-6 rounded-md bg-rose-50 dark:bg-slate-700 text-rose-500 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-600 flex items-center justify-center transition-all shadow-sm" title="Hapus">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-2.5 flex items-center justify-between border-t border-slate-50 dark:border-slate-700/50 pt-2">
                        <div class="flex flex-wrap gap-1">
                             <?php 
                                $roles = explode(',', $m['role_access']);
                                foreach($roles as $r): 
                                    if(empty($r)) continue;
                             ?>
                                <span class="px-1.5 py-0.5 text-[8px] font-bold uppercase rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    <?= $r ?>
                                </span>
                             <?php endforeach; ?>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer scale-75 origin-right">
                            <input type="checkbox" value="" class="sr-only peer" onchange="toggleStatus(<?= $m['kode'] ?>, this.checked)" <?= $m['status'] == 1 ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <!-- Modal Form -->
    <div id="menuModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl shadow-2xl transform transition-all scale-95 opacity-0 animate__animated animate__zoomIn animate__faster" id="modalContent">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50 rounded-t-2xl">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Menu</h3>
                    <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form id="menuForm" onsubmit="handleFormSubmit(event)" class="p-6 space-y-4">
                    <input type="hidden" name="kode" id="formKode">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">Nama Menu</label>
                        <input type="text" name="nama" id="formNama" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold" placeholder="Nama Menu">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">URL / Link</label>
                            <input type="text" name="alamat_url" id="formUrl" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm" placeholder="/example">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5 ml-1">Icon (Ionicons)</label>
                            <div class="relative">
                                <input type="text" name="ikon" id="formIcon" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm" placeholder="Ex: settings-outline" oninput="previewIcon(this.value)">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xl">
                                    <ion-icon name="star" id="iconPreview" class="text-slate-400"></ion-icon>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 ml-1">Hak Akses</label>
                        <div class="flex flex-wrap gap-2">
                            <?php $availRoles = ['s_admin', 'admin', 'pengurus', 'user', 'warga']; ?>
                            <?php foreach($availRoles as $role): ?>
                                <label class="inline-flex items-center cursor-pointer p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <input type="checkbox" name="roles[]" value="<?= $role ?>" class="form-checkbox text-indigo-600 rounded focus:ring-indigo-500 border-slate-300 dark:border-slate-600 dark:bg-slate-800">
                                    <span class="ml-2 text-xs font-bold uppercase text-slate-700 dark:text-slate-300"><?= $role ?></span>
                                </label>
                            <?php endforeach; ?>
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
    <?= $this->include('partials/submit_guard') ?>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>

    <script>
        // --- AUTO REGISTRATION & PERMISSIONS LOGIC ---
        const vapidPublicKey = 'BIb0u4eLioyZgzPJRmFAoI3LdD87wOR2_4L6CpqDmAyIeUK_JqfW17fT-Iy3C4zTlSlrEBZn2cjZ5vh68W0KdSk';
        const firebaseConfig = {
            apiKey: "AIzaSyCMO1z8UGvFNyOnzAV-dsx1VLjOtCAjtdc",
            authDomain: "jimpitan-app-a7by777.firebaseapp.com",
            projectId: "jimpitan-app-a7by777",
            storageBucket: "jimpitan-app-a7by777.firebasestorage.app",
            messagingSenderId: "53228839762",
            appId: "1:53228839762:web:ae75cb6fc64b9441ac108b",
            measurementId: "G-XG704TQRJ2"
        };
        
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        document.addEventListener('DOMContentLoaded', async () => {
            await checkAndRequestPermissions();
        });

        async function checkAndRequestPermissions() {
            // 1. Check Notification Permission
            if (!('serviceWorker' in navigator)) return;

            if (Notification.permission === 'default') {
                // Soft Ask for Notification
                await Swal.fire({
                    title: 'Aktifkan Fitur?',
                    html: 'Mohon izinkan <strong>Notifikasi</strong> agar aplikasi berjalan maksimal.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Izinkan',
                    cancelButtonText: 'Nanti',
                    confirmButtonColor: '#4f46e5',
                    allowOutsideClick: false
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        await Notification.requestPermission();
                        if (Notification.permission === 'granted') {
                            registerFCM(true);
                        }
                    }
                });
            } else if (Notification.permission === 'granted') {
                // Auto sync in background if already granted
                registerFCM(true);
            }

            // 2. Check Camera Permission (Optional, quiet check)
            /*
            try {
                // Only ask if we really need it or if status is prompt. 
                // Browsers don't support "checking" camera permission easily without triggering it.
                // We can query permission API.
                const camPerm = await navigator.permissions.query({ name: 'camera' });
                if (camPerm.state === 'prompt') {
                     // Maybe ask? Or wait until Scan QR feature is used.
                     // Generally better to ask contextually.
                }
            } catch(e) {}
            */
        }

        async function registerFCM(silent = false) {
            try {
                // Register SW if not already
                let registration = await navigator.serviceWorker.getRegistration();
                if(!registration) {
                    registration = await navigator.serviceWorker.register('<?= base_url("sw.js") ?>');
                    await navigator.serviceWorker.ready;
                }
                
                const token = await messaging.getToken({
                    serviceWorkerRegistration: registration,
                    vapidKey: vapidPublicKey
                });
                
                if (token) {
                    // console.log('FCM Auto-Sync:', token);
                    await sendFCMTokenToServer(token, silent);
                }
            } catch (err) {
                console.error('FCM Register Error:', err);
            }
        }

        async function sendFCMTokenToServer(token, silent = false) {
            try {
                const res = await fetch('<?= base_url("push/subscribe_fcm") ?>', {
                    method: 'POST',
                    body: JSON.stringify({ token: token, device_type: 'web' }),
                    headers: { 'Content-Type': 'application/json' },
                    skipLoader: true
                });
                if(res.ok && !silent) {
                    // Optional: Notify success
                }
            } catch (err) { }
        }
    </script>

    <script>
        // --- UI Logic ---
        
        // Modal Logic
        const modal = document.getElementById('menuModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('menuForm');
        const modalContent = document.getElementById('modalContent');

        function openModal() {
            modalTitle.innerText = 'Tambah Menu';
            form.reset();
            document.getElementById('formKode').value = '';
            previewIcon('');
            
            // Reset readonly state
            const formNama = document.getElementById('formNama');
            formNama.readOnly = false;
            formNama.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-slate-100', 'dark:bg-slate-800');
            
            // Uncheck all roles
            document.querySelectorAll('input[name="roles[]"]').forEach(cb => cb.checked = false);

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

        function editMenu(data) {
            modalTitle.innerText = 'Edit Menu';
            document.getElementById('formKode').value = data.kode;
            const formNama = document.getElementById('formNama');
            formNama.value = data.nama;
            
            // Check if Setting Menu -> Lock Name
            if (data.nama.toLowerCase() === 'setting menu' || data.alamat_url === '/menu') {
                formNama.readOnly = true;
                formNama.classList.add('opacity-60', 'cursor-not-allowed', 'bg-slate-100', 'dark:bg-slate-800');
            } else {
                 formNama.readOnly = false;
                 formNama.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-slate-100', 'dark:bg-slate-800');
            }

            document.getElementById('formUrl').value = data.alamat_url;
            document.getElementById('formIcon').value = data.ikon;
            previewIcon(data.ikon);
            
            // Handle Roles Checkboxes
            const roles = data.role_access ? data.role_access.split(',') : [];
            document.querySelectorAll('input[name="roles[]"]').forEach(cb => {
                cb.checked = roles.includes(cb.value);
            });
            
            modal.classList.remove('hidden');
             // Animation In
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function previewIcon(val) {
            const i = document.getElementById('iconPreview');
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
            const kode = formData.get('kode');
            const url = kode ? '/menu/update' : '/menu/store';

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
                    if(window.resetSubmitButtons) window.resetSubmitButtons();
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                if(window.resetSubmitButtons) window.resetSubmitButtons();
            } finally {
                window.hideLoader();
            }
        }

        async function deleteMenu(kode, name) {
            const result = await Swal.fire({
                title: 'Hapus Menu?',
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
                    formData.append('kode', kode);
                    
                    const res = await fetch('/menu/delete', {
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

        async function toggleStatus(kode, checked) {
            try {
                const formData = new FormData();
                formData.append('kode', kode);
                formData.append('status', checked ? 1 : 0);
                
                const res = await fetch('/menu/toggleStatus', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    skipLoader: true
                });
            } catch (err) {
                console.error(err);
            }
        }

        // Theme Toggle
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
