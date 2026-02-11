<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jimpitan App | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS with Dark Mode Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        body { 
            font-family: 'Outfit', sans-serif; 
            background-color: #f8fafc; 
            overflow-x: hidden; 
            transition: background-color 0.3s ease;
        }
        body.dark {
            background-color: #0f172a;
        }
        .glass { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.2); 
            transition: all 0.3s ease;
        }
        .dark .glass {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 bg-slate-50 dark:bg-dark transition-colors duration-300">
    <!-- Theme Toggle Floating -->
    <div class="fixed top-6 right-6 z-50">
        <button id="themeToggle" class="bg-white dark:bg-slate-800 p-3 rounded-2xl shadow-xl dark:shadow-none text-slate-600 dark:text-slate-300 hover:scale-110 active:scale-95 transition-all border border-slate-100 dark:border-slate-700">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
    </div>

    <div class="w-full max-w-[400px] animate__animated animate__fadeInUp">
        <div class="text-center mb-6 sm:mb-8">
            <div class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-indigo-200 mx-auto mb-4 animate__animated animate__bounceIn">
                <i class="fas fa-hand-holding-dollar text-3xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-white tracking-tight">Selamat Datang</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 text-sm sm:text-base">Silakan login untuk mengelola jimpitan</p>
        </div>

        <div class="glass p-6 sm:p-8 rounded-[2rem] shadow-2xl shadow-indigo-100/50">
            <?php if(session()->getFlashdata('msg')):?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        text: '<?= session()->getFlashdata('msg') ?>',
                        confirmButtonColor: '#6366f1',
                    });
                </script>
            <?php endif;?>

            <form action="/auth/login" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-1">Username</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                            <i class="fas fa-user-circle"></i>
                        </span>
                        <input type="text" name="user_name" required class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-200 placeholder:text-slate-300 dark:placeholder:text-slate-600 shadow-sm" placeholder="Username Anda">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-1">Password</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-200 placeholder:text-slate-300 dark:placeholder:text-slate-600 shadow-sm" placeholder="Password Anda">
                    </div>
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center text-xs text-slate-500 dark:text-slate-400 cursor-pointer group">
                        <input type="checkbox" name="remember_me" class="w-4 h-4 text-indigo-600 border-slate-300 dark:border-slate-600 rounded focus:ring-indigo-500 transition-all mr-2 group-hover:border-indigo-400">
                        Ingat Saya
                    </label>
                    <a href="javascript:void(0)" onclick="forgotPassword()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">Lupa Password?</a>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-200 transition-all duration-200 flex items-center justify-center space-x-2">
                    <span>Masuk Aplikasi</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
            </form>
        </div>

        <div class="mt-8 text-center space-y-4">
             <div class="flex items-center justify-center space-x-2 text-slate-300">
                <span class="h-px w-8 bg-slate-200"></span>
                <span class="text-[10px] uppercase font-bold tracking-widest text-slate-400">Verified System</span>
                <span class="h-px w-8 bg-slate-200"></span>
            </div>
            <p class="text-slate-400 text-xs font-medium">
                &copy; 2026 Jimpitan App. Semua Hak Dilindungi.
            </p>
        </div>
    </div>
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const body = document.body;

        if (localStorage.getItem('theme') === 'dark') {
            html.classList.add('dark');
            body.classList.add('dark');
        }

        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            body.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        };

        function forgotPassword() {
            const cp = "<?= $profil['cp'] ?? 'Admin' ?>";
            const hp = "<?= $profil['hp'] ?? '' ?>";
            
            Swal.fire({
                title: 'Lupa Password?',
                text: `Silakan hubungi ${cp} di nomor WA ${hp} untuk reset password.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#25D366', // WhatsApp color
                cancelButtonColor: '#94a3b8',
                confirmButtonText: '<i class="fab fa-whatsapp"></i> Hubungi via WA',
                cancelButtonText: 'Tutup'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!hp) {
                        Swal.fire('Error', 'Nomor HP Admin tidak tersedia.', 'error');
                        return;
                    }
                    
                    // Format HP for WhatsApp (ensure starts with 62)
                    let formattedHp = hp.replace(/\D/g, ''); // User only digits
                    if (formattedHp.startsWith('0')) {
                        formattedHp = '62' + formattedHp.substring(1);
                    } else if (!formattedHp.startsWith('62')) {
                        formattedHp = '62' + formattedHp; // Assume local if no country code? Or just append. Best guess.
                    }
                    
                    window.open(`https://wa.me/${formattedHp}?text=Halo ${cp}, saya lupa password aplikasi Jimpitan. Mohon bantuannya.`, '_blank');
                }
            });
        }
    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>
    <?= $this->include('partials/submit_guard') ?>

</body>
</html>
