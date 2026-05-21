    <!-- Modal Slip Gaji -->
    <div id="overlay" onclick="tutupSemuaModal()"></div>

    <!-- Modal Form Skema Payroll -->
    <div id="modalSkema" class="modal-skema" style="width: 1100px; max-width: 95%;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);">
            <h3 id="modalSkemaTitle">Tambah Skema Payroll</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSkema()"></i>
        </div>
        <form id="formSkema">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="skemaId">
                <input type="hidden" id="skemaIsPersentase" value="0">
                <input type="hidden" id="skemaTipe" value="bulanan">

                <!-- Nama Skema (Full Width) -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; font-size: 14px; color: #475569; display: block; margin-bottom: 8px;">Nama Skema</label>
                    <input type="text" id="skemaNama" placeholder="Masukkan Nama Skema" required style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid #ddd; outline: none; font-size: 14px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                </div>

                <!-- Two Column Layout: Left (Kompensasi), Right (Skema Absen & Deskripsi) -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 20px;">
                    
                    <!-- Left Column: Komponen -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Komponen</h4>
                        
                        <!-- Gaji Pokok -->
                        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Sumber Gaji Pokok</label>
                                <select id="skemaSumber" onchange="handlePayrollSchemeSumberNilaiChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                                    <option value="ump">UMP (Provinsi)</option>
                                    <option value="umk">UMK (Kota/Kabupaten)</option>
                                    <option value="nominal" selected>Nominal Custom</option>
                    </select>
                </div>
                            <div class="form-group" style="margin: 0;">
                                <label id="labelNilaiSkemaPayroll" style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Gaji Pokok (Rp)</label>
                                 <input type="text" id="skemaNilai" placeholder="Masukkan Gaji Pokok" onkeyup="formatRupiahInput(this)" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
            </div>
                        </div>

                        <!-- Periode Gaji -->
                        <div class="form-group" style="margin: 0;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Periode Gaji</label>
                            <select id="skemaPeriode" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white; height: 42px;">
                                <option value="bulan" selected>Per Bulan</option>
                                <option value="minggu">Per Minggu</option>
                                <option value="hari_kerja">Per Hari Kerja</option>
                                <option value="tahun">Per Tahun</option>
                            </select>
                        </div>

                        <!-- Komponen Tetap Table -->
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-weight: 600; font-size: 13px; color: #475569; margin: 0;">Komponen Tetap</label>
                                <button type="button" onclick="bukaModalPilihSkema('tetap')" style="background: none; border: none; color: #0d6efd; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">Pilih Skema</button>
                            </div>
                            
                            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: white;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #0d6efd; color: white;">
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: left; font-weight: 600; width: 60%;">Name</th>
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: right; font-weight: 600; width: 40%;">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelKompensasiTetapBody">
                                        <tr>
                                            <td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">Belum ada skema komponen terpilih</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Komponen Tidak Tetap Table -->
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-weight: 600; font-size: 13px; color: #475569; margin: 0;">Komponen Tidak Tetap</label>
                                <button type="button" onclick="bukaModalPilihSkema('tidak_tetap')" style="background: none; border: none; color: #0d6efd; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">Pilih Skema</button>
                            </div>
                            
                            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: white;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #0d6efd; color: white;">
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: left; font-weight: 600; width: 60%;">Name</th>
                                            <th style="padding: 8px 12px; font-size: 12px; text-align: right; font-weight: 600; width: 40%;">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelKompensasiTidakTetapBody">
                                        <tr>
                                            <td colspan="2" style="padding: 12px; text-align: center; color: #94a3b8; font-size: 13px;">Belum ada skema komponen terpilih</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </div>

                    <!-- Right Column: Skema Absen & Deskripsi -->
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; gap: 15px;">
                        <h4 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Skema Absen</h4>
                        
                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; color: #1e293b; margin: 0;">
                                <input type="radio" name="skemaAbsenRule" value="prorate" onchange="handleSkemaAbsenRuleChange()" style="cursor: pointer; width: 18px; height: 18px;">
                                Prorate
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; color: #1e293b; margin: 0;">
                                <input type="radio" name="skemaAbsenRule" value="tidak_potong" onchange="handleSkemaAbsenRuleChange()" style="cursor: pointer; width: 18px; height: 18px;">
                                Absen Tidak Memotong Gaji
                            </label>
                            
                            <label style="display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; cursor: pointer; color: #1e293b; margin: 0;">
                                <input type="radio" name="skemaAbsenRule" value="potong_nominal" onchange="handleSkemaAbsenRuleChange()" style="cursor: pointer; width: 18px; height: 18px;">
                                Absen Potong Nominal
                            </label>
                            
                            <div id="containerNominalPotonganSkema" class="form-group" style="display: none; margin: 8px 0 0 0;">
                                <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Nominal Potongan Absen per Hari (Rp)</label>
                                 <input type="text" id="skemaNominalPotongan" placeholder="Contoh: 100000" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; height: 42px;">
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group" style="margin: 0; flex-grow: 1; display: flex; flex-direction: column;">
                            <label style="font-weight: 600; font-size: 13px; color: #475569; display: block; margin-bottom: 6px;">Deskripsi / Catatan</label>
                            <textarea id="skemaDeskripsi" rows="8" placeholder="Masukkan deskripsi singkat skema payroll atau catatan tambahan di sini..." style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; outline: none; font-size: 14px; resize: none; font-family: inherit; flex-grow: 1; min-height: 180px;"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 15px 25px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                <button type="button" class="btn-cancel" onclick="tutupModalSkema()" style="padding: 10px 24px; border-radius: 8px;">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color); box-shadow: 0 4px 6px rgba(243, 156, 18, 0.2); padding: 10px 24px; border-radius: 8px;">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Komponen Payroll -->
    <div id="modalKomponen" class="modal-skema">
        <div class="modal-header" style="background: var(--info);">
            <h3 id="modalKomponenTitle">Tambah Komponen</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKomponen()"></i>
        </div>
        <form id="formKomponen">
            <div class="modal-body">
                <input type="hidden" id="komponenId">
                <input type="hidden" id="komponenSchemeId">
                <div class="form-group">
                    <label>Kategori</label>
                    <select id="komponenKategori" required onchange="onKategoriChange()">
                        <option value="gaji">Gaji Pokok</option>
                        <option value="tunjangan">Tunjangan</option>
                        <option value="insentif">Insentif</option>
                        <option value="lembur">Lembur</option>
                        <option value="absensi">Potongan Absensi</option>
                        <option value="bpjs_kesehatan">BPJS Kesehatan</option>
                        <option value="bpjs_ketenagakerjaan">BPJS Ketenagakerjaan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nama Komponen</label>
                    <input type="text" id="komponenNama" placeholder="Contoh: Gaji Pokok" required>
                </div>
                <div class="form-group">
                    <label>Tipe</label>
                    <select id="komponenTipe" required>
                        <option value="pendapatan">Pendapatan (+)</option>
                        <option value="potongan">Potongan (-)</option>
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nilai (Rp atau %)</label>
                         <input type="text" id="komponenNilai" placeholder="0" value="0" onkeyup="handleKomponenNilaiInput(this)">
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <select id="komponenIsPersentase" onchange="handleKomponenNilaiInput(document.getElementById('komponenNilai'))">
                            <option value="false">Rupiah (Rp)</option>
                            <option value="true">Persentase (%)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Keterangan (opsional)</label>
                    <input type="text" id="komponenKeterangan" placeholder="Catatan tambahan">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKomponen()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Klien -->
    <div id="modalClient">
        <div class="modal-header">
            <h3 id="modalTitle">Tambah Data Client</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModal()"></i>
        </div>
        <form id="formKlien">
            <div class="modal-body">
                <input type="hidden" id="clientId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nama Klien</label>
                        <input type="text" id="namaKlien" placeholder="Masukkan nama klien" required>
                    </div>
                    <div class="form-group">
                        <label>Email Klien</label>
                        <input type="email" id="emailKlien" placeholder="klien@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label>Pilih Sektor Klien</label>
                        <select id="sektorKlien" required>
                            <option value="">-- Pilih Sektor --</option>
                            <option value="Retail">Retail</option>
                            <option value="Manufaktur">Manufaktur</option>
                            <option value="Jasa">Jasa</option>
                            <option value="Teknologi">Teknologi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Induk Berusaha</label>
                        <input type="text" id="nib" placeholder="Masukkan NIB" required>
                    </div>
                    <div class="form-group">
                        <label>NPWP</label>
                        <input type="text" id="npwp" placeholder="Masukkan NPWP" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Bergabung</label>
                        <input type="date" id="tanggalBergabung" required>
                    </div>
                    <div class="form-group full-width">
                        <label>Alamat</label>
                        <textarea id="alamat" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModal()">Batal</button>
                <button type="submit" id="btnSubmit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Organisasi -->
    <div id="modalOrg" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; width: 450px; border-radius: 12px; z-index: 1100; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div class="modal-header">
            <h3 id="modalOrgTitle">Tambah Divisi</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalOrg()"></i>
        </div>
        <form id="formOrg">
            <div class="modal-body">
                <input type="hidden" id="orgId">
                <input type="hidden" id="orgParentId">
                <input type="hidden" id="orgType">

                <div class="form-group">
                    <label id="labelOrgName">Nama Divisi</label>
                    <input type="text" id="orgName" placeholder="Masukkan nama" required>
                    <div id="quickBadgeContainer" style="margin-top: 8px; display: flex; flex-wrap: wrap; gap: 6px;"></div>
                </div>
                <!-- Extra Fields (Hanya untuk Posisi) -->
                <div id="posExtraFields" style="display: none;">
                    <div class="form-group">
                        <label>Level Posisi</label>
                        <select id="posLevel" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; font-size: 14px;">
                            <option value="">-- Pilih Level --</option>
                            <option value="Intern">Intern</option>
                            <option value="Junior">Junior</option>
                            <option value="Staff">Staff</option>
                            <option value="Staff Senior">Staff Senior</option>
                            <option value="Assistant Manager">Assistant Manager</option>
                            <option value="Manager">Manager</option>
                            <option value="Lead">Lead</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalOrg()">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Form Skema Pajak -->
    <div id="modalPajak" class="modal-skema">
        <div class="modal-header" style="background: var(--danger);">
            <h3 id="modalPajakTitle">Tambah Skema Pajak</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPajak()"></i>
        </div>
        <form id="formPajak">
            <div class="modal-body">
                <input type="hidden" id="pajakId">
                <div class="form-group">
                    <label>Nama Skema Pajak</label>
                    <input type="text" id="pajakNama" placeholder="Contoh: PPh 21 Tetap" required>
                </div>
                <div class="form-group">
                    <label>Metode Pajak</label>
                    <select id="pajakMetode" required>
                        <option value="Gross">Gross (Pajak ditanggung Karyawan)</option>
                        <option value="Gross Up">Gross Up (Tunjangan Pajak)</option>
                        <option value="Nett">Nett (Pajak ditanggung Perusahaan)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status PTKP Default</label>
                    <select id="pajakPtkp" required>
                        <option value="TK/0">TK/0 (Tidak Kawin)</option>
                        <option value="K/0">K/0 (Kawin)</option>
                        <option value="K/1">K/1 (Kawin Anak 1)</option>
                        <option value="K/2">K/2 (Kawin Anak 2)</option>
                        <option value="K/3">K/3 (Kawin Anak 3)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea id="pajakDeskripsi" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalPajak()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--danger);">Simpan</button>
            </div>
        </form>
    </div>

    <!-- Modal Setup Payroll Klien -->
    <div id="modalSetup" class="modal-skema">
        <div class="modal-header" style="background: var(--secondary-color);">
            <h3 id="modalSetupTitle">Setup Payroll Klien</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSetup()"></i>
        </div>
        <form id="formSetup">
            <div class="modal-body">
                <input type="hidden" id="setupId">
                <input type="hidden" id="setupClientId">
                <div class="form-group">
                    <label>Klien</label>
                    <input type="text" id="setupClientNama" readonly style="background: #f0f0f0;">
                </div>
                <div class="form-group">
                    <label>Pilih Tipe Skema Payroll</label>
                    <select id="setupPayrollSchemeTipe" onchange="handleSetupPayrollSchemeTipeChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; margin-bottom: 10px;">
                        <option value="">-- Pilih Tipe Skema --</option>
                        <option value="UMP">UMP (Upah Minimum Provinsi)</option>
                        <option value="UMK">UMK (Upah Minimum Kota/Kabupaten)</option>
                        <option value="Nominal">Nominal Custom</option>
                        <option value="Template">Template Skema Payroll</option>
                    </select>
                </div>
                <div class="form-group" id="setupPayrollSchemeWilayahContainer" style="display: none; margin-bottom: 10px;">
                    <label>Pilih Wilayah UMP/UMK</label>
                    <select id="setupPayrollSchemeWilayah" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Pilih Wilayah --</option>
                    </select>
                </div>
                <div class="form-group" id="setupPayrollSchemeNominalContainer" style="display: none; margin-bottom: 10px;">
                    <label>Nominal Gaji Pokok (Rp)</label>
                    <input type="number" id="setupPayrollSchemeNominal" placeholder="Contoh: 5000000" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>
                <div class="form-group" id="setupPayrollSchemeTemplateContainer" style="display: none; margin-bottom: 10px;">
                    <label>Pilih Template Skema</label>
                    <select id="setupPayrollScheme" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <!-- Injected by app.js -->
                    </select>
                </div>
                <div class="form-group">
                    <label>Pilih Skema Pajak</label>
                    <select id="setupTaxScheme" required>
                        <!-- Injected by app.js -->
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tanggal Gajian</label>
                        <input type="number" id="setupPayDate" min="1" max="31" value="25" required>
                    </div>
                    <div class="form-group">
                        <label>Start Cutoff (Tgl)</label>
                        <input type="number" id="setupCutoffStart" min="1" max="31" value="21" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalSetup()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--secondary-color);">Simpan Konfigurasi</button>
            </div>
        </form>
    </div>


    <!-- Custom Toast Container -->
    <div id="toastContainer"></div>

    <!-- Custom Confirm Dialog -->
    <div id="confirmOverlay" class="confirm-overlay"></div>
    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 id="confirmTitle" class="confirm-title">Konfirmasi</h3>
        <p id="confirmMessage" class="confirm-message">Apakah Anda yakin?</p>
        <div class="confirm-actions">
            <button id="confirmCancel" class="confirm-btn confirm-btn-cancel">Batal</button>
            <button id="confirmOk" class="confirm-btn confirm-btn-ok">Ya, Hapus</button>
        </div>
    </div>

    </div>

    <!-- Modal Form PKWT -->
    <div id="modalPKWT" class="modal-skema">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalPKWTTitle">Buat Kontrak PKWT</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPKWT()"></i>
        </div>
        <form id="formPKWT">
            <div class="modal-body">
                <input type="hidden" id="pkwtId">
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <input type="text" id="pkwtEmployeeName" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>Klien</label>
                    <select id="pkwtClientId" required onchange="updatePKWTSchemeInfo()">
                        <option value="">-- Pilih Klien --</option>
                        <!-- Injected by app.js -->
                    </select>
                </div>
                <div id="pkwtSchemeInfo" style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 12px; display: none;">
                    <i class="fas fa-info-circle"></i> <span id="pkwtSchemeText">Skema: -</span>
                </div>
                <div class="form-group">
                    <label>Posisi / Jabatan</label>
                    <input type="text" id="pkwtPositionName" placeholder="Contoh: Staff Admin" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tanggal Mulai</label>
                        <input type="date" id="pkwtStartDate" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Berakhir</label>
                        <input type="date" id="pkwtEndDate" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Gaji Pokok (Rp)</label>
                    <input type="number" id="pkwtBasicSalary" placeholder="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalPKWT()">Batal</button>
                <button type="submit" class="btn-save">Generate & Simpan PKWT</button>
            </div>
        </form>
    </div>

    </div>

    <!-- Modal Kelola Periode -->
    <div id="modalPeriode" class="modal-skema">
        <div class="modal-header" style="background: var(--info);">
            <h3>Manajemen Periode Payroll</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalPeriode()"></i>
        </div>
        <div class="modal-body">
            <form id="formPeriode" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Bulan</label>
                        <select id="periodMonth" required>
                            <option value="1">Januari</option><option value="2">Februari</option><option value="3">Maret</option>
                            <option value="4">April</option><option value="5">Mei</option><option value="6">Juni</option>
                            <option value="7">Juli</option><option value="8">Agustus</option><option value="9">September</option>
                            <option value="10">Oktober</option><option value="11">November</option><option value="12">Desember</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" id="periodYear" value="2024" required>
                    </div>
                </div>
                <button type="submit" class="btn-save" style="width: 100%; background: var(--info);">Buka Periode Baru</button>
            </form>
            
            <h4 style="margin-bottom: 10px; font-size: 14px;">Riwayat Periode</h4>
            <div id="periodHistoryList" style="max-height: 200px; overflow-y: auto;">
                <!-- List of periods injected by app.js -->
            </div>
        </div>
    </div>

    <!-- Modal Input Cut-Off -->
    <div id="modalCutOff" class="modal-skema">
        <div class="modal-header" style="background: var(--secondary-color);">
            <h3 id="modalCutOffTitle">Input Data Cut-Off</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalCutOff()"></i>
        </div>
        <form id="formCutOff">
            <div class="modal-body">
                <input type="hidden" id="cutoffPkwtId">
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <input type="text" id="cutoffEmployeeName" readonly style="background: #f0f0f0;">
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Hari Kerja</label>
                        <input type="number" id="cutoffHariKerja" value="22" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Lembur (Jam)</label>
                        <input type="number" id="cutoffJamLembur" step="0.5" value="0" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Potongan Absensi (Rp)</label>
                        <input type="number" id="cutoffPotongan" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Bonus/Lainnya (Rp)</label>
                        <input type="number" id="cutoffBonus" value="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalCutOff()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--secondary-color);">Simpan Data</button>
            </div>
        </form>
    </div>

    <!-- Modal Upload CSV UMP/UMK -->
    <div id="modalUploadUmr" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3>Upload Data UMP/UMK</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalUploadUmr()"></i>
        </div>
        <form id="formUploadUmr">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="uploadUmrTipe" value="UMP">
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">Tahun Minimum Wage</label>
                    <select id="uploadUmrTahun" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; margin-bottom: 8px; display: block;">File CSV</label>
                    <!-- Drag & Drop Uploader Area -->
                    <div id="umrDropZone" style="border: 2px dashed #ddd; border-radius: 12px; padding: 35px 20px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.3s ease;">
                        <i class="fas fa-file-csv" style="font-size: 48px; color: var(--primary-color); margin-bottom: 15px;"></i>
                        <h4 style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 6px;">Seret & Lepas file di sini</h4>
                        <p style="font-size: 12px; color: #7f8c8d; margin-bottom: 15px;">atau klik untuk menelusuri file dari komputer Anda</p>
                        <span id="umrFileName" style="font-size: 13px; font-weight: 600; color: var(--info); display: block; word-break: break-all;">Belum ada file terpilih</span>
                        <input type="file" id="fileUmr" accept=".csv" style="display: none;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalUploadUmr()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Unggah Sekarang</button>
            </div>
        </form>
    </div>

    <!-- Modal Input Manual UMP/UMK -->
    <div id="modalManualUmr" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: #2c3e50;">
            <h3 id="modalManualTitle">Tambah Data Minimum Wage</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalManualUmr()"></i>
        </div>
        <form id="formManualUmr">
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="manualUmrId">
                <input type="hidden" id="manualUmrTipe" value="UMP">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Kode Daerah (Provinsi / Kota / Kab)</label>
                    <input type="text" id="manualUmrKode" placeholder="Contoh: ID 31 atau 31.71" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Nama Daerah</label>
                    <input type="text" id="manualUmrNama" placeholder="Contoh: DKI JAKARTA atau JAKARTA SELATAN" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Provinsi Induk (Khusus UMK)</label>
                    <input type="text" id="manualUmrProvinsi" placeholder="Contoh: JAWA BARAT (kosongkan jika UMP)" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                </div>

                <input type="hidden" id="manualUmrNominal" value="0">
                <div class="form-group" style="margin-bottom: 15px;">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Tahun</label>
                        <input type="number" id="manualUmrTahun" placeholder="2026" required value="2026" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                    </div>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalManualUmr()">Batal</button>
                <button type="submit" class="btn-save" style="background: #2c3e50;">Simpan Data</button>
            </div>
        </form>
    </div>

    <!-- Modal Data Karyawan -->
    <div id="modalKaryawan" class="modal-skema" style="display: none; z-index: 1000;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalKaryawanTitle">Tambah Data Karyawan</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKaryawan()"></i>
        </div>
        <form id="formKaryawan">
            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="employeeId">
                
                <div class="form-group" id="empClientIdContainer" style="margin-bottom: 15px; display: none;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Pilih Perusahaan / Klien</label>
                    <select id="empClientId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Pilih Klien --</option>
                    </select>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">NIK Karyawan (NIK-KTP)</label>
                        <input type="text" id="empNik" placeholder="Contoh: 317301XXXXXXXXXX" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Nama Lengkap</label>
                        <input type="text" id="empNama" placeholder="Nama Karyawan" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Tempat Lahir</label>
                        <input type="text" id="empTempatLahir" placeholder="Contoh: Jakarta" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Tanggal Lahir</label>
                        <input type="date" id="empTanggalLahir" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">NPWP</label>
                        <input type="text" id="empNpwp" placeholder="Contoh: 00.000.000.0-000.000" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Status Pernikahan</label>
                        <select id="empStatusPernikahan" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Pilih Status --</option>
                            <option value="Belum">Belum</option>
                            <option value="Sudah">Sudah</option>
                            <option value="Cerai">Cerai</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="empJumlahAnakContainer" style="margin-bottom: 15px; display: none;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Jumlah Anak</label>
                    <input type="number" id="empJumlahAnak" min="0" placeholder="0" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Mulai Kontrak (Start)</label>
                        <input type="date" id="empStartContract" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Selesai Kontrak (End)</label>
                        <input type="date" id="empEndContract" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Tipe Perjanjian</label>
                        <select id="empTipePerjanjian" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="PKWT">PKWT</option>
                            <option value="PKWTT">PKWTT</option>
                            <option value="PKHL">PKHL</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Lokasi Kerja</label>
                    <select id="empWorkLocationId" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Pilih Lokasi Kerja --</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKaryawan()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Simpan Data</button>
            </div>
        </form>
    </div>

    <!-- Modal Lokasi Kerja -->
    <div id="modalLokasiKerja" class="modal-skema" style="display: none; z-index: 1000;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalLokasiKerjaTitle">Tambah Lokasi Kerja</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalLokasiKerja()"></i>
        </div>
        <form id="formLokasiKerja">
            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="workLocationId">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="font-weight: 600; margin-bottom: 6px; display: block;">Pilih Perusahaan / Klien</label>
                    <select id="locClientId" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="">-- Pilih Klien --</option>
                    </select>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Lokasi Kerja</label>
                        <input type="text" id="locName" placeholder="Contoh: Kantor Cabang Bandung" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Location Code</label>
                        <input type="text" id="locCode" placeholder="Contoh: L001" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Divisi</label>
                        <select id="locDivisionId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Pilih Divisi --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Departmen</label>
                        <select id="locDepartmentId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Pilih Departemen --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Posisi/Jabatan</label>
                        <select id="locPositionId" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="">-- Pilih Jabatan --</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Provinsi</label>
                        <input type="text" id="locProvinsi" placeholder="Contoh: Jawa Barat" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 600; margin-bottom: 6px; display: block;">Kota/Kabupaten</label>
                        <input type="text" id="locKotaKabupaten" placeholder="Contoh: Bandung" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalLokasiKerja()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Simpan Data</button>
            </div>
        </form>
    </div>

    <!-- Modal Skema Kompensasi (Master) -->
    <div id="modalSkemaKompensasi" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: var(--primary-color);">
            <h3 id="modalSkemaKompensasiTitle">Tambah Skema Komponen</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalSkemaKompensasi()"></i>
        </div>
        <form id="formSkemaKompensasi">
            <div class="modal-body">
                <input type="hidden" id="skemaKompensasiId">
                <input type="hidden" id="skemaKompensasiDeskripsi" value="">
                <input type="hidden" id="skemaKompensasiIsPersentase" value="0">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Nama Skema Komponen</label>
                    <input type="text" id="skemaKompensasiNama" placeholder="Contoh: Tunjangan Makan" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Sifat Komponen</label>
                    <select id="skemaKompensasiSifat" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white;">
                        <option value="tetap">Komponen Tetap</option>
                        <option value="tidak_tetap">Komponen Tidak Tetap</option>
                    </select>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label>Sumber Nilai</label>
                        <select id="skemaKompensasiSumber" onchange="handleSchemeSumberNilaiChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white;">
                            <option value="ump">UMP (Provinsi)</option>
                            <option value="umk">UMK (Kota/Kabupaten)</option>
                            <option value="nominal" selected>Nominal Custom</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Periode</label>
                        <select id="skemaKompensasiPeriode" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; background: white;">
                            <option value="bulan" selected>Per Bulan</option>
                            <option value="minggu">Per Minggu</option>
                            <option value="hari_kerja">Per Hari Kerja</option>
                            <option value="tahun">Per Tahun</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label id="labelNilaiSkema">Nominal Custom (Rp)</label>
                    <input type="text" id="skemaKompensasiNilai" placeholder="Contoh: 200000" onkeyup="formatRupiahInput(this)" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalSkemaKompensasi()">Batal</button>
                <button type="submit" class="btn-save" style="background: var(--primary-color);">Simpan Skema</button>
            </div>
        </form>
    </div>

    <!-- Modal Komponen Kompensasi (Master) -->
    <div id="modalKomponenKompensasi" class="modal-skema" style="display: none;">
        <div class="modal-header" style="background: #10b981;">
            <h3 id="modalKomponenKompensasiTitle">Tambah Komponen</h3>
            <i class="fas fa-times" style="cursor: pointer;" onclick="tutupModalKomponenKompensasi()"></i>
        </div>
        <form id="formKomponenKompensasi">
            <div class="modal-body">
                <input type="hidden" id="komponenKompensasiId">
                <input type="hidden" id="komponenKompensasiSchemeId">
                
                <div class="form-group">
                    <label>Jenis Komponen</label>
                    <select id="komponenKompensasiJenis" onchange="handleJenisKomponenChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="basic_salary">Basic Salary</option>
                        <option value="kompensasi" selected>Komponen Tetap / Tidak Tetap</option>
                    </select>
                </div>

                <!-- Tampilkan Tipe Kompensasi jika jenisnya Kompensasi -->
                <div class="form-group" id="containerSifatKompensasi" style="display: block; margin-bottom: 15px;">
                    <label>Tipe Komponen</label>
                    <select id="komponenKompensasiSifat" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                        <option value="tetap">Komponen Tetap</option>
                        <option value="tidak_tetap">Komponen Tidak Tetap</option>
                    </select>
                </div>


                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label>Sumber Nilai</label>
                        <select id="komponenKompensasiSumber" onchange="handleSumberNilaiChange()" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="ump">UMP (Provinsi)</option>
                            <option value="umk">UMK (Kota/Kabupaten)</option>
                            <option value="nominal" selected>Nominal Custom</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="containerFormatNilai" style="display: none;">
                        <label>Format Nilai</label>
                            <select id="komponenKompensasiIsPersentase" onchange="handleKomponenKompensasiNilaiInput(document.getElementById('komponenKompensasiNilai'))" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                             <option value="0" selected>Rupiah (Rp)</option>
                             <option value="1">Persentase (%)</option>
                         </select>
                    </div>
                </div>

                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label id="labelNilaiKompensasi">Nominal Custom (Rp)</label>
                        <input type="text" id="komponenKompensasiNilai" placeholder="Contoh: 5000000" value="0" onkeyup="handleKomponenKompensasiNilaiInput(this)" required>
                    </div>

                    <div class="form-group">
                        <label>Periode / Siklus</label>
                        <select id="komponenKompensasiPeriode" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px;">
                            <option value="hari">Hari</option>
                            <option value="minggu">Minggu</option>
                            <option value="bulan" selected>Bulan</option>
                            <option value="tahun">Tahun</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="tutupModalKomponenKompensasi()">Batal</button>
                <button type="submit" class="btn-save" style="background: #10b981;">Simpan Komponen</button>
            </div>
        </form>
    </div>

    <!-- Modal Pilih Skema (Pop-up Baru) -->
    <div id="overlayPilihSkema" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;" onclick="tutupModalPilihSkema()"></div>
    <div id="modalPilihSkema" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 700px; max-width: 90%; background: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 2001; overflow: hidden; font-family: 'Inter', sans-serif;">
        <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
            <h3 id="modalPilihSkemaTitle" style="margin: 0; font-size: 18px; font-weight: 600; color: white;">Pilih Skema</h3>
            <i class="fas fa-times" style="cursor: pointer; color: white;" onclick="tutupModalPilihSkema()"></i>
        </div>
        <div class="modal-body" style="padding: 20px; max-height: 400px; overflow-y: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1; color: #475569; background: #e2e8f0;">
                        <th style="padding: 10px 8px; text-align: center; width: 60px;">Pilih</th>
                        <th style="padding: 10px 8px; text-align: left; width: 35%;">Nama Skema</th>
                        <th style="padding: 10px 8px; text-align: left; width: 60%;">Komponen</th>
                    </tr>
                </thead>
                <tbody id="modalPilihSkemaBody">
                    <!-- Dynamically populated -->
                </tbody>
            </table>
        </div>
        <div class="modal-footer" style="padding: 15px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <button type="button" class="btn-cancel" onclick="tutupModalPilihSkema()" style="margin: 0; padding: 10px 20px;">Batal</button>
            <button type="button" class="btn-save" onclick="terapkanPilihanSkema()" style="margin: 0; padding: 10px 20px; background: #0d6efd; color: white;">Terapkan</button>
        </div>
    </div>

    <!-- Custom Confirm Dialog -->
    <div id="confirmOverlay" class="confirm-overlay"></div>
    <div id="confirmDialog" class="confirm-dialog">
        <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h3 id="confirmTitle">Konfirmasi</h3>
        <p id="confirmMessage">Apakah Anda yakin?</p>
        <div class="confirm-actions">
            <button id="confirmCancel" class="btn-cancel">Batal</button>
            <button id="confirmOk" class="btn-save" style="background: var(--danger); color: white; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 600; cursor: pointer;">Ya, Lanjutkan</button>
        </div>
    </div>
