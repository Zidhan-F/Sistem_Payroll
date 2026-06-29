// ===== USER MANAGEMENT MODULE =====
// Handles CRUD operations for admin user management

let allUsers = [];
let deleteUserId = null;

const USER_ROLE_CONFIG = {
    pending:              { label: 'Pending Approval',   color: '#ef4444', icon: 'fa-user-clock' },
    admin:                { label: 'Admin',              color: '#8b5cf6', icon: 'fa-user-shield' },
    payroll:              { label: 'Payroll',             color: '#06b6d4', icon: 'fa-file-invoice-dollar' },
    business_development: { label: 'Business Dev',       color: '#f59e0b', icon: 'fa-briefcase' },
    recruiter:            { label: 'Recruiter',           color: '#10b981', icon: 'fa-user-plus' },
    client_superior:      { label: 'Client / Superior',  color: '#e11d48', icon: 'fa-user-tie' },
    hc_ops:               { label: 'HC Ops',              color: '#3b82f6', icon: 'fa-clipboard-list' },
    staff:                { label: 'Staff',                color: '#6b7280', icon: 'fa-user' }
};

/**
 * Load all users from API
 */
async function loadUsers() {
    try {
        const res = await fetch(`${API_URL}/users`);
        const data = await res.json();

        if (!res.ok) {
            showToast(data.messages?.error || 'Failed to load users', 'error');
            return;
        }

        allUsers = data.data || [];
        renderUsersTable(allUsers);
        updateUserStats(allUsers);
    } catch (err) {
        console.error('Error loading users:', err);
        showToast('Failed to load users', 'error');
    }
}

/**
 * Render users table
 */
function renderUsersTable(users) {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;

    if (!users || users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 40px; color: #888;">
            <i class="fas fa-users" style="font-size: 32px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
            No users found
        </td></tr>`;
        return;
    }

    tbody.innerHTML = users.map((user, i) => {
        const roleConfig = USER_ROLE_CONFIG[user.role] || { label: user.role, color: '#6b7280', icon: 'fa-user' };
        const isActive = user.is_active == 1 || user.is_active === true;
        const createdAt = user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

        return `<tr class="${!isActive ? 'row-inactive' : ''}">
            <td>${i + 1}</td>
            <td>
                <div class="user-cell">
                    <div class="user-avatar" style="background: ${roleConfig.color}22; color: ${roleConfig.color};">
                        <i class="fas ${roleConfig.icon}"></i>
                    </div>
                    <strong>${escapeHtml(user.username)}</strong>
                </div>
            </td>
            <td>${escapeHtml(user.full_name || '-')}</td>
            <td>${escapeHtml(user.email || '-')}</td>
            <td>
                <span class="role-badge-table" style="background: ${roleConfig.color}18; color: ${roleConfig.color}; border: 1px solid ${roleConfig.color}33;">
                    <i class="fas ${roleConfig.icon}" style="font-size: 10px;"></i>
                    ${roleConfig.label}
                </span>
            </td>
            <td>
                <span class="status-badge ${isActive ? 'status-active' : 'status-inactive'}">
                    <i class="fas fa-circle" style="font-size: 6px;"></i>
                    ${isActive ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td style="color: #888; font-size: 12px;">${createdAt}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-edit" onclick="openEditUserModal(${user.id})" title="Edit User">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn-action btn-delete" onclick="openDeleteUserModal(${user.id}, '${escapeHtml(user.username)}')" title="Delete User">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

/**
 * Update stats cards
 */
function updateUserStats(users) {
    const total = users.length;
    const active = users.filter(u => u.is_active == 1 || u.is_active === true).length;
    const inactive = total - active;

    const statTotal = document.getElementById('statTotalUsers');
    const statActive = document.getElementById('statActiveUsers');
    const statInactive = document.getElementById('statInactiveUsers');

    if (statTotal) statTotal.textContent = total;
    if (statActive) statActive.textContent = active;
    if (statInactive) statInactive.textContent = inactive;
}

/**
 * Filter users table by search input
 */
function filterUsersTable() {
    const query = (document.getElementById('searchUsers')?.value || '').toLowerCase();
    if (!query) {
        renderUsersTable(allUsers);
        return;
    }
    const filtered = allUsers.filter(u => 
        (u.username || '').toLowerCase().includes(query) ||
        (u.full_name || '').toLowerCase().includes(query) ||
        (u.email || '').toLowerCase().includes(query) ||
        (u.role || '').toLowerCase().includes(query)
    );
    renderUsersTable(filtered);
}

/**
 * Open create user modal
 */
function openCreateUserModal() {
    document.getElementById('userModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Add New User';
    document.getElementById('editUserId').value = '';
    document.getElementById('userFormUsername').value = '';
    document.getElementById('userFormFullName').value = '';
    document.getElementById('userFormEmail').value = '';
    document.getElementById('userFormPassword').value = '';
    document.getElementById('userFormRole').value = 'staff';
    document.getElementById('userFormActive').checked = true;
    document.getElementById('userActiveLabel').textContent = 'Active';
    document.getElementById('passwordRequired').style.display = '';
    document.getElementById('passwordHint').style.display = 'none';

    document.getElementById('modalUserForm').style.display = 'block';
    if (document.getElementById('overlay')) document.getElementById('overlay').style.display = 'block';
}

/**
 * Open edit user modal
 */
function openEditUserModal(userId) {
    const user = allUsers.find(u => u.id == userId);
    if (!user) {
        showToast('User not found', 'error');
        return;
    }

    document.getElementById('userModalTitle').innerHTML = '<i class="fas fa-user-edit"></i> Edit User';
    document.getElementById('editUserId').value = user.id;
    document.getElementById('userFormUsername').value = user.username || '';
    document.getElementById('userFormFullName').value = user.full_name || '';
    document.getElementById('userFormEmail').value = user.email || '';
    document.getElementById('userFormPassword').value = '';
    document.getElementById('userFormRole').value = user.role || 'staff';
    document.getElementById('userFormActive').checked = user.is_active == 1;
    document.getElementById('userActiveLabel').textContent = user.is_active == 1 ? 'Active' : 'Inactive';
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordHint').style.display = '';

    document.getElementById('modalUserForm').style.display = 'block';
    if (document.getElementById('overlay')) document.getElementById('overlay').style.display = 'block';
}

/**
 * Close user modal
 */
function closeUserModal() {
    document.getElementById('modalUserForm').style.display = 'none';
    if (document.getElementById('overlay')) document.getElementById('overlay').style.display = 'none';
}

/**
 * Save user (create or update)
 */
async function saveUser() {
    const editId = document.getElementById('editUserId').value;
    const isEdit = !!editId;

    const username  = document.getElementById('userFormUsername').value.trim();
    const fullName  = document.getElementById('userFormFullName').value.trim();
    const email     = document.getElementById('userFormEmail').value.trim();
    const password  = document.getElementById('userFormPassword').value;
    const role      = document.getElementById('userFormRole').value;
    const isActive  = document.getElementById('userFormActive').checked;

    if (!username) {
        showToast('Username wajib diisi', 'error');
        return;
    }
    if (!isEdit && !password) {
        showToast('Password wajib diisi', 'error');
        return;
    }

    const payload = {
        username,
        full_name: fullName,
        email,
        role,
        is_active: isActive ? 1 : 0
    };
    if (password) {
        payload.password = password;
    }

    try {
        const url = isEdit ? `${API_URL}/users/${editId}` : `${API_URL}/users`;
        const method = isEdit ? 'PUT' : 'POST';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (res.ok && data.success) {
            showToast(data.message || (isEdit ? 'User berhasil diupdate' : 'User berhasil dibuat'), 'success');
            closeUserModal();
            loadUsers();
        } else {
            showToast(data.messages?.error || data.message || 'Gagal menyimpan user', 'error');
        }
    } catch (err) {
        console.error('Error saving user:', err);
        showToast('Gagal menyimpan user', 'error');
    }
}

/**
 * Open delete confirmation modal
 */
function openDeleteUserModal(userId, username) {
    deleteUserId = userId;
    document.getElementById('deleteUserName').textContent = username;
    document.getElementById('modalDeleteUser').style.display = 'block';
    if (document.getElementById('overlay')) document.getElementById('overlay').style.display = 'block';
}

/**
 * Close delete modal
 */
function closeDeleteUserModal() {
    document.getElementById('modalDeleteUser').style.display = 'none';
    if (document.getElementById('overlay')) document.getElementById('overlay').style.display = 'none';
    deleteUserId = null;
}

/**
 * Confirm and execute user deletion
 */
async function confirmDeleteUser() {
    if (!deleteUserId) return;

    try {
        const res = await fetch(`${API_URL}/users/${deleteUserId}`, {
            method: 'DELETE'
        });

        const data = await res.json();

        if (res.ok && data.success) {
            showToast(data.message || 'User berhasil dihapus', 'success');
            closeDeleteUserModal();
            loadUsers();
        } else {
            showToast(data.messages?.error || data.message || 'Gagal menghapus user', 'error');
        }
    } catch (err) {
        console.error('Error deleting user:', err);
        showToast('Gagal menghapus user', 'error');
    }
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility() {
    const input = document.getElementById('userFormPassword');
    const icon = document.getElementById('passwordToggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Toggle active label text
 */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('userFormActive');
    if (toggle) {
        toggle.addEventListener('change', () => {
            const label = document.getElementById('userActiveLabel');
            if (label) label.textContent = toggle.checked ? 'Active' : 'Inactive';
        });
    }
});

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}
