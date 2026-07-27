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
    selectedStartDate: '',
    selectedEndDate: '',
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

        const startDateEl = document.getElementById('filterReportStartDate');
        const endDateEl = document.getElementById('filterReportEndDate');
        const startDateFilter = startDateEl ? startDateEl.value : (window.reportState.selectedStartDate || '');
        const endDateFilter = endDateEl ? endDateEl.value : (window.reportState.selectedEndDate || '');

        window.reportState.selectedClient = clientFilter;
        window.reportState.selectedTahun = tahunFilter;
        window.reportState.selectedStartDate = startDateFilter;
        window.reportState.selectedEndDate = endDateFilter;

        showToast('Memuat data laporan gaji...', 'info');

        const queryParams = new URLSearchParams({
            client_id: clientFilter,
            tahun: tahunFilter,
            start_date: startDateFilter,
            end_date: endDateFilter
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
                    if (div.innerText && (div.innerText.includes('Tahun Periode:') || div.innerText.includes('Pilih Klien:') || div.innerText.includes('Tanggal Mulai'))) {
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

function onReportTahunChange() {
    const startDateEl = document.getElementById('filterReportStartDate');
    const endDateEl = document.getElementById('filterReportEndDate');
    if (startDateEl) startDateEl.value = '';
    if (endDateEl) endDateEl.value = '';

    window.reportState.selectedStartDate = '';
    window.reportState.selectedEndDate = '';

    loadPayrollReport();
}

function onReportDateRangeChange() {
    const startDateEl = document.getElementById('filterReportStartDate');
    const endDateEl = document.getElementById('filterReportEndDate');
    const selectTahunEl = document.getElementById('filterReportTahun');

    if ((startDateEl && startDateEl.value) || (endDateEl && endDateEl.value)) {
        if (selectTahunEl) selectTahunEl.value = 'all';
        window.reportState.selectedTahun = 'all';
    }

    loadPayrollReport();
}

function resetReportFilter() {
    const startDateEl = document.getElementById('filterReportStartDate');
    const endDateEl = document.getElementById('filterReportEndDate');
    const selectTahunEl = document.getElementById('filterReportTahun');
    const selectMetricEl = document.getElementById('filterReportMetric');

    if (startDateEl) startDateEl.value = '';
    if (endDateEl) endDateEl.value = '';
    if (selectTahunEl) selectTahunEl.value = '2026';
    if (selectMetricEl) selectMetricEl.value = 'total_thp';

    window.reportState.selectedStartDate = '';
    window.reportState.selectedEndDate = '';
    window.reportState.selectedTahun = '2026';
    window.reportState.selectedMetric = 'total_thp';

    loadPayrollReport();
}

function dateStr() {
    const d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

// Global helpers attached to window
window.loadPayrollReport = loadPayrollReport;
window.onReportTahunChange = onReportTahunChange;
window.onReportDateRangeChange = onReportDateRangeChange;
window.resetReportFilter = resetReportFilter;
window.exportReportExcel = exportReportExcel;
window.exportReportPdf = exportReportPdf;

/**
 * Show KPI Detail Modal for top summary cards
 */
function showKpiDetailModal(type) {
    const modal = document.getElementById('modalReportKpiDetail');
    const overlay = document.getElementById('overlay');
    if (!modal) return;

    const titleEl = document.getElementById('modalKpiDetailTitle');
    const summaryEl = document.getElementById('modalKpiDetailSummary');
    const theadEl = document.getElementById('modalKpiDetailThead');
    const tbodyEl = document.getElementById('modalKpiDetailTbody');

    const summary = window.reportState.summary || {};
    const data = window.reportState.data || [];

    if (!data.length) {
        showToast('Belum ada data summary gaji untuk ditampilkan', 'warning');
        return;
    }

    if (type === 'total_thp') {
        titleEl.innerHTML = `<i class="fas fa-coins" style="color: #ffffff;"></i> Detail Total THP / Gaji Bersih`;
        
        let totalGajiPokok = 0, totalTunjangan = 0, totalPotongan = 0, totalThp = 0;
        data.forEach(item => {
            totalGajiPokok += (item.total_gaji_pokok || 0);
            const tunj = Math.max(0, (item.total_pendapatan || 0) - (item.total_gaji_pokok || 0));
            totalTunjangan += tunj;
            totalPotongan += (item.total_potongan || 0);
            totalThp += (item.total_thp || 0);
        });

        summaryEl.innerHTML = `
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL THP</div>
                <div style="font-size: 16px; font-weight: 800; color: #3b82f6;">${formatRupiah(totalThp)}</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">GAJI POKOK</div>
                <div style="font-size: 16px; font-weight: 800; color: #1e293b;">${formatRupiah(totalGajiPokok)}</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL TUNJANGAN</div>
                <div style="font-size: 16px; font-weight: 800; color: #10b981;">+${formatRupiah(totalTunjangan)}</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL POTONGAN</div>
                <div style="font-size: 16px; font-weight: 800; color: #ef4444;">-${formatRupiah(totalPotongan)}</div>
            </div>
        `;

        theadEl.innerHTML = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">No</th>
                <th style="padding: 12px; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Nama Klien</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Periode</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Gaji Pokok</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Tunjangan</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Potongan</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Take Home Pay</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Kontribusi %</th>
            </tr>
        `;

        let rows = '';
        data.forEach((item, idx) => {
            const tunj = Math.max(0, (item.total_pendapatan || 0) - (item.total_gaji_pokok || 0));
            const share = totalThp > 0 ? ((item.total_thp / totalThp) * 100).toFixed(1) : 0;
            rows += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 12px; font-weight: 700;">${escapeHtml(item.client_name)}</td>
                    <td style="padding: 12px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 12px; text-align: right;">${formatRupiah(item.total_gaji_pokok)}</td>
                    <td style="padding: 12px; text-align: right; color: #10b981;">+${formatRupiah(tunj)}</td>
                    <td style="padding: 12px; text-align: right; color: #ef4444;">-${formatRupiah(item.total_potongan)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: #3b82f6;">${formatRupiah(item.total_thp)}</td>
                    <td style="padding: 12px; text-align: center;"><span style="background: #f1f5f9; padding: 2px 8px; border-radius: 12px; font-weight: 700;">${share}%</span></td>
                </tr>
            `;
        });
        tbodyEl.innerHTML = rows;

    } else if (type === 'headcount') {
        titleEl.innerHTML = `<i class="fas fa-user-friends" style="color: #ffffff;"></i> Detail Total Karyawan (Headcount)`;

        let maxHc = 0, totalHcAll = 0;
        data.forEach(item => {
            totalHcAll += (item.total_karyawan || 0);
            if (item.total_karyawan > maxHc) maxHc = item.total_karyawan;
        });
        const avgHc = (totalHcAll / data.length).toFixed(1);

        summaryEl.innerHTML = `
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">TOTAL HEADCOUNT</div>
                <div style="font-size: 16px; font-weight: 800; color: #10b981;">${summary.total_headcount || 0} Orang</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">RATA-RATA / PERIODE</div>
                <div style="font-size: 16px; font-weight: 800; color: #1e293b;">${avgHc} Orang</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">PERIODE TERBANYAK</div>
                <div style="font-size: 16px; font-weight: 800; color: #1e293b;">${maxHc} Orang</div>
            </div>
        `;

        theadEl.innerHTML = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">No</th>
                <th style="padding: 12px; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Nama Klien</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Periode</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Jumlah Karyawan</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Total THP</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Rata-Rata Gaji / Orang</th>
            </tr>
        `;

        let rows = '';
        data.forEach((item, idx) => {
            const avgVal = item.total_karyawan > 0 ? (item.total_thp / item.total_karyawan) : 0;
            rows += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 12px; font-weight: 700;">${escapeHtml(item.client_name)}</td>
                    <td style="padding: 12px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 12px; text-align: center;"><span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 12px; font-weight: 800;">${item.total_karyawan} Orang</span></td>
                    <td style="padding: 12px; text-align: right; font-weight: 700;">${formatRupiah(item.total_thp)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #1e293b;">${formatRupiah(avgVal)}</td>
                </tr>
            `;
        });
        tbodyEl.innerHTML = rows;

    } else if (type === 'avg_salary') {
        titleEl.innerHTML = `<i class="fas fa-calculator" style="color: #ffffff;"></i> Detail Rata-Rata Gaji Per Karyawan`;

        let highestAvg = 0, lowestAvg = Infinity;
        data.forEach(item => {
            const avg = item.total_karyawan > 0 ? (item.total_thp / item.total_karyawan) : 0;
            if (avg > highestAvg) highestAvg = avg;
            if (avg < lowestAvg && avg > 0) lowestAvg = avg;
        });
        if (lowestAvg === Infinity) lowestAvg = 0;

        summaryEl.innerHTML = `
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">RATA-RATA UMUM</div>
                <div style="font-size: 16px; font-weight: 800; color: #3b82f6;">${formatRupiah(summary.avg_thp_per_employee || 0)}</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">RATA-RATA TERTINGGI</div>
                <div style="font-size: 16px; font-weight: 800; color: #10b981;">${formatRupiah(highestAvg)}</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">RATA-RATA TERENDAH</div>
                <div style="font-size: 16px; font-weight: 800; color: #ef4444;">${formatRupiah(lowestAvg)}</div>
            </div>
        `;

        theadEl.innerHTML = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">No</th>
                <th style="padding: 12px; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Nama Klien</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Periode</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Headcount</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Total THP</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Rata-Rata Gaji</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Kategori</th>
            </tr>
        `;

        const globalAvg = summary.avg_thp_per_employee || 0;
        let rows = '';
        data.forEach((item, idx) => {
            const avgVal = item.total_karyawan > 0 ? (item.total_thp / item.total_karyawan) : 0;
            const diffPct = globalAvg > 0 ? (((avgVal - globalAvg) / globalAvg) * 100).toFixed(1) : 0;
            const isAbove = avgVal >= globalAvg;

            rows += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 12px; font-weight: 700;">${escapeHtml(item.client_name)}</td>
                    <td style="padding: 12px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 12px; text-align: center;">${item.total_karyawan} Orang</td>
                    <td style="padding: 12px; text-align: right; font-weight: 600;">${formatRupiah(item.total_thp)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: #1e293b;">${formatRupiah(avgVal)}</td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; ${isAbove ? 'background: rgba(16, 185, 129, 0.1); color: #059669;' : 'background: rgba(239, 68, 68, 0.1); color: #dc2626;'}">
                            ${isAbove ? 'Diatas Rata-Rata' : 'Dibawah Rata-Rata'} (${diffPct > 0 ? '+' : ''}${diffPct}%)
                        </span>
                    </td>
                </tr>
            `;
        });
        tbodyEl.innerHTML = rows;

    } else if (type === 'mom_growth') {
        titleEl.innerHTML = `<i class="fas fa-chart-line" style="color: #ffffff;"></i> Detail Tren MoM (Month-on-Month Growth)`;

        const lastItem = data.length > 0 ? data[data.length - 1] : {};
        const growth = lastItem.mom_growth_percent || 0;
        const diffAmt = lastItem.mom_diff_amount || 0;
        const isPos = growth >= 0;

        summaryEl.innerHTML = `
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">PERTUMBUHAN MOM TERAKHIR</div>
                <div style="font-size: 16px; font-weight: 800; color: ${isPos ? '#10b981' : '#ef4444'};">${growth > 0 ? '+' : ''}${growth}%</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">SELISIH NOMINAL TERAKHIR</div>
                <div style="font-size: 16px; font-weight: 800; color: ${diffAmt >= 0 ? '#10b981' : '#ef4444'};">${diffAmt > 0 ? '+' : ''}${formatRupiah(diffAmt)}</div>
            </div>
            <div style="background: white; padding: 12px 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="font-size: 11px; color: #64748b; font-weight: 600;">STATUS TREN</div>
                <div style="font-size: 16px; font-weight: 800; color: #8b5cf6;">${isPos ? 'Meningkat ↑' : 'Penurunan ↓'}</div>
            </div>
        `;

        theadEl.innerHTML = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">No</th>
                <th style="padding: 12px; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Nama Klien</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Periode</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Total THP</th>
                <th style="padding: 12px; text-align: right; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Selisih Nominal (Rp)</th>
                <th style="padding: 12px; text-align: center; position: sticky; top: 0; background: #f1f5f9; z-index: 10; box-shadow: inset 0 -2px 0 #cbd5e1;">Pertumbuhan MoM (%)</th>
            </tr>
        `;

        let rows = '';
        data.forEach((item, idx) => {
            const g = item.mom_growth_percent || 0;
            const diff = item.mom_diff_amount || 0;
            const pos = g > 0;
            const neg = g < 0;

            let badgeClass = 'background: #f1f5f9; color: #475569;';
            let badgeIcon = 'minus';
            if (pos) {
                badgeClass = 'background: rgba(16, 185, 129, 0.1); color: #059669;';
                badgeIcon = 'arrow-up';
            } else if (neg) {
                badgeClass = 'background: rgba(239, 68, 68, 0.1); color: #dc2626;';
                badgeIcon = 'arrow-down';
            }

            rows += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 12px; font-weight: 700;">${escapeHtml(item.client_name)}</td>
                    <td style="padding: 12px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 12px; text-align: right; font-weight: 800;">${formatRupiah(item.total_thp)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 600; color: ${diff >= 0 ? '#059669' : '#dc2626'};">${diff > 0 ? '+' : ''}${formatRupiah(diff)}</td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; ${badgeClass}">
                            <i class="fas fa-${badgeIcon}"></i> ${g > 0 ? '+' : ''}${g}%
                        </span>
                    </td>
                </tr>
            `;
        });
        tbodyEl.innerHTML = rows;
    }

    if (overlay) overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.style.flexDirection = 'column';
}

function tutupModalKpiDetail() {
    const modal = document.getElementById('modalReportKpiDetail');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';

    const openModals = document.querySelectorAll('.modal-skema[style*="display: block"]');
    if (openModals.length === 0 && overlay) {
        overlay.style.display = 'none';
    }
}

window.showKpiDetailModal = showKpiDetailModal;
window.tutupModalKpiDetail = tutupModalKpiDetail;

/**
 * ============================================================================
 * Laporan Payroll BPJS Module (BPJS Kesehatan & BPJS Ketenagakerjaan)
 * ============================================================================
 */

window.bpjsReportState = {
    clients: [],
    data: [],
    summary: {},
    chartTrend: null,
    chartComposition: null,
    selectedClient: 'all',
    selectedTahun: '2026',
    selectedStartDate: '',
    selectedEndDate: '',
    selectedViewMode: 'summary'
};

function switchReportSubTab(tabName) {
    const btnSummary = document.getElementById('subTabReportSummary');
    const btnBpjs = document.getElementById('subTabReportBpjs');
    const panelSummary = document.getElementById('panelReportSummary');
    const panelBpjs = document.getElementById('panelReportBpjs');

    if (tabName === 'bpjs') {
        if (btnSummary) {
            btnSummary.classList.remove('active');
            btnSummary.style.color = '#64748b';
            btnSummary.style.borderBottom = '2px solid transparent';
        }
        if (btnBpjs) {
            btnBpjs.classList.add('active');
            btnBpjs.style.color = 'var(--primary-color)';
            btnBpjs.style.borderBottom = '2px solid var(--primary-color)';
        }
        if (panelSummary) panelSummary.style.display = 'none';
        if (panelBpjs) panelBpjs.style.display = 'block';

        loadBpjsReport();
    } else {
        if (btnBpjs) {
            btnBpjs.classList.remove('active');
            btnBpjs.style.color = '#64748b';
            btnBpjs.style.borderBottom = '2px solid transparent';
        }
        if (btnSummary) {
            btnSummary.classList.add('active');
            btnSummary.style.color = 'var(--primary-color)';
            btnSummary.style.borderBottom = '2px solid var(--primary-color)';
        }
        if (panelBpjs) panelBpjs.style.display = 'none';
        if (panelSummary) panelSummary.style.display = 'block';

        loadPayrollReport();
    }
}
window.switchReportSubTab = switchReportSubTab;

async function loadBpjsReport(overrideClientId = null) {
    try {
        let clientFilter = overrideClientId || window.selectedClientId || 'all';

        const selectClientEl = document.getElementById('filterBpjsClient');
        if (selectClientEl) {
            if (overrideClientId || window.selectedClientId) {
                selectClientEl.value = overrideClientId || window.selectedClientId;
            }
            clientFilter = selectClientEl.value || clientFilter;
        }

        const selectTahunEl = document.getElementById('filterBpjsTahun');
        const tahunFilter = selectTahunEl ? selectTahunEl.value : (window.bpjsReportState.selectedTahun || '2026');

        const startDateEl = document.getElementById('filterBpjsStartDate');
        const endDateEl = document.getElementById('filterBpjsEndDate');
        const startDateFilter = startDateEl ? startDateEl.value : '';
        const endDateFilter = endDateEl ? endDateEl.value : '';

        const viewModeEl = document.getElementById('filterBpjsViewMode');
        const viewModeFilter = viewModeEl ? viewModeEl.value : 'summary';

        window.bpjsReportState.selectedClient = clientFilter;
        window.bpjsReportState.selectedTahun = tahunFilter;
        window.bpjsReportState.selectedStartDate = startDateFilter;
        window.bpjsReportState.selectedEndDate = endDateFilter;
        window.bpjsReportState.selectedViewMode = viewModeFilter;

        showToast('Memuat data laporan BPJS...', 'info');

        const queryParams = new URLSearchParams({
            client_id: clientFilter,
            tahun: tahunFilter,
            start_date: startDateFilter,
            end_date: endDateFilter,
            view_mode: viewModeFilter
        });

        const response = await fetch(`${window.API}/reports/bpjs-summary?${queryParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.status === 'success') {
            window.bpjsReportState.clients = result.clients || [];
            window.bpjsReportState.data = result.data || [];
            window.bpjsReportState.summary = result.summary || {};

            populateBpjsClientOptions(clientFilter);
            renderBpjsKpiCards();
            renderBpjsCharts();
            renderBpjsTable();
            showToast('Laporan BPJS berhasil diperbarui', 'success');
        } else {
            showToast(result.message || 'Gagal memuat data laporan BPJS', 'error');
        }
    } catch (err) {
        console.error('Error loading BPJS report:', err);
        showToast('Terjadi kesalahan saat memuat laporan BPJS', 'error');
    }
}
window.loadBpjsReport = loadBpjsReport;

function populateBpjsClientOptions(activeClientId = null) {
    const select = document.getElementById('filterBpjsClient');
    if (!select) return;

    const targetVal = activeClientId || window.selectedClientId || select.value || 'all';
    let html = '<option value="all">Semua Klien</option>';
    (window.bpjsReportState.clients || []).forEach(c => {
        html += `<option value="${c.id}">${escapeHtml(c.nama)}</option>`;
    });
    select.innerHTML = html;
    select.value = targetVal;
}

function resetBpjsFilter() {
    const selectClient = document.getElementById('filterBpjsClient');
    const selectTahun = document.getElementById('filterBpjsTahun');
    const inputStart = document.getElementById('filterBpjsStartDate');
    const inputEnd = document.getElementById('filterBpjsEndDate');
    const selectMode = document.getElementById('filterBpjsViewMode');

    if (selectClient) selectClient.value = window.selectedClientId || 'all';
    if (selectTahun) selectTahun.value = '2026';
    if (inputStart) inputStart.value = '';
    if (inputEnd) inputEnd.value = '';
    if (selectMode) selectMode.value = 'summary';

    loadBpjsReport();
}
window.resetBpjsFilter = resetBpjsFilter;

function renderBpjsKpiCards() {
    const summary = window.bpjsReportState.summary || {};

    const elKes = document.getElementById('kpiBpjsTotalKes');
    const elTk  = document.getElementById('kpiBpjsTotalTk');
    const elPribadi = document.getElementById('kpiBpjsTotalPribadi');
    const elCompany = document.getElementById('kpiBpjsTotalCompany');

    const badgeKesEmp = document.getElementById('badgeKesEmp');
    const badgeKesCo  = document.getElementById('badgeKesCo');
    const badgeTkEmp  = document.getElementById('badgeTkEmp');
    const badgeTkCo   = document.getElementById('badgeTkCo');

    if (elKes) elKes.innerText = formatRupiah(summary.total_bpjs_kes || 0);
    if (elTk)  elTk.innerText  = formatRupiah(summary.total_bpjs_tk || 0);
    if (elPribadi) elPribadi.innerText = formatRupiah(summary.total_bpjs_pribadi || 0);
    if (elCompany) elCompany.innerText = formatRupiah(summary.total_bpjs_company || 0);

    if (badgeKesEmp) badgeKesEmp.innerText = 'Pribadi: ' + formatRupiah(summary.total_bpjs_kes_karyawan || 0);
    if (badgeKesCo)  badgeKesCo.innerText  = 'Co: ' + formatRupiah(summary.total_bpjs_kes_perusahaan || 0);
    if (badgeTkEmp)  badgeTkEmp.innerText  = 'Pribadi: ' + formatRupiah(summary.total_bpjs_tk_karyawan || 0);
    if (badgeTkCo)   badgeTkCo.innerText   = 'Co: ' + formatRupiah(summary.total_bpjs_tk_perusahaan || 0);
}

function renderBpjsCharts() {
    if (typeof Chart === 'undefined') return;

    const data = window.bpjsReportState.data || [];

    if (window.bpjsReportState.chartTrend) {
        window.bpjsReportState.chartTrend.destroy();
        window.bpjsReportState.chartTrend = null;
    }
    if (window.bpjsReportState.chartComposition) {
        window.bpjsReportState.chartComposition.destroy();
        window.bpjsReportState.chartComposition = null;
    }

    const ctxTrend = document.getElementById('chartBpjsTrend');
    const ctxComp = document.getElementById('chartBpjsComposition');

    if (!data.length) return;

    const periodLabelsSet = new Set();
    data.forEach(item => periodLabelsSet.add(item.bulan_tahun_label));
    const labels = Array.from(periodLabelsSet);

    const kesData = labels.map(lbl => {
        const items = data.filter(d => d.bulan_tahun_label === lbl);
        return items.reduce((acc, curr) => acc + (curr.subtotal_bpjs_kes || 0), 0);
    });

    const tkData = labels.map(lbl => {
        const items = data.filter(d => d.bulan_tahun_label === lbl);
        return items.reduce((acc, curr) => acc + (curr.subtotal_bpjs_tk || 0), 0);
    });

    const empData = labels.map(lbl => {
        const items = data.filter(d => d.bulan_tahun_label === lbl);
        return items.reduce((acc, curr) => acc + (curr.total_pribadi || 0), 0);
    });

    const coData = labels.map(lbl => {
        const items = data.filter(d => d.bulan_tahun_label === lbl);
        return items.reduce((acc, curr) => acc + (curr.total_company || 0), 0);
    });

    if (ctxTrend) {
        window.bpjsReportState.chartTrend = new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'BPJS Kesehatan (Kes)',
                        data: kesData,
                        backgroundColor: '#0284c7',
                        borderRadius: 4
                    },
                    {
                        label: 'BPJS Ketenagakerjaan (TK)',
                        data: tkData,
                        backgroundColor: '#9333ea',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return (context.dataset.label || '') + ': ' + formatRupiah(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
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

    if (ctxComp) {
        window.bpjsReportState.chartComposition = new Chart(ctxComp, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Tanggungan Pribadi (Dipotong Gaji)',
                        data: empData,
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    },
                    {
                        label: 'Tanggungan Company (Perusahaan)',
                        data: coData,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end' },
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

function renderBpjsTable() {
    const headContainer = document.getElementById('tableReportBpjsHead');
    const bodyContainer = document.getElementById('tableReportBpjsBody');
    const titleEl = document.getElementById('tableReportBpjsTitle');
    const subtitleEl = document.getElementById('tableReportBpjsSubtitle');
    if (!bodyContainer || !headContainer) return;

    const data = window.bpjsReportState.data || [];
    const isEmployeeMode = window.bpjsReportState.selectedViewMode === 'employee';

    if (titleEl) {
        titleEl.innerText = isEmployeeMode ? 'Rincian Laporan BPJS Per Karyawan' : 'Rincian Laporan BPJS Per Klien & Periode';
    }
    if (subtitleEl) {
        subtitleEl.innerText = isEmployeeMode ? 'Menampilkan akumulasi kontribusi BPJS setiap karyawan' : 'Menampilkan akumulasi kontribusi BPJS per perusahaan & bulan';
    }

    if (isEmployeeMode) {
        headContainer.innerHTML = `
            <tr style="background: #f1f5f9; text-align: center; color: #475569; font-weight: 700; border-bottom: 1px solid #cbd5e1;">
                <th rowspan="2" style="width: 40px; padding: 10px;">No</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">Nama Karyawan</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">ID / Jabatan</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">Klien</th>
                <th rowspan="2" style="padding: 10px;">Periode</th>
                <th colspan="3" style="padding: 8px; background: #e0f2fe; color: #0369a1;">BPJS Kesehatan</th>
                <th colspan="3" style="padding: 8px; background: #f3e8ff; color: #7e22ce;">BPJS Ketenagakerjaan</th>
                <th colspan="3" style="padding: 8px; background: #ecfdf5; color: #047857;">Total Kontribusi BPJS</th>
            </tr>
            <tr style="background: #f8fafc; text-align: right; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0; font-size: 11px;">
                <th style="padding: 8px; background: #f0f9ff;">Pribadi (1%)</th>
                <th style="padding: 8px; background: #f0f9ff;">Company (4%)</th>
                <th style="padding: 8px; background: #e0f2fe; color: #0284c7;">Total Kes</th>
                <th style="padding: 8px; background: #faf5ff;">Pribadi (JHT+JP)</th>
                <th style="padding: 8px; background: #faf5ff;">Company (TK)</th>
                <th style="padding: 8px; background: #f3e8ff; color: #9333ea;">Total TK</th>
                <th style="padding: 8px; background: #f0fdf4; color: #dc2626;">Total Pribadi</th>
                <th style="padding: 8px; background: #f0fdf4; color: #059669;">Total Company</th>
                <th style="padding: 8px; background: #dcfce7; color: #065f46;">Grand Total</th>
            </tr>
        `;
    } else {
        headContainer.innerHTML = `
            <tr style="background: #f1f5f9; text-align: center; color: #475569; font-weight: 700; border-bottom: 1px solid #cbd5e1;">
                <th rowspan="2" style="width: 40px; padding: 10px;">No</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">Nama Klien</th>
                <th rowspan="2" style="padding: 10px;">Periode</th>
                <th rowspan="2" style="padding: 10px;">Headcount</th>
                <th colspan="3" style="padding: 8px; background: #e0f2fe; color: #0369a1;">BPJS Kesehatan</th>
                <th colspan="3" style="padding: 8px; background: #f3e8ff; color: #7e22ce;">BPJS Ketenagakerjaan</th>
                <th colspan="3" style="padding: 8px; background: #ecfdf5; color: #047857;">Total Kontribusi BPJS</th>
            </tr>
            <tr style="background: #f8fafc; text-align: right; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0; font-size: 11px;">
                <th style="padding: 8px; background: #f0f9ff;">Pribadi (1%)</th>
                <th style="padding: 8px; background: #f0f9ff;">Company (4%)</th>
                <th style="padding: 8px; background: #e0f2fe; color: #0284c7;">Total Kes</th>
                <th style="padding: 8px; background: #faf5ff;">Pribadi (JHT+JP)</th>
                <th style="padding: 8px; background: #faf5ff;">Company (TK)</th>
                <th style="padding: 8px; background: #f3e8ff; color: #9333ea;">Total TK</th>
                <th style="padding: 8px; background: #f0fdf4; color: #dc2626;">Total Pribadi</th>
                <th style="padding: 8px; background: #f0fdf4; color: #059669;">Total Company</th>
                <th style="padding: 8px; background: #dcfce7; color: #065f46;">Grand Total</th>
            </tr>
        `;
    }

    if (!data.length) {
        const colspan = isEmployeeMode ? 14 : 13;
        bodyContainer.innerHTML = `
            <tr>
                <td colspan="${colspan}" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-folder-open" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                    Belum ada data BPJS untuk periode / klien yang dipilih.
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    data.forEach((item, index) => {
        if (isEmployeeMode) {
            html += `
                <tr onclick="showBpjsRowDetailModal(${index})" title="Klik untuk lihat rincian detail BPJS" style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 10px; text-align: center; font-weight: 600; color: #64748b;">${index + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #0284c7;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            ${escapeHtml(item.employee_name)}
                            <i class="fas fa-search-plus" style="font-size: 11px; opacity: 0.6;"></i>
                        </span>
                    </td>
                    <td style="padding: 10px; color: #64748b; font-size: 11px;">
                        <span style="font-weight: 600; color: #334155;">${escapeHtml(item.employ_id)}</span><br>
                        <span>${escapeHtml(item.position_name)}</span>
                    </td>
                    <td style="padding: 10px; font-weight: 600; color: #334155;">${escapeHtml(item.client_name)}</td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="background: #e0e7ff; color: #3730a3; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                            ${escapeHtml(item.bulan_tahun_label)}
                        </span>
                    </td>
                    <!-- BPJS Kes -->
                    <td style="padding: 10px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #0284c7; background: rgba(224, 242, 254, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_kes)}</td>
                    <!-- BPJS TK -->
                    <td style="padding: 10px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #9333ea; background: rgba(243, 232, 255, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_tk)}</td>
                    <!-- Totals -->
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #dc2626; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_pribadi)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #059669; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_company)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #0f172a; background: rgba(220, 252, 231, 0.5); font-variant-numeric: tabular-nums;">${formatRupiah(item.grand_total_bpjs)}</td>
                </tr>
            `;
        } else {
            html += `
                <tr onclick="showBpjsRowDetailModal(${index})" title="Klik untuk lihat rincian detail BPJS" style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px; text-align: center; font-weight: 600; color: #64748b;">${index + 1}</td>
                    <td style="padding: 12px; font-weight: 700; color: #0284c7;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            ${escapeHtml(item.client_name)}
                            <i class="fas fa-search-plus" style="font-size: 11px; opacity: 0.6;"></i>
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                            ${escapeHtml(item.bulan_tahun_label)}
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 600;">${item.total_karyawan} orang</td>
                    <!-- BPJS Kes -->
                    <td style="padding: 12px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 12px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #0284c7; background: rgba(224, 242, 254, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_kes)}</td>
                    <!-- BPJS TK -->
                    <td style="padding: 12px; text-align: right; color: #ef4444; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_karyawan)}</td>
                    <td style="padding: 12px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_perusahaan)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #9333ea; background: rgba(243, 232, 255, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_tk)}</td>
                    <!-- Totals -->
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #dc2626; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_pribadi)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #059669; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_company)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: #0f172a; background: rgba(220, 252, 231, 0.5); font-variant-numeric: tabular-nums;">${formatRupiah(item.grand_total_bpjs)}</td>
                </tr>
            `;
        }
    });

    bodyContainer.innerHTML = html;
}

function exportBpjsReportExcel() {
    const data = window.bpjsReportState.data || [];
    if (!data.length) {
        showToast('Tidak ada data BPJS untuk diekspor', 'warning');
        return;
    }

    const xlsxLib = window.XLSX || window.XLSXStyle || window.XLSXReader;
    if (!xlsxLib) {
        showToast('Library Excel belum dimuat', 'error');
        return;
    }

    const isEmployeeMode = window.bpjsReportState.selectedViewMode === 'employee';

    const rows = data.map((item, idx) => {
        if (isEmployeeMode) {
            return {
                'No': idx + 1,
                'Nama Karyawan': item.employee_name,
                'ID Karyawan': item.employ_id,
                'Jabatan': item.position_name,
                'Nama Klien': item.client_name,
                'Periode': item.bulan_tahun_label,
                'BPJS Kes Karyawan (Pribadi)': item.bpjs_kes_karyawan,
                'BPJS Kes Perusahaan (Company)': item.bpjs_kes_perusahaan,
                'Subtotal BPJS Kesehatan': item.subtotal_bpjs_kes,
                'BPJS TK Karyawan (JHT+JP)': item.bpjs_tk_karyawan,
                'BPJS TK Perusahaan (JHT+JP+JKK+JKM)': item.bpjs_tk_perusahaan,
                'Subtotal BPJS Ketenagakerjaan': item.subtotal_bpjs_tk,
                'Total Tanggungan Pribadi': item.total_pribadi,
                'Total Tanggungan Company': item.total_company,
                'Grand Total BPJS': item.grand_total_bpjs
            };
        } else {
            return {
                'No': idx + 1,
                'Nama Klien': item.client_name,
                'Periode': item.bulan_tahun_label,
                'Jumlah Karyawan': item.total_karyawan,
                'BPJS Kes Karyawan (Pribadi)': item.bpjs_kes_karyawan,
                'BPJS Kes Perusahaan (Company)': item.bpjs_kes_perusahaan,
                'Subtotal BPJS Kesehatan': item.subtotal_bpjs_kes,
                'BPJS TK Karyawan (JHT+JP)': item.bpjs_tk_karyawan,
                'BPJS TK Perusahaan (JHT+JP+JKK+JKM)': item.bpjs_tk_perusahaan,
                'Subtotal BPJS Ketenagakerjaan': item.subtotal_bpjs_tk,
                'Total Tanggungan Pribadi': item.total_pribadi,
                'Total Tanggungan Company': item.total_company,
                'Grand Total BPJS': item.grand_total_bpjs
            };
        }
    });

    const ws = xlsxLib.utils.json_to_sheet(rows);
    const wb = xlsxLib.utils.book_new();
    const sheetName = isEmployeeMode ? "Laporan BPJS Per Karyawan" : "Summary BPJS Klien";
    xlsxLib.utils.book_append_sheet(wb, ws, sheetName);
    xlsxLib.writeFile(wb, `Laporan_Payroll_BPJS_${isEmployeeMode ? 'Employee' : 'Client'}_${dateStr()}.xlsx`);
    showToast('Berhasil mengunduh laporan BPJS Excel', 'success');
}
window.exportBpjsReportExcel = exportBpjsReportExcel;

function exportBpjsReportPdf() {
    const element = document.getElementById('panelReportBpjs');
    if (!element) {
        showToast('Elemen laporan BPJS tidak ditemukan', 'error');
        return;
    }

    if (typeof html2pdf === 'undefined') {
        showToast('Library html2pdf belum siap. Menggunakan cetak browser...', 'info');
        window.print();
        return;
    }

    showToast('Menyiapkan file PDF BPJS, mohon tunggu...', 'info');

    const opt = {
        margin:       [0.3, 0.3, 0.3, 0.3],
        filename:     `Laporan_Payroll_BPJS_${dateStr()}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        pagebreak:    { mode: ['css', 'legacy'] },
        html2canvas:  { scale: 2, useCORS: true, logging: false, backgroundColor: '#ffffff' },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        showToast('Berhasil mendownload Laporan BPJS PDF', 'success');
    }).catch(err => {
        console.error('Export PDF BPJS error:', err);
        showToast('Gagal mengekspor PDF BPJS', 'error');
    });
}
window.exportBpjsReportPdf = exportBpjsReportPdf;

/**
 * ============================================================================
 * Modal Detail BPJS Handler (Row Click & KPI Click Details)
 * ============================================================================
 */

function showBpjsRowDetailModal(idx) {
    const data = window.bpjsReportState.data || [];
    const item = data[idx];
    if (!item) return;

    const isEmployeeMode = window.bpjsReportState.selectedViewMode === 'employee';
    const modal = document.getElementById('modalBpjsReportDetail');
    const overlay = document.getElementById('overlay');
    const titleEl = document.getElementById('modalBpjsReportTitle');
    const metaEl = document.getElementById('modalBpjsReportHeaderMeta');
    const theadEl = document.getElementById('modalBpjsReportThead');
    const tbodyEl = document.getElementById('modalBpjsReportTbody');

    if (!modal || !metaEl || !theadEl || !tbodyEl) return;

    const headerEl = document.getElementById('modalBpjsReportHeader');
    if (headerEl) {
        headerEl.style.background = 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)';
    }

    const subjectName = isEmployeeMode ? item.employee_name : item.client_name;
    if (titleEl) {
        titleEl.innerHTML = `<i class="fas fa-notes-medical" style="color: white;"></i> Rincian Perhitungan BPJS - ${escapeHtml(subjectName)}`;
    }

    let metaHtml = `
        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Subjek:</strong><br><span style="font-weight: 700; color: #0284c7; font-size: 15px;">${escapeHtml(subjectName)}</span></div>
        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Periode:</strong><br><span style="font-weight: 700; color: #1e293b;">${escapeHtml(item.bulan_tahun_label)}</span></div>
    `;
    if (isEmployeeMode) {
        metaHtml += `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">ID / Jabatan:</strong><br><span style="font-weight: 600; color: #334155;">${escapeHtml(item.employ_id)} - ${escapeHtml(item.position_name)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Klien:</strong><br><span style="font-weight: 600; color: #334155;">${escapeHtml(item.client_name)}</span></div>
        `;
    } else {
        metaHtml += `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Headcount:</strong><br><span style="font-weight: 700; color: #334155;">${item.total_karyawan} Orang</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Grand Total BPJS:</strong><br><span style="font-weight: 800; color: #059669;">${formatRupiah(item.grand_total_bpjs)}</span></div>
        `;
    }
    metaEl.innerHTML = metaHtml;

    theadEl.innerHTML = `
        <tr style="background: #f1f5f9; color: #475569; font-weight: 700; text-align: left;">
            <th style="padding: 12px;">Program BPJS</th>
            <th style="padding: 12px; text-align: right;">Pribadi / Karyawan</th>
            <th style="padding: 12px; text-align: right;">Company / Perusahaan</th>
            <th style="padding: 12px; text-align: right;">Total Kontribusi</th>
        </tr>
    `;

    const kesEmp = item.bpjs_kes_karyawan || 0;
    const kesCo  = item.bpjs_kes_perusahaan || 0;
    const kesTot = item.subtotal_bpjs_kes || 0;

    const jhtEmp = item.bpjs_jht_karyawan || 0;
    const jhtCo  = item.bpjs_jht_perusahaan || 0;
    const jhtTot = jhtEmp + jhtCo;

    const jpEmp  = item.bpjs_jp_karyawan || 0;
    const jpCo   = item.bpjs_jp_perusahaan || 0;
    const jpTot  = jpEmp + jpCo;

    const jkkEmp = 0;
    const jkkCo  = item.bpjs_jkk_perusahaan || 0;
    const jkkTot = jkkCo;

    const jkmEmp = 0;
    const jkmCo  = item.bpjs_jkm_perusahaan || 0;
    const jkmTot = jkmCo;

    const tkEmp = item.bpjs_tk_karyawan || 0;
    const tkCo  = item.bpjs_tk_perusahaan || 0;
    const tkTot = item.subtotal_bpjs_tk || 0;

    const totPribadi = item.total_pribadi || 0;
    const totCompany = item.total_company || 0;
    const grandTot   = item.grand_total_bpjs || 0;

    tbodyEl.innerHTML = `
        <tr style="border-bottom: 1px solid #e2e8f0; background: #f0f9ff;">
            <td style="padding: 12px; font-weight: 700; color: #0369a1;"><i class="fas fa-heartbeat" style="margin-right: 6px;"></i> BPJS Kesehatan (5%)</td>
            <td style="padding: 12px; text-align: right; color: #ef4444; font-weight: 600;">${formatRupiah(kesEmp)} <span style="font-size: 11px; opacity: 0.8;">(1%)</span></td>
            <td style="padding: 12px; text-align: right; color: #475569; font-weight: 600;">${formatRupiah(kesCo)} <span style="font-size: 11px; opacity: 0.8;">(4%)</span></td>
            <td style="padding: 12px; text-align: right; font-weight: 800; color: #0284c7;">${formatRupiah(kesTot)}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 10px 12px; padding-left: 24px; color: #334155; font-weight: 600;">• BPJS TK - JHT (Jaminan Hari Tua)</td>
            <td style="padding: 10px 12px; text-align: right; color: #ef4444;">${formatRupiah(jhtEmp)} <span style="font-size: 11px; opacity: 0.8;">(2%)</span></td>
            <td style="padding: 10px 12px; text-align: right; color: #475569;">${formatRupiah(jhtCo)} <span style="font-size: 11px; opacity: 0.8;">(3.7%)</span></td>
            <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #1e293b;">${formatRupiah(jhtTot)}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 10px 12px; padding-left: 24px; color: #334155; font-weight: 600;">• BPJS TK - JP (Jaminan Pensiun)</td>
            <td style="padding: 10px 12px; text-align: right; color: #ef4444;">${formatRupiah(jpEmp)} <span style="font-size: 11px; opacity: 0.8;">(1%)</span></td>
            <td style="padding: 10px 12px; text-align: right; color: #475569;">${formatRupiah(jpCo)} <span style="font-size: 11px; opacity: 0.8;">(2%)</span></td>
            <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #1e293b;">${formatRupiah(jpTot)}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 10px 12px; padding-left: 24px; color: #334155; font-weight: 600;">• BPJS TK - JKK (Jaminan Kecelakaan Kerja)</td>
            <td style="padding: 10px 12px; text-align: right; color: #94a3b8;">Rp 0 <span style="font-size: 11px; opacity: 0.8;">(0%)</span></td>
            <td style="padding: 10px 12px; text-align: right; color: #475569;">${formatRupiah(jkkCo)} <span style="font-size: 11px; opacity: 0.8;">(0.24%)</span></td>
            <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #1e293b;">${formatRupiah(jkkTot)}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 10px 12px; padding-left: 24px; color: #334155; font-weight: 600;">• BPJS TK - JKM (Jaminan Kematian)</td>
            <td style="padding: 10px 12px; text-align: right; color: #94a3b8;">Rp 0 <span style="font-size: 11px; opacity: 0.8;">(0%)</span></td>
            <td style="padding: 10px 12px; text-align: right; color: #475569;">${formatRupiah(jkmCo)} <span style="font-size: 11px; opacity: 0.8;">(0.30%)</span></td>
            <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #1e293b;">${formatRupiah(jkmTot)}</td>
        </tr>
        <tr style="border-bottom: 2px solid #cbd5e1; background: #faf5ff;">
            <td style="padding: 12px; font-weight: 700; color: #7e22ce;"><i class="fas fa-briefcase" style="margin-right: 6px;"></i> Subtotal BPJS Ketenagakerjaan</td>
            <td style="padding: 12px; text-align: right; color: #ef4444; font-weight: 700;">${formatRupiah(tkEmp)}</td>
            <td style="padding: 12px; text-align: right; color: #475569; font-weight: 700;">${formatRupiah(tkCo)}</td>
            <td style="padding: 12px; text-align: right; font-weight: 800; color: #9333ea;">${formatRupiah(tkTot)}</td>
        </tr>
        <tr style="background: #ecfdf5; font-weight: 800; color: #0f172a; font-size: 14px;">
            <td style="padding: 14px 12px;">GRAND TOTAL BPJS (Kesehatan + TK)</td>
            <td style="padding: 14px 12px; text-align: right; color: #dc2626;">${formatRupiah(totPribadi)}</td>
            <td style="padding: 14px 12px; text-align: right; color: #059669;">${formatRupiah(totCompany)}</td>
            <td style="padding: 14px 12px; text-align: right; color: #065f46; font-size: 15px;">${formatRupiah(grandTot)}</td>
        </tr>
    `;

    if (overlay) overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.style.flexDirection = 'column';
}
window.showBpjsRowDetailModal = showBpjsRowDetailModal;

function showBpjsKpiDetailModal(kpiType) {
    const data = window.bpjsReportState.data || [];
    const summary = window.bpjsReportState.summary || {};
    const modal = document.getElementById('modalBpjsReportDetail');
    const overlay = document.getElementById('overlay');
    const titleEl = document.getElementById('modalBpjsReportTitle');
    const metaEl = document.getElementById('modalBpjsReportHeaderMeta');
    const theadEl = document.getElementById('modalBpjsReportThead');
    const tbodyEl = document.getElementById('modalBpjsReportTbody');
    const isEmployeeMode = window.bpjsReportState.selectedViewMode === 'employee';

    if (!modal || !metaEl || !theadEl || !tbodyEl) return;

    const headerEl = document.getElementById('modalBpjsReportHeader');
    if (headerEl) {
        headerEl.style.background = 'linear-gradient(135deg, #f97316 0%, #ea580c 100%)';
    }

    let titleText = 'Detail Metrik BPJS';
    let metaHtml = '';
    let theadHtml = '';
    let tbodyHtml = '';

    if (kpiType === 'kes') {
        titleText = 'Detail Metrik: BPJS Kesehatan (5%)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total BPJS Kes:</strong><br><span style="font-weight: 800; color: #0284c7; font-size: 16px;">${formatRupiah(summary.total_bpjs_kes || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Tanggungan Pribadi (1%):</strong><br><span style="font-weight: 700; color: #dc2626;">${formatRupiah(summary.total_bpjs_kes_karyawan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Tanggungan Company (4%):</strong><br><span style="font-weight: 700; color: #059669;">${formatRupiah(summary.total_bpjs_kes_perusahaan || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Pribadi (1%)</th>
                <th style="padding: 10px; text-align: right;">Company (4%)</th>
                <th style="padding: 10px; text-align: right;">Total BPJS Kes (5%)</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #ef4444; font-weight: 600;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569; font-weight: 600;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #0284c7;">${formatRupiah(item.subtotal_bpjs_kes)}</td>
                </tr>
            `;
        });
    } else if (kpiType === 'tk') {
        titleText = 'Detail Metrik: BPJS Ketenagakerjaan (JHT, JP, JKK, JKM)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total BPJS TK:</strong><br><span style="font-weight: 800; color: #9333ea; font-size: 16px;">${formatRupiah(summary.total_bpjs_tk || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Tanggungan Pribadi (JHT+JP):</strong><br><span style="font-weight: 700; color: #dc2626;">${formatRupiah(summary.total_bpjs_tk_karyawan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Tanggungan Company (TK):</strong><br><span style="font-weight: 700; color: #059669;">${formatRupiah(summary.total_bpjs_tk_perusahaan || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">JHT (Emp/Co)</th>
                <th style="padding: 10px; text-align: right;">JP (Emp/Co)</th>
                <th style="padding: 10px; text-align: right;">JKK (Co)</th>
                <th style="padding: 10px; text-align: right;">JKM (Co)</th>
                <th style="padding: 10px; text-align: right;">Total BPJS TK</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            const jhtTot = (item.bpjs_jht_karyawan || 0) + (item.bpjs_jht_perusahaan || 0);
            const jpTot  = (item.bpjs_jp_karyawan || 0) + (item.bpjs_jp_perusahaan || 0);
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; font-weight: 600;">${formatRupiah(jhtTot)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 600;">${formatRupiah(jpTot)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jkk_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jkm_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #9333ea;">${formatRupiah(item.subtotal_bpjs_tk)}</td>
                </tr>
            `;
        });
    } else if (kpiType === 'pribadi') {
        titleText = 'Detail Metrik: Tanggungan Pribadi (Dipotong dari Gaji)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Potongan BPJS Karyawan:</strong><br><span style="font-weight: 800; color: #dc2626; font-size: 16px;">${formatRupiah(summary.total_bpjs_pribadi || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Kes Pribadi (1%):</strong><br><span style="font-weight: 700; color: #0369a1;">${formatRupiah(summary.total_bpjs_kes_karyawan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">TK Pribadi (JHT+JP 3%):</strong><br><span style="font-weight: 700; color: #7e22ce;">${formatRupiah(summary.total_bpjs_tk_karyawan || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Kes Pribadi (1%)</th>
                <th style="padding: 10px; text-align: right;">JHT Pribadi (2%)</th>
                <th style="padding: 10px; text-align: right;">JP Pribadi (1%)</th>
                <th style="padding: 10px; text-align: right;">Total Dipotong Gaji</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #0284c7; font-weight: 600;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #9333ea; font-weight: 600;">${formatRupiah(item.bpjs_jht_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #9333ea; font-weight: 600;">${formatRupiah(item.bpjs_jp_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #dc2626;">${formatRupiah(item.total_pribadi)}</td>
                </tr>
            `;
        });
    } else if (kpiType === 'company') {
        titleText = 'Detail Metrik: Tanggungan Company (Beban Perusahaan)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Beban BPJS Perusahaan:</strong><br><span style="font-weight: 800; color: #059669; font-size: 16px;">${formatRupiah(summary.total_bpjs_company || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Kes Perusahaan (4%):</strong><br><span style="font-weight: 700; color: #0369a1;">${formatRupiah(summary.total_bpjs_kes_perusahaan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">TK Perusahaan (JHT,JP,JKK,JKM):</strong><br><span style="font-weight: 700; color: #7e22ce;">${formatRupiah(summary.total_bpjs_tk_perusahaan || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Kes Co (4%)</th>
                <th style="padding: 10px; text-align: right;">JHT Co (3.7%)</th>
                <th style="padding: 10px; text-align: right;">JP Co (2%)</th>
                <th style="padding: 10px; text-align: right;">JKK Co (0.24%)</th>
                <th style="padding: 10px; text-align: right;">JKM Co (0.3%)</th>
                <th style="padding: 10px; text-align: right;">Total Tanggungan Company</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #0284c7; font-weight: 600;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #9333ea; font-weight: 600;">${formatRupiah(item.bpjs_jht_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #9333ea; font-weight: 600;">${formatRupiah(item.bpjs_jp_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jkk_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jkm_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #059669;">${formatRupiah(item.total_company)}</td>
                </tr>
            `;
        });
    }

    if (titleEl) titleEl.innerHTML = `<i class="fas fa-chart-pie" style="color: white;"></i> ${titleText}`;
    metaEl.innerHTML = metaHtml;
    theadEl.innerHTML = theadHtml;
    tbodyEl.innerHTML = tbodyHtml;

    if (overlay) overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.style.flexDirection = 'column';
}
window.showBpjsKpiDetailModal = showBpjsKpiDetailModal;

function tutupModalBpjsReportDetail() {
    const modal = document.getElementById('modalBpjsReportDetail');
    const overlay = document.getElementById('overlay');
    if (modal) modal.style.display = 'none';

    const openModals = document.querySelectorAll('.modal-skema[style*="display: block"]');
    if (openModals.length === 0 && overlay) {
        overlay.style.display = 'none';
    }
}
window.tutupModalBpjsReportDetail = tutupModalBpjsReportDetail;



