<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?> - <?=$profil['nama'] ?? 'Jimpitan'?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: { dark: '#0f172a' }
                }
            }
        }
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
    <style>
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .dark .glass { background: rgba(30, 41, 59, 0.9); }
        .modal-blur { backdrop-filter: blur(8px); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-200 min-h-screen pb-20">

    <nav class="sticky top-0 z-40 glass border-b border-slate-200 dark:border-slate-800 px-2 md:px-4 py-2 md:py-3 flex justify-between items-center">
        <div class="flex items-center gap-2 md:gap-3 flex-1 min-w-0">
            <a href="/keuangan/jurnal_jimpitan" onclick="if(window.showLoader) window.showLoader()" class="w-8 h-8 md:w-10 md:h-10 bg-indigo-600 rounded-lg md:rounded-xl flex-shrink-0 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <i class="fas fa-arrow-left text-xs md:text-base"></i>
            </a>
            <h1 class="text-sm md:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600 truncate ml-1">Hutang Jimpitan</h1>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-2 md:p-4 overflow-hidden">
        <!-- Search Bar -->
        <div class="mb-2 md:mb-6">
            <form action="" method="GET" class="relative">
                <input type="text" name="search" value="<?=esc($search ?? '')?>" placeholder="Cari warga..." 
                    class="w-full pl-8 pr-4 py-2 md:pl-12 md:py-4 bg-white dark:bg-slate-800 border-none rounded-lg md:rounded-2xl shadow-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white text-[10px] md:text-base">
                <i class="fas fa-search absolute left-3 md:left-5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] md:text-base"></i>
            </form>
        </div>

        <!-- KK List -->
        <div class="bg-white dark:bg-slate-800 rounded-xl md:rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <table class="w-full text-left text-[10px] md:text-sm table-fixed">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-[8px] md:text-[10px] uppercase text-slate-500 font-bold border-b border-slate-100 dark:border-slate-700">
                    <tr>
                        <th class="px-1 md:px-4 py-2 md:py-3 w-6 md:w-12 text-center">No</th>
                        <th class="px-1 md:px-4 py-2 md:py-3">Warga</th>
                        <th class="px-2 md:px-4 py-2 md:py-3 text-center hidden sm:table-cell">Kode KK</th>
                        <th class="px-1 md:px-4 py-2 md:py-3 text-center w-10 md:w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-700">
                    <?php if (empty($dataKK)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-6 opacity-50 text-[10px]">
                                <p>Tidak ditemukan</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($dataKK as $kk): ?>
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-1 md:px-4 py-1.5 md:py-2 text-center text-slate-400 font-medium"><?=$i++?></td>
                                <td class="px-1 md:px-4 py-1.5 md:py-2 min-w-0">
                                    <div class="font-bold text-slate-800 dark:text-white uppercase leading-none truncate"><?=$kk['kk_name']?></div>
                                    <div class="text-[7px] md:text-[9px] text-slate-400 mt-0.5 font-medium tracking-tighter truncate"><?=$kk['nikk']?></div>
                                </td>
                                <td class="px-2 md:px-4 py-1.5 md:py-2 text-center hidden sm:table-cell whitespace-nowrap">
                                    <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-md text-[9px] font-bold">
                                        <?=$kk['code_id']?>
                                    </span>
                                </td>
                                <td class="px-1 md:px-4 py-1.5 md:py-2 text-center">
                                    <a href="/keuangan/hutang_jimpitan/detail/<?=$kk['code_id']?>" onclick="if(window.showLoader) window.showLoader()"
                                        class="inline-flex w-7 h-7 md:w-8 md:h-8 bg-indigo-600 text-white rounded-lg items-center justify-center shadow-md hover:scale-110 active:scale-95 transition-all">
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Only keep minor search logic if needed, but the search is handled by Form GET
    </script>
    <?= $this->include('partials/loader') ?>
</body>
</html>
