const fs = require('fs');
const path = require('path');

const downloadsDir = 'c:\\Users\\steph\\Downloads';
try {
    const files = fs.readdirSync(downloadsDir);
    const excelFiles = files.filter(f => f.endsWith('.xlsx') || f.endsWith('.xls') || f.endsWith('.csv'));
    console.log("EXCEL FILES IN DOWNLOADS MODIFIED TODAY:");
    excelFiles.forEach(f => {
        const fullPath = path.join(downloadsDir, f);
        const stats = fs.statSync(fullPath);
        const today = new Date();
        if (stats.mtime.toDateString() === today.toDateString()) {
            console.log(`${f} | Size: ${stats.size} bytes | Modified: ${stats.mtime}`);
        }
    });
} catch (e) {
    console.error(e.message);
}
