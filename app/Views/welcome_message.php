<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $profil['nama'] ?? 'Jimpitan App' ?> | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS with Dark Mode Config -->
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
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
            transition: background-color 0.3s ease;
        }
        body.dark {
            background-color: #0f172a;
        }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .dark .glass {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .mobile-menu {
            transition: all 0.3s ease-in-out;
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
        }
        .mobile-menu.active {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .user-dropdown {
            transition: all 0.2s ease-out;
            transform: translateY(10px) scale(0.95);
            opacity: 0;
            pointer-events: none;
        }
        .user-dropdown.active {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }
        .modal {
            transition: all 0.3s ease-out;
            opacity: 0;
            pointer-events: none;
        }
        .modal.active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-content {
            transition: all 0.3s ease-out;
            transform: scale(0.9) translateY(20px);
        }
        .modal.active .modal-content {
            transform: scale(1) translateY(0);
        }
        /* Mobile Bottom Nav Styles */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4.5rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 0 1rem;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
            z-index: 1000;
        }
        .dark .bottom-nav {
            background: rgba(15, 23, 42, 0.9);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.3);
        }
        .scan-button {
            position: absolute;
            top: -2.8rem;
            left: 50%;
            transform: translateX(-50%);
            width: 5.5rem;
            height: 5.5rem;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.2rem;
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.5);
            border: 6px solid #fff;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .dark .scan-button {
            border-color: #0f172a;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }
        .scan-button:active {
            transform: translateX(-50%) scale(0.9);
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #64748b;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .nav-item.active {
            color: #6366f1;
        }
        .dark .nav-item {
            color: #94a3b8;
        }
        .dark .nav-item.active {
            color: #818cf8;
        }
        /* Mobile adjustment for content padding at bottom */
        @media (max-width: 768px) {
            body { padding-bottom: 8rem; }
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes wave {
            0% { transform: rotate(0deg); }
            10% { transform: rotate(14deg); }
            20% { transform: rotate(-8deg); }
            30% { transform: rotate(14deg); }
            40% { transform: rotate(-4deg); }
            50% { transform: rotate(10deg); }
            60% { transform: rotate(0deg); }
            100% { transform: rotate(0deg); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animate-wave {
            animation: wave 2s infinite;
            transform-origin: 70% 70%;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark transition-colors duration-300">

    <!-- Mobile Navigation Menu -->
    <div id="mobileMenu" class="mobile-menu fixed inset-0 z-[60] bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-6 flex flex-col space-y-6 md:hidden">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold gradient-text">Menu</h1>
            <button id="closeMenu" class="text-slate-500 dark:text-slate-400 text-2xl"><i class="fas fa-times"></i></button>
        </div>
        <a href="#" class="flex items-center space-x-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold transition-colors active:bg-indigo-50 active:text-indigo-600">
            <i class="fas fa-chart-pie w-6"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" class="flex items-center space-x-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold transition-colors active:bg-indigo-50 active:text-indigo-600">
            <i class="fas fa-users w-6"></i>
            <span>Data Warga</span>
        </a>
        <a href="#" class="flex items-center space-x-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-semibold transition-colors active:bg-indigo-50 active:text-indigo-600">
            <i class="fas fa-file-invoice-dollar w-6"></i>
            <span>Laporan</span>
        </a>
        <div class="pt-6 border-t border-slate-100 dark:border-slate-800 mt-auto">
            <a href="/logout" class="flex items-center space-x-4 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 font-bold transition-colors">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span>Keluar Aplikasi</span>
            </a>
        </div>
    </div>

    <!-- Sidebar / Nav -->
    <nav class="glass sticky top-0 z-50 px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-2">
            <div class="w-9 h-9 sm:w-10 sm:h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-hand-holding-dollar text-lg sm:text-xl"></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold gradient-text"><?= $profil['nama'] ?? 'Jimpitan App' ?></h1>
        </div>
        
        <div class="hidden md:flex space-x-6 text-slate-600 dark:text-slate-300 font-medium">
            <a href="/" class="hover:text-indigo-600 transition-colors">Dashboard</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">Data Warga</a>
            <a href="#" class="hover:text-indigo-600 transition-colors">Laporan</a>
        </div>

        <div class="flex items-center space-x-3 sm:space-x-4">
            <!-- Theme Toggle -->
            <button id="themeToggle" class="bg-slate-100 dark:bg-slate-800 p-2 sm:p-2.5 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
                <i class="fas fa-moon dark:hidden"></i>
                <i class="fas fa-sun hidden dark:block text-amber-400"></i>
            </button>
            <button id="testAlert" class="bg-slate-100 dark:bg-slate-800 p-2 sm:p-2.5 rounded-full text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors relative">
                <i class="fas fa-bell"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full border-2 border-white dark:border-slate-800"></span>
            </button>
            <div class="relative">
                <button id="userDropdownTrigger" class="flex items-center space-x-2 sm:space-x-3 sm:pl-4 sm:border-l sm:border-slate-200 dark:border-slate-700 hover:opacity-80 transition-opacity">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200"><?= session()->get('name') ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400"><?= ucfirst(session()->get('role')) ?></p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode(session()->get('name')) ?>&background=6366f1&color=fff" alt="Avatar" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border-2 border-white shadow-sm ring-1 ring-slate-100 dark:ring-slate-800">
                </button>

                <!-- Dropdown Menu -->
                <div id="userDropdown" class="user-dropdown absolute right-0 mt-3 w-56 glass rounded-2xl shadow-xl overflow-hidden z-50">
                    <div class="p-3 border-b border-slate-100 dark:border-slate-800 md:hidden">
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200"><?= session()->get('name') ?></p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider"><?= session()->get('role') ?></p>
                    </div>
                    <div class="p-1.5">
                        <button onclick="openProfileModal()" class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            <i class="fas fa-user-circle text-indigo-500"></i>
                            <span>Ubah Profil</span>
                        </button>
                        <button onclick="openPasswordModal()" class="w-full flex items-center space-x-3 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-xl transition-colors">
                            <i class="fas fa-key text-amber-500"></i>
                            <span>Ubah Password</span>
                        </button>
                    </div>
                    <div class="p-1.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        <a href="/logout" class="flex items-center space-x-3 px-4 py-2.5 text-sm font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu Toggle -->
            <button id="openMenu" class="md:hidden text-slate-600 text-xl pl-2">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-10">
        <!-- Hero Section -->
        <div class="mb-8 sm:mb-10 animate__animated animate__fadeIn">
            <?php if(session()->getFlashdata('success')): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?= session()->getFlashdata('success') ?>',
                        confirmButtonColor: '#6366f1',
                    });
                </script>
            <?php endif; ?>
            <?php if(session()->getFlashdata('error')): ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: '<?= session()->getFlashdata('error') ?>',
                        confirmButtonColor: '#6366f1',
                    });
                </script>
            <?php endif; ?>
            <!-- Modern Hero Section -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-purple-600 to-fuchsia-600 p-6 sm:p-8 shadow-xl shadow-indigo-500/20 mb-6 z-0">
                
                <!-- Animated Background Blobs -->
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-48 h-48 bg-white/10 rounded-full blur-2xl animate-blob"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-fuchsia-500/20 rounded-full blur-2xl animate-blob animation-delay-2000"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-40 h-40 bg-indigo-500/20 rounded-full blur-2xl animate-blob animation-delay-4000"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
                    <div class="space-y-2">
                        <div class="animate__animated animate__fadeInLeft">
                            <h2 class="text-xl sm:text-2xl font-bold text-white leading-tight">
                                Halo, <?= session()->get('name') ?> <span class="animate-wave inline-block">👋</span>
                            </h2>
                            <p class="text-indigo-100 text-xs sm:text-sm max-w-md">
                                Selamat datang kembali di aplikasi jimpitan.
                            </p>
                        </div>
                        
                        <div class="animate__animated animate__fadeInUp animate__delay-1s">
                            <p class="text-indigo-200 text-[10px] font-bold uppercase tracking-widest mb-0.5">Total Tagihan Anda</p>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                                    Rp <?= number_format($bill ?? 0, 0, ',', '.') ?>
                                </span>
                                <?php if (($bill ?? 0) > 0): ?>
                                    <span class="px-2 py-0.5 rounded-full bg-rose-500/80 text-white text-[9px] font-bold border border-rose-400/50">BELUM LUNAS</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/80 text-white text-[9px] font-bold border border-emerald-400/50">LUNAS</span>
                                <?php endif; ?>
                                <button onclick="openBillModal()" class="w-6 h-6 rounded-full bg-white/20 hover:bg-white text-white hover:text-indigo-600 flex items-center justify-center transition-colors border border-white/10 ml-1" title="Lihat Rincian">
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Bill Details Modal -->
        <div id="billModal" class="modal fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="modal-content w-full max-w-lg glass p-0 rounded-2xl shadow-2xl relative overflow-hidden bg-white dark:bg-slate-900">
                <!-- Header -->
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-indigo-600/5">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fas fa-receipt mr-2 text-indigo-500"></i> Rincian Tagihan
                    </h3>
                    <button onclick="closeModal('billModal')" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-rose-500 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-5 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    
                    <!-- Section: Kewajiban -->
                    <div class="mb-6">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Kewajiban (Tahun Lalu & Sekarang)</h4>
                        <div class="space-y-2">
                            <?php if (!empty($billDetails)): ?>
                                <?php foreach ($billDetails as $item): ?>
                                    <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50 last:border-0">
                                        <span class="text-sm text-slate-600 dark:text-slate-300"><?= $item['item'] ?></span>
                                        <span class="text-sm font-semibold text-slate-800 dark:text-white">Rp <?= number_format($item['amount'], 0, ',', '.') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-400 italic">Tidak ada data tarif aktif.</p>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-slate-100 dark:border-slate-800 font-bold">
                                <span class="text-sm text-slate-800 dark:text-slate-200">Total Kewajiban</span>
                                <span class="text-sm text-indigo-600 dark:text-indigo-400">Rp <?= number_format($totalObligation ?? 0, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Pembayaran -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Riwayat Pembayaran (2 Thn Terakhir)</h4>
                        <div class="space-y-2">
                             <?php if (!empty($userPayments)): ?>
                                <?php foreach ($userPayments as $p): ?>
                                    <div class="flex justify-between items-center py-2 border-b border-slate-50 dark:border-slate-800/50 last:border-0">
                                        <div>
                                            <p class="text-xs text-slate-500"><?= date('d M Y', strtotime($p['tgl_bayar'])) ?></p>
                                            <p class="text-sm text-slate-600 dark:text-slate-300"><?= $p['keterangan'] ?: 'Pembayaran Iuran' ?></p>
                                        </div>
                                        <span class="text-sm font-semibold text-green-600">Rp <?= number_format($p['jml_bayar'], 0, ',', '.') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-400 italic">Belum ada pembayaran tercatat untuk periode ini.</p>
                            <?php endif; ?>

                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-slate-100 dark:border-slate-800 font-bold">
                                <span class="text-sm text-slate-800 dark:text-slate-200">Total Dibayar</span>
                                <span class="text-sm text-green-600">Rp <?= number_format($totalPaid ?? 0, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer: Grand Total -->
                <div class="p-5 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-slate-700 dark:text-slate-300">Sisa Tagihan</span>
                        <span class="text-xl font-extrabold <?= ($bill ?? 0) > 0 ? 'text-rose-600' : 'text-green-600' ?>">Rp <?= number_format($bill ?? 0, 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>


        <!-- Menu Grid (Mobile Style) -->
        <div class="grid grid-cols-4 sm:grid-cols-5 lg:grid-cols-12 gap-2 sm:gap-2 animate__animated animate__fadeInUp">
            <?php if(!empty($menus)): ?>
                <?php foreach($menus as $menu): ?>
                    <a href="<?= $menu['alamat_url'] ?>" class="group flex flex-col items-center">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-center mb-2 group-hover:shadow-md group-hover:scale-105 transition-all duration-300">
                            <ion-icon name="<?= $menu['ikon'] ?>" class="text-3xl sm:text-4xl text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-500"></ion-icon>
                        </div>
                        <span class="text-[10px] sm:text-sm font-bold text-slate-700 dark:text-slate-300 text-center leading-tight"><?= $menu['nama'] ?></span>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="col-span-full text-center text-slate-400">Belum ada menu tersedia.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="mt-12 sm:mt-20 py-8 sm:py-10 border-t border-slate-100 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-slate-400 dark:text-slate-500 text-xs sm:text-sm font-medium">&copy; <?= date('Y') ?> <?= 'Jimpitan App' ?>. Built with CodeIgniter 4.</p>
            <div class="flex items-center justify-center space-x-2 mt-0">
                 <span class="px-0 py-0.5 text-[10px] font-bold text-green-400 dark:text-green-500 tracking-tighter">Wesite: <a href="http://appsbee.my.id" target="_blank">appsbee.my.id</a></span>
            </div>
        </div>
    </footer>

    <!-- Profile Modal -->
    <div id="profileModal" class="modal fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div class="modal-content w-full max-w-md glass p-6 sm:p-8 rounded-[2rem] shadow-2xl relative">
            <button onclick="closeModal('profileModal')" class="absolute top-6 right-6 text-slate-400 hover:text-rose-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-xl mx-auto mb-4">
                    <i class="fas fa-user-circle text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Ubah Profil</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Gunakan nama yang mudah dikenali</p>
            </div>
            <form action="/auth/updateProfile" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-1">Nama Lengkap Baru</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <input type="text" name="name" value="<?= session()->get('name') ?>" required class="w-full pl-10 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-200 placeholder:text-slate-300 shadow-sm" placeholder="Nama Lengkap">
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-[0.98]">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="modal fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div class="modal-content w-full max-w-md glass p-6 sm:p-8 rounded-[2rem] shadow-2xl relative">
            <button onclick="closeModal('passwordModal')" class="absolute top-6 right-6 text-slate-400 hover:text-rose-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-tr from-amber-600 to-amber-500 rounded-2xl flex items-center justify-center text-white shadow-xl mx-auto mb-4">
                    <i class="fas fa-key text-3xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white">Ubah Password</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Pastikan password unik dan kuat</p>
            </div>
            <form action="/auth/updatePassword" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-1">Password Lama</label>
                    <input type="password" name="current_password" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-slate-700 dark:text-slate-200 shadow-sm" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-1">Password Baru</label>
                    <input type="password" name="new_password" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-200 shadow-sm" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5 ml-1">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-slate-700 dark:text-slate-200 shadow-sm" placeholder="••••••••">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-indigo-200 transition-all mt-2 active:scale-[0.98]">
                    Perbarui Password
                </button>
            </form>
        </div>
    </div>

    <script>
        // Dropdown Logic
        const dropdownTrigger = document.getElementById('userDropdownTrigger');
        const userDropdown = document.getElementById('userDropdown');

        dropdownTrigger.onclick = (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        };

        window.onclick = () => {
            if (userDropdown.classList.contains('active')) {
                userDropdown.classList.remove('active');
            }
        };

        // Modal Logic
        function openProfileModal() {
            document.getElementById('profileModal').classList.add('active');
            if (userDropdown) userDropdown.classList.remove('active');
            if (typeof mobileMenu !== 'undefined' && mobileMenu.classList.contains('active')) mobileMenu.classList.remove('active');
        }

        function openPasswordModal() {
            document.getElementById('passwordModal').classList.add('active');
            if (userDropdown) userDropdown.classList.remove('active');
            if (typeof mobileMenu !== 'undefined' && mobileMenu.classList.contains('active')) mobileMenu.classList.remove('active');
        }

        function openBillModal() {
            document.getElementById('billModal').classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Close modal on background click
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });

        // Theme Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const body = document.body;

        // Check for saved theme
        if (localStorage.getItem('theme') === 'dark') {
            html.classList.add('dark');
            body.classList.add('dark');
        }

        if (themeToggle) {
            themeToggle.onclick = () => {
                html.classList.toggle('dark');
                body.classList.toggle('dark');
                const isDark = html.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            };
        }

        // Mobile Menu Logic
        const mobileMenu = document.getElementById('mobileMenu');
        const openMenu = document.getElementById('openMenu');
        const closeMenu = document.getElementById('closeMenu');

        if (openMenu) openMenu.onclick = () => mobileMenu.classList.add('active');
        if (closeMenu) closeMenu.onclick = () => mobileMenu.classList.remove('active');



        // Test SweetAlert2
        const testAlertBtn = document.getElementById('testAlert');
        if (testAlertBtn) {
            testAlertBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Notifikasi',
                    text: 'Semua sistem berjalan normal hari ini.',
                    icon: 'info',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#6366f1',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl shadow-lg ring-4 ring-indigo-500/10'
                    }
                });
            });
        }
    </script>

    <!-- Bottom Navigation Bar (Mobile) -->
    <div class="md:hidden bottom-nav animate__animated animate__fadeInUp">
        <a href="/" class="nav-item active">
            <i class="fas fa-home text-xl mb-1"></i>
            <span>Home</span>
        </a>
        
        <?php 
        $role = session()->get('role');
        $shift = session()->get('shift');
        $today = date('l');
        
        $showQR = false;
        if ($role === 's_admin' || $role === 'admin') {
            $showQR = true;
        } elseif ($role !== 'warga' && $shift === $today) {
            $showQR = true;
        }
        
        if ($showQR): 
        ?>
        <div class="relative w-16">
            <a href="<?= base_url('/scan') ?>" class="scan-button">
                <i class="fas fa-qrcode"></i>
            </a>
        </div>
        <?php endif; ?>

        <a href="javascript:void(0)" onclick="history.back()" class="nav-item active">
            <i class="fas fa-arrow-left text-xl mb-1"></i>
            <span>Kembali</span>
        </a>
    </div>

</body>
</html>
