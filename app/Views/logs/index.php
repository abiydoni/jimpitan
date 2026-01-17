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

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" onclick="window.showLoader()" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold gradient-text">Log Aktivitas</h1>
        </div>
        <div class="flex items-center space-x-3">
             <!-- Theme Toggle -->
             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        
        <!-- Search Bar -->
        <div class="mb-6 animate__animated animate__fadeIn">
            <div class="relative group">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                <input type="text" id="logSearch" placeholder="Cari aktivitas, user, atau keterangan..." 
                       class="w-full pl-11 pr-12 py-4 bg-white dark:bg-slate-800 border-none rounded-2xl shadow-sm text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                <button id="clearSearch" class="hidden absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Log Table (Maximum Density) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden animate__animated animate__fadeInUp">
            <div class="overflow-x-auto min-h-[300px]"> <!-- Set min-height -->
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-[9px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="p-2 w-14 align-top">Waktu</th>
                            <th class="p-2 w-20 align-top">User</th>
                            <th class="p-2 w-14 align-top">Aktifitas</th>
                            <th class="p-2 align-top">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 text-[10px]">
                        <?php foreach($logs as $log): ?>
                            <?php
                                $action = $log['action'];
                                $colorClass = 'text-slate-600 dark:text-slate-400';
                                if(strpos($action, 'DELETE') !== false) $colorClass = 'text-rose-600 dark:text-rose-500';
                                elseif(strpos($action, 'CREATE') !== false) $colorClass = 'text-emerald-600 dark:text-emerald-500';
                                elseif(strpos($action, 'UPDATE') !== false) $colorClass = 'text-amber-600 dark:text-amber-500';
                                elseif(strpos($action, 'LOGIN') !== false) $colorClass = 'text-blue-600 dark:text-blue-500';
                            ?>
                            <tr class="log-row hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group" 
                                data-search="<?= strtolower($log['username'] . ' ' . $log['action'] . ' ' . $log['description']) ?>">
                                
                                <!-- Waktu -->
                                <td class="p-1.5 align-top font-mono text-slate-500 dark:text-slate-400 leading-none">
                                    <span class="block font-bold text-slate-700 dark:text-slate-200"><?= date('d/m/y', strtotime($log['created_at'])) ?></span>
                                    <span class="text-[9px]"><?= date('H:i', strtotime($log['created_at'])) ?></span>
                                </td>

                                <!-- User & Role -->
                                <td class="p-1.5 align-top leading-none">
                                    <div class="font-bold text-slate-700 dark:text-slate-200"><?= $log['username'] ?></div>
                                    <div class="text-[8px] text-slate-400 uppercase font-bold mt-0.5"><?= $log['role'] ?></div>
                                </td>

                                <!-- Action -->
                                <td class="p-1.5 align-top font-bold <?= $colorClass ?> text-[9px] uppercase tracking-tight leading-none">
                                    <?= $log['action'] ?>
                                </td>

                                <!-- Description -->
                                <td class="p-1.5 align-top text-slate-600 dark:text-slate-300 leading-tight break-words">
                                    <?= $log['description'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <div id="paginationControls" class="flex justify-between items-center px-4 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <div class="text-[10px] text-slate-500 font-medium">
                    Hal <span id="currentPage">1</span> dari <span id="totalPages">1</span>
                </div>
                <div class="flex gap-1">
                    <button id="prevPage" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-xs">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button id="nextPage" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-600 hover:text-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-xs">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-20">
            <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                <i class="fas fa-search-minus text-3xl"></i>
            </div>
            <p class="text-slate-500">Tidak ada log yang cocok.</p>
        </div>

    </main>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Theme Toggle
            const themeToggle = document.getElementById('themeToggle');
            const html = document.documentElement;
            themeToggle.onclick = () => {
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            };

            // Search & Pagination Variables
            const searchInput = document.getElementById('logSearch');
            const clearBtn = document.getElementById('clearSearch');
            const rows = Array.from(document.querySelectorAll('.log-row')); 
            const emptyState = document.getElementById('emptyState');
            const paginationControls = document.getElementById('paginationControls');
            
            const rowsPerPage = 20;
            let currentPage = 1;
            let filteredRows = rows; // Initially all rows

            function updatePagination() {
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                
                // Ensure page is valid
                if (currentPage > totalPages) currentPage = totalPages || 1;
                if (currentPage < 1) currentPage = 1;

                // Update UI Text
                document.getElementById('currentPage').textContent = currentPage;
                document.getElementById('totalPages').textContent = totalPages || 1;
                
                // Disable/Enable Buttons
                document.getElementById('prevPage').disabled = currentPage === 1;
                document.getElementById('nextPage').disabled = (currentPage === totalPages || totalPages === 0);

                // Hide controls if no data
                paginationControls.style.display = filteredRows.length > 0 ? 'flex' : 'none';

                renderRows();
            }

            function renderRows() {
                // Hide ALL rows first (safest approach without complex DOM manipulation)
                rows.forEach(r => r.style.display = 'none');

                if (filteredRows.length === 0) {
                    emptyState.classList.remove('hidden');
                    return;
                } else {
                    emptyState.classList.add('hidden');
                }

                // Calculate slice for current page
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const pageRows = filteredRows.slice(start, end);

                // Show only rows for this page
                pageRows.forEach(row => {
                    row.style.display = ''; // Reset display to default (table-row)
                });
            }

            function performSearch(q) {
                if (!q) {
                    filteredRows = rows;
                } else {
                    filteredRows = rows.filter(row => {
                        const searchData = row.dataset.search;
                        return searchData.includes(q);
                    });
                }
                
                // Reset to page 1 on search
                currentPage = 1;
                updatePagination();
                
                if (q.length > 0) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }

            // Event Listeners
            searchInput.addEventListener('input', (e) => performSearch(e.target.value.toLowerCase()));

            clearBtn.onclick = () => {
                searchInput.value = '';
                performSearch('');
                searchInput.focus();
            };

            document.getElementById('prevPage').onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            };

            document.getElementById('nextPage').onclick = () => {
                const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            };

            // Initial Render
            updatePagination();
        });
    </script>
    <?= view('partials/loader') ?>
</body>
</html>
