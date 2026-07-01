<!-- Section: User Management (Admin CMS) -->
<style>
.custom-status-switch {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
}
.custom-status-switch input {
    display: none !important;
}
.custom-status-slider {
    width: 48px;
    height: 26px;
    background-color: #cbd5e1;
    border-radius: 13px;
    position: relative;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}
.custom-status-slider::after {
    content: '';
    width: 20px;
    height: 20px;
    background-color: white;
    border-radius: 50%;
    position: absolute;
    top: 3px;
    left: 3px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
.custom-status-switch input:checked + .custom-status-slider {
    background-color: #10b981;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05), 0 0 8px rgba(16, 185, 129, 0.3);
}
.custom-status-switch input:checked + .custom-status-slider::after {
    transform: translateX(22px);
}
.custom-status-label {
    font-size: 14px;
    font-weight: 600;
    color: #ef4444; /* Default to Inactive (Red) */
    transition: color 0.3s ease;
}
.custom-status-switch input:checked ~ .custom-status-label {
    color: #10b981; /* Active (Green) */
}
</style>
<div id="viewUserManagement" class="view-section">
    <div class="section-header">
        <h3><i class="fas fa-user-shield"></i> User Management</h3>
        <button class="btn-add" onclick="openCreateUserModal()">
            <i class="fas fa-user-plus"></i> Add New User
        </button>
    </div>



    <!-- User Table -->
    <div class="card">
        <div class="card-header">
            <h4>All Users</h4>
            <div class="card-actions">
                <input type="search" id="searchUsers" placeholder="Search users..." class="search-input" oninput="filterUsersTable()" autocomplete="off">
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr><td colspan="8" style="text-align:center; padding: 40px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create/Edit User -->
<div id="modalUserForm" class="modal-skema" style="display:none; z-index: 1000;">
    <div class="modal-header">
        <h3 id="userModalTitle"><i class="fas fa-user-plus"></i> Add New User</h3>
        <i class="fas fa-times" style="cursor: pointer;" onclick="closeUserModal()"></i>
    </div>
    <div class="modal-body">
        <input type="hidden" id="editUserId" value="">
        <div class="form-group">
            <label for="userFormUsername">Username <span class="required">*</span></label>
            <input type="text" id="userFormUsername" placeholder="Enter username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="userFormFullName">Full Name</label>
            <input type="text" id="userFormFullName" placeholder="Enter full name" class="form-control">
        </div>
        <div class="form-group">
            <label for="userFormEmail">Email</label>
            <input type="email" id="userFormEmail" placeholder="Enter email address" class="form-control">
        </div>
        <div class="form-group">
            <label for="userFormPassword">Password <span class="required" id="passwordRequired">*</span></label>
            <div class="input-wrapper" style="position: relative;">
                <input type="password" id="userFormPassword" placeholder="Enter password" class="form-control">
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #888;">
                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                </button>
            </div>
            <small id="passwordHint" style="display: none; color: #888;">Leave blank to keep current password</small>
        </div>
        <div class="form-group">
            <label for="userFormRole">Role <span class="required">*</span></label>
            <select id="userFormRole" class="form-control">
                <option value="pending">Pending Approval</option>
                <option value="admin">Admin</option>
                <option value="payroll">Payroll</option>
                <option value="business_development">Business Development</option>
                <option value="recruiter">Recruiter</option>
                <option value="client_superior">Client / Superior</option>
                <option value="hc_ops">HC Ops</option>
                <option value="staff">Staff</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label style="font-weight: 600; margin-bottom: 8px; display: block; color: #475569; font-size: 14px;">Status</label>
            <div style="display: flex; align-items: center; padding: 4px 0;">
                <label class="custom-status-switch">
                    <input type="checkbox" id="userFormActive" checked>
                    <span class="custom-status-slider"></span>
                    <span class="custom-status-label" id="userActiveLabel">Active</span>
                </label>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn-cancel" onclick="closeUserModal()">Cancel</button>
        <button class="btn-save" onclick="saveUser()"><i class="fas fa-save"></i> Save User</button>
    </div>
</div>

<!-- Modal: Delete Confirmation -->
<div id="modalDeleteUser" class="modal-skema" style="display:none; z-index: 1000;">
    <div class="modal-header" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
        <h3><i class="fas fa-exclamation-triangle"></i> Delete User</h3>
        <i class="fas fa-times" style="cursor: pointer;" onclick="closeDeleteUserModal()"></i>
    </div>
    <div class="modal-body" style="text-align: center; padding: 30px;">
        <i class="fas fa-user-times" style="font-size: 48px; color: #ef4444; margin-bottom: 16px;"></i>
        <p style="font-size: 16px; margin-bottom: 8px;">Are you sure you want to delete user:</p>
        <p style="font-size: 20px; font-weight: 700;" id="deleteUserName">-</p>
        <p style="color: #888; font-size: 13px;">This action cannot be undone.</p>
    </div>
    <div class="modal-footer" style="justify-content: center;">
        <button class="btn-cancel" onclick="closeDeleteUserModal()">Cancel</button>
        <button class="btn-danger" onclick="confirmDeleteUser()"><i class="fas fa-trash"></i> Delete</button>
    </div>
</div>
