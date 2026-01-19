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
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.5); border-radius: 20px; }

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
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 flex flex-col h-[100dvh] overflow-hidden">

    <!-- Header Mobile (Visible only on mobile when on user list) -->
    <div id="mobileHeader" class="bg-indigo-600 text-white p-4 shadow-md md:hidden flex justify-between items-center">
        <a href="<?= base_url('/') ?>" class="text-white"><i class="fas fa-arrow-left"></i></a>
        <h1 class="font-bold text-lg">Pesan</h1>
        <div class="w-6"></div>
    </div>

    <div class="flex flex-1 h-full overflow-hidden max-w-7xl mx-auto w-full md:p-4 gap-4 relative">
        
        <!-- Sidebar (User List) -->
        <div id="sidebar" class="w-full md:w-1/3 lg:w-1/4 bg-white dark:bg-gray-800 md:rounded-2xl shadow-lg flex flex-col h-full z-10 transition-transform duration-300 absolute md:relative top-0 left-0">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h2 class="font-bold text-xl text-indigo-600 dark:text-indigo-400">Pesan</h2>
                <div id="connectionStatus" class="w-3 h-3 rounded-full bg-green-500" title="Terhubung"></div>
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
                        <button onclick="toggleSidebar()" class="md:hidden text-gray-500 hover:text-indigo-600"><i class="fas fa-arrow-left text-lg"></i></button>
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
                <div id="messagesContainer" class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4 min-h-0">
                    <!-- Messages will be rendered here -->
                </div>

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

        // ------------------ Global Functions ------------------

        async function fetchUsers() {
            try {
                const res = await fetch(`${baseUrl}/chat/users`);
                const users = await res.json();
                allUsers = users; // Store globally
                renderUserList(users);
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
            
            // Search Filter
            const searchInput = document.getElementById('searchUser');
            const search = searchInput ? searchInput.value.toLowerCase() : '';
            
            const filtered = users.filter(u => u.name.toLowerCase().includes(search));

            filtered.forEach(user => {
                const initial = user.name.charAt(0).toUpperCase();
                const isActive = user.id_code === activeUserId;
                const unreadBadge = user.unread_count > 0 
                    ? `<div class="w-5 h-5 bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold">${user.unread_count}</div>` 
                    : '';

                const div = document.createElement('div');
                div.className = `p-3 rounded-xl cursor-pointer flex items-center gap-3 transition-colors ${isActive ? 'bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'}`;
                div.onclick = () => selectUser(user);
                div.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-800 text-indigo-600 dark:text-indigo-300 flex items-center justify-center font-bold relative">
                        ${initial}
                        <!-- Online Status Indicator (Mock) -->
                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white dark:border-gray-800 rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-0.5">
                            <h4 class="font-semibold text-sm truncate text-gray-800 dark:text-gray-100">${user.name}</h4>
                            <span class="text-[10px] text-gray-400">...</span>
                        </div>
                        <p class="text-xs text-gray-500 truncate">Klik untuk chat</p>
                    </div>
                    ${unreadBadge}
                `;
                userListEl.appendChild(div);
            });
        }

        async function selectUser(user) {
            activeUserId = user.id_code;
            
            // UI Updates
            document.getElementById('receiverId').value = activeUserId;
            document.getElementById('chatName').innerText = user.name;
            document.getElementById('chatAvatar').innerText = user.name.charAt(0).toUpperCase();
            
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

            // 2. Code Block (```code```)
            formatted = formatted.replace(/```(.*?)```/gs, '<code class="bg-gray-200 dark:bg-gray-800 p-1 rounded font-mono text-xs">$1</code>');

            // 3. Bold (*text*)
            formatted = formatted.replace(/\*(.*?)\*/g, '<strong>$1</strong>');

            // 4. Italic (_text_)
            formatted = formatted.replace(/_(.*?)_/g, '<em>$1</em>');

            // 5. Strikethrough (~text~)
            formatted = formatted.replace(/~(.*?)~/g, '<del>$1</del>');

            // 6. Newlines to <br>
            formatted = formatted.replace(/\n/g, '<br>');

            // 7. Jumbo Emoji Check (post-formatting check is risky if formatting added HTML, 
            // but we can check the original text or a stripped version)
            // Better to check original text.
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

        function renderMessage(msg, scroll = true) {
            const isMe = msg.sender_id === currentUserId;
            const messagesContainer = document.getElementById('messagesContainer');
            
            const div = document.createElement('div');
            div.id = `msg-${msg.id}`;
            // PERFORMANCE FIX: Only animate if it's a NEW message (not history load)
            const animationClass = scroll ? 'animate__animated animate__fadeInUp animate__faster' : '';
            div.className = `flex ${isMe ? 'justify-end' : 'justify-start'} mb-4 ${animationClass} group items-end gap-2`;
            
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

            const actionsHtml = `
                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex flex-col gap-1 ${isMe ? 'order-first' : ''}">
                    <button onclick="startReply(${msg.id}, '${displayName}', '${msg.message.replace(/'/g, "\\'").replace(/\n/g, "\\n")}')" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400" title="Balas">
                        <i class="fas fa-reply text-[10px]"></i>
                    </button>
                    <button onclick="openForwardModal('${msg.message.replace(/'/g, "\\'").replace(/\n/g, "\\n")}')" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-400" title="Teruskan">
                        <i class="fas fa-share text-[10px]"></i>
                    </button>
                </div>
            `;

            div.innerHTML = `
                ${isMe ? actionsHtml : ''}
                <div class="max-w-[75%] md:max-w-[60%] flex flex-col ${isMe ? 'items-end' : 'items-start'}">
                    <div class="${isJumbo ? 'px-0 py-0' : 'px-4 py-2'} rounded-2xl text-sm shadow-sm relative ${bubbleClass}">
                        ${(!isMe && activeUserId === 'GROUP_ALL' && !isJumbo) ? `<p class="text-[11px] font-bold ${nameColor} mb-0.5 leading-tight">${displayName}</p>` : ''}
                        ${(!isMe && activeUserId === 'GROUP_ALL' && isJumbo) ? `<p class="text-[10px] font-bold ${nameColor} mb-0 leading-tight bg-white/80 dark:bg-slate-800/80 px-1 rounded absolute -top-4 left-0 w-max shadow-sm">${displayName}</p>` : ''}
                        ${quoteHtml}
                        ${formatMessage(msg.message)}
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 px-1 flex gap-1 items-center">
                        ${time}
                        ${isMe ? (msg.is_read == 1 ? '<i class="fas fa-check-double text-blue-400"></i>' : '<i class="fas fa-check"></i>') : ''}
                    </span>
                </div>
                ${!isMe ? actionsHtml : ''}
            `;
            
            messagesContainer.appendChild(div);
            if(scroll) scrollToBottom();
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
                        const currentCount = messagesContainer.childElementCount;
                        if(data.messages.length !== currentCount) { 
                             const oldScroll = messagesContainer.scrollTop;
                             const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop === messagesContainer.clientHeight;
                             
                             messagesContainer.innerHTML = '';
                             data.messages.forEach(m => renderMessage(m, false));
                             
                             if(wasAtBottom) scrollToBottom();
                             else messagesContainer.scrollTop = oldScroll;
                        }
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
            if(!confirm(`Teruskan pesan ke ${user.name}?`)) return;
            try {
                const formData = new FormData();
                formData.append('receiver_id', user.id_code);
                formData.append('message', `${messageToForward}`);
                await fetch(`${baseUrl}/chat/send`, {
                    method: 'POST', body: formData
                });
                alert('Pesan diteruskan!');
                closeForwardModal();
                if(activeUserId === user.id_code) { loadMessages(); scrollToBottom(); }
            } catch(e) { alert('Gagal meneruskan pesan.'); }
        }

        function toggleSidebar() {
             const sidebar = document.getElementById('sidebar');
             const chatArea = document.getElementById('chatArea');
            if(isMobile) {
                activeUserId = null;
                updateMobileView();
            }
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
            await fetchUsers();
            
            const urlParams = new URLSearchParams(window.location.search);
            const targetUserId = urlParams.get('user_id');
            
            if (targetUserId) {
                const targetUser = allUsers.find(u => u.id_code === targetUserId);
                if (targetUser) {
                    selectUser(targetUser);
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

            document.getElementById('messageForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const input = document.getElementById('messageInput');
                const message = input.value.trim();
                
                if(!message || !activeUserId) return;

                // Optimistic Rendering
                renderMessage({
                    sender_id: currentUserId,
                    message: message,
                    created_at: new Date().toISOString(),
                    is_pending: true 
                }, true);
                
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

                    const res = await fetch(`${baseUrl}/chat/send`, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    
                } catch(err) {
                    console.error("Send failed", err);
                    alert("Gagal mengirim pesan");
                }
            });
        });

        // --- PUSH NOTIFICATION LOGIC ---
        const vapidPublicKey = '<?= $vapid_public_key ?>';

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        async function askPermission() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                alert('Browser tidak mendukung Push Notification.');
                return;
            }
            
            if(!vapidPublicKey) {
                alert("VAPID Key belum dikonfigurasi di server.");
                return;
            }

            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                registerServiceWorker();
            } else {
                alert('Notifikasi diblokir. Silakan izinkan di pengaturan browser.');
            }
        }

        async function registerServiceWorker() {
            try {
                const registration = await navigator.serviceWorker.register('<?= base_url("sw.js") ?>');
                console.log('Service Worker Registered');
                
                const subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                });
                
                await sendSubscriptionToServer(subscription);
                
                document.getElementById('btnEnableNotif').classList.add('hidden');
                alert("Notifikasi telah diaktifkan!");
                
            } catch (error) {
                console.error('Service Worker Error', error);
                alert("Gagal mengaktifkan notifikasi: " + error.message);
            }
        }

        async function sendSubscriptionToServer(subscription) {
            await fetch('<?= base_url("push/subscribe") ?>', {
                method: 'POST',
                body: JSON.stringify(subscription),
                headers: {
                    'Content-Type': 'application/json'
                }
            });
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

        async function forceResetSubscription() {
            if (!confirm('Ini akan mereset koneksi notifikasi Anda. Lanjutkan?')) return;
            
            if ('serviceWorker' in navigator) {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                if (sub) {
                    await sub.unsubscribe();
                    console.log('Old subscription removed.');
                }
                // Unregister SW to be safe
                // await reg.unregister(); 
                
                // Re-register
                await registerServiceWorker();
                alert('Notifikasi berhasil di-reset! Silakan coba test kirim pesan.');
            } else {
                alert('Browser tidak mendukung Service Worker.');
            }
        }

        // Check if already subscribed
        if ('serviceWorker' in navigator && 'PushManager' in window && vapidPublicKey) {
            navigator.serviceWorker.ready.then(async (reg) => {
               try {
                   const sub = await reg.pushManager.getSubscription();
                   if (!sub) {
                       // Case 1: No subscription. Auto-ask permission on load
                       askPermission();
                   } else {
                       // Case 2: Subscription exists. Check against current Key.
                       const existingKeyBuffer = sub.options.applicationServerKey;
                       if (existingKeyBuffer) {
                           const existingKey = arrayBufferToBase64(existingKeyBuffer);
                           
                           // Compare existing key with server key (remove padding '=' for safety comparison)
                           const currentKeyClean = vapidPublicKey.replace(/=+$/, '');
                           
                           if (existingKey !== currentKeyClean) {
                               console.log("⚠️ Key Mismatch detected! Rotating subscription...");
                               
                               // Unsubscribe old
                               await sub.unsubscribe();
                               // Subscribe new
                               registerServiceWorker();
                               return; 
                           }
                       }
                       
                       // Keys match, just ensure server has it
                       await sendSubscriptionToServer(sub);
                   }
               } catch (e) {
                   console.error("Subscription check failed", e);
               }
            });
        }
    </script>
</body>
</html>
