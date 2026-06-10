/**
 * AI Assistant & Summarizer Frontend Module
 */

(function () {
    const STATE = {
        isOpen: false,
        activeTab: 'chat', // 'chat', 'summary', 'page'
        chatHistory: [], // {role: 'user'|'model', text: '...'}
        isLoading: false
    };

    // Initialize Assistant
    function init() {
        renderUI();
        bindEvents();
        
        // Add welcome message
        addMessage('model', 'Halo! Saya adalah **AI Assistant**. Bagaimana saya bisa membantu Anda mengelola data HR, absensi, atau payroll hari ini?');
        
        // Expose update function globally
        window.updateAiAssistantContext = updatePageAnalysisContextLabel;
    }

    // Render HTML components into DOM
    function renderUI() {
        // FAB Button
        const fab = document.createElement('div');
        fab.id = 'aiFab';
        fab.className = 'ai-fab';
        fab.innerHTML = '<i class="fas fa-question"></i>';
        document.body.appendChild(fab);

        // Drawer Panel
        const drawer = document.createElement('div');
        drawer.id = 'aiDrawer';
        drawer.className = 'ai-drawer';
        drawer.innerHTML = `
            <!-- Header -->
            <div class="ai-header">
                <div class="ai-header-title">
                    <div>
                        <h3>AI Assistant</h3>
                    </div>
                </div>
                <button id="aiCloseBtn" class="ai-close-btn"><i class="fas fa-times"></i></button>
            </div>

            <!-- Navigation Tabs -->
            <div class="ai-tabs">
                <button class="ai-tab-btn active" data-tab="chat">
                    <i class="fas fa-comments"></i> Chat Assistant
                </button>
                <button class="ai-tab-btn" data-tab="summary">
                    <i class="fas fa-chart-pie"></i> Ringkas Dashboard
                </button>
                <button class="ai-tab-btn" data-tab="page">
                    <i class="fas fa-file-invoice-dollar"></i> Analisis Halaman
                </button>
            </div>

            <!-- Body Contents -->
            <div class="ai-body">
                <!-- Panel 1: Chat -->
                <div id="aiPanelChat" class="ai-panel active">
                    <div id="aiChatMessages" class="ai-chat-messages">
                        <!-- Messages go here -->
                    </div>
                    
                    <!-- Quick action suggestions -->
                    <div class="ai-chat-quick-actions">
                        <button class="ai-quick-btn" data-query="Berapa total karyawan terdaftar saat ini?">Total Karyawan</button>
                        <button class="ai-quick-btn" data-query="Klien apa saja yang terdaftar di sistem?">Daftar Klien</button>
                    </div>

                    <!-- Input Bar -->
                    <div class="ai-chat-input-bar">
                        <input type="text" id="aiChatInput" class="ai-chat-input" placeholder="Tanyakan tentang payroll, denda, UMK..." autocomplete="off">
                        <button id="aiSendBtn" class="ai-send-btn" disabled><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>

                <!-- Panel 2: Summary -->
                <div id="aiPanelSummary" class="ai-panel">
                    <div class="ai-report-panel">
                        <div id="aiSummaryPlaceholder" class="ai-report-empty">
                            <i class="fas fa-chart-line"></i>
                            <h4>Rangkuman Operasional Eksekutif</h4>
                            <p>Dapatkan ringkasan instan tentang performa mitra klien, karyawan aktif, dan log sistem terbaru hari ini.</p>
                            <button id="aiBtnGenerateSummary" class="ai-btn-primary ai-btn-sm">
                                Buat Ringkasan AI
                            </button>
                        </div>
                        <div id="aiSummaryResult" class="ai-report-content" style="display:none;"></div>
                    </div>
                </div>

                <!-- Panel 3: Page Analysis -->
                <div id="aiPanelPage" class="ai-panel">
                    <div class="ai-report-panel">
                        <div id="aiPagePlaceholder" class="ai-report-empty">
                            <h4>Analisis Laporan Payroll Klien</h4>
                            <p>AI akan otomatis membaca data lembur, absensi, denda, dan total pembayaran payroll klien yang sedang dibuka.</p>
                            <button id="aiBtnAnalyzePage" class="ai-btn-primary ai-btn-sm">
                                Analisis Laporan Klien
                            </button>
                        </div>
                        <div id="aiPageResult" class="ai-report-content" style="display:none;"></div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(drawer);
    }

    // Bind Event Listeners
    function bindEvents() {
        const fab = document.getElementById('aiFab');
        const closeBtn = document.getElementById('aiCloseBtn');
        const chatInput = document.getElementById('aiChatInput');
        const sendBtn = document.getElementById('aiSendBtn');
        const tabs = document.querySelectorAll('.ai-tab-btn');
        const quickBtns = document.querySelectorAll('.ai-quick-btn');
        const btnGenSummary = document.getElementById('aiBtnGenerateSummary');
        const btnAnalyzePage = document.getElementById('aiBtnAnalyzePage');

        fab.addEventListener('click', () => toggleDrawer());
        closeBtn.addEventListener('click', () => toggleDrawer(false));

        // Input keypress & button activation
        chatInput.addEventListener('input', function () {
            sendBtn.disabled = !this.value.trim();
        });

        chatInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && this.value.trim() && !STATE.isLoading) {
                e.preventDefault();
                submitUserMessage(this.value.trim());
                this.value = '';
                sendBtn.disabled = true;
            }
        });

        sendBtn.addEventListener('click', function () {
            if (chatInput.value.trim() && !STATE.isLoading) {
                submitUserMessage(chatInput.value.trim());
                chatInput.value = '';
                this.disabled = true;
            }
        });

        // Tab switches
        tabs.forEach(btn => {
            btn.addEventListener('click', function () {
                switchTab(this.dataset.tab);
            });
        });

        // Quick query action buttons
        quickBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                if (!STATE.isLoading) {
                    submitUserMessage(this.dataset.query);
                }
            });
        });

        // Report buttons
        btnGenSummary.addEventListener('click', generateExecutiveSummary);
        btnAnalyzePage.addEventListener('click', analyzeCurrentPage);
    }

    // Toggle drawer open state
    function toggleDrawer(forceState = null) {
        const drawer = document.getElementById('aiDrawer');
        const fab = document.getElementById('aiFab');
        
        STATE.isOpen = (typeof forceState === 'boolean') ? forceState : !STATE.isOpen;

        if (STATE.isOpen) {
            drawer.classList.add('open');
            fab.classList.add('open');
            fab.innerHTML = '<i class="fas fa-times"></i>';
            document.getElementById('aiChatInput').focus();
            
            // Check context dynamically when drawer opens
            if (STATE.activeTab === 'page') {
                updatePageAnalysisContextLabel();
            }
        } else {
            drawer.classList.remove('open');
            fab.classList.remove('open');
            fab.innerHTML = '<i class="fas fa-question"></i>';
        }
    }

    // Switch active panel tab
    function switchTab(tabId) {
        STATE.activeTab = tabId;
        
        // Buttons
        document.querySelectorAll('.ai-tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tabId);
        });

        // Panels
        document.getElementById('aiPanelChat').classList.toggle('active', tabId === 'chat');
        document.getElementById('aiPanelSummary').classList.toggle('active', tabId === 'summary');
        document.getElementById('aiPanelPage').classList.toggle('active', tabId === 'page');

        // Auto focus if chat
        if (tabId === 'chat') {
            document.getElementById('aiChatInput').focus();
        } else if (tabId === 'page') {
            // Check view context
            updatePageAnalysisContextLabel();
        }
    }

    // Update instruction text dynamically depending on active screen
    function updatePageAnalysisContextLabel() {
        const activeSection = document.querySelector('.view-section.active');
        const placeholder = document.getElementById('aiPagePlaceholder');
        if (!placeholder) return;

        const title = placeholder.querySelector('h4');
        const desc = placeholder.querySelector('p');
        const btn = document.getElementById('aiBtnAnalyzePage');

        if (window.selectedClientId) {
            const clientName = document.getElementById('clientWorkspaceTitle')?.innerText || 'Klien Aktif';
            title.innerText = `Analisis Laporan Payroll ${clientName}`;
            desc.innerText = `AI akan mengekstrak total transfer, rata-rata gaji pokok, denda absensi, lembur, dan BPJS klien ${clientName} untuk ditinjau.`;
            btn.style.display = 'inline-flex';
        } else {
            title.innerText = "Workspace Klien Belum Dibuka";
            desc.innerText = "Silakan buka salah satu perusahaan klien di menu 'Client Management' terlebih dahulu agar AI dapat membaca & menganalisis laporan payroll klien tersebut.";
            btn.style.display = 'none';
        }
    }

    // Add bubble message to chat list
    function addMessage(role, text) {
        const container = document.getElementById('aiChatMessages');
        if (!container) return;

        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${role === 'user' ? 'user' : 'ai'}`;
        
        // Render simple markdown to html
        bubble.innerHTML = parseMarkdown(text);
        container.appendChild(bubble);

        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    // Add typing indicator
    function showTypingIndicator() {
        const container = document.getElementById('aiChatMessages');
        if (!container) return;

        const indicator = document.createElement('div');
        indicator.id = 'aiTypingIndicator';
        indicator.className = 'typing-indicator';
        indicator.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        container.appendChild(indicator);
        container.scrollTop = container.scrollHeight;
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('aiTypingIndicator');
        if (indicator) indicator.remove();
    }

    // Render loading skeletons in report panels
    function showReportLoading(panelResultId, placeholderId) {
        document.getElementById(placeholderId).style.display = 'none';
        
        const resultDiv = document.getElementById(panelResultId);
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = `
            <div class="ai-skeleton">
                <div class="ai-skeleton-line short"></div>
                <div class="ai-skeleton-line medium"></div>
                <div class="ai-skeleton-line"></div>
                <div class="ai-skeleton-line medium"></div>
                <div class="ai-skeleton-line short"></div>
            </div>
        `;
    }

    // Send Message Logic (Tab 1)
    async function submitUserMessage(message) {
        if (STATE.isLoading) return;

        // Add to view
        addMessage('user', message);
        showTypingIndicator();
        STATE.isLoading = true;

        // Collect context to make AI smart
        const activeSection = document.querySelector('.view-section.active');
        let currentViewName = 'Dashboard';
        if (activeSection) {
            const sectionId = activeSection.id;
            // Map section ID to readable name
            const viewsMap = {
                viewDashboard: 'Dashboard Perusahaan',
                viewKlien: 'Client Management (Daftar Mitra)',
                viewManajemenKaryawan: 'Data Karyawan Global',
                viewClientWorkspace: 'Workspace Klien',
                viewPayroll: 'Master Skema Payroll',
                viewPajak: 'Master Skema Pajak',
                viewSchedule: 'Jadwal & Shift Kehadiran',
                viewGlobalLokasiKerja: 'Lokasi Kerja Cabang',
                viewSto: 'Global STO Master',
                viewMasterKompensasi: 'Master Skema Kompensasi/Tunjangan'
            };
            currentViewName = viewsMap[sectionId] || sectionId;
        }

        const contextPayload = {
            current_view: currentViewName,
            selected_client_id: window.selectedClientId || null
        };

        try {
            // Keep history limits
            const recentHistory = STATE.chatHistory.slice(-6); // last 6 exchanges

            const res = await fetch(`${window.API}/ai/chat`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    history: recentHistory,
                    context: contextPayload
                })
            });

            const data = await res.json();
            removeTypingIndicator();

            if (res.ok && data.status === 200) {
                const reply = data.reply;
                // Save to history state
                STATE.chatHistory.push({ role: 'user', text: message });
                STATE.chatHistory.push({ role: 'model', text: reply });

                addMessage('model', reply);
            } else {
                addMessage('model', 'Maaf, saya kesulitan memproses pertanyaan Anda saat ini. Silakan coba kembali.');
            }
        } catch (err) {
            console.error('AI chat error:', err);
            removeTypingIndicator();
            addMessage('model', 'Terjadi kesalahan koneksi jaringan. Pastikan server Spark berjalan.');
        } finally {
            STATE.isLoading = false;
        }
    }

    // Generate Dashboard Summary (Tab 2)
    async function generateExecutiveSummary() {
        if (STATE.isLoading) return;

        showReportLoading('aiSummaryResult', 'aiSummaryPlaceholder');
        STATE.isLoading = true;

        try {
            const res = await fetch(`${window.API}/ai/summarize-dashboard`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            });

            const data = await res.json();
            const resultDiv = document.getElementById('aiSummaryResult');

            if (res.ok && data.status === 200) {
                resultDiv.innerHTML = parseMarkdown(data.summary);
            } else {
                resultDiv.innerHTML = '<p style="color:red;">Gagal membuat rangkuman. Coba beberapa saat lagi.</p>';
            }
        } catch (err) {
            console.error('AI Summary error:', err);
            document.getElementById('aiSummaryResult').innerHTML = '<p style="color:red;">Koneksi jaringan terputus.</p>';
        } finally {
            STATE.isLoading = false;
        }
    }

    // Analyze Current Page/Workspace (Tab 3)
    async function analyzeCurrentPage() {
        if (STATE.isLoading) return;
        if (!window.selectedClientId) {
            updatePageAnalysisContextLabel();
            return;
        }

        showReportLoading('aiPageResult', 'aiPagePlaceholder');
        STATE.isLoading = true;

        // Get period ID if active (from selectPeriodInput dropdown)
        const selectPeriod = document.getElementById('selectPeriodInput');
        const periodId = selectPeriod ? selectPeriod.value : null;

        try {
            const res = await fetch(`${window.API}/ai/summarize-payroll`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    client_id: window.selectedClientId,
                    period_id: periodId
                })
            });

            const data = await res.json();
            const resultDiv = document.getElementById('aiPageResult');

            if (res.ok && data.status === 200) {
                resultDiv.innerHTML = parseMarkdown(data.summary);
            } else {
                resultDiv.innerHTML = `<p style="color:red;">Gagal menganalisis payroll klien: ${data.message || 'Error API'}</p>`;
            }
        } catch (err) {
            console.error('AI Page Analysis error:', err);
            document.getElementById('aiPageResult').innerHTML = '<p style="color:red;">Koneksi jaringan terputus.</p>';
        } finally {
            STATE.isLoading = false;
        }
    }

    // Extremely simple markdown formatter
    function parseMarkdown(md) {
        if (!md) return '';
        
        let html = md;
        
        // Escape HTML tags to prevent XSS
        html = html.replace(/</g, "&lt;").replace(/>/g, "&gt;");

        // Headers
        html = html.replace(/^### (.*$)/gim, '<h4>$1</h4>');
        html = html.replace(/^## (.*$)/gim, '<h3>$1</h3>');
        html = html.replace(/^# (.*$)/gim, '<h2>$1</h2>');

        // Bold
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // Blockquotes
        html = html.replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>');

        // Tables
        const lines = html.split('\n');
        let inTable = false;
        let tableHtml = '';

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            if (line.startsWith('|') && line.endsWith('|')) {
                if (!inTable) {
                    inTable = true;
                    tableHtml = '<table>';
                }
                const cells = line.split('|').map(c => c.trim()).filter((c, idx, arr) => idx > 0 && idx < arr.length - 1);
                
                // Check if separator line
                if (cells.every(c => c.match(/^:?-+:?$/))) {
                    continue; // Skip the separator row
                }
                
                const isHeader = (tableHtml === '<table>');
                tableHtml += '<tr>';
                cells.forEach(cell => {
                    tableHtml += isHeader ? `<th>${cell}</th>` : `<td>${cell}</td>`;
                });
                tableHtml += '</tr>';
                lines[i] = ''; // clear line
            } else {
                if (inTable) {
                    inTable = false;
                    tableHtml += '</table>';
                    lines[i - 1] = tableHtml; // put table on previous line
                }
            }
        }
        
        if (inTable) {
            tableHtml += '</table>';
            lines[lines.length - 1] = tableHtml;
        }

        html = lines.join('\n');

        // Lists
        html = html.replace(/^\s*[\*\-]\s+(.*$)/gim, '<li>$1</li>');
        // Wrap <li> groups in <ul>
        // This is a simple regex that wraps adjacent <li> tags
        html = html.replace(/(<li>.*?<\/li>)+/gs, '<ul>$&</ul>');

        // Paragraphs: double newline to paragraph
        html = html.replace(/\n\n/g, '</p><p>');
        html = html.replace(/\n/g, '<br>');

        return `<p>${html}</p>`.replace(/<p><br><\/p>/g, '').replace(/<p><\/p>/g, '');
    }

    // Self-initialize on DOMContentLoaded or immediately if already loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
