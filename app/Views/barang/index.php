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
            <a href="/" class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-500">Inventori Barang</h1>
        </div>
        <button id="themeToggle" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-moon dark:hidden"></i>
            <i class="fas fa-sun hidden dark:block text-amber-400"></i>
        </button>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        
        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex flex-col">
                <h2 class="text-xl font-bold dark:text-white">Daftar Aset & Barang</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Manajemen inventori warga</p>
            </div>
            
            <!-- Search Bar -->
            <form action="" method="get" class="w-full sm:w-72 relative">
                <input type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Cari barang..." 
                    class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-slate-800 border-none rounded-xl text-sm shadow-sm focus:ring-2 focus:ring-indigo-500 dark:text-white placeholder-slate-400">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-400"></i>
            </form>

            <?php if(!empty($canManage)): ?>
            <div class="flex gap-2 w-full sm:w-auto">
                <a href="/peminjaman" class="flex-1 sm:flex-none px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-bold rounded-xl shadow-sm flex items-center justify-center gap-2 transition-all active:scale-95">
                    <i class="fas fa-hand-holding"></i>
                    <span>Peminjaman</span>
                </a>
                <button onclick="openModal()" class="flex-1 sm:flex-none px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2 transition-all active:scale-95">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Barang</span>
                </button>
            </div>
            <?php endif; ?>
        </div>

        <!-- Grid View -->
        <!-- Grid View -->
        <!-- Grid View -->
        <?php if(!empty($barang)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-8">
            <?php foreach ($barang as $b): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-3 border border-slate-100 dark:border-slate-700 shadow-sm hover:shadow-lg transition-all group flex flex-col h-full relative overflow-hidden">
                    
                    <div class="flex items-center gap-3">
                        <!-- Image / Icon -->
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-slate-700/50 flex-shrink-0 flex items-center justify-center border border-indigo-100 dark:border-slate-600">
                             <i class="fas fa-box text-sm text-indigo-500 dark:text-indigo-400"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-slate-800 dark:text-white truncate leading-tight">
                                <?= $b['nama'] ?>
                            </h3>
                            <div class="flex items-center justify-between mt-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="bg-slate-100 dark:bg-slate-700 px-1.5 py-px rounded text-[10px] font-mono text-slate-500 dark:text-slate-400">
                                        <?= $b['kode_brg'] ?>
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                        <?= $b['jumlah'] ?> Unit
                                    </span>
                                </div>
                                
                                <?php if(!empty($canManage)): ?>
                                <div class="flex gap-1">
                                     <button onclick="editBarang(<?= htmlspecialchars(json_encode($b)) ?>)" class="w-5 h-5 rounded bg-indigo-50 dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-600 dark:hover:text-white flex items-center justify-center transition-all">
                                        <i class="fas fa-pencil-alt text-[9px]"></i>
                                    </button>
                                    <button onclick="deleteBarang(<?= $b['kode'] ?>, '<?= $b['nama'] ?>')" class="w-5 h-5 rounded bg-rose-50 dark:bg-slate-700 text-rose-500 hover:bg-rose-500 hover:text-white dark:hover:bg-rose-600 flex items-center justify-center transition-all">
                                        <i class="fas fa-trash-alt text-[9px]"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            <?= $pager->links('barang', 'tailwind_full') ?>
        </div>

        <?php else: ?>
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-search text-3xl text-slate-400"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300">Tidak ada barang ditemukan</h3>
            <p class="text-slate-500 text-sm mt-1">Coba kata kunci lain atau tambahkan barang baru.</p>
        </div>
        <?php endif; ?>

    </main>

    <!-- Modal Form -->
    <div id="barangModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-3xl shadow-2xl transform transition-all scale-95 opacity-0 animate__animated animate__zoomIn animate__faster max-h-[90vh] overflow-y-auto custom-scrollbar" id="modalContent">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white" id="modalTitle">Tambah Barang</h3>
                        <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-500 hover:text-rose-500 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="barangForm" onsubmit="handleFormSubmit(event)" class="space-y-4" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="formId">
                        <input type="hidden" name="old_foto" id="formOldFoto">

                        <div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Kode Barang</label>
                                <input type="text" name="kode_brg" id="formKode" required 
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-mono placeholder-slate-300" placeholder="BRG-001">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nama Barang</label>
                            <input type="text" name="nama" id="formNama" required 
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold placeholder-slate-300" placeholder="Ex: Speaker Aktif">
                        </div>

                        <div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Jumlah</label>
                                <input type="number" name="jumlah" id="formJumlah" required min="0" value="0"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 dark:text-white text-sm font-semibold">
                            </div>
                        </div>



                        <div class="pt-2">
                            <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-2xl shadow-lg shadow-indigo-500/30 transition-all active:scale-[0.98]">
                                Simpan Barang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Loader -->
    <?= $this->include('partials/loader') ?>

    <script>
        // Modal Logic
        const modal = document.getElementById('barangModal');
        const modalTitle = document.getElementById('modalTitle');
        const form = document.getElementById('barangForm');
        const modalContent = document.getElementById('modalContent');

        function openModal() {
            modalTitle.innerText = 'Tambah Barang';
            form.reset();
            document.getElementById('formId').value = '';
            document.getElementById('formOldFoto').value = '';
            
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
            }, 200);
        }

        function editBarang(data) {
            modalTitle.innerText = 'Edit Barang';
            document.getElementById('formId').value = data.kode;
            document.getElementById('formKode').value = data.kode_brg;
            document.getElementById('formNama').value = data.nama;
            document.getElementById('formJumlah').value = data.jumlah;
            
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('opacity-0', 'scale-95');
                modalContent.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        // CRUD
        async function handleFormSubmit(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const id = formData.get('id');
            const url = id ? '/barang/update' : '/barang/store';

            try {
                window.showLoader();
                const res = await fetch(url, {
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
                console.error(err);
            } finally {
                window.hideLoader();
            }
        }

        async function deleteBarang(id, name) {
            const result = await Swal.fire({
                title: 'Hapus Barang?',
                text: `Anda akan menghapus "${name}".`,
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
                    
                    const res = await fetch('/barang/delete', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await res.json();

                    if (json.status === 'success') {
                        Swal.fire('Terhapus!', json.message, 'success').then(() => location.reload());
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
        
        // Theme
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.onclick = () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            };
        }
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
</body>
</html>
