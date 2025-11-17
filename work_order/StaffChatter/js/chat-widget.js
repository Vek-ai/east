class ChatWidget {
    constructor(config) {
        this.currentUserId = config.currentUserId;
        this.currentUserName = config.currentUserName;
        this.contacts = [];
        this.activeChats = new Map();
        this.unreadCounts = new Map();
        this.pollingInterval = 2000;
        this.notificationSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YQoGAAD//////////////////////////////////////////////////////////////////w==');
        
        this.init();
    }

    init() {
        this.createWidgetHTML();
        this.attachEventListeners();
        this.loadContacts();
        this.startGlobalPolling();
        this.requestNotificationPermission();
    }

    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    createWidgetHTML() {
        const html = `
            <div class="chat-widget-container" id="chatWidget">
                <button class="chat-widget-button" id="chatToggle">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                    </svg>
                    <span class="chat-badge" id="chatBadge">0</span>
                </button>

                <div class="chat-list-panel" id="chatListPanel">
                    <div class="chat-list-header">Messages</div>
                    <div class="chat-list-search">
                        <input type="text" placeholder="Search contacts..." id="chatSearch">
                    </div>
                    <div class="chat-list-contacts" id="chatContacts">
                        <div class="chat-empty-state">Loading contacts...</div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
    }

    attachEventListeners() {
        document.getElementById('chatToggle').addEventListener('click', () => this.toggleChatList());
        document.getElementById('chatSearch').addEventListener('input', (e) => this.searchContacts(e.target.value));
        
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#chatWidget') && !e.target.closest('.chat-window')) {
                this.closeChatList();
            }
        });
    }

    toggleChatList() {
        const panel = document.getElementById('chatListPanel');
        panel.classList.toggle('show');
    }

    closeChatList() {
        document.getElementById('chatListPanel').classList.remove('show');
    }

    async loadContacts() {
        try {
            const response = await fetch('StaffChatter/api/get_contacts.php');
            const data = await response.json();
            
            if (data.success) {
                this.contacts = data.contacts;
                this.renderContacts();
            }
        } catch (error) {
            console.error('Error loading contacts:', error);
        }
    }

    renderContacts(filter = '') {
        const container = document.getElementById('chatContacts');
        const filtered = this.contacts.filter(c => 
            c.name.toLowerCase().includes(filter.toLowerCase())
        );

        if (filtered.length === 0) {
            container.innerHTML = '<div class="chat-empty-state">No contacts found</div>';
            return;
        }

        container.innerHTML = filtered.map(contact => {
            const initials = contact.name.split(' ').map(n => n[0]).join('');
            const unreadCount = this.unreadCounts.get(contact.staff_id) || 0;
            
            return `
                <div class="chat-contact-item" onclick="chatWidget.openChat(${contact.staff_id}, '${contact.name}')">
                    <div class="chat-contact-avatar">
                        ${initials}
                        ${unreadCount > 0 ? '<div class="chat-contact-unread"></div>' : ''}
                    </div>
                    <div class="chat-contact-info">
                        <div class="chat-contact-name">${this.escapeHtml(contact.name)}</div>
                        <div class="chat-contact-preview">${contact.username}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    searchContacts(query) {
        this.renderContacts(query);
    }

    openChat(staffId, staffName) {
        this.closeChatList();
        
        if (this.activeChats.has(staffId)) {
            const existingWindow = document.getElementById(`chatWindow${staffId}`);
            if (existingWindow && !existingWindow.classList.contains('show')) {
                existingWindow.classList.add('show');
            }
            return;
        }

        const chatWindow = this.createChatWindow(staffId, staffName);
        this.activeChats.set(staffId, {
            staffId,
            staffName,
            lastMessageId: 0,
            element: chatWindow,
            messages: []
        });

        this.repositionChatWindows();
        this.loadMessages(staffId);
        this.unreadCounts.set(staffId, 0);
        this.updateBadge();
    }

    repositionChatWindows() {
        const visibleWindows = Array.from(this.activeChats.values())
            .map(chat => chat.element)
            .filter(el => el && el.classList.contains('show'));
        
        visibleWindows.forEach((window, index) => {
            const rightPosition = 370 + (index * 340);
            window.style.right = rightPosition + 'px';
        });
    }

    createChatWindow(staffId, staffName) {
        const existingWindow = document.getElementById(`chatWindow${staffId}`);
        if (existingWindow) {
            existingWindow.classList.add('show');
            return existingWindow;
        }

        const html = `
            <div class="chat-window show" id="chatWindow${staffId}">
                <div class="chat-window-header">
                    <div class="chat-window-title">${this.escapeHtml(staffName)}</div>
                    <div class="chat-window-actions">
                        <button class="chat-action-btn" onclick="chatWidget.minimizeChat(${staffId})">−</button>
                        <button class="chat-action-btn" onclick="chatWidget.closeChat(${staffId})">×</button>
                    </div>
                </div>
                <div class="chat-messages-container" id="chatMessages${staffId}">
                    <div class="chat-empty-state">Loading messages...</div>
                </div>
                <div class="chat-input-container">
                    <div class="chat-file-preview" id="filePreview${staffId}">
                        <span class="chat-file-icon">📎</span>
                        <div class="chat-file-preview-info">
                            <div class="chat-file-preview-name" id="fileName${staffId}"></div>
                            <div class="chat-file-preview-size" id="fileSize${staffId}"></div>
                        </div>
                        <button class="chat-file-preview-remove" onclick="chatWidget.removeFile(${staffId})">×</button>
                    </div>
                    <div class="chat-input-wrapper">
                        <button class="chat-file-upload-btn" onclick="document.getElementById('chatFileInput${staffId}').click()">
                            <svg viewBox="0 0 24 24">
                                <path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/>
                            </svg>
                        </button>
                        <input type="file" id="chatFileInput${staffId}" onchange="chatWidget.selectFile(${staffId}, this)">
                        <textarea class="chat-input-field" id="chatInput${staffId}" placeholder="Type a message..." rows="1"></textarea>
                        <button class="chat-send-btn" id="chatSend${staffId}" onclick="chatWidget.sendMessage(${staffId})">
                            <svg viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        
        const textarea = document.getElementById(`chatInput${staffId}`);
        textarea.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage(staffId);
            }
        });
        
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        return document.getElementById(`chatWindow${staffId}`);
    }

    minimizeChat(staffId) {
        const window = document.getElementById(`chatWindow${staffId}`);
        window.classList.remove('show');
        this.repositionChatWindows();
    }

    closeChat(staffId) {
        const window = document.getElementById(`chatWindow${staffId}`);
        if (window) {
            window.remove();
        }
        this.activeChats.delete(staffId);
        this.repositionChatWindows();
    }

    selectFile(staffId, input) {
        const file = input.files[0];
        if (!file) return;

        const chat = this.activeChats.get(staffId);
        if (!chat) return;

        chat.selectedFile = file;
        
        document.getElementById(`fileName${staffId}`).textContent = file.name;
        document.getElementById(`fileSize${staffId}`).textContent = this.formatFileSize(file.size);
        document.getElementById(`filePreview${staffId}`).classList.add('show');
    }

    removeFile(staffId) {
        const chat = this.activeChats.get(staffId);
        if (chat) {
            chat.selectedFile = null;
            document.getElementById(`chatFileInput${staffId}`).value = '';
            document.getElementById(`filePreview${staffId}`).classList.remove('show');
        }
    }

    async sendMessage(staffId) {
        const chat = this.activeChats.get(staffId);
        if (!chat) return;

        const input = document.getElementById(`chatInput${staffId}`);
        const messageText = input.value.trim();
        const file = chat.selectedFile;

        if (!messageText && !file) return;

        const sendBtn = document.getElementById(`chatSend${staffId}`);
        sendBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('other_staff_id', staffId);
            formData.append('message_text', messageText || '');
            
            if (file) {
                formData.append('file', file);
            }

            const response = await fetch('StaffChatter/api/send_message.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                input.value = '';
                input.style.height = 'auto';
                this.removeFile(staffId);
                await this.loadMessages(staffId);
            } else {
                alert('Error sending message: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Error sending message. Please try again.');
        }

        sendBtn.disabled = false;
    }

    async loadMessages(staffId) {
        const chat = this.activeChats.get(staffId);
        if (!chat) return;

        try {
            const response = await fetch(`StaffChatter/api/get_messages.php?other_staff_id=${staffId}&last_message_id=${chat.lastMessageId}`);
            const data = await response.json();

            if (data.success && data.messages.length > 0) {
                data.messages.forEach(message => {
                    this.displayMessage(staffId, message);
                    if (message.message_id > chat.lastMessageId) {
                        chat.lastMessageId = message.message_id;
                    }
                });
            } else if (chat.lastMessageId === 0) {
                const container = document.getElementById(`chatMessages${staffId}`);
                container.innerHTML = '<div class="chat-empty-state">No messages yet. Start the conversation!</div>';
            }
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    displayMessage(staffId, message) {
        const chat = this.activeChats.get(staffId);
        if (!chat) return;

        const container = document.getElementById(`chatMessages${staffId}`);

        if (document.getElementById(`msg-${message.message_id}`)) {
            return;
        }

        if (container.querySelector('.chat-empty-state')) {
            container.innerHTML = '';
        }

        const isSent = message.sender_id == this.currentUserId;

        const fileHtml = message.file_name ? `
            <div class="chat-message-file" onclick="window.open('StaffChatter/uploads/${message.file_path}', '_blank')">
                <span class="chat-file-icon">${this.getFileIcon(message.file_type)}</span>
                <div class="chat-file-info">
                    <div class="chat-file-name">${this.escapeHtml(message.file_name)}</div>
                    <div class="chat-file-size">${this.formatFileSize(message.file_size)}</div>
                </div>
            </div>
        ` : '';

        const messageHtml = `
            <div class="chat-message ${isSent ? 'sent' : 'received'}" id="msg-${message.message_id}">
                <div class="chat-message-bubble">
                    ${message.message_text ? this.escapeHtml(message.message_text) : ''}
                    ${fileHtml}
                    <span class="chat-message-time">${this.formatTime(message.created_at)}</span>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', messageHtml);
        container.scrollTop = container.scrollHeight;
    }

    async startGlobalPolling() {
        setInterval(async () => {
            await this.checkNewMessages();
            
            for (const [staffId, chat] of this.activeChats) {
                await this.loadMessages(staffId);
            }
        }, this.pollingInterval);
    }

    async checkNewMessages() {
        try {
            const response = await fetch('StaffChatter/api/get_unread_counts.php');
            const data = await response.json();

            if (data.success) {
                data.unread_counts.forEach(item => {
                    const oldCount = this.unreadCounts.get(item.staff_id) || 0;
                    this.unreadCounts.set(item.staff_id, item.unread_count);
                    
                    if (item.unread_count > oldCount && !this.activeChats.has(item.staff_id)) {
                        this.showNotification(item.staff_name, `${item.unread_count} new message(s)`);
                        this.playNotificationSound();
                    }
                });
                
                this.updateBadge();
                this.renderContacts(document.getElementById('chatSearch').value);
            }
        } catch (error) {
            console.error('Error checking new messages:', error);
        }
    }

    updateBadge() {
        const total = Array.from(this.unreadCounts.values()).reduce((a, b) => a + b, 0);
        const badge = document.getElementById('chatBadge');
        
        if (total > 0) {
            badge.textContent = total > 99 ? '99+' : total;
            badge.classList.add('show');
        } else {
            badge.classList.remove('show');
        }
    }

    showNotification(title, message) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                badge: '/favicon.ico'
            });
        }
    }

    playNotificationSound() {
        this.notificationSound.play().catch(e => console.log('Sound play failed:', e));
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        
        if (diff < 86400000) {
            return `${hours}:${minutes}`;
        } else if (diff < 172800000) {
            return `Yesterday ${hours}:${minutes}`;
        } else {
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            return `${day}/${month} ${hours}:${minutes}`;
        }
    }

    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    getFileIcon(fileType) {
        if (!fileType) return '📄';
        if (fileType.startsWith('image/')) return '🖼️';
        if (fileType.startsWith('video/')) return '🎥';
        if (fileType.startsWith('audio/')) return '🎵';
        if (fileType.includes('pdf')) return '📕';
        if (fileType.includes('word')) return '📘';
        if (fileType.includes('excel') || fileType.includes('spreadsheet')) return '📗';
        if (fileType.includes('zip') || fileType.includes('rar')) return '📦';
        return '📄';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
