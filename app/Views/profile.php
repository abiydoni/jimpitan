<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?= $profil['nama'] ?? 'Jimpitan App' ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>

    <style>
        body { 
            font-family: 'Outfit', sans-serif; 
            -webkit-tap-highlight-color: transparent;
            background-color: #f8fafc;
        }
        body.dark { background-color: #0f172a; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.05); }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Ensure SweetAlert is always on top */
        .swal2-container {
            z-index: 9999 !important;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 dark:text-slate-200 transition-colors duration-300 pb-20">

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" onclick="window.showLoader()" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text">Profil Saya</h1>
        </div>
        <button id="themeToggle" class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
    </nav>

    <main class="max-w-md mx-auto px-4 py-6">
        
        <!-- User Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-slate-100 dark:border-slate-700 text-center mb-6 animate__animated animate__fadeInDown">
            <div id="mainAvatarContainer" class="w-24 h-24 mx-auto rounded-full flex items-center justify-center text-4xl font-bold mb-4 shadow-xl overflow-hidden bg-gradient-to-tr from-indigo-500 to-purple-600 text-white relative">
                <?php if(!empty($userWarga['foto']) && file_exists(FCPATH . 'img/warga/' . $userWarga['foto'])): ?>
                    <img id="mainAvatarImg" src="/img/warga/<?= $userWarga['foto'] ?>" alt="<?= $user['name'] ?>" class="w-full h-full object-cover">
                    <span id="mainAvatarInitial" class="hidden"><?= substr($user['name'], 0, 1) ?></span>
                <?php else: ?>
                    <img id="mainAvatarImg" src="" class="w-full h-full object-cover hidden">
                    <span id="mainAvatarInitial"><?= substr($user['name'], 0, 1) ?></span>
                <?php endif; ?>
            </div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white"><?= $user['name'] ?></h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">@<?= $user['user_name'] ?></p>
            <div class="mt-3 inline-flex items-center px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full text-xs font-bold uppercase tracking-wider">
                <?= $user['role'] ?>
            </div>
        </div>

        <!-- Family Section -->
        <?php if (!empty($familyMembers)): ?>
            <div class="mb-4 flex items-center justify-between animate__animated animate__fadeIn">
                <h3 class="font-bold text-slate-600 dark:text-slate-300">Anggota Keluarga</h3>
                <?php if($kkData): ?>
                    <span class="text-xs px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg border border-emerald-100 dark:border-emerald-900/30">
                        KK: <?= $kkData['kk_name'] ?? '-' ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="space-y-3 animate__animated animate__fadeInUp">
                <?php foreach($familyMembers as $member): ?>
                    <div id="member-card-<?= $member['id_warga'] ?>" onclick="showMemberDetail('<?= $member['id_warga'] ?>')" class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center space-x-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors active:scale-98">
                        <!-- Avatar -->
                        <div class="relative w-12 h-12">
                            <?php if(!empty($member['foto']) && file_exists(FCPATH . 'img/warga/' . $member['foto'])): ?>
                                <img id="member-img-<?= $member['id_warga'] ?>" src="/img/warga/<?= $member['foto'] ?>" alt="<?= $member['nama'] ?>" class="w-full h-full rounded-full object-cover border-2 border-white dark:border-slate-700 shadow-sm">
                                <div id="member-init-<?= $member['id_warga'] ?>" class="hidden w-full h-full rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 font-bold border-2 border-white dark:border-slate-700 shadow-sm">
                                    <?= substr($member['nama'], 0, 1) ?>
                                </div>
                            <?php else: ?>
                                <img id="member-img-<?= $member['id_warga'] ?>" src="" class="hidden w-full h-full rounded-full object-cover border-2 border-white dark:border-slate-700 shadow-sm">
                                <div id="member-init-<?= $member['id_warga'] ?>" class="w-full h-full rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-400 font-bold border-2 border-white dark:border-slate-700 shadow-sm">
                                    <?= substr($member['nama'], 0, 1) ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status Indicator -->
                            <?php if(str_contains(strtolower($member['hubungan']), 'kepala')): ?>
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center text-white text-[10px] border-2 border-white dark:border-slate-800" title="Kepala Keluarga">
                                    <i class="fas fa-crown"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-slate-800 dark:text-white truncate"><?= $member['nama'] ?></h4>
                            <div class="flex items-center text-xs text-slate-500 dark:text-slate-400 mt-0.5 space-x-2">
                                <span class="capitalize"><?= strtolower($member['jenkel']) ?></span>
                                <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                <span class="capitalize"><?= strtolower($member['hubungan']) ?></span>
                            </div>
                        </div>

                        <!-- Badge if User -->
                        <?php if(($member['id_warga'] ?? 0) == ($user['id_warga'] ?? 0) || ($member['nik'] ?? 0) == ($user['nik'] ?? 0)): ?> 
                            <div class="px-2 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-lg">
                                ME
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-10 bg-white dark:bg-slate-800 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 animate__animated animate__fadeIn">
                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-300">
                    <i class="fas fa-users-slash text-2xl"></i>
                </div>
                <p class="text-sm text-slate-500">Belum ada data keluarga yang terhubung.</p>
                <p class="text-xs text-slate-400 mt-1">Hubungi admin untuk menautkan data NIKK.</p>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Hidden File Input -->
    <input type="file" id="photoInput" class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp">

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="relative w-full max-w-sm bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl overflow-hidden animate__animated animate__zoomIn animate__faster">
            
            <!-- Header/Cover -->
            <div class="h-24 bg-gradient-to-r from-indigo-500 to-purple-600 relative">
                <button onclick="closeModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/30 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Avatar Wrapper -->
            <div class="relative w-24 h-24 mx-auto -mt-12 mb-3">
                <div class="w-full h-full rounded-full border-4 border-white dark:border-slate-900 shadow-lg bg-white dark:bg-slate-800 flex items-center justify-center overflow-hidden relative group cursor-pointer" onclick="triggerPhotoUpload()">
                    <img id="detailFoto" src="" class="w-full h-full object-cover hidden">
                    <span id="detailInitial" class="text-3xl font-bold text-slate-300 select-none">A</span>
                    
                    <!-- Hover Overlay (Desktop) -->
                    <div class="absolute inset-0 bg-black/40 hidden md:flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <i class="fas fa-camera text-white"></i>
                    </div>
                </div>

                <!-- Floating Edit Button (Mobile/Always Visible) -->
                <button onclick="triggerPhotoUpload()" class="absolute bottom-0 right-0 translate-x-1 translate-y-1 w-8 h-8 rounded-full bg-indigo-600 border-2 border-white dark:border-slate-800 text-white flex items-center justify-center shadow-md hover:bg-indigo-700 active:scale-95 transition-transform z-10">
                    <i class="fas fa-camera text-xs"></i>
                </button>
            </div>

            <!-- Name & Info -->
            <div class="text-center px-6 mb-6">
                <h3 id="detailNama" class="text-lg font-bold text-slate-800 dark:text-white leading-tight">Nama Warga</h3>
                <p id="detailNik" class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-1">1234567890123456</p>
            </div>

            <!-- Body -->
            <div class="px-6 pb-6 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Hubungan</p>
                        <p id="detailHubungan" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Kepala Keluarga</p>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Jenis Kelamin</p>
                        <p id="detailJenkel" class="text-sm font-semibold text-slate-700 dark:text-slate-200">Laki-Laki</p>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Tempat Lahir</p>
                        <p id="detailTptLahir" class="text-sm font-semibold text-slate-700 dark:text-slate-200">-</p>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Tanggal Lahir</p>
                        <p id="detailTglLahir" class="text-sm font-semibold text-slate-700 dark:text-slate-200">01 Jan 1990</p>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">Alamat Domisili</p>
                    <p id="detailAlamat" class="text-sm text-slate-700 dark:text-slate-200 leading-snug">
                        Jl. Contoh No. 123, RT 01 RW 02, Kelurahan, Kecamatan
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Initialize Members Data from PHP
        const rawMembers = <?= json_encode($familyMembers) ?>;
        const membersMap = rawMembers.reduce((acc, member) => {
            acc[member.id_warga] = member;
            return acc;
        }, {});

        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const body = document.body;

        // Init Theme
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
            body.classList.add('dark');
        }

        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            body.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };

        // Sync Theme Across Tabs
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                if (e.newValue === 'dark') {
                    html.classList.add('dark');
                    body.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                    body.classList.remove('dark');
                }
            }
        });

        const loggedInWargaId = '<?= $userWarga['id_warga'] ?? '' ?>';
        let currentIdWarga = null;

        function showMemberDetail(id) {
            const member = membersMap[id];
            if (!member) return;

            currentIdWarga = id;
            const modal = document.getElementById('detailModal');
            const img = document.getElementById('detailFoto');
            const initial = document.getElementById('detailInitial');
            
            // Populate Data
            document.getElementById('detailNama').innerText = member.nama;
            document.getElementById('detailNik').innerText = member.nik;
            document.getElementById('detailHubungan').innerText = member.hubungan;
            document.getElementById('detailJenkel').innerText = member.jenkel === 'L' ? 'Laki-Laki' : 'Perempuan';
            document.getElementById('detailTptLahir').innerText = member.tpt_lahir || '-';
            
            // Format Date
            if (member.tgl_lahir) {
                const date = new Date(member.tgl_lahir);
                document.getElementById('detailTglLahir').innerText = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            } else {
                document.getElementById('detailTglLahir').innerText = '-';
            }

            // Address Construction
            let alamat = member.alamat || '';
            if (member.rt) alamat += `, RT ${member.rt}`;
            if (member.rw) alamat += ` RW ${member.rw}`;
            if (member.kelurahan) alamat += `, ${member.kelurahan}`;
            if (member.kecamatan) alamat += `, ${member.kecamatan}`;
            document.getElementById('detailAlamat').innerText = alamat;

            // Image Handling
            // Use the data from membersMap which is updated on upload
            if (member.foto) {
                img.src = `/img/warga/${member.foto}`;
                img.classList.remove('hidden');
                initial.classList.add('hidden');
            } else {
                img.src = ''; // Reset src
                img.classList.add('hidden');
                initial.classList.remove('hidden');
                initial.innerText = member.nama.charAt(0).toUpperCase();
            }

            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('detailModal').classList.add('hidden');
            currentIdWarga = null;
        }

        // Photo Upload Logic
        function triggerPhotoUpload() {
            document.getElementById('photoInput').click();
        }

        document.getElementById('photoInput').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!currentIdWarga) {
                Swal.fire('Error', 'ID Warga tidak ditemukan', 'error');
                return;
            }

            // Show Loader
            if(window.showLoader) window.showLoader();

            const formData = new FormData();
            formData.append('foto', file);
            formData.append('id_warga', currentIdWarga);

            try {
                const response = await fetch('/profil/updatePhoto', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                     // Update Map Data immediately so re-opening modal works
                     if (membersMap[currentIdWarga]) {
                         membersMap[currentIdWarga].foto = result.new_photo;
                     }

                     // Update data object
                     const newSrc = `/img/warga/${result.new_photo}?t=${new Date().getTime()}`;

                     // 1. Update View in Modal
                     const img = document.getElementById('detailFoto');
                     const initial = document.getElementById('detailInitial');
                     img.src = newSrc;
                     img.classList.remove('hidden');
                     initial.classList.add('hidden');

                     // 2. Update View in List (Family Members)
                     const listImg = document.getElementById(`member-img-${currentIdWarga}`);
                     const listInit = document.getElementById(`member-init-${currentIdWarga}`);
                     if (listImg && listInit) {
                         listImg.src = newSrc;
                         listImg.classList.remove('hidden');
                         listInit.classList.add('hidden');
                     }

                     // 3. Update Main Profile (if it matches logged in user)
                     // Strict check using PHP injected ID
                     if (loggedInWargaId && currentIdWarga == loggedInWargaId) {
                         const mainImg = document.getElementById('mainAvatarImg');
                         const mainInit = document.getElementById('mainAvatarInitial');
                         if(mainImg && mainInit) {
                             mainImg.src = newSrc;
                             mainImg.classList.remove('hidden');
                             mainInit.classList.add('hidden');
                         }
                     }

                     if(window.hideLoader) window.hideLoader();
                     
                     Swal.fire({
                         icon: 'success',
                         title: 'Berhasil',
                         text: 'Foto profil berhasil diperbarui',
                         timer: 1500,
                         showConfirmButton: false
                     });
                     
                } else {
                    if(window.hideLoader) window.hideLoader();
                    Swal.fire('Gagal', result.message || 'Terjadi kesalahan', 'error');
                }

            } catch (err) {
                console.error(err);
                if(window.hideLoader) window.hideLoader();
                Swal.fire('Error', 'Gagal menghubungkan ke server', 'error');
            }
            
            // Reset input
            e.target.value = '';
        });
    </script>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
