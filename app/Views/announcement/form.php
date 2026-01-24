<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= $profil['nama'] ?? 'Jimpitan App' ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            <a href="<?= base_url('announcement'); ?>" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text"><?= $title ?></h1>
        </div>
        <div class="flex items-center space-x-3">
             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm outline outline-1 outline-slate-100 dark:outline-slate-700 p-6 sm:p-8 animate__animated animate__fadeInUp">
            
            <form action="<?= base_url('announcement/' . $action . ($action == 'update' ? '/' . $data['id'] : '')); ?>" method="post" enctype="multipart/form-data" class="space-y-5">
                <?= csrf_field(); ?>
                <?php if($action == 'update'): ?>
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="old_image" value="<?= $data['image'] ?? '' ?>">
                <?php endif; ?>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Judul Pengumuman</label>
                    <input type="text" id="title" name="title" value="<?= old('title', $data['title'] ?? '') ?>" 
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" required placeholder="Contoh: Kerja Bakti Minggu Ini">
                    <?php if(session('errors.title')): ?>
                        <p class="text-rose-500 text-xs mt-1 ml-1"><?= session('errors.title') ?></p>
                    <?php endif; ?>
                </div>

                <!-- Dates Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="start_date" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Mulai Tayang</label>
                        <input type="datetime-local" id="start_date" name="start_date" value="<?= old('start_date', isset($data['start_date']) ? date('Y-m-d\TH:i', strtotime($data['start_date'])) : '') ?>" 
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" required>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Selesai Tayang</label>
                        <input type="datetime-local" id="end_date" name="end_date" value="<?= old('end_date', isset($data['end_date']) ? date('Y-m-d\TH:i', strtotime($data['end_date'])) : '') ?>" 
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" required>
                    </div>
                </div>

                <!-- Active Toggle -->
                 <div class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl mb-4">
                    <input type="checkbox" id="is_active" name="is_active" value="1" <?= old('is_active', $data['is_active'] ?? 1) == 1 ? 'checked' : '' ?> 
                        class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500 dark:bg-slate-600 dark:border-slate-500">
                    <label for="is_active" class="ml-3 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Aktifkan Pengumuman ini
                    </label>
                </div>
                
                <!-- Transparent Toggle -->
                 <div class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl mb-4">
                    <input type="checkbox" id="is_transparent" name="is_transparent" value="1" <?= old('is_transparent', $data['is_transparent'] ?? 1) == 1 ? 'checked' : '' ?> 
                        class="w-5 h-5 text-purple-600 border-slate-300 rounded focus:ring-purple-500 dark:bg-slate-600 dark:border-slate-500">
                    <label for="is_transparent" class="ml-3 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Gunakan Background Transparan
                        <span class="block text-xs font-normal text-slate-500 dark:text-slate-400">Jika dicentang, background modal akan hilang total (Teks & Gambar melayang).</span>
                    </label>
                </div>

                <!-- Hide Text Toggle -->
                 <div class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <input type="checkbox" id="hide_text" name="hide_text" value="1" <?= old('hide_text', $data['hide_text'] ?? 0) == 1 ? 'checked' : '' ?> 
                        class="w-5 h-5 text-rose-600 border-slate-300 rounded focus:ring-rose-500 dark:bg-slate-600 dark:border-slate-500">
                    <label for="hide_text" class="ml-3 block text-sm font-medium text-slate-700 dark:text-slate-200">
                        Sembunyikan Teks (Mode Gambar Full)
                        <span class="block text-xs font-normal text-slate-500 dark:text-slate-400">Jika dicentang, judul dan konten tidak akan ditampilkan. Modal otomatis melebar untuk gambar.</span>
                    </label>
                </div>

                 <!-- Image -->
                 <div class="input-group">
                    <label for="image" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Gambar Banner (Opsional)</label>
                    <?php if(isset($data['image']) && $data['image']): ?>
                        <div class="mb-3 relative w-fit group">
                            <img src="<?= base_url('img/announcement/' . $data['image']) ?>" alt="Preview" class="h-40 w-auto object-cover rounded-xl shadow-sm border border-slate-200 dark:border-slate-600">
                            <div class="absolute inset-x-0 bottom-0 bg-black/60 text-white text-[10px] text-center py-1 rounded-b-xl opacity-0 group-hover:opacity-100 transition-opacity">Gambar Saat Ini</div>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*" class="block w-full text-sm text-slate-500
                        file:mr-4 file:py-2.5 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-bold
                        file:bg-indigo-50 file:text-indigo-700
                        hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300
                        cursor-pointer
                    ">
                     <p class="text-xs text-slate-400 mt-2"><i class="fas fa-info-circle mr-1"></i> Format: JPG, PNG, WEBP. Max 2MB.</p>
                </div>

                 <!-- Content -->
                 <div>
                    <label for="content" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Isi Pengumuman (Opsional)</label>
                    <textarea id="content" name="content" rows="4" 
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white placeholder-slate-400" placeholder="Tulis detail pengumuman di sini..."><?= old('content', $data['content'] ?? '') ?></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-700/50 flex gap-4">
                    <a href="<?= base_url('announcement'); ?>" class="flex-1 py-3.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition-all text-center">
                        Batal
                    </a>
                    <button type="submit" class="flex-[2] py-3.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all">
                        <i class="fas fa-save mr-2"></i> <?= $action == 'store' ? 'Simpan Pengumuman' : 'Update Perubahan'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };
    </script>
</body>
</html>
