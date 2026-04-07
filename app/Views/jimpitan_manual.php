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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

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
         
         /* Tom Select Customization */
        .ts-control { border-radius: 1rem; padding: 0.8rem 1rem; border: none; background-color: #f8fafc; box-shadow: none; }
        .dark .ts-control { background-color: #1e293b !important; color: white !important; }
        .dark .ts-control .item { color: white !important; }
        .dark .ts-control input { color: white !important; background: transparent !important; }
        .ts-dropdown { border-radius: 1rem; overflow: hidden; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); z-index: 9999; }
        .dark .ts-dropdown { background-color: #1e293b !important; color: white !important; }
        .dark .ts-dropdown .option { color: #cbd5e1; }
        .dark .ts-dropdown .active { background-color: #334155; color: white; }
        .ts-wrapper.focus .ts-control { box-shadow: 0 0 0 2px #6366f1; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300">
    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Jimpitan Manual</h1>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openModal()" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg shadow-lg hover:bg-indigo-700 transition-all flex items-center gap-1">
                <i class="fas fa-plus"></i> Input
            </button>
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
        <div id="scanList" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-800 overflow-hidden shadow-sm">
            <!-- Items injected by JS -->
            <div class="text-center py-10 text-slate-400 text-sm animate-pulse">
                Memuat data...
            </div>
        </div>
    </main>

    <!-- MANUAL INPUT MODAL -->
    <div id="manualModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>
        
        <!-- Modal Content -->
        <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-[2rem] p-6 shadow-2xl transform transition-all scale-90 opacity-0 relative flex flex-col max-h-[90vh]" id="modalContent">
            
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Input Masal</h3>
                <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 flex items-center justify-center hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4 flex-1 overflow-hidden flex flex-col">
                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tanggal Jimpitan</label>
                    <input type="date" name="jimpitan_date" id="inputDate" value="<?= date('Y-m-d') ?>" required
                           class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white dark:[color-scheme:dark]">
                </div>

                <!-- Resident Search & List -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Pilih Warga (Belum Scan)</label>
                    
                    <div class="relative mb-2">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="modalSearch" placeholder="Cari Nama Warga..." 
                               class="w-full pl-10 pr-10 py-2 bg-slate-50 dark:bg-slate-900 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white">
                        <button id="clearSearch" class="hidden absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 text-[10px] items-center justify-center hover:bg-slate-300 transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="flex items-center justify-between px-1 mb-2">
                        <button type="button" onclick="toggleSelectAll()" id="selectAllBtn" class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Pilih Semua</button>
                        <span id="selectedCount" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">0 Terpilih</span>
                    </div>

                    <div id="residentList" class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-700 p-2 space-y-1 min-h-[200px]">
                        <!-- Resident items with checkboxes -->
                        <div class="text-center py-10 text-slate-400 text-xs italic">Memuat daftar warga...</div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" onclick="submitBulk()" id="submitBtn" class="w-full py-3.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center space-x-2 disabled:bg-slate-300 disabled:shadow-none" disabled>
                        <i class="fas fa-paper-plane text-sm"></i>
                        <span>Simpan Terpilih</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        }

        let allNotScanned = [];
        let filteredNotScanned = [];
        let selectedIds = new Set(); // Track selected IDs

        // --- LIST LOGIC ---
        async function updateData() {
            try {
                const dateVal = document.getElementById('dateFilter').value;
                const response = await fetch('/scan/getRecentScans?date=' + dateVal, { skipLoader: true });
                const result = await response.json();
                
                if(result.status === 'success') {
                    // Update Stats
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
                    if (list.children.length === 0) {
                         list.appendChild(el);
                    } else {
                        list.insertBefore(el, list.firstChild);
                    }
                } else {
                     if (el.classList.contains('animate__fadeIn')) {
                        el.classList.remove('animate__animated', 'animate__fadeIn');
                     }
                    updateItemContent(el, item, number);
                }
                
                const currentChild = list.children[index];
                if (currentChild !== el) {
                     if (currentChild) list.insertBefore(el, currentChild);
                     else list.appendChild(el);
                }
           });

           existingMap.forEach((el, id) => {
               if(!processedIds.has(id)) el.remove();
           });
        }

        function updateItemContent(el, item, number) {
              const signature = `${item.nama}|${item.waktu}|${item.nominal}|${number}`;
              if (el.dataset.sig === signature) return;
              el.dataset.sig = signature;

            const isSystem = (item.collector === 'System' || item.collector.toLowerCase() === 'system');
            
            const badgeClass = isSystem 
                ? 'text-amber-500 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800/50' 
                : 'text-slate-400 bg-slate-100 dark:bg-slate-700 border border-transparent';
            const badgeText = isSystem ? 'By System' : 'Berhasil';

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
                              <span class="truncate ${isSystem ? 'text-amber-500 font-medium' : ''}">${item.collector}</span>
                          </div>
                      </div>
                  </div>
                  <div class="text-right shrink-0 flex items-center gap-2">
                      <div class="text-right">
                          <div class="font-bold text-emerald-500 text-xs leading-none">${formatRupiah(item.nominal)}</div>
                          <div class="text-[8px] font-bold mt-0.5 px-1 py-px rounded inline-block leading-none ${badgeClass}">
                              ${badgeText}
                          </div>
                      </div>
                      <button onclick="deleteScan('${item.id}', '${item.nama}')" class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-500 flex items-center justify-center hover:bg-red-100 transition-colors">
                          <i class="fas fa-trash-alt text-xs"></i>
                      </button>
                  </div>
              `;
        }

        // --- MODAL LOGIC ---
        const modal = document.getElementById('manualModal');
        const backdrop = document.getElementById('modalBackdrop');
        const content = document.getElementById('modalContent');
        const residentList = document.getElementById('residentList');
        const modalSearch = document.getElementById('modalSearch');
        const inputDate = document.getElementById('inputDate');
        
        async function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                content.classList.remove('scale-90', 'opacity-0');
            }, 10);
            
            inputDate.value = document.getElementById('dateFilter').value;
            selectedIds.clear(); // Clear on open
            fetchNotScanned();
        }

        async function fetchNotScanned() {
            residentList.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs italic">Memuat daftar warga...</div>';
            try {
                const res = await fetch(`/scan/getNotScannedBulk?date=${inputDate.value}`);
                const result = await res.json();
                allNotScanned = result.data || [];
                selectedIds.clear(); // Clear selection when date changes
                applyFilter();
            } catch (e) {
                residentList.innerHTML = '<div class="text-center py-10 text-red-400 text-xs italic">Gagal memuat data</div>';
            }
        }

        function applyFilter() {
            const query = modalSearch.value.toLowerCase();
            const clearBtn = document.getElementById('clearSearch');
            
            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
                clearBtn.classList.add('flex');
            } else {
                clearBtn.classList.remove('flex');
                clearBtn.classList.add('hidden');
            }

            filteredNotScanned = allNotScanned.filter(w => 
                w.nama.toLowerCase().includes(query) || 
                (w.value && w.value.toLowerCase().includes(query)) ||
                (w.nikk && w.nikk.toLowerCase().includes(query))
            );
            renderResidentItems();
        }

        function renderResidentItems() {
            if (filteredNotScanned.length === 0) {
                residentList.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs italic">Warga tidak ditemukan</div>';
                return;
            }

            residentList.innerHTML = filteredNotScanned.map(w => `
                <label class="flex items-center gap-3 p-3 bg-white dark:bg-slate-800 rounded-xl cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors border border-transparent has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/30">
                    <input type="checkbox" name="resident_ids" value="${w.value}" 
                           ${selectedIds.has(w.value) ? 'checked' : ''} 
                           onchange="handleCheckboxChange(this)" 
                           class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600">
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-800 dark:text-white leading-tight">${w.nama}</div>
                        <div class="text-[9px] text-slate-400 leading-none mt-1">${w.value}</div>
                    </div>
                </label>
            `).join('');
            updateSelectedCount();
        }

        function handleCheckboxChange(cb) {
            if (cb.checked) {
                selectedIds.add(cb.value);
            } else {
                selectedIds.delete(cb.value);
            }
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = selectedIds.size;
            document.getElementById('selectedCount').innerText = `${count} Terpilih`;
            document.getElementById('submitBtn').disabled = count === 0;
            
            // Update Select All Btn Title (Check against filtered list)
            const allCheckboxes = document.querySelectorAll('input[name="resident_ids"]');
            const allInFilterChecked = Array.from(allCheckboxes).every(cb => cb.checked);
            document.getElementById('selectAllBtn').innerText = (allInFilterChecked && allCheckboxes.length > 0) ? 'Batal Semua' : 'Pilih Semua';
        }

        function toggleSelectAll() {
            const allCheckboxesInView = document.querySelectorAll('input[name="resident_ids"]');
            const allCheckedInView = Array.from(allCheckboxesInView).every(cb => cb.checked);
            const shouldSelect = !allCheckedInView;
            
            allCheckboxesInView.forEach(cb => {
                cb.checked = shouldSelect;
                if (shouldSelect) selectedIds.add(cb.value);
                else selectedIds.delete(cb.value);
            });
            updateSelectedCount();
        }

        function closeModal() {
            backdrop.classList.add('opacity-0');
            content.classList.add('scale-90', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modalSearch.value = '';
                selectedIds.clear();
            }, 300);
        }

        backdrop.addEventListener('click', closeModal);
        modalSearch.addEventListener('input', applyFilter);
        inputDate.addEventListener('change', fetchNotScanned);

        document.getElementById('clearSearch').onclick = () => {
            modalSearch.value = '';
            applyFilter();
            modalSearch.focus();
        };

        async function deleteScan(id, nama) {
            const confirm = await Swal.fire({
                title: 'Hapus Data?',
                text: `Yakin ingin menghapus jimpitan ${nama}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (confirm.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', didOpen: () => Swal.showLoading() });
                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    const res = await fetch('/scan/deleteScan', { method: 'POST', body: formData });
                    const result = await res.json();
                    if (result.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Terhapus', timer: 1000, showConfirmButton: false });
                        updateData();
                    } else {
                        Swal.fire('Gagal', result.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Gagal menghubungi server', 'error');
                }
            }
        }

        async function submitBulk() {
            if (selectedIds.size === 0) return;

            Swal.fire({
                title: 'Menyimpan...',
                timerProgressBar: true,
                didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();
            selectedIds.forEach(id => formData.append('code_ids[]', id));
            formData.append('jimpitan_date', inputDate.value);
            formData.append('alasan', 'Input Masal via Manual');

            try {
                const response = await fetch('/scan/storeBatchManual', {
                    method: 'POST',
                    body: formData
                });
                const res = await response.json();

                if (res.status === 'success') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    closeModal();
                    updateData();
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }

        // --- PAGE LOGIC ---
        document.addEventListener('DOMContentLoaded', () => {
            updateData();
            setInterval(updateData, 5000);
        });

        document.getElementById('dateFilter').addEventListener('change', (e) => {
             const dateObj = new Date(e.target.value);
             const day = String(dateObj.getDate()).padStart(2, '0');
             const month = String(dateObj.getMonth() + 1).padStart(2, '0');
             const year = dateObj.getFullYear();
             document.getElementById('dateDisplay').innerText = `${day}/${month}/${year}`;
             document.getElementById('scanList').innerHTML = '<div class="text-center py-10 text-slate-400 text-sm animate-pulse">Memuat data...</div>';
             updateData();
        });

        const html = document.documentElement;
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        }

        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.onclick = () => {
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            };
        }
    </script>
    
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
