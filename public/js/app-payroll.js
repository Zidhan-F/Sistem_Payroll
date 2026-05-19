// === PKWT / KONTRAK ===
async function renderPKWT(){
    if(!selectedClientId)return;
    const tbody=document.getElementById('tabelPKWTBody');
    tbody.innerHTML='<tr><td colspan="6" style="text-align:center;">Memuat...</td></tr>';
    try{
        const r=await fetch(`${API}/contracts/client/${selectedClientId}`);const data=await r.json();
        tbody.innerHTML='';
        if(!Array.isArray(data)||data.length===0){tbody.innerHTML='<tr><td colspan="6" style="text-align:center;color:#888;">Belum ada PKWT. Klik "Buat PKWT" untuk membuat kontrak baru.</td></tr>';return;}
        data.forEach(c=>{
            const isActive=c.status_pkwt==='Aktif';
            tbody.innerHTML+=`<tr>
                <td style="font-weight:600;color:var(--info);">${c.no_kontrak}</td>
                <td><strong>${c.nama_karyawan||'-'}</strong><br><small style="color:#888;">${c.nik||''}</small></td>
                <td>${c.tgl_mulai} s/d ${c.tgl_berakhir}</td>
                <td>${formatRupiah(c.gaji_pokok)}</td>
                <td><span class="badge" style="background:${isActive?'#e8fdf0':'#fde8e8'};color:${isActive?'#2ecc71':'#e74c3c'};padding:4px 8px;border-radius:4px;font-size:11px;">${c.status_pkwt}</span></td>
                <td><div class="action-btns">${isActive?`<button class="btn-icon" style="background:#ef4444;" onclick="terminatePKWT(${c.id})" title="Akhiri Kontrak"><i class="fas fa-times"></i></button>`:''}</div></td>
            </tr>`;
        });
    }catch(e){console.error(e);}
}

function bukaModalPKWT(){
    document.getElementById('modalPKWT').style.display='block';document.getElementById('overlay').style.display='block';
    document.getElementById('formPKWT').reset();document.getElementById('pkwtId').value='';
    const sel=document.getElementById('pkwtEmployeeId');
    sel.innerHTML='<option value="">-- Pilih Karyawan --</option>';
    const ce=employees.filter(e=>e.client_id==selectedClientId);
    ce.forEach(e=>{sel.innerHTML+=`<option value="${e.id}">${e.nama} (${e.nik})</option>`;});
    if(ce.length===0){
        // Load fresh
        fetch(`${API}/employees`).then(r=>r.json()).then(d=>{
            employees=d;const fe=d.filter(e=>e.client_id==selectedClientId);
            fe.forEach(e=>{sel.innerHTML+=`<option value="${e.id}">${e.nama} (${e.nik})</option>`;});
        });
    }
}
function tutupModalPKWT(){document.getElementById('modalPKWT').style.display='none';document.getElementById('overlay').style.display='none';}

document.getElementById('formPKWT')?.addEventListener('submit',async(e)=>{
    e.preventDefault();
    const d={employee_id:document.getElementById('pkwtEmployeeId').value,client_id:selectedClientId,tgl_mulai:document.getElementById('pkwtTglMulai').value,tgl_berakhir:document.getElementById('pkwtTglBerakhir').value,gaji_pokok:document.getElementById('pkwtGajiPokok').value,status_pkwt:'Aktif'};
    const r=await fetch(`${API}/contracts`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});
    if(r.ok){tutupModalPKWT();renderPKWT();renderTableKaryawanClient();showToast('PKWT berhasil dibuat! Gaji karyawan disesuaikan.');}
    else showToast('Gagal membuat PKWT','error');
});

async function terminatePKWT(id){
    if(!await showConfirm('Yakin ingin mengakhiri kontrak ini?'))return;
    const r=await fetch(`${API}/contracts/terminate/${id}`,{method:'POST'});
    if(r.ok){renderPKWT();showToast('Kontrak berhasil diakhiri');}
}

// === KOMPONEN PAYROLL ===
async function renderKomponen(){
    if(!selectedClientId)return;
    const list=document.getElementById('komponenList');
    list.innerHTML='<div style="text-align:center;padding:20px;color:#888;">Memuat komponen...</div>';
    try{
        const r=await fetch(`${API}/clients/components/${selectedClientId}`);const data=await r.json();
        if(!Array.isArray(data)||data.length===0){list.innerHTML='<div style="text-align:center;padding:40px;border:2px dashed #eee;border-radius:12px;background:#fff;color:#888;"><i class="fas fa-puzzle-piece" style="font-size:30px;margin-bottom:10px;display:block;color:#ccc;"></i>Belum ada komponen payroll. Tambahkan tunjangan atau potongan custom.</div>';return;}
        list.innerHTML='';
        data.forEach(c=>{
            const isPot=c.tipe==='Potongan';
            list.innerHTML+=`<div class="komponen-card ${isPot?'potongan':''}">
                <div><strong>${c.nama_komponen}</strong><br><small style="color:#888;">${c.tipe} • ${c.jenis_nilai==='Tetap'?formatRupiah(c.nilai):c.nilai+'% dari Gaji Pokok'}</small></div>
                <div class="action-btns"><button class="btn-icon btn-delete" onclick="hapusKomponen(${c.id})"><i class="fas fa-trash"></i></button></div>
            </div>`;
        });
    }catch(e){console.error(e);}
}

function bukaModalKomponen(){
    document.getElementById('modalKomponen').style.display='block';document.getElementById('overlay').style.display='block';
    document.getElementById('formKomponen').reset();document.getElementById('komponenId').value='';
}
function tutupModalKomponen(){document.getElementById('modalKomponen').style.display='none';document.getElementById('overlay').style.display='none';}

document.getElementById('formKomponen')?.addEventListener('submit',async(e)=>{
    e.preventDefault();
    const d={client_id:selectedClientId,nama_komponen:document.getElementById('komponenNama').value,tipe:document.getElementById('komponenTipe').value,jenis_nilai:document.getElementById('komponenJenis').value,nilai:document.getElementById('komponenNilai').value};
    const r=await fetch(`${API}/clients/components`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});
    if(r.ok){tutupModalKomponen();renderKomponen();showToast('Komponen berhasil ditambahkan!');}
});

async function hapusKomponen(id){
    if(!await showConfirm('Yakin ingin menghapus komponen ini?'))return;
    const r=await fetch(`${API}/clients/components/${id}`,{method:'DELETE'});
    if(r.ok){renderKomponen();showToast('Komponen dihapus');}
}

// === PAYROLL FLOW ===
function updateStepper(step){
    document.querySelectorAll('.payroll-stepper .step').forEach((s,i)=>{
        s.classList.remove('active','done');
        if(i+1<step)s.classList.add('done');
        else if(i+1===step)s.classList.add('active');
    });
    document.querySelectorAll('.payroll-stepper .step-line').forEach((l,i)=>{
        l.classList.toggle('done',i+1<step);
    });
}

async function filterPayrollByClient(){
    if(!selectedClientId)return;
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const container=document.getElementById('tabelPayrollContainer');
    container.innerHTML='<div style="text-align:center;padding:20px;">Memproses data...</div>';
    try{
        const er=await fetch(`${API}/employees`);employees=await er.json();
        const pr=await fetch(`${API}/payroll/status?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
        const existPay=pr.ok?await pr.json():[];
        const ce=employees.filter(e=>e.client_id==selectedClientId);
        if(ce.length===0){container.innerHTML='<div style="text-align:center;padding:20px;">Tidak ada karyawan di klien ini.</div>';updateStepper(1);return;}
        const allApproved=existPay.length>0&&existPay.every(p=>p.status_pembayaran==='Approved');
        const hasPayrolls=existPay.length>0;
        let html='';

        if(!hasPayrolls){
            // Step 1: Input Cut Off
            updateStepper(1);
            html=`<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                <div style="padding:15px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <h4 style="margin:0;color:#1e293b;"><i class="fas fa-edit"></i> Input Data Cut-Off Kehadiran</h4>
                    <button class="btn-save" onclick="prosesPayrollBulk()" style="background:var(--primary-color);"><i class="fas fa-cogs"></i> Proses & Generate Gaji</button>
                </div><table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f1f5f9;text-align:left;font-size:13px;color:#64748b;">
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Nama Karyawan</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Hadir</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Sakit/Izin</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Alpa</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Lembur (Jam)</th>
                </tr></thead><tbody>`;
            ce.forEach(emp=>{
                html+=`<tr style="border-bottom:1px solid #e2e8f0;" class="cutoff-row" data-empid="${emp.id}">
                    <td style="padding:12px 15px;"><strong>${emp.nama}</strong><br><span style="font-size:11px;color:#64748b;">${emp.nama_posisi||'-'}</span></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-hadir" value="22" style="width:60px;padding:5px;text-align:center;"></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-sakit" value="0" style="width:60px;padding:5px;text-align:center;"></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-alpa" value="0" style="width:60px;padding:5px;text-align:center;"></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-lembur" value="0" style="width:60px;padding:5px;text-align:center;"></td></tr>`;
            });
            html+='</tbody></table></div>';
        } else if(allApproved){
            // Step 5: Slip
            updateStepper(5);
            html=buildPayrollTable(ce,existPay,'Approved');
        } else {
            // Step 3-4: Checking & Approval
            updateStepper(3);
            html=`<div style="margin-bottom:15px;display:flex;gap:10px;">
                <button class="btn-save" style="background:#10b981;" onclick="doCheckAndApprove()"><i class="fas fa-check-double"></i> Pengecekan & Approve Semua</button>
                <button class="btn-cancel" onclick="rejectPayrollAll()" style="color:#ef4444;border-color:#fca5a5;"><i class="fas fa-undo"></i> Batal / Revisi</button>
            </div><div id="checkIssuesContainer"></div>`+buildPayrollTable(ce,existPay,'WaitingApproval');
        }
        container.innerHTML=html;
    }catch(e){console.error(e);}
}

function buildPayrollTable(ce,existPay,phase){
    let html=`<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f1f5f9;text-align:left;font-size:13px;color:#64748b;">
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Nama</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Gaji Pokok</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Tunjangan</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Potongan</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Net Salary</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Status</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Action</th>
        </tr></thead><tbody>`;
    ce.forEach(emp=>{
        const p=existPay.find(x=>x.employee_id==emp.id);if(!p)return;
        const approved=p.status_pembayaran==='Approved';
        const statusHtml=approved?'<span class="badge" style="background:#e8fdf0;color:#2ecc71;padding:4px 8px;border-radius:4px;font-size:11px;">Approved</span>':'<span class="badge" style="background:#fff9db;color:#f59f00;padding:4px 8px;border-radius:4px;font-size:11px;">Waiting</span>';
        const actionHtml=approved?`<button class="btn-save" style="font-size:11px;padding:5px 10px;background:var(--info);" onclick="viewSlip(${p.id})"><i class="fas fa-file-invoice"></i> Slip</button>`:`<button class="btn-save" style="font-size:11px;padding:5px 10px;background:#2ecc71;" onclick="approvePayroll(${p.id})"><i class="fas fa-check"></i> Approve</button>`;
        html+=`<tr style="border-bottom:1px solid #e2e8f0;">
            <td style="padding:12px 15px;"><strong>${emp.nama}</strong><br><span style="font-size:11px;color:#64748b;">${emp.nama_posisi||'-'}</span></td>
            <td style="padding:12px 15px;">${formatRupiah(p.gaji_pokok)}</td>
            <td style="padding:12px 15px;color:#10b981;">+ ${formatRupiah(p.total_tunjangan)}</td>
            <td style="padding:12px 15px;color:#ef4444;">- ${formatRupiah(p.total_potongan)}</td>
            <td style="padding:12px 15px;"><strong>${formatRupiah(p.take_home_pay)}</strong></td>
            <td style="padding:12px 15px;">${statusHtml}</td>
            <td style="padding:12px 15px;">${actionHtml}</td></tr>`;
    });
    html+='</tbody></table></div>';
    return html;
}

async function prosesPayrollBulk(){
    if(!await showConfirm('Generate gaji berdasarkan data absensi?'))return;
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const rows=document.querySelectorAll('.cutoff-row'),data=[];
    rows.forEach(row=>{data.push({employee_id:row.dataset.empid,hadir:parseFloat(row.querySelector('.input-hadir').value)||0,sakit:parseFloat(row.querySelector('.input-sakit').value)||0,alpa:parseFloat(row.querySelector('.input-alpa').value)||0,lembur:parseFloat(row.querySelector('.input-lembur').value)||0});});
    const r=await fetch(`${API}/payroll/process-bulk`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({client_id:selectedClientId,bulan,tahun,data})});
    if(r.ok){showToast('Payroll berhasil digenerate! Menunggu Pengecekan & Approval.');filterPayrollByClient();}
    else showToast('Gagal generate gaji','error');
}

async function doCheckAndApprove(){
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const ic=document.getElementById('checkIssuesContainer');
    ic.innerHTML='<div style="padding:15px;background:#f8fafc;border-radius:8px;margin-bottom:15px;">Memeriksa data...</div>';
    updateStepper(3);
    try{
        const r=await fetch(`${API}/payroll/check?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
        const data=await r.json();
        if(data.issues&&data.issues.length>0){
            let ih=`<div style="padding:15px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;margin-bottom:15px;">
                <h4 style="margin:0 0 10px;color:#92400e;"><i class="fas fa-exclamation-triangle"></i> Ditemukan ${data.total_issues} Masalah</h4>`;
            data.issues.forEach(i=>{
                const isCritical=i.issue_type==='Gaji Kosong'||i.issue_type==='Kontrak Expired';
                ih+=`<div class="issue-card ${isCritical?'critical':''}"><i class="fas fa-${isCritical?'times-circle':'exclamation-circle'}" style="color:${isCritical?'#ef4444':'#f59e0b'};"></i><div><strong>${i.nama}</strong> — ${i.issue_type}: ${i.issue_detail}</div></div>`;
            });
            ih+=`<div style="margin-top:15px;"><button class="btn-save" style="background:#f59e0b;" onclick="forceApproveAll()"><i class="fas fa-check"></i> Approve Tetap (Abaikan Warning)</button></div></div>`;
            ic.innerHTML=ih;
        } else {
            ic.innerHTML='<div style="padding:15px;background:#e8fdf0;border:1px solid #a7f3d0;border-radius:12px;margin-bottom:15px;color:#065f46;"><i class="fas fa-check-circle"></i> <strong>Semua data valid!</strong> Melanjutkan approval...</div>';
            await forceApproveAll();
        }
    }catch(e){console.error(e);ic.innerHTML='';}
}

async function forceApproveAll(){
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const r=await fetch(`${API}/payroll/approve-all`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({client_id:selectedClientId,bulan,tahun})});
    if(r.ok){updateStepper(5);showToast('Semua payroll berhasil di-approve! Slip gaji siap.');filterPayrollByClient();}
}

async function approvePayroll(id){
    if(!await showConfirm('Approve payroll ini?'))return;
    const r=await fetch(`${API}/payroll/approve/${id}`,{method:'POST'});
    if(r.ok){showToast('Payroll Approved!');filterPayrollByClient();}
}

async function rejectPayrollAll(){
    if(!await showConfirm('Batalkan semua draft gaji dan ulang input absensi?'))return;
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const pr=await fetch(`${API}/payroll/status?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
    const ep=await pr.json();
    for(const p of ep){if(p.status_pembayaran!=='Approved')await fetch(`${API}/payroll/reject/${p.id}`,{method:'DELETE'});}
    showToast('Draft gaji dibatalkan. Silakan input ulang.');filterPayrollByClient();
}

async function viewSlip(id){
    const r=await fetch(`${API}/payroll/slip/${id}`);const data=await r.json();
    const slip=data.payroll,emp=data.employee,details=data.details,period=data.period;
    document.getElementById('slipContent').innerHTML=`
        <div style="text-align:center;border-bottom:2px solid #eee;padding-bottom:15px;margin-bottom:20px;">
            <h2 style="color:var(--primary-color);">SLIP GAJI</h2>
            <p style="font-size:13px;color:#666;">Periode: ${slip.bulan}/${slip.tahun}${period&&period.pay_date?' • Pay Date: '+period.pay_date:''}</p>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:20px;font-size:14px;">
            <div><p><strong>Nama:</strong> ${emp.nama}</p><p><strong>NIK:</strong> ${emp.nik}</p></div>
            <div style="text-align:right;"><p><strong>Status:</strong> ${slip.status_pembayaran}</p><p><strong>Rekening:</strong> ${emp.no_rekening}</p></div>
        </div>
        <table style="width:100%;font-size:14px;">
            <tr style="background:#f8f9fa;"><th colspan="2">Penerimaan</th></tr>
            <tr><td>Gaji Pokok</td><td style="text-align:right;">${formatRupiah(slip.gaji_pokok)}</td></tr>
            ${details.filter(d=>d.tipe==='Tunjangan').map(d=>`<tr><td>${d.nama_komponen}</td><td style="text-align:right;">${formatRupiah(d.jumlah)}</td></tr>`).join('')}
            <tr style="background:#f8f9fa;"><th colspan="2">Potongan</th></tr>
            ${details.filter(d=>d.tipe==='Potongan').map(d=>`<tr><td>${d.nama_komponen}</td><td style="text-align:right;color:#e74c3c;">- ${formatRupiah(d.jumlah)}</td></tr>`).join('')}
            <tr style="border-top:2px solid #eee;"><th style="padding-top:15px;font-size:16px;">TAKE HOME PAY</th><th style="padding-top:15px;font-size:16px;text-align:right;color:var(--success);">${formatRupiah(slip.take_home_pay)}</th></tr>
        </table>`;
    document.getElementById('modalSlip').style.display='block';document.getElementById('overlay').style.display='block';
}
function tutupModalSlip(){document.getElementById('modalSlip').style.display='none';document.getElementById('overlay').style.display='none';}

// Set default bulan
const now=new Date();
if(document.getElementById('payrollBulan'))document.getElementById('payrollBulan').value=now.getMonth()+1;

// Exports
window.renderPKWT=renderPKWT;window.bukaModalPKWT=bukaModalPKWT;window.tutupModalPKWT=tutupModalPKWT;window.terminatePKWT=terminatePKWT;
window.renderKomponen=renderKomponen;window.bukaModalKomponen=bukaModalKomponen;window.tutupModalKomponen=tutupModalKomponen;window.hapusKomponen=hapusKomponen;
window.filterPayrollByClient=filterPayrollByClient;window.prosesPayrollBulk=prosesPayrollBulk;window.doCheckAndApprove=doCheckAndApprove;window.forceApproveAll=forceApproveAll;window.approvePayroll=approvePayroll;window.rejectPayrollAll=rejectPayrollAll;window.viewSlip=viewSlip;window.tutupModalSlip=tutupModalSlip;
