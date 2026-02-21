<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?> - <?=$profil['nama'] ?? 'Jimpitan'?></title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .dark .glass { background: rgba(30, 41, 59, 0.9); }
        .input-field {
            @apply w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all;
        }
        .fancy-scrollbar::-webkit-scrollbar { width: 5px; }
        .fancy-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .fancy-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .fancy-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-200 min-h-screen pb-20">

    <!-- Navbar -->
    <nav class="sticky top-0 z-40 glass border-b border-slate-200 dark:border-slate-800 px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="/" class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Jurnal Jimpitan</h1>
        </div>
        
        <?php if (empty($isViewOnly)): ?>
        <div class="flex gap-2">
            <button onclick="openSetorModal()" class="px-4 py-2 bg-emerald-600 rounded-xl flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-emerald-500/30 hover:scale-105 transition-transform gap-2">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Setor</span>
            </button>
            <button onclick="openModal()" class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 hover:scale-105 transition-transform">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <?php endif; ?>
    </nav>

    <!-- Content -->
    <main class="max-w-7xl mx-auto p-4">
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-0.5">Masuk</span>
                <span class="text-sm font-bold text-emerald-500 leading-tight"><?=number_format($totalDebetAll, 0, ',', '.')?></span>
            </div>
            <div class="bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-0.5">Keluar</span>
                <span class="text-sm font-bold text-rose-500 leading-tight"><?=number_format($totalKreditAll, 0, ',', '.')?></span>
            </div>
            <div class="bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-0.5">Saldo</span>
                <span class="text-sm font-bold <?=$saldoAll >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-500'?> leading-tight"><?=number_format($saldoAll, 0, ',', '.')?></span>
            </div>
        </div>

        <!-- List -->
        <div class="space-y-4">
            <?php if (isset($error)): ?>
                <div class="bg-rose-100 text-rose-600 p-4 rounded-xl border border-rose-200">
                    <h3 class="font-bold">Database Error</h3>
                    <p class="text-xs font-mono mt-1"><?=$error?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($transaksi)): ?>
                <div class="text-center py-20 opacity-50">
                    <i class="fas fa-clipboard-list text-4xl mb-3 text-slate-300"></i>
                    <p>Belum ada data transaksi jimpitan</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 text-[10px] uppercase text-slate-500 font-bold border-b border-slate-100 dark:border-slate-700">
                            <tr>
                                <th class="px-3 py-1.5">Transaksi</th>
                                <th class="px-3 py-1.5 text-right text-emerald-600">Debet</th>
                                <th class="px-3 py-1.5 text-right text-rose-600">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <?php foreach ($transaksi as $t): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-3 py-1.5 align-top">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-[10px] text-slate-400 bg-slate-100 dark:bg-slate-700/50 px-1.5 rounded whitespace-nowrap"><?=date('d/m/y', strtotime($t['date_trx']))?></span>
                                            <div class="font-bold text-slate-700 dark:text-slate-200 text-xs">
                                                <?=$t['nama_akun']?>
                                            </div>
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 italic leading-tight pl-0.5">
                                            <?php
                                                $rawDesc = $t['detail_trx'] ?: ($t['desc_trx'] ?? '-');
                                                if (strpos($rawDesc, '||') !== false) {
                                                    $parts = explode('||', $rawDesc);
                                                    echo $parts[0];
                                                } else {
                                                    echo $rawDesc;
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-medium text-emerald-600 align-middle whitespace-nowrap w-24">
                                    <?=$t['debet'] > 0 ? number_format($t['debet'], 0, ',', '.') : '-'?>
                                </td>
                                <td class="px-3 py-1.5 text-right font-medium text-rose-600 align-middle whitespace-nowrap w-24">
                                    <?=$t['kredit'] > 0 ? number_format($t['kredit'], 0, ',', '.') : '-'?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (isset($pager)): ?>
                    <div class="mt-4 flex justify-center">
                        <?=$pager->links('jimpitan', 'mini_pager')?> 
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Setor Jimpitan Modal -->
    <div id="setorModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeSetorModal()"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2rem] p-6 shadow-2xl animate__animated animate__zoomIn animate__faster">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold dark:text-white">Setor Jimpitan</h2>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest mt-1">Daftar Jimpitan Belum Disetor</p>
                </div>
                <button onclick="closeSetorModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="setorList" class="max-h-80 overflow-y-auto mb-6 fancy-scrollbar space-y-2 pr-1">
                <div class="text-center py-10 text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                    <p class="text-xs mt-2">Memuat data...</p>
                </div>
            </div>

            <div class="text-right">
                <button onclick="closeSetorModal()" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl font-bold text-sm transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Manual Jurnal Modal (Based on jurnal_sub) -->
    <div id="modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 sm:p-8 shadow-2xl animate__animated animate__zoomIn animate__faster">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold dark:text-white">Jurnal Baru</h2>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="/keuangan/save_sub" method="POST" class="space-y-4">
                <?=csrf_field()?>
                <input type="hidden" name="kode_tarif" value="JMP">
                <input type="hidden" name="redirect_url" value="/keuangan/jurnal_jimpitan">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tanggal</label>
                        <input type="date" name="tanggal" value="<?=date('Y-m-d')?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Akun COA</label>
                        <select name="coa_code" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none" required>
                            <option value="">Pilih Akun</option>
                            <?php foreach ($coa as $c): ?>
                                <option value="<?=$c['code']?>" <?=$c['name'] == 'Jimpitan' ? 'selected' : ''?>><?=$c['code']?> - <?=$c['name']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jenis Transaksi</label>
                        <select name="jenis" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none" required>
                            <option value="masuk">Pemasukan</option>
                            <option value="keluar">Pengeluaran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nominal (Rp)</label>
                        <input type="text" name="nominal" id="nominal" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-lg font-bold text-indigo-600 focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="0" onkeyup="formatRupiah(this)" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Keterangan</label>
                    <textarea name="keterangan" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white h-24 resize-none" placeholder="Deskripsi transaksi..."></textarea>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all mt-6">
                    Simpan Jurnal Baru
                </button>
            </form>
        </div>
    </div>

    <script>
        function openSetorModal() {
            document.getElementById('setorModal').classList.remove('hidden');
            fetchUnsettled();
        }
        function closeSetorModal() {
            document.getElementById('setorModal').classList.add('hidden');
        }
        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        async function fetchUnsettled() {
            const listDiv = document.getElementById('setorList');
            listDiv.innerHTML = '<div class="text-center py-10 text-slate-400"><i class="fas fa-spinner fa-spin text-2xl"></i><p class="text-xs mt-2">Memuat data...</p></div>';
            
            try {
                const res = await fetch('/keuangan/get_unsettled_jimpitan');
                const json = await res.json();
                
                if (json.status === 'success' && json.data.length > 0) {
                    listDiv.innerHTML = '';
                    json.data.forEach(item => {
                        const dateFormatted = new Date(item.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                        const nominalFormatted = new Intl.NumberFormat('id-ID').format(item.total);
                        
                        const div = document.createElement('div');
                        div.className = "flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 hover:border-emerald-500 transition-all group";
                        div.innerHTML = `
                            <div>
                                <p class="text-sm font-bold dark:text-white">${dateFormatted}</p>
                                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">${item.jml_scan} Warga Scan</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-emerald-600">Rp ${nominalFormatted}</span>
                                <button onclick="setor('${item.tanggal}', ${item.total})" class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                            </div>
                        `;
                        listDiv.appendChild(div);
                    });
                } else {
                    listDiv.innerHTML = '<div class="text-center py-10 opacity-50"><i class="fas fa-check-circle text-4xl mb-3 text-emerald-500"></i><p>Semua jimpitan sudah disetor</p></div>';
                }
            } catch (err) {
                listDiv.innerHTML = '<p class="text-center text-rose-500 text-xs py-10">Gagal memuat data</p>';
            }
        }

        async function setor(tanggal, total) {
            const { isConfirmed } = await Swal.fire({
                title: 'Konfirmasi Setoran',
                text: `Setorkan jimpitan tanggal ${tanggal} sebesar Rp ${new Intl.NumberFormat('id-ID').format(total)}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Setorkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#059669'
            });

            if (isConfirmed) {
                Swal.showLoading();
                try {
                    const formData = new FormData();
                    formData.append('tanggal', tanggal);
                    
                    const res = await fetch('/keuangan/setor_jimpitan', {
                        method: 'POST',
                        body: formData
                    });
                    const json = await res.json();
                    
                    if (json.status === 'success') {
                        Swal.fire('Berhasil', json.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', json.message, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'Sistem bermasalah', 'error');
                }
            }
        }

        function formatRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            input.value = rupiah;
        }

        <?php if (session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?=esc(session()->getFlashdata('success'), 'js')?>', timer: 1500, showConfirmButton: false });
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal', text: '<?=esc(session()->getFlashdata('error'), 'js')?>' });
        <?php endif; ?>
    </script>
</body>
</html>
