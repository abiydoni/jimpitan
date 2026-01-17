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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { dark: '#0f172a' }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; -webkit-tap-highlight-color: transparent; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); }
        
        .gold-gradient { background: linear-gradient(135deg, #FFD700 0%, #FDB931 100%); }
        .silver-gradient { background: linear-gradient(135deg, #E0E0E0 0%, #BDBDBD 100%); }
        .bronze-gradient { background: linear-gradient(135deg, #CD7F32 0%, #A0522D 100%); }
        
        /* Floating Animation */
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: floating 3s ease-in-out infinite; }
        .animate-float-delay-1 { animation: floating 3.5s ease-in-out infinite; animation-delay: 0.5s; }
        .animate-float-delay-2 { animation: floating 4s ease-in-out infinite; animation-delay: 1s; }

        /* Crown Shake */
        @keyframes crown-shimmer {
            0%, 100% { transform: rotate(-5deg) scale(1); filter: drop-shadow(0 0 2px rgba(250, 204, 21, 0.5)); }
            50% { transform: rotate(5deg) scale(1.1); filter: drop-shadow(0 0 8px rgba(250, 204, 21, 0.8)); }
        }
        .crown-effect { animation: crown-shimmer 2s ease-in-out infinite; }

        /* Glow Pulse */
        @keyframes glow-pulse {
            0%, 100% { box-shadow: 0 0 15px rgba(99, 102, 241, 0.3); }
            50% { box-shadow: 0 0 25px rgba(99, 102, 241, 0.6); }
        }
        .glow-gold { animation: glow-pulse 2s infinite; }

        /* Shimmer Effect */
        @keyframes shimmer {
            0% { transform: translateX(-150%) rotate(45deg); opacity: 0; }
            10% { opacity: 1; }
            50% { opacity: 1; }
            100% { transform: translateX(150%) rotate(45deg); opacity: 0; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <!-- Header -->
    <div class="bg-indigo-600 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                 <a href="<?= base_url('/') ?>" class="p-2 -ml-2 hover:bg-white/10 rounded-full transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-lg font-bold">Scan Leaderboard</h1>
            </div>
            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
                <button id="themeToggle" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block text-amber-300"></i>
                </button>
                <!-- Reset Button -->
                <button onclick="confirmReset()" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-colors text-indigo-100" title="Reset Leaderboard">
                    <i class="fas fa-trash-alt text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.onclick = () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        };

        // Reset Logic
        function confirmReset() {
            Swal.fire({
                title: 'Reset Leaderboard?',
                text: "Semua status scan akan di-reset menjadi 0. Masukkan Password Admin:",
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Password Admin'
                },
                showCancelButton: true,
                confirmButtonText: 'Ya, Reset!',
                confirmButtonColor: '#e11d48', // Rose-600
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    return fetch(`<?= base_url('scan/reset') ?>`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `password=${encodeURIComponent(password)}`,
                        skipLoader: true
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(response.statusText)
                        }
                        return response.json()
                    })
                    .then(data => {
                        if (data.status !== 'success') {
                            throw new Error(data.message || 'Gagal melakukan reset')
                        }
                        return data
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error}`
                        )
                    })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Leaderboard telah di-reset.',
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            })
        }
    </script>

    <main class="max-w-md mx-auto px-4">
        
        <?php if(empty($rankings)): ?>
            <div class="text-center py-20 opacity-60">
                <i class="fas fa-trophy text-6xl mb-4 text-slate-300"></i>
                <p class="font-bold text-slate-500">Belum ada rekor tersimpan.</p>
                <p class="text-xs text-slate-400">Jadilah yang pertama!</p>
            </div>
        <?php else: ?>

            <!-- Podium Area -->
            <div class="flex justify-center items-end gap-3 mb-10 mt-6 relative">
                
                <!-- Background Glow for #1 -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-32 bg-yellow-400/20 blur-3xl rounded-full -z-10 animate-pulse"></div>

                <!-- Rank 2 (Silver) -->
                <?php if(isset($rankings[1])): ?>
                <div class="w-1/3 flex flex-col items-center animate__animated animate__fadeInUp animate-float-delay-1 relative z-0">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-full border-4 border-slate-300 shadow-lg overflow-hidden mb-3 relative flex items-center justify-center bg-slate-50">
                            <i class="fas fa-user text-2xl text-slate-400"></i>
                        </div>
                        <div class="absolute -bottom-1 -right-2 w-7 h-7 rounded-full silver-gradient flex items-center justify-center text-xs font-bold text-white ring-2 ring-white dark:ring-slate-800 shadow-md">
                            2
                        </div>
                    </div>
                    
                    <div class="text-center mt-2">
                        <h3 class="font-bold text-xs text-slate-600 dark:text-slate-300 w-full px-1 leading-tight">
                            <?= $rankings[1]['nama_u'] ?>
                        </h3>
                        <div class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full mt-1 border border-slate-200 dark:border-slate-700">
                            <i class="fas fa-bolt text-[10px] text-slate-400"></i>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400"><?= number_format($rankings[1]['total_scan']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Rank 1 (Gold) -->
                <?php if(isset($rankings[0])): ?>
                <div class="w-1/3 flex flex-col items-center z-10 animate__animated animate__zoomIn animate-float">
                    <div class="relative mb-1">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 text-4xl text-yellow-400 crown-effect z-20">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="w-24 h-24 rounded-full border-[6px] border-yellow-400 dark:border-yellow-500 shadow-[0_0_20px_rgba(250,204,21,0.5)] overflow-hidden relative flex items-center justify-center bg-yellow-50 dark:bg-yellow-900/20 glow-gold">
                            <i class="fas fa-user-astronaut text-4xl text-yellow-500 dark:text-yellow-400"></i>
                            
                            <!-- Shine Effect -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/30 to-transparent rotate-45 translate-y-full animate-[shimmer_3s_infinite]"></div>
                        </div>
                        <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 w-10 h-10 rounded-full gold-gradient flex items-center justify-center text-xl font-bold text-white ring-4 ring-white dark:ring-slate-900 shadow-xl z-20">
                            1
                        </div>
                    </div>
                    
                    <div class="text-center mt-4 relative">
                        <!-- Decorative laurels/wings can go here if needed -->
                        <h3 class="font-extrabold text-sm text-slate-800 dark:text-white w-full px-1 leading-tight bg-yellow-100 dark:bg-yellow-900/30 py-1 rounded-md border border-yellow-200 dark:border-yellow-700/50">
                            <?= $rankings[0]['nama_u'] ?>
                        </h3>
                        <div class="inline-flex items-center gap-1.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-3 py-1 rounded-full mt-1.5 shadow-lg shadow-indigo-500/30 scale-105">
                            <i class="fas fa-fire text-[10px] text-yellow-300 animate-pulse"></i>
                            <span class="text-xs font-bold"><?= number_format($rankings[0]['total_scan']) ?> Scan</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Rank 3 (Bronze) -->
                <?php if(isset($rankings[2])): ?>
                <div class="w-1/3 flex flex-col items-center animate__animated animate__fadeInUp animate-float-delay-2 relative z-0">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-full border-4 border-orange-300 shadow-lg overflow-hidden mb-3 relative flex items-center justify-center bg-orange-50">
                            <i class="fas fa-user text-2xl text-orange-400"></i>
                        </div>
                        <div class="absolute -bottom-1 -left-2 w-7 h-7 rounded-full bronze-gradient flex items-center justify-center text-xs font-bold text-white ring-2 ring-white dark:ring-slate-800 shadow-md">
                            3
                        </div>
                    </div>

                    <div class="text-center mt-2">
                        <h3 class="font-bold text-xs text-slate-600 dark:text-slate-300 w-full px-1 leading-tight">
                            <?= $rankings[2]['nama_u'] ?>
                        </h3>
                         <div class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full mt-1 border border-slate-200 dark:border-slate-700">
                            <i class="fas fa-bolt text-[10px] text-slate-400"></i>
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400"><?= number_format($rankings[2]['total_scan']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- List Rank 4-10+ -->
            <div class="space-y-1">
                <?php for($i = 3; $i < count($rankings); $i++): ?>
                    <?php 
                        $rank = $i + 1;
                        $iconAnim = '';
                        
                        // Determine Styles & Icons based on Rank
                        if ($rank <= 6) {
                            // Rank 4-6 (Runner Up) -> Medal
                            $cardClass = 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-100 dark:border-indigo-800/50';
                            $numClass = 'bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-300';
                            $textClass = 'text-indigo-900 dark:text-indigo-100';
                            $iconAnim = '<i class="fas fa-medal text-indigo-500 ml-2 animate__animated animate__heartBeat animate__infinite animate__slow"></i>';
                        } elseif ($rank <= 10) {
                            // Rank 7-10 (Top 10) -> Star
                            $cardClass = 'bg-blue-50/50 dark:bg-blue-900/10 border-blue-100 dark:border-blue-900/30';
                            $numClass = 'bg-blue-100 dark:bg-blue-800/50 text-blue-600 dark:text-blue-300';
                            $textClass = 'text-slate-800 dark:text-slate-200';
                            $iconAnim = '<i class="fas fa-star text-amber-400 ml-2 animate__animated animate__pulse animate__infinite"></i>';
                        } else {
                            // Rank 11+ (Standard)
                            $cardClass = 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-700';
                            $numClass = 'bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400';
                            $textClass = 'text-slate-700 dark:text-slate-300';
                        }
                    ?>
                    <div class="<?= $cardClass ?> p-2 rounded-lg border shadow-sm flex items-center justify-between animate__animated animate__fadeInUp" style="animation-delay: <?= 0.5 + ($i * 0.05) ?>s">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 flex items-center justify-center font-bold text-xs rounded-md <?= $numClass ?>">
                                <?= $rank ?>
                            </div>
                            <div class="flex items-center">
                                <span class="font-bold text-xs <?= $textClass ?> line-clamp-1">
                                    <?= $rankings[$i]['nama_u'] ?>
                                </span>
                                <?= $iconAnim ?>
                            </div>
                        </div>
                        <div class="text-right font-bold text-xs <?= $rank <= 6 ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-500 dark:text-slate-400' ?>">
                            <?= number_format($rankings[$i]['total_scan']) ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

        <?php endif; ?>

    </main>

    

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>


