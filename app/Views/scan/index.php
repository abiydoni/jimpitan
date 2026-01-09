<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Scan QR' ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Html5Qrcode -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        #reader { width: 100%; border-radius: 12px; overflow: hidden; background: #000; }
        #reader video { object-fit: cover; border-radius: 12px; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Header -->
    <div class="bg-indigo-600 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="<?= base_url('/') ?>" class="p-2 -ml-2 hover:bg-white/10 rounded-full transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Scan Jimpitan</h1>
            <div class="w-8"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 max-w-md mx-auto w-full flex flex-col gap-4">
        
        <!-- Tariff Info -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-coins text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase font-bold tracking-wide">Tarif Aktif</p>
                    <p class="text-lg font-bold text-slate-800"><?= $tariff['nama_tarif'] ?? 'Jimpitan' ?></p>
                </div>
            </div>
            <span class="text-xl font-bold text-indigo-600">Rp <?= number_format($tariff['tarif'] ?? 500, 0, ',', '.') ?></span>
        </div>

        <!-- Scanner Area -->
        <div class="bg-white p-2 rounded-2xl shadow-md border border-slate-100">
            <div id="reader" class="w-full text-center"></div>
            <p class="text-center text-xs text-slate-400 mt-2 pb-1">Arahkan kamera ke QR Code Warga</p>
        </div>

        <!-- Manual Input Fallback -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Input Manual (Tanpa Kamera)</h3>
            <div class="flex gap-2">
                <input type="text" id="manualCode" class="flex-1 bg-slate-50 border border-slate-200 text-slate-800 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 outline-none" placeholder="Ketik Kode Warga (Contoh: KK001)">
                <button onclick="handleManualInput()" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 text-sm font-bold transition-colors">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <p class="text-[10px] text-slate-400 mt-2 italic">*Gunakan ini jika kamera bermasalah atau untuk tes lokal.</p>
        </div>

        <!-- Recent Scans -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-slate-700 ml-1">Riwayat Sesi Ini</h3>
            <div id="scanHistory" class="space-y-2">
                <!-- History Items will replace here -->
                <div id="emptyHistory" class="text-center py-8 text-slate-400 text-sm italic">
                    Belum ada scan baru.
                </div>
            </div>
        </div>

    </div>

    <!-- Audio Effect -->
    <audio id="beepSound" src="<?= base_url('assets/beep.mp3') ?>"></audio> 
    <!-- Create a simple beep using JS if file incorrect, but prefer valid asset path. For now assume system sound or JS beep -->

    <script>
        const beep = new Audio("https://cdn.freesound.org/previews/335/335908_5865517-lq.mp3"); // Generic beep online
        let isProcessing = false;

        function onScanSuccess(decodedText, decodedResult) {
            if (isProcessing) return;
            isProcessing = true;
            
            beep.play();

            // Send to server
            fetch('<?= base_url('scan/store') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code_id: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    Swal.fire({
                        title: 'Berhasil Masuk!',
                        html: `${data.data.nama}<br><b class="text-xl text-indigo-600">Rp ${new Intl.NumberFormat('id-ID').format(data.data.nominal)}</b><br><span class="text-sm text-slate-500">Data tersimpan</span>`,
                        icon: 'success',
                        timer: 3000, // 3 seconds
                        showConfirmButton: false
                    }).then(() => {
                        isProcessing = false;
                        addHistoryItem(data.data);
                    });
                } else if (data.status === 'deleted') {
                    Swal.fire({
                        title: 'Data Dibatalkan!',
                        html: `${data.data.nama}<br><span class="text-sm text-red-500">Jimpitan hari ini dihapus (Scan Ganda)</span>`,
                        icon: 'warning',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        isProcessing = false;
                        // Optional: Remove from history or just reload list logic if implemented fully
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error'
                    }).then(() => {
                        isProcessing = false; // Allow retry
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                isProcessing = false;
            });
        }

        // Check for Secure Context (HTTPS or Localhost)
        if (!window.isSecureContext) {
            Swal.fire({
                title: 'Mode Tes Lokal',
                html: `
                    <div class="text-left text-sm text-slate-600">
                        <p class="mb-2">Akses kamera otomatis dimatikan oleh browser karena Anda sedang di <b>Jaringan Lokal (HTTP)</b>.</p>
                        <ul class="list-disc pl-5 space-y-1 text-xs">
                            <li>Tenang, fitur ini akan <b>aktif otomatis</b> saat aplikasi di-onlinekan (Hosting HTTPS).</li>
                            <li>Untuk tes sekarang, gunakan <b>Input Manual</b> di bawah.</li>
                        </ul>
                    </div>
                `,
                icon: 'info', // Changed from warning to info (less scary)
                confirmButtonText: 'Oke, Siap',
                confirmButtonColor: '#6366f1'
            });
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { 
                fps: 10, 
                qrbox: {width: 250, height: 250},
                rememberLastUsedCamera: true
            },
            /* verbose= */ false);

        // Render with specific error handling
        html5QrcodeScanner.render(onScanSuccess, (errorMessage) => {
            // parse error, ignore standard scanning errors
            // Only alert if it's a permission error (usually happens during start, but scanner handles it differently)
        });

        // Hook into internal failure if possible, or just rely on the scanner's own UI for permission
        // Html5QrcodeScanner automatically shows "Request Camera Permissions" button. 
        // If it fails, it usually shows text. We can try to catch global errors.
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && event.reason.toString().includes('NotAllowedError')) {
                Swal.fire('Izin Kamera Ditolak', 'Mohon izinkan akses kamera di pengaturan browser Anda.', 'error');
            }
        });

        function handleManualInput() {
            const code = document.getElementById('manualCode').value.trim();
            if (!code) {
                Swal.fire('Input Kosong', 'Silakan ketik kode warga terlebih dahulu.', 'warning');
                return;
            }
            onScanSuccess(code, null);
            document.getElementById('manualCode').value = ''; // Clear input
        }

        // Allow Enter key
        document.getElementById('manualCode').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') handleManualInput();
        });

        function addHistoryItem(data) {
            const container = document.getElementById('scanHistory');
            const empty = document.getElementById('emptyHistory');
            if(empty) empty.remove();

            const item = `
                <div class="bg-white p-3 rounded-lg shadow-sm border border-slate-100 flex justify-between items-center animate__animated animate__fadeInDown">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-check text-xs"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">${data.nama}</p>
                            <p class="text-[10px] text-slate-500">${data.waktu}</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Rp ${new Intl.NumberFormat('id-ID').format(data.nominal)}</span>
                </div>
            `;
            container.insertAdjacentHTML('afterbegin', item);
        }
    </script>
</body>
</html>
