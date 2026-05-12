const express = require('express');
const cors = require('cors');
const db = require('./db');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

// Inisialisasi Database (Create Tables if not exists)
const initDb = async () => {
    try {
        // Tabel Client
        await db.query(`
            CREATE TABLE IF NOT EXISTS clients (
                id SERIAL PRIMARY KEY,
                nama VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                sektor VARCHAR(100),
                nib VARCHAR(50),
                npwp VARCHAR(50),
                tgl_gabung DATE,
                alamat TEXT
            );
        `);

        // Tabel Divisi
        await db.query(`
            CREATE TABLE IF NOT EXISTS divisions (
                id SERIAL PRIMARY KEY,
                nama VARCHAR(255) NOT NULL
            );
        `);

        // Tabel Department
        await db.query(`
            CREATE TABLE IF NOT EXISTS departments (
                id SERIAL PRIMARY KEY,
                nama VARCHAR(255) NOT NULL,
                division_id INTEGER REFERENCES divisions(id) ON DELETE CASCADE
            );
        `);

        // Tabel Positions
        await db.query(`
            CREATE TABLE IF NOT EXISTS positions (
                id SERIAL PRIMARY KEY,
                nama VARCHAR(255) NOT NULL,
                employee_name VARCHAR(255),
                email VARCHAR(255),
                phone VARCHAR(50),
                department_id INTEGER REFERENCES departments(id) ON DELETE CASCADE
            );
        `);

        // Tabel Users (Login)
        await db.query(`
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(100) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'admin'
            );
        `);

        // Seed Admin User (admin123)
        await db.query(`
            INSERT INTO users (username, email, password, role)
            VALUES ('admin', 'admin@payroll.com', 'admin123', 'admin')
            ON CONFLICT (username) DO NOTHING;
        `);

        console.log("Struktur tabel database siap!");
    } catch (err) {
        console.error("Gagal menginisialisasi tabel:", err);
    }
};

initDb();

// --- API ENDPOINTS CLIENT ---
app.get('/api/clients', async (req, res) => {
    try {
        const result = await db.query('SELECT * FROM clients ORDER BY id DESC');
        res.json(result.rows);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.post('/api/clients', async (req, res) => {
    const { nama, email, sektor, nib, npwp, tgl_gabung, alamat } = req.body;
    try {
        const result = await db.query(
            'INSERT INTO clients (nama, email, sektor, nib, npwp, tgl_gabung, alamat) VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING *',
            [nama, email, sektor, nib, npwp, tgl_gabung, alamat]
        );
        res.json(result.rows[0]);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.put('/api/clients/:id', async (req, res) => {
    const { id } = req.params;
    const { nama, email, sektor, nib, npwp, tgl_gabung, alamat } = req.body;
    try {
        const result = await db.query(
            'UPDATE clients SET nama=$1, email=$2, sektor=$3, nib=$4, npwp=$5, tgl_gabung=$6, alamat=$7 WHERE id=$8 RETURNING *',
            [nama, email, sektor, nib, npwp, tgl_gabung, alamat, id]
        );
        res.json(result.rows[0]);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.delete('/api/clients/:id', async (req, res) => {
    const { id } = req.params;
    console.log(`Menerima permintaan hapus klien ID: ${id}`);
    try {
        const result = await db.query('DELETE FROM clients WHERE id = $1', [id]);
        console.log(`Berhasil menghapus klien. Rows affected: ${result.rowCount}`);
        res.json({ message: 'Client dihapus' });
    } catch (err) {
        console.error('Error saat hapus klien:', err.message);
        res.status(500).json({ error: err.message });
    }
});

// --- API ENDPOINTS ORGANISASI ---

// Get All (Struktur Hierarki)
app.get('/api/org', async (req, res) => {
    try {
        const divs = await db.query('SELECT * FROM divisions ORDER BY id ASC');
        const depts = await db.query('SELECT * FROM departments ORDER BY id ASC');
        const pos = await db.query('SELECT * FROM positions ORDER BY id ASC');

        // Rakit menjadi JSON hierarkis
        const structure = divs.rows.map(div => ({
            ...div,
            departments: depts.rows.filter(d => d.division_id === div.id).map(dept => ({
                ...dept,
                positions: pos.rows.filter(p => p.department_id === dept.id)
            }))
        }));

        res.json(structure);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Create Division
app.post('/api/divisions', async (req, res) => {
    const { nama } = req.body;
    try {
        const result = await db.query('INSERT INTO divisions (nama) VALUES ($1) RETURNING *', [nama]);
        res.json(result.rows[0]);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Create Department
app.post('/api/departments', async (req, res) => {
    const { nama, division_id } = req.body;
    try {
        const result = await db.query('INSERT INTO departments (nama, division_id) VALUES ($1, $2) RETURNING *', [nama, division_id]);
        res.json(result.rows[0]);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Create Position
app.post('/api/positions', async (req, res) => {
    const { nama, employee_name, email, phone, department_id } = req.body;
    try {
        const result = await db.query(
            'INSERT INTO positions (nama, employee_name, email, phone, department_id) VALUES ($1, $2, $3, $4, $5) RETURNING *',
            [nama, employee_name, email, phone, department_id]
        );
        res.json(result.rows[0]);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Update Generic (Division/Dept/Pos)
app.put('/api/org/:type/:id', async (req, res) => {
    const { type, id } = req.params;
    const { nama, employee_name, email, phone } = req.body;
    try {
        let query = '';
        let params = [];
        if (type === 'divisi') {
            query = 'UPDATE divisions SET nama=$1 WHERE id=$2 RETURNING *';
            params = [nama, id];
        } else if (type === 'department') {
            query = 'UPDATE departments SET nama=$1 WHERE id=$2 RETURNING *';
            params = [nama, id];
        } else if (type === 'posisi') {
            query = 'UPDATE positions SET nama=$1, employee_name=$2, email=$3, phone=$4 WHERE id=$5 RETURNING *';
            params = [nama, employee_name, email, phone, id];
        }
        const result = await db.query(query, params);
        res.json(result.rows[0]);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Delete Generic
app.delete('/api/org/:type/:id', async (req, res) => {
    const { type, id } = req.params;
    console.log(`Menerima permintaan hapus: Tipe=${type}, ID=${id}`);
    
    try {
        let table = '';
        if (type === 'divisi') table = 'divisions';
        else if (type === 'department') table = 'departments';
        else if (type === 'posisi') table = 'positions';
        
        if (!table) {
            console.error(`Tipe tidak dikenal: ${type}`);
            return res.status(400).json({ error: 'Tipe organisasi tidak valid' });
        }

        const result = await db.query(`DELETE FROM ${table} WHERE id = $1`, [id]);
        console.log(`Berhasil menghapus dari ${table}. Row count: ${result.rowCount}`);
        res.json({ message: 'Deleted successfully' });
    } catch (err) {
        console.error('Error saat menghapus:', err.message);
        res.status(500).json({ error: err.message });
    }
});

// --- API ENDPOINT LOGIN ---
app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    try {
        const result = await db.query('SELECT * FROM users WHERE username = $1 AND password = $2', [username, password]);
        if (result.rows.length > 0) {
            const user = result.rows[0];
            res.json({ 
                success: true, 
                message: 'Login berhasil', 
                user: { username: user.username, email: user.email, role: user.role } 
            });
        } else {
            res.status(401).json({ success: false, message: 'Username atau password salah' });
        }
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});
