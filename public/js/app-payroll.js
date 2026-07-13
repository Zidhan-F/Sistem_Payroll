// === PKWT / KONTRAK ===
async function renderPKWT(){
    if(!selectedClientId)return;
    const tbody=document.getElementById('tabelPKWTBody');
    tbody.innerHTML='<tr><td colspan="6" style="text-align:center;">Loading...</td></tr>';
    try{
        const r=await fetch(`${API}/contracts/client/${selectedClientId}`);const data=await r.json();
        tbody.innerHTML='';
        if(!Array.isArray(data)||data.length===0){tbody.innerHTML='<tr><td colspan="6" style="text-align:center;color:#888;">No PKWT/contract available. Click "Create PKWT" to create a new contract.</td></tr>';return;}
        data.forEach(c=>{
            const isActive=c.status_pkwt==='Aktif' || c.status_pkwt==='Active';
            tbody.innerHTML+=`<tr>
                <td style="font-weight:600;color:var(--info);">${c.no_kontrak}</td>
                <td><strong>${c.nama_karyawan||'-'}</strong><br><small style="color:#888;">${c.nik||''}</small></td>
                <td>${c.tgl_mulai} s/d ${c.tgl_berakhir}</td>
                <td>${formatRupiah(c.gaji_pokok)}</td>
                <td><span class="badge" style="background:${isActive?'#e8fdf0':'#fde8e8'};color:${isActive?'#2ecc71':'#e74c3c'};padding:4px 8px;border-radius:4px;font-size:11px;">${isActive?'Active':'Inactive'}</span></td>
                <td><div class="action-btns">${isActive?`<button class="btn-icon" style="background:#ef4444;" onclick="terminatePKWT(${c.id})" title="Terminate Contract"><i class="fas fa-times"></i></button>`:''}</div></td>
            </tr>`;
        });
    }catch(e){console.error(e);}
}

function bukaModalPKWT(){
    document.getElementById('modalPKWT').style.display='block';document.getElementById('overlay').style.display='block';
    document.getElementById('formPKWT').reset();document.getElementById('pkwtId').value='';
    const sel=document.getElementById('pkwtEmployeeId');
    sel.innerHTML='<option value="">-- Select Employee --</option>';
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
    const d={employee_id:document.getElementById('pkwtEmployeeId').value,client_id:selectedClientId,tgl_mulai:document.getElementById('pkwtTglMulai').value,tgl_berakhir:document.getElementById('pkwtTglBerakhir').value,gaji_pokok:document.getElementById('pkwtGajiPokok').value,status_pkwt:'Active'};
    const r=await fetch(`${API}/contracts`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});
    if(r.ok){tutupModalPKWT();renderPKWT();renderTableKaryawanClient();showToast('PKWT created successfully! Employee salary adjusted.');}
    else showToast('Failed to create PKWT','error');
});

async function terminatePKWT(id){
    if(!await showConfirm('Are you sure you want to terminate this contract?'))return;
    const r=await fetch(`${API}/contracts/terminate/${id}`,{method:'POST'});
    if(r.ok){renderPKWT();showToast('Contract terminated successfully');}
}

// === KOMPONEN PAYROLL ===
async function renderKomponen(){
    if(!selectedClientId)return;
    const list=document.getElementById('komponenList');
    list.innerHTML='<div style="text-align:center;padding:20px;color:#888;">Loading components...</div>';
    try{
        const r=await fetch(`${API}/clients/components/${selectedClientId}`);const data=await r.json();
        if(!Array.isArray(data)||data.length===0){list.innerHTML='<div style="text-align:center;padding:40px;border:2px dashed #eee;border-radius:12px;background:#fff;color:#888;"><i class="fas fa-puzzle-piece" style="font-size:30px;margin-bottom:10px;display:block;color:#ccc;"></i>No payroll components available. Add custom allowance or deduction.</div>';return;}
        list.innerHTML='';
        data.forEach(c => {
            const isPot = c.tipe === 'Potongan' || c.tipe === 'Deduction';
            const displayType = c.tipe === 'Potongan' ? 'Deduction' : (c.tipe === 'Tunjangan' ? 'Allowance' : c.tipe);
            const displayVal = c.jenis_nilai === 'Tetap' || c.jenis_nilai === 'Fixed' ? formatRupiah(c.nilai) : c.nilai + '% of Basic Salary';
            list.innerHTML+=`<div class="komponen-card ${isPot?'potongan':''}">
                <div><strong>${c.nama_komponen}</strong><br><small style="color:#888;">${displayType} • ${displayVal}</small></div>
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
    if(r.ok){tutupModalKomponen();renderKomponen();showToast('Component added successfully!');}
});

async function hapusKomponen(id){
    if(!await showConfirm('Are you sure you want to delete this component?'))return;
    const r=await fetch(`${API}/clients/components/${id}`,{method:'DELETE'});
    if(r.ok){renderKomponen();showToast('Component deleted');}
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
    container.innerHTML='<div style="text-align:center;padding:20px;">Processing data...</div>';
    try{
        const er=await fetch(`${API}/employees`);employees=await er.json();
        const pr=await fetch(`${API}/payroll/status?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
        const existPay=pr.ok?await pr.json():[];
        const ce=employees.filter(e=>e.client_id==selectedClientId);
        if(ce.length===0){container.innerHTML='<div style="text-align:center;padding:20px;">No employees in this client.</div>';updateStepper(1);return;}
        const allApproved=existPay.length>0&&existPay.every(p=>p.status_pembayaran==='Approved');
        const hasPayrolls=existPay.length>0;
        let html='';

        if(!hasPayrolls){
            // Step 1: Input Cut Off
            updateStepper(1);
            let summaryData = [];
            try {
                const sr = await fetch(`${API}/payroll/attendance-summary?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
                if (sr.ok) summaryData = await sr.json();
            } catch (err) {
                console.error("Failed to fetch attendance summary", err);
            }

            html=`<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                <div style="padding:15px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
                    <h4 style="margin:0;color:#1e293b;"><i class="fas fa-edit"></i> Attendance Cut-off Data Input</h4>
                    <button class="btn-save" onclick="prosesPayrollBulk()" style="background:var(--primary-color);"><i class="fas fa-cogs"></i> Process & Generate Salary</button>
                </div><table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f1f5f9;text-align:left;font-size:13px;color:#64748b;">
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Employee Name</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Present</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Sick/Leave</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Absent</th>
                    <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Overtime (Hours)</th>
                </tr></thead><tbody>`;
            ce.forEach(emp=>{
                const sum = (Array.isArray(summaryData) ? summaryData.find(s => s.employee_id == emp.id) : null) || {};
                const hadirVal = sum.hadir !== undefined ? sum.hadir : 22;
                const sakitVal = sum.sakit !== undefined ? sum.sakit : 0;
                const alpaVal = sum.alpa !== undefined ? sum.alpa : 0;
                const lemburVal = sum.lembur !== undefined ? sum.lembur : 0;

                html+=`<tr style="border-bottom:1px solid #e2e8f0;" class="cutoff-row" data-empid="${emp.id}">
                    <td style="padding:12px 15px;"><strong>${emp.nama}</strong><br><span style="font-size:11px;color:#64748b;">${emp.nama_posisi||'-'}</span></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-hadir" value="${hadirVal}" style="width:60px;padding:5px;text-align:center;"></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-sakit" value="${sakitVal}" style="width:60px;padding:5px;text-align:center;"></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-alpa" value="${alpaVal}" style="width:60px;padding:5px;text-align:center;"></td>
                    <td style="padding:12px 15px;"><input type="number" class="input-lembur" value="${lemburVal}" style="width:60px;padding:5px;text-align:center;"></td></tr>`;
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
                <button class="btn-save" style="background:#10b981;" onclick="doCheckAndApprove()"><i class="fas fa-check-double"></i> Check & Approve All</button>
                <button class="btn-cancel" onclick="rejectPayrollAll()" style="color:#ef4444;border-color:#fca5a5;"><i class="fas fa-undo"></i> Cancel / Revision</button>
            </div><div id="checkIssuesContainer"></div>`+buildPayrollTable(ce,existPay,'WaitingApproval');
        }
        container.innerHTML=html;
    }catch(e){console.error(e);}
}

function buildPayrollTable(ce,existPay,phase){
    let html=`<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;"><thead><tr style="background:#f1f5f9;text-align:left;font-size:13px;color:#64748b;">
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Name</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Basic Salary</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Allowance</th>
            <th style="padding:12px 15px;border-bottom:1px solid #e2e8f0;">Deduction</th>
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
    if(!await showConfirm('Generate salary based on attendance data?', 'Generate Salary', 'Yes, Generate', 'Cancel', 'primary'))return;
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const rows=document.querySelectorAll('.cutoff-row'),data=[];
    rows.forEach(row=>{data.push({employee_id:row.dataset.empid,hadir:parseFloat(row.querySelector('.input-hadir').value)||0,sakit:parseFloat(row.querySelector('.input-sakit').value)||0,alpa:parseFloat(row.querySelector('.input-alpa').value)||0,lembur:parseFloat(row.querySelector('.input-lembur').value)||0});});
    const r=await fetch(`${API}/payroll/process-bulk`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({client_id:selectedClientId,bulan,tahun,data})});
    if(r.ok){showToast('Payroll generated successfully! Awaiting Check & Approval.');filterPayrollByClient();}
    else showToast('Failed to generate salary','error');
}

async function doCheckAndApprove(){
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const ic=document.getElementById('checkIssuesContainer');
    ic.innerHTML='<div style="padding:15px;background:#f8fafc;border-radius:8px;margin-bottom:15px;">Checking data...</div>';
    updateStepper(3);
    try{
        const r=await fetch(`${API}/payroll/check?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
        const data=await r.json();
        if(data.issues&&data.issues.length>0){
            let ih=`<div style="padding:15px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;margin-bottom:15px;">
                <h4 style="margin:0 0 10px;color:#92400e;"><i class="fas fa-exclamation-triangle"></i> Found ${data.total_issues} Issues</h4>`;
            data.issues.forEach(i=>{
                const isCritical=i.issue_type==='Gaji Kosong'||i.issue_type==='Kontrak Expired'||i.issue_type==='Empty Salary'||i.issue_type==='Expired Contract';
                ih+=`<div class="issue-card ${isCritical?'critical':''}"><i class="fas fa-${isCritical?'times-circle':'exclamation-circle'}" style="color:${isCritical?'#ef4444':'#f59e0b'};"></i><div><strong>${i.nama}</strong> — ${i.issue_type}: ${i.issue_detail}</div></div>`;
            });
            ih+=`<div style="margin-top:15px;"><button class="btn-save" style="background:#f59e0b;" onclick="forceApproveAll()"><i class="fas fa-check"></i> Approve Anyway (Ignore Warning)</button></div></div>`;
            ic.innerHTML=ih;
        } else {
            ic.innerHTML='<div style="padding:15px;background:#e8fdf0;border:1px solid #a7f3d0;border-radius:12px;margin-bottom:15px;color:#065f46;"><i class="fas fa-check-circle"></i> <strong>All data valid!</strong> Continuing approval...</div>';
            await forceApproveAll();
        }
    }catch(e){console.error(e);ic.innerHTML='';}
}

async function forceApproveAll(){
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const r=await fetch(`${API}/payroll/approve-all`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({client_id:selectedClientId,bulan,tahun})});
    if(r.ok){updateStepper(5);showToast('All payroll approved successfully! Salary slips ready.');filterPayrollByClient();}
}

async function approvePayroll(id){
    if(!await showConfirm('Approve this payroll?', 'Confirm Approval', 'Yes, Approve', 'Cancel', 'primary'))return;
    const r=await fetch(`${API}/payroll/approve/${id}`,{method:'POST'});
    if(r.ok){showToast('Payroll Approved!');filterPayrollByClient();}
}

async function rejectPayrollAll(){
    if(!await showConfirm('Cancel all salary drafts and re-input attendance?'))return;
    const bulan=document.getElementById('payrollBulan').value,tahun=document.getElementById('payrollTahun').value;
    const pr=await fetch(`${API}/payroll/status?bulan=${bulan}&tahun=${tahun}&client_id=${selectedClientId}`);
    const ep=await pr.json();
    for(const p of ep){if(p.status_pembayaran!=='Approved')await fetch(`${API}/payroll/reject/${p.id}`,{method:'DELETE'});}
    showToast('Salary draft canceled. Please re-input.');filterPayrollByClient();
}

async function viewSlip(id){
    const r=await fetch(`${API}/payroll/slip/${id}`);const data=await r.json();
    const slip=data.payroll,emp=data.employee,details=data.details,period=data.period;
    
    // Extract values
    const basicSalary = parseFloat(slip.gaji_pokok) || 0;
    let transportAlw = 0;
    let specialAlw = 0;
    let overtime = 0;
    let earlyArrival = 0;
    
    details.filter(d => d.tipe === 'Tunjangan' || d.tipe === 'Allowance').forEach(d => {
        const nameLower = d.nama_komponen.toLowerCase();
        if (nameLower.includes('transport')) {
            transportAlw += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('lembur') || nameLower.includes('overtime')) {
            overtime += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('early arrival')) {
            earlyArrival += parseFloat(d.jumlah) || 0;
        } else {
            specialAlw += parseFloat(d.jumlah) || 0;
        }
    });
    
    let jkk = 0, jkm = 0, jhtc = 0, bpjsCompany = 0, jpCompany = 0;
    details.filter(d => d.tipe === 'Beban Perusahaan' || d.tipe === 'Tanggungan Perusahaan').forEach(d => {
        const nameLower = d.nama_komponen.toLowerCase();
        if (nameLower.includes('jkk')) {
            jkk += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('jkm')) {
            jkm += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('jht')) {
            jhtc += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('kesehatan') || nameLower.includes('bpjs kes')) {
            bpjsCompany += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('jp') || nameLower.includes('pensiun')) {
            jpCompany += parseFloat(d.jumlah) || 0;
        }
    });
    
    let tax = 0, jhte = 0, bpjsEmployee = 0, jpEmployee = 0;
    let iuranWajib = 0, shopDeduction = 0;
    
    details.filter(d => d.tipe === 'Potongan' || d.tipe === 'Deduction').forEach(d => {
        const nameLower = d.nama_komponen.toLowerCase();
        if (nameLower.includes('pph') || nameLower.includes('pajak')) {
            tax += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('jht')) {
            jhte += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('kesehatan') || nameLower.includes('bpjs kes')) {
            bpjsEmployee += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('jp') || nameLower.includes('pensiun')) {
            jpEmployee += parseFloat(d.jumlah) || 0;
        } else if (nameLower.includes('wajib') || nameLower.includes('iuran')) {
            iuranWajib += parseFloat(d.jumlah) || 0;
        } else {
            shopDeduction += parseFloat(d.jumlah) || 0;
        }
    });

    const totalIncome = basicSalary + transportAlw + specialAlw + overtime + earlyArrival + jkk + jkm + jhtc + bpjsCompany + jpCompany;
    const totalDeduction = iuranWajib + shopDeduction + tax + jhte + bpjsEmployee + jpEmployee + jkk + jkm + jhtc + bpjsCompany + jpCompany;
    const hasBpjs = details.some(d => d.nama_komponen.includes('BPJS'));

    document.getElementById('slipContent').innerHTML = `
        <div style="font-family: Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; padding: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
                <span style="font-size: 16px; font-weight: bold; text-transform: uppercase;">
                    ${slip.client_name || '-' }
                </span>
                ${hasBpjs ? `<a href="javascript:void(0)" onclick="bukaDetailBpjsModal('bulk', ${id})" style="font-size: 12px; color: #f39c12; font-weight: bold; text-decoration: none;"><i class="fas fa-calculator"></i> Detail Perhitungan BPJS</a>` : ''}
            </div>
            
            <table style="width: 100%; border: none; margin-bottom: 20px; font-size: 12px; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                        <table style="border: none; border-collapse: collapse;">
                            <tr><td style="padding: 2px 0; font-weight: bold; width: 80px;">PERIOD</td><td style="padding: 2px 0; width: 10px;">:</td><td style="padding: 2px 0;">${slip.bulan}/${slip.tahun}${period && period.pay_date ? ' • Pay Date: ' + period.pay_date : ''}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">NAME</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${emp.nama || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">NIK</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${emp.nik || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">PTKP</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${slip.ptkp_status || '-'}</td></tr>
                        </table>
                    </td>
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0;">
                        <table style="border: none; border-collapse: collapse; margin-left: auto;">
                            <tr><td style="padding: 2px 0; font-weight: bold; width: 80px;">POSITION</td><td style="padding: 2px 0; width: 10px;">:</td><td style="padding: 2px 0;">${emp.position_name || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">DEPT</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${emp.department_name || '-'}</td></tr>
                            <tr><td style="padding: 2px 0; font-weight: bold;">NPWP</td><td style="padding: 2px 0;">:</td><td style="padding: 2px 0;">${emp.npwp || '-'}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <hr style="border: none; border-top: 1px solid #000; margin-bottom: 15px;">
            
            <table style="width: 100%; border: none; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                <tr>
                    <!-- Left Column: INCOME -->
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0 15px 0 0;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">BASIC SALARY</td><td style="padding: 4px 0; text-align: right; width: 40%;">${formatRupiah(basicSalary)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">TRANSPORT ALW</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(transportAlw)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">SPECIAL ALW</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(specialAlw)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">OVERTIME</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(overtime)}</td></tr>
                            ${earlyArrival > 0 ? `<tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">EARLY ARRIVAL</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(earlyArrival)}</td></tr>` : ''}
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JKK</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(jkk)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JKM</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(jkm)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JHTC</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(jhtc)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">BPJS by Company</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(bpjsCompany)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JAMINAN PENSIUN COMP</td><td style="padding: 4px 0; text-align: right;">${formatRupiah(jpCompany)}</td></tr>
                        </table>
                    </td>
                    <!-- Vertical Divider -->
                    <td style="width: 1px; border-left: 1px solid #000; padding: 0;"></td>
                    <!-- Right Column: DEDUCTION -->
                    <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 15px;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">IURAN WAJIB</td><td style="padding: 4px 0; text-align: right; color: #e74c3c; width: 40%;">${formatRupiah(iuranWajib)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">SHOP DEDUCTION</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(shopDeduction)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">TAX</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(tax)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JHTE</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jhte)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">BPJS by Employee</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(bpjsEmployee)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JAMINAN PENSIUN EMP</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jpEmployee)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JKK</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jkk)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JKM</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jkm)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JHTC</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jhtc)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">BPJS by Company</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(bpjsCompany)}</td></tr>
                            <tr><td style="padding: 4px 0; font-weight: bold; text-transform: uppercase;">JAMINAN PENSIUN COMP</td><td style="padding: 4px 0; text-align: right; color: #e74c3c;">${formatRupiah(jpCompany)}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <hr style="border: none; border-top: 1px solid #000; margin: 15px 0;">
            
            <table style="width: 100%; border: none; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                <tr>
                    <td style="width: 50%; vertical-align: middle; border: none; padding: 0 15px 0 0;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">TOTAL INCOME</td><td style="padding: 4px 0; text-align: right; font-weight: bold; width: 40%;">${formatRupiah(totalIncome)}</td></tr>
                        </table>
                    </td>
                    <td style="width: 1px; border-left: 1px solid #000; padding: 0;"></td>
                    <td style="width: 50%; vertical-align: middle; border: none; padding: 0 0 0 15px;">
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr><td style="padding: 4px 0; font-weight: bold; width: 60%; text-transform: uppercase;">TOTAL DEDUCTION</td><td style="padding: 4px 0; text-align: right; font-weight: bold; color: #e74c3c; width: 40%;">${formatRupiah(totalDeduction)}</td></tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <hr style="border: none; border-top: 1px solid #000; margin: 15px 0;">
            
            <table style="width: 100%; border: none; border-collapse: collapse; font-size: 12px; font-weight: bold; background-color: #fafafa; border: 1px solid #eee;">
                <tr>
                    <td style="padding: 12px 15px; text-transform: uppercase; width: 50%;">TOTAL INCOME THP</td>
                    <td style="padding: 12px 15px; text-align: right; color: var(--success); font-size: 14px; width: 50%;">${formatRupiah(slip.take_home_pay)}</td>
                </tr>
            </table>
        </div>
        `;
    document.getElementById('modalSlip').style.display='block';document.getElementById('overlay').style.display='block';
}
function tutupModalSlip(){
    document.getElementById('modalSlip').style.display='none';
    if(document.getElementById('modalDetailBpjs')) document.getElementById('modalDetailBpjs').style.display='none';
    document.getElementById('overlay').style.display='none';
}

// Set default bulan
const now=new Date();
if(document.getElementById('payrollBulan'))document.getElementById('payrollBulan').value=now.getMonth()+1;

// Exports
window.renderPKWT=renderPKWT;window.bukaModalPKWT=bukaModalPKWT;window.tutupModalPKWT=tutupModalPKWT;window.terminatePKWT=terminatePKWT;
window.renderKomponen=renderKomponen;window.bukaModalKomponen=bukaModalKomponen;window.tutupModalKomponen=tutupModalKomponen;window.hapusKomponen=hapusKomponen;
window.filterPayrollByClient=filterPayrollByClient;window.prosesPayrollBulk=prosesPayrollBulk;window.doCheckAndApprove=doCheckAndApprove;window.forceApproveAll=forceApproveAll;window.approvePayroll=approvePayroll;window.rejectPayrollAll=rejectPayrollAll;window.viewSlip=viewSlip;window.tutupModalSlip=tutupModalSlip;
