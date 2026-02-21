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
            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Setting Profil</h1>
        </div>
        <button id="themeToggle" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl overflow-hidden border border-slate-100 dark:border-slate-700 animate__animated animate__fadeInUp">
            
            <!-- Banner/Cover Preview -->
            <div class="relative h-32 sm:h-48 bg-slate-200 dark:bg-slate-700 overflow-hidden group">
                <img id="previewGambar" src="<?= base_url('assets/img/' . ($profil['gambar'] ?? 'default_cover.jpg')) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MDAiIGhlaWdodD0iNDAwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjY2JkNWUxIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzY0NzQ4YiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIENvdmVyPC90ZXh0Pjwvc3ZnPg=='">
                <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors"></div>
                <label for="inputGambar" class="absolute bottom-4 right-4 px-3 py-1.5 bg-black/50 hover:bg-black/70 text-white text-xs font-bold rounded-lg cursor-pointer backdrop-blur-sm transition-all flex items-center gap-2">
                    <i class="fas fa-camera"></i> Ganti Sampul
                </label>
            </div>

            <form id="profilForm" onsubmit="handleUpdate(event)" class="p-6 sm:p-8 relative">
                
                <!-- Logo Upload -->
                <div class="absolute -top-16 left-6 sm:left-8">
                    <div class="relative group">
                        <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl border-4 border-white dark:border-slate-800 bg-white dark:bg-slate-800 shadow-lg overflow-hidden flex items-center justify-center">
                            <img id="previewLogo" src="<?= base_url('assets/img/' . ($profil['logo'] ?? 'default_logo.png')) ?>" class="w-full h-full object-cover" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNTAiIGhlaWdodD0iMTUwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjFmMZVmIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyMCIgZmlsbD0iIzk0YTM4OCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIExvZ288L3RleHQ+PC9zdmc+'">
                        </div>
                        <label for="inputLogo" class="absolute bottom-0 right-0 w-8 h-8 bg-indigo-600 text-white rounded-full flex items-center justify-center shadow-lg cursor-pointer hover:bg-indigo-700 transition-transform hover:scale-110 border-2 border-white dark:border-slate-800">
                             <i class="fas fa-pencil-alt text-xs"></i>
                        </label>
                    </div>
                </div>

                <div class="mt-12 sm:mt-16 space-y-6">
                    
                    <!-- Hidden File Inputs -->
                    <input type="file" name="logo" id="inputLogo" class="hidden" accept="image/*" onchange="previewFile(this, 'previewLogo')">
                    <input type="file" name="gambar" id="inputGambar" class="hidden" accept="image/*" onchange="previewFile(this, 'previewGambar')">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <!-- Nama Aplikasi -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Nama Aplikasi / Organisasi</label>
                            <input type="text" name="nama" value="<?= esc($profil['nama']) ?>" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white font-bold text-lg" placeholder="Nama Aplikasi">
                        </div>

                        <!-- Contact Person -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Contact Person (CP)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fas fa-user"></i></span>
                                <input type="text" name="cp" value="<?= esc($profil['cp']) ?>" class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white font-semibold" placeholder="Nama CP">
                            </div>
                        </div>

                        <!-- No HP -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Nomor WhatsApp / HP</label>
                             <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fab fa-whatsapp"></i></span>
                                <input type="text" name="hp" value="<?= esc($profil['hp']) ?>" class="w-full pl-10 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white font-mono" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                        <!-- Jimpitan Effective Date -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-xs font-bold text-indigo-500 dark:text-indigo-400 uppercase mb-2">Tanggal Efektif Setoran Jimpitan</label>
                             <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fas fa-calendar-alt"></i></span>
                                <input type="date" name="jimpitan_start_date" value="<?= esc($profil['jimpitan_start_date']) ?>" class="w-full pl-10 pr-4 py-3 bg-indigo-50/50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-900/50 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white font-bold" title="Data scan sebelum tanggal ini tidak akan muncul di daftar setoran.">
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1 italic">Data scan sebelum tanggal ini tidak akan muncul di daftar "Setor Jimpitan".</p>
                        </div>

                        <!-- Alamat -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Alamat Lengkap</label>
                            <textarea name="alamat" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm"><?= esc($profil['alamat']) ?></textarea>
                        </div>

                         <!-- Catatan / Slogan -->
                        <div class="col-span-1 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">Catatan / Slogan</label>
                            <textarea name="catatan" rows="3" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm italic"><?= esc($profil['catatan']) ?></textarea>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                        <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>

                </div>
            </form>
        </div>

    </main>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>
    <?= $this->include('partials/submit_guard') ?>

    <script>
        // Preview Image
        function previewFile(input, imgId) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(imgId).src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        }

        // Form Submit
        async function handleUpdate(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);

            try {
                window.showLoader();
                const res = await fetch('/profil/update', {
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
