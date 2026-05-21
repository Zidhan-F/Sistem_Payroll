            <!-- Section: Manajemen Karyawan (Global) -->
            <div id="viewManajemenKaryawan" class="view-section">
                <div class="content-card">
                    <div class="section-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color); margin: 0;">Data Karyawan</h3>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <input type="text" id="cariKaryawanGlobal" placeholder="Cari nama, nik, posisi..." oninput="cariKaryawanGlobalAktif()" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalKaryawanGlobal()" style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                <i class="fas fa-plus"></i> Tambah Karyawan
                            </button>
                        </div>
                    </div>

                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                                    <th>NIK</th>
                                                    <th>Nama Karyawan</th>
                                                    <th>Lahir</th>
                                                    <th>NPWP</th>
                                                    <th>Perusahaan / Klien</th>
                                                    <th>Kontrak</th>
                                                    <th>Lokasi Kerja</th>
                                                    <th>Status Nikah</th>
                                                    <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tabelKaryawanGlobalBody">
                            <!-- Injected by app.js -->
                        </tbody>
                    </table>
                </div>
            </div>
