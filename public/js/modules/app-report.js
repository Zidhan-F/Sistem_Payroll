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

        const reportTahunEl = document.getElementById('filterReportTahun');
        const processTahunEl = document.getElementById('filterProcessReportTahun');
        let tahunFilter = window.reportState.selectedTahun || '2026';
        if (processTahunEl && processTahunEl.value) {
            tahunFilter = processTahunEl.value;
        } else if (reportTahunEl && reportTahunEl.value) {
            tahunFilter = reportTahunEl.value;
        }
        if (reportTahunEl) reportTahunEl.value = tahunFilter;
        if (processTahunEl) processTahunEl.value = tahunFilter;

        const reportStartEl = document.getElementById('filterReportStartDate');
        const processStartEl = document.getElementById('filterProcessReportStartDate');
        let startDateFilter = window.reportState.selectedStartDate || '';
        if (processStartEl && processStartEl.value) {
            startDateFilter = processStartEl.value;
        } else if (reportStartEl && reportStartEl.value) {
            startDateFilter = reportStartEl.value;
        }
        if (reportStartEl) reportStartEl.value = startDateFilter;
        if (processStartEl) processStartEl.value = startDateFilter;

        const reportEndEl = document.getElementById('filterReportEndDate');
        const processEndEl = document.getElementById('filterProcessReportEndDate');
        let endDateFilter = window.reportState.selectedEndDate || '';
        if (processEndEl && processEndEl.value) {
            endDateFilter = processEndEl.value;
        } else if (reportEndEl && reportEndEl.value) {
            endDateFilter = reportEndEl.value;
        }
        if (reportEndEl) reportEndEl.value = endDateFilter;
        if (processEndEl) processEndEl.value = endDateFilter;

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

    const thpText = formatRupiah(summary.total_thp || 0);
    const hcText = (summary.total_headcount || 0) + ' Orang';
    const avgText = formatRupiah(summary.avg_thp_per_employee || 0);

    let momHtml = '0.00% MoM';
    if (data.length > 1) {
        const lastItem = data[data.length - 1];
        const growth = lastItem.mom_growth_percent || 0;
        const isPos = growth >= 0;
        momHtml = `
            <span style="color: ${isPos ? '#10b981' : '#ef4444'}; font-weight: 700;">
                <i class="fas fa-arrow-${isPos ? 'up' : 'down'}"></i> ${growth > 0 ? '+' : ''}${growth}%
            </span>
            <span style="font-size: 12px; color: #64748b; margin-left: 4px;">MoM</span>
        `;
    }

    const elThp = document.getElementById('kpiReportTotalThp');
    if (elThp) elThp.innerText = thpText;

    const elHc = document.getElementById('kpiReportHeadcount');
    if (elHc) elHc.innerText = hcText;

    const elAvg = document.getElementById('kpiReportAvgSalary');
    if (elAvg) elAvg.innerText = avgText;

    const elMom = document.getElementById('kpiReportMomGrowth');
    if (elMom) elMom.innerHTML = momHtml;

    const elNetSalary = document.getElementById('kpiProcessTotalNetSalary');
    const elKesCo = document.getElementById('kpiProcessBpjsKesCo');
    const elTkCo = document.getElementById('kpiProcessBpjsTkCo');
    const elKesEmp = document.getElementById('kpiProcessBpjsKesEmp');
    const elTkEmp = document.getElementById('kpiProcessBpjsTkEmp');
    const elAbsen = document.getElementById('kpiProcessDeductionAbsen');

    if (elNetSalary) elNetSalary.innerText = formatRupiah(summary.total_thp !== undefined ? summary.total_thp : (summary.thp || summary.total_net_salary || 0));
    if (elKesCo) elKesCo.innerText = formatRupiah(summary.bpjs_kes_perusahaan || 0);
    if (elTkCo) elTkCo.innerText = formatRupiah(summary.bpjs_tk_perusahaan || 0);
    if (elKesEmp) elKesEmp.innerText = formatRupiah(summary.bpjs_kes_karyawan || 0);
    if (elTkEmp) elTkEmp.innerText = formatRupiah(summary.bpjs_tk_karyawan || 0);
    if (elAbsen) elAbsen.innerText = formatRupiah(summary.potongan_absen || 0);
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

function onReportTahunChange(event) {
    let yearVal = 'all';
    if (event && event.target && event.target.value !== undefined) {
        yearVal = event.target.value;
    } else {
        const pEl = document.getElementById('filterProcessReportTahun');
        const rEl = document.getElementById('filterReportTahun');
        yearVal = pEl ? pEl.value : (rEl ? rEl.value : 'all');
    }

    ['filterReportTahun', 'filterProcessReportTahun'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = yearVal;
    });

    ['filterReportStartDate', 'filterProcessReportStartDate', 'filterReportEndDate', 'filterProcessReportEndDate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    window.reportState.selectedTahun = yearVal;
    window.reportState.selectedStartDate = '';
    window.reportState.selectedEndDate = '';

    loadPayrollReport();
}

function onReportDateRangeChange(event) {
    let startVal = '';
    let endVal = '';

    if (event && event.target) {
        if (event.target.id.includes('Process')) {
            const pStart = document.getElementById('filterProcessReportStartDate');
            const pEnd = document.getElementById('filterProcessReportEndDate');
            startVal = pStart ? pStart.value : '';
            endVal = pEnd ? pEnd.value : '';
        } else {
            const rStart = document.getElementById('filterReportStartDate');
            const rEnd = document.getElementById('filterReportEndDate');
            startVal = rStart ? rStart.value : '';
            endVal = rEnd ? rEnd.value : '';
        }
    } else {
        const pStart = document.getElementById('filterProcessReportStartDate');
        const rStart = document.getElementById('filterReportStartDate');
        const pEnd = document.getElementById('filterProcessReportEndDate');
        const rEnd = document.getElementById('filterReportEndDate');
        startVal = (pStart && pStart.value) || (rStart && rStart.value) || '';
        endVal = (pEnd && pEnd.value) || (rEnd && rEnd.value) || '';
    }

    ['filterReportStartDate', 'filterProcessReportStartDate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = startVal;
    });

    ['filterReportEndDate', 'filterProcessReportEndDate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = endVal;
    });

    if (startVal || endVal) {
        ['filterReportTahun', 'filterProcessReportTahun'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = 'all';
        });
        window.reportState.selectedTahun = 'all';
    }

    window.reportState.selectedStartDate = startVal;
    window.reportState.selectedEndDate = endVal;

    loadPayrollReport();
}

function resetReportFilter() {
    ['filterReportStartDate', 'filterProcessReportStartDate', 'filterReportEndDate', 'filterProcessReportEndDate'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    ['filterReportTahun', 'filterProcessReportTahun'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '2026';
    });

    ['filterReportMetric', 'filterProcessReportMetric'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = 'total_thp';
    });

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

window.taxReportState = {
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
    const btnBpjs    = document.getElementById('subTabReportBpjs');
    const btnTax     = document.getElementById('subTabReportTax');
    const panelSummary = document.getElementById('panelReportSummary');
    const panelBpjs    = document.getElementById('panelReportBpjs');
    const panelTax     = document.getElementById('panelReportTax');

    const tabs = [
        { name: 'summary', btn: btnSummary, panel: panelSummary, loadFn: loadPayrollReport },
        { name: 'bpjs',    btn: btnBpjs,    panel: panelBpjs,    loadFn: loadBpjsReport },
        { name: 'tax',     btn: btnTax,     panel: panelTax,     loadFn: loadTaxReport }
    ];

    tabs.forEach(t => {
        if (t.name === tabName) {
            if (t.btn) {
                t.btn.classList.add('active');
                t.btn.style.color = 'var(--primary-color)';
                t.btn.style.borderBottom = '2px solid var(--primary-color)';
            }
            if (t.panel) t.panel.style.display = 'block';
            if (typeof t.loadFn === 'function') t.loadFn();
        } else {
            if (t.btn) {
                t.btn.classList.remove('active');
                t.btn.style.color = '#64748b';
                t.btn.style.borderBottom = '2px solid transparent';
            }
            if (t.panel) t.panel.style.display = 'none';
        }
    });
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

    const elKesEmp = document.getElementById('kpiBpjsKesEmp');
    const elTkEmp  = document.getElementById('kpiBpjsTkEmp');
    const elKesCo  = document.getElementById('kpiBpjsKesCo');
    const elTkCo   = document.getElementById('kpiBpjsTkCo');

    const elKes = document.getElementById('kpiBpjsTotalKes');
    const elTk  = document.getElementById('kpiBpjsTotalTk');
    const elPribadi = document.getElementById('kpiBpjsTotalPribadi');
    const elCompany = document.getElementById('kpiBpjsTotalCompany');

    const badgeKesEmp = document.getElementById('badgeKesEmp');
    const badgeKesCo  = document.getElementById('badgeKesCo');
    const badgeTkEmp  = document.getElementById('badgeTkEmp');
    const badgeTkCo   = document.getElementById('badgeTkCo');

    if (elKesEmp) elKesEmp.innerText = formatRupiah(summary.total_bpjs_kes_karyawan || 0);
    if (elTkEmp)  elTkEmp.innerText  = formatRupiah(summary.total_bpjs_tk_karyawan || 0);
    if (elKesCo)  elKesCo.innerText  = formatRupiah(summary.total_bpjs_kes_perusahaan || 0);
    if (elTkCo)   elTkCo.innerText   = formatRupiah(summary.total_bpjs_tk_perusahaan || 0);

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
                        backgroundColor: '#334155',
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
                        backgroundColor: '#0284c7',
                        borderRadius: 4
                    },
                    {
                        label: 'Tanggungan Company (Perusahaan)',
                        data: coData,
                        backgroundColor: '#475569',
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
            <tr style="background: #f1f5f9; text-align: center; color: #334155; font-weight: 700; border-bottom: 1px solid #cbd5e1;">
                <th rowspan="2" style="width: 40px; padding: 10px;">No</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">Nama Karyawan</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">Jabatan</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">Klien</th>
                <th rowspan="2" style="padding: 10px;">Periode</th>
                <th colspan="3" style="padding: 8px; background: #e0f2fe; color: #0369a1;">BPJS Kesehatan</th>
                <th colspan="3" style="padding: 8px; background: #f8fafc; color: #334155;">BPJS Ketenagakerjaan</th>
                <th colspan="3" style="padding: 8px; background: #f1f5f9; color: #1e293b;">Total Kontribusi BPJS</th>
            </tr>
            <tr style="background: #f8fafc; text-align: right; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0; font-size: 11px;">
                <th style="padding: 8px; background: #f0f9ff;">Pribadi (1%)</th>
                <th style="padding: 8px; background: #f0f9ff;">Company (4%)</th>
                <th style="padding: 8px; background: #bae6fd; color: #0369a1;">Total Kes</th>
                <th style="padding: 8px; background: #f1f5f9;">Pribadi (JHT+JP)</th>
                <th style="padding: 8px; background: #f1f5f9;">Company (TK)</th>
                <th style="padding: 8px; background: #e2e8f0; color: #1e293b;">Total TK</th>
                <th style="padding: 8px; background: #e0f2fe; color: #0369a1;">Total Pribadi</th>
                <th style="padding: 8px; background: #f1f5f9; color: #334155;">Total Company</th>
                <th style="padding: 8px; background: #e2e8f0; color: #0f172a;">Grand Total</th>
            </tr>
        `;
    } else {
        headContainer.innerHTML = `
            <tr style="background: #f1f5f9; text-align: center; color: #334155; font-weight: 700; border-bottom: 1px solid #cbd5e1;">
                <th rowspan="2" style="width: 40px; padding: 10px;">No</th>
                <th rowspan="2" style="padding: 10px; text-align: left;">Nama Klien</th>
                <th rowspan="2" style="padding: 10px;">Periode</th>
                <th rowspan="2" style="padding: 10px;">Headcount</th>
                <th colspan="3" style="padding: 8px; background: #e0f2fe; color: #0369a1;">BPJS Kesehatan</th>
                <th colspan="3" style="padding: 8px; background: #f8fafc; color: #334155;">BPJS Ketenagakerjaan</th>
                <th colspan="3" style="padding: 8px; background: #f1f5f9; color: #1e293b;">Total Kontribusi BPJS</th>
            </tr>
            <tr style="background: #f8fafc; text-align: right; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0; font-size: 11px;">
                <th style="padding: 8px; background: #f0f9ff;">Pribadi (1%)</th>
                <th style="padding: 8px; background: #f0f9ff;">Company (4%)</th>
                <th style="padding: 8px; background: #bae6fd; color: #0369a1;">Total Kes</th>
                <th style="padding: 8px; background: #f1f5f9;">Pribadi (JHT+JP)</th>
                <th style="padding: 8px; background: #f1f5f9;">Company (TK)</th>
                <th style="padding: 8px; background: #e2e8f0; color: #1e293b;">Total TK</th>
                <th style="padding: 8px; background: #e0f2fe; color: #0369a1;">Total Pribadi</th>
                <th style="padding: 8px; background: #f1f5f9; color: #334155;">Total Company</th>
                <th style="padding: 8px; background: #e2e8f0; color: #0f172a;">Grand Total</th>
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
                    <td style="padding: 10px; font-weight: 600; color: #334155;">${escapeHtml(item.position_name)}</td>
                    <td style="padding: 10px; font-weight: 600; color: #334155;">${escapeHtml(item.client_name)}</td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                            ${escapeHtml(item.bulan_tahun_label)}
                        </span>
                    </td>
                    <!-- BPJS Kes -->
                    <td style="padding: 10px; text-align: right; color: #0284c7; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #0369a1; background: rgba(224, 242, 254, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_kes)}</td>
                    <!-- BPJS TK -->
                    <td style="padding: 10px; text-align: right; color: #0284c7; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #1e293b; background: rgba(226, 232, 240, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_tk)}</td>
                    <!-- Totals -->
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #0284c7; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_pribadi)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #334155; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_company)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #0f172a; background: rgba(226, 232, 240, 0.6); font-variant-numeric: tabular-nums;">${formatRupiah(item.grand_total_bpjs)}</td>
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
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                            ${escapeHtml(item.bulan_tahun_label)}
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 600;">${item.total_karyawan} orang</td>
                    <!-- BPJS Kes -->
                    <td style="padding: 12px; text-align: right; color: #0284c7; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 12px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #0369a1; background: rgba(224, 242, 254, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_kes)}</td>
                    <!-- BPJS TK -->
                    <td style="padding: 12px; text-align: right; color: #0284c7; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_karyawan)}</td>
                    <td style="padding: 12px; text-align: right; color: #475569; font-variant-numeric: tabular-nums;">${formatRupiah(item.bpjs_tk_perusahaan)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #1e293b; background: rgba(226, 232, 240, 0.4); font-variant-numeric: tabular-nums;">${formatRupiah(item.subtotal_bpjs_tk)}</td>
                    <!-- Totals -->
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #0284c7; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_pribadi)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #334155; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_company)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: #0f172a; background: rgba(226, 232, 240, 0.6); font-variant-numeric: tabular-nums;">${formatRupiah(item.grand_total_bpjs)}</td>
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
        headerEl.style.background = 'linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%)';
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
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Jabatan:</strong><br><span style="font-weight: 600; color: #334155;">${escapeHtml(item.position_name)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Klien:</strong><br><span style="font-weight: 600; color: #334155;">${escapeHtml(item.client_name)}</span></div>
        `;
    } else {
        metaHtml += `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Headcount:</strong><br><span style="font-weight: 700; color: #334155;">${item.total_karyawan} Orang</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Grand Total BPJS:</strong><br><span style="font-weight: 800; color: #0f172a;">${formatRupiah(item.grand_total_bpjs)}</span></div>
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
        <tr style="border-bottom: 1px solid #e2e8f0; background: #e0f2fe;">
            <td style="padding: 12px; font-weight: 700; color: #0369a1;"><i class="fas fa-heartbeat" style="margin-right: 6px;"></i> BPJS Kesehatan (5%)</td>
            <td style="padding: 12px; text-align: right; color: #0284c7; font-weight: 600;">${formatRupiah(kesEmp)} <span style="font-size: 11px; opacity: 0.8;">(1%)</span></td>
            <td style="padding: 12px; text-align: right; color: #475569; font-weight: 600;">${formatRupiah(kesCo)} <span style="font-size: 11px; opacity: 0.8;">(4%)</span></td>
            <td style="padding: 12px; text-align: right; font-weight: 800; color: #0369a1;">${formatRupiah(kesTot)}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 10px 12px; padding-left: 24px; color: #334155; font-weight: 600;">• BPJS TK - JHT (Jaminan Hari Tua)</td>
            <td style="padding: 10px 12px; text-align: right; color: #0284c7;">${formatRupiah(jhtEmp)} <span style="font-size: 11px; opacity: 0.8;">(2%)</span></td>
            <td style="padding: 10px 12px; text-align: right; color: #475569;">${formatRupiah(jhtCo)} <span style="font-size: 11px; opacity: 0.8;">(3.7%)</span></td>
            <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #1e293b;">${formatRupiah(jhtTot)}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 10px 12px; padding-left: 24px; color: #334155; font-weight: 600;">• BPJS TK - JP (Jaminan Pensiun)</td>
            <td style="padding: 10px 12px; text-align: right; color: #0284c7;">${formatRupiah(jpEmp)} <span style="font-size: 11px; opacity: 0.8;">(1%)</span></td>
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
        <tr style="border-bottom: 2px solid #cbd5e1; background: #f8fafc;">
            <td style="padding: 12px; font-weight: 700; color: #334155;"><i class="fas fa-briefcase" style="margin-right: 6px;"></i> Subtotal BPJS Ketenagakerjaan</td>
            <td style="padding: 12px; text-align: right; color: #0284c7; font-weight: 700;">${formatRupiah(tkEmp)}</td>
            <td style="padding: 12px; text-align: right; color: #475569; font-weight: 700;">${formatRupiah(tkCo)}</td>
            <td style="padding: 12px; text-align: right; font-weight: 800; color: #1e293b;">${formatRupiah(tkTot)}</td>
        </tr>
        <tr style="background: #e2e8f0; font-weight: 800; color: #0f172a; font-size: 14px;">
            <td style="padding: 14px 12px;">GRAND TOTAL BPJS (Kesehatan + TK)</td>
            <td style="padding: 14px 12px; text-align: right; color: #0284c7;">${formatRupiah(totPribadi)}</td>
            <td style="padding: 14px 12px; text-align: right; color: #334155;">${formatRupiah(totCompany)}</td>
            <td style="padding: 14px 12px; text-align: right; color: #0f172a; font-size: 15px;">${formatRupiah(grandTot)}</td>
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
        headerEl.style.background = 'linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%)';
    }

    let titleText = 'Detail Metrik BPJS';
    let metaHtml = '';
    let theadHtml = '';
    let tbodyHtml = '';

    if (kpiType === 'kes_pribadi' || kpiType === 'kes') {
        titleText = 'Detail Metrik: BPJS Kesehatan - Tanggungan Pribadi (1%)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Kes Pribadi (1%):</strong><br><span style="font-weight: 800; color: #0284c7; font-size: 16px;">${formatRupiah(summary.total_bpjs_kes_karyawan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Kes Company (4%):</strong><br><span style="font-weight: 700; color: #334155;">${formatRupiah(summary.total_bpjs_kes_perusahaan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Grand Total BPJS Kes (5%):</strong><br><span style="font-weight: 700; color: #1e293b;">${formatRupiah(summary.total_bpjs_kes || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Kes Pribadi (1%)</th>
                <th style="padding: 10px; text-align: right;">Kes Co (4%)</th>
                <th style="padding: 10px; text-align: right;">Total BPJS Kes</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #0284c7; font-weight: 700;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #1e293b;">${formatRupiah(item.subtotal_bpjs_kes)}</td>
                </tr>
            `;
        });
    } else if (kpiType === 'tk_pribadi' || kpiType === 'tk') {
        titleText = 'Detail Metrik: BPJS Ketenagakerjaan - Tanggungan Pribadi (3%)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total TK Pribadi (3%):</strong><br><span style="font-weight: 800; color: #0284c7; font-size: 16px;">${formatRupiah(summary.total_bpjs_tk_karyawan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">TK Company (8.04%):</strong><br><span style="font-weight: 700; color: #334155;">${formatRupiah(summary.total_bpjs_tk_perusahaan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Grand Total BPJS TK:</strong><br><span style="font-weight: 700; color: #1e293b;">${formatRupiah(summary.total_bpjs_tk || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">JHT Pribadi (2%)</th>
                <th style="padding: 10px; text-align: right;">JP Pribadi (1%)</th>
                <th style="padding: 10px; text-align: right;">Total TK Pribadi (3%)</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            const tkPribadiTot = (item.bpjs_jht_karyawan || 0) + (item.bpjs_jp_karyawan || 0);
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #0284c7; font-weight: 600;">${formatRupiah(item.bpjs_jht_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; color: #0284c7; font-weight: 600;">${formatRupiah(item.bpjs_jp_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #0284c7;">${formatRupiah(tkPribadiTot)}</td>
                </tr>
            `;
        });
    } else if (kpiType === 'kes_company') {
        titleText = 'Detail Metrik: BPJS Kesehatan - Tanggungan Company (4%)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Kes Company (4%):</strong><br><span style="font-weight: 800; color: #334155; font-size: 16px;">${formatRupiah(summary.total_bpjs_kes_perusahaan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Kes Pribadi (1%):</strong><br><span style="font-weight: 700; color: #0284c7;">${formatRupiah(summary.total_bpjs_kes_karyawan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Grand Total BPJS Kes (5%):</strong><br><span style="font-weight: 700; color: #1e293b;">${formatRupiah(summary.total_bpjs_kes || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Kes Co (4%)</th>
                <th style="padding: 10px; text-align: right;">Kes Pribadi (1%)</th>
                <th style="padding: 10px; text-align: right;">Total BPJS Kes</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #334155; font-weight: 700;">${formatRupiah(item.bpjs_kes_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_kes_karyawan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #1e293b;">${formatRupiah(item.subtotal_bpjs_kes)}</td>
                </tr>
            `;
        });
    } else if (kpiType === 'tk_company') {
        titleText = 'Detail Metrik: BPJS Ketenagakerjaan - Tanggungan Company (8.04%)';
        metaHtml = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total TK Company:</strong><br><span style="font-weight: 800; color: #334155; font-size: 16px;">${formatRupiah(summary.total_bpjs_tk_perusahaan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">TK Pribadi (3%):</strong><br><span style="font-weight: 700; color: #0284c7;">${formatRupiah(summary.total_bpjs_tk_karyawan || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Grand Total BPJS TK:</strong><br><span style="font-weight: 700; color: #1e293b;">${formatRupiah(summary.total_bpjs_tk || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">JHT Co (3.7%)</th>
                <th style="padding: 10px; text-align: right;">JP Co (2%)</th>
                <th style="padding: 10px; text-align: right;">JKK Co (0.24%)</th>
                <th style="padding: 10px; text-align: right;">JKM Co (0.3%)</th>
                <th style="padding: 10px; text-align: right;">Total TK Company</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jht_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jp_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jkk_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; color: #475569;">${formatRupiah(item.bpjs_jkm_perusahaan)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #334155;">${formatRupiah(item.bpjs_tk_perusahaan)}</td>
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

/* =====================================================================
 * TAX REPORT (PPh 21) MODULE
 * ===================================================================== */

async function loadTaxReport(overrideClientId = null) {
    try {
        let clientFilter = overrideClientId || window.selectedClientId || 'all';

        const selectClientEl = document.getElementById('filterTaxClient');
        if (selectClientEl) {
            if (overrideClientId || window.selectedClientId) {
                selectClientEl.value = overrideClientId || window.selectedClientId;
            }
            clientFilter = selectClientEl.value || clientFilter;
        }

        const selectTahunEl = document.getElementById('filterTaxTahun');
        const tahunFilter = selectTahunEl ? selectTahunEl.value : (window.taxReportState.selectedTahun || '2026');

        const startDateEl = document.getElementById('filterTaxStartDate');
        const endDateEl = document.getElementById('filterTaxEndDate');
        const startDateFilter = startDateEl ? startDateEl.value : '';
        const endDateFilter = endDateEl ? endDateEl.value : '';

        const viewModeEl = document.getElementById('filterTaxViewMode');
        const viewModeFilter = viewModeEl ? viewModeEl.value : 'summary';

        window.taxReportState.selectedClient = clientFilter;
        window.taxReportState.selectedTahun = tahunFilter;
        window.taxReportState.selectedStartDate = startDateFilter;
        window.taxReportState.selectedEndDate = endDateFilter;
        window.taxReportState.selectedViewMode = viewModeFilter;

        showToast('Memuat data laporan Pajak (PPh 21)...', 'info');

        const queryParams = new URLSearchParams({
            client_id: clientFilter,
            tahun: tahunFilter,
            start_date: startDateFilter,
            end_date: endDateFilter,
            view_mode: viewModeFilter
        });

        const response = await fetch(`${window.API}/reports/tax-summary?${queryParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.status === 'success') {
            window.taxReportState.clients = result.clients || [];
            window.taxReportState.data = result.data || [];
            window.taxReportState.summary = result.summary || {};

            populateTaxClientOptions(clientFilter);
            renderTaxKpiCards();
            renderTaxCharts();
            renderTaxTable();
            showToast('Laporan Pajak (PPh 21) berhasil diperbarui', 'success');
        } else {
            showToast(result.message || 'Gagal memuat data laporan Pajak', 'error');
        }
    } catch (err) {
        console.error('Error loading Tax report:', err);
        showToast('Terjadi kesalahan saat memuat laporan Pajak', 'error');
    }
}
window.loadTaxReport = loadTaxReport;

function populateTaxClientOptions(activeClientId = null) {
    const select = document.getElementById('filterTaxClient');
    if (!select) return;

    const targetVal = activeClientId || window.selectedClientId || select.value || 'all';
    let html = '<option value="all">Semua Klien</option>';
    (window.taxReportState.clients || []).forEach(c => {
        html += `<option value="${c.id}">${escapeHtml(c.nama)}</option>`;
    });
    select.innerHTML = html;
    select.value = targetVal;
}

function resetTaxFilter() {
    const selectClient = document.getElementById('filterTaxClient');
    const selectTahun  = document.getElementById('filterTaxTahun');
    const inputStart   = document.getElementById('filterTaxStartDate');
    const inputEnd     = document.getElementById('filterTaxEndDate');
    const selectMode   = document.getElementById('filterTaxViewMode');

    if (selectClient) selectClient.value = window.selectedClientId || 'all';
    if (selectTahun)  selectTahun.value = '2026';
    if (inputStart)   inputStart.value = '';
    if (inputEnd)     inputEnd.value = '';
    if (selectMode)   selectMode.value = 'summary';

    loadTaxReport();
}
window.resetTaxFilter = resetTaxFilter;

function renderTaxKpiCards() {
    const summary = window.taxReportState.summary || {};

    const elPph21     = document.getElementById('kpiTaxTotalPph21');
    const elAllowance = document.getElementById('kpiTaxTotalAllowance');
    const elBruto     = document.getElementById('kpiTaxTotalBruto');
    const elHeadcount = document.getElementById('kpiTaxTotalHeadcount');

    if (elPph21)     elPph21.innerText     = formatRupiah(summary.total_pph21 || 0);
    if (elAllowance) elAllowance.innerText = formatRupiah(summary.total_tax_allowance || 0);
    if (elBruto)     elBruto.innerText     = formatRupiah(summary.total_bruto || 0);
    if (elHeadcount) elHeadcount.innerText = (summary.total_headcount || 0) + ' Orang';
}

function renderTaxCharts() {
    if (typeof Chart === 'undefined') return;

    const data = window.taxReportState.data || [];
    const isEmployeeMode = window.taxReportState.selectedViewMode === 'employee';

    const ctxTrend = document.getElementById('chartTaxTrend');
    const ctxComp  = document.getElementById('chartTaxComposition');

    if (window.taxReportState.chartTrend) window.taxReportState.chartTrend.destroy();
    if (window.taxReportState.chartComposition) window.taxReportState.chartComposition.destroy();

    const periodMap = {};
    data.forEach(item => {
        const label = item.bulan_tahun_label || item.bulan + ' ' + item.tahun;
        if (!periodMap[label]) {
            periodMap[label] = { pph21: 0, allowance: 0, bruto: 0 };
        }
        periodMap[label].pph21     += (isEmployeeMode ? (item.pph21 || 0) : (item.total_pph21 || 0));
        periodMap[label].allowance += (isEmployeeMode ? (item.tax_allowance || 0) : (item.total_tax_allowance || 0));
        periodMap[label].bruto     += (isEmployeeMode ? (item.gaji_pokok || 0) : (item.total_gaji_pokok || 0));
    });

    const labels    = Object.keys(periodMap);
    const pphData   = labels.map(l => periodMap[l].pph21);
    const allowData = labels.map(l => periodMap[l].allowance);
    const brutoData = labels.map(l => periodMap[l].bruto);

    if (ctxTrend) {
        window.taxReportState.chartTrend = new Chart(ctxTrend, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'PPh 21 (Pajak)',
                        data: pphData,
                        backgroundColor: '#0284c7',
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
        window.taxReportState.chartComposition = new Chart(ctxComp, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Penghasilan Bruto Kena Pajak',
                        data: brutoData,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4
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
}

function renderTaxTable() {
    const headContainer = document.getElementById('tableReportTaxHead');
    const bodyContainer = document.getElementById('tableReportTaxBody');
    const titleEl = document.getElementById('tableReportTaxTitle');
    const subtitleEl = document.getElementById('tableReportTaxSubtitle');
    if (!bodyContainer || !headContainer) return;

    const data = window.taxReportState.data || [];
    const isEmployeeMode = window.taxReportState.selectedViewMode === 'employee';

    if (titleEl) {
        titleEl.innerText = isEmployeeMode ? 'Rincian Laporan Pajak PPh 21 Per Karyawan' : 'Rincian Laporan Pajak PPh 21 Per Klien & Periode';
    }
    if (subtitleEl) {
        subtitleEl.innerText = isEmployeeMode ? 'Menampilkan rincian PPh 21 & PTKP setiap karyawan' : 'Menampilkan akumulasi PPh 21 per perusahaan & bulan';
    }

    if (isEmployeeMode) {
        headContainer.innerHTML = `
            <tr style="background: #f1f5f9; text-align: center; color: #334155; font-weight: 700; border-bottom: 1px solid #cbd5e1;">
                <th style="width: 40px; padding: 10px;">No</th>
                <th style="padding: 10px; text-align: left;">Nama Karyawan</th>
                <th style="padding: 10px; text-align: left;">Jabatan</th>
                <th style="padding: 10px; text-align: left;">Klien</th>
                <th style="padding: 10px;">Periode</th>
                <th style="padding: 10px; text-align: center; background: #f8fafc; color: #334155;">Status PTKP</th>
                <th style="padding: 10px; text-align: right; background: #f8fafc; color: #334155;">Penghasilan Bruto</th>
                <th style="padding: 10px; text-align: right; background: #f8fafc; color: #334155;">PPh 21</th>
                <th style="padding: 10px; text-align: right; background: #e2e8f0; color: #0f172a;">Gaji Bersih (THP)</th>
            </tr>
        `;
    } else {
        headContainer.innerHTML = `
            <tr style="background: #f1f5f9; text-align: center; color: #334155; font-weight: 700; border-bottom: 1px solid #cbd5e1;">
                <th style="width: 40px; padding: 10px;">No</th>
                <th style="padding: 10px; text-align: left;">Nama Klien</th>
                <th style="padding: 10px;">Periode</th>
                <th style="padding: 10px;">Headcount</th>
                <th style="padding: 10px; text-align: right; background: #f8fafc; color: #334155;">Total Bruto</th>
                <th style="padding: 10px; text-align: right; background: #f8fafc; color: #334155;">Total PPh 21</th>
                <th style="padding: 10px; text-align: right; background: #e2e8f0; color: #0f172a;">Total THP Karyawan</th>
            </tr>
        `;
    }

    if (!data.length) {
        const colspan = isEmployeeMode ? 9 : 7;
        bodyContainer.innerHTML = `
            <tr>
                <td colspan="${colspan}" style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i class="fas fa-folder-open" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                    Belum ada data PPh 21 untuk periode / klien yang dipilih.
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    data.forEach((item, index) => {
        if (isEmployeeMode) {
            html += `
                <tr onclick="showTaxRowDetailModal(${index})" title="Klik untuk lihat rincian detail PPh 21" style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 10px; text-align: center; font-weight: 600; color: #64748b;">${index + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            ${escapeHtml(item.employee_name)}
                            <i class="fas fa-search-plus" style="font-size: 11px; opacity: 0.6;"></i>
                        </span>
                    </td>
                    <td style="padding: 10px; font-weight: 600; color: #334155;">${escapeHtml(item.position_name)}</td>
                    <td style="padding: 10px; font-weight: 600; color: #334155;">${escapeHtml(item.client_name)}</td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">
                            ${escapeHtml(item.bulan_tahun_label)}
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="background: #f1f5f9; color: #334155; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 11px;">
                            ${escapeHtml(item.ptkp_status || 'TK/0')}
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: right; color: #334155; font-variant-numeric: tabular-nums;">${formatRupiah(item.gaji_pokok)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 700; color: #1e293b; font-variant-numeric: tabular-nums;">${formatRupiah(item.pph21)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #0f172a; background: rgba(226, 232, 240, 0.6); font-variant-numeric: tabular-nums;">${formatRupiah(item.take_home_pay)}</td>
                </tr>
            `;
        } else {
            html += `
                <tr onclick="showTaxRowDetailModal(${index})" title="Klik untuk lihat rincian detail PPh 21" style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 12px; text-align: center; font-weight: 600; color: #64748b;">${index + 1}</td>
                    <td style="padding: 12px; font-weight: 700; color: #1e293b;">
                        <span style="display: flex; align-items: center; gap: 6px;">
                            ${escapeHtml(item.client_name)}
                            <i class="fas fa-search-plus" style="font-size: 11px; opacity: 0.6;"></i>
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">
                            ${escapeHtml(item.bulan_tahun_label)}
                        </span>
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 600;">${item.total_karyawan} orang</td>
                    <td style="padding: 12px; text-align: right; color: #334155; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_gaji_pokok)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; color: #1e293b; font-variant-numeric: tabular-nums;">${formatRupiah(item.total_pph21)}</td>
                    <td style="padding: 12px; text-align: right; font-weight: 800; color: #0f172a; background: rgba(226, 232, 240, 0.6); font-variant-numeric: tabular-nums;">${formatRupiah(item.total_take_home_pay)}</td>
                </tr>
            `;
        }
    });

    bodyContainer.innerHTML = html;
}

function exportTaxReportExcel() {
    showToast('Mempersiapkan data Excel Laporan Pajak...', 'info');
    const state = window.taxReportState;
    const queryParams = new URLSearchParams({
        client_id: state.selectedClient || 'all',
        tahun: state.selectedTahun || '2026',
        start_date: state.selectedStartDate || '',
        end_date: state.selectedEndDate || '',
        view_mode: state.selectedViewMode || 'summary',
        export: 'excel'
    });
    window.open(`${window.API}/reports/tax-summary?${queryParams.toString()}`, '_blank');
}
window.exportTaxReportExcel = exportTaxReportExcel;

function exportTaxReportPdf() {
    showToast('Mencetak Laporan Pajak ke PDF...', 'info');
    window.print();
}
window.exportTaxReportPdf = exportTaxReportPdf;

function showTaxRowDetailModal(index) {
    const data = window.taxReportState.data || [];
    const item = data[index];
    if (!item) return;

    const modal   = document.getElementById('modalTaxReportDetail');
    const overlay = document.getElementById('overlay');
    const titleEl = document.getElementById('modalTaxReportTitle');
    const metaEl  = document.getElementById('modalTaxReportHeaderMeta');
    const theadEl = document.getElementById('modalTaxReportThead');
    const tbodyEl = document.getElementById('modalTaxReportTbody');
    const isEmployeeMode = window.taxReportState.selectedViewMode === 'employee';

    if (!modal || !metaEl || !theadEl || !tbodyEl) return;

    const subjectName = isEmployeeMode ? item.employee_name : item.client_name;
    if (titleEl) {
        titleEl.innerHTML = `<i class="fas fa-file-invoice-dollar" style="color: white; margin-right: 8px;"></i> Rincian Pajak PPh 21: ${escapeHtml(subjectName)}`;
    }

    let metaHtml = `
        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Subjek:</strong><br><span style="font-weight: 700; color: #1e293b; font-size: 15px;">${escapeHtml(subjectName)}</span></div>
        <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Periode:</strong><br><span style="font-weight: 700; color: #1e293b;">${escapeHtml(item.bulan_tahun_label)}</span></div>
    `;
    if (isEmployeeMode) {
        metaHtml += `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Jabatan:</strong><br><span style="font-weight: 600; color: #334155;">${escapeHtml(item.position_name)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Status PTKP:</strong><br><span style="font-weight: 700; color: #334155;">${escapeHtml(item.ptkp_status || 'TK/0')}</span></div>
        `;
    } else {
        metaHtml += `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Headcount:</strong><br><span style="font-weight: 700; color: #334155;">${item.total_karyawan} Orang</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total PPh 21:</strong><br><span style="font-weight: 800; color: #1e293b;">${formatRupiah(item.total_pph21)}</span></div>
        `;
    }
    metaEl.innerHTML = metaHtml;

    theadEl.innerHTML = `
        <tr style="background: #f1f5f9; color: #475569; font-weight: 700; text-align: left;">
            <th style="padding: 12px;">Komponen Pajak</th>
            <th style="padding: 12px; text-align: right;">Nominal</th>
        </tr>
    `;

    const bruto = isEmployeeMode ? item.gaji_pokok : item.total_gaji_pokok;
    const pph   = isEmployeeMode ? item.pph21 : item.total_pph21;
    const thp   = isEmployeeMode ? item.take_home_pay : item.total_take_home_pay;

    tbodyEl.innerHTML = `
        <tr style="border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 12px; font-weight: 600; color: #334155;">Penghasilan Bruto (Gaji Pokok / Total Gross)</td>
            <td style="padding: 12px; text-align: right; color: #1e293b; font-weight: 600;">${formatRupiah(bruto)}</td>
        </tr>
        <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
            <td style="padding: 12px; font-weight: 700; color: #334155;">PPh 21 (Dipotong)</td>
            <td style="padding: 12px; text-align: right; font-weight: 800; color: #1e293b;">${formatRupiah(pph)}</td>
        </tr>
        <tr style="background: #e2e8f0; font-weight: 800; color: #0f172a; font-size: 14px;">
            <td style="padding: 14px 12px;">TAKE HOME PAY (Gaji Bersih)</td>
            <td style="padding: 14px 12px; text-align: right; color: #0f172a; font-size: 15px;">${formatRupiah(thp)}</td>
        </tr>
    `;

    if (overlay) overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.style.flexDirection = 'column';
}
window.showTaxRowDetailModal = showTaxRowDetailModal;

function tutupModalTaxReportDetail() {
    const modal   = document.getElementById('modalTaxReportDetail');
    const overlay = document.getElementById('overlay');
    if (modal)   modal.style.display = 'none';

    const openModals = document.querySelectorAll('.modal-skema[style*="display: block"], .modal-skema[style*="display: flex"]');
    if (openModals.length === 0 && overlay) {
        overlay.style.display = 'none';
    }
}
window.tutupModalTaxReportDetail = tutupModalTaxReportDetail;

function showTaxKpiDetailModal(kpiType) {
    const data    = window.taxReportState.data || [];
    const summary = window.taxReportState.summary || {};
    const modal   = document.getElementById('modalTaxReportDetail');
    const overlay = document.getElementById('overlay');
    const titleEl = document.getElementById('modalTaxReportTitle');
    const metaEl  = document.getElementById('modalTaxReportHeaderMeta');
    const theadEl = document.getElementById('modalTaxReportThead');
    const tbodyEl = document.getElementById('modalTaxReportTbody');
    const isEmployeeMode = window.taxReportState.selectedViewMode === 'employee';

    if (!modal || !metaEl || !theadEl || !tbodyEl) return;

    let titleText = 'Detail Metrik Pajak PPh 21';
    let metaHtml  = '';
    let theadHtml = '';
    let tbodyHtml = '';

    if (kpiType === 'pph21') {
        titleText = 'Detail Metrik: Total PPh 21';
        metaHtml  = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total PPh 21:</strong><br><span style="font-weight: 800; color: #1e293b; font-size: 16px;">${formatRupiah(summary.total_pph21 || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Bruto:</strong><br><span style="font-weight: 700; color: #334155;">${formatRupiah(summary.total_bruto || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Bruto</th>
                <th style="padding: 10px; text-align: right;">PPh 21</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj = isEmployeeMode ? item.employee_name : item.client_name;
            const bruto = isEmployeeMode ? item.gaji_pokok : item.total_gaji_pokok;
            const pph   = isEmployeeMode ? item.pph21 : item.total_pph21;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; color: #334155;">${formatRupiah(bruto)}</td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #1e293b;">${formatRupiah(pph)}</td>
                </tr>
            `;
        });
    } else if (kpiType === 'allowance') {
        titleText = 'Detail Metrik: Total Tunjangan Pajak (Gross Up)';
        metaHtml  = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Tunjangan Pajak:</strong><br><span style="font-weight: 800; color: #1e293b; font-size: 16px;">${formatRupiah(summary.total_tax_allowance || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total PPh 21:</strong><br><span style="font-weight: 700; color: #1e293b;">${formatRupiah(summary.total_pph21 || 0)}</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Tunjangan Pajak</th>
                <th style="padding: 10px; text-align: right;">PPh 21</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj  = isEmployeeMode ? item.employee_name : item.client_name;
            const allow = isEmployeeMode ? item.tax_allowance : item.total_tax_allowance;
            const pph   = isEmployeeMode ? item.pph21 : item.total_pph21;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #334155;">${formatRupiah(allow)}</td>
                    <td style="padding: 10px; text-align: right; color: #1e293b;">${formatRupiah(pph)}</td>
                </tr>
            `;
        });
    } else {
        titleText = 'Detail Metrik: Subjek & Penghasilan Bruto Kena Pajak';
        metaHtml  = `
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Bruto:</strong><br><span style="font-weight: 800; color: #1e293b; font-size: 16px;">${formatRupiah(summary.total_bruto || 0)}</span></div>
            <div><strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Total Subjek Pajak:</strong><br><span style="font-weight: 700; color: #334155;">${summary.total_headcount || 0} Orang</span></div>
        `;
        theadHtml = `
            <tr style="background: #f1f5f9; color: #475569; font-weight: 700;">
                <th style="padding: 10px; text-align: center;">No</th>
                <th style="padding: 10px;">Subjek (${isEmployeeMode ? 'Karyawan' : 'Klien'})</th>
                <th style="padding: 10px; text-align: center;">Periode</th>
                <th style="padding: 10px; text-align: right;">Penghasilan Bruto</th>
                <th style="padding: 10px; text-align: right;">PPh 21</th>
            </tr>
        `;
        data.forEach((item, idx) => {
            const subj  = isEmployeeMode ? item.employee_name : item.client_name;
            const bruto = isEmployeeMode ? item.gaji_pokok : item.total_gaji_pokok;
            const pph   = isEmployeeMode ? item.pph21 : item.total_pph21;
            tbodyHtml += `
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 10px; text-align: center; font-weight: 600;">${idx + 1}</td>
                    <td style="padding: 10px; font-weight: 700; color: #1e293b;">${escapeHtml(subj)}</td>
                    <td style="padding: 10px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">${escapeHtml(item.bulan_tahun_label)}</span></td>
                    <td style="padding: 10px; text-align: right; font-weight: 800; color: #334155;">${formatRupiah(bruto)}</td>
                    <td style="padding: 10px; text-align: right; color: #1e293b;">${formatRupiah(pph)}</td>
                </tr>
            `;
        });
    }

    if (titleEl) titleEl.innerHTML = `<i class="fas fa-file-invoice-dollar" style="color: white; margin-right: 8px;"></i> ${titleText}`;
    metaEl.innerHTML  = metaHtml;
    theadEl.innerHTML = theadHtml;
    tbodyEl.innerHTML = tbodyHtml;

    if (overlay) overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.style.flexDirection = 'column';
}
window.showTaxKpiDetailModal = showTaxKpiDetailModal;



