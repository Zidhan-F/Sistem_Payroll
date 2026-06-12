const XLSX = require('xlsx');
const path = require('path');
const downloadsDir = 'c:\\Users\\steph\\Downloads';

const filePath = path.join(downloadsDir, 'Attendance_Juni_2026_Updated.xlsx');
const workbook = XLSX.readFile(filePath);
const sheetName = workbook.SheetNames[0];
const worksheet = workbook.Sheets[sheetName];
const data = XLSX.utils.sheet_to_json(worksheet, { raw: false });

data.forEach((row, i) => {
    console.log(`[${String(i).padStart(2, ' ')}] Emp: ${row['Employee ID']} | Name: ${row['Nama']} | Date: ${row['Tgl dan Hari']} | In: ${row['Jam Masuk']} | Out: ${row['Jam Keluar']}`);
});
