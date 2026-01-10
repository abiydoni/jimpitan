<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Warga - Jimpitan App</title>
    
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
        // Dark Mode Init
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

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
        .dark .glass { background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="bg-indigo-50 dark:bg-slate-900 transition-colors duration-300 min-h-screen pb-24">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-300 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-indigo-100 dark:border-slate-800">
        <div class="max-w-md mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/20 text-white">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <h1 class="font-bold text-slate-800 dark:text-white text-lg leading-tight">Data Warga</h1>
                    <p class="text-xs text-slate-500 font-medium">Manage Residents</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <button onclick="toggleDarkMode()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-amber-400 hover:bg-slate-200 transition-all flex items-center justify-center">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block"></i>
                </button>
                <button onclick="window.location.href='/'" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 transition-all flex items-center justify-center">
                    <i class="fas fa-arrow-left"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-md mx-auto pt-24 px-4">
        
        <!-- Search & Add Actions -->
        <div class="flex space-x-3 mb-6">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" placeholder="Cari Nama/NIK..." value="<?= esc($keyword) ?>"
                       class="w-full pl-11 pr-4 py-3.5 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-medium rounded-2xl shadow-sm border-none ring-1 ring-slate-100 dark:ring-slate-700/50 focus:ring-2 focus:ring-indigo-500 transition-all"
                       oninput="debounceSearch(this.value)">
            </div>
            
            <?php if($canManage): ?>
            <button onclick="openAddModal()" class="w-12 rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-500/30 flex items-center justify-center hover:bg-indigo-700 transition-transform active:scale-95">
                <i class="fas fa-plus text-lg"></i>
            </button>
            <?php endif; ?>
        </div>

        <!-- Warga List -->
        <div id="wargaListContainer" class="space-y-4 pb-20">
            <?php if(empty($warga)): ?>
                <div class="glass rounded-3xl p-8 text-center animate__animated animate__fadeIn">
                    <div class="w-20 h-20 bg-indigo-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-300 dark:text-slate-600">
                        <i class="fas fa-users-slash text-3xl"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 dark:text-white mb-1">Tidak Ada Data</h3>
                    <p class="text-xs text-slate-500">Belum ada data warga ditemukan.</p>
                </div>
            <?php endif; ?>

            <?php foreach($warga as $w): ?>
            <div class="bg-white dark:bg-slate-900 rounded-[1.5rem] p-5 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800 relative overflow-hidden group hover:scale-[1.02] transition-transform duration-300 animate__animated animate__fadeIn">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg shrink-0">
                            <?= strtoupper(substr($w['nama'], 0, 1)) ?>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-white text-base leading-snug line-clamp-1"><?= esc($w['nama']) ?></h3>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-xs font-mono bg-slate-100 dark:bg-slate-800 text-slate-500 px-2 py-0.5 rounded-lg"><?= esc($w['nik']) ?></span>
                                <span class="text-[10px] uppercase font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-md"><?= esc($w['hubungan']) ?></span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1"><?= esc($w['alamat']) ?: '-' ?></p>
                        </div>
                    </div>

                    <?php if($canManage): ?>
                    <div class="flex space-x-1">
                        <button onclick='openEditModal(<?= json_encode($w) ?>)' class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-500 flex items-center justify-center hover:bg-amber-100 transition-colors">
                            <i class="fas fa-pencil-alt text-xs"></i>
                        </button>
                        <button onclick="confirmDelete(<?= $w['id_warga'] ?>, '<?= $w['nama'] ?>')" class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-rose-500 flex items-center justify-center hover:bg-rose-100 transition-colors">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="wargaModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        
        <!-- Content -->
        <!-- Matched classes with kk.php: max-w-md (changed to max-w-lg for longer form), rounded-[2.5rem], simple padding -->
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 sm:p-8 flex flex-col max-h-[90vh] animate__animated animate__zoomIn animate__faster">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-6 shrink-0">
                <h3 id="modalTitle" class="text-2xl font-bold text-slate-800 dark:text-white">Tambah Warga</h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Scrollable Form Container -->
            <div class="overflow-y-auto custom-scrollbar pr-2 -mr-2">
                <form id="wargaForm" class="space-y-5">
                    <input type="hidden" name="id_warga" id="id_warga">
                    
                    <!-- Section: Personal Info -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500 flex items-center justify-center">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Informasi Pribadi</h4>
                        </div>
                        
                        <!-- NIK & Nama -->
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="label"><i class="far fa-id-card mr-1"></i> NIK</label>
                                <input type="number" name="nik" id="nik" required class="input-field" placeholder="16 digit NIK">
                            </div>
                            <div>
                                <label class="label"><i class="far fa-user mr-1"></i> Nama Lengkap</label>
                                <input type="text" name="nama" id="nama" required class="input-field" placeholder="Nama sesuai KTP">
                            </div>
                        </div>

                        <!-- Hubungan & Gender -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label"><i class="fas fa-users mr-1"></i> Hubungan</label>
                                <div class="relative">
                                    <select name="hubungan" id="hubungan" class="input-field appearance-none">
                                        <option value="KEPALA KELUARGA">KEPALA KELUARGA</option>
                                        <option value="ISTRI">ISTRI</option>
                                        <option value="ANAK">ANAK</option>
                                        <option value="FAMILI LAIN">FAMILI LAIN</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="label"><i class="fas fa-venus-mars mr-1"></i> Gender</label>
                                <div class="relative">
                                    <select name="jenkel" id="jenkel" class="input-field appearance-none">
                                        <option value="LAKI-LAKI">Laki-Laki</option>
                                        <option value="PEREMPUAN">Perempuan</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                        </div>

                        <!-- TTL -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label"><i class="fas fa-map-marker-alt mr-1"></i> Tempat Lahir</label>
                                <input type="text" name="tpt_lahir" id="tpt_lahir" class="input-field">
                            </div>
                            <div>
                                <label class="label"><i class="far fa-calendar-alt mr-1"></i> Tgl Lahir</label>
                                <input type="date" name="tgl_lahir" id="tgl_lahir" class="input-field">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Alamat -->
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-500 flex items-center justify-center">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Alamat Domisili</h4>
                        </div>

                        <div>
                            <label class="label"><i class="fas fa-home mr-1"></i> Alamat Lengkap</label>
                            <textarea name="alamat" id="alamat" rows="2" class="input-field resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">RT</label>
                                <input type="text" name="rt" id="rt" class="input-field">
                            </div>
                            <div>
                                <label class="label">RW</label>
                                <input type="text" name="rw" id="rw" class="input-field">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Kelurahan</label>
                                <input type="text" name="kelurahan" id="kelurahan" class="input-field">
                            </div>
                            <div>
                                <label class="label">Kecamatan</label>
                                <input type="text" name="kecamatan" id="kecamatan" class="input-field">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Others -->
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center space-x-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-500 flex items-center justify-center">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm">Data Tambahan</h4>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label"><i class="fas fa-praying-hands mr-1"></i> Agama</label>
                                <div class="relative">
                                    <select name="agama" id="agama" class="input-field appearance-none">
                                        <option value="ISLAM">ISLAM</option>
                                        <option value="KRISTEN">KRISTEN</option>
                                        <option value="KATOLIK">KATOLIK</option>
                                        <option value="HINDU">HINDU</option>
                                        <option value="BUDHA">BUDHA</option>
                                        <option value="KONGHUCU">KONGHUCU</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="label"><i class="fas fa-heart mr-1"></i> Status</label>
                                <div class="relative">
                                    <select name="status" id="status" class="input-field appearance-none">
                                        <option value="KAWIN">KAWIN</option>
                                        <option value="BELUM KAWIN">BELUM KAWIN</option>
                                        <option value="CERAI HIDUP">CERAI HIDUP</option>
                                        <option value="CERAI MATI">CERAI MATI</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="label"><i class="fas fa-briefcase mr-1"></i> Pekerjaan</label>
                            <input type="text" name="pekerjaan" id="pekerjaan" class="input-field">
                        </div>
                        
                         <div>
                            <label class="label"><i class="fas fa-user-tie mr-1"></i> NIK Kepala Keluarga</label>
                            <input type="number" name="nikk" id="nikk" class="input-field" placeholder="NIK Kepala Keluarga">
                        </div>
                    </div>

                    <button type="button" onclick="submitForm()" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all mt-4">
                        Simpan Data
                    </button>
                    <!-- Bottom spacing -->
                    <div class="h-2"></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- UI Logic ---
        const modal = document.getElementById('wargaModal');
        const form = document.getElementById('wargaForm');
        let isEdit = false;

        function searchWarga(query) {
            window.location.href = `/warga?q=${encodeURIComponent(query)}`;
        }

        function openAddModal() {
            isEdit = false;
            document.getElementById('modalTitle').innerText = 'Tambah Warga';
            form.reset();
            document.getElementById('id_warga').value = '';
            modal.classList.remove('hidden');
        }

        function openEditModal(data) {
            isEdit = true;
            document.getElementById('modalTitle').innerText = 'Edit Warga';
            form.reset();
            
            // Populate Fields
            for (const key in data) {
                if (form.elements[key]) {
                    form.elements[key].value = data[key];
                }
            }
            
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        // --- CRUD Logic ---
        function submitForm() {
            if(!form.checkValidity()){
                form.reportValidity();
                return;
            }

            const url = isEdit ? '/warga/update' : '/warga/store';
            const formData = new FormData(form);

            Swal.fire({
                title: 'Mohon Tunggu...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            })
            .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Data?',
                text: `Yakin ingin menghapus data ${name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch(`/warga/delete/${id}`, { method: 'POST' })
                    .then(res => res.json())
                    .then(res => {
                        if(res.status === 'success') {
                            Swal.fire('Terhapus!', res.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    });
                }
            });
        }

        // Dark Mode Toggle
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }

        // Live Search Logic
        let timeout = null;
        function debounceSearch(query) {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        }

        function performSearch(query) {
            const url = `/warga?q=${encodeURIComponent(query)}`;
            
            // Update URL without reload
            window.history.pushState({path: url}, '', url);

            // Fetch Data
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('wargaListContainer').innerHTML;
                    document.getElementById('wargaListContainer').innerHTML = newContent;
                })
                .catch(err => console.error('Search Error:', err));
        }
    </script>

    <style>
        .label { @apply block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1; }
        .input-field { @apply w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white font-medium placeholder:text-slate-300; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</body>
</html>
