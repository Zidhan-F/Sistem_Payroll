<header>
    <div class="header-left">
        <button class="header-hamburger" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h2 id="viewTitle">Dashboard</h2>
    </div>
    <div style="display: flex; align-items: center;">
        <div class="notifications-container">
            <div class="notifications-bell" id="notificationsBell">
                <i class="fas fa-bell"></i>
                <span class="notifications-badge" id="notificationsBadge" style="display: none;">0</span>
            </div>
            <div class="notifications-dropdown" id="notificationsDropdown">
                <div class="notifications-header">
                    <h3>Notifikasi Peringatan</h3>
                    <span class="notifications-count" id="notificationsCount">0 Baru</span>
                </div>
                <div class="notifications-list" id="notificationsList">
                    <div class="notifications-loading">Memuat...</div>
                </div>
            </div>
        </div>
        <div class="user-profile" onclick="logout()">
            <i class="fas fa-user-circle"></i>
            <span id="headerUserName">User</span>
            <i class="fas fa-sign-out-alt" title="Logout"></i>
        </div>
    </div>
</header>
