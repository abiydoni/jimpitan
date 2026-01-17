<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Pilih Warga' ?></title>
    
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
        .gradient-text { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300 pb-6">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/payment" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex flex-col">
                <h1 class="text-sm font-bold text-slate-500 dark:text-slate-400">Pembayaran</h1>
                <span class="text-lg font-bold gradient-text leading-tight"><?= $tarif['nama_tarif'] ?></span>
            </div>
        </div>
        <div class="flex items-center space-x-2">
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
        <!-- Search -->
        <div class="mb-6 animate__animated animate__fadeIn">
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" id="searchInput" placeholder="Cari Nama KK atau NIKK..." 
                       class="w-full pl-11 pr-12 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl shadow-sm text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                <button id="clearSearch" class="hidden absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>

        <!-- List Warga -->
        <div id="wargaList" class="space-y-3">
            <?php foreach($warga_list as $w): ?>
                <?php $isExempt = in_array($w['nikk'], $exempt_nikk ?? []); ?>
                <a href="<?= $isExempt ? 'javascript:void(0)' : '/payment/detail/'.$tarif['kode_tarif'].'/'.$w['nikk'] ?>" 
                   class="warga-card block rounded-2xl p-4 transition-all duration-200 border border-transparent animate__animated animate__fadeInUp <?= $isExempt ? 'bg-slate-100 dark:bg-slate-800/40 cursor-not-allowed' : 'glass hover:bg-white dark:hover:bg-slate-800/80 hover:border-indigo-500/20 active:scale-[0.99]' ?>"
                   data-name="<?= strtolower($w['nama']) ?>"
                   data-nikk="<?= strtolower($w['nikk']) ?>">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold shrink-0 <?= $isExempt ? 'bg-slate-200 text-slate-400 dark:bg-slate-700 dark:text-slate-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' ?>">
                                <?= substr($w['nama'], 0, 1) ?>
                            </div>
                            <div>
                                <h4 class="font-bold line-clamp-1 <?= $isExempt ? 'text-slate-500 dark:text-slate-500' : 'text-slate-800 dark:text-white' ?>"><?= $w['nama'] ?></h4>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs font-mono <?= $isExempt ? 'text-slate-400' : 'text-slate-500 dark:text-slate-400' ?>"><?= $w['nikk'] ?></span>
                                    <span class="text-[10px] bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 px-1.5 py-0.5 rounded <?= $isExempt ? 'opacity-50' : '' ?>">RT <?= $w['rt'] ?></span>
                                    <?php if($isExempt): ?>
                                        <span class="text-[10px] bg-gradient-to-r from-orange-500 to-rose-500 text-white px-2 py-0.5 rounded-full font-bold shadow-sm animate-pulse flex items-center">
                                            <i class="fas fa-check-circle mr-1"></i>Bebas Iuran
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if(!$isExempt): ?>
                        <div class="text-slate-300">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-20">
            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                <i class="fas fa-users-slash text-3xl"></i>
            </div>
            <p class="text-slate-500">Warga tidak ditemukan.</p>
        </div>
    </main>

    <!-- Global History Modal -->
    <div id="globalHistoryModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeGlobalHistory()"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 sm:p-8 animate__animated animate__zoomIn animate__faster max-h-[85vh] flex flex-col">
            <div class="flex flex-col gap-4 mb-4 shrink-0">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Semua Warga</h3>
                    <button onclick="closeGlobalHistory()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <!-- Filters -->
                <div class="flex gap-2">
                    <select id="globalHistoryYear" onchange="fetchGlobalHistory()" class="w-1/3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-2 py-3 text-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600 dark:text-slate-300 transition-all cursor-pointer">
                        <?php for($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <select id="globalHistoryFilter" onchange="renderGlobalHistory()" class="w-2/3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-2 py-3 text-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600 dark:text-slate-300 transition-all cursor-pointer">
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

    <script>
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.onclick = () => {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        };

        // Search Logic
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearch');
        const cards = document.querySelectorAll('.warga-card');
        const emptyState = document.getElementById('emptyState');

        function performSearch(q) {
            let hasResult = false;
            cards.forEach(card => {
                const name = card.dataset.name;
                const nikk = card.dataset.nikk;
                if (name.includes(q) || nikk.includes(q)) {
                    card.style.display = 'block';
                    hasResult = true;
                } else {
                    card.style.display = 'none';
                }
            });
            emptyState.style.display = hasResult ? 'none' : 'block';
            
            if (q.length > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        searchInput.oninput = (e) => performSearch(e.target.value.toLowerCase());
        clearBtn.onclick = () => {
            searchInput.value = '';
            performSearch('');
            searchInput.focus();
        };

        // Global History Logic
        let globalPayments = [];
        const kodeTarif = '<?= $tarif['kode_tarif'] ?>';

        async function openGlobalHistory() {
            document.getElementById('globalHistoryModal').classList.remove('hidden');
            
            // Set default filter to current month
            const currentMonth = new Date().getMonth() + 1;
            document.getElementById('globalHistoryFilter').value = currentMonth;

            if(globalPayments.length === 0) {
               await fetchGlobalHistory();
            } else {
                renderGlobalHistory();
            }
        }

        function closeGlobalHistory() {
             document.getElementById('globalHistoryModal').classList.add('hidden');
        }

        async function fetchGlobalHistory() {
            try {
                const year = document.getElementById('globalHistoryYear').value;
                const res = await fetch(`/payment/history-global/${kodeTarif}?year=${year}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    skipLoader: true
                });
                
                if (!res.ok) {
                     // Try to parse error message
                     const errData = await res.json().catch(() => ({}));
                     throw new Error(errData.message || res.statusText);
                }

                const data = await res.json();
                
                // If the response is the error object structure I just built
                if(data.status === 'error') {
                    throw new Error(data.message);
                }

                globalPayments = data;
                renderGlobalHistory();
            } catch (e) {
                console.error(e);
                document.getElementById('globalHistoryList').innerHTML = 
                    `<div class="text-center text-rose-500 p-4">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i><br>
                        Gagal memuat data:<br><span class="text-xs">${e.message}</span>
                    </div>`;
            }
        }

        function renderGlobalHistory() {
            const listContainer = document.getElementById('globalHistoryList');
            const totalEl = document.getElementById('globalHistoryTotal');
            const filterVal = document.getElementById('globalHistoryFilter').value;
            const year = document.getElementById('globalHistoryYear').value;
            
            listContainer.innerHTML = '';
            
            // 1. Process Data & Calculate Grand Total
            let grandTotal = 0;
            let hasData = false;
            let displayIndex = 1;

            globalPayments.forEach(group => {
                // Filter Transactions inside the group
                let transactions = group.transactions;
                
                if (filterVal !== "") {
                    transactions = transactions.filter(p => {
                        const d = new Date(p.tgl_bayar);
                        return (d.getMonth() + 1) == filterVal;
                    });
                }
                
                // If group has relevant transactions
                if (transactions.length > 0) {
                    hasData = true;
                    
                    // Group Total (for those visible transactions)
                    const groupTotal = transactions.reduce((sum, p) => sum + parseInt(p.jumlah), 0);
                    grandTotal += groupTotal;

                    // Yearly Status Badge logic
                    // Note: is_lunas_tahun is based on YEARLY total, not monthly view.
                    // So we always show the yearly status badge even if viewing a specific month.
                    const isLunasYear = group.is_lunas_tahun;
                    const badge = isLunasYear 
                        ? `<span class="ml-2 text-[10px] bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 px-1.5 py-0.5 rounded font-bold">Lunas</span>`
                        : `<span class="ml-2 text-[10px] bg-amber-100 dark:bg-amber-900/30 text-amber-600 px-1.5 py-0.5 rounded font-bold">Belum Lunas</span>`;

                    // Render Group Card (Compact Summary)
                    const card = document.createElement('div');
                    card.className = 'glass rounded-xl p-2 mb-1 animate__animated animate__fadeIn flex justify-between items-center hover:bg-white dark:hover:bg-slate-800 transition-colors border border-transparent hover:border-indigo-500/20';
                    
                    card.innerHTML = `
                         <div class="flex-1 min-w-0 pr-2">
                            <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-0 leading-tight">
                                <span class="text-slate-500 mr-1">${displayIndex}.</span>${group.nama || 'Warga'}
                            </h4>
                            <div class="flex items-center gap-2">
                                ${badge}
                                <span class="text-[10px] text-slate-400">
                                    ${new Date(transactions[transactions.length-1].tgl_bayar).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'})}
                                </span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                             <div class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">Rp ${new Intl.NumberFormat('id-ID').format(group.total_paid_year)}</div>
                             <div class="text-[10px] text-slate-400">${transactions.length} Tx</div>
                        </div>
                    `;
                    
                    listContainer.appendChild(card);
                    displayIndex++;
                }
            });

            // Set Grand Total
            totalEl.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}`;

            if (!hasData) {
                 listContainer.innerHTML = `
                    <div class="text-center py-10 text-slate-400">
                        <i class="fas fa-receipt text-4xl mb-3 opacity-50"></i>
                        <p>Tidak ada riwayat${filterVal ? ' di bulan ini' : ''}.</p>
                    </div>`;
            }
        }
    </script>
    <!-- Bottom Nav -->

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
