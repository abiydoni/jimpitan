<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
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
        
        // Dark Mode Logic
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.3); border-radius: 20px; }

        /* Chat Backgrounds */
        #messagesContainer {
            background-image: url('<?= base_url('assets/img/light.jpg') ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed; /* Parallax efffect */
        }
        .dark #messagesContainer {
            background-image: url('<?= base_url('assets/img/dark.jpg') ?>');
        }
        
        /* Compact Tweaks */
        .chat-bubble {
            padding: 0.4rem 0.75rem !important;
            line-height: 1.4 !important;
            font-size: 0.875rem !important;
        }
        .message-row {
            margin-bottom: 0.4rem !important;
        }
        .date-separator {
            margin-top: 0.75rem !important;
            margin-bottom: 0.75rem !important;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex flex-col h-[100dvh] overflow-hidden text-sm">

    <!-- Header Mobile (Visible only on mobile when on user list) -->


    <div class="flex flex-1 h-full overflow-hidden max-w-7xl mx-auto w-full md:p-4 gap-4 relative">
        
        <!-- Sidebar (User List) -->
        <div id="sidebar" class="w-full md:w-1/3 lg:w-1/4 bg-white dark:bg-gray-800 md:rounded-2xl shadow-lg flex flex-col h-full z-10 transition-transform duration-300 absolute md:relative top-0 left-0">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <a href="<?= base_url('/') ?>" class="mr-1 w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 transition-colors" title="Kembali ke Menu Utama">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </a>
                    <?php if(!empty($user_foto)): ?>
                        <img src="<?= base_url('img/warga/' . $user_foto) ?>" class="w-10 h-10 rounded-full object-cover border-2 border-indigo-100 dark:border-gray-600">
                    <?php else: ?>
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold">
                            <?= strtoupper(substr($user_name, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                         <h2 class="font-bold text-lg text-gray-800 dark:text-gray-100 leading-tight"><?= esc($user_name) ?></h2>
                         <p class="text-[10px] text-green-500 font-semibold">Online</p>
                    </div>
                    <div class="ml-auto flex gap-2">
                        <button id="btnEnableNotif" onclick="askPermission(true)" class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 hover:bg-indigo-200 dark:hover:bg-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 transition-colors shadow-sm hidden" title="Aktifkan Notifikasi">
                            <i class="fas fa-bell text-xs"></i>
                        </button>
                    </div>
                </div>
                <!-- Connection Status Dot -->
                <div id="connectionStatus" class="w-3 h-3 rounded-full bg-green-500 shadow-sm border border-white dark:border-gray-800" title="Terhubung"></div>
            </div>
            
            <!-- Search -->
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50">
                <div class="flex gap-2">
                    <input type="text" id="searchUser" placeholder="Cari warga..." class="flex-1 p-2 rounded-lg bg-white dark:bg-gray-600 border-none focus:ring-2 focus:ring-indigo-500 text-sm shadow-sm">

                </div>
            </div>

            <!-- User List -->
            <div id="userList" class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
                <!-- User items will be rendered here -->
                <div class="text-center text-gray-400 py-4 text-sm">Memuat pengguna...</div>
            </div>
        </div>

        <!-- Chat Area -->
        <div id="chatArea" class="w-full md:w-2/3 lg:w-3/4 bg-white dark:bg-gray-800 md:rounded-2xl shadow-lg flex flex-col h-full transform translate-x-full md:translate-x-0 absolute md:relative transition-transform duration-300 z-20 top-0 left-0">
            
            <!-- Default State -->
            <div id="emptyState" class="hidden md:flex flex-col items-center justify-center h-full text-gray-400">
                <i class="far fa-comments text-6xl mb-4 text-gray-300 dark:text-gray-700"></i>
                <p>Pilih warga untuk mulai chat</p>
            </div>

            <!-- Chat Content -->
            <div id="activeChat" class="flex flex-col h-full hidden">
                <!-- Chat Header -->
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-white dark:bg-gray-800 shadow-sm z-30">
                    <div class="flex items-center gap-3">
                        <button onclick="goBack()" class="md:hidden text-gray-500 hover:text-indigo-600"><i class="fas fa-arrow-left text-lg"></i></button>
                        <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold text-lg" id="chatAvatar">
                            ?
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white leading-tight" id="chatName">Nama Warga</h3>
                            <p class="text-xs text-green-500">Online</p>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div id="messagesContainer" class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2 min-h-0 relative">
                    <!-- Messages will be rendered here -->
                </div>

                <!-- Scroll Bottom Button -->
                <button id="scrollTopBtn" onclick="scrollToBottom()" class="absolute bottom-24 right-5 w-10 h-10 bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 rounded-full shadow-lg border border-gray-100 dark:border-gray-600 hidden flex-col items-center justify-center hover:scale-110 transition-transform z-20 opacity-90 hover:opacity-100">
                    <i class="fas fa-chevron-down text-sm"></i>
                </button>

                <!-- Input Area -->
                <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                    <!-- Reply Preview -->
                    <div id="replyPreview" class="hidden mb-2 p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-l-4 border-indigo-500 flex justify-between items-center">
                        <div class="overflow-hidden">
                            <p class="text-[10px] font-bold text-indigo-500 mb-0.5" id="replySenderName">Sender Name</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" id="replyMessageText">Message...</p>
                        </div>
                        <button onclick="cancelReply()" class="text-gray-400 hover:text-red-500 p-1"><i class="fas fa-times"></i></button>
                    </div>

                    <form id="messageForm" class="flex gap-2 items-end">
                        <input type="hidden" id="receiverId" name="receiver_id">
                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-2xl p-2 flex items-center gap-2">
                            <textarea id="messageInput" rows="1" placeholder="Ketik pesan..." class="w-full bg-transparent border-none focus:ring-0 outline-none focus:outline-none text-sm resize-none max-h-32 py-2" oninput="autoResize(this)"></textarea>
                        </div>
                        <button type="submit" class="w-10 h-10 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center shadow-lg transition-transform active:scale-95">
                            <i class="fas fa-paper-plane text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Forward Modal -->
    <div id="forwardModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeForwardModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 flex flex-col max-h-[80vh]">
            <div class="flex justify-between items-center mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">
                <h3 class="font-bold text-gray-800 dark:text-gray-200">Teruskan Pesan</h3>
                <button onclick="closeForwardModal()" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
            </div>
            <input type="text" id="searchForward" placeholder="Cari warga..." class="w-full p-2 mb-2 rounded-lg bg-gray-100 dark:bg-gray-700 border-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <div id="forwardUserList" class="flex-1 overflow-y-auto custom-scrollbar space-y-1">
                <!-- User list rendering here -->
            </div>
        </div>
    </div>

    <!-- JavaScript Implementation -->
    <script>
        const baseUrl = '<?= base_url() ?>';
        const currentUserId = '<?= $user_id ?>';
        let activeUserId = null;
        let pollingInterval = null;
        let isMobile = window.innerWidth < 768;
        let replyingTo = null; // { id, message, sender }
        
        let allUsers = []; // Global Users State

        function getTimeAgo(date) {
            if (!date || date === '0000-00-00 00:00:00' || date === '0000-00-00') return 'Pernah aktif';
            const now = new Date();
            const then = new Date(date);
            const seconds = Math.floor((now - then) / 1000);
            
            if (seconds < 60) return 'Baru saja aktif';
            if (seconds < 3600) return `Aktif ${Math.floor(seconds / 60)}m lalu`;
            if (seconds < 86400) return `Aktif ${Math.floor(seconds / 3600)}j lalu`;
            return `Aktif ${Math.floor(seconds / 86400)}h lalu`;
        }

        async function fetchUsers() {
            try {
                const res = await fetch(`${baseUrl}/chat/users`);
                const users = await res.json();
                allUsers = users; // Store globally
                renderUserList(users);
                
                // Update active user status in header if there is one
                if (activeUserId) {
                    const activeUser = allUsers.find(u => u.id_code === activeUserId);
                    if (activeUser) updateChatHeaderStatus(activeUser);
                }
                
                return users;
            } catch(e) {
                console.error(e);
                return [];
            }
        }

        function renderUserList(users) {
            const userListEl = document.getElementById('userList');
            if(!userListEl) return;
            userListEl.innerHTML = '';
            
            if (!Array.isArray(users)) {
                console.error("renderUserList expected array, got:", users);
                userListEl.innerHTML = '<div class="text-center text-red-500 py-4 text-xs">Gagal memuat warga</div>';
                return;
            }
            
            // Search Filter
            const searchInput = document.getElementById('searchUser');
            const search = searchInput ? searchInput.value.toLowerCase() : '';
            
            const filtered = users.filter(u => u.name.toLowerCase().includes(search));

            filtered.forEach(user => {
                const isGroup = user.id_code === 'GROUP_ALL';
                const isActive = user.id_code === activeUserId;
                const unreadBadge = user.unread_count > 0 
                    ? `<div class="w-5 h-5 bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold shadow-sm ring-2 ring-white dark:ring-gray-800">${user.unread_count}</div>` 
                    : '';

                // Style Differentiation
                const avatarBg = isGroup 
                    ? 'bg-orange-100 dark:bg-orange-900/50 text-orange-600 dark:text-orange-400' 
                    : (user.id_code === 'SYSTEM' 
                        ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400'
                        : 'bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-300');
                
                const statusColor = user.is_online ? 'bg-green-500' : 'bg-gray-400';
                const onlineDot = (isGroup || user.id_code === 'SYSTEM')
                    ? '' // No online dot for group or system
                    : `<div class="absolute bottom-0 right-0 w-3 h-3 ${statusColor} border-2 border-white dark:border-gray-800 rounded-full"></div>`;

                // Initial or Photo
                let avatarContent;
                if (isGroup) {
                    avatarContent = '<i class="fas fa-users text-sm"></i>';
                } else if (user.id_code === 'SYSTEM') {
                    avatarContent = '<i class="fas fa-robot text-sm"></i>';
                } else if (user.foto) {
                    avatarContent = `<img src="${baseUrl}/img/warga/${user.foto}" class="w-full h-full rounded-full object-cover">`;
                } else {
                    avatarContent = user.name.charAt(0).toUpperCase();
                }

                const div = document.createElement('div');
                // Reduced padding from p-3 to p-2
                div.className = `p-2 rounded-xl cursor-pointer flex items-center gap-3 transition-colors ${isActive ? 'bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'}`;
                div.onclick = () => selectUser(user);
                
                const statusText = isGroup ? 'Ruang diskusi warga' : getTimeAgo(user.last_active_at);

                div.innerHTML = `
                    <div class="w-10 h-10 rounded-full shrink-0 ${!user.foto ? avatarBg : ''} flex items-center justify-center font-bold relative">
                        ${avatarContent}
                        ${onlineDot}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-0">
                            <h4 class="font-semibold text-sm truncate ${isGroup ? 'text-orange-700 dark:text-orange-300' : 'text-gray-800 dark:text-gray-100'}">${user.name}</h4>
                            <span class="text-[10px] text-gray-400"></span>
                        </div>
                        <p class="text-[11px] text-gray-500 truncate">${statusText}</p>
                    </div>
                    ${unreadBadge}
                `;
                userListEl.appendChild(div);
            });
        }

        async function selectUser(user, pushHistory = true) {
            activeUserId = user.id_code;
            
            if (pushHistory) {
                const newUrl = `${baseUrl}/chat?user_id=${user.id_code}`;
                history.pushState({ userId: user.id_code }, '', newUrl);
            }
            
            // UI Updates
            document.getElementById('receiverId').value = activeUserId;
            
            // Customize Name
            let displayName = user.name;
            if (activeUserId === 'SYSTEM') displayName = 'appsbee System';
            document.getElementById('chatName').innerText = displayName;

            updateChatHeaderStatus(user);
            
            const avatarEl = document.getElementById('chatAvatar');
            if (user.id_code === 'GROUP_ALL') {
                 avatarEl.innerHTML = '<i class="fas fa-users text-sm"></i>';
                 avatarEl.className = 'w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-lg';
            } else if (user.id_code === 'SYSTEM') {
                 avatarEl.innerHTML = '<i class="fas fa-robot text-sm"></i>';
                 avatarEl.className = 'w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg';
            } else if (user.foto) {
                 avatarEl.innerHTML = `<img src="${baseUrl}/img/warga/${user.foto}" class="w-full h-full rounded-full object-cover">`;
                 avatarEl.className = 'w-10 h-10 rounded-full bg-transparent flex items-center justify-center';
            } else {
                 avatarEl.innerText = user.name.charAt(0).toUpperCase();
                 avatarEl.className = 'w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold text-lg';
            }
            
            const emptyState = document.getElementById('emptyState');
            const activeChat = document.getElementById('activeChat');

            if(window.innerWidth >= 768) {
                emptyState.classList.add('hidden');
                emptyState.classList.remove('md:flex'); // Important: remove desktop flex display
                activeChat.classList.remove('hidden');
            } else {
                activeChat.classList.remove('hidden');
            }
            
            updateMobileView();
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.innerHTML = '<div class="text-center py-4 text-gray-400 text-xs">Memuat pesan...</div>';
            
            await loadMessages();
            fetchUsers();
        }

        function updateChatHeaderStatus(user) {
            const statusEl = document.querySelector('#activeChat h3 + p'); // Target the p after h3 (chatName)
            if (!statusEl) return;
            
            if (user.id_code === 'GROUP_ALL') {
                statusEl.innerText = 'Grup Diskusi';
                statusEl.className = 'text-xs text-orange-500';
            } else if (user.id_code === 'SYSTEM') {
                statusEl.innerText = 'Official System';
                statusEl.className = 'text-xs text-blue-500 font-bold';
            } else {
                if (user.is_online) {
                    statusEl.innerText = 'Online';
                    statusEl.className = 'text-xs text-green-500';
                } else {
                    statusEl.innerText = getTimeAgo(user.last_active_at);
                    statusEl.className = 'text-xs text-gray-400';
                }
            }
        }

        async function loadMessages() {
            if(!activeUserId) return;
            try {
                const res = await fetch(`${baseUrl}/chat/messages?user_id=${activeUserId}`);
                const messages = await res.json();
                
                const messagesContainer = document.getElementById('messagesContainer');
                messagesContainer.innerHTML = '';
                
                messages.forEach(msg => {
                    renderMessage(msg, false);
                });
                
                scrollToBottom();
            } catch(e) { console.error(e); }
        }

        function getNameColor(name) {
            const colors = [
                'text-red-500', 'text-orange-500', 'text-amber-500', 
                'text-green-500', 'text-emerald-500', 'text-teal-500', 
                'text-cyan-500', 'text-blue-500', 'text-indigo-500', 
                'text-violet-500', 'text-purple-500', 'text-fuchsia-500', 'text-pink-500', 'text-rose-500'
            ];
            let hash = 0;
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            return colors[Math.abs(hash) % colors.length];
        }

        function formatMessage(text) {
            if (!text) return '';
            
            // 1. Escape HTML (Security)
            let formatted = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            // 2. Auto-link URLs, Emails, Phones
            // URLs
            const urlRegex = /(https?:\/\/[^\s<]+)/g;
            formatted = formatted.replace(urlRegex, '<a href="$1" target="_blank" class="text-blue-500 hover:underline break-words">$1</a>');
            
            // Emails
            const emailRegex = /([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/g;
            formatted = formatted.replace(emailRegex, '<a href="mailto:$1" class="text-blue-500 hover:underline">$1</a>');

            // Phone Numbers (Indonesian format trigger: +62, 62, 08.. followed by digits)
            const phoneRegex = /(?:\+62|62|08)[0-9]{8,13}\b/g;
            formatted = formatted.replace(phoneRegex, (match) => {
                 // Ensure +62 format for href
                 let clean = match;
                 if(clean.startsWith('0')) clean = '62' + clean.substring(1);
                 if(!clean.startsWith('+')) clean = '+' + clean;
                 return `<a href="tel:${clean}" class="text-blue-500 hover:underline">${match}</a>`;
            });

            // 3. Code Block (```code```)
            formatted = formatted.replace(/```(.*?)```/gs, '<code class="bg-gray-200 dark:bg-gray-800 p-1 rounded font-mono text-xs">$1</code>');

            // 4. Bold (*text*)
            formatted = formatted.replace(/\*(.*?)\*/g, '<strong>$1</strong>');

            // 5. Italic (_text_)
            formatted = formatted.replace(/_(.*?)_/g, '<em>$1</em>');

            // 6. Strikethrough (~text~)
            formatted = formatted.replace(/~(.*?)~/g, '<del>$1</del>');

            // 7. Newlines to <br>
            formatted = formatted.replace(/\n/g, '<br>');

            return formatted;
        }

        function isOnlyEmojis(str) {
            if(!str) return false;
            // Remove whitespace and newlines
            const clean = str.replace(/\s/g, '');
            if(!clean) return false;
            
            // Regex for Emoji (Simple version, covers most)
            // Matches 1 to 5 emojis from start to end
            const regex = /^(\p{Extended_Pictographic}|\p{Emoji_Presentation}){1,5}$/u;
            return regex.test(clean);
        }

        function formatDateSeparator(dateString) {
            const date = new Date(dateString);
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);

            const isToday = date.toDateString() === today.toDateString();
            const isYesterday = date.toDateString() === yesterday.toDateString();

            if (isToday) return 'Hari Ini';
            if (isYesterday) return 'Kemarin';
            
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }

        async function copyMessage(text) {
             try {
                 await navigator.clipboard.writeText(text);
                 const Toast = Swal.mixin({
                    toast: true,
                    position: "top",
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: false,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: "success",
                    title: "Pesan disalin"
                });
             } catch (err) {
                 console.error('Failed to copy: ', err);
             }
        }

        function decodeHtml(html) {
            const txt = document.createElement("textarea");
            txt.innerHTML = html;
            return txt.value;
        }

        function renderMessage(msg, scroll = true) {
            // Anti-duplicate check
            if (msg.id && document.getElementById(`msg-${msg.id}`)) return;

            const isMe = msg.sender_id === currentUserId;
            const messagesContainer = document.getElementById('messagesContainer');
            
            // --- Date Separator Logic ---
            const msgDate = new Date(msg.created_at);
            const msgDateString = msgDate.toDateString(); 
            
            const lastMsg = messagesContainer.lastElementChild;
            const lastDateString = lastMsg ? lastMsg.getAttribute('data-date') : null;

            if (lastDateString !== msgDateString) {
                const separatorDiv = document.createElement('div');
                separatorDiv.className = 'flex justify-center date-separator opacity-80';
                separatorDiv.innerHTML = `
                    <span class="bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[9px] px-2 py-0.5 rounded-full shadow-sm font-medium">
                        ${formatDateSeparator(msg.created_at)}
                    </span>
                `;
                separatorDiv.setAttribute('data-date', msgDateString); // Prevent duplicate separators if polling re-runs
                messagesContainer.appendChild(separatorDiv);
            }

            // Deduplication for pending handling:
            // If this is a real message (has ID) and we have a pending message with same text, remove the pending one.
            if (msg.id && !msg.is_pending) {
                const pending = document.querySelector(`.msg-pending[data-content="${CSS.escape(msg.message)}"]`);
                if (pending) pending.remove();
            }

            const div = document.createElement('div');
            div.id = msg.id ? `msg-${msg.id}` : `temp-${Date.now()}`;
            div.setAttribute('data-date', msgDateString); // Track date
            if(msg.is_pending) {
                div.classList.add('msg-pending');
                div.setAttribute('data-content', msg.message);
            }

            // PERFORMANCE FIX: Only animate if it's a NEW message (not history load)
            const animationClass = scroll ? 'animate__animated animate__fadeInUp animate__faster' : '';
            div.tabIndex = 0; // Make focusable for mobile tap
            div.className = `flex ${isMe ? 'justify-end' : 'justify-start'} message-row ${animationClass} group items-end gap-1 outline-none tap-highlight-transparent ${msg.is_pending ? 'msg-pending' : ''}`;
            
            const time = new Date(msg.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
            
            const displayName = msg.sender_name || 'Warga';
            const nameColor = (!isMe && activeUserId === 'GROUP_ALL') ? getNameColor(displayName) : '';
            
            // Check for Jumbo Emoji
            const isJumbo = isOnlyEmojis(msg.message);
            const bubbleClass = isJumbo 
                ? 'bg-transparent shadow-none text-4xl p-0 border-none' // Jumbo Style
                : (isMe 
                    ? 'bg-indigo-600 text-white rounded-br-none' 
                    : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-bl-none border border-gray-100 dark:border-gray-600');
            
            let quoteHtml = '';
            if(msg.reply_to_id && msg.reply_message) {
                 const replySender = msg.reply_sender || 'User';
                 const replyText = formatMessage(msg.reply_message); 
                 
                 // Context-aware styling
                 const quoteStyles = isMe
                    ? 'bg-black/10 border-white/50 text-white/90' // On User's Blue Bubble
                    : 'bg-indigo-50 dark:bg-gray-800 border-indigo-500 text-gray-600 dark:text-gray-300'; // On Friend's White Bubble
                 
                 const senderStyles = isMe
                    ? 'text-white'
                    : 'text-indigo-600 dark:text-indigo-400';

                 quoteHtml = `
                    <div class="mb-2 pl-3 py-2 pr-2 rounded-r-lg rounded-tl-lg border-l-[3px] ${quoteStyles} text-xs cursor-pointer hover:brightness-95 transition-all w-full relative overflow-hidden" onclick="scrollToMessage(${msg.reply_to_id})">
                        <p class="font-bold ${senderStyles} text-[10px] mb-0.5 truncate">${replySender}</p>
                        <p class="truncate opacity-80 line-clamp-1 italic">${replyText}</p>
                    </div>
                 `;
            }

            const rawMessage = msg.message.replace(/'/g, "\\'").replace(/\n/g, "\\n");

            const actionsHtml = `
                <div class="opacity-0 group-hover:opacity-100 group-focus:opacity-100 group-active:opacity-100 group-focus-within:opacity-100 transition-opacity flex flex-col items-center gap-1 ${isMe ? 'order-first mr-1' : 'ml-1'}">
                    <button onclick="copyMessage('${rawMessage}')" class="px-1.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400 text-xs shadow-sm transition-transform hover:scale-105" title="Salin">
                         <i class="far fa-copy"></i>
                    </button>
                    <button onclick="startReply(${msg.id}, '${displayName}', '${rawMessage}')" class="px-1.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-blue-500 dark:text-blue-400 text-xs shadow-sm transition-transform hover:scale-105" title="Balas">
                        <i class="fas fa-reply"></i>
                    </button>
                    <button onclick="openForwardModal('${rawMessage}')" class="px-1.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-green-500 dark:text-green-400 text-xs shadow-sm transition-transform hover:scale-105" title="Teruskan">
                        <i class="fas fa-share"></i>
                    </button>
                </div>
            `;

            div.innerHTML = `
                ${isMe ? actionsHtml : ''}
                <div class="max-w-[90%] md:max-w-[80%] flex flex-col ${isMe ? 'items-end' : 'items-start'} overflow-hidden">
                    <div class="${isJumbo ? 'px-0 py-0' : 'chat-bubble'} rounded-2xl text-[13px] shadow-sm relative ${bubbleClass} break-words overflow-hidden max-w-full">
                        ${(!isMe && activeUserId === 'GROUP_ALL' && !isJumbo) ? `<p class="text-[10px] font-bold ${nameColor} mb-0.5 leading-tight">${displayName}</p>` : ''}
                        ${(!isMe && activeUserId === 'GROUP_ALL' && isJumbo) ? `<p class="text-[9px] font-bold ${nameColor} mb-0 leading-tight bg-white/80 dark:bg-slate-800/80 px-1 rounded absolute -top-4 left-0 w-max shadow-sm">${displayName}</p>` : ''}
                        ${quoteHtml}
                        ${formatMessage(msg.message)}
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 px-1 flex gap-1 items-center">
                        ${time}
                        ${isMe ? (msg.is_read == 1 ? '<i class="fas fa-check-double text-blue-400"></i>' : (msg.is_pending ? '<i class="far fa-clock"></i>' : '<i class="fas fa-check"></i>')) : ''}
                    </span>
                </div>
                ${!isMe ? actionsHtml : ''}
            `;
            
            // --- SWIPE TO REPLY LOGIC ---
            // Touch handlers
            div.addEventListener('touchstart', handleTouchStart, false);
            div.addEventListener('touchmove', handleTouchMove, false);
            div.addEventListener('touchend', handleTouchEnd, false);
            
            // Only attach for non-pending real messages
            if (msg.id) {
                div.dataset.msgId = msg.id;
                div.dataset.sender = displayName;
                div.dataset.content = rawMessage;
            }

            messagesContainer.appendChild(div);
            if(scroll) scrollToBottom();
        }

        // --- Swipe Gesture Handlers ---
        let xDown = null;
        let yDown = null;
        let swipeEl = null;
        let isSwiping = false;

        function handleTouchStart(evt) {
            const firstTouch = evt.touches[0];
            xDown = firstTouch.clientX;
            yDown = firstTouch.clientY;
            // Find the message-row wrapper
            swipeEl = evt.currentTarget; 
            isSwiping = false;
        }

        function handleTouchMove(evt) {
            if (!xDown || !yDown) return;

            let xUp = evt.touches[0].clientX;
            let yUp = evt.touches[0].clientY;

            let xDiff = xDown - xUp;
            let yDiff = yDown - yUp;

            // Horizontal Swipe Check (more horizontal than vertical)
            if (Math.abs(xDiff) > Math.abs(yDiff)) {
                // Prevent vertical scroll if swiping hard horizontal
                if (Math.abs(xDiff) > 10) evt.preventDefault();
                
                // Visual Feedback: Translate Element
                // Limit drag to reasonable amount
                let translateX = -xDiff; 
                if (translateX > 70) translateX = 70; // Cap right pull
                if (translateX < -70) translateX = -70; // Cap left pull
                
                // Only move if it's a pull (optional: allow both ways)
                swipeEl.style.transform = `translateX(${translateX}px)`;
                swipeEl.style.transition = 'none'; // Instant follow
                
                isSwiping = true;
            }
        }

        function handleTouchEnd(evt) {
            if (!xDown || !yDown || !isSwiping) {
                // Reset if just a tap
                if(swipeEl) {
                    swipeEl.style.transform = '';
                    swipeEl.style.transition = '';
                }
                xDown = null; yDown = null;
                return;
            }

            let xUp = evt.changedTouches[0].clientX;
            let xDiff = xDown - xUp;
            const threshold = 50; // px to trigger

            // Snap back animation
            swipeEl.style.transition = 'transform 0.3s ease-out';
            swipeEl.style.transform = 'translateX(0px)';

            // Trigger Reply if threshold met (Left or Right swipe)
            if (Math.abs(xDiff) > threshold) {
                // Vibrate if supported
                if (navigator.vibrate) navigator.vibrate(50);
                
                const id = swipeEl.dataset.msgId;
                const sender = swipeEl.dataset.sender;
                const content = swipeEl.dataset.content;
                
                if (id && sender && content) {
                    startReply(id, sender, content);
                }
            }

            // Reset
            xDown = null;
            yDown = null;
            swipeEl = null;
            isSwiping = false;
        }

        function scrollToBottom() {
            const messagesContainer = document.getElementById('messagesContainer');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function scrollToMessage(id) {
            const el = document.getElementById(`msg-${id}`);
            if(el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const bubble = el.querySelector('.shadow-sm'); 
                if(bubble) {
                    bubble.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-2');
                    setTimeout(() => bubble.classList.remove('ring-2', 'ring-indigo-500', 'ring-offset-2'), 2000);
                }
            } else {
                console.log("Message not loaded/found:", id);
            }
        }

        function cleanupDuplicates(newMessages) {
            const pendings = document.querySelectorAll('.msg-pending');
            if(pendings.length === 0) return;
            
            newMessages.forEach(msg => {
                 const cleanMsg = decodeHtml(msg.message || '').trim().replace(/\r\n/g, '\n');
                 const cleanMsgUrl = decodeURIComponent(cleanMsg);
                 
                 pendings.forEach(p => {
                     const rawP = (p.getAttribute('data-content') || '').trim().replace(/\r\n/g, '\n');
                     const rawPUrl = decodeURIComponent(rawP);
                     
                     if (rawP === cleanMsg || rawPUrl === cleanMsgUrl || rawP === cleanMsgUrl || rawPUrl === cleanMsg) {
                         p.remove();
                     }
                 });
            });
        }

        async function startPolling() {
            if(pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(async () => {
                try {
                    const url = activeUserId 
                        ? `${baseUrl}/chat/poll?active_user=${activeUserId}`
                        : `${baseUrl}/chat/poll`;
                    const res = await fetch(url);
                    const data = await res.json();
                    
                    if(activeUserId && data.messages) {
                        const messagesContainer = document.getElementById('messagesContainer');
                        
                        // Self-healing: Aggressive Cleanup
                        cleanupDuplicates(data.messages);
                        
                        // Optimized Polling: Append Only, Don't Wipe
                        let hasNew = false;
                        data.messages.forEach(m => {
                             if(!document.getElementById(`msg-${m.id}`)) {
                                 renderMessage(m, true);
                                 hasNew = true;
                             }
                        });
                        
                        if(hasNew) scrollToBottom();
                    }
                } catch(e) { console.error("Poll error", e); }
            }, 3000);
            
            setInterval(fetchUsers, 10000);
        }

        // Window Helpers (attached to buttons)
        window.autoResize = function(el) {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        }

        window.startReply = function(id, sender, message) {
            replyingTo = { id, sender, message };
            document.getElementById('replySenderName').innerText = sender;
            document.getElementById('replyMessageText').innerText = message;
            document.getElementById('replyPreview').classList.remove('hidden');
            document.getElementById('messageInput').focus();
        }

        window.cancelReply = function() {
            replyingTo = null;
            document.getElementById('replyPreview').classList.add('hidden');
        }
        
        let messageToForward = null;
        window.openForwardModal = function(message) {
            messageToForward = message;
            document.getElementById('forwardModal').classList.remove('hidden');
            renderForwardUserList();
        }
        
        window.closeForwardModal = function() {
            messageToForward = null;
            document.getElementById('forwardModal').classList.add('hidden');
        }
        
        function renderForwardUserList() {
             const list = document.getElementById('forwardUserList');
             list.innerHTML = '';
             const searchInput = document.getElementById('searchForward');
             const search = searchInput ? searchInput.value.toLowerCase() : '';
             
             // Filter out current active user/group to prevent forwarding to same context
             const filtered = allUsers.filter(u => 
                u.name.toLowerCase().includes(search) && 
                u.id_code !== activeUserId
             );
             
             filtered.forEach(user => {
                 const div = document.createElement('div');
                 div.className = 'p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer flex items-center justify-between';
                 div.onclick = () => confirmForward(user);
                 div.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold text-xs">
                            ${user.name.charAt(0).toUpperCase()}
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">${user.name}</span>
                    </div>
                    <i class="fas fa-paper-plane text-gray-400"></i>
                 `;
                 list.appendChild(div);
             });
        }
        
        async function confirmForward(user) {
            // SweetAlert Confirmation
            const result = await Swal.fire({
                title: 'Teruskan Pesan?',
                text: `Kirim ke ${user.name}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal',
                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
            });

            if (!result.isConfirmed) return;
            
            const msgContent = messageToForward; // Capture before clearing
            closeForwardModal();
            
            // Optimistic Render if forwarding to currently active chat
            if(activeUserId === user.id_code) {
                renderMessage({
                    sender_id: currentUserId,
                    message: msgContent, // forwarded message content
                    created_at: new Date().toISOString(),
                    is_pending: true
                }, true);
            }

            try {
                const formData = new FormData();
                formData.append('receiver_id', user.id_code);
                formData.append('message', msgContent);
                if(currentPushEndpoint) {
                    formData.append('exclude_endpoint', currentPushEndpoint);
                }
                await fetch(`${baseUrl}/chat/send`, {
                    method: 'POST', body: formData
                });
                
                // Show success toast/alert only if NOT in the same chat (to avoid clutter)
                if(activeUserId !== user.id_code) {
                     Swal.fire({
                        icon: 'success',
                        title: 'Terkirim',
                        text: `Pesan berhasil diteruskan ke ${user.name}`,
                        timer: 1500,
                        showConfirmButton: false,
                        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                    });
                }
            } catch(e) { 
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal meneruskan pesan.',
                    background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#1f2937'
                });
            }
        }

        function handlePopState(event) {
            const state = event.state;
            if (state && state.userId) {
                const user = allUsers.find(u => u.id_code === state.userId);
                if (user && user.id_code !== activeUserId) selectUser(user, false);
            } else {
                activeUserId = null;
                const rx = document.getElementById('receiverId');
                if(rx) rx.value = '';
                updateMobileView();
            }
        }

        window.addEventListener('popstate', handlePopState);

        window.goBack = function() {
             history.back();
        }

        // Kept for legacy passing if needed, but logic is replaced
        function toggleSidebar() {
             goBack();
        }

        function updateMobileView() {
            const sidebar = document.getElementById('sidebar');
            const chatArea = document.getElementById('chatArea');
            if(window.innerWidth >= 768) {
                sidebar.style.transform = 'none';
                chatArea.style.transform = 'none';
                return;
            }

            if(activeUserId) {
                chatArea.style.transform = 'translateX(0)';
            } else {
                chatArea.style.transform = 'translateX(100%)';
                sidebar.style.transform = 'translateX(0)'; 
            }
        }

        // ------------------ Initialization ------------------
        
        document.addEventListener('DOMContentLoaded', async () => {
            
            // Scroll to Bottom Button Logic
            const msgContainer = document.getElementById('messagesContainer');
            if(msgContainer) {
                msgContainer.addEventListener('scroll', () => {
                    const btn = document.getElementById('scrollTopBtn');
                    if (msgContainer.scrollHeight - msgContainer.scrollTop - msgContainer.clientHeight > 300) {
                        btn.classList.remove('hidden');
                        btn.classList.add('flex');
                    } else {
                        btn.classList.add('hidden');
                        btn.classList.remove('flex');
                    }
                });
            }
            
            await fetchUsers();
            
            const urlParams = new URLSearchParams(window.location.search);
            const targetUserId = urlParams.get('user_id');
            
            if (targetUserId) {
                const targetUser = allUsers.find(u => u.id_code === targetUserId);
                if (targetUser) {
                    history.replaceState({ userId: targetUser.id_code }, '', window.location.href);
                    selectUser(targetUser, false);
                }
            }
            
            startPolling();
            
            document.getElementById('searchUser').addEventListener('input', () => {
                 renderUserList(allUsers); // Re-render logic with internal filtering
            });
            
            document.getElementById('searchForward').addEventListener('input', renderForwardUserList);
            
            // Mobile Keyboard Fix: Scroll to bottom when input focused
            const msgInput = document.getElementById('messageInput');
            msgInput.addEventListener('focus', () => {
                setTimeout(scrollToBottom, 300); // Wait for keyboard animation
            });
            
            // Mobile Height Fix (fallback if dvh doesn't work well on some older webviews)
            window.addEventListener('resize', () => {
                if(window.innerWidth < 768) {
                   scrollToBottom();
                }
            });

            const msgForm = document.getElementById('messageForm');
            // Singleton Guard: Prevent duplicate listeners if script re-runs
            if (msgForm.getAttribute('data-listener-attached')) {
                console.log("Listener already attached to messageForm");
            } else {
                msgForm.setAttribute('data-listener-attached', 'true');
                
                let isSending = false;
                msgForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    if(isSending) return;
                    
                    const input = document.getElementById('messageInput');
                    const message = input.value.trim();
                    
                    if(!message || !activeUserId) return;
    
                    isSending = true;
                    const sendBtn = document.querySelector('#messageForm button');
                    if(sendBtn) sendBtn.disabled = true;
    
                    // Optimistic Rendering
                    renderMessage({
                        sender_id: currentUserId,
                        message: message,
                        created_at: new Date().toISOString(),
                        is_pending: true,
                        reply_to_id: replyingTo ? replyingTo.id : null,
                        reply_message: replyingTo ? replyingTo.message : null,
                        reply_sender: replyingTo ? replyingTo.sender : null
                    }, true);
                    
                    // Play Sent Sound (Soft) using CDN
                    // Message Sent Swoosh
                    const sentAudio = new Audio('https://cdn.pixabay.com/download/audio/2021/08/09/audio_88447e769f.mp3'); 
                    sentAudio.volume = 0.6; 
                    sentAudio.play().catch(e => console.error("Sent Audio Error:", e));
                    
                    input.value = '';
                    input.style.height = 'auto';
                    scrollToBottom();
    
                    try {
                        const formData = new FormData();
                        formData.append('receiver_id', activeUserId);
                        formData.append('message', message);
                        if(replyingTo) {
                            formData.append('reply_to_id', replyingTo.id);
                            cancelReply();
                        }
                        
                        if(currentPushEndpoint) {
                             formData.append('exclude_endpoint', currentPushEndpoint);
                             console.log('📤 Sending exclude_endpoint:', currentPushEndpoint);
                        } else if (Notification.permission === 'granted') {
                             console.log('ℹ️ Push endpoint not yet captured, deduplication may be limited.');
                        }
    
                        const res = await fetch(`${baseUrl}/chat/send`, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        
                    } catch(err) {
                        console.error("Send failed", err);
                        alert("Gagal mengirim pesan");
                    } finally {
                        isSending = false;
                        const sendBtn = document.querySelector('#messageForm button');
                        if(sendBtn) sendBtn.disabled = false;
                    }
                });
            }
        });

        // --- PUSH NOTIFICATION LOGIC ---
        const vapidPublicKey = 'BIb0u4eLioyZgzPJRmFAoI3LdD87wOR2_4L6CpqDmAyIeUK_JqfW17fT-Iy3C4zTlSlrEBZn2cjZ5vh68W0KdSk';
        let currentPushEndpoint = null;
        
        // --- FCM LOGIC ---
        const firebaseConfig = {
            apiKey: "AIzaSyCMO1z8UGvFNyOnzAV-dsx1VLjOtCAjtdc",
            authDomain: "jimpitan-app-a7by777.firebaseapp.com",
            projectId: "jimpitan-app-a7by777",
            storageBucket: "jimpitan-app-a7by777.firebasestorage.app",
            messagingSenderId: "53228839762",
            appId: "1:53228839762:web:ae75cb6fc64b9441ac108b",
            measurementId: "G-XG704TQRJ2"
        };
        
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();
        
        // Handle foreground messages
        messaging.onMessage((payload) => {
            console.log('Message received. ', payload);
            const { title, body } = payload.notification || {};
            const { url } = payload.data || {};
            
            // Only show if chat is NOT active for this sender
            // (Or just show Toast unconditionally for testing)
            // Ideally, play sound here too.
            
            // Native Notification (if permission granted but page focused)
            // Some browsers don't show native notif if page focused, so fallback to Toast/Swal
            
            // Play Sound (LOUD) using CDN for reliability
            // Glass Ping
            const audio = new Audio('https://cdn.pixabay.com/download/audio/2022/03/10/audio_c8c8a73467.mp3'); 
            audio.volume = 1.0; 
            audio.play().catch(e => console.error("Audio Play Error:", e));
            
            // VIBRATE (Tambahan baru)
            if (navigator.vibrate) {
                navigator.vibrate([200, 100, 200, 100, 200]); // Getar 3x
            }

            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onclick = () => {
                        if(url) window.location.href = url;
                    };
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: "info",
                title: title || "Pesan Baru",
                text: body
            });
        });

        async function registerFCM(silent = false) {
            try {
                const registration = await navigator.serviceWorker.ready;
                const token = await messaging.getToken({
                    serviceWorkerRegistration: registration,
                    vapidKey: vapidPublicKey
                });
                
                if (token) {
                    console.log('✅ FCM Token captured:', token);
                    currentPushEndpoint = token; // Add this line
                    await sendFCMTokenToServer(token, silent);
                } else {
                    console.warn('No FCM token received.');
                }
            } catch (err) {
                console.error('Error getting FCM token:', err);
            }
        }

        async function sendFCMTokenToServer(token, silent = false) {
            try {
                const res = await fetch('<?= base_url("push/subscribe_fcm") ?>', {
                    method: 'POST',
                    body: JSON.stringify({ token: token, device_type: 'web' }),
                    headers: { 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                
                if (res.ok) {
                    if (!silent) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });
                        Toast.fire({
                            icon: "success",
                            title: "Notifikasi Aktif"
                        });
                    }
                    const btn = document.getElementById('btnEnableNotif');
                    if(btn) btn.classList.add('hidden');
                } else {
                    console.error('Server error registration:', data);
                }
            } catch (err) {
                console.error('Failed to send token to server:', err);
            }
        }

        async function askPermission(isManual = false) {
            // Fix isManual being an event if called from onclick="askPermission()"
            if(typeof isManual !== 'boolean') isManual = true;

            console.log(`🔔 askPermission called (manual: ${isManual})`);
            
            if (!('serviceWorker' in navigator)) {
                if(isManual) alert('Browser tidak mendukung Service Worker.');
                return;
            }

            // Check if already denied
            if (Notification.permission === 'denied') {
                showManualUnblockGuide();
                return;
            }

            try {
                const permission = await Notification.requestPermission();
                console.log(`🎭 Permission outcome: ${permission}`);
                
                if (permission === 'granted') {
                    if(isManual) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Notifikasi diaktifkan!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                    registerServiceWorker();
                } else if (isManual && permission === 'denied') {
                    // Just blocked it now
                    showManualUnblockGuide();
                }
            } catch (err) {
                console.warn('⚠️ Notification permission request failed or ignored', err);
            }
        }

        async function registerServiceWorker() {
            try {
                await navigator.serviceWorker.register('<?= base_url("sw.js") ?>');
                console.log('Service Worker Registered');
                
                await navigator.serviceWorker.ready;
                
                const btn = document.getElementById('btnEnableNotif');
                if(btn) btn.classList.add('hidden');
                
                registerFCM();
            } catch (error) {
                console.error('Service Worker Error', error);
                if (window.isResubscribing) {
                    alert("Gagal mengaktifkan notifikasi: " + error.message);
                }
            }
        }

        // Helper to convert ArrayBuffer to Base64 (URL Safe)
        function arrayBufferToBase64(buffer) {
            var binary = '';
            var bytes = new Uint8Array(buffer);
            var len = bytes.byteLength;
            for (var i = 0; i < len; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return window.btoa(binary)
                .replace(/\+/g, '-')
                .replace(/\//g, '_')
                .replace(/=+$/, '');
        }


        // Initialize Notifications
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= base_url("sw.js") ?>').then(() => {
                return navigator.serviceWorker.ready;
            }).then(async (reg) => {
               try {
                   reg.update();

                   if (Notification.permission === 'granted') {
                       console.log('✅ Permission granted. Syncing token...');
                       // Auto-register without bothering the user
                       registerFCM(true);
                   } else if (Notification.permission === 'default') {
                       console.log('⏳ Permission is default. Showing Soft Ask...');
                       
                       const btn = document.getElementById('btnEnableNotif');
                       if (btn) btn.classList.remove('hidden');

                       // SOFT ASK STRATEGY
                       // Don't trigger browser prompt yet. Ask politely first.
                       // If they say NO/LATER, we do nothing (Status remains 'default', not 'denied').
                       Swal.fire({
                           title: 'Aktifkan Notifikasi?',
                           text: 'Agar tidak ketinggalan pesan baru, izinkan kami mengirim notifikasi ke perangkat ini.',
                           icon: 'question',
                           showCancelButton: true,
                           confirmButtonText: 'Ya, Aktifkan',
                           cancelButtonText: 'Nanti Saja',
                           confirmButtonColor: '#4f46e5',
                           cancelButtonColor: '#9ca3af',
                           reverseButtons: true,
                           allowOutsideClick: false // Force choice
                       }).then((result) => {
                           if (result.isConfirmed) {
                               // Only trigger actual browser prompt if they said YES
                               askPermission(true);
                           }
                           // If Cancel/Nanti: We do nothing. 
                           // Browser permission stays 'default'. 
                           // Next visit, we can ask again!
                       });
                   } else {
                       console.log('🚫 Permission is denied.');
                       // Show bell so they can change their mind
                       const btn = document.getElementById('btnEnableNotif');
                       if (btn) btn.classList.remove('hidden');
                   }
               } catch (e) {
                   console.error("FCM Check failed", e);
               }
            });
        }
        
        function showManualUnblockGuide() {
            Swal.fire({
                title: 'Akses Notifikasi Diblokir',
                html: `
                    <div class="text-left text-sm space-y-2">
                        <p>Anda sebelumnya memilih "Block". Browser mencegah kami meminta izin lagi secara otomatis.</p>
                        <p class="font-bold mt-2">Cara Mengaktifkan Kembali:</p>
                        <ol class="list-decimal pl-5 space-y-1">
                            <li>Klik ikon <i class="fas fa-lock"></i> <strong>Gembok</strong> / <strong>Pengaturan</strong> di samping alamat website (URL) di atas.</li>
                            <li>Cari menu <strong>Notifications</strong> atau <strong>Izin Situs</strong>.</li>
                            <li>Ubah dari <strong>Block</strong> menjadi <strong>Allow / Izinkan</strong>.</li>
                            <li>Muat ulang (Refresh) halaman ini.</li>
                        </ol>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Saya paham',
                confirmButtonColor: '#4f46e5'
            });
        }
    </script>
</body>
</html>
