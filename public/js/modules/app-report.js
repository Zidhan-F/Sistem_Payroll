/**
 * Client Payroll Report Module (Month-on-Month Summary & Visualization)
 */

window.reportState = {
    clients: [],
    data: [],
    summary: {},
    chartMom: null,
    chartComposition: null,
    selectedClient: 'all',
    selectedTahun: 'all',
    selectedMetric: 'total_thp'
};

document.addEventListener('DOMContentLoaded', function() {
    const activeView = localStorage.getItem('activeView');
    if (activeView === 'laporanGaji') {
        loadPayrollReport();
    }
});

// Hook into view switching if needed
const originalSwitchView = window.switchView;
if (typeof originalSwitchView === 'function') {
    window.switchView = function(view) {
        originalSwitchView(view);
        if (view === 'laporanGaji') {
            loadPayrollReport();
        }
    };
}

/**
 * Fetch payroll report data from backend API
 */
async function loadPayrollReport(overrideClientId = null) {
    try {
        let clientFilter = overrideClientId || window.selectedClientId || 'all';

        const selectClientEl = document.getElementById('filterReportClient');
        if (selectClientEl) {
            if (overrideClientId || window.selectedClientId) {
                selectClientEl.value = overrideClientId || window.selectedClientId;
            }
            clientFilter = selectClientEl.value || clientFilter;
        }

        const selectTahunEl = document.getElementById('filterReportTahun');
        const tahunFilter = selectTahunEl ? selectTahunEl.value : (window.reportState.selectedTahun || 'all');

        window.reportState.selectedClient = clientFilter;
        window.reportState.selectedTahun = tahunFilter;

        showToast('Memuat data laporan gaji...', 'info');

        const queryParams = new URLSearchParams({
            client_id: clientFilter,
            tahun: tahunFilter
        });

        const response = await fetch(`${window.API}/reports/payroll-summary?${queryParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.status === 'success') {
            window.reportState.clients = result.clients || [];
            window.reportState.data = result.data || [];
            window.reportState.summary = result.summary || {};

            populateReportClientOptions(clientFilter);
            renderReportKpiCards();
            renderReportCharts();
            renderReportTable();
            showToast('Laporan gaji berhasil diperbarui', 'success');
        } else {
            showToast(result.message || 'Gagal memuat data laporan gaji', 'error');
        }
    } catch (err) {
        console.error('Error loading payroll report:', err);
        showToast('Terjadi kesalahan saat memuat laporan gaji', 'error');
    }
}

/**
 * Populate Client Dropdown Filter
 */
function populateReportClientOptions(activeClientId = null) {
    const select = document.getElementById('filterReportClient');
    if (!select) return;

    const targetVal = activeClientId || window.selectedClientId || select.value || 'all';
    let html = '<option value="all">Semua Klien</option>';
    (window.reportState.clients || []).forEach(c => {
        html += `<option value="${c.id}">${escapeHtml(c.nama)}</option>`;
    });
    select.innerHTML = html;
    select.value = targetVal;
}

/**
 * Render Top KPI Cards
 */
function renderReportKpiCards() {
    const summary = window.reportState.summary || {};
    const data = window.reportState.data || [];

    const elThp = document.getElementById('kpiReportTotalThp');
    const elHeadcount = document.getElementById('kpiReportHeadcount');
    const elAvg = document.getElementById('kpiReportAvgSalary');
    const elMom = document.getElementById('kpiReportMomGrowth');

    if (elThp) elThp.innerText = formatRupiah(summary.total_thp || 0);
    if (elHeadcount) elHeadcount.innerText = (summary.total_headcount || 0) + ' Orang';
    if (elAvg) elAvg.innerText = formatRupiah(summary.avg_thp_per_employee || 0);

    if (elMom) {
        if (data.length > 1) {
            const lastItem = data[data.length - 1];
            const growth = lastItem.mom_growth_percent || 0;
            const isPos = growth >= 0;
            elMom.innerHTML = `
                <span style="color: ${isPos ? '#10b981' : '#ef4444'}; font-weight: 700;">
                    <i class="fas fa-arrow-${isPos ? 'up' : 'down'}"></i> ${growth > 0 ? '+' : ''}${growth}%
                </span>
                <span style="font-size: 12px; color: #64748b; margin-left: 4px;">MoM</span>
            `;
        } else {
            elMom.innerText = '0.00% MoM';
        }
    }
}

/**
 * Render Charts (Month-on-Month Line Chart & Component Bar Chart)
 */
function renderReportCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js library not loaded yet.');
        return;
    }

    const data = window.reportState.data || [];
    const metricKey = document.getElementById('filterReportMetric') ? document.getElementById('filterReportMetric').value : 'total_thp';
    window.reportState.selectedMetric = metricKey;

    if (window.reportState.chartMom) {
        window.reportState.chartMom.destroy();
        window.reportState.chartMom = null;
    }
    if (window.reportState.chartComposition) {
        window.reportState.chartComposition.destroy();
        window.reportState.chartComposition = null;
    }

    const ctxMom = document.getElementById('chartMomPayroll');
    const ctxComp = document.getElementById('chartPayrollComposition');

    if (!data.length) {
        if (ctxMom) {
            const ctx = ctxMom.getContext('2d');
            ctx.clearRect(0, 0, ctxMom.width, ctxMom.height);
        }
        return;
    }

    const periodLabelsSet = new Set();
    data.forEach(item => periodLabelsSet.add(item.bulan_tahun_label));
    const labels = Array.from(periodLabelsSet);

    const clientDataMap = {};
    const palette = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#ef4444', '#64748b'];

    data.forEach(item => {
        const cName = item.client_name;
        if (!clientDataMap[cName]) {
            clientDataMap[cName] = {};
        }
        clientDataMap[cName][item.bulan_tahun_label] = item[metricKey] || 0;
    });

    const datasetsMom = Object.keys(clientDataMap).map((cName, idx) => {
        const color = palette[idx % palette.length];
        const seriesData = labels.map(lbl => clientDataMap[cName][lbl] || 0);
        return {
            label: cName,
            data: seriesData,
            borderColor: color,
            backgroundColor: color + '22',
            tension: 0.35,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7,
            borderWidth: 3
        };
    });

    // 1. Render Line Chart (MoM Trend)
    if (ctxMom) {
        window.reportState.chartMom = new Chart(ctxMom, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasetsMom
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: 600 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (metricKey === 'total_karyawan') {
                                    label += context.parsed.y + ' Orang';
                                } else {
                                    label += formatRupiah(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        ticks: {
                            callback: function(val) {
                                if (metricKey === 'total_karyawan') return val + ' Orang';
                                if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' B';
                                if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                                if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' Rb';
                                return 'Rp ' + val;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Render Stacked Bar Chart (Gaji Pokok vs Tunjangan vs Potongan per Period)
    if (ctxComp) {
        const gpData = labels.map(lbl => {
            const items = data.filter(d => d.bulan_tahun_label === lbl);
            return items.reduce((acc, curr) => acc + (curr.total_gaji_pokok || 0), 0);
        });

        const tunjData = labels.map(lbl => {
            const items = data.filter(d => d.bulan_tahun_label === lbl);
            return items.reduce((acc, curr) => acc + Math.max(0, (curr.total_pendapatan || 0) - (curr.total_gaji_pokok || 0)), 0);
        });

        const potData = labels.map(lbl => {
            const items = data.filter(d => d.bulan_tahun_label === lbl);
            return items.reduce((acc, curr) => acc + (curr.total_potongan || 0), 0);
        });

        window.reportState.chartComposition = new Chart(ctxComp, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gaji Pokok',
                        data: gpData,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    },
                    {
                        label: 'Tunjangan & Lembur',
                        data: tunjData,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Potongan / Denda',
                        data: potData,
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: 600 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return (context.dataset.label || '') + ': ' + formatRupiah(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: {
                        stacked: true,
                        ticks: {
                            callback: function(val) {
                                if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + ' B';
                                if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' Jt';
                                if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' Rb';
                                return 'Rp ' + val;
                            }
                        }
                    }
                }
            }
        });
    }
}

/**
 * Render Summary Table
 */
function renderReportTable() {
    const container = document.getElementById('tableReportPayrollBody');
    if (!container) return;

    const data = window.reportState.data || [];

    if (!data.length) {
        container.innerHTML = `
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-folder-open" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                    Belum ada data gaji karyawan untuk periode / klien yang dipilih.
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    data.forEach((item, index) => {
        const growth = item.mom_growth_percent || 0;
        const diffAmt = item.mom_diff_amount || 0;
        const isPos = growth > 0;
        const isNeg = growth < 0;

        let badgeClass = 'background: #f1f5f9; color: #475569;';
        let badgeIcon = 'minus';
        if (isPos) {
            badgeClass = 'background: rgba(16, 185, 129, 0.1); color: #059669;';
            badgeIcon = 'arrow-up';
        } else if (isNeg) {
            badgeClass = 'background: rgba(239, 68, 68, 0.1); color: #dc2626;';
            badgeIcon = 'arrow-down';
        }

        const tunjEst = Math.max(0, (item.total_pendapatan || 0) - (item.total_gaji_pokok || 0));

        html += `
            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                <td style="padding: 14px 16px; text-align: center; font-weight: 600; color: #64748b; white-space: nowrap;">${index + 1}</td>
                <td style="padding: 14px 16px; font-weight: 700; color: #1e293b; white-space: nowrap;">
                    ${escapeHtml(item.client_name)}
                </td>
                <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                    <span style="background: #e0e7ff; color: #3730a3; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; display: inline-block; white-space: nowrap; line-height: 1;">
                        ${escapeHtml(item.bulan_tahun_label)}
                    </span>
                </td>
                <td style="padding: 14px 16px; text-align: center; font-weight: 600; white-space: nowrap;">${item.total_karyawan} orang</td>
                <td style="padding: 14px 16px; text-align: right; font-weight: 500; white-space: nowrap;">${formatRupiah(item.total_gaji_pokok)}</td>
                <td style="padding: 14px 16px; text-align: right; font-weight: 500; color: #059669; white-space: nowrap;">+${formatRupiah(tunjEst)}</td>
                <td style="padding: 14px 16px; text-align: right; font-weight: 500; color: #dc2626; white-space: nowrap;">-${formatRupiah(item.total_potongan)}</td>
                <td style="padding: 14px 16px; text-align: right; font-weight: 800; color: #1e293b; font-size: 14px; white-space: nowrap;">
                    ${formatRupiah(item.total_thp)}
                </td>
                <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                    <span style="padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; ${badgeClass}">
                        <i class="fas fa-${badgeIcon}"></i> ${growth > 0 ? '+' : ''}${growth}%
                    </span>
                </td>
            </tr>
        `;
    });

    container.innerHTML = html;
}

/**
 * Export Laporan to Excel
 */
function exportReportExcel() {
    const data = window.reportState.data || [];
    if (!data.length) {
        showToast('Tidak ada data untuk diekspor', 'warning');
        return;
    }

    const xlsxLib = window.XLSX || window.XLSXStyle || window.XLSXReader;
    if (!xlsxLib) {
        showToast('Library Excel belum dimuat', 'error');
        return;
    }

    const rows = data.map((item, idx) => ({
        'No': idx + 1,
        'Nama Klien': item.client_name,
        'Periode': item.bulan_tahun_label,
        'Jumlah Karyawan': item.total_karyawan,
        'Total Gaji Pokok (Rp)': item.total_gaji_pokok,
        'Total Tunjangan & Lembur (Rp)': Math.max(0, item.total_pendapatan - item.total_gaji_pokok),
        'Total Potongan (Rp)': item.total_potongan,
        'Take Home Pay (THP) (Rp)': item.total_thp,
        'Pertumbuhan MoM (%)': item.mom_growth_percent
    }));

    const ws = xlsxLib.utils.json_to_sheet(rows);
    const wb = xlsxLib.utils.book_new();
    xlsxLib.utils.book_append_sheet(wb, ws, "Laporan Gaji Klien");
    xlsxLib.writeFile(wb, `Laporan_Summary_Gaji_Klien_${dateStr()}.xlsx`);
    showToast('Berhasil mengunduh laporan Excel', 'success');
}

/**
 * Export Laporan to PDF (Multi-page Executive PDF Report)
 */
function exportReportPdf() {
    const element = document.getElementById('viewLaporan') || document.getElementById('viewLaporanGaji');
    if (!element) {
        showToast('Elemen laporan tidak ditemukan', 'error');
        return;
    }

    if (typeof html2pdf === 'undefined') {
        showToast('Library html2pdf belum siap. Menggunakan cetak browser...', 'info');
        window.print();
        return;
    }

    showToast('Menyiapkan file PDF, mohon tunggu...', 'info');

    const opt = {
        margin:       [0.3, 0.3, 0.3, 0.3],
        filename:     `Laporan_Gaji_Klien_Month_on_Month_${dateStr()}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        pagebreak:    { mode: ['css', 'legacy'] },
        html2canvas:  {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff',
            onclone: function(clonedDoc) {
                const target = clonedDoc.getElementById('viewLaporan') || clonedDoc.getElementById('viewLaporanGaji');
                if (!target) return;

                // Client name & year metadata
                let clientName = window.selectedClientName || '';
                if (!clientName || clientName === '-') {
                    const found = (window.reportState.clients || []).find(c => String(c.id) === String(window.reportState.selectedClient));
                    clientName = found ? found.nama : 'Semua Klien';
                }
                const activeTahun = document.getElementById('filterReportTahun') ? document.getElementById('filterReportTahun').value : '2026';

                // Completely remove interactive UI elements (Buttons, action bar, dropdowns, labels) from cloned DOM
                const interactiveNodes = target.querySelectorAll('button, select, label, .report-actions-bar, [onclick*="exportReport"], [onclick*="loadPayrollReport"]');
                interactiveNodes.forEach(node => {
                    if (node && node.parentNode) {
                        node.parentNode.removeChild(node);
                    }
                });

                // Remove filter container bar
                target.querySelectorAll('div').forEach(div => {
                    if (div.innerText && (div.innerText.includes('Tahun Periode:') || div.innerText.includes('Pilih Klien:'))) {
                        if (div.parentNode) {
                            div.parentNode.removeChild(div);
                        }
                    }
                });

                // Inject explicit CSS styles & variables into cloned document
                const style = clonedDoc.createElement('style');
                style.innerHTML = `
                    :root {
                        --primary-color: #f39c12 !important;
                        --primary-dark: #e67e22 !important;
                        --secondary-color: #1e293b !important;
                        --bg-color: #ffffff !important;
                        --white: #ffffff !important;
                        --text-main: #1e293b !important;
                        --text-muted: #64748b !important;
                    }
                    * {
                        animation: none !important;
                        transition: none !important;
                        opacity: 1 !important;
                        filter: none !important;
                        box-sizing: border-box !important;
                    }
                    .report-actions-bar, button, select, label {
                        display: none !important;
                    }
                    body, html {
                        background: #ffffff !important;
                        color: #1e293b !important;
                        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
                    }
                    .pdf-header-banner {
                        display: flex !important;
                        justify-content: space-between !important;
                        align-items: center !important;
                        border-bottom: 2px solid #3b82f6 !important;
                        padding-bottom: 10px !important;
                        margin-bottom: 15px !important;
                    }
                    .pdf-header-title {
                        font-size: 18px !important;
                        font-weight: 800 !important;
                        color: #1e293b !important;
                        margin: 0 0 4px 0 !important;
                    }
                    .pdf-header-sub {
                        font-size: 12px !important;
                        color: #64748b !important;
                        margin: 0 !important;
                    }
                    .pdf-header-meta {
                        text-align: right !important;
                        font-size: 11px !important;
                        color: #64748b !important;
                    }
                    .pdf-page-break {
                        page-break-before: always !important;
                        break-before: page !important;
                    }
                    .pdf-no-break {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }
                    table {
                        width: 100% !important;
                        min-width: 100% !important;
                        table-layout: fixed !important;
                        border-collapse: collapse !important;
                        font-size: 10px !important;
                    }
                    th, td {
                        padding: 7px 6px !important;
                        white-space: nowrap !important;
                        word-break: break-all !important;
                    }
                    th {
                        background-color: #f1f5f9 !important;
                        color: #334155 !important;
                        font-size: 10px !important;
                        font-weight: 700 !important;
                    }
                `;
                clonedDoc.head.appendChild(style);

                // Set fixed printable width for target container
                target.style.width = '1020px';
                target.style.padding = '10px';
                target.style.background = '#ffffff';
                target.style.opacity = '1';
                target.style.transform = 'none';

                target.querySelectorAll('*').forEach(el => {
                    el.style.opacity = '1';
                    el.style.animation = 'none';
                    el.style.transition = 'none';
                    el.style.transform = 'none';
                });

                // Replace top header with Executive PDF Banner
                const contentCard = target.querySelector('.content-card') || target;
                const topHeaderDiv = contentCard.querySelector('div[style*="justify-content: space-between"]');
                
                const bannerDiv = clonedDoc.createElement('div');
                bannerDiv.className = 'pdf-header-banner';
                bannerDiv.innerHTML = `
                    <div>
                        <h1 class="pdf-header-title">LAPORAN SUMMARY GAJI KLIEN (MONTH-ON-MONTH)</h1>
                        <p class="pdf-header-sub">Nama Klien: <strong>${escapeHtml(clientName)}</strong> &nbsp;|&nbsp; Tahun Periode: <strong>${escapeHtml(activeTahun)}</strong></p>
                    </div>
                    <div class="pdf-header-meta">
                        <div>Tanggal Cetak: <strong>${dateStr()}</strong></div>
                        <div>Sistem Payroll Enterprise</div>
                    </div>
                `;

                if (topHeaderDiv) {
                    topHeaderDiv.parentNode.replaceChild(bannerDiv, topHeaderDiv);
                } else {
                    contentCard.insertBefore(bannerDiv, contentCard.firstChild);
                }

                // Format Charts Section (Page 1 fits Header + KPIs + Charts)
                const chartGrid = target.querySelector('div[style*="grid-template-columns"]');
                if (chartGrid) {
                    chartGrid.className = 'pdf-no-break';
                    chartGrid.style.display = 'grid';
                    chartGrid.style.gridTemplateColumns = '1fr 1fr';
                    chartGrid.style.gap = '12px';
                    chartGrid.style.marginBottom = '10px';
                }

                const chartContainers = target.querySelectorAll('div[style*="height: 300px"]');
                chartContainers.forEach(cc => {
                    cc.style.height = '200px';
                });

                // Convert Chart.js canvases to PNG images for 100% sharp rendering
                const origCanvases = element.querySelectorAll('canvas');
                const clonedCanvases = target.querySelectorAll('canvas');
                origCanvases.forEach((origCanvas, i) => {
                    const clonedCanvas = clonedCanvases[i];
                    if (clonedCanvas && origCanvas.width > 0 && origCanvas.height > 0) {
                        try {
                            const img = clonedDoc.createElement('img');
                            img.src = origCanvas.toDataURL('image/png', 1.0);
                            img.style.width = '100%';
                            img.style.height = '200px';
                            img.style.objectFit = 'contain';
                            clonedCanvas.parentNode.replaceChild(img, clonedCanvas);
                        } catch (e) {
                            console.warn('Canvas convert error:', e);
                        }
                    }
                });

                // Format Table Container for Page 2 (Clean page break before table)
                const tableOuterDiv = target.querySelector('div[style*="overflow-x: auto"]') || target.querySelector('table')?.parentNode;
                if (tableOuterDiv) {
                    const tableContainer = tableOuterDiv.parentNode.classList.contains('content-card') ? tableOuterDiv : tableOuterDiv.parentNode;
                    tableContainer.className = 'pdf-page-break';
                    tableContainer.style.pageBreakBefore = 'always';
                    tableContainer.style.breakBefore = 'page';
                    tableContainer.style.marginTop = '15px';
                    
                    tableOuterDiv.style.overflow = 'visible';
                    tableOuterDiv.style.width = '100%';
                }

                const tableEl = target.querySelector('table');
                if (tableEl) {
                    tableEl.style.width = '100%';
                    tableEl.style.minWidth = '100%';
                    tableEl.style.tableLayout = 'fixed';

                    // Set proportional widths for columns
                    const colWidths = ['5%', '18%', '11%', '10%', '14%', '13%', '13%', '16%'];
                    const ths = tableEl.querySelectorAll('thead th');
                    ths.forEach((th, idx) => {
                        if (colWidths[idx]) {
                            th.style.width = colWidths[idx];
                        }
                    });
                }
            }
        },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        showToast('Berhasil mengunduh file PDF', 'success');
    }).catch(err => {
        console.error('PDF export error:', err);
        showToast('Gagal membuat PDF, membuka jendela cetak...', 'warning');
        window.print();
    });
}

function dateStr() {
    const d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

// Global helpers attached to window
window.loadPayrollReport = loadPayrollReport;
window.exportReportExcel = exportReportExcel;
window.exportReportPdf = exportReportPdf;
