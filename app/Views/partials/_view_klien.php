            <!-- Section: Klien -->
            <div id="viewKlien" class="view-section">
                <div class="content-card">
                    <div class="section-header">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Data Klien</h3>
                        <button class="btn-add" onclick="bukaModal('tambah')">
                            <i class="fas fa-plus"></i> Tambah
                        </button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Klien</th>
                                    <th>Bisnis Klien</th>
                                    <th>NPWP</th>
                                    <th>NIB</th>
                                    <th>Tanggal Bergabung</th>
                                    <th>Alamat</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKlienBody">
                                <!-- Data injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
