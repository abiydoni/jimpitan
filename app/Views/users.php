<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - <?= $profil['nama'] ?? 'Jimpitan App' ?></title>
    
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
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Fix SweetAlert2 z-index to stay above modals */
        .swal2-container {
            z-index: 2000 !important;
        }
        /* TomSelect Dropdown Width */
        .ts-wrapper .ts-dropdown {
            width: 100% !important;
            left: 0 !important;
            right: 0 !important;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300">
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" onclick="window.showLoader()" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text">Manajemen User</h1>
        </div>
        <div class="flex items-center space-x-3">
             <!-- Theme Toggle -->
             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
             <button onclick="openAddModal()" class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/30 active:scale-95 transition-all">
                <i class="fas fa-plus"></i>
             </button>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <!-- Search & Info -->
        <div class="mb-6 animate__animated animate__fadeIn">
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" id="userSearch" placeholder="Cari warga berdasarkan nama atau username..." 
                       class="w-full pl-11 pr-12 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl shadow-sm text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                <button id="clearSearch" class="hidden absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>

        <!-- User List -->
        <div id="userGrid" class="space-y-1.5">
            <?php 
            // Debug: Verify role hierarchy data (disabled)
            // echo '<pre style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">Current Role: ' . $currentUserRole . '</pre>';
            // echo '<pre style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">Role Weights: '; print_r($roleWeights); echo '</pre>';
            ?>
            <?php foreach($users as $u): ?>
                <div class="user-card bg-white dark:bg-slate-800 rounded-xl px-4 py-2 flex items-center justify-between shadow-sm animate__animated animate__fadeInUp border border-slate-100 dark:border-slate-800 hover:shadow-md transition-all" 
                     data-name="<?= strtolower($u['name']) ?>" 
                     data-username="<?= strtolower($u['user_name']) ?>">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 font-bold text-xs shrink-0">
                            <?= substr($u['name'] ?: $u['user_name'], 0, 1) ?>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-slate-800 dark:text-white text-sm truncate leading-tight">
                                <?= $u['name'] ?: $u['user_name'] ?>
                                <?php if(!empty($u['nikk'])): ?>
                                    <i class="fas fa-check-circle text-emerald-500 text-xs ml-0.5" title="Terverifikasi (NIKK: <?= $u['nikk'] ?>)"></i>
                                <?php endif; ?>
                            </h4>
                            <div class="flex items-center gap-2 mt-0.5 text-[10px]">
                                <span class="text-slate-400 font-mono">@<?= $u['user_name'] ?></span>
                                <span class="w-0.5 h-0.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                <span class="<?= $u['role'] === 's_admin' ? 'text-rose-500' : 'text-indigo-500' ?> font-bold uppercase tracking-wider">
                                    <?= $u['role'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0 ml-2">
                        <?php 
                        // Check if current user can manage this user based on role hierarchy
                        $targetWeight = $roleWeights[$u['role']] ?? 0;
                        $myWeight = $roleWeights[(string)$currentUserRole] ?? 0;
                        $isSelf = ($u['id_code'] == session()->get('id_code'));
                        
                        // Can EDIT users with equal or lower role weight (>= allows same level)
                        $canEdit = $myWeight >= $targetWeight;
                        
                        // Can DELETE users with equal or lower role weight, BUT NOT self
                        $canDelete = ($myWeight >= $targetWeight) && !$isSelf;
                        ?>
                        
                        <?php if($canEdit): ?>
                            <button onclick='openEditModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)' class="w-8 h-8 rounded-lg text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center justify-center transition-colors">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                        <?php endif; ?>
                        
                        <?php if($canDelete): ?>
                            <button onclick="confirmDelete('<?= $u['id_code'] ?>', '<?= $u['name'] ?>')" class="w-8 h-8 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-20">
            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                <i class="fas fa-user-slash text-4xl"></i>
            </div>
            <p class="text-slate-500">Tidak ada user ditemukan.</p>
        </div>
    </main>

    <!-- User Modal -->
    <div id="userModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeUserModal()"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 sm:p-8 animate__animated animate__zoomIn animate__faster">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalTitle" class="text-2xl font-bold text-slate-800 dark:text-white">Tambah User</h3>
                <button onclick="closeUserModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="userForm" class="space-y-2.5">
                <input type="hidden" name="id_code" id="userId">
                
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 ml-1">Username</label>
                    <input type="text" name="user_name" id="username" required 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 ml-1">Nama Lengkap</label>
                    <input type="text" name="name" id="fullName" required 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 ml-1">Tautkan NIKK (Opsional)</label>
                    <div class="relative">
                        <select name="nikk" id="nikkSelect" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                            <option value="">-- Cari NIKK atau Nama KK --</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 ml-1">Password <span id="pwdLabel" class="normal-case font-medium text-slate-400"></span></label>
                    <input type="password" name="password" id="password" 
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 ml-1">Role</label>
                        <select name="role" id="role" required 
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none">
                            <?php foreach($roles as $r): ?>
                                <?php 
                                // tb_role: 'name' column = role code (s_admin, admin, etc), 'remark' = display name
                                $roleCode = $r['name'] ?? $r['id'] ?? $r['role'] ?? '';
                                $roleName = $r['remark'] ?? $r['nama'] ?? $roleCode;
                                ?>
                                <?php if(($roleWeights[$roleCode] ?? 0) <= ($roleWeights[(string)$currentUserRole] ?? 0)): ?>
                                    <option value="<?= $roleCode ?>" <?= $roleCode === 'user' ? 'selected' : '' ?>><?= $roleName ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 ml-1">Shift Jaga</label>
                        <select name="shift" id="shift" 
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none">
                            <option value="-">-</option>
                            <option value="Monday">Senin</option>
                            <option value="Tuesday">Selasa</option>
                            <option value="Wednesday">Rabu</option>
                            <option value="Thursday">Kamis</option>
                            <option value="Friday">Jumat</option>
                            <option value="Saturday">Sabtu</option>
                            <option value="Sunday">Minggu</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 ml-1">Akses Tarif</label>
                        <select name="tarif" id="tarif" 
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none">
                            <option value="0">-- Tidak Ada Akses --</option>
                            <?php if(!empty($tarifs)): ?>
                                <?php foreach($tarifs as $t): ?>
                                    <option value="<?= $t['id'] ?>">Pengurus: <?= $t['nama_tarif'] ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <option value="99">Bendahara Umum (Jurnal Umum)</option>
                            <option value="100">Super Admin & Admin (Akses Penuh)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all mt-4">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>



    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };

        // Modal Logic
        const modal = document.getElementById('userModal');
        const form = document.getElementById('userForm');
        let isEdit = false;
        let tsNikk;

        document.addEventListener('DOMContentLoaded', () => {
             tsNikk = new TomSelect('#nikkSelect', {
                valueField: 'nikk', // Store NIKK
                labelField: 'text', // Display Text
                searchField: ['nama', 'nikk'],
                create: false,
                placeholder: "Cari NIKK atau Nama KK...",
                plugins: ['clear_button'],
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
                        return `<div class="py-2 px-3 hover:bg-indigo-50 dark:hover:bg-slate-700">
                                <span class="font-bold block text-sm text-slate-700 dark:text-slate-200">${escape(item.nama)}</span>
                                <span class="text-xs text-slate-400 block mt-0.5">${escape(item.nikk || '-')}</span>
                            </div>`;
                    },
                    item: function(item, escape) {
                        return `<div class="flex items-center gap-2">
                                <span class="truncate">${escape(item.nama)}</span>
                                <span class="text-xs text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">${escape(item.nikk || '-')}</span>
                            </div>`;
                    }
                }
            });
        });

        function openAddModal() {
            isEdit = false;
            document.getElementById('modalTitle').innerText = 'Tambah User';
            document.getElementById('pwdLabel').innerText = '(Min. 4 Karakter)';
            document.getElementById('password').required = true;
            document.getElementById('username').removeAttribute('readonly');
            document.getElementById('username').classList.remove('opacity-60', 'cursor-not-allowed');
            form.reset();
            if(tsNikk) tsNikk.clear(); // Clear TomSelect
            document.getElementById('userId').value = '';
            modal.classList.remove('hidden');
        }

        function openEditModal(user) {
            isEdit = true;
            document.getElementById('modalTitle').innerText = 'Edit User';
            document.getElementById('pwdLabel').innerText = '(Kosongkan jika tidak diubah)';
            document.getElementById('password').required = false;
            
            // Disable username field in edit mode
            document.getElementById('username').setAttribute('readonly', 'readonly');
            document.getElementById('username').classList.add('opacity-60', 'cursor-not-allowed');
            
            modal.classList.remove('hidden');

            document.getElementById('userId').value = user.id_code;
            document.getElementById('username').value = user.user_name;
            document.getElementById('fullName').value = user.name;
            document.getElementById('password').value = ''; // Clear password field
            
            // Set role value - need to wait for modal to be visible
            setTimeout(() => {
                const roleSelect = document.getElementById('role');
                roleSelect.value = user.role;
            }, 50);
            
            
            document.getElementById('shift').value = user.shift || '-';
            // Set Tarif
            document.getElementById('tarif').value = user.tarif || 0;

            // Set NIKK
            if(tsNikk) {
                tsNikk.clear();
                tsNikk.clearOptions();
                if(user.nikk) {
                    // We need to add option manually because it's remote loaded
                    // For display, we assume NIKK is enough context or we can pass name if available.
                    // Users table currently doesn't storing KK Name, only NIKK. 
                    // So we will display NIKK as label temporarily or just add option {nikk: user.nikk, text: user.nikk}
                    tsNikk.addOption({
                        nikk: user.nikk,
                        text: (user.linked_kk_name || 'Warga') + ' (' + user.nikk + ')',
                        nama: user.linked_kk_name || 'Warga'
                    });
                    tsNikk.addItem(user.nikk);
                }
            }
        }

        function closeUserModal() {
            modal.classList.add('hidden');
        }

        function openAddModal() {
            isEdit = false;
            document.getElementById('modalTitle').innerText = 'Tambah User';
            document.getElementById('pwdLabel').innerText = '(Min. 4 Karakter)';
            document.getElementById('password').required = true;
            document.getElementById('username').removeAttribute('readonly');
            document.getElementById('username').classList.remove('opacity-60', 'cursor-not-allowed');
            form.reset();
            if(tsNikk) tsNikk.clear(); // Clear TomSelect
            document.getElementById('userId').value = '';
            document.getElementById('tarif').value = 0; // Reset Tarif
            modal.classList.remove('hidden');
        }

        // CRUD Logic
        form.onsubmit = async (e) => {
            e.preventDefault();
            const url = isEdit ? '/users/update' : '/users/store';
            const formData = new FormData(form);

            try {
                const response = await fetch(url, { method: 'POST', body: formData });
                const res = await response.json();

                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false })
                        .then(() => location.reload());
                } else {
                    // Check if there are detailed validation errors
                    let errorMessage = res.message;
                    if (res.errors && typeof res.errors === 'object') {
                        const errorList = Object.values(res.errors).join('<br>');
                        errorMessage = `<div style="text-align: left;">${errorList}</div>`;
                    }
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Gagal', 
                        html: errorMessage 
                    });
                }
            } catch (err) {
                Swal.fire('Error', 'Sistem bermasalah', 'error');
            }
        };

        async function confirmDelete(id, name) {
            const result = await Swal.fire({
                title: 'Hapus User?',
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
                formData.append('id_code', id);
                try {
                    const response = await fetch('/users/delete', { method: 'POST', body: formData });
                    const res = await response.json();
                    if (res.status === 'success') location.reload();
                    else Swal.fire('Gagal', res.message, 'error');
                } catch (e) {
                    Swal.fire('Error', 'Sistem bermasalah', 'error');
                }
            }
        }

        // Search Logic
        const searchInput = document.getElementById('userSearch');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.user-card');
        const emptyState = document.getElementById('emptyState');

        function performSearch(q) {
            let hasResult = false;
            cards.forEach(card => {
                const name = card.dataset.name;
                const username = card.dataset.username;
                if (name.includes(q) || username.includes(q)) {
                    card.style.display = 'flex';
                    hasResult = true;
                } else {
                    card.style.display = 'none';
                }
            });
            emptyState.style.display = hasResult ? 'none' : 'block';
            
            // Toggle clear button
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
    <?= view('partials/loader') ?>
</body>
</html>
