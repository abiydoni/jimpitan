<!DOCTYPE html>
<html lang="en">
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
<body class="min-h-screen bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300">
    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Scan Hari Ini</h1>
        </div>
        <div class="flex items-center gap-2">

            <button id="themeToggle" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
            </button>
        </div>
    </nav>

    <main class="max-w-md mx-auto px-4 py-6 pb-24">
        <!-- Date Filter Section -->
        <!-- Date Filter Section -->
        <div class="mb-4 relative group cursor-pointer">
            <div class="absolute inset-0 opacity-0 z-10 w-full h-full overflow-hidden">
                <input type="date" id="dateFilter" value="<?= date('Y-m-d') ?>" class="w-full h-full cursor-pointer">
            </div>
            
            <div class="flex items-center justify-between p-4 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 group-hover:border-indigo-300 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i class="far fa-calendar-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">TANGGAL</h3>
                        <p class="text-xs font-bold text-slate-800 dark:text-white leading-none">Filter Data</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="dateDisplay" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 font-mono tracking-tight"><?= date('d/m/Y') ?></span>
                    <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="col-span-1 px-3 py-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5 truncate">Warga</div>
                <div class="text-lg font-black text-indigo-500 leading-tight" id="totalCount">0</div>
            </div>
            <div class="col-span-2 px-3 py-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mb-0.5 truncate">Total Dana Terkumpul</div>
                <div class="text-lg font-black text-emerald-500 truncate leading-tight" id="totalNominal">Rp 0</div>
            </div>
        </div>

        <!-- List Header -->
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Riwayat Realtime</h2>
            <div class="flex items-center gap-2">
                 <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-xs text-emerald-500 font-bold">Live</span>
            </div>
        </div>

        <!-- List Container -->
        <!-- List Container -->
        <div id="scanList" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
            <!-- Items injected by JS -->
            <div class="text-center py-10 text-slate-400 text-sm animate-pulse">
                Memuat data...
            </div>
        </div>
    </main>

    <script>
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        async function updateData() {
            try {
                const dateVal = document.getElementById('dateFilter').value;
                const response = await fetch('/scan/getRecentScans?date=' + dateVal, { skipLoader: true });
                const result = await response.json();
                
                if(result.status === 'success') {
                    // Update Stats safely
                    const countStr = result.count.toString();
                    const nominalStr = formatRupiah(result.total_nominal);

                    const countEl = document.getElementById('totalCount');
                    const nominalEl = document.getElementById('totalNominal');

                    if(countEl.innerText !== countStr) countEl.innerText = countStr;
                    if(nominalEl.innerText !== nominalStr) nominalEl.innerText = nominalStr;
                    
                    const sortedData = result.data.sort((a, b) => b.id - a.id);

                    renderList(sortedData);
                }
            } catch(e) {
                console.error("Failed fetching updates", e);
            }
        }

        function renderList(items) {
           const list = document.getElementById('scanList');
           
           if(!items || items.length === 0) {
               list.innerHTML = `
                   <div class="text-center py-12">
                       <i class="fas fa-clipboard-list text-2xl text-slate-300 mb-2"></i>
                       <p class="text-slate-400 text-xs">Belum ada scan hari ini.</p>
                   </div>
               `;
               return;
           }

           // Remove empty placeholder
           if(list.querySelector('.fa-clipboard-list') || list.innerText.includes('Memuat')) {
               list.innerHTML = '';
           }

           const existingMap = new Map();
           list.querySelectorAll('.scan-item').forEach(el => existingMap.set(el.dataset.id, el));
           const processedIds = new Set();

           items.forEach((item, index) => {
               const itemId = item.id ? String(item.id) : `${item.nama}-${item.waktu}`;
               processedIds.add(itemId);

               let el = existingMap.get(itemId);
               const number = index + 1;

               if (!el) {
                   el = document.createElement('div');
                   el.className = 'scan-item px-3 py-1 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center animate__animated animate__fadeIn last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors';
                   el.dataset.id = itemId;
                   updateItemContent(el, item, number);
               } else {
                   // Ensure no lingering animation classes on existing items to prevent re-triggering issues
                   if (el.classList.contains('animate__fadeIn')) {
                       el.classList.remove('animate__animated', 'animate__fadeIn');
                   }
                   updateItemContent(el, item, number);
               }

               // Smart Reconciliation: Only move if not in correct position
               const currentChild = list.children[index];
               if (currentChild !== el) {
                   if (currentChild) {
                       list.insertBefore(el, currentChild);
                   } else {
                       list.appendChild(el);
                   }
               }
           });

           existingMap.forEach((el, id) => {
               if(!processedIds.has(id)) el.remove();
           });
        }

        function updateItemContent(el, item, number) {
            // Create a signature of the content to prevent re-rendering identical HTML
            // Note: 'number' changes if position changes, which is desired.
            const signature = `${item.nama}|${item.waktu}|${item.nominal}|${number}`;
            
            if (el.dataset.sig === signature) return; // Skip update if identical
            el.dataset.sig = signature;

            el.innerHTML = `
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center text-[10px] font-bold font-mono shrink-0">
                        ${number}
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-bold text-slate-800 dark:text-white text-xs truncate leading-none mb-0.5">${item.nama}</h4>
                        <div class="flex items-center gap-1 text-[9px] text-slate-400 leading-none">
                            <span>${item.waktu}</span>
                            <span class="w-0.5 h-0.5 rounded-full bg-slate-300"></span>
                            <span class="truncate">${item.collector}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="font-bold text-emerald-500 text-xs leading-none">${formatRupiah(item.nominal)}</div>
                    <div class="text-[8px] font-bold text-slate-400 mt-0.5 bg-slate-100 dark:bg-slate-700 px-1 py-px rounded inline-block leading-none">
                        Berhasil
                    </div>
                </div>
            `;
        }

        // Initial Load
        updateData();

        // Realtime Polling (every 3 seconds)
        setInterval(updateData, 3000);

        // Dark Mode Logic
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        }

        if (themeToggle) {
            themeToggle.onclick = () => {
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            };
        }

        // Sync Theme Across Tabs
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                if (e.newValue === 'dark') html.classList.add('dark');
                else html.classList.remove('dark');
            }
        });

        // Date Filter Logic
        document.getElementById('dateFilter').addEventListener('change', (e) => {
             // Update Display
             const dateObj = new Date(e.target.value);
             const day = String(dateObj.getDate()).padStart(2, '0');
             const month = String(dateObj.getMonth() + 1).padStart(2, '0');
             const year = dateObj.getFullYear();
             document.getElementById('dateDisplay').innerText = `${day}/${month}/${year}`;

             // Reset UI state to loading style before fetch if desired, or just update
             document.getElementById('scanList').innerHTML = '<div class="text-center py-10 text-slate-400 text-sm animate-pulse">Memuat data...</div>';
             updateData();
        });
    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
