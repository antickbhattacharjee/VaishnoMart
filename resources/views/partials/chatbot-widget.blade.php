<!-- VaishnoMart RAG AI Chatbot Widget -->
<div id="vaishnoChatContainer">
    <!-- Floating Trigger Launcher Button -->
    <button id="btnOpenChatbot" class="btn rounded-pill shadow-lg d-flex align-items-center gap-2 px-3 py-2" 
            style="position: fixed; bottom: 25px; right: 25px; z-index: 1060; background: linear-gradient(135deg, #059669 0%, #064e3b 100%); color: #ffffff; border: 2px solid rgba(255,255,255,0.4); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 25px rgba(5, 150, 105, 0.35);">
        <span class="position-relative d-inline-block">
            <i class="bi bi-robot fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-warning border border-light rounded-circle" style="width: 10px; height: 10px;"></span>
        </span>
        <span class="fw-bold small d-none d-sm-inline">VaishnoBot AI Help</span>
        <span class="badge rounded-pill bg-warning text-dark small fw-bold px-2 py-0">RAG</span>
    </button>

    <!-- Chat Modal Window -->
    <div id="vaishnoChatWindow" class="card border-0 shadow-2xl rounded-4 overflow-hidden d-none" 
         style="position: fixed; bottom: 85px; right: 25px; width: 380px; max-width: calc(100vw - 40px); height: 560px; max-height: calc(100vh - 120px); z-index: 1065; box-shadow: 0 20px 40px -5px rgba(15, 23, 42, 0.25); border: 1px solid rgba(226, 232, 240, 0.8) !important; background: #ffffff;">
        
        <!-- Header -->
        <div class="p-3 text-white d-flex align-items-center justify-content-between" 
             style="background: linear-gradient(135deg, #064e3b 0%, #059669 100%); border-bottom: 1px solid rgba(255,255,255,0.15);">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-white bg-opacity-20 p-2 d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px;">
                    <i class="bi bi-robot fs-5"></i>
                </div>
                <div>
                    <div class="fw-bold d-flex align-items-center gap-1 lh-1">
                        VaishnoBot
                        <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.65rem;">RAG AI</span>
                    </div>
                    <small class="text-light opacity-75 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                        <span class="spinner-grow spinner-grow-sm text-success bg-white rounded-circle p-0" style="width: 6px; height: 6px;"></span>
                        Online • 24/7 Smart Support
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" id="btnResetChat" class="btn btn-sm text-white opacity-75 hover-opacity-100 p-1" title="Clear Chat History">
                    <i class="bi bi-arrow-clockwise fs-5"></i>
                </button>
                <button type="button" id="btnCloseChatbot" class="btn btn-sm text-white opacity-75 hover-opacity-100 p-1" title="Close">
                    <i class="bi bi-x-lg fs-5"></i>
                </button>
            </div>
        </div>

        <!-- Quick Help Chips Bar -->
        <div class="px-3 py-2 bg-light border-bottom overflow-x-auto d-flex gap-1 text-nowrap" style="scrollbar-width: none;">
            <button class="btn btn-xs btn-white border rounded-pill shadow-xs quick-chip" data-query="Track my latest order">
                <i class="bi bi-geo-alt text-primary me-1"></i>Track Order
            </button>
            <button class="btn btn-xs btn-white border rounded-pill shadow-xs quick-chip" data-query="Do you have 20L water cans?">
                <i class="bi bi-droplet-fill text-info me-1"></i>Water Cans
            </button>
            <button class="btn btn-xs btn-white border rounded-pill shadow-xs quick-chip" data-query="How does two-step OTP verification work?">
                <i class="bi bi-shield-check text-success me-1"></i>OTP Security
            </button>
            <button class="btn btn-xs btn-white border rounded-pill shadow-xs quick-chip" data-query="What is the refund and cancellation policy?">
                <i class="bi bi-cash-stack text-warning me-1"></i>Refund Policy
            </button>
            <button class="btn btn-xs btn-white border rounded-pill shadow-xs quick-chip" data-query="What are delivery fees and times?">
                <i class="bi bi-clock text-secondary me-1"></i>Delivery Times
            </button>
        </div>

        <!-- Messages Container -->
        <div id="vaishnoChatMessages" class="p-3 overflow-y-auto flex-grow-1" style="background-color: #f8fafc; font-size: 0.88rem;">
            <!-- Welcome Bot Message -->
            <div class="d-flex gap-2 mb-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px;">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="p-3 rounded-4 bg-white border shadow-xs" style="max-width: 85%;">
                    <div class="fw-bold text-dark mb-1">Namaste! Welcome to VaishnoMart.</div>
                    <p class="mb-2 text-secondary">
                        I am your <strong>Retrieval-Augmented AI Assistant (RAG)</strong>. I can track your orders in real-time, search local store stocks, and explain delivery or OTP verification procedures.
                    </p>
                    <div class="small text-muted fst-italic">What can I assist you with today?</div>
                </div>
            </div>
        </div>

        <!-- Typing Indicator Animation -->
        <div id="vaishnoTypingIndicator" class="px-3 py-2 bg-light border-top d-none align-items-center gap-2">
            <div class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status"></div>
            <small class="text-muted fst-italic">VaishnoBot is searching knowledge base & live catalogue...</small>
        </div>

        <!-- Input Box -->
        <div class="p-2 border-top bg-white">
            <form id="vaishnoChatForm" class="d-flex align-items-center gap-2">
                <input type="text" id="vaishnoChatInput" class="form-control form-control-sm rounded-pill px-3 py-2 border" 
                       placeholder="Ask about orders, products, OTP, delivery..." autocomplete="off" required>
                <button type="submit" class="btn btn-sm btn-vm-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                        style="width: 36px; height: 36px;" title="Send">
                    <i class="bi bi-send-fill" style="transform: rotate(45deg); margin-left: -2px;"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
    .btn-xs { font-size: 0.75rem; padding: 0.25rem 0.6rem; }
    .quick-chip { transition: all 0.2s ease; }
    .quick-chip:hover { background-color: #ecfdf5 !important; border-color: #059669 !important; color: #064e3b !important; }
    #vaishnoChatMessages pre, #vaishnoChatMessages code { background: #e2e8f0; padding: 2px 5px; border-radius: 4px; font-size: 0.8rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnOpen = document.getElementById('btnOpenChatbot');
    const btnClose = document.getElementById('btnCloseChatbot');
    const btnReset = document.getElementById('btnResetChat');
    const chatWindow = document.getElementById('vaishnoChatWindow');
    const chatMessages = document.getElementById('vaishnoChatMessages');
    const chatForm = document.getElementById('vaishnoChatForm');
    const chatInput = document.getElementById('vaishnoChatInput');
    const typingIndicator = document.getElementById('vaishnoTypingIndicator');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Toggle Chat Window
    btnOpen.addEventListener('click', function () {
        chatWindow.classList.toggle('d-none');
        if (!chatWindow.classList.contains('d-none')) {
            chatInput.focus();
            scrollToBottom();
            loadHistoryIfNeeded();
        }
    });

    btnClose.addEventListener('click', function () {
        chatWindow.classList.add('d-none');
    });

    // Reset Chat
    btnReset.addEventListener('click', function () {
        if (confirm('Clear current chat history?')) {
            fetch("{{ route('chatbot.clear') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }).then(() => {
                chatMessages.innerHTML = `
                    <div class="d-flex gap-2 mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px;">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="p-3 rounded-4 bg-white border shadow-xs" style="max-width: 85%;">
                            <div class="fw-bold text-dark mb-1">Chat history cleared.</div>
                            <p class="mb-0 text-secondary">How can I assist you right now?</p>
                        </div>
                    </div>
                `;
            });
        }
    });

    // Quick Chips
    document.querySelectorAll('.quick-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            const query = this.getAttribute('data-query');
            chatInput.value = query;
            sendMessage(query);
        });
    });

    // Handle Form Submit
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;
        chatInput.value = '';
        sendMessage(text);
    });

    function sendMessage(text) {
        // Append user bubble
        appendUserMessage(text);
        scrollToBottom();

        // Show typing indicator
        typingIndicator.classList.remove('d-none');
        typingIndicator.classList.add('d-flex');
        scrollToBottom();

        fetch("{{ route('chatbot.message') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message: text })
        })
        .then(res => res.json())
        .then(data => {
            typingIndicator.classList.add('d-none');
            typingIndicator.classList.remove('d-flex');

            if (data.success) {
                appendBotMessage(data.reply, data.actions, data.sources, data.confidence);
            } else {
                appendBotMessage('Sorry, I encountered an issue while processing your question. Please try again.', [], []);
            }
            scrollToBottom();
        })
        .catch(err => {
            typingIndicator.classList.add('d-none');
            typingIndicator.classList.remove('d-flex');
            appendBotMessage('Network connection error. Please verify your connection.', [], []);
            scrollToBottom();
        });
    }

    function appendUserMessage(text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'd-flex justify-content-end mb-3';
        msgDiv.innerHTML = `
            <div class="p-3 rounded-4 text-white shadow-xs" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); max-width: 85%;">
                <div>${escapeHtml(text)}</div>
            </div>
        `;
        chatMessages.appendChild(msgDiv);
    }

    function appendBotMessage(markdownText, actions = [], sources = [], confidence = null) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'd-flex gap-2 mb-3';

        let html = parseSimpleMarkdown(markdownText);

        // Sources Attribution
        let sourcesHtml = '';
        if (sources && sources.length > 0) {
            sourcesHtml = `<div class="mt-2 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
                <i class="bi bi-database-check text-success me-1"></i> RAG Grounding: ${sources.join(', ')}
            </div>`;
        }

        // Action Buttons
        let actionsHtml = '';
        if (actions && actions.length > 0) {
            actionsHtml = '<div class="mt-3 d-flex flex-wrap gap-1">';
            actions.forEach(act => {
                actionsHtml += `
                    <a href="${act.url}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 text-decoration-none" style="font-size: 0.78rem;">
                        <i class="bi ${act.icon || 'bi-arrow-right'} me-1"></i> ${escapeHtml(act.label)}
                    </a>
                `;
            });
            actionsHtml += '</div>';
        }

        msgDiv.innerHTML = `
            <div class="rounded-circle bg-success bg-opacity-10 text-success p-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px;">
                <i class="bi bi-robot"></i>
            </div>
            <div class="p-3 rounded-4 bg-white border shadow-xs" style="max-width: 85%;">
                <div>${html}</div>
                ${actionsHtml}
                ${sourcesHtml}
            </div>
        `;
        chatMessages.appendChild(msgDiv);
    }

    function parseSimpleMarkdown(text) {
        let t = text;
        // Headers
        t = t.replace(/### (.*?)\n/g, '<h6 class="fw-bold mb-2 text-dark">$1</h6>');
        t = t.replace(/## (.*?)\n/g, '<h5 class="fw-bold mb-2 text-dark">$1</h5>');
        // Bold & Italic
        t = t.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        t = t.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Blockquotes
        t = t.replace(/> (.*?)\n/g, '<div class="p-2 my-2 bg-light border-start border-3 border-success small rounded-end">$1</div>');
        // Lists
        t = t.replace(/^\* (.*?)$/gm, '<li class="mb-1">$1</li>');
        t = t.replace(/(<li.*<\/li>)/s, '<ul class="ps-3 mb-2">$1</ul>');
        // Linebreaks
        t = t.replace(/\n\n/g, '<div class="my-2"></div>');
        t = t.replace(/\n/g, '<br>');
        return t;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    let historyLoaded = false;
    function loadHistoryIfNeeded() {
        if (historyLoaded) return;
        historyLoaded = true;

        fetch("{{ route('chatbot.history') }}")
            .then(res => res.json())
            .then(data => {
                if (data.success && data.history && data.history.length > 0) {
                    data.history.forEach(item => {
                        if (item.sender === 'user') {
                            appendUserMessage(item.text);
                        } else {
                            appendBotMessage(item.text, item.actions || [], item.sources || []);
                        }
                    });
                    scrollToBottom();
                }
            }).catch(() => {});
    }
});
</script>
