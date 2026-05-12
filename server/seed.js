const { Pool } = require('pg');
const pool = new Pool({
    connectionString: 'postgresql://postgres.vwbgxliuoimxtbpduhii:2442zidhan24@aws-1-ap-southeast-1.pooler.supabase.com:6543/postgres',
    ssl: { rejectUnauthorized: false }
});

async function seed() {
    try {
        const resDiv = await pool.query("INSERT INTO divisions (nama) VALUES ('Divisi Operasional') RETURNING id");
        const divId = resDiv.rows[0].id;
        const resDept = await pool.query("INSERT INTO departments (nama, division_id) VALUES ('Department Payroll', $1) RETURNING id", [divId]);
        const deptId = resDept.rows[0].id;
        await pool.query("INSERT INTO positions (nama, employee_name, email, phone, department_id) VALUES ('Manager', 'Angelique Karel Sonya Sefia', 'angelique@payroll.com', '0812345678', $1)", [deptId]);
        console.log('Data contoh berhasil dimasukkan!');
    } catch (e) {
        console.error('Gagal memasukkan data:', e);
    } finally {
        await pool.end();
    }
}
seed();
