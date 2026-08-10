/**
 * Module: Tax Education & Policy Hub (Menu Pajak)
 * Berisi informasi & panduan perpajakan yang 100% terimplementasi di aplikasi BiPayroll.
 */

// Data Artikel Edukasi & Kebijakan Pajak yang Benar-Benar Terimplementasi di Sistem
const TAX_ARTICLES_DATA = [
    {
        id: 'ter_pp58',
        category: 'ter',
        categoryLabel: 'TER PP 58/2023',
        badgeColor: '#f39c12',
        icon: 'fas fa-percentage',
        title: 'Skema Tarif Efektif Rata-Rata (TER) Bulanan (Januari – November)',
        summary: 'Sistem memotong PPh 21 bulanan (Jan–Nov) menggunakan Tarif Efektif Rata-Rata (TER) Kategori A, B, dan C berdasarkan status PTKP karyawan.',
        content: `
            <div style="background: #fffbe6; border: 1px solid #fed7aa; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; color: #92400e; font-size: 15px; font-weight: 700;">💡 Formula Bruto Pajak TER di Aplikasi (PMK 168/2023)</h4>
                <p style="margin: 0; color: #78350f; font-size: 13.5px; line-height: 1.5;">
                    Pada aplikasi ini, <strong>Penghasilan Bruto Pajak TER</strong> dihitung dari:<br>
                    <code>Bruto Pajak = Gaji Pokok + Tunjangan (Transport, Makan, Kehadiran, Jabatan, Kinerja) + Lembur + Premi JKK (0.24%) + Premi JKM (0.30%)</code><br><br>
                    <em>Catatan: Sesuai PMK 168/2023, premi BPJS Kesehatan perusahaan (4%), JHT perusahaan (3.7%), dan JP perusahaan (2%) BUKAN objek PPh 21 (tidak menambah Bruto TER).</em>
                </p>
            </div>

            <h4 style="color: #92400e; font-size: 15px; font-weight: 700; margin: 18px 0 10px 0;">🏷️ Pembagian Kategori TER Berdasarkan Status PTKP:</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 20px;">
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px;">
                    <span style="background: #3b82f6; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">KATEGORI A</span>
                    <h5 style="margin: 8px 0 4px 0; color: #1e293b; font-size: 13.5px;">Status PTKP:</h5>
                    <p style="margin: 0; color: #475569; font-size: 13px; font-weight: 700;">TK/0, TK/1, K/0</p>
                </div>
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px;">
                    <span style="background: #d97706; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">KATEGORI B</span>
                    <h5 style="margin: 8px 0 4px 0; color: #1e293b; font-size: 13.5px;">Status PTKP:</h5>
                    <p style="margin: 0; color: #475569; font-size: 13px; font-weight: 700;">TK/2, TK/3, K/1, K/2</p>
                </div>
                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px;">
                    <span style="background: #dc2626; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">KATEGORI C</span>
                    <h5 style="margin: 8px 0 4px 0; color: #1e293b; font-size: 13.5px;">Status PTKP:</h5>
                    <p style="margin: 0; color: #475569; font-size: 13px; font-weight: 700;">K/3</p>
                </div>
            </div>

            <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 14px 16px; border-radius: 0 8px 8px 0;">
                <h5 style="margin: 0 0 6px 0; color: #1e40af; font-size: 13.5px; font-weight: 700;">📌 Cara Kerja Pemotongan Jan – Nov:</h5>
                <p style="margin: 0; color: #1e3a8a; font-size: 13px;">Sistem mencocokkan total Bruto Pajak dengan matriks TER Kategori sesuai PTKP karyawan, lalu mengalikan Bruto Pajak dengan % TER yang didapat. PPh 21 langsung memotong Gaji Bersih (THP).</p>
            </div>
        `
    },
    {
        id: 'rekonsiliasi_desember',
        category: 'progresif',
        categoryLabel: 'Pajak Progresif',
        badgeColor: '#8b5cf6',
        icon: 'fas fa-sync-alt',
        title: 'Rekonsiliasi PPh 21 Bulan Desember (Pasal 17 Progresif Tahunan)',
        summary: 'Khusus pada periode Desember, sistem secara otomatis menghitung ulang PPh 21 setahun penuh menggunakan Tarif Progresif Pasal 17 UU HPP.',
        content: `
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; color: #14532d; font-size: 15px; font-weight: 700;">🔄 Alur Rekonsiliasi Otomatis Desember di Sistem</h4>
                <p style="margin: 0; color: #166534; font-size: 13.5px; line-height: 1.5;">
                    Pada bulan Desember, modul kalkulasi sistem (` + '`calculateDecemberTaxReconciliation`' + `) menjalankan perhitungan akhir:
                </p>
            </div>

            <ol style="margin: 0 0 20px 0; padding-left: 20px; color: #334155; font-size: 13.5px; line-height: 1.7;">
                <li><strong>Hitung Total Bruto Setahun:</strong> Menjumlahkan bruto dari bulan Jan s/d Nov ditambahi bruto bulan Desember.</li>
                <li><strong>Kurangi Pengurang Pajak Setahun:</strong>
                    <ul style="margin: 4px 0 8px 0; padding-left: 18px; color: #64748b;">
                        <li>Biaya Jabatan: 5% dari Bruto Setahun (maksimum Rp 6.000.000 / tahun).</li>
                        <li>Iuran JHT Karyawan (2%) & Iuran JP Karyawan (1%) setahun.</li>
                    </ul>
                </li>
                <li><strong>Kurangi PTKP Tahunan:</strong> Sesuai status PTKP karyawan (TK/0 = Rp 54 jt, K/0 = Rp 58.5 jt, K/1 = Rp 63 jt, dll).</li>
                <li><strong>Hitung PPh 21 Tahunan (Pasal 17):</strong>
                    <ul style="margin: 4px 0 8px 0; padding-left: 18px; color: #64748b;">
                        <li>s/d Rp 60.000.000 = 5%</li>
                        <li>> Rp 60.000.000 s/d Rp 250.000.000 = 15%</li>
                        <li>> Rp 250.000.000 s/d Rp 500.000.000 = 25%</li>
                        <li>> Rp 500.000.000 s/d Rp 5.000.000.000 = 30%</li>
                        <li>> Rp 5.000.000.000 = 35%</li>
                    </ul>
                </li>
                <li><strong>PPh 21 Bulan Desember:</strong> PPh 21 Tahunan dikurangi total TER yang sudah dipotong (Jan–Nov). Jika potongan Jan–Nov lebih besar dari PPh 21 Tahunan, selisihnya menjadi <strong>Pengembalian Pajak (Tax Refund)</strong> yang menambah THP karyawan di bulan Desember.</li>
            </ol>
        `
    },
    {
        id: 'pph21_pesangon',
        category: 'simulasi',
        categoryLabel: 'Contoh & Pesangon',
        badgeColor: '#d97706',
        icon: 'fas fa-hand-holding-usd',
        title: 'Perhitungan PPh 21 Final Kompensasi Kontrak / Pesangon (PP 68/2009)',
        summary: 'Uang kompensasi pengakhiran kontrak / pesangon dihitung menggunakan tarif PPh 21 Final bertingkat secara otomatis oleh modul Contract Compensation.',
        content: `
            <div style="background: #fcf6e5; border: 1px solid #fef3c7; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; color: #92400e; font-size: 15px; font-weight: 700;">🛡️ Ketentuan Pajak Kompensasi Kontrak di Aplikasi</h4>
                <p style="margin: 0; color: #78350f; font-size: 13.5px; line-height: 1.5;">
                    Pemotongan pajak atas Uang Kompensasi Kontrak / Pesangon pada aplikasi ini dihitung terpisah dari gaji bulanan dan bersifat <strong>Final (PP 68/2009)</strong>.
                </p>
            </div>

            <h4 style="color: #92400e; font-size: 15px; font-weight: 700; margin: 18px 0 10px 0;">📈 Lapisan Tarif PPh 21 Final Pesangon:</h4>
            <div style="overflow-x: auto; margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #f39c12; color: white;">
                            <th style="padding: 10px 14px; text-align: left; border-radius: 6px 0 0 0;">Nilai Kompensasi / Pesangon Bruto</th>
                            <th style="padding: 10px 14px; text-align: center; border-radius: 0 6px 0 0;">Tarif PPh 21 Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px 14px;">Sampai dengan Rp 50.000.000</td>
                            <td style="padding: 10px 14px; text-align: center; font-weight: 700; color: #16a34a;">0% (Bebas Pajak)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <td style="padding: 10px 14px;">Diatas Rp 50.000.000 s/d Rp 100.000.000</td>
                            <td style="padding: 10px 14px; text-align: center; font-weight: 700; color: #2563eb;">5%</td>
                        </tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 10px 14px;">Diatas Rp 100.000.000 s/d Rp 500.000.000</td>
                            <td style="padding: 10px 14px; text-align: center; font-weight: 700; color: #d97706;">15%</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 14px;">Diatas Rp 500.000.000</td>
                            <td style="padding: 10px 14px; text-align: center; font-weight: 700; color: #dc2626;">25%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `
    },
    {
        id: 'pph21_mitra',
        category: 'kebijakan',
        categoryLabel: 'Perhitungan Pajak Mitra',
        badgeColor: '#059669',
        icon: 'fas fa-user-tag',
        title: 'Perhitungan Pajak PPh 21 Mitra / Bukan Pegawai (DPP 50%)',
        summary: 'Untuk skema pajak Bukan Pegawai / Tenaga Ahli / Kemitraan, sistem menerapkan Dasar Pengenaan Pajak (DPP) sebesar 50% dari Fee Bruto.',
        content: `
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; color: #14532d; font-size: 15px; font-weight: 700;">🤝 Formula Pajak Mitra di Aplikasi</h4>
                <p style="margin: 0; color: #166534; font-size: 13.5px; line-height: 1.5;">
                    Pada master skema pajak (` + '`tax_schemes`' + `), kategori <strong>Bukan Pegawai (Tenaga Ahli / Kemitraan)</strong> dikalkulasi menggunakan rumus:<br><br>
                    <code>DPP (Dasar Pengenaan Pajak) = 50% × Fee Kemitraan Bruto</code><br>
                    <code>PPh 21 Dipotong = DPP × Tarif Pajak (TER / Pasal 17)</code>
                </p>
            </div>
        `
    },
    {
        id: 'kebijakan_gross',
        category: 'kebijakan',
        categoryLabel: 'Kebijakan Perusahaan',
        badgeColor: '#059669',
        icon: 'fas fa-building',
        title: 'Kebijakan Pemotongan Pajak Sistem (Gross Method)',
        summary: 'Aplikasi memproses pemotongan PPh 21 menggunakan metode Gross di mana pajak dipotong langsung dari penghasilan bruto karyawan.',
        content: `
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                    <h5 style="margin: 0 0 6px 0; color: #1e293b; font-size: 14px; font-weight: 700;">1. Metode Gross Ter-enforce di Sistem</h5>
                    <p style="margin: 0; color: #64748b; font-size: 13px;">Seluruh kalkulasi payroll di backend (` + '`Payroll.php`' + `) meng-enforce metode <strong>Gross</strong>. Pemotongan PPh 21 dihitung dari bruto dan dikurangi secara otomatis untuk menentukan <strong>Take Home Pay (THP)</strong> akhir di slip gaji.</p>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                    <h5 style="margin: 0 0 6px 0; color: #1e293b; font-size: 14px; font-weight: 700;">2. Integrasi Data Presensi & Lembur ke Bruto Pajak</h5>
                    <p style="margin: 0; color: #64748b; font-size: 13px;">Tunjangan harian presensi (makan/transport), lembur PP 35/2021, serta insentif KPI secara otomatis menambahkan Bruto Pajak, sedangkan potongan alpa/keterlambatan mengurangi Bruto Pajak sebelum dikenakan TER.</p>
                </div>
            </div>
        `
    }
];

// Current filter state
let currentTaxCategoryFilter = 'all';

// Initialize Tax Hub Event Listeners & UI
document.addEventListener('DOMContentLoaded', () => {
    renderTaxArticles();
});

// Render Article Grid based on Search & Category Filter
function renderTaxArticles() {
    const container = document.getElementById('taxArticlesContainer');
    if (!container) return;

    const searchInput = document.getElementById('searchTaxArticlesInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';

    const filtered = TAX_ARTICLES_DATA.filter(item => {
        const matchesCategory = (currentTaxCategoryFilter === 'all') || (item.category === currentTaxCategoryFilter);
        const matchesQuery = query === '' || 
            item.title.toLowerCase().includes(query) || 
            item.summary.toLowerCase().includes(query) ||
            item.categoryLabel.toLowerCase().includes(query);
        return matchesCategory && matchesQuery;
    });

    if (filtered.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 16px;">
                <i class="fas fa-search" style="font-size: 32px; color: #94a3b8; margin-bottom: 12px;"></i>
                <h4 style="margin: 0 0 6px 0; color: #334155; font-size: 16px; font-weight: 700;">Topik tidak ditemukan</h4>
                <p style="margin: 0; color: #64748b; font-size: 13px;">Coba gunakan kata kunci pencarian lain atau ubah kategori filter.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = filtered.map(item => `
        <div class="tax-article-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.25s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.02);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 20px -5px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.02)';">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="background: ${item.badgeColor}15; color: ${item.badgeColor}; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="${item.icon}"></i> ${item.categoryLabel}
                    </span>
                    <i class="fas fa-check-circle" style="color: #10b981; font-size: 14px;" title="Terimplementasi Resmi"></i>
                </div>
                <h4 style="margin: 0 0 8px 0; font-size: 15.5px; font-weight: 700; color: #1e293b; line-height: 1.4;">${item.title}</h4>
                <p style="margin: 0 0 16px 0; color: #64748b; font-size: 13px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">${item.summary}</p>
            </div>
            <button type="button" onclick="openTaxArticleModal('${item.id}')" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; color: #1e293b; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.background='#f39c12'; this.style.color='white'; this.style.borderColor='#f39c12';" onmouseout="this.style.background='#f8fafc'; this.style.color='#1e293b'; this.style.borderColor='#cbd5e1';">
                <span>Baca Selengkapnya</span>
                <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
            </button>
        </div>
    `).join('');
}

// Filter Categories
function filterTaxCategory(cat, btnElem) {
    currentTaxCategoryFilter = cat;
    document.querySelectorAll('.tax-cat-badge').forEach(b => {
        b.style.background = 'white';
        b.style.color = '#475569';
        b.style.borderColor = '#cbd5e1';
    });
    if (btnElem) {
        btnElem.style.background = '#f39c12';
        btnElem.style.color = 'white';
        btnElem.style.borderColor = '#f39c12';
    }
    renderTaxArticles();
}

// Open Article Detail Modal
function openTaxArticleModal(articleId) {
    const article = TAX_ARTICLES_DATA.find(a => a.id === articleId);
    if (!article) return;

    document.getElementById('taxModalTitle').innerText = article.title;
    document.getElementById('taxModalBadge').innerText = article.categoryLabel;
    document.getElementById('taxModalIcon').className = article.icon;
    document.getElementById('taxModalBodyContent').innerHTML = article.content;

    const modal = document.getElementById('modalTaxDetail');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeTaxDetailModal() {
    const modal = document.getElementById('modalTaxDetail');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Conceptual Tax Simulation Widget
function calculateTaxEduSimulation() {
    const bruto = parseFloat(document.getElementById('simTaxBruto').value) || 0;
    const ptkpStatus = document.getElementById('simTaxPtkp').value || 'TK/0';

    if (bruto <= 0) {
        document.getElementById('simTaxResultContainer').style.display = 'none';
        return;
    }

    // Determine TER Category
    let category = 'A';
    if (['TK/2', 'TK/3', 'K/1', 'K/2'].includes(ptkpStatus)) {
        category = 'B';
    } else if (ptkpStatus === 'K/3') {
        category = 'C';
    }

    // TER Rate Calculation approximation based on PMK 168/2023
    let terRate = 0;
    if (category === 'A') {
        if (bruto <= 5400000) terRate = 0;
        else if (bruto <= 5650000) terRate = 0.25;
        else if (bruto <= 5950000) terRate = 0.50;
        else if (bruto <= 6300000) terRate = 0.75;
        else if (bruto <= 6750000) terRate = 1.00;
        else if (bruto <= 7500000) terRate = 1.25;
        else if (bruto <= 8550000) terRate = 1.50;
        else if (bruto <= 9650000) terRate = 1.75;
        else if (bruto <= 10050000) terRate = 2.00;
        else if (bruto <= 10350000) terRate = 2.25;
        else terRate = 3.00;
    } else if (category === 'B') {
        if (bruto <= 6200000) terRate = 0;
        else if (bruto <= 6500000) terRate = 0.25;
        else if (bruto <= 6850000) terRate = 0.50;
        else if (bruto <= 7300000) terRate = 0.75;
        else if (bruto <= 9200000) terRate = 1.50;
        else if (bruto <= 10750000) terRate = 2.00;
        else terRate = 3.50;
    } else { // Category C
        if (bruto <= 6600000) terRate = 0;
        else if (bruto <= 6950000) terRate = 0.25;
        else if (bruto <= 7350000) terRate = 0.50;
        else if (bruto <= 11000000) terRate = 1.75;
        else terRate = 4.00;
    }

    const pphEst = bruto * (terRate / 100);
    const thpEst = bruto - pphEst;

    document.getElementById('simResCategory').innerText = `Kategori ${category}`;
    document.getElementById('simResRate').innerText = `${terRate.toFixed(2)}%`;
    document.getElementById('simResPph').innerText = `Rp ${Math.round(pphEst).toLocaleString('id-ID')}`;
    document.getElementById('simResThp').innerText = `Rp ${Math.round(thpEst).toLocaleString('id-ID')}`;
    document.getElementById('simTaxResultContainer').style.display = 'block';
}

// Global Export Functions
window.filterTaxArticles = renderTaxArticles;
window.filterTaxCategory = filterTaxCategory;
window.openTaxArticleModal = openTaxArticleModal;
window.closeTaxDetailModal = closeTaxDetailModal;
window.calculateTaxEduSimulation = calculateTaxEduSimulation;
