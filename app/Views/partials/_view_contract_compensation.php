<div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
    <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
        <div>
            <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Kompensasi Akhir Kontrak PKWT</h3>
            <p style="color: #64748b; font-size: 13px; margin: 0;">Kelola kompensasi akhir kontrak PKWT berdasarkan basic salary tanpa tunjangan.</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <?php if (in_array($_COOKIE['user_role'] ?? '', ['admin', 'hc_ops'])): ?>
            <button class="btn-add" onclick="bukaModalHitungKompensasi()" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; padding: 10px 18px; border: none; cursor: pointer; transition: all 0.2s ease;">
                <i class="fas fa-calculator"></i> Hitung Kompensasi
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
        <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="text-align: left; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Karyawan</th>
                    <th style="text-align: left; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Masa Kontrak</th>
                    <th style="text-align: center; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Masa Kerja</th>
                    <th style="text-align: right; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Gaji Pokok</th>
                    <th style="text-align: center; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Multiplier</th>
                    <th style="text-align: right; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Nilai Kompensasi</th>
                    <th style="text-align: right; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Nilai Final</th>
                    <th style="text-align: center; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Status</th>
                    <th style="text-align: center; padding: 16px; color: #475569; font-weight: 600; font-size: 14px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tabelKompensasiKontrakBody">
                <tr>
                    <td colspan="9" style="text-align: center; padding: 30px; color: #64748b;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 20px; margin-bottom: 10px; color: var(--primary-color);"></i>
                        <br>Memuat data kompensasi...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
