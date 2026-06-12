const XLSX = require('xlsx');
const path = require('path');

const API_URL = 'http://localhost:8080/index.php/api';
const clientId = 1080; // SIKET AAA
const periodId = 25; // Juni 2026

async function run() {
    try {
        // 1. Get current period attendance/employees
        console.log("Fetching employees from API...");
        const resEmp = await fetch(`${API_URL}/attendance/${periodId}?client_id=${clientId}`);
        if (!resEmp.ok) {
            throw new Error(`Failed to fetch employees: ${resEmp.statusText}`);
        }
        const employees = await resEmp.json();
        console.log(`Loaded ${employees.length} employees:`, employees.map(e => ({ id: e.employee_id, name: e.employee_name, pkwt_id: e.pkwt_id })));

        // 2. Parse Excel file
        const filePath = 'c:\\Users\\steph\\Downloads\\Attendance_Juni_2026_Updated.xlsx';
        console.log(`Parsing Excel file: ${filePath}`);
        const workbook = XLSX.readFile(filePath);
        const sheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[sheetName];
        const rows = XLSX.utils.sheet_to_json(worksheet, { raw: false });
        console.log(`Parsed ${rows.length} rows from Excel.`);

        // Map Excel rows to the structure expected by API
        const excelByEmp = {};
        rows.forEach(row => {
            const empId = String(row['Employee ID'] || '').trim();
            const empName = String(row['Nama'] || '').trim();
            const tglVal = row['Tgl dan Hari'] || '';
            const checkin = String(row['Jam Masuk'] || '').trim();
            const checkout = String(row['Jam Keluar'] || '').trim();
            const status = String(row['Status'] || 'Hadir').trim();
            const shift = String(row['Shift'] || '').trim();

            const key = empId || empName.toLowerCase();
            if (!key) return;

            if (!excelByEmp[key]) {
                excelByEmp[key] = [];
            }
            excelByEmp[key].push({
                dateVal: tglVal,
                checkin: checkin,
                checkout: checkout,
                status: status,
                shift: shift
            });
        });

        // Date parser (copied from app-process.js)
        function parseExcelDate(val) {
            if (val instanceof Date) return val;
            if (typeof val === 'number') {
                return new Date((val - 25569) * 86400 * 1000);
            }
            if (typeof val === 'string') {
                const num = parseFloat(val);
                if (!isNaN(num) && num > 40000 && num < 60000) {
                    return new Date((num - 25569) * 86400 * 1000);
                }
                const bulanID = {
                    'januari': 0, 'februari': 1, 'maret': 2, 'april': 3,
                    'mei': 4, 'juni': 5, 'juli': 6, 'agustus': 7,
                    'september': 8, 'oktober': 9, 'november': 10, 'desember': 11
                };
                const lower = val.toLowerCase().trim();
                const matchID = lower.match(/(?:senin|selasa|rabu|kamis|jumat|sabtu|minggu)?\s*(\d{1,2})\s+([a-z]+)\s+(\d{4})/);
                if (matchID) {
                    const tgl = parseInt(matchID[1]);
                    const bulan = bulanID[matchID[2]];
                    const thn = parseInt(matchID[3]);
                    if (bulan !== undefined) {
                        return new Date(thn, bulan, tgl);
                    }
                }
                let clean = val.replace(/senin|selasa|rabu|kamis|jumat|sabtu|minggu/gi, '').trim();
                Object.entries(bulanID).forEach(([name, idx]) => {
                    clean = clean.replace(new RegExp(name, 'gi'), String(idx + 1).padStart(2, '0'));
                });
                clean = clean.replace(/[^0-9\/\-]/g, ' ').trim().replace(/\s+/g, '-');
                let parts = clean.split(/[-/]/);
                if (parts.length === 3) {
                    if (parts[0].length === 4) {
                        return new Date(parts[0], parts[1] - 1, parts[2]);
                    } else {
                        return new Date(parts[2], parts[1] - 1, parts[0]);
                    }
                }
                let parsed = Date.parse(clean);
                if (!isNaN(parsed)) return new Date(parsed);
            }
            return null;
        }

        const parsedDailyLogs = [];
        const parsedAttendanceData = [];
        const payoutPeriodStr = '6-2026';

        employees.forEach(emp => {
            const empId = String(emp.employ_id || emp.nik || '').trim();
            const empName = String(emp.employee_name || '').trim();

            let matchedRows = excelByEmp[empId] || excelByEmp[empName.toLowerCase()];
            if (!matchedRows) {
                const matchingKey = Object.keys(excelByEmp).find(k => 
                    k.toLowerCase() === empName.toLowerCase() || 
                    empName.toLowerCase().includes(k.toLowerCase()) ||
                    k.toLowerCase().includes(empName.toLowerCase())
                );
                if (matchingKey) {
                    matchedRows = excelByEmp[matchingKey];
                }
            }

            if (!matchedRows) {
                console.warn(`⚠️ Employee not found in Excel: "${empName}"`);
                return;
            }

            const workDaysConfig = parseInt(emp.employee_hari_kerja || emp.position_hari_kerja || 5);
            let totalHadir = 0;
            let totalLembur = 0;
            let totalAlfa = 0;

            matchedRows.forEach(row => {
                const dateObj = parseExcelDate(row.dateVal);
                if (!dateObj) return;

                const yyyy = dateObj.getFullYear();
                const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
                const dd = String(dateObj.getDate()).padStart(2, '0');
                const formattedDate = `${yyyy}-${mm}-${dd}`;

                parsedDailyLogs.push({
                    employee_id: emp.employee_id,
                    tanggal: formattedDate,
                    jam_masuk: row.checkin && row.checkin !== 'null' ? row.checkin : '',
                    jam_keluar: row.checkout && row.checkout !== 'null' ? row.checkout : '',
                    status: row.status || 'Hadir',
                    keterangan: '',
                    shift_name: row.shift,
                    payout_period: payoutPeriodStr
                });

                const dayOfWeek = dateObj.getDay();
                const statusNorm = row.status.toLowerCase().trim();

                let isRestDay = false;
                if (workDaysConfig === 5) {
                    isRestDay = (dayOfWeek === 0 || dayOfWeek === 6);
                } else if (workDaysConfig === 6) {
                    isRestDay = (dayOfWeek === 0);
                }

                let hasTimes = false;
                if (row.checkin && row.checkout && row.checkin !== 'null' && row.checkout !== 'null') {
                    hasTimes = true;
                }

                const isPresent = (statusNorm === 'hadir' || statusNorm === 'present' || (statusNorm === '' && hasTimes));
                if (isPresent) {
                    totalHadir++;
                }

                const isAbsent = (statusNorm === 'alfa' || statusNorm === 'absent' || statusNorm === 'missing');
                if (isAbsent && !isRestDay) {
                    totalAlfa++;
                }
            });

            const gajiPokok = parseFloat(emp.gaji_pokok || 0);
            const divider = (workDaysConfig === 5) ? 22 : ((workDaysConfig === 6) ? 26 : 30);
            const dendaAbsenPerDay = gajiPokok / divider;
            const totalPotongan = totalAlfa * dendaAbsenPerDay;

            parsedAttendanceData.push({
                period_id: periodId,
                pkwt_id: emp.pkwt_id,
                hari_kerja: totalHadir,
                jam_lembur: 0.0, // Backend calculates this from daily logs
                potongan_absensi: parseFloat(totalPotongan.toFixed(2)),
                bonus_tambahan: parseFloat(emp.bonus_tambahan || 0)
            });
        });

        console.log(`Constructed payload: ${parsedDailyLogs.length} daily logs, ${parsedAttendanceData.length} summary logs.`);

        // 3. POST to /api/attendance-logs/bulk
        console.log("Sending daily logs to API...");
        const resLogs = await fetch(`${API_URL}/attendance-logs/bulk`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ logs: parsedDailyLogs })
        });
        const resLogsText = await resLogs.text();
        console.log("Daily logs response:", resLogsText);

        // 4. POST to /api/attendance-bulk
        console.log("Sending summary logs to API...");
        const resSummary = await fetch(`${API_URL}/attendance-bulk`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(parsedAttendanceData)
        });
        const resSummaryText = await resSummary.text();
        console.log("Summary response:", resSummaryText);

    } catch (e) {
        console.error("Error running simulation:", e);
    }
}

run();
