            <!-- Section: Employee Management (Global) -->
            <div id="viewManajemenKaryawan" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Employee Data</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariKaryawanGlobal" placeholder="Search name, NIK, position..." oninput="cariKaryawanGlobalAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalKaryawanGlobal()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <i class="fas fa-plus"></i> Add Employee
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                                        <th>NIK</th>
                                                        <th>Employee Name</th>
                                                        <th>Birth Date</th>
                                                        <th>NPWP</th>
                                                        <th>Company / Client</th>
                                                        <th>Contract</th>
                                                        <th>Work Location</th>
                                                        <th>Marital Status</th>
                                                        <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKaryawanGlobalBody">
                                <!-- Injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
