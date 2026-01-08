<!DOCTYPE html>
<html lang="id" class="<?= session()->get('theme') === 'dark' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | Jimpitan</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: '#0f172a'
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.3); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.6); }
        }
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-50 dark:from-slate-900 dark:via-indigo-950 dark:to-purple-950 transition-colors duration-300 flex items-center justify-center p-4">
    
    <!-- Theme Toggle -->
    <button id="themeToggle" class="fixed top-6 right-6 w-12 h-12 rounded-full glass flex items-center justify-center text-slate-600 dark:text-slate-300 hover:scale-110 transition-all z-50 pulse-glow">
        <i class="fas fa-moon dark:hidden text-lg"></i>
        <i class="fas fa-sun hidden dark:block text-lg"></i>
    </button>

    <!-- Main Content -->
    <div class="max-w-2xl w-full text-center animate__animated animate__fadeIn">
        
        <!-- 404 Illustration -->
        <div class="mb-8 relative">
            <div class="text-[120px] sm:text-[160px] md:text-[200px] font-black gradient-text leading-none float-animation select-none">
                404
            </div>
            
            <!-- Floating Icons -->
            <div class="absolute inset-0 pointer-events-none">
                <i class="fas fa-question-circle absolute top-10 left-1/4 text-4xl text-indigo-400 opacity-20 animate__animated animate__fadeIn animate__delay-1s"></i>
                <i class="fas fa-exclamation-triangle absolute top-20 right-1/4 text-3xl text-purple-400 opacity-20 animate__animated animate__fadeIn animate__delay-2s"></i>
                <i class="fas fa-search absolute bottom-10 left-1/3 text-3xl text-pink-400 opacity-20 animate__animated animate__fadeIn animate__delay-3s"></i>
            </div>
        </div>

        <!-- Message -->
        <div class="glass rounded-3xl p-8 sm:p-10 mb-8 animate__animated animate__fadeInUp animate__delay-1s">
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-800 dark:text-white mb-4">
                Oops! Halaman Tidak Ditemukan
            </h1>
            <p class="text-slate-600 dark:text-slate-300 text-lg mb-6">
                Sepertinya halaman yang Anda cari sedang bersembunyi atau mungkin tidak pernah ada. 
                Jangan khawatir, mari kita bantu Anda kembali ke jalur yang benar!
            </p>
            
            <!-- Error Code -->
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-rose-100 dark:bg-rose-900/30 rounded-full text-rose-600 dark:text-rose-400 text-sm font-semibold">
                <i class="fas fa-exclamation-circle"></i>
                <span>Error Code: 404</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center animate__animated animate__fadeInUp animate__delay-2s">
            <a href="/" class="group relative px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl font-bold text-lg shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/50 hover:scale-105 transition-all overflow-hidden">
                <span class="relative z-10 flex items-center justify-center gap-2">
                    <i class="fas fa-home"></i>
                    Kembali ke Beranda
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            </a>
        </div>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        // Load saved theme
        if (localStorage.getItem('theme') === 'dark') {
            html.classList.add('dark');
        }
        
        themeToggle.onclick = () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        };
    </script>
</body>
</html>
