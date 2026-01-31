<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<body class="min-h-screen bg-slate-50 dark:bg-dark text-slate-800 dark:text-slate-100 transition-colors duration-300 pb-20">

    <!-- Header -->
    <nav class="glass sticky top-0 z-50 px-4 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <a href="/barang" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Peminjaman Barang</h1>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        
        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold dark:text-white">Daftar Peminjaman</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola peminjaman inventori warga</p>
            </div>
            
            <?php if(in_array(session()->get('role'), ['s_admin', 'admin', 'pengurus'])): ?>
            <button onclick="openModal()" class="w-full sm:w-auto px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2 transition-all active:scale-95">
                <i class="fas fa-plus"></i>
                <span>Pinjam Barang</span>
            </button>
            <?php endif; ?>
        </div>

        <!-- List View -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-6 py-4">Barang</th>
                            <th class="px-6 py-4">Peminjam</th>
                            <th class="px-6 py-4">Tgl Pinjam</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <?php if(empty($peminjaman)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                    Belum ada data peminjaman
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($peminjaman as $p): ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 font-medium dark:text-white">
                                    <?= $p['nama_barang'] ?>
                                    <span class="block text-xs text-slate-400 font-mono"><?= $p['kode_brg'] ?></span>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                    <?= $p['nama_peminjam'] ?>
                                    <span class="block text-xs text-slate-400 font-mono">Total Pinjam: <?= $p['jumlah'] ?></span>
                                    <?php 
                                        $kembali = $p['jumlah_kembali'] ?? 0;
                                        $sisa = $p['jumlah'] - $kembali;
                                    ?>
                                    <?php if($kembali > 0): ?>
                                        <span class="block text-xs text-emerald-500 font-bold">Sudah Kembali: <?= $kembali ?></span>
                                    <?php endif; ?>
                                    <?php if($p['status'] == 'dipinjam'): ?>
                                        <span class="block text-xs text-amber-500 font-bold">Sisa: <?= $sisa ?></span>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($p['nominal_ganti_rugi']) && $p['nominal_ganti_rugi'] > 0): ?>
                                        <span class="block text-xs text-rose-500 font-bold">Ganti Rugi: Rp <?= number_format($p['nominal_ganti_rugi'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                    <?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?>
                                    <?php if($p['tanggal_kembali']): ?>
                                        <span class="block text-xs text-emerald-500">Selesai: <?= date('d M Y', strtotime($p['tanggal_kembali'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($p['status'] == 'dipinjam'): ?>
                                        <span class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-600 text-xs font-bold dark:bg-amber-900/30 dark:text-amber-400">
                                            Dipinjam
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-600 text-xs font-bold dark:bg-emerald-900/30 dark:text-emerald-400">
                                            Lunas
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if($p['status'] == 'dipinjam' && in_array(session()->get('role'), ['s_admin', 'admin', 'pengurus'])): ?>
                                        <button onclick="openReturnModal(<?= $p['id'] ?>, '<?= addslashes($p['nama_barang']) ?>', <?= $sisa ?>)" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-xs font-bold transition-colors dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50">
                                            Kembalikan
                                        </button>
                                    <?php elseif($p['status'] == 'kembali'): ?>
                                        <span class="text-slate-300 dark:text-slate-600"><i class="fas fa-check"></i></span>
                                    <?php endif; ?>
                                    
                                    <?php if(in_array(session()->get('role'), ['s_admin', 'admin'])): ?>
                                        <button onclick="deleteItem(<?= $p['id'] ?>)" class="ml-2 text-rose-400 hover:text-rose-600 transition-colors">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal Form Pinjam -->
    <div id="pinjamModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl transform transition-all scale-95 opacity-0 animate__animated animate__zoomIn animate__faster" id="modalContent">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Pinjam Barang</h3>
                        <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="pinjamForm" onsubmit="handleFormSubmit(event)" class="space-y-4">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Pilih Barang</label>
                            <select name="barang_id" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm">
                                <option value="">-- Pilih Barang --</option>
                                <?php foreach($barangList as $b): ?>
                                    <option value="<?= $b['kode'] ?>" <?= $b['jumlah'] <= 0 ? 'disabled' : '' ?>>
                                        <?= $b['nama'] ?> (Stok: <?= $b['jumlah'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nama Peminjam</label>
                            <input type="text" name="nama_peminjam" required 
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm" placeholder="Nama Peminjam">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jumlah</label>
                            <input type="number" name="jumlah" required min="1" value="1"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Keterangan (Opsional)</label>
                            <textarea name="keterangan" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm"></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 transition-all active:scale-[0.98]">
                                Simpan Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Pengembalian -->
    <div id="returnModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeReturnModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl transform transition-all scale-95 opacity-0 animate__animated animate__zoomIn animate__faster" id="returnModalContent">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Pengembalian Barang</h3>
                        <button onclick="closeReturnModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="returnForm" onsubmit="handleReturnSubmit(event)" class="space-y-4">
                        <input type="hidden" name="id" id="returnId">
                        
                        <div class="bg-indigo-50 dark:bg-slate-700/50 p-4 rounded-xl mb-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase font-bold">Barang</p>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400" id="returnItemName">-</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jumlah Dipinjam: <span id="returnItemQty" class="font-bold text-slate-700 dark:text-white">0</span></p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jumlah Kembali Fisik</label>
                            <input type="number" name="jumlah_kembali" id="returnJumlah" required min="0"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-bold text-center text-lg" oninput="checkReturnReason()">
                            <p class="text-[10px] text-slate-400 mt-1 ml-1">* Masukkan jumlah barang fisik yang kembali</p>
                        </div>
                        
                        <!-- Container: Alasan / Ganti Rugi -->
                        <div id="reasonContainer" class="hidden animate__animated animate__fadeIn space-y-4">
                            
                            <!-- Checkbox Ganti Uang -->
                            <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-100 dark:border-amber-900/30">
                                <input type="checkbox" name="ganti_rugi" id="checkGantiRugi" 
                                    class="w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300"
                                    onchange="toggleGantiRugi()">
                                <label for="checkGantiRugi" class="text-sm font-bold text-amber-700 dark:text-amber-400 cursor-pointer">
                                    Sisa barang diganti uang/hilang?
                                </label>
                            </div>

                            <!-- Input Nominal (Hidden by default) -->
                            <div id="nominalContainer" class="hidden">
                                <label class="block text-xs font-bold text-emerald-500 uppercase tracking-widest mb-1.5 ml-1">Nominal Ganti Rugi (Rp)</label>
                                <input type="text" name="nominal_uang" id="nominalUang" 
                                    class="w-full px-4 py-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl focus:ring-2 focus:ring-emerald-500 dark:text-white text-sm font-mono" placeholder="0"
                                    oninput="formatCurrency(this)">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-rose-400 uppercase tracking-widest mb-1.5 ml-1">Keterangan / Alasan <span class="text-rose-500">*</span></label>
                                <textarea name="keterangan" id="returnKeterangan" rows="2" class="w-full px-4 py-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-900/50 rounded-2xl focus:ring-2 focus:ring-rose-500 dark:text-white text-sm" placeholder="Jelaskan kenapa jumlah kembali kurang..."></textarea>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-2xl shadow-lg shadow-emerald-500/30 transition-all active:scale-[0.98]">
                                Simpan Pengembalian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <?= $this->include('partials/loader') ?>

    <script>
        const modal = document.getElementById('pinjamModal');
        const modalContent = document.getElementById('modalContent');
        const form = document.getElementById('pinjamForm');
        
        // Return Modal
        const returnModal = document.getElementById('returnModal');
        const returnModalContent = document.getElementById('returnModalContent');
        const returnForm = document.getElementById('returnForm');
        let currentBorrowedQty = 0;

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('opacity-100', 'scale-100');
            modalContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                form.reset();
            }, 200);
        }

        function openReturnModal(id, name, qty) {
            document.getElementById('returnId').value = id;
            document.getElementById('returnItemName').innerText = name;
            document.getElementById('returnItemQty').innerText = qty;
            document.getElementById('returnJumlah').value = qty;
            document.getElementById('returnJumlah').max = qty;
            currentBorrowedQty = qty;
            
            // Reset state
            document.getElementById('checkGantiRugi').checked = false;
            document.getElementById('nominalUang').value = '';
            toggleGantiRugi();
            checkReturnReason();

            returnModal.classList.remove('hidden');
            setTimeout(() => {
                returnModalContent.classList.remove('opacity-0', 'scale-95');
                returnModalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeReturnModal() {
            returnModalContent.classList.remove('opacity-100', 'scale-100');
            returnModalContent.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                returnModal.classList.add('hidden');
                returnForm.reset();
            }, 200);
        }

        function checkReturnReason() {
            const val = parseInt(document.getElementById('returnJumlah').value) || 0;
            const container = document.getElementById('reasonContainer');
            const inputKeterangan = document.getElementById('returnKeterangan');

            if (val < currentBorrowedQty) {
                container.classList.remove('hidden');
                inputKeterangan.required = true;
            } else {
                container.classList.add('hidden');
                inputKeterangan.required = false;
                // Uncheck if hidden
                document.getElementById('checkGantiRugi').checked = false;
                toggleGantiRugi();
            }
        }

        function toggleGantiRugi() {
            const isChecked = document.getElementById('checkGantiRugi').checked;
            const nominalContainer = document.getElementById('nominalContainer');
            const nominalInput = document.getElementById('nominalUang');

            if (isChecked) {
                nominalContainer.classList.remove('hidden');
                nominalInput.required = true;
            } else {
                nominalContainer.classList.add('hidden');
                nominalInput.required = false;
                nominalInput.value = '';
            }
        }

        function formatCurrency(input) {
            let value = input.value.replace(/[^0-9]/g, '');
            if (value === '') {
                input.value = '';
                return;
            }
            input.value = new Intl.NumberFormat('id-ID').format(value);
        }

        async function handleFormSubmit(e) {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                window.showLoader();
                const res = await fetch('/peminjaman/store', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();

                if (json.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: json.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    let msg = json.message;
                    if(json.errors) msg += '<br>' + JSON.stringify(json.errors);
                    Swal.fire('Gagal', msg, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            } finally {
                window.hideLoader();
            }
        }

        async function handleReturnSubmit(e) {
            e.preventDefault();
            const formData = new FormData(returnForm);

            try {
                window.showLoader();
                const res = await fetch('/peminjaman/returnItem', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await res.json();

                if (json.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: json.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', json.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            } finally {
                window.hideLoader();
            }
        }
        
        async function deleteItem(id) {
            const result = await Swal.fire({
                title: 'Hapus Riwayat?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                try {
                    window.showLoader();
                    const formData = new FormData();
                    formData.append('id', id);
                    
                    const res = await fetch('/peminjaman/delete', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await res.json();

                    if (json.status === 'success') {
                        Swal.fire('Terhapus', json.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', json.message, 'error');
                    }
                } catch (err) {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                } finally {
                    window.hideLoader();
                }
            }
        }
    </script>
</body>
</html>
