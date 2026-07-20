            <!-- Section: Employee Management (Global) -->
            <div id="viewManajemenKaryawan" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Employee Data</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariKaryawanGlobal" placeholder="Search name, NIK, position..." oninput="cariKaryawanGlobalAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalKaryawanGlobal()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <img src="https://cdn-icons-png.flaticon.com/512/992/992651.png" style="width: 16px; height: 16px; object-fit: contain; filter: brightness(0) invert(1);"> Add Employee
                            </button>
                        </div>
                    </div>

                    <div class="table-container">
                        <table style="width: 100%;">
                             <thead>
                                 <tr>
                                     <th>Company / Client</th>
                                     <th>Employee ID (NIK)</th>
                                     <th>Employee Name</th>
                                     <th>Place & Date of Birth</th>
                                     <th>NPWP</th>
                                     <th>Status PTKP</th>
                                     <th>Division</th>
                                     <th>Department</th>
                                     <th>Position / Role</th>
                                     <th>Work Location</th>
                                     <th>City/Regency</th>
                                     <th>Province</th>
                                     <th>Shift</th>
                                     <th>Min. Wage (UMP/UMK)</th>
                                     <th>Contract</th>
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
