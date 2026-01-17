<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Rincian Tagihan' ?> - <?= $profil['nama'] ?? 'Jimpitan App' ?></title>
    
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
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
    </style>
     <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark text-slate-800 dark:text-white transition-colors duration-300">

    <!-- Header (Matching Users.php) -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" onclick="window.showLoader()" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text">Rincian Tagihan</h1>
        </div>
        <div class="flex items-center space-x-3">
             <!-- Theme Toggle -->
             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-4 space-y-4">
        
        <!-- Compact Hero Summary Card -->
        <div class="animate__animated animate__fadeInDown">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-fuchsia-600 p-5 text-white shadow-lg shadow-indigo-500/20 text-center">
                <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-32 h-32 bg-black/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col items-center justify-center gap-2">
                    <div>
                        <p class="text-indigo-100 text-[10px] font-bold uppercase tracking-widest leading-none mb-1">Total Tanggungan Anda</p>
                        <h2 class="text-3xl font-extrabold tracking-tight leading-tight">Rp <?= number_format($bill ?? 0, 0, ',', '.') ?></h2>
                         <p class="text-indigo-100/90 text-[10px] font-medium leading-none mt-1">
                            <?= ($bill ?? 0) > 0 ? 'Segera lakukan pembayaran.' : 'Terima kasih, Anda lunas!' ?>
                        </p>
                    </div>
                    

                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Card: Kewajiban -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden animate__animated animate__fadeInUp animate__delay-100ms flex flex-col h-full">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h3 class="font-bold text-xs text-slate-700 dark:text-white uppercase tracking-wide">Daftar Kewajiban</h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded">
                        Rp <?= number_format($totalObligation ?? 0, 0, ',', '.') ?>
                    </span>
                </div>
                
                <div class="p-1 flex-1 overflow-y-auto max-h-[350px] custom-scrollbar">
                    <?php if (!empty($billDetails)): ?>
                        <div class="space-y-px">
                            <?php $i = 1; foreach ($billDetails as $item): ?>
                                <div class="group flex justify-between items-center px-1 py-0.5 hover:bg-slate-50 dark:hover:bg-slate-800/50 rounded transition-all">
                                    <div class="flex items-start gap-1.5 leading-none">
                                        <span class="text-[9px] text-slate-300 font-mono"><?= $i++ ?>.</span>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-semibold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"><?= $item['item'] ?></span>
                                            <?php if ($item['remaining'] > 0): ?>
                                                <span class="text-[8px] text-rose-500 font-bold flex items-center gap-1 mt-px">
                                                    Kurang Rp <?= number_format($item['remaining'], 0, ',', '.') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-[8px] text-emerald-500 font-bold flex items-center gap-1 mt-px">
                                                    Lunas
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="text-right leading-none">
                                        <span class="block text-[10px] font-bold text-slate-800 dark:text-white">Rp <?= number_format($item['amount'], 0, ',', '.') ?></span>
                                        <?php if ($item['remaining'] > 0 && $item['remaining'] != $item['amount']): ?>
                                             <span class="text-[8px] text-slate-400">Bayar: <?= number_format($item['paid'], 0, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                            <i class="fas fa-receipt text-2xl mb-1 opacity-50"></i>
                            <span class="text-[10px]">Tidak ada data kewajiban.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card: Riwayat Pembayaran -->
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden animate__animated animate__fadeInUp animate__delay-200ms flex flex-col h-full">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                         <div class="w-6 h-6 rounded bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-xs">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3 class="font-bold text-xs text-slate-700 dark:text-white uppercase tracking-wide">Riwayat Pembayaran</h3>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded">
                        Rp <?= number_format($paid ?? 0, 0, ',', '.') ?>
                    </span>
                </div>

                <div class="p-1 flex-1 overflow-y-auto max-h-[350px] custom-scrollbar">
                    <?php 
                    $months = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    if (!empty($groupedPayments)): ?>
                        <div class="space-y-1">
                            <?php foreach ($groupedPayments as $groupName => $payments): ?>
                                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-md overflow-hidden">
                                    <div class="bg-slate-50 dark:bg-slate-800 px-2 py-1 border-b border-slate-100 dark:border-slate-700">
                                        <h4 class="text-[9px] font-extrabold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                                            <?= $groupName ?>
                                        </h4>
                                    </div>
                                    <div class="divide-y divide-slate-50 dark:divide-slate-800">
                                        <?php $j = 1; foreach ($payments as $p): 
                                            $monthName = (is_numeric($p['bulan']) && isset($months[$p['bulan']])) ? $months[$p['bulan']] : $p['bulan'];
                                        ?>
                                            <div class="flex justify-between items-center px-1 py-0.5 hover:bg-white dark:hover:bg-slate-700/50 rounded transition-colors cursor-default">
                                                <div class="flex items-start gap-1.5 leading-none">
                                                    <span class="text-[9px] text-slate-300 font-mono"><?= $j++ ?>.</span>
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] font-bold text-slate-700 dark:text-slate-200">
                                                            <?= ($monthName ? $monthName . ' ' : '') . $p['tahun'] ?>
                                                        </span>
                                                        <span class="text-[8px] text-slate-400 font-mono mt-px">
                                                            <?= date('d/m/y H:i', strtotime($p['tgl_bayar'])) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 px-1 py-px rounded">
                                                    +<?= number_format($p['jml_bayar'], 0, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                             <i class="fas fa-wallet text-2xl mb-1 opacity-50"></i>
                             <span class="text-[10px]">Belum ada pembayaran.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </main>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.onclick = () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        };
    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
