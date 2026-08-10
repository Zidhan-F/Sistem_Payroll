<!-- Section: Pusat Informasi Pajak (Standalone View) -->
<div id="viewPusatPajak" class="view-section">
    <!-- Header Banner in Warm Orange Theme -->
    <div style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); border-radius: 16px; padding: 28px 32px; color: white; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(243, 156, 18, 0.4); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="max-width: 650px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.22); padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 12px; backdrop-filter: blur(4px);">
                <i class="fas fa-landmark"></i> PUSAT INFORMASI & EDUKASI PERPAJAKAN
            </div>
            <h2 style="margin: 0 0 8px 0; font-size: 25px; font-weight: 800; color: white; letter-spacing: -0.5px;">Edukasi & Kebijakan Perpajakan Perusahaan</h2>
            <p style="margin: 0; font-size: 14px; opacity: 0.95; line-height: 1.5; color: #fff7ed;">
                Pusat referensi resmi mengenai skema Tarif Efektif Rata-Rata (TER PP 58/2023), kalkulasi PPh 21 progresif, PPh 21 pesangon final, serta kebijakan dan administrasi perpajakan internal perusahaan.
            </p>
        </div>
        <div style="background: rgba(255,255,255,0.18); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.3); border-radius: 12px; padding: 14px 20px; text-align: center; min-width: 180px;">
            <i class="fas fa-file-invoice-dollar" style="font-size: 24px; margin-bottom: 6px; color: #fef3c7;"></i>
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #ffedd5; letter-spacing: 0.5px;">Status Modul</div>
            <div style="font-size: 14px; font-weight: 800; color: #ffffff;">Informatif / Read-Only</div>
        </div>
    </div>

    <!-- Quick Search & Category Filter Bar -->
    <div class="content-card" style="border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; background: white; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <!-- Search Bar -->
            <div style="position: relative; flex: 1; min-width: 280px;">
                <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                <input type="text" id="searchTaxArticlesInput" placeholder="Cari topik pajak (misal: TER, Progresif, Pesangon, Mitra, Gross)..." onkeyup="filterTaxArticles()" style="width: 100%; padding: 11px 16px 11px 40px; border: 1.5px solid #cbd5e1; border-radius: 10px; font-size: 14px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#f39c12'" onblur="this.style.borderColor='#cbd5e1'">
            </div>
            <!-- Category Badges -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="tax-cat-badge active" onclick="filterTaxCategory('all', this)" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #f39c12; background: #f39c12; color: white; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                    Semua Topik
                </button>
                <button type="button" class="tax-cat-badge" onclick="filterTaxCategory('ter', this)" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; color: #475569; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-percentage" style="color: #f39c12; margin-right: 4px;"></i> TER PP 58/2023
                </button>
                <button type="button" class="tax-cat-badge" onclick="filterTaxCategory('progresif', this)" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; color: #475569; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-sync-alt" style="color: #8b5cf6; margin-right: 4px;"></i> Rekonsiliasi Des
                </button>
                <button type="button" class="tax-cat-badge" onclick="filterTaxCategory('simulasi', this)" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; color: #475569; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-hand-holding-usd" style="color: #d97706; margin-right: 4px;"></i> Pesangon Final
                </button>
                <button type="button" class="tax-cat-badge" onclick="filterTaxCategory('kebijakan', this)" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; background: white; color: #475569; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-building" style="color: #059669; margin-right: 4px;"></i> Kebijakan System
                </button>
            </div>
        </div>
    </div>

    <!-- Conceptual Calculator Widget Card in Orange Theme -->
    <div style="background: linear-gradient(135deg, #fffbe6 0%, #fff7ed 100%); border: 1px solid #fed7aa; border-radius: 16px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(243, 156, 18, 0.06);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 8px; background: #f39c12; color: white; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="fas fa-calculator"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #92400e;">Kalkulator Simulasi Konseptual TER (Edukasi)</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12.5px; color: #78350f;">Simulasi cepat estimasi persentase TER bulanan dan dampak terhadap THP (sesuai modul kalkulasi PMK 168/2023 sistem).</p>
                </div>
            </div>
            <span style="background: #ffedd5; color: #c2410c; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">PMK 168/2023 Presisi</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: flex-end;">
            <div>
                <label style="font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Estimasi Gaji Bruto Bulanan (Rp)</label>
                <input type="number" id="simTaxBruto" placeholder="Contoh: 8000000" oninput="calculateTaxEduSimulation()" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: white;">
            </div>
            <div>
                <label style="font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Status Tanggungan (PTKP)</label>
                <select id="simTaxPtkp" onchange="calculateTaxEduSimulation()" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: white;">
                    <option value="TK/0">TK/0 (Tidak Kawin - 0 Tanggungan)</option>
                    <option value="TK/1">TK/1 (Tidak Kawin - 1 Tanggungan)</option>
                    <option value="TK/2">TK/2 (Tidak Kawin - 2 Tanggungan)</option>
                    <option value="TK/3">TK/3 (Tidak Kawin - 3 Tanggungan)</option>
                    <option value="K/0">K/0 (Kawin - 0 Tanggungan)</option>
                    <option value="K/1">K/1 (Kawin - 1 Tanggungan)</option>
                    <option value="K/2">K/2 (Kawin - 2 Tanggungan)</option>
                    <option value="K/3">K/3 (Kawin - 3 Tanggungan)</option>
                </select>
            </div>
            <button type="button" onclick="calculateTaxEduSimulation()" style="padding: 10px 20px; background: #f39c12; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 13.5px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#e67e22'" onmouseout="this.style.background='#f39c12'">
                Hitung Simulasi
            </button>
        </div>

        <!-- Result Box -->
        <div id="simTaxResultContainer" style="display: none; margin-top: 18px; padding-top: 16px; border-top: 1px dashed #fed7aa;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; background: white; padding: 14px; border-radius: 10px; border: 1px solid #fed7aa;">
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Kategori TER</span>
                    <h4 id="simResCategory" style="margin: 2px 0 0 0; font-size: 16px; font-weight: 800; color: #92400e;">-</h4>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Tarif Efektif TER</span>
                    <h4 id="simResRate" style="margin: 2px 0 0 0; font-size: 16px; font-weight: 800; color: #d97706;">-</h4>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Estimasi PPh 21 Sebulan</span>
                    <h4 id="simResPph" style="margin: 2px 0 0 0; font-size: 16px; font-weight: 800; color: #dc2626;">-</h4>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase;">Estimasi THP Bersih</span>
                    <h4 id="simResThp" style="margin: 2px 0 0 0; font-size: 16px; font-weight: 800; color: #16a34a;">-</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Article Cards Grid -->
    <div id="taxArticlesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Articles dynamically populated by app-tax-edu.js -->
    </div>
</div>
