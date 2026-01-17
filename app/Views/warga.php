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
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300">
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
            <h1 class="text-xl font-bold gradient-text">Data Warga</h1>
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

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-6">
        
        <!-- Search & Info -->
        <div class="mb-6 animate__animated animate__fadeIn">
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" id="searchInput" placeholder="Cari Nama/NIK..." value="<?= esc($keyword) ?>"
                       class="w-full pl-11 pr-12 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl shadow-sm text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white"
                       oninput="debounceSearch(this.value)">
                <?php if($keyword): ?>
                    <button onclick="searchWarga('')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Warga List -->
        <div id="wargaListContainer" class="space-y-2 pb-20">
            <?php if(empty($warga)): ?>
                <div class="text-center py-20">
                    <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                        <i class="fas fa-users-slash text-4xl"></i>
                    </div>
                    <p class="text-slate-500">Belum ada data warga ditemukan.</p>
                </div>
            <?php endif; ?>

            <?php foreach($warga as $w): ?>
            <?php 
                // Server-side check to prevent 404s
                // if(!empty($w['foto']) && !file_exists(FCPATH . 'img/warga/' . $w['foto'])) {
                //    $w['foto'] = ''; // Reset if file not found
                // }
            ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl px-4 py-2 flex items-center justify-between border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md transition-all group">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center shrink-0 overflow-hidden relative">
                        <?php if(!empty($w['foto'])): ?>
                            <img src="/img/warga/<?= esc(basename($w['foto'])) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <span class="text-slate-500 dark:text-slate-400 font-bold text-xs uppercase"><?= substr($w['nama'], 0, 1) ?></span>
                    </div>
                    <div class="min-w-0 flex flex-col justify-center">
                        <div class="flex items-baseline gap-2">
                            <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm truncate"><?= esc($w['nama']) ?></h4>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] text-slate-400 font-medium">
                            <span class="tracking-wider font-mono"><?= esc($w['nik']) ?></span>
                            <span class="w-0.5 h-0.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                            <span class="uppercase text-indigo-500"><?= esc($w['hubungan']) ?></span>
                            <span class="w-0.5 h-0.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                            <span><?= ($w['jenkel'] == 'L' || $w['jenkel'] == 'LAKI-LAKI') ? 'L' : 'P' ?></span>
                        </div>
                    </div>
                </div>

                <?php if($canManage): ?>
                <div class="flex items-center gap-1 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick='openEditModal(<?= json_encode($w) ?>)' class="w-8 h-8 rounded-lg text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 flex items-center justify-center transition-colors">
                        <i class="fas fa-pencil-alt text-xs"></i>
                    </button>
                    <button onclick="confirmDelete(<?= $w['id_warga'] ?>, '<?= esc($w['nama']) ?>')" class="w-8 h-8 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 flex items-center justify-center transition-colors">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Modal Form -->
    <div id="wargaModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 sm:p-8 flex flex-col max-h-[90vh] animate__animated animate__zoomIn animate__faster">
            
            <div class="flex justify-between items-center mb-6 shrink-0">
                <h3 id="modalTitle" class="text-2xl font-bold text-slate-800 dark:text-white">Tambah Warga</h3>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="overflow-y-auto custom-scrollbar pr-2 -mr-2">
                <form id="wargaForm" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" name="id_warga" id="id_warga">

                    <!-- FOTO PROFIL -->
                    <div class="flex flex-col items-center mb-2">
                        <div class="relative group cursor-pointer transition-transform active:scale-95" onclick="document.getElementById('foto').click()">
                            <div class="w-24 h-24 rounded-full border-4 border-white dark:border-slate-700 shadow-lg overflow-hidden bg-slate-100 dark:bg-slate-800">
                                <img id="fotoPreview" src="https://ui-avatars.com/api/?name=Warga&background=cbd5e1&color=fff" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name='+(document.getElementById('nama').value || 'Warga')+'&background=cbd5e1&color=fff';">
                            </div>
                            <div class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg border-2 border-white dark:border-slate-900 group-hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-camera text-xs"></i>
                            </div>
                        </div>
                        <input type="file" name="foto" id="foto" class="hidden" accept="image/*" onchange="previewPhoto(this)">
                        <p class="text-[10px] text-slate-400 mt-2 font-medium">Format: JPG, PNG (Max 2MB)</p>
                    </div>
                    
                    <!-- INFORMASI PRIBADI -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">NIK Kepala Keluarga</label>
                            <input type="number" name="nikk" id="nikk" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" placeholder="Opsional">
                        </div>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">NIK</label>
                                <input type="number" name="nik" id="nik" required class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" placeholder="16 Digit NIK">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nama Lengkap</label>
                                <input type="text" name="nama" id="nama" required class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" placeholder="Sesuai KTP">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Hubungan</label>
                                <div class="relative">
                                    <select name="hubungan" id="hubungan" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none">
                                        <option value="Kepala Keluarga">Kepala Keluarga</option>
                                        <option value="Istri">Istri</option>
                                        <option value="Anak">Anak</option>
                                        <option value="Famili Lain">Famili Lain</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Gender</label>
                                <div class="relative">
                                    <select name="jenkel" id="jenkel" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none">
                                        <option value="L">Laki-Laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tempat Lahir</label>
                                <input type="text" name="tpt_lahir" id="tpt_lahir" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tanggal Lahir</label>
                                <input type="date" name="tgl_lahir" id="tgl_lahir" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- ALAMAT DOMISILI -->
                    <div class="space-y-4">
                        <hr class="border-slate-100 dark:border-slate-800">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Alamat Lengkap</label>
                            <textarea name="alamat" id="alamat" rows="2" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">RT</label>
                                <input type="text" name="rt" id="rt" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white text-center">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">RW</label>
                                <input type="text" name="rw" id="rw" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white text-center">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Kelurahan</label>
                                <input type="text" name="kelurahan" id="kelurahan" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Kecamatan</label>
                                <input type="text" name="kecamatan" id="kecamatan" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- DATA TAMBAHAN -->
                    <div class="space-y-4">
                        <hr class="border-slate-100 dark:border-slate-800">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Agama</label>
                                <div class="relative">
                                    <select name="agama" id="agama" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none">
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Budha">Budha</option>
                                        <option value="Konghucu">Konghucu</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Status</label>
                                <div class="relative">
                                    <select name="status" id="status" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none">
                                        <option value="Kawin">Kawin</option>
                                        <option value="Belum Kawin">Belum Kawin</option>
                                        <option value="Cerai Hidup">Cerai Hidup</option>
                                        <option value="Cerai Mati">Cerai Mati</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Pekerjaan</label>
                            <input type="text" name="pekerjaan" id="pekerjaan" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                        </div>
                        

                    </div>

                    <button type="button" onclick="submitForm()" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all mt-6">
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

        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('fotoPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openAddModal() {
            isEdit = false;
            document.getElementById('modalTitle').innerText = 'Tambah Warga';
            form.reset();
            document.getElementById('id_warga').value = '';
            document.getElementById('fotoPreview').src = "https://ui-avatars.com/api/?name=New+Warga&background=cbd5e1&color=fff";
            modal.classList.remove('hidden');
        }

        function openEditModal(data) {
            isEdit = true;
            document.getElementById('modalTitle').innerText = 'Edit Warga';
            form.reset();
            
            // Photo handling
            if (data.foto) {
                const photoName = data.foto.split('/').pop();
                document.getElementById('fotoPreview').src = `/img/warga/${photoName}`;
            } else {
                document.getElementById('fotoPreview').src = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.nama)}&background=cbd5e1&color=fff`;
            }
            
            // Populate Fields
            for (const key in data) {
                if (form.elements[key] && key !== 'foto') {
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: res.message
                    });
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
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.onclick = () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        };

        // Sync Theme Across Tabs
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                if (e.newValue === 'dark') document.documentElement.classList.add('dark');
                else document.documentElement.classList.remove('dark');
            }
        });

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
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, skipLoader: true })
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
        .swal2-container { z-index: 2000 !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
        
        /* Mobile Bottom Nav Styles */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    </style>
    

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
