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
         .gradient-text { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300">
    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="<?= base_url('/') ?>" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Belum Scan</h1>
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
        <div class="mb-4 relative group cursor-pointer">
            <div class="absolute inset-0 opacity-0 z-10 w-full h-full overflow-hidden">
                <input type="date" id="filterDate" value="<?= date('Y-m-d') ?>" class="w-full h-full cursor-pointer">
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

        <!-- Count Indicator -->
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Sisa Belum Scan</h2>
            <span id="counter" class="bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 px-2 py-0.5 rounded text-[10px] font-bold shadow-sm border border-rose-200 dark:border-rose-800">
                ...
            </span>
        </div>

        <!-- List Container -->
        <div id="listContainer" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
            <!-- Items injected by JS -->
            <div class="py-10 text-center">
                <i class="fas fa-circle-notch fa-spin text-indigo-500 text-sm"></i>
            </div>
        </div>
    </main>

    <script>
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

        // Realtime Logic
        const filterDate = document.getElementById('filterDate');
        const listContainer = document.getElementById('listContainer');
        const counter = document.getElementById('counter');
        
        let existingIds = new Set();
        let isFirstLoad = true;

        async function fetchData() {
            try {
                const date = filterDate.value;
                const res = await fetch(`<?= base_url('scan/getNotScannedJson') ?>?date=${date}`, { skipLoader: true });
                const json = await res.json();

                if(json.status === 'success') {
                    updateUI(json.data, json.count);
                }
            } catch (err) {
                console.error("Polling Error:", err);
            }
        }

        function updateUI(items, count) {
            // Update Counter
            counter.innerText = count;

            if (items.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center py-10 animate__animated animate__fadeIn">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-2 text-green-500">
                            <i class="fas fa-check-double text-xl"></i>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">Semua Tuntas!</p>
                    </div>
                `;
                existingIds.clear();
                return;
            }

            // New Set of IDs for comparison
            const newIds = new Set(items.map(i => i.code_id));
            
            // 1. If First Load, Simple Render
            if(isFirstLoad) {
                renderAll(items);
                existingIds = newIds;
                isFirstLoad = false;
                return;
            }

            // 2. Identify Removed items (Just Scanned) -> Animate Out
            existingIds.forEach(id => {
                if(!newIds.has(id)) {
                    const el = document.getElementById(`item-${id}`);
                    if(el) {
                        el.classList.add('animate__fadeOutRight'); // Animate out
                        setTimeout(() => el.remove(), 500); // Remove from DOM
                    }
                }
            });

            // 3. Identify New items
            const hasNewDetails = items.filter(i => !existingIds.has(i.code_id)).length > 0;
            if(hasNewDetails || items.length !== existingIds.size) {
                 if(hasNewDetails) {
                      renderAll(items);
                 }
            }
            
            // Update reference
            existingIds = newIds;
        }

        function createCardHtml(item, index) {
            const number = index + 1;
            return `
                <div id="item-${item.code_id}" class="warga-item px-3 py-1.5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center animate__animated animate__fadeIn last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center font-bold font-mono text-[10px] shrink-0">
                            ${number}
                        </div>
                        <div class="leading-none min-w-0">
                            <h4 class="font-bold text-slate-800 dark:text-white text-xs mb-0.5 truncate">${item.kk_name}</h4>
                            <p class="text-[9px] text-slate-400 font-mono tracking-wide truncate md:w-auto w-32">${item.nikk}</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                       <i class="fas fa-clock text-slate-200 dark:text-slate-600/50 text-[10px]"></i>
                    </div>
                </div>
            `;
        }

        function renderAll(items) {
            listContainer.innerHTML = items.map((item, index) => createCardHtml(item, index)).join('');
        }

        // Init
        fetchData(); // Immediate
        
        // Poll
        setInterval(fetchData, 3000); // Every 3 seconds

        // Re-fetch on date change
        filterDate.onchange = (e) => {
             // Update Display
             const dateObj = new Date(e.target.value);
             const day = String(dateObj.getDate()).padStart(2, '0');
             const month = String(dateObj.getMonth() + 1).padStart(2, '0');
             const year = dateObj.getFullYear();
             document.getElementById('dateDisplay').innerText = `${day}/${month}/${year}`;

            listContainer.innerHTML = '<div class="py-10 text-center text-slate-400">Loading...</div>';
            isFirstLoad = true;
            fetchData();
        };

    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
