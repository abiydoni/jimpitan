<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - <?= $profil['nama'] ?? 'Jimpitan' ?></title>
    
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
    </style>
</head>
<body class="bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-200 min-h-screen pb-20">

    <!-- Navbar -->
    <nav class="sticky top-0 z-40 glass border-b border-slate-200 dark:border-slate-800 px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <a href="/" class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Arus Kas Khusus</h1>
        </div>
        <button onclick="openModal()" class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 hover:scale-105 transition-transform">
            <i class="fas fa-plus"></i>
        </button>
    </nav>

    <!-- Content -->
    <main class="max-w-7xl mx-auto p-4">
        
        <?php if(isset($userTarif) && $userTarif == 100): ?>
            <div class="mb-4 flex justify-end">
                <form method="GET" action="" class="w-full sm:w-auto">
                    <div class="relative">
                        <select name="filter_tarif" onchange="this.form.submit()" class="w-full sm:w-64 pl-4 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-indigo-100 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 focus:ring-2 focus:ring-indigo-500 appearance-none shadow-sm cursor-pointer">
                            <option value="">Semua Data</option>
                            <?php foreach($tarif as $t): ?>
                                <option value="<?= $t['kode_tarif'] ?>" <?= (isset($selectedFilter) && $selectedFilter == $t['kode_tarif']) ? 'selected' : '' ?>>
                                    <?= $t['nama_tarif'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-indigo-500">
                            <i class="fas fa-filter text-xs"></i>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Summary Cards -->
        <?php 
            $totalDebet = !empty($transaksi) ? array_sum(array_column($transaksi, 'debet')) : 0;
            $totalKredit = !empty($transaksi) ? array_sum(array_column($transaksi, 'kredit')) : 0;
            $saldo = $totalDebet - $totalKredit;
        ?>
        <div class="grid grid-cols-3 gap-2 mb-3">
            <!-- Pemasukan -->
            <div class="bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-0.5">Masuk</span>
                <span class="text-sm font-bold text-emerald-500 leading-tight"><?= number_format($totalDebet, 0, ',', '.') ?></span>
            </div>
            <!-- Pengeluaran -->
            <div class="bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-0.5">Keluar</span>
                <span class="text-sm font-bold text-rose-500 leading-tight"><?= number_format($totalKredit, 0, ',', '.') ?></span>
            </div>
            <!-- Saldo -->
            <div class="bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col justify-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-0.5">Saldo</span>
                <span class="text-sm font-bold <?= $saldo >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-500' ?> leading-tight"><?= number_format($saldo, 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- List -->
        <div class="space-y-4">
            <?php if(isset($error)): ?>
                <div class="bg-rose-100 text-rose-600 p-4 rounded-xl border border-rose-200">
                    <h3 class="font-bold">Database Error</h3>
                    <p class="text-xs font-mono mt-1"><?= $error ?></p>
                </div>
            <?php endif; ?>

            <?php if(empty($transaksi)): ?>
                <div class="text-center py-20 opacity-50">
                    <i class="fas fa-clipboard-list text-4xl mb-3 text-slate-300"></i>
                    <p>Belum ada data transaksi</p>
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
                            <?php foreach($transaksi as $t): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-3 py-1.5 align-top">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-[10px] text-slate-400 bg-slate-100 dark:bg-slate-700/50 px-1.5 rounded whitespace-nowrap"><?= date('d/m/y', strtotime($t['date_trx'])) ?></span>
                                            <div class="font-bold text-slate-700 dark:text-slate-200 text-xs flex items-center gap-1">
                                                <?= $t['nama_akun'] ?>
                                                <?php if(strpos($t['reff'] ?? '', '_AUTO') !== false): ?>
                                                    <span class="text-[6px] bg-indigo-50 text-indigo-500 border border-indigo-100 px-1 rounded uppercase tracking-wider font-bold" title="Otomatis dari Sistem">
                                                        auto
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 italic leading-tight pl-0.5">
                                            <?= $t['desc_trx'] ?: '-' ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-1.5 text-right font-medium text-emerald-600 align-top whitespace-nowrap w-24 align-middle">
                                    <?= $t['debet'] > 0 ? number_format($t['debet'],0,',','.') : '-' ?>
                                </td>
                                <td class="px-3 py-1.5 text-right font-medium text-rose-600 align-top whitespace-nowrap w-24 align-middle">
                                    <?= $t['kredit'] > 0 ? number_format($t['kredit'],0,',','.') : '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($pager)): ?>
                    <div class="mt-4 flex justify-center">
                        <?= $pager->links('default', 'mini_pager') ?> 
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </main>

    <!-- Add Modal -->
    <div id="modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[2.5rem] p-6 sm:p-8 shadow-2xl animate__animated animate__zoomIn animate__faster">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold dark:text-white">Transaksi Baru</h2>
                <button onclick="closeModal()" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="/keuangan/save_sub" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Row 1 Left: Tanggal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white" required>
                    </div>

                    <!-- Row 1 Right: Jenis Iuran / Tarif -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jenis Iuran</label>
                        <?php 
                            // Determine User's Tarif Code
                            $userTarifCode = '';
                            if (isset($userTarif) && $userTarif != 100) {
                                foreach($tarif as $t) {
                                    if ($t['id'] == $userTarif) {
                                        $userTarifCode = $t['kode_tarif'];
                                        $userTarifName = $t['nama_tarif'];
                                        break;
                                    }
                                }
                            }
                        ?>
                        
                        <?php if(isset($userTarif) && $userTarif == 100): ?>
                            <select name="kode_tarif" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none" required>
                                <option value="">Pilih Tarif</option>
                                <?php foreach($tarif as $t): ?>
                                    <option value="<?= $t['kode_tarif'] ?>"><?= $t['nama_tarif'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                             <input type="hidden" name="kode_tarif" value="<?= $userTarifCode ?>">
                             <input type="text" value="<?= $userTarifName ?? '-' ?>" class="w-full px-4 py-3.5 bg-slate-100 dark:bg-slate-700/50 border-none rounded-2xl text-sm text-slate-500 cursor-not-allowed" readonly>
                        <?php endif; ?>
                    </div>

                    <!-- Row 2 Left: Jenis Transaksi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jenis Transaksi</label>
                        <select name="jenis" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none" required>
                            <option value="masuk">Pemasukan</option>
                            <option value="keluar">Pengeluaran</option>
                        </select>
                    </div>

                    <!-- Row 2 Right: Akun COA -->
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Akun COA</label>
                        <select name="coa_code" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white appearance-none" required>
                            <option value="">Pilih Akun</option>
                            <?php foreach($coa as $c): ?>
                                <option value="<?= $c['code'] ?>"><?= $c['code'] ?> - <?= $c['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Iuran removed as per request (automated only) -->

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nominal (Rp)</label>
                    <input type="text" name="nominal" id="nominal" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-lg font-bold text-indigo-600 focus:ring-2 focus:ring-indigo-500 transition-all" placeholder="0" onkeyup="formatRupiah(this)" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Keterangan</label>
                    <textarea name="keterangan" class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 transition-all dark:text-white h-24 resize-none" placeholder="Deskripsi transaksi..."></textarea>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 active:scale-95 transition-all mt-6">
                    Simpan Transaksi
                </button>
            </form>

        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
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

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= session()->getFlashdata('success') ?>', timer: 1500, showConfirmButton: false });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= session()->getFlashdata('error') ?>' });
        <?php endif; ?>
    </script>
</body>
</html>
