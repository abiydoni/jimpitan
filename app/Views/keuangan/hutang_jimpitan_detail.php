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
            <a href="/keuangan/hutang_jimpitan" onclick="if(window.showLoader) window.showLoader()" class="w-8 h-8 md:w-10 md:h-10 bg-indigo-600 rounded-lg md:rounded-xl flex-shrink-0 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <i class="fas fa-arrow-left text-xs md:text-base"></i>
            </a>
            <h1 class="text-sm md:text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600 truncate ml-1">Rincian Hutang</h1>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-2 md:p-4 overflow-hidden">
        
        <!-- Header Info -->
        <div class="bg-white dark:bg-slate-800 p-3 md:p-6 rounded-xl md:rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-700 mb-3 md:mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 md:gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 md:w-14 md:h-14 bg-indigo-600 rounded-xl md:rounded-2xl flex-shrink-0 flex items-center justify-center text-white text-lg md:text-2xl shadow-lg shadow-indigo-500/30">
                        <i class="fas fa-house-user"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm md:text-xl font-bold dark:text-white leading-none truncate"><?=$kk['kk_name']?></h2>
                        <p class="text-[8px] md:text-[10px] text-slate-500 uppercase font-bold tracking-widest mt-1 truncate"><?=$kk['code_id']?> • <?=$kk['nikk']?></p>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-700/50 p-1.5 md:p-3 rounded-xl flex items-center justify-between md:justify-start gap-2 border border-slate-100 dark:border-slate-700">
                    <span class="text-[9px] md:text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Tahun</span>
                    <select id="yearSelect" onchange="changeYear(this.value)" class="bg-white dark:bg-slate-800 border-none rounded-lg text-[10px] md:text-sm font-bold focus:ring-2 focus:ring-indigo-500 transition-all px-2 py-1 md:px-4 md:py-2">
                        <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?=$y?>" <?=$year == $y ? 'selected' : ''?>><?=$y?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>

        <?php 
            $totalTarget = array_sum(array_column($monthlyData, 'target'));
            $totalPaid = array_sum(array_column($monthlyData, 'total_paid'));
            $totalDebt = array_sum(array_column($monthlyData, 'debt'));
        ?>
        <!-- Stats Summary -->
        <div class="grid grid-cols-3 gap-2 md:gap-4 mb-4 md:mb-6">
            <div class="bg-white dark:bg-slate-800 p-2 md:p-4 rounded-xl md:rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm">
                <p class="text-[8px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Target</p>
                <p class="text-[10px] md:text-lg font-bold text-slate-700 dark:text-white">Rp <?=number_format($totalTarget, 0, ',', '.')?></p>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/10 p-2 md:p-4 rounded-xl md:rounded-2xl border border-emerald-100 dark:border-emerald-800/30 shadow-sm">
                <p class="text-[8px] md:text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1">Bayar</p>
                <p class="text-[10px] md:text-lg font-bold text-emerald-700 dark:text-emerald-300">Rp <?=number_format($totalPaid, 0, ',', '.')?></p>
            </div>
            <div class="bg-rose-50 dark:bg-rose-900/10 p-2 md:p-4 rounded-xl md:rounded-2xl border border-rose-100 dark:border-rose-800/30 shadow-sm">
                <p class="text-[8px] md:text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-widest mb-1">Hutang</p>
                <p class="text-[10px] md:text-lg font-bold text-rose-700 dark:text-rose-300">Rp <?=number_format($totalDebt, 0, ',', '.')?></p>
            </div>
        </div>

        <!-- Monthly Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl md:rounded-[2.5rem] shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <table class="w-full text-left text-[10px] md:text-xs table-fixed">
                <thead class="bg-slate-50 dark:bg-slate-700/50 text-[8px] md:text-[10px] uppercase text-slate-500 font-bold border-b border-slate-100 dark:border-slate-700">
                    <tr>
                        <th class="px-1 md:px-6 py-2 md:py-4 w-[22%] md:w-auto">Bulan</th>
                        <th class="px-1 md:px-6 py-2 md:py-4 text-right hidden sm:table-cell">Target</th>
                        <th class="px-1 md:px-6 py-2 md:py-4 text-right w-[20%] md:w-auto">Hutang</th>
                        <th class="px-1 md:px-6 py-2 md:py-4 text-right w-[20%] md:w-auto">Bayar</th>
                        <th class="px-1 md:px-6 py-2 md:py-4 text-center w-[12%] md:w-auto">S</th>
                        <th class="px-1 md:px-6 py-2 md:py-4 text-center w-[20%] md:w-auto">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php foreach($monthlyData as $d): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer group" onclick="if(!event.target.closest('button')) openDailyModal('<?=$kk['code_id']?>', <?=$d['bulan']?>, <?=$year?>)">
                        <td class="px-1 md:px-6 py-1.5 md:py-4 font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 truncate"><?=$d['nama_bulan']?></td>
                        <td class="px-1 md:px-6 py-1.5 md:py-4 text-right text-slate-500 hidden sm:table-cell"><?=number_format($d['target'], 0, ',', '.')?></td>
                        <td class="px-1 md:px-6 py-1.5 md:py-4 text-right font-bold <?=$d['debt'] > 0 ? 'text-rose-500' : 'text-slate-300'?> whitespace-nowrap">
                            <?=number_format($d['target'], 0, ',', '.')?>
                        </td>
                        <td class="px-1 md:px-6 py-1.5 md:py-4 text-right text-emerald-600">
                            <?=number_format($d['total_paid'], 0, ',', '.')?>
                        </td>
                        <td class="px-1 md:px-6 py-1.5 md:py-4 text-center">
                            <div class="flex justify-center">
                                <div class="w-2 h-2 rounded-full <?=$d['debt'] > 0 ? 'bg-rose-500' : 'bg-emerald-500'?> shadow-sm"></div>
                            </div>
                        </td>
                        <td class="px-1 md:px-6 py-1.5 md:py-4 text-right">
                            <div class="flex justify-end gap-1">
                                <?php if($d['debt'] > 0 && $d['is_past']): ?>
                                    <button onclick="pay(<?=$d['bulan']?>, '<?=$d['nama_bulan']?>', <?=$d['debt']?>)" class="w-7 h-7 md:w-8 md:h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center hover:bg-indigo-700 transition-all shadow-sm" title="Bayar Hutang">
                                        <i class="fas fa-hand-holding-dollar text-[10px]"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if($d['paid_manual'] > 0): ?>
                                    <button onclick="batalBayar(<?=$d['bulan']?>, '<?=$d['nama_bulan']?>')" class="w-7 h-7 md:w-8 md:h-8 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm" title="Batal Pembayaran">
                                        <i class="fas fa-undo text-[10px]"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Daily Modal (Kept as requested) -->
    <div id="dailyModal" class="fixed inset-0 z-[60] hidden overflow-y-auto modal-blur bg-dark/40 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in slide-in-from-bottom-10 duration-300">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600">
                        <i class="fas fa-calendar-day text-xs"></i>
                    </div>
                    <div>
                        <h2 id="dailyModalMonth" class="font-bold text-lg dark:text-white">Detail Harian</h2>
                        <p id="dailyModalKk" class="text-[10px] text-slate-500 font-bold uppercase tracking-widest"></p>
                    </div>
                </div>
                <button onclick="closeModal('dailyModal')" class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="max-h-[60vh] overflow-y-auto custom-scrollbar p-6">
                <!-- Daily Grid -->
                <div id="dailyGrid" class="grid grid-cols-7 gap-2 mb-6">
                    <!-- Dates via AJAX -->
                </div>
                
                <!-- Manual Payments Section -->
                <div id="paymentSection" class="mt-8">
                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Riwayat Pelunasan Manual</h3>
                    <div id="paymentList" class="space-y-2">
                        <!-- Payments via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function changeYear(year) {
            window.location.href = `?year=${year}`;
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        async function openDailyModal(codeId, month, year) {
            const modal = document.getElementById('dailyModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            const grid = document.getElementById('dailyGrid');
            const plist = document.getElementById('paymentList');
            grid.innerHTML = '<div class="col-span-7 text-center py-10"><i class="fas fa-circle-notch fa-spin text-indigo-500"></i></div>';
            plist.innerHTML = '';

            try {
                const res = await fetch(`/keuangan/get_daily_detail/${codeId}/${month}/${year}`);
                const data = await res.json();

                if (data.status === 'success') {
                    document.getElementById('dailyModalMonth').innerText = `${data.month_name} ${data.year}`;
                    document.getElementById('dailyModalKk').innerText = data.kk_name;

                    // Days
                    grid.innerHTML = data.daily.map(d => `
                        <div class="aspect-square rounded-xl flex flex-col items-center justify-center gap-1 border ${d.scanned ? 'bg-emerald-50 border-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:border-emerald-800/30' : 'bg-slate-50 border-slate-100 text-slate-400 dark:bg-slate-800/50 dark:border-slate-700/50'}">
                            <span class="text-[10px] font-bold">${d.tgl}</span>
                            <i class="fas ${d.scanned ? 'fa-check-circle' : 'fa-times'} text-[8px]"></i>
                        </div>
                    `).join('');

                    // Payments
                    if(data.payments.length === 0) {
                        plist.innerHTML = '<p class="text-xs text-slate-400 italic">Belum ada pemnbayaran manual</p>';
                    } else {
                        plist.innerHTML = data.payments.map(p => {
                            const isManual = p.keterangan === 'Pelunasan Hutang Jimpitan';
                            return `
                                <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/50 p-3 rounded-2xl border border-slate-100 dark:border-slate-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center text-emerald-600">
                                            <i class="fas fa-receipt text-[10px]"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${new Date(p.tgl_bayar).toLocaleDateString('id-ID')}</p>
                                            <p class="text-xs font-bold dark:text-white">Rp ${new Intl.NumberFormat('id-ID').format(p.jml_bayar)}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded-lg text-[8px] font-bold uppercase tracking-wider">LUNAS</span>
                                        ${isManual ? `
                                            <button onclick="hapusPembayaran(${p.id_iuran})" class="w-8 h-8 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-sm" title="Hapus Pembayaran Ini">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                }
            } catch (err) {
                grid.innerHTML = '<div class="col-span-7 text-rose-500">Error loading data</div>';
            }
        }

        async function pay(bulan, namaBulan, nominal) {
            const { value: inputNominal, isConfirmed } = await Swal.fire({
                title: 'Pelunasan Hutang',
                html: `Bulan: <b>${namaBulan} <?=$year?></b>`,
                input: 'number',
                inputLabel: 'Jumlah Pembayaran (Rp)',
                inputValue: nominal,
                inputAttributes: { min: 1, max: nominal, step: 500 },
                showCancelButton: true,
                confirmButtonText: 'Bayar Sekarang',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                inputValidator: (value) => {
                    if (!value || value <= 0) return 'Jumlah tidak valid!'
                    if (parseInt(value) > parseInt(nominal)) return 'Melebihi total hutang!'
                }
            });

            if (isConfirmed && inputNominal) {
                Swal.showLoading();
                try {
                    const formData = new FormData();
                    formData.append('code_id', '<?=$kk['code_id']?>');
                    formData.append('bulan', bulan);
                    formData.append('tahun', '<?=$year?>');
                    formData.append('nominal', inputNominal);
                    
                    const res = await fetch('/keuangan/hutang_jimpitan/bayar', { method: 'POST', body: formData });
                    const json = await res.json();
                    
                    if (json.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: json.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', json.message, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'Sistem error', 'error');
                }
            }
        }

        async function batalBayar(bulan, namaBulan) {
            const { isConfirmed } = await Swal.fire({
                title: 'Batal Pembayaran?',
                html: `Anda akan menghapus data pelunasan manual bulan <b>${namaBulan} <?=$year?></b>.<br><small class="text-rose-500">Jurnal kas juga akan ikut terhapus.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batal',
                cancelButtonText: 'Jangan',
                confirmButtonColor: '#e11d48',
            });

            if (isConfirmed) {
                Swal.showLoading();
                try {
                    const formData = new FormData();
                    formData.append('code_id', '<?=$kk['code_id']?>');
                    formData.append('bulan', bulan);
                    formData.append('tahun', '<?=$year?>');
                    
                    const res = await fetch('/keuangan/hutang_jimpitan/batal', { method: 'POST', body: formData });
                    const json = await res.json();
                    
                    if (json.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Dibatalkan', text: json.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', json.message, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'Sistem error', 'error');
                }
            }
        }

        async function hapusPembayaran(id) {
            const { isConfirmed } = await Swal.fire({
                title: 'Hapus Pembayaran?',
                text: 'Data pembayaran ini dan jurnal kas terkait akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#e11d48',
            });

            if (isConfirmed) {
                Swal.showLoading();
                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    const res = await fetch('/keuangan/hutang_jimpitan/hapus_item', { method: 'POST', body: formData });
                    const json = await res.json();
                    
                    if (json.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Dihapus', text: json.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', json.message, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'Sistem error', 'error');
                }
            }
        }
    </script>
    <?= $this->include('partials/loader') ?>
</body>
</html>
