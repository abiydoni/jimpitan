<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Detail Pembayaran' ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        /* Fix SweetAlert z-index to be above custom modals (z-1100) */
        .swal2-container { z-index: 2000 !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300 pb-24 md:pb-6">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <a href="/payment/warga/<?= $tarif['kode_tarif'] ?>" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="flex flex-col">
                <h1 class="text-sm font-bold text-slate-500 dark:text-slate-400"><?= $tarif['nama_tarif'] ?></h1>
                <span class="text-lg font-bold gradient-text leading-tight"><?= $warga['nama'] ?></span>
            </div>
        </div>
        <div class="flex items-center space-x-2">
             <button onclick="openHistoryModal()" class="w-9 h-9 bg-rose-100 dark:bg-rose-900/30 text-rose-600 rounded-xl flex items-center justify-center hover:bg-rose-200 transition-all shadow-sm">
                <i class="fas fa-history"></i>
             </button>
             


             <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
             </button>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-6">
        <!-- Year Filter (Moved Below Navbar) -->

        <!-- Info Card -->
        <div class="glass rounded-[2rem] p-6 mb-6 border border-white/40 dark:border-white/5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 dark:text-white -ml-0.5">
                        Rp <?= number_format($tarif['tarif'], 0, ',', '.') ?>
                    </h2>
                    <p class="text-slate-400 font-medium text-sm mt-1">Tagihan / Periode</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <!-- Year Filter (Inside Card) -->
                     <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>
                        <div class="relative flex items-center bg-white/60 dark:bg-slate-800/60 backdrop-blur-md border border-white/50 dark:border-white/10 rounded-2xl shadow-sm transition-all duration-300 group-hover:shadow-md group-hover:bg-white dark:group-hover:bg-slate-800">
                            <div class="pl-3 py-1.5 flex items-center pointer-events-none gap-2 border-r border-slate-200 dark:border-white/10">
                                <i class="fas fa-calendar-alt text-indigo-500 text-[10px]"></i>
                                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Tahun</span>
                            </div>
                            <select onchange="window.location.href='?year='+this.value" class="appearance-none bg-transparent border-none text-slate-700 dark:text-slate-200 py-1.5 pl-2 pr-8 text-xs font-bold focus:ring-0 cursor-pointer outline-none">
                                <?php 
                                $currentYear = date('Y');
                                for($y = $currentYear + 1; $y >= $currentYear - 3; $y--): 
                                ?>
                                    <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?> class="bg-white dark:bg-slate-800"><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400 group-hover:text-indigo-500 transition-colors">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                     </div>

                    <div class="flex flex-col items-end gap-1">
                        <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-[10px] font-bold text-slate-500 border border-slate-200 dark:border-white/5">
                            <?= $warga['nikk'] ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment List -->
        <div class="space-y-2">
            
            <!-- Metode: Bulanan (1) -->
            <?php if($tarif['metode'] == 1): ?>
                <?php 
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                ?>
                <?php foreach($months as $num => $name): ?>
                    <?php 
                        $bill = $tarif['tarif'];
                        $paid = $paymentSummary[$num] ?? 0;
                        $remaining = $bill - $paid;
                        if($remaining < 0) $remaining = 0;
                        $status = ($paid >= $bill) ? 'Lunas' : ($paid > 0 ? 'Sebagian' : 'Belum');
                        $hasHistory = $paid > 0;
                    ?>
                    <div class="glass rounded-xl p-3 flex items-center justify-between hover:bg-white dark:hover:bg-slate-800/80 transition-all border border-white/40 dark:border-white/5 shadow-sm group">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-400 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors"><?= $num ?></span>
                                <h3 class="font-bold text-slate-800 dark:text-white text-base"><?= $name ?></h3>
                            </div>
                            <div class="flex items-center gap-2 mt-1 pl-8">
                                <?php if($status == 'Lunas'): ?>
                                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full flex items-center gap-1">
                                        <i class="fas fa-check-circle text-[9px]"></i> Lunas
                                    </span>
                                <?php elseif($status == 'Sebagian'): ?>
                                    <span class="text-[10px] text-amber-600 font-bold bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 rounded-full">Kurang: <?= number_format($remaining,0,',','.') ?></span>
                                <?php else: ?>
                                    <span class="text-[10px] text-slate-400 font-medium bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">Belum Bayar</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 pl-2">
                            <?php if($hasHistory): ?>
                                <button onclick="openHistoryModal(<?= $num ?>, '<?= $name ?>')" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-indigo-600 dark:text-slate-400 hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors flex items-center justify-center">
                                    <i class="fas fa-list-ul text-xs"></i>
                                </button>
                            <?php endif; ?>

                            <?php if($status != 'Lunas'): ?>
                                <button onclick="openPaymentModal(<?= $num ?>, '<?= $name ?>', <?= $remaining ?>)" 
                                        class="px-3 py-1.5 bg-indigo-600 text-white font-bold rounded-lg shadow-md hover:shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 active:scale-95 transition-all text-xs flex items-center gap-1.5">
                                    <span>Bayar</span>
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>
                            <?php elseif(!$hasHistory): ?>
                                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center">
                                    <i class="fas fa-check text-xs"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            <!-- Metode: Tahunan (2) or Selamanya (3) -->
            <?php else: ?>
                 <?php 
                    $bill = $tarif['tarif'];
                    // 0 represents the general pot for non-monthly
                    $paid = $paymentSummary[0] ?? 0; 
                    $remaining = $bill - $paid;
                    if($remaining < 0) $remaining = 0;
                    $status = ($paid >= $bill) ? 'Lunas' : ($paid > 0 ? 'Sebagian' : 'Belum');
                    
                    $label = ($tarif['metode'] == 2) ? "Tagihan Tahun $year" : "Tagihan Selamanya";
                ?>
                <div class="glass rounded-2xl p-6 flex flex-col items-center justify-center text-center space-y-4">
                    <h3 class="text-xl font-bold text-slate-800 dark:text-white"><?= $label ?></h3>
                     <div class="flex flex-col items-center">
                        <span class="text-sm text-slate-500">Kekurangan</span>
                        <span class="text-2xl font-bold text-rose-500">Rp <?= number_format($remaining,0,',','.') ?></span>
                    </div>

                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                        <div class="bg-emerald-500 h-full transition-all duration-500" style="width: <?= ($paid/$bill)*100 ?>%"></div>
                    </div>
                    
                    <?php if($status != 'Lunas'): ?>
                         <div class="w-full space-y-3">
                            <button onclick="openPaymentModal(0, '<?= $label ?>', <?= $remaining ?>)" 
                                    class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all">
                                Bayar Sekarang
                            </button>
                            <?php if($paid > 0): ?>
                                <button onclick="openHistoryModal(0, '<?= $label ?>')" class="w-full py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                                    <i class="fas fa-history"></i> Lihat Riwayat
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col gap-3 w-full">
                            <div class="flex items-center justify-center gap-2 text-emerald-500 font-bold bg-emerald-50 dark:bg-emerald-900/30 px-4 py-3 rounded-xl w-full">
                                <i class="fas fa-check-circle"></i> Lunas
                            </div>
                            <button onclick="openHistoryModal(0, '<?= $label ?>')" class="w-full py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-history"></i> Lihat Riwayat
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- History Modal -->
    <div id="historyModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeHistoryModal()"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl p-6 sm:p-8 max-h-[85vh] flex flex-col">
            <div class="flex flex-col gap-4 mb-4 shrink-0">
                <div class="flex justify-between items-center">
                    <h3 id="historyTitle" class="text-xl font-bold text-slate-800 dark:text-white">Riwayat Pembayaran</h3>
                    <button onclick="closeHistoryModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <!-- Filters -->
                <div class="flex gap-2">
                    <select id="historyYear" onchange="fetchPersonalHistory()" class="w-1/3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-2 py-3 text-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600 dark:text-slate-300 transition-all cursor-pointer">
                        <?php for($y = date('Y'); $y >= date('Y')-2; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <select id="historyFilter" onchange="renderHistoryList()" class="w-2/3 bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-2 py-3 text-sm focus:ring-2 focus:ring-indigo-500 font-medium text-slate-600 dark:text-slate-300 transition-all cursor-pointer">
                        <option value="">Semua Bulan</option>
                        <?php 
                        $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        foreach($monthNames as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div id="historyList" class="flex-1 overflow-y-auto custom-scrollbar pr-2 space-y-3 min-h-[200px]">
                <!-- JS Rendered Content -->
            </div>

            <!-- Total Footer -->
            <div class="mt-4 pt-4 border-t border-slate-100 dark:border-white/5 flex justify-between items-center shrink-0">
                <span class="text-slate-500 font-medium">Total Terbayar</span>
                <span id="historyTotal" class="text-lg font-bold text-indigo-600 dark:text-indigo-400">Rp 0</span>
            </div>
        </div>
    </div>


    <script>
        const themeToggle = document.getElementById('themeToggle');
        themeToggle.onclick = () => {
             const isDark = document.documentElement.classList.toggle('dark');
             localStorage.setItem('theme', isDark ? 'dark' : 'light');
        };

        const config = {
            kode_tarif: '<?= $tarif['kode_tarif'] ?>',
            nikk: '<?= $warga['nikk'] ?>',
            year: <?= $year ?>
        };
        
        // Pass PHP variable to JS
        const allPayments = <?= json_encode($rawPayments ?? []) ?>;

        function openPaymentModal(month, label, maxAmount) {
            Swal.fire({
                title: 'Bayar Iuran',
                text: `Tagihan tersisa: Rp ${new Intl.NumberFormat('id-ID').format(maxAmount)}`,
                input: 'number',
                inputValue: maxAmount,
                inputAttributes: {
                    min: 100, // min 100 perak
                    max: maxAmount,
                    step: 100
                },
                showCancelButton: true,
                confirmButtonText: 'Bayar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                showLoaderOnConfirm: true,
                preConfirm: (value) => {
                    const val = parseInt(value);
                    if (!val || val <= 0) {
                        Swal.showValidationMessage('Nominal tidak valid');
                        return false;
                    }
                    if (val > maxAmount) {
                        Swal.showValidationMessage(`Nominal tidak boleh melebihi sisa tagihan (Rp ${new Intl.NumberFormat('id-ID').format(maxAmount)})`);
                        return false;
                    }
                    return val;
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    sendPayment(month, result.value);
                }
            });
        }

        async function sendPayment(month, nominal) {
            try {
                const payload = {
                    kode_tarif: config.kode_tarif,
                    nikk: config.nikk,
                    tahun: config.year,
                    nominal: parseInt(nominal),
                    bulan: month 
                };

                const response = await fetch('/payment/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const res = await response.json();

                if(res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Pembayaran berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message || 'Terjadi kesalahan.', 'error');
                }

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Gagal menghubungkan ke server.', 'error');
            }
        }

        // History Functions
        let billingFilter = null; // Filter by Billing Month (Tagihan Bulan X)
        let personalPayments = []; // Data container
        let allowDelete = false; // Flag to control delete button

        async function openHistoryModal(monthFilter = null, monthName = '') {
            const filterEl = document.getElementById('historyFilter');
            const titleEl = document.getElementById('historyTitle');
            const yearEl = document.getElementById('historyYear');
            
            // Set billing filter (from row click)
            billingFilter = monthFilter;
            
            // Default Year to Current Page Year
            yearEl.value = config.year;

            if (billingFilter !== null) {
                // Opened from Specific Month Row (History Per Bulan)
                titleEl.textContent = `Riwayat Tagihan ${monthName}`;
                filterEl.value = ""; // Show all transactions for this specific bill
                allowDelete = true; // Allow deletion
            } else {
                // Opened from Header (Riwayat Umum)
                titleEl.textContent = `Riwayat Pembayaran`;
                // Default to Current Month
                const currentMonth = new Date().getMonth() + 1;
                filterEl.value = currentMonth;
                allowDelete = false; // Disable deletion
            }

            document.getElementById('historyModal').classList.remove('hidden');
            
            // Always fetch fresh data when opening or year changes
            await fetchPersonalHistory();
        }

        async function fetchPersonalHistory() {
            const listContainer = document.getElementById('historyList');
            const year = document.getElementById('historyYear').value;
            
            listContainer.innerHTML = '<div class="flex justify-center p-8"><i class="fas fa-spinner fa-spin text-slate-400"></i></div>';

            try {
                const res = await fetch(`/payment/history-personal/${config.kode_tarif}/${config.nikk}?year=${year}`, {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    skipLoader: true
                });
                const data = await res.json();
                
                if(data.status === 'error') throw new Error(data.message);
                
                personalPayments = data;
                renderHistory(personalPayments);

            } catch (e) {
                listContainer.innerHTML = `<div class="text-center text-rose-500 p-4">Error: ${e.message}</div>`;
            }
        }

        const monthNames = [
            "Lainnya", "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        function renderHistory(paymentGroups) {
            const listContainer = document.getElementById('historyList');
            const totalEl = document.getElementById('historyTotal');
            
            listContainer.innerHTML = '';
            
            // Mode Check: General (Header) vs Specific Month (Row)
            if (billingFilter !== null) {
                // --- SPECIFIC MONTH MODE (Flat List) ---
                
                // Find group for this month
                const group = paymentGroups.find(g => g.bulan == billingFilter);
                const transactions = group ? group.transaksi : [];
                
                // Calculate Total for this month
                const total = transactions.reduce((sum, p) => sum + parseInt(p.jumlah), 0);
                totalEl.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;

                if (transactions.length === 0) {
                     listContainer.innerHTML = `
                        <div class="text-center py-10 text-slate-400">
                            <i class="fas fa-receipt text-4xl mb-3 opacity-50"></i>
                            <p>Tidak ada riwayat untuk bulan ini.</p>
                        </div>`;
                } else {
                     let displayIndex = 1;
                     transactions.forEach(p => {
                        const date = new Date(p.tgl_bayar);
                        const dateStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
                        
                        const item = document.createElement('div');
                        item.className = 'flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-white/5';
                        item.innerHTML = `
                             <div class="flex-1">
                                <h4 class="font-bold text-slate-800 dark:text-white line-clamp-1 text-sm">
                                    <span class="text-slate-500 mr-1">${displayIndex}.</span>Pembayaran
                                </h4>
                                <div class="text-xs text-slate-500 mt-1">${dateStr}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">Rp ${new Intl.NumberFormat('id-ID').format(p.jumlah)}</span>
                                ${allowDelete ? `
                                    <button onclick="confirmDelete(${p.id_iuran})" class="w-8 h-8 rounded-full bg-rose-50 dark:bg-rose-900/20 text-rose-500 flex items-center justify-center hover:bg-rose-100 transition-colors shrink-0">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                ` : ''}
                            </div>
                        `;
                        listContainer.appendChild(item);
                        displayIndex++;
                    });
                }

            } else {
                // --- GENERAL HISTORY MODE (Grouped by Month) ---
                
                // Total is sum of all groups' total_bayar
                const total = paymentGroups.reduce((sum, grp) => sum + parseInt(grp.total_bayar), 0);
                totalEl.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;

                if (paymentGroups.length === 0) {
                     listContainer.innerHTML = `
                        <div class="text-center py-10 text-slate-400">
                            <i class="fas fa-receipt text-4xl mb-3 opacity-50"></i>
                            <p>Tidak ada riwayat pembayaran di tahun ini.</p>
                        </div>`;
                } else {
                     let displayIndex = 1;
                     paymentGroups.forEach(grp => {
                        // Fix: Label "Lainnya" -> "Tahunan" for non-monthly payments (index 0)
                        const monthName = parseInt(grp.bulan) === 0 ? 'Tahunan' : monthNames[parseInt(grp.bulan)];
                        const isLunas = grp.status === 'Lunas';
                        
                        // Get latest date (first in array due to desc sort)
                        const latestTrans = grp.transaksi[0];
                        const date = new Date(latestTrans.tgl_bayar);
                        const dateStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });

                        // Group Container (Simplified to Single Card)
                        const groupItem = document.createElement('div');
                        groupItem.className = 'bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-white/5 p-3 flex justify-between items-center';
                        
                        groupItem.innerHTML = `
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-400 text-sm">${displayIndex}.</span>
                                <div>
                                    <h4 class="font-bold text-slate-700 dark:text-slate-200 text-sm">${monthName}</h4>
                                    <div class="text-[10px] text-slate-400 mt-0.5">${dateStr}</div>
                                </div>
                            </div>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">Rp ${new Intl.NumberFormat('id-ID').format(grp.total_bayar)}</span>
                        `;

                        listContainer.appendChild(groupItem);
                        displayIndex++;
                    });
                }
            }
        }

        function closeHistoryModal() {
            document.getElementById('historyModal').classList.add('hidden');
        }

        async function confirmDelete(id) {
            const result = await Swal.fire({
                title: 'Hapus Data?',
                text: "Data pembayaran ini akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('id', id);

                    const response = await fetch('/payment/delete', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    
                    const res = await response.json();
                    
                    if(res.status === 'success') {
                        location.reload();
                    } else {
                        Swal.fire('Gagal', 'Tidak dapat menghapus data.', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                }
            }
        }
    </script>
    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

</body>
</html>
