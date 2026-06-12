const XLSX = require('xlsx');
const path = require('path');

function printFile(filename) {
    const filePath = path.join('c:\\Users\\steph\\Downloads', filename);
    console.log(`\n=================== FILE: ${filename} ===================`);
    try {
        const workbook = XLSX.readFile(filePath);
        const sheetName = workbook.SheetNames[0];
        const worksheet = workbook.Sheets[sheetName];
        const data = XLSX.utils.sheet_to_json(worksheet, { raw: false });
        console.log(data.slice(0, 20)); // Print first 20 rows
    } catch (e) {
        console.error(`Error reading ${filename}:`, e.message);
    }
}

printFile('Attendance_Juni_2026_Updated.xlsx');
printFile('Attendance_Juni_2026_Full(1).xlsx');
