            <!-- Section: Global Lokasi Kerja -->
            <div id="viewGlobalLokasiKerja" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Data Lokasi Kerja</h3>
                        <button class="btn-add" onclick="bukaModalLokasiKerja()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                            <i class="fas fa-plus"></i> Tambah Lokasi Kerja
                        </button>
                    </div>

                    <div class="table-container">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Lokasi Kerja</th>
                                    <th>Location Code</th>
                                    <th>Klien / Perusahaan</th>
                                    <th>Divisi</th>
                                    <th>Department</th>
                                    <th>Posisi / Jabatan</th>
                                    <th>Provinsi</th>
                                    <th>Kota/Kabupaten</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelGlobalLokasiKerjaBody">
                                <!-- Injected by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
