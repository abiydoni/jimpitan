<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Jaga - <?= $profil['nama'] ?? 'Jimpitan App' ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
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
        .day-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .day-card.today {
            border: 2px solid #6366f1;
            transform: scale(1.02);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
        }
        .day-card.today {
            border: 2px solid #6366f1;
            transform: scale(1.02);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
        }
        /* Fix SweetAlert2 z-index to stay above modals */
        .swal2-container {
            z-index: 2000 !important;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
            document.body.classList.add('dark');
        }
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text">Jadwal Jaga</h1>
        </div>
        <div class="flex items-center space-x-3">
             <!-- Theme Toggle -->
             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
             <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <i class="fas fa-calendar-alt text-sm"></i>
             </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <!-- Hero Title -->
        <!-- <div class="mb-8 animate__animated animate__fadeIn">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Daftar Petugas Jaga</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Jadwal ronda rutin warga <?= $profil['alamat'] ?? '' ?> demi keamanan bersama.</p>
        </div> -->

        <!-- Schedule Grid -->
        <div class="space-y-2 animate__animated animate__fadeInUp">
            <?php 
            $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
            $dayNames = [
                "Monday" => "Senin",
                "Tuesday" => "Selasa",
                "Wednesday" => "Rabu",
                "Thursday" => "Kamis",
                "Friday" => "Jumat",
                "Saturday" => "Sabtu",
                "Sunday" => "Minggu"
            ];
            $today = date('l');
            ?>

            <?php foreach($days as $day): ?>
                <?php if(isset($jadwal[$day])): ?>
                    <div class="day-card glass rounded-xl p-3 <?= $day === $today ? 'today active shadow-xl' : 'shadow-sm' ?>">
                        <div class="flex justify-between items-start mb-1.5">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 <?= $day === $today ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' ?> rounded-xl flex items-center justify-center shadow-inner transition-colors">
                                    <i class="fas fa-moon text-base"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 dark:text-white text-sm sm:text-base"><?= $dayNames[$day] ?></h3>
                                    <?php if($day === $today): ?>
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/40 rounded-full">Hari Ini</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-[10px] font-medium text-slate-400 mr-1"><?= count($jadwal[$day]) ?> Petugas</span>
                                <?php if($canManage): ?>
                                    <button onclick="openSelectUserModal('<?= $day ?>')" class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center hover:bg-emerald-200 transition-colors">
                                        <i class="fas fa-plus text-[10px]"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-0.5">
                            <?php foreach($jadwal[$day] as $user): ?>
                                <div class="group relative flex items-center space-x-2 px-2 py-1 bg-white/50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800 shadow-sm">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center text-[10px] text-white font-bold shrink-0">
                                        <?= substr($user['name'], 0, 1) ?>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 flex-grow"><?= $user['name'] ?></span>
                                    
                                    <?php if($canManage): ?>
                                        <button onclick="removeUserFromSchedule('<?= $user['id'] ?>', '<?= $user['name'] ?>')" class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-500 dark:text-rose-400 flex items-center justify-center transition-all">
                                            <i class="fas fa-times text-[10px]"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if(empty($jadwal)): ?>
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fas fa-calendar-times text-4xl"></i>
                    </div>
                    <p class="text-slate-500">Belum ada jadwal yang diatur.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Bottom Nav (Simplified for this page) -->



    <!-- Management Modal (Admin Only) -->
    <?php if($canManage): ?>
    <div id="managementModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeManagementModal()"></div>
        <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl p-6 animate__animated animate__zoomIn animate__faster">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white" id="modalTitle">Pilih Petugas</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Jadwal Hari: <span id="targetDayName" class="font-bold text-indigo-600">Senin</span></p>
                </div>
                <button onclick="closeManagementModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Search -->
            <div class="relative mb-6">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="userSearch" placeholder="Cari nama warga..." class="w-full pl-11 pr-12 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                <button id="clearSearch" class="hidden absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <!-- User List -->
            <div id="userList" class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                <div class="flex items-center justify-center py-10 text-slate-400">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i>
                    <span>Memuat data...</span>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    </style>
    <?php endif; ?>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const body = document.body;

        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            body.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };

        <?php if($canManage): ?>
        let currentTargetDay = '';
        let allUsers = [];

        async function openSelectUserModal(day) {
            currentTargetDay = day;
            const dayNames = {
                "Monday": "Senin", "Tuesday": "Selasa", "Wednesday": "Rabu", 
                "Thursday": "Kamis", "Friday": "Jumat", "Saturday": "Sabtu", "Sunday": "Minggu"
            };
            document.getElementById('targetDayName').innerText = dayNames[day];
            document.getElementById('managementModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            if (allUsers.length === 0) {
                try {
                    const response = await fetch('/jadwal/get_users', { skipLoader: true });
                    allUsers = await response.json();
                } catch (e) {
                    console.error('Failed to fetch users', e);
                }
            }
            renderUserList();
        }

        function closeManagementModal() {
            document.getElementById('managementModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('userSearch').value = '';
        }

        function renderUserList(search = '') {
            const container = document.getElementById('userList');
            const filtered = allUsers.filter(u => 
                u.name.toLowerCase().includes(search.toLowerCase()) && u.shift !== currentTargetDay
            );

            if (filtered.length === 0) {
                container.innerHTML = `<div class="text-center py-10 text-slate-400 text-sm">Tidak ada warga ditemukan</div>`;
                return;
            }

            container.innerHTML = filtered.map(u => `
                <div onclick="updateUserShift('${u.id}', '${currentTargetDay}')" class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                            ${u.name.substring(0, 1).toUpperCase()}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-white">${u.name}</p>
                            <p class="text-[10px] text-slate-500">${u.shift && u.shift !== '-' ? 'Shift: ' + u.shift : 'Belum ada jadwal'}</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-slate-300 group-hover:text-indigo-500 transition-colors"></i>
                </div>
            `).join('');
        }

        document.getElementById('userSearch')?.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            renderUserList(q);
            
            const clearBtn = document.getElementById('clearSearch');
            if (clearBtn) {
                if (q.length > 0) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }
        });

        document.getElementById('clearSearch')?.addEventListener('click', () => {
            const input = document.getElementById('userSearch');
            if (input) {
                input.value = '';
                renderUserList('');
                document.getElementById('clearSearch').classList.add('hidden');
                input.focus();
            }
        });

        async function updateUserShift(userId, shift) {
            try {
                const formData = new FormData();
                formData.append('id_code', userId);
                formData.append('shift', shift);

                const response = await fetch('/jadwal/update', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (e) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }

        async function removeUserFromSchedule(userId, name) {
            const confirmed = await Swal.fire({
                title: 'Hapus Petugas?',
                text: `Pindahkan ${name} dari jadwal jaga?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f43f5e',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (confirmed.isConfirmed) {
                updateUserShift(userId, '-');
            }
        }
        <?php endif; ?>
    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
