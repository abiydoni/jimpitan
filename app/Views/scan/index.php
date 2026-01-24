<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Jimpitan</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Html5Qrcode -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; background: #000; }
        #reader video { object-fit: cover; border-radius: 12px; }
        
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 15px rgba(99, 102, 241, 0.5); }
        }
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); box-shadow: 0 0 15px rgba(99, 102, 241, 0.5); }
        }
        .animate-heartbeat {
            animation: heartbeat 2s infinite ease-in-out;
        }
        .flash-on {
            background: linear-gradient(135deg, #facc15 0%, #ca8a04 100%);
            box-shadow: 0 0 20px rgba(250, 204, 21, 0.6);
            border: 2px solid #fef08a;
            transform: scale(1.1);
        }
        .flash-off {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .flash-off {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100 transition-colors duration-200">

    <!-- Header -->
    <div class="bg-indigo-600 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="<?= base_url('/') ?>" class="p-2 -ml-2 hover:bg-white/10 rounded-full transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="text-center">
                <h1 class="text-lg font-bold"><?= $title ?></h1>
                <p id="headerClock" class="text-[10px] text-indigo-100 font-mono leading-tight">...</p>
            </div>
            <button id="themeToggle" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-300"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 max-w-md mx-auto w-full flex flex-col gap-4">
        
        <!-- Scan Stats Card -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex items-center justify-between transition-colors relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="fas fa-qrcode text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-bold tracking-wide">Total Scan Hari Ini</p>
                    <p id="totalScanDisplay" class="text-lg font-bold text-slate-800 dark:text-white">
                        <?= $scanCount ?? 0 ?> KK - Rp <?= number_format($totalNominal ?? 0, 0, ',', '.') ?>
                    </p>
                </div>
            </div>
            <button onclick="openDetailModal()" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition-all cursor-pointer active:scale-95 animate-heartbeat shadow-lg shadow-indigo-500/30 border border-indigo-400/20">
                <i class="fas fa-list-ul mr-1"></i> Detail
            </button>
        </div>

        <!-- Scanner Area -->
        <div class="bg-white dark:bg-slate-800 p-2 rounded-2xl shadow-md border border-slate-100 dark:border-slate-700 transition-colors relative group">
            <div id="reader" class="w-full text-center"></div>
            
            <!-- Flash Toggle Button -->
            <button id="flashToggle" onclick="toggleFlash()" class="absolute top-4 right-4 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 hidden z-20 flash-off animate-heartbeat group/btn">
                <i class="fas fa-bolt text-lg text-yellow-400 group-hover/btn:animate-pulse"></i>
            </button>

            <p class="text-center text-xs text-slate-400 mt-2 pb-1">Arahkan kamera ke QR Code Warga</p>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="fixed inset-0 z-[100] flex items-center justify-center invisible opacity-0 transition-opacity duration-300">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeDetailModal()"></div>
        <div class="bg-white dark:bg-slate-900 w-[95%] max-w-md rounded-2xl shadow-2xl transform scale-95 opacity-0 transition-all duration-300 pointer-events-auto h-[80vh] flex flex-col">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-indigo-600 text-white rounded-t-2xl">
                <div><h3 class="font-bold text-lg">Daftar Scan Hari Ini</h3><p class="text-xs text-indigo-200">Realtime Update</p></div>
                <div>
                    <button onclick="refreshDetails()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 inline-flex items-center justify-center transition-colors"><i class="fas fa-sync-alt text-sm"></i></button>
                    <button onclick="closeDetailModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 inline-flex items-center justify-center transition-colors ml-2"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div id="detailList" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar"></div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                <div class="flex justify-between items-center text-sm font-bold text-slate-600 dark:text-slate-300"><span>Total Data:</span><span id="detailCount" class="text-indigo-600 dark:text-indigo-400">0</span></div>
            </div>
        </div>
    </div>

    <!-- Audio Effect (Handled by JS) --> 
    <!-- Create a simple beep using JS if file incorrect, but prefer valid asset path. For now assume system sound or JS beep -->

    <script>
        // --- Theme Logic ---
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        if (themeToggle) {
            themeToggle.onclick = () => {
                html.classList.toggle('dark');
                const isDark = html.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            };
        }
        // --- End Theme Logic ---

        const beep = new Audio("https://cdn.freesound.org/previews/335/335908_5865517-lq.mp3"); // Generic beep online
        let isProcessing = false;

        // Check for Secure Context (HTTPS or Localhost) first
        if (!window.isSecureContext) {
             Swal.fire({
                title: 'Akses Kamera Dibatasi',
                html: `
                    <div class="text-left text-sm text-slate-600">
                        <p class="mb-2">Browser memblokir kamera.</p>
                        <p class="mb-2 font-bold">Pastikan Anda sudah mengaktifkan izin kamera di browser.</p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Saya Paham',
                confirmButtonColor: '#6366f1'
            });
        }

        // Global Error Handler for Permissions
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && (
                event.reason.toString().includes('NotAllowedError') || 
                event.reason.toString().includes('NotFoundError') ||
                event.reason.toString().includes('NotReadableError')
            )) {
                Swal.fire({
                    title: 'Akses Kamera Bermasalah',
                    html: `
                        <div class="text-left text-sm">
                            <p class="mb-2 font-bold text-rose-600">${event.reason.message || 'Izin ditolak'}</p>
                            <p class="mb-1">Kemungkinan penyebab:</p>
                            <ul class="list-disc pl-5 mb-2">
                                <li>Anda memblokir izin kamera.</li>
                                <li>Web dibuka via <b>HTTP</b> (bukan Localhost/HTTPS).</li>
                                <li>Kamera sedang dipakai aplikasi lain.</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning'
                });
            }
        });

        // Dynamic QR Box Logic
        function getQrBoxSize() {
            const width = window.innerWidth;
            const size = Math.min(250, width * 0.7); 
            return { width: size, height: size };
        }

        // Audio Play Safety
        async function playBeep() {
            try {
                await beep.play();
            } catch (err) {
                console.log("Audio play blocked (Autoplay policy):", err);
            }
        }

        // Handle Scan Success
        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;
            
            playBeep();
            
            processScan(decodedText, false);
        }

        // Separated process function to handle recursive delete confirmation
        function processScan(codeId, confirmDelete = false) {
             // IMMEDIATE FEEDBACK: Show Loading
             Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                showConfirmButton: false,
                background: '#fff',
                customClass: { popup: 'rounded-2xl' },
                didOpen: () => { Swal.showLoading(); }
             });

             const payload = { code_id: codeId };
             if (confirmDelete) payload.confirm_delete = true;

             fetch('<?= base_url('scan/store') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                skipLoader: true
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Update Stats Immediately
                    updateStats();

                    // DESIGN: Success Save
                    Swal.fire({
                        title: 'Berhasil Masuk!',
                        html: `
                            <div class="mt-2">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 animate__animated animate__bounceIn">
                                    <i class="fas fa-check text-3xl text-green-500"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800">${data.data.nama}</h3>
                                <p class="text-sm text-slate-500 mb-1">Jimpitan Tercatat</p>
                                <b class="text-2xl text-indigo-600 block my-2">Rp ${new Intl.NumberFormat('id-ID').format(data.data.nominal)}</b>
                            </div>
                        `,
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#fff',
                        customClass: {
                            popup: 'rounded-3xl shadow-xl'
                        }
                    }).then(() => {
                        isProcessing = false;
                    });

                } else if (data.status === 'confirm_delete') {
                    // DESIGN: Confirmation Before Delete
                    Swal.fire({
                        title: 'Data Sudah Ada!',
                        html: `
                            <p class="text-sm text-slate-600 mb-4">Warga ini sudah scan jimpitan hari ini.</p>
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 mb-4">
                                <p class="font-bold text-slate-800">${data.data.nama}</p>
                            </div>
                            <p class="text-sm font-semibold text-rose-600">Apakah ingin MENGHAPUS data ini?</p>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus Data',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-2xl',
                            confirmButton: 'rounded-xl px-4 py-2 font-bold',
                            cancelButton: 'rounded-xl px-4 py-2 font-bold'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                             // Call again with confirm_delete = true
                             processScan(codeId, true);
                        } else {
                            // Canceled
                            isProcessing = false;
                        }
                    });

                } else if (data.status === 'deleted') {
                    // Update stats immediately
                    updateStats();

                    // DESIGN: Success Delete
                    Swal.fire({
                        title: 'Data Dihapus!',
                        html: `
                            <div class="mt-2">
                                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-3 animate__animated animate__headShake">
                                    <i class="fas fa-trash-alt text-3xl text-rose-500"></i>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">${data.data.nama}</h3>
                                <p class="text-sm text-rose-500 mt-1">Transaksi dibatalkan.</p>
                            </div>
                        `,
                        showConfirmButton: false,
                        timer: 2000,
                        customClass: {
                            popup: 'rounded-3xl shadow-xl'
                        }
                    }).then(() => {
                        isProcessing = false;
                    });

                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error'
                    }).then(() => {
                        isProcessing = false;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                isProcessing = false;
            });
        }

        // Global Instance (Single Source of Truth)
        const html5QrCode = new Html5Qrcode("reader");
        let isScanning = false;
        let isFlashOn = false;

        // Start Scanner manually
        async function startScanner() {
            if (isScanning) return; // Prevent double start

            try {
                // FIXED: EXACT LEGACY CONFIG (Jimpitan New Style)
                const config = { 
                    fps: 20, 
                    qrbox: 250
                };
                
                await html5QrCode.start(
                    { facingMode: "environment" }, 
                    config,
                    onScanSuccess,
                    (errorMessage) => {}
                );
                
                isScanning = true;

                // FORCE SHOW FLASH BUTTON
                document.getElementById('flashToggle').classList.remove('hidden');
                updateFlashUI(); 

            } catch (err) {
                console.error(err);
                // Clear state just in case
                isScanning = false;
                
                document.getElementById('reader').innerHTML = `
                    <div class="p-4 bg-red-50 text-red-600 rounded-lg text-sm font-bold">
                        Gagal Membuka Kamera: ${err.message}<br>
                        <button onclick="location.reload()" class="mt-2 bg-red-600 text-white px-3 py-1 rounded">Coba Refresh</button>
                    </div>
                `;
            }
        }

        // --- Detail Modal Logic ---
        const detailModal = document.getElementById('detailModal');
        // ... (existing code)

        // ... (inside toggleFlash)
                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: 'error',
                    title: 'Gagal menyalakan lampu.',
                    text: 'Error Sistem: ' + (err.message || 'Constraint Failed'), // Show actual error
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        }

        // Init
        startScanner();

        // --- Detail Modal Logic ---
        const detailModal = document.getElementById('detailModal');
        const detailList = document.getElementById('detailList');
        const detailCountSpan = document.getElementById('detailCount');
        const totalScanDisplay = document.getElementById('totalScanDisplay');

        function openDetailModal() {
            detailModal.classList.remove('invisible', 'opacity-0');
            // Remove hidden classes
            const content = detailModal.querySelector('div.transform');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100'); // Ensure full visibility
            loadDetails(); // Fetch data
        }

        function closeDetailModal() {
            const content = detailModal.querySelector('div.transform');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                detailModal.classList.add('opacity-0', 'invisible');
            }, 300);
        }

        function refreshDetails() {
            // Animate Icon
            const icon = detailModal.querySelector('.fa-sync-alt');
            icon.classList.add('fa-spin');
            loadDetails().then(() => {
                setTimeout(() => icon.classList.remove('fa-spin'), 500);
            });
        }

        async function loadDetails(silent = false) {
            try {
                // Assuming Scan::getRecentScans is mapped to /scan/getRecentScans or similar
                // CodeIgniter 4 controller/method routing is often auto-mapped or configured in Routes.php
                // I will use scan/getRecentScans based on default convention
                const response = await fetch('<?= base_url('scan/getRecentScans') ?>?t=' + new Date().getTime(), { skipLoader: silent }); 
                const data = await response.json();
                
                if(data.status === 'success') {
                    renderDetails(data.data);
                    // Update stats string
                    const formattedTotal = new Intl.NumberFormat('id-ID').format(data.total_nominal || 0);
                    const statsString = `${data.count} KK - Rp ${formattedTotal}`;
                    
                    totalScanDisplay.innerText = statsString;
                    detailCountSpan.innerText = statsString;
                }
            } catch(e) {
                console.error(e);
                if (!silent) {
                    detailList.innerHTML = '<p class="text-center text-red-500 py-4">Gagal memuat data.</p>';
                }
            }
        }

        function renderDetails(items) {
            const list = document.getElementById('detailList');
            
            if(!items || items.length === 0) {
                list.innerHTML = `
                    <div class="text-center py-8 opacity-50">
                        <i class="fas fa-inbox text-3xl mb-2 text-slate-300"></i>
                        <p class="text-[10px]">Belum ada data hari ini.</p>
                    </div>
                `;
                return;
            }

            // Remove empty state placeholder
            if(list.querySelector('.fa-inbox')) list.innerHTML = '';

            const existingMap = new Map();
            list.querySelectorAll('.scan-item').forEach(el => existingMap.set(el.dataset.id, el));
            const processedIds = new Set();

            items.forEach((item, index) => {
                const itemId = item.id ? String(item.id) : `${item.nama}-${item.waktu}`;
                processedIds.add(itemId);

                let el = existingMap.get(itemId);
                // Numbering: Index + 1
                const number = index + 1;

                if (!el) {
                    el = document.createElement('div');
                    // Ultra Compact: p-1, text-xs, border-b only (no margin between items for table look)
                    el.className = 'scan-item bg-white dark:bg-slate-800 px-2 py-1.5 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center animate__animated animate__fadeIn first:rounded-t-lg last:border-0 last:rounded-b-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors';
                    el.dataset.id = itemId;
                    updateElementContent(el, item, number);
                } else {
                    updateElementContent(el, item, number);
                }
                list.appendChild(el);
            });

            existingMap.forEach((el, id) => {
                if (!processedIds.has(id)) el.remove();
            });
        }

        function updateElementContent(el, item, number) {
            el.innerHTML = `
                <div class="flex gap-2 items-center min-w-0">
                    <div class="w-5 h-5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center text-[10px] font-bold font-mono shrink-0">
                        ${number}
                    </div>
                    <div class="min-w-0 truncate">
                        <p class="text-xs font-bold text-slate-800 dark:text-white leading-none truncate">${item.nama}</p>
                        <p class="text-[9px] text-slate-400 dark:text-slate-500 leading-none mt-0.5 truncate">
                           ${item.collector}
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0 pl-2">
                    <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 leading-none">Rp ${new Intl.NumberFormat('id-ID').format(item.nominal)}</p>
                    <span class="text-[9px] text-slate-400 font-mono mt-0.5 block">${item.waktu}</span>
                </div>
            `;
        }

        function updateStats() {
            // Re-fetch everything
             loadDetails(true);
        }

        // Realtime Polling (Every 5 seconds)
        setInterval(() => {
            loadDetails(true);
        }, 3000);

        // Flash/Torch Control
        // Removed checkFlashCapability to force button show
        
        async function toggleFlash() {
            let success = false;
            const targetState = !isFlashOn;

            // Method 1: Html5Qrcode Helper
            if (html5QrCode) {
                try {
                    await html5QrCode.applyVideoConstraints({ advanced: [{ torch: targetState }] });
                    success = true;
                } catch (err) {
                    console.log("Method 1 (Lib) failed:", err);
                }
            }

            // Method 2: Native Track Manipulation (Stronger)
            if (!success) {
                try {
                    const video = document.querySelector('#reader video');
                    if (video && video.srcObject) {
                        const track = video.srcObject.getVideoTracks()[0];
                        
                        // Force constraints
                        await track.applyConstraints({
                            advanced: [{ torch: targetState }]
                        });
                        success = true;
                    }
                } catch (err) {
                     console.log("Method 2 (Native) failed:", err);
                     // Method 3: Fallback for some weird devices (brightness/fillLight)
                     try {
                        const video = document.querySelector('#reader video');
                        const track = video.srcObject.getVideoTracks()[0];
                        await track.applyConstraints({
                            advanced: [{ fillLightMode: targetState ? "flash" : "off" }]
                        });
                        success = true;
                     } catch(e) {}
                }
            }

            if (success) {
                isFlashOn = targetState;
                updateFlashUI();
            } else {
                // If it fails, force UI update anyway to 'pretend' but warn execution failed?
                // No, better to show error.
                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: 'error',
                    title: 'Gagal menyalakan lampu.',
                    text: 'Gagal akses hardware. Coba refresh halaman.',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        }

        function updateFlashUI() {
            const btn = document.getElementById('flashToggle');
            const icon = btn.querySelector('i');
            
            // Clean slate for icon to avoid class conflict
            icon.className = 'fas fa-bolt text-lg transition-colors duration-300';

            if (isFlashOn) {
                // State: ON
                btn.classList.remove('flash-off', 'animate-heartbeat'); 
                btn.classList.add('flash-on');
                
                // Icon: White, Pulsing
                icon.classList.add('text-white', 'animate-pulse');
            } else {
                // State: OFF
                btn.classList.remove('flash-on');
                btn.classList.add('flash-off', 'animate-heartbeat'); 
                
                // Icon: Yellow, Also Pulsing (to match button heartbeat)
                icon.classList.add('text-yellow-400', 'animate-pulse');
            }
        }

        // Realtime Clock
        function updateClock() {
            const now = new Date();
            const optionsDate = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            const dateStr = now.toLocaleDateString('id-ID', optionsDate);
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
            
            // Side by side layout
            document.getElementById('headerClock').innerHTML = `${dateStr} &bull; ${timeStr}`;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Init
    </script>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
