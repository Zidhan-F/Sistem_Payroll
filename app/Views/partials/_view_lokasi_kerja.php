            <!-- Section: Global Lokasi Kerja -->
            <div id="viewGlobalLokasiKerja" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Work Location Data</h3>
                        <button id="btnTambahLokasiKerjaGlobal" class="btn-add" onclick="bukaModalLokasiKerja()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                            <i class="fas fa-plus"></i> Add Work Location
                        </button>
                    </div>

                    <div class="table-container">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Work Location</th>
                                    <th>Location Code</th>
                                    <th>Client / Company</th>
                                    <th>Division</th>
                                    <th>Department</th>
                                    <th>Position / Role</th>
                                    <th>Province</th>
                                    <th>City/Regency</th>
                                    <th id="thActionLokasiKerjaGlobal">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelGlobalLokasiKerjaBody">
                                <!-- Injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
