// === ORG TREE ===
const API = window.API || '/api';
window.toggleNode = function(el){
    const c=el.nextElementSibling, i=el.querySelector('.toggle-icon');
    if(c.style.display==='none'){c.style.display=c.dataset.displayType||'block';if(i)i.style.transform='rotate(0deg)';}
    else{if(!c.dataset.displayType)c.dataset.displayType=window.getComputedStyle(c).display||'block';c.style.display='none';if(i)i.style.transform='rotate(-90deg)';}
};

async function renderClientOrg(clientId){
    selectedClientId=clientId;
    const container=document.getElementById('clientOrgContainer');
    if(!container)return;
    container.innerHTML='<div class="empty-state">Memuat Struktur...</div>';
    try{
        const r=await fetch(`${API}/org?client_id=${clientId}`);
        orgData=await r.json();
        container.innerHTML='';
        if(!Array.isArray(orgData)||orgData.length===0){container.innerHTML+='<div class="payroll-empty-state"><i class="fas fa-sitemap"></i><h4>Belum Ada Struktur</h4><p>Silakan klik tombol <b>Tambah Divisi</b> di atas untuk menyusun struktur.</p></div>';return;}
        orgData.forEach(div=>{
            let h=`<div class="org-level" style="margin-bottom:30px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#fff;">
                <div class="level-header" style="background:#f1f5f9;padding:12px 20px;cursor:pointer;border-bottom:1px solid #e2e8f0;" onclick="toggleNode(this)">
                    <div class="level-title" style="font-size:15px;font-weight:800;color:#1e293b;gap:10px;"><i class="fas fa-chevron-down toggle-icon" style="color:#64748b;font-size:12px;transition:transform 0.2s;"></i><i class="fas fa-sitemap" style="color:var(--primary-color);"></i> ${div.nama}</div>
                    <div class="action-btns" onclick="event.stopPropagation()">
                        <button class="btn-nested-add" style="background:var(--info);border:none;color:white;padding:5px 10px;border-radius:6px;font-size:11px;cursor:pointer;" onclick="bukaModalOrg('department','tambah',null,${div.id})"><i class="fas fa-plus"></i> Dept</button>
                        <button class="btn-icon btn-edit" onclick="bukaModalOrg('divisi','edit',${div.id},${clientId})"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon btn-delete" onclick="hapusOrg('divisi',${div.id})"><i class="fas fa-trash"></i></button>
                    </div></div>
                <div class="nested-container" style="padding:15px;">`;
            if(div.departments&&div.departments.length>0){
                div.departments.forEach(dept=>{
                    h+=`<div class="org-level" style="margin-bottom:20px;border-left:3px solid var(--info);padding-left:15px;">
                        <div class="level-header" style="margin-bottom:10px;cursor:pointer;padding:5px;border-radius:6px;" onclick="toggleNode(this)">
                            <div class="level-title" style="font-size:14px;font-weight:700;color:#334155;gap:8px;"><i class="fas fa-chevron-down toggle-icon" style="color:#94a3b8;font-size:10px;"></i><i class="fas fa-building" style="color:var(--info);"></i> ${dept.nama}</div>
                            <div class="action-btns" onclick="event.stopPropagation()">
                                <button class="btn-nested-add" style="background:#10b981;border:none;color:white;padding:4px 8px;border-radius:6px;font-size:10px;cursor:pointer;" onclick="bukaModalOrg('posisi','tambah',null,${dept.id})"><i class="fas fa-user-plus"></i> Posisi</button>
                                <button class="btn-icon btn-edit" onclick="bukaModalOrg('department','edit',${dept.id},${div.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon btn-delete" onclick="hapusOrg('department',${dept.id})"><i class="fas fa-trash"></i></button>
                            </div></div>
                        <div class="nested-container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;" data-display-type="grid">`;
                    if(dept.positions&&dept.positions.length>0){
                        dept.positions.forEach(pos=>{
                            const emp=pos.employees&&pos.employees.length>0?pos.employees[0]:null;
                            h+=`<div style="display:flex;align-items:center;gap:12px;background:white;padding:12px;border-radius:10px;border:1px solid #eef2f7;box-shadow:0 2px 4px rgba(0,0,0,0.01);${emp?'border-left:4px solid var(--primary-color);':'border:1px dashed #cbd5e0;background:#f8fafc;'}">
                                <div style="width:36px;height:36px;background:${emp?'#fff9f0':'#f1f5f9'};color:${emp?'var(--primary-color)':'#94a3b8'};border-radius:8px;display:grid;place-items:center;font-size:14px;"><i class="fas ${emp?'fa-user-tie':'fa-user-plus'}"></i></div>
                                <div style="display:flex;flex-direction:column;flex-grow:1;"><span style="font-size:9px;font-weight:800;text-transform:uppercase;color:#64748b;">${pos.level ? pos.level + ' — ' : ''}${pos.nama}</span><span style="font-weight:700;color:#1e293b;font-size:13px;">${emp?emp.nama:''}</span></div>
                                <div class="action-btns" style="gap:4px;"><button class="btn-icon btn-edit" style="padding:4px;font-size:10px;" onclick="bukaModalOrg('posisi','edit',${pos.id},${dept.id})"><i class="fas fa-edit"></i></button><button class="btn-icon btn-delete" style="padding:4px;font-size:10px;" onclick="hapusOrg('posisi',${pos.id})"><i class="fas fa-trash"></i></button></div></div>`;
                        });
                    } else h+='<div class="empty-state" style="font-size:11px;color:#94a3b8;grid-column:1/-1;">Belum ada posisi.</div>';
                    h+=`</div></div>`;
                });
            } else h+='<div class="empty-state" style="font-size:12px;color:#94a3b8;text-align:center;padding:10px;">Belum ada departemen.</div>';
            h+=`</div></div>`;
            container.innerHTML+=h;
        });
    }catch(e){console.error(e);}
}

const quickBadges = {
    divisi: ['IT', 'HRD', 'Finance', 'Marketing', 'Operations'],
    department: ['Software Engineering', 'Recruitment', 'Accounting', 'Sales', 'Production'],
    posisi: ['Staff', 'Supervisor', 'Manager', 'Senior', 'Intern']
};

function tambahDeptInline() {
    const divId = document.getElementById('empDivisionId').value;
    if (!divId) {
        showToast('Silakan pilih Divisi terlebih dahulu!', 'error');
        return;
    }
    bukaModalOrg('department', 'tambah', null, divId);
}

function tambahPosisiInline() {
    const deptId = document.getElementById('empDepartmentId').value;
    if (!deptId) {
        showToast('Silakan pilih Department terlebih dahulu!', 'error');
        return;
    }
    bukaModalOrg('posisi', 'tambah', null, deptId);
}

// === MODAL ORG ===
function bukaModalOrg(type,mode,id=null,parentId=null){
    const m=document.getElementById('modalOrg'),o=document.getElementById('overlay');
    m.style.display='block';o.style.display='block';
    
    // Reset semua field terlebih dahulu (agar tidak ada sisa data lama)
    document.getElementById('orgName').value='';
    if(document.getElementById('posEmployeeName'))document.getElementById('posEmployeeName').value='';
    if(document.getElementById('posNik'))document.getElementById('posNik').value='';
    if(document.getElementById('posEmail'))document.getElementById('posEmail').value='';
    if(document.getElementById('posPhone'))document.getElementById('posPhone').value='';
    if(document.getElementById('posLevel'))document.getElementById('posLevel').value='';
    
    document.getElementById('orgType').value=type;document.getElementById('orgId').value=id;document.getElementById('orgParentId').value=parentId;
    const lbl=type==='divisi'?'Divisi':type==='department'?'Departemen':'Posisi/Jabatan';
    document.getElementById('modalOrgTitle').innerText=(mode==='edit'?'Edit ':'Tambah ')+lbl;
    document.getElementById('labelOrgName').innerText='Nama '+lbl;
    document.getElementById('orgName').placeholder='Masukkan nama '+lbl.toLowerCase();
    document.getElementById('posEmployeeField').style.display=type==='posisi'?'block':'none';
    document.getElementById('posExtraFields').style.display=type==='posisi'?'block':'none';
    
    // Render quick-select badges
    const badgeContainer = document.getElementById('quickBadgeContainer');
    if (badgeContainer) {
        badgeContainer.innerHTML = '';
        const list = quickBadges[type] || [];
        if (list.length > 0 && mode === 'tambah') {
            let badgeHTML = '<span style="font-size: 11px; color: #64748b; width: 100%; margin-bottom: 2px; display: block;">Pilihan Cepat (Rekomendasi):</span>';
            list.forEach(val => {
                badgeHTML += `<span class="quick-badge" onclick="document.getElementById('orgName').value='${val}'" style="background: #e2e8f0; color: #334155; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-block; margin-right: 4px; margin-bottom: 4px;">${val}</span>`;
            });
            badgeContainer.innerHTML = badgeHTML;
            
            if (!document.getElementById('badge-hover-style')) {
                const style = document.createElement('style');
                style.id = 'badge-hover-style';
                style.innerHTML = '.quick-badge:hover { background: var(--primary-color) !important; color: white !important; }';
                document.head.appendChild(style);
            }
        }
    }

    if(mode==='edit'){
        let d={nama:'',level:''};
        if(type==='divisi'){const x=orgData.find(v=>v.id==id);if(x)d.nama=x.nama;}
        else if(type==='department'){orgData.forEach(v=>{const x=(v.departments||[]).find(dd=>dd.id==id);if(x)d.nama=x.nama;});}
        else{orgData.forEach(v=>{(v.departments||[]).forEach(dd=>{const x=(dd.positions||[]).find(p=>p.id==id);if(x)d={nama:x.nama,employeeName:x.employee_name||'',level:x.level||''};});});}
        document.getElementById('orgName').value=d.nama||'';
        if(document.getElementById('posEmployeeName'))document.getElementById('posEmployeeName').value=d.employeeName||'';
        if(document.getElementById('posLevel'))document.getElementById('posLevel').value=d.level||'';
    } else {document.getElementById('orgName').value='';if(document.getElementById('posEmployeeName'))document.getElementById('posEmployeeName').value='';if(document.getElementById('posLevel'))document.getElementById('posLevel').value='';}
    setTimeout(()=>document.getElementById('orgName').focus(),100);
}

function tutupModalOrg(){
    document.getElementById('modalOrg').style.display='none';
    if(document.getElementById('modalKaryawan').style.display!=='block'){
        document.getElementById('overlay').style.display='none';
    }
}

document.getElementById('formOrg')?.addEventListener('submit',async(e)=>{
    e.preventDefault();
    const type=document.getElementById('orgType').value,id=document.getElementById('orgId').value,parentId=document.getElementById('orgParentId').value,name=document.getElementById('orgName').value.trim();
    const empName=document.getElementById('posEmployeeName')?.value.trim()||'';
    const nik=document.getElementById('posNik')?.value.trim()||'';
    const email=document.getElementById('posEmail')?.value.trim()||'';
    const phone=document.getElementById('posPhone')?.value.trim()||'';
    
    // Retrieve target client ID (from global context or active employee form client selection)
    const targetClientId = selectedClientId || document.getElementById('empClientId')?.value || null;
    
    if(!name){showToast('Nama harus diisi!','error');return;}
    try{
        let r;
        if(id&&id!==''&&id!=='null'){
            r=await fetch(`${API}/org/${type}/${id}`,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({nama:name,employee_name:empName,email,phone,level:document.getElementById('posLevel')?.value||''})});
        }else{
            let ep='',body={nama:name};
            if(type==='divisi'){ep='/divisions';body.client_id=targetClientId;}
            else if(type==='department'){ep='/departments';body.division_id=parentId;}
            else{ep='/positions';body.department_id=parentId;body.client_id=targetClientId;body.employee_name=empName;body.nik=nik;body.email=email;body.phone=phone;body.level=document.getElementById('posLevel')?.value||''};
            r=await fetch(`${API}${ep}`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
        }
        if(r.ok){
            const newObj = await r.json();
            const newId = newObj.id;
            
            tutupModalOrg();
            if(selectedClientId){
                renderClientOrg(selectedClientId);
                renderTableKaryawanClient();
            }
            
            // Auto select in employee modal if open
            const modalKaryawan = document.getElementById('modalKaryawan');
            if (modalKaryawan && modalKaryawan.style.display === 'block') {
                const divSelect = document.getElementById('empDivisionId');
                const deptSelect = document.getElementById('empDepartmentId');
                
                if (type === 'divisi') {
                    await loadOrgSelects(targetClientId, newId);
                } else if (type === 'department') {
                    const currentDivId = divSelect.value;
                    await loadOrgSelects(targetClientId, currentDivId, newId);
                } else if (type === 'posisi') {
                    const currentDivId = divSelect.value;
                    const currentDeptId = deptSelect.value;
                    await loadOrgSelects(targetClientId, currentDivId, currentDeptId, newId);
                }
            }
            
            showToast('Data berhasil disimpan!');
        }
        else{const err=await r.json();showToast('Gagal: '+(err.error||'Server error'),'error');}
    }catch(e){console.error(e);showToast('Kesalahan koneksi.','error');}
});

async function hapusOrg(type,id){
    if(!await showConfirm(`Yakin ingin menghapus ${type} ini?`))return;
    const r=await fetch(`${API}/org/${type}/${id}`,{method:'DELETE'});
    if(r.ok){if(selectedClientId)renderClientOrg(selectedClientId);showToast('Berhasil dihapus');}
}

// === EMPLOYEE ===
async function renderTableKaryawanClient(){
    if (typeof renderAllEmployees === 'function') {
        renderAllEmployees();
    }
}

let clientOrgHierarchy = [];

async function loadOrgSelects(clientId, selectedDivId = null, selectedDeptId = null, selectedPosId = null) {
    const divSelect = document.getElementById('empDivisionId');
    const deptSelect = document.getElementById('empDepartmentId');
    const posSelect = document.getElementById('empPositionId');
    
    if (!divSelect || !deptSelect || !posSelect) return;
    
    divSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
    deptSelect.innerHTML = '<option value="">-- Pilih Department --</option>';
    posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
    
    try {
        const r = await fetch(`${API}/org?client_id=${clientId}`);
        clientOrgHierarchy = await r.json();
        
        if (Array.isArray(clientOrgHierarchy)) {
            clientOrgHierarchy.forEach(div => {
                divSelect.innerHTML += `<option value="${div.id}">${div.nama}</option>`;
            });
        }
        
        // Setup cascading triggers
        divSelect.onchange = () => {
            const divId = divSelect.value;
            deptSelect.innerHTML = '<option value="">-- Pilih Department --</option>';
            posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
            if (divId) {
                const division = clientOrgHierarchy.find(d => d.id == divId);
                if (division && Array.isArray(division.departments)) {
                    division.departments.forEach(dept => {
                        deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
                    });
                }
            }
        };
        
        deptSelect.onchange = () => {
            const divId = divSelect.value;
            const deptId = deptSelect.value;
            posSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
            if (divId && deptId) {
                const division = clientOrgHierarchy.find(d => d.id == divId);
                if (division && Array.isArray(division.departments)) {
                    const dept = division.departments.find(dp => dp.id == deptId);
                    if (dept && Array.isArray(dept.positions)) {
                        dept.positions.forEach(pos => {
                            posSelect.innerHTML += `<option value="${pos.id}">${pos.nama}</option>`;
                        });
                    }
                }
            }
        };
        
        if (selectedDivId) {
            divSelect.value = selectedDivId;
            divSelect.onchange();
            
            if (selectedDeptId) {
                deptSelect.value = selectedDeptId;
                deptSelect.onchange();
                
                if (selectedPosId) {
                    posSelect.value = selectedPosId;
                }
            }
        }
    } catch (e) {
        console.error('Error loading org selects:', e);
    }
}

async function loadWorkLocationsForSelect(clientId, activeLocationId = null) {
    const locSel = document.getElementById('empWorkLocationId');
    if (!locSel) return;
    locSel.innerHTML = '<option value="">-- Memuat Lokasi... --</option>';
    try {
        const r = await fetch(`${API}/work-locations?client_id=${clientId}`);
        const locations = await r.json();
        locSel.innerHTML = '<option value="">-- Pilih Lokasi Kerja --</option>';
        if (Array.isArray(locations)) {
            locations.forEach(loc => {
                const opt = document.createElement('option');
                opt.value = loc.id;
                opt.innerText = `${loc.lokasi_kerja} (${loc.location_code || ''})`;
                if (activeLocationId && activeLocationId == loc.id) {
                    opt.selected = true;
                }
                locSel.appendChild(opt);
            });
        }
    } catch (e) {
        console.error('Error loading work locations:', e);
        locSel.innerHTML = '<option value="">-- Gagal memuat lokasi --</option>';
    }
}

async function bukaModalKaryawan(mode,id=null){
    const m=document.getElementById('modalKaryawan'),cs=document.getElementById('empClientId');
    m.style.display='block';document.getElementById('overlay').style.display='block';
    
    // Reset form first so it doesn't overwrite values set below!
    document.getElementById('formKaryawan').reset();
    document.getElementById('employeeId').value='';
    document.getElementById('empWorkLocationId').innerHTML = '<option value="">-- Pilih Lokasi Kerja --</option>';
    document.getElementById('empEmployId').value = '';
    
    const empEmployIdContainer = document.getElementById('empEmployIdContainer');
    const empNikNamaGrid = document.getElementById('empNikNamaGrid');
    if (mode === 'edit') {
        if (empEmployIdContainer) empEmployIdContainer.style.display = 'block';
        if (empNikNamaGrid) empNikNamaGrid.style.gridTemplateColumns = '1fr 2fr';
    } else {
        if (empEmployIdContainer) empEmployIdContainer.style.display = 'none';
        if (empNikNamaGrid) empNikNamaGrid.style.gridTemplateColumns = '1fr';
    }
    
    const statusPernikahanSel = document.getElementById('empStatusPernikahan');

    const jumlahAnakContainer = document.getElementById('empJumlahAnakContainer');
    const jumlahAnakInput = document.getElementById('empJumlahAnak');
    
    if (jumlahAnakContainer) jumlahAnakContainer.style.display = 'none';
    if (jumlahAnakInput) jumlahAnakInput.value = '0';

    const updateJumlahAnakVisibility = () => {
        if (!statusPernikahanSel || !jumlahAnakContainer) return;
        const val = statusPernikahanSel.value;
        if (val === 'Sudah' || val === 'Cerai') {
            jumlahAnakContainer.style.display = 'block';
        } else {
            jumlahAnakContainer.style.display = 'none';
            if (jumlahAnakInput) jumlahAnakInput.value = '0';
        }
    };

    if (statusPernikahanSel) {
        statusPernikahanSel.onchange = updateJumlahAnakVisibility;
    }
    
    cs.innerHTML='<option value="">-- Memuat Klien... --</option>';
    try {
        const r = await fetch(API + '/clients');
        const clientsData = await r.json();
        cs.innerHTML = '<option value="">-- Pilih Klien --</option>';
        clientsData.forEach(c => {
            cs.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
        
        // Perbarui clients global jika diperlukan
        if (typeof window.clients !== 'undefined') window.clients = clientsData;
    } catch (err) {
        console.error('Error fetching clients:', err);
        cs.innerHTML = '<option value="">-- Gagal memuat --</option>';
    }

    cs.onchange = async () => {
        const cid = cs.value;
        if (cid) {
            await loadWorkLocationsForSelect(cid);
        } else {
            document.getElementById('empWorkLocationId').innerHTML = '<option value="">-- Pilih Lokasi Kerja --</option>';
        }
    };

    const clientContainer = document.getElementById('empClientIdContainer');
    if(selectedClientId){
        cs.value=selectedClientId;
        cs.disabled=true;
        if(clientContainer) clientContainer.style.display = 'block'; // Selalu tampilkan agar user tahu
        await loadWorkLocationsForSelect(selectedClientId);
    }else{
        cs.disabled=false;
        if(clientContainer) clientContainer.style.display = 'block';
    }
    
    if(mode==='edit'&&id){
        const emp=(window.employees || []).find(e=>e.id==id) || (window.allEmployeesGlobal || []).find(e=>e.id==id);
        document.getElementById('employeeId').value=emp.id;
        document.getElementById('empEmployId').value = emp.employ_id || '';
        document.getElementById('empNama').value=emp.nama;
        document.getElementById('empClientId').value=emp.client_id;
        
        // Populating new fields
        document.getElementById('empTempatLahir').value = emp.tempat_lahir || '';
        document.getElementById('empTanggalLahir').value = emp.tanggal_lahir || '';
        document.getElementById('empNpwp').value = emp.npwp || '';
        if (document.getElementById('empStatusPernikahan')) {
            document.getElementById('empStatusPernikahan').value = emp.status_pernikahan || '';
        }

        if (jumlahAnakInput) jumlahAnakInput.value = emp.jumlah_anak || 0;
        if (typeof updateJumlahAnakVisibility === 'function') updateJumlahAnakVisibility();

        document.getElementById('empStartContract').value = emp.start_contract || '';
        document.getElementById('empEndContract').value = emp.end_contract || '';
        document.getElementById('empTipePerjanjian').value = emp.tipe_perjanjian || '';
        
        await loadWorkLocationsForSelect(emp.client_id, emp.work_location_id);
    }
    
    if (window.empClientSelectInstance) {
        window.empClientSelectInstance.destroy();
    }
    window.empClientSelectInstance = new TomSelect(cs, {
        create: false,
        sortField: { field: "text", direction: "asc" }
    });
}

async function loadPositions(cid){
    if(!cid)return;const ps=document.getElementById('empPositionId');
    if(!ps) return;
    ps.innerHTML='<option value="">-- Memuat Posisi... --</option>';
    try{const r=await fetch(`${API}/positions/client/${cid}`);const p=await r.json();ps.innerHTML='<option value="">-- Pilih Posisi --</option>';if(Array.isArray(p))p.forEach(x=>{ps.innerHTML+=`<option value="${x.id}">${x.nama}</option>`;});}catch(e){console.error(e);}
}

document.getElementById('formKaryawan')?.addEventListener('submit',async(e)=>{
    e.preventDefault();
    const id=document.getElementById('employeeId').value;
    const d={
        nama:document.getElementById('empNama').value,
        client_id:document.getElementById('empClientId').value,
        tempat_lahir:document.getElementById('empTempatLahir').value,
        tanggal_lahir:document.getElementById('empTanggalLahir').value,
        npwp:document.getElementById('empNpwp').value,
        status_pernikahan:document.getElementById('empStatusPernikahan') ? document.getElementById('empStatusPernikahan').value : '',

        jumlah_anak: parseInt(document.getElementById('empJumlahAnak')?.value || 0, 10),
        start_contract:document.getElementById('empStartContract').value,
        end_contract:document.getElementById('empEndContract').value,
        tipe_perjanjian:document.getElementById('empTipePerjanjian').value,
        work_location_id:document.getElementById('empWorkLocationId').value || null
    };
    // For new employees, generate a dummy nik from employ_id (nik is required by DB)
    if (!id) {
        d.nik = document.getElementById('empEmployId').value || ('EMP-' + Date.now());
    }
    const r=await fetch(id?`${API}/employees/${id}`:`${API}/employees`,{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});
    if(r.ok){
        tutupModalKaryawan();
        if(selectedClientId){
            renderTableKaryawanClient();
            renderClientOrg(selectedClientId);
        }
        if(typeof renderManajemenKaryawan === 'function'){
            renderManajemenKaryawan();
        }
        showToast('Data karyawan berhasil disimpan');
    } else {
        const err = await r.json().catch(() => ({}));
        let msg = 'Gagal menyimpan data karyawan';
        if (err.messages) msg = Object.values(err.messages).join(', ');
        else if (err.message) msg = err.message;
        showToast(msg, 'error');
    }
});

function tutupModalKaryawan(){
    document.getElementById('modalKaryawan').style.display='none';
    if(document.getElementById('modalOrg').style.display!=='block'){
        document.getElementById('overlay').style.display='none';
    }
}
async function hapusKaryawan(id){
    if(!await showConfirm('Yakin ingin menghapus karyawan ini?'))return;
    const r=await fetch(`${API}/employees/${id}`,{method:'DELETE'});
    if(r.ok){if(selectedClientId){renderTableKaryawanClient();renderClientOrg(selectedClientId);}showToast('Karyawan dihapus');}
}
function bukaModalKaryawanSpecific(){bukaModalKaryawan('tambah');const cs=document.getElementById('empClientId');if(cs){cs.value=selectedClientId;cs.closest('.form-group').style.display='none';}}

// Auto-fetch Employ ID when contract start date is set
document.getElementById('empStartContract')?.addEventListener('change', async (e) => {
    const dateVal = e.target.value;
    const employeeId = document.getElementById('employeeId').value;
    // Only auto-generate for new employees (not edit)
    if (employeeId) return;
    if (!dateVal) {
        document.getElementById('empEmployId').value = '';
        return;
    }
    const year = dateVal.substring(0, 4);
    try {
        const r = await fetch(`${API}/employees/next-employ-id?year=${year}`);
        const data = await r.json();
        if (data && data.employ_id) {
            document.getElementById('empEmployId').value = data.employ_id;
        }
    } catch (err) {
        console.error('Error fetching next employ ID:', err);
    }
});

window.renderClientOrg=renderClientOrg;window.bukaModalOrg=bukaModalOrg;window.tutupModalOrg=tutupModalOrg;window.hapusOrg=hapusOrg;window.toggleNode=toggleNode;window.renderTableKaryawanClient=renderTableKaryawanClient;window.bukaModalKaryawan=bukaModalKaryawan;window.bukaModalKaryawanSpecific=bukaModalKaryawanSpecific;window.tutupModalKaryawan=tutupModalKaryawan;window.hapusKaryawan=hapusKaryawan;window.loadPositions=loadPositions;
window.tambahDeptInline=tambahDeptInline;window.tambahPosisiInline=tambahPosisiInline;
