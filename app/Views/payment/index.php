<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Pembayaran' ?></title>
    
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
        .gradient-text { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300 pb-24 md:pb-6">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text">Pilih Pembayaran</h1>
        </div>
        <div class="flex items-center space-x-3">
             <button onclick="openGlobalHistory()" class="w-9 h-9 bg-rose-100 dark:bg-rose-900/30 text-rose-600 rounded-xl flex items-center justify-center hover:bg-rose-200 transition-all shadow-sm">
                <i class="fas fa-history"></i>
             </button>
             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <!-- Tarif Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach($tarifs as $t): ?>
                <a href="/payment/warga/<?= $t['kode_tarif'] ?>" class="block group">
                    <div class="glass rounded-[2rem] p-6 hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 border border-transparent hover:border-indigo-500/20 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 rounded-full blur-2xl -mr-10 -mt-10 group-hover:scale-125 transition-transform duration-500"></div>
                        
                        <div class="relative flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl shadow-lg shadow-indigo-500/30 group-hover:rotate-6 transition-transform duration-300">
                                <i class="bx <?= $t['icon'] ? $t['icon'] : 'bx-money' ?>"></i> <!-- Using 'bx' might fail if Boxicons not loaded, fallback to font awesome logic or load boxicons -->
                                <!-- Fallback if icon string is fontawesome -->
                                <?php if(strpos($t['icon'], 'bx') === false): ?>
                                    <i class="fas fa-wallet"></i>
                                <?php else: ?>
                                    <!-- Need Boxicons CDN -->
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-1"><?= $t['nama_tarif'] ?></h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">
                                    Rp <?= number_format($t['tarif'], 0, ',', '.') ?>
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                <i class="fas fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Global History Modal -->
    <div id="globalHistoryModal" class="fixed inset-0 z-[1050] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeGlobalHistory()"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 sm:p-8 animate__animated animate__zoomIn animate__faster max-h-[85vh] flex flex-col">
            <div class="flex flex-col gap-4 mb-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Global</h3>
                    </div>
                    <button onclick="closeGlobalHistory()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <!-- Filters -->
                <div class="flex gap-2">
                    <select id="globalHistoryYear" onchange="fetchGlobalSummary()" class="w-1/3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-2 py-3 text-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600 dark:text-slate-300 transition-all cursor-pointer">
                        <?php for($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <select id="globalHistoryFilter" onchange="fetchGlobalSummary()" class="w-2/3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-2 py-3 text-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600 dark:text-slate-300 transition-all cursor-pointer">
                        <option value="">Semua Bulan</option>
                        <?php 
                        $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        foreach($monthNames as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div id="globalHistoryList" class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3 min-h-[200px]">
                <!-- Load -->
                <div class="flex flex-col items-center justify-center h-40 text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <span>Memuat data...</span>
                </div>
            </div>

             <!-- Total Footer -->
             <div class="mt-4 pt-4 border-t border-slate-100 dark:border-white/5 flex justify-between items-center shrink-0">
                <span class="text-slate-500 font-medium">Total Terkumpul</span>
                <span id="globalHistoryTotal" class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Rp 0</span>
            </div>
        </div>
    </div>

    <!-- Boxicons for Tarif Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.onclick = () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        };

        // Global History Logic
        let globalSummary = [];

        async function openGlobalHistory() {
            document.getElementById('globalHistoryModal').classList.remove('hidden');
            
            // Default to current month
            const currentMonth = new Date().getMonth() + 1;
            document.getElementById('globalHistoryFilter').value = currentMonth;
            
            // Reset Year to current
            document.getElementById('globalHistoryYear').value = new Date().getFullYear();

            await fetchGlobalSummary();
        }

        function closeGlobalHistory() {
             document.getElementById('globalHistoryModal').classList.add('hidden');
        }

        async function fetchGlobalSummary() {
            const listContainer = document.getElementById('globalHistoryList');
            const year = document.getElementById('globalHistoryYear').value;
            const month = document.getElementById('globalHistoryFilter').value;

            listContainer.innerHTML = '<div class="flex justify-center p-8"><i class="fas fa-spinner fa-spin text-slate-400"></i></div>';

            try {
                // Fetch summary from new endpoint
                const res = await fetch(`/payment/summary-global?year=${year}&month=${month}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    skipLoader: true
                });
                
                if (!res.ok) throw new Error(res.statusText);

                const data = await res.json();
                if(data.status === 'error') throw new Error(data.message);

                globalSummary = data;
                renderGlobalSummary();
            } catch (e) {
                console.error(e);
                listContainer.innerHTML = 
                    `<div class="text-center text-rose-500 p-4">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i><br>
                        Gagal memuat data:<br><span class="text-xs">${e.message}</span>
                    </div>`;
            }
        }

        function renderGlobalSummary() {
            const listContainer = document.getElementById('globalHistoryList');
            const totalEl = document.getElementById('globalHistoryTotal');
            
            listContainer.innerHTML = '';
            
            const total = globalSummary.reduce((sum, item) => sum + parseInt(item.total), 0);
            totalEl.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;

            if (globalSummary.length === 0) {
                 listContainer.innerHTML = `
                    <div class="text-center py-10 text-slate-400">
                        <i class="fas fa-receipt text-4xl mb-3 opacity-50"></i>
                        <p>Tidak ada data pembayaran.</p>
                    </div>`;
            } else {
                globalSummary.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'w-full p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-white/5 animate__animated animate__fadeIn flex items-center justify-between mb-2';
                    el.innerHTML = `
                        <div>
                            <div class="font-bold text-slate-700 dark:text-slate-200 text-lg">${item.nama_tarif}</div>
                            <div class="text-xs text-slate-500">${item.count} Transaksi</div>
                        </div>
                        <div class="font-bold text-indigo-600 dark:text-indigo-400 text-lg">
                            Rp ${new Intl.NumberFormat('id-ID').format(item.total)}
                        </div>
                    `;
                    listContainer.appendChild(el);
                });
            }
        }
    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
