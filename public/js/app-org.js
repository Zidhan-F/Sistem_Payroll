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
    container.innerHTML='<div style="text-align: center; padding: 40px; color: #94a3b8; width: 100%;"><i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i>Loading data...</div>';
    try{
        const r=await fetch(`${API}/org?client_id=${clientId}`);
        orgData=await r.json();
        container.innerHTML='';
        if(!Array.isArray(orgData)||orgData.length===0){container.innerHTML+='<div class="payroll-empty-state"><i class="fas fa-sitemap"></i><h4>No Structure Yet</h4><p>Please click the <b>Add Division</b> button above to build a structure.</p></div>';return;}
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
                                <button class="btn-nested-add" style="background:#10b981;border:none;color:white;padding:4px 8px;border-radius:6px;font-size:10px;cursor:pointer;" onclick="bukaModalOrg('posisi','tambah',null,${dept.id})"><i class="fas fa-user-plus"></i> Position</button>
                                <button class="btn-icon btn-edit" onclick="bukaModalOrg('department','edit',${dept.id},${div.id})"><i class="fas fa-edit"></i></button>
                                <button class="btn-icon btn-delete" onclick="hapusOrg('department',${dept.id})"><i class="fas fa-trash"></i></button>
                            </div></div>
                        <div class="nested-container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;" data-display-type="grid">`;
                    if(dept.positions&&dept.positions.length>0){
                        dept.positions.forEach(pos=>{
                            h+=`<div style="display:flex;align-items:center;gap:12px;background:white;padding:12px;border-radius:10px;border:1px solid #eef2f7;box-shadow:0 2px 4px rgba(0,0,0,0.01);border-left:4px solid var(--primary-color);">
                                <div style="width:36px;height:36px;background:#fff9f0;color:var(--primary-color);border-radius:8px;display:grid;place-items:center;font-size:14px;"><i class="fas fa-briefcase"></i></div>
                                <div style="display:flex;flex-direction:column;flex-grow:1;"><span style="font-size:9px;font-weight:800;text-transform:uppercase;color:#64748b;">${pos.level || 'Position'}</span><span style="font-weight:700;color:#1e293b;font-size:13px;">${pos.nama}</span></div>
                                <div class="action-btns" style="gap:4px;"><button class="btn-icon btn-edit" style="padding:4px;font-size:10px;" onclick="bukaModalOrg('posisi','edit',${pos.id},${dept.id})"><i class="fas fa-edit"></i></button><button class="btn-icon btn-delete" style="padding:4px;font-size:10px;" onclick="hapusOrg('posisi',${pos.id})"><i class="fas fa-trash"></i></button></div></div>`;
                        });
                    } else h+='<div class="empty-state" style="font-size:11px;color:#94a3b8;grid-column:1/-1;">No positions yet.</div>';
                    h+=`</div></div>`;
                });
            } else h+='<div class="empty-state" style="font-size:12px;color:#94a3b8;text-align:center;padding:10px;">No departments yet.</div>';
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
        showToast('Please select a Division first!', 'error');
        return;
    }
    bukaModalOrg('department', 'tambah', null, divId);
}

function tambahPosisiInline() {
    const deptId = document.getElementById('empDepartmentId').value;
    if (!deptId) {
        showToast('Please select a Department first!', 'error');
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
    const lbl=type==='divisi'?'Division':type==='department'?'Department':'Position/Title';
    document.getElementById('modalOrgTitle').innerText=(mode==='edit'?'Edit ':'Add ')+lbl;
    document.getElementById('labelOrgName').innerText=lbl+' Name';
    document.getElementById('orgName').placeholder='Enter '+lbl.toLowerCase()+' name';
    if(document.getElementById('posEmployeeField')) {
        document.getElementById('posEmployeeField').style.display=type==='posisi'?'block':'none';
    }
    if(document.getElementById('posExtraFields')) {
        document.getElementById('posExtraFields').style.display=type==='posisi'?'block':'none';
    }
    
    // Render quick-select badges
    const badgeContainer = document.getElementById('quickBadgeContainer');
    if (badgeContainer) {
        badgeContainer.innerHTML = '';
        const list = quickBadges[type] || [];
        if (list.length > 0 && mode === 'tambah') {
            let badgeHTML = '<span style="font-size: 11px; color: #64748b; width: 100%; margin-bottom: 2px; display: block;">Quick Select (Recommended):</span>';
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
        if(typeof populateOrgNameSelect === 'function') {
            populateOrgNameSelect(type, d.nama);
        }
    } else {
        document.getElementById('orgName').value='';
        if(document.getElementById('posEmployeeName'))document.getElementById('posEmployeeName').value='';
        if(document.getElementById('posLevel'))document.getElementById('posLevel').value='';
        if(typeof populateOrgNameSelect === 'function') {
            populateOrgNameSelect(type, '');
        }
    }
    setTimeout(()=>{
        const sel = document.getElementById('orgNameSelect');
        if(sel && sel.tomselect) {
            sel.tomselect.focus();
        } else {
            document.getElementById('orgName').focus();
        }
    },100);
}

function tutupModalOrg(){
    document.getElementById('modalOrg').style.display='none';
    if(document.getElementById('modalKaryawan').style.display!=='block'){
        document.getElementById('overlay').style.display='none';
    }
    if (window.orgNameSelectInstance) {
        window.orgNameSelectInstance.destroy();
        window.orgNameSelectInstance = null;
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
    
    if(!name){showToast('Name is required!','error');return;}
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
            
            showToast('Data saved successfully!');
        }
        else{const err=await r.json();showToast('Failed: '+(err.error||'Server error'),'error');}
    }catch(e){console.error(e);showToast('Connection error.','error');}
});

async function hapusOrg(type,id){
    if(!await showConfirm(`Are you sure you want to delete this ${type}?`))return;
    const r=await fetch(`${API}/org/${type}/${id}`,{method:'DELETE'});
    if(r.ok){if(selectedClientId)renderClientOrg(selectedClientId);showToast('Deleted successfully');}
}

// === EMPLOYEE ===
async function renderTableKaryawanClient(){
    if (typeof renderAllEmployees === 'function') {
        renderAllEmployees();
    }
}

let clientOrgHierarchy = [];

window.updateEmpDeptDropdown = function(clientId, divId, selectedDeptId = '') {
    const deptSelect = document.getElementById('empDepartmentId');
    if (!deptSelect) return;
    
    if (deptSelect.tomselect) {
        deptSelect.tomselect.destroy();
    }
    
    deptSelect.innerHTML = '<option value="">-- Select Department --</option>';
    
    if (divId && Array.isArray(clientOrgHierarchy)) {
        const division = clientOrgHierarchy.find(d => d.id == divId);
        if (division && Array.isArray(division.departments)) {
            division.departments.forEach(dept => {
                deptSelect.innerHTML += `<option value="${dept.id}">${dept.nama}</option>`;
            });
        }
    }
    
    new TomSelect(deptSelect, { 
        create: false, 
        sortField: { field: 'text', direction: 'asc' } 
    });
    
    deptSelect.tomselect.on('change', (val) => {
        window.updateEmpPosDropdown(clientId, divId, val);
        if (typeof checkSchemaAvailability === 'function') checkSchemaAvailability();
    });

    if (selectedDeptId) {
        deptSelect.tomselect.setValue(selectedDeptId);
    } else {
        window.updateEmpPosDropdown(clientId, divId, '');
    }
};

window.updateEmpPosDropdown = function(clientId, divId, deptId, selectedPosId = '') {
    const posSelect = document.getElementById('empPositionId');
    if (!posSelect) return;
    
    if (posSelect.tomselect) {
        posSelect.tomselect.destroy();
    }
    
    posSelect.innerHTML = '<option value="">-- Select Position --</option>';
    
    if (divId && deptId && Array.isArray(clientOrgHierarchy)) {
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
    
    new TomSelect(posSelect, { 
        create: false, 
        sortField: { field: 'text', direction: 'asc' } 
    });
    
    posSelect.tomselect.on('change', () => {
        if (typeof checkSchemaAvailability === 'function') checkSchemaAvailability();
    });
    
    if (selectedPosId) {
        posSelect.tomselect.setValue(selectedPosId);
    }
};

async function loadOrgSelects(clientId, selectedDivId = null, selectedDeptId = null, selectedPosId = null) {
    const divSelect = document.getElementById('empDivisionId');
    const deptSelect = document.getElementById('empDepartmentId');
    const posSelect = document.getElementById('empPositionId');
    
    if (!divSelect || !deptSelect || !posSelect) return;
    
    // Destroy TomSelect if already initialized to modify innerHTML
    if (divSelect.tomselect) divSelect.tomselect.destroy();
    if (deptSelect.tomselect) deptSelect.tomselect.destroy();
    if (posSelect.tomselect) posSelect.tomselect.destroy();
    
    divSelect.innerHTML = '<option value="">-- Select Division --</option>';
    deptSelect.innerHTML = '<option value="">-- Select Department --</option>';
    posSelect.innerHTML = '<option value="">-- Select Position --</option>';
    
    try {
        const r = await fetch(`${API}/org?client_id=${clientId}`);
        clientOrgHierarchy = await r.json();
        
        if (Array.isArray(clientOrgHierarchy)) {
            clientOrgHierarchy.forEach(div => {
                divSelect.innerHTML += `<option value="${div.id}">${div.nama}</option>`;
            });
        }

        // Initialize TomSelects
        new TomSelect(divSelect, { create: false, sortField: { field: "text", direction: "asc" } });
        new TomSelect(deptSelect, { create: false, sortField: { field: "text", direction: "asc" } });
        new TomSelect(posSelect, { create: false, sortField: { field: "text", direction: "asc" } });
        
        // Listen to changes to trigger cascading & schema check
        divSelect.tomselect.on('change', (val) => {
            window.updateEmpDeptDropdown(clientId, val);
            if (typeof checkSchemaAvailability === 'function') checkSchemaAvailability();
        });

        deptSelect.tomselect.on('change', (val) => {
            window.updateEmpPosDropdown(clientId, divSelect.value, val);
            if (typeof checkSchemaAvailability === 'function') checkSchemaAvailability();
        });

        posSelect.tomselect.on('change', () => {
            if (typeof checkSchemaAvailability === 'function') checkSchemaAvailability();
        });

        // Set edit values sequentially if provided
        if (selectedDivId) {
            divSelect.tomselect.setValue(selectedDivId);
            window.updateEmpDeptDropdown(clientId, selectedDivId, selectedDeptId);
            if (selectedDeptId) {
                window.updateEmpPosDropdown(clientId, selectedDivId, selectedDeptId, selectedPosId);
            }
        }

    } catch (e) {
        console.error('Error loading org selects:', e);
    }
}

let workLocationsCache = [];
let minimumWagesCache = [];

async function handleWorkLocationChange() {
    const locSel = document.getElementById('empWorkLocationId');
    const minWageInput = document.getElementById('empMinimumWage');
    const minWageInfo = document.getElementById('empMinimumWageInfo');
    const minWageContainer = document.getElementById('empMinimumWageContainer');
    
    if (!locSel || !minWageInput || !minWageInfo || !minWageContainer) return;
    
    const locationId = locSel.value;
    if (!locationId) {
        minWageContainer.style.display = 'none';
        minWageInput.value = '';
        minWageInfo.innerHTML = '';
        return;
    }
    
    const loc = workLocationsCache.find(l => l.id == locationId);
    if (!loc) {
        minWageContainer.style.display = 'none';
        return;
    }
    
    if (minimumWagesCache.length === 0) {
        try {
            const res = await fetch(`${API}/minimum-wages?tipe=all`);
            minimumWagesCache = await res.json();
        } catch (err) {
            console.error('Error fetching minimum wages:', err);
        }
    }
    
    let match = null;
    let wageType = '';
    
    // 1. Search UMK matching kota_kabupaten
    if (loc.kota_kabupaten) {
        const searchName = loc.kota_kabupaten.trim().toLowerCase();
        match = minimumWagesCache.find(w => w.tipe === 'UMK' && w.nama_daerah.trim().toLowerCase() === searchName);
        if (match) wageType = 'UMK';
    }
    
    // 2. Fallback to UMP matching provinsi
    if (!match && loc.provinsi) {
        const searchName = loc.provinsi.trim().toLowerCase();
        match = minimumWagesCache.find(w => w.tipe === 'UMP' && w.nama_daerah.trim().toLowerCase() === searchName);
        if (match) wageType = 'UMP';
    }
    
    minWageContainer.style.display = 'block';
    if (match) {
        minWageInput.value = parseFloat(match.nominal).toLocaleString('id-ID');
        minWageInfo.innerHTML = `<i class="fas fa-check-circle"></i> Auto-filled using <b>${wageType}</b> for <b>${match.nama_daerah}</b> (Rp ${parseFloat(match.nominal).toLocaleString('id-ID')})`;
        minWageInfo.style.color = '#15803d';
    } else {
        minWageInput.value = '0';
        minWageInfo.innerHTML = `<i class="fas fa-exclamation-triangle"></i> No UMP/UMK configured for ${loc.kota_kabupaten || loc.provinsi || 'this region'}.`;
        minWageInfo.style.color = '#b91c1c';
    }
}

async function loadWorkLocationsForSelect(clientId, activeLocationId = null) {
    const locSel = document.getElementById('empWorkLocationId');
    if (!locSel) return;
    
    if (!locSel.dataset.listenerAttached) {
        locSel.addEventListener('change', handleWorkLocationChange);
        locSel.dataset.listenerAttached = 'true';
    }
    
    locSel.innerHTML = '<option value="">-- Loading Locations... --</option>';
    try {
        const r = await fetch(`${API}/work-locations?client_id=${clientId}`);
        const locations = await r.json();
        workLocationsCache = locations;
        
        locSel.innerHTML = '<option value="">-- Select Work Location --</option>';
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
        
        if (activeLocationId) {
            handleWorkLocationChange();
        } else {
            const minWageContainer = document.getElementById('empMinimumWageContainer');
            if (minWageContainer) minWageContainer.style.display = 'none';
        }
    } catch (e) {
        console.error('Error loading work locations:', e);
        locSel.innerHTML = '<option value="">-- Failed to load locations --</option>';
    }
}

async function bukaModalKaryawan(mode,id=null){
    const m=document.getElementById('modalKaryawan'),cs=document.getElementById('empClientId');
    m.style.display='block';document.getElementById('overlay').style.display='block';
    
    // Reset form first so it doesn't overwrite values set below!
    document.getElementById('formKaryawan').reset();
    document.getElementById('employeeId').value='';
    document.getElementById('empWorkLocationId').innerHTML = '<option value="">-- Select Work Location --</option>';
    document.getElementById('empEmployId').value = '';
    
    const hariKerjaInput = document.getElementById('empHariKerja');
    if (hariKerjaInput) {
        hariKerjaInput.style.pointerEvents = 'auto';
        hariKerjaInput.style.background = '';
    }
    
    const minWageContainer = document.getElementById('empMinimumWageContainer');
    if (minWageContainer) minWageContainer.style.display = 'none';
    
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
    
    cs.innerHTML='<option value="">-- Loading Clients... --</option>';
    try {
        const r = await fetch(API + '/clients');
        const clientsData = await r.json();
        cs.innerHTML = '<option value="">-- Select Client --</option>';
        clientsData.forEach(c => {
            cs.innerHTML += `<option value="${c.id}">${c.nama}</option>`;
        });
        
        // Perbarui clients global jika diperlukan
        if (typeof window.clients !== 'undefined') window.clients = clientsData;
    } catch (err) {
        console.error('Error fetching clients:', err);
        cs.innerHTML = '<option value="">-- Failed to load --</option>';
    }

    cs.onchange = async () => {
        const cid = cs.value;
        if (cid) {
            await loadWorkLocationsForSelect(cid);
            await loadOrgSelects(cid);
        } else {
            document.getElementById('empWorkLocationId').innerHTML = '<option value="">-- Select Work Location --</option>';
            const ds = document.getElementById('empDivisionId');
            const deps = document.getElementById('empDepartmentId');
            const ps = document.getElementById('empPositionId');
            if (ds && ds.tomselect) ds.tomselect.destroy();
            if (deps && deps.tomselect) deps.tomselect.destroy();
            if (ps && ps.tomselect) ps.tomselect.destroy();
            if(ds) ds.innerHTML = '<option value="">-- Select Division --</option>';
            if(deps) deps.innerHTML = '<option value="">-- Select Department --</option>';
            if(ps) ps.innerHTML = '<option value="">-- Select Position --</option>';
            if(ds) new TomSelect(ds, { create: false });
            if(deps) new TomSelect(deps, { create: false });
            if(ps) new TomSelect(ps, { create: false });
        }
    };

    const clientContainer = document.getElementById('empClientIdContainer');
    if(selectedClientId){
        cs.value=selectedClientId;
        cs.disabled=true;
        if(clientContainer) clientContainer.style.display = 'block'; // Selalu tampilkan agar user tahu
        await loadWorkLocationsForSelect(selectedClientId);
        if (mode !== 'edit') await loadOrgSelects(selectedClientId);
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
        document.getElementById('empHariKerja').value = emp.hari_kerja || '5';
        
        await loadWorkLocationsForSelect(emp.client_id, emp.work_location_id);
        await loadOrgSelects(emp.client_id, emp.division_id, emp.department_id, emp.position_id);
    }
    
    if (window.empClientSelectInstance) {
        window.empClientSelectInstance.destroy();
    }
    window.empClientSelectInstance = new TomSelect(cs, {
        create: false,
        sortField: { field: "text", direction: "asc" }
    });
    
    // Initial check schema
    if (mode === 'edit' && id) {
        setTimeout(checkSchemaAvailability, 500);
    } else {
        document.getElementById('schemaInfoContainer').style.display = 'none';
    }

    // Attach listeners for automated payroll preview
    const empPosEl = document.getElementById('empPositionId');
    const empLocEl = document.getElementById('empWorkLocationId');
    if (empPosEl) {
        empPosEl.addEventListener('change', checkSchemaAvailability);
    }
    if (empLocEl) {
        empLocEl.addEventListener('change', checkSchemaAvailability);
    }
}

async function loadPositions(cid){
    if(!cid)return;const ps=document.getElementById('empPositionId');
    if(!ps) return;
    ps.innerHTML='<option value="">-- Loading Positions... --</option>';
    try{const r=await fetch(`${API}/positions/client/${cid}`);const p=await r.json();ps.innerHTML='<option value="">-- Select Position --</option>';if(Array.isArray(p))p.forEach(x=>{ps.innerHTML+=`<option value="${x.id}">${x.nama}</option>`;});}catch(e){console.error(e);}
}

document.getElementById('formKaryawan')?.addEventListener('submit',async(e)=>{
    e.preventDefault();
    // JS-side validation (novalidate is set on form to avoid browser popup conflicts with TomSelect)
    const requiredChecks = [
        { id: 'empNama', label: 'Full Name' },
        { id: 'empTempatLahir', label: 'Place of Birth' },
        { id: 'empTanggalLahir', label: 'Date of Birth' },
        { id: 'empStartContract', label: 'Contract Start' },
        { id: 'empEndContract', label: 'Contract End' },
        { id: 'empTipePerjanjian', label: 'Agreement Type' },
        { id: 'empWorkLocationId', label: 'Work Location' },
    ];
    for (const chk of requiredChecks) {
        const el = document.getElementById(chk.id);
        if (!el || !el.value) {
            showToast(`Field "${chk.label}" wajib diisi!`, 'error');
            if (el) el.focus();
            return;
        }
    }
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
        hari_kerja: document.getElementById('empHariKerja').value,
        work_location_id:document.getElementById('empWorkLocationId').value || null,
        position_id:document.getElementById('empPositionId')?.value || null
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

// Kalkulasi denda absen - sekarang dihitung otomatis dari scheme payroll
// Tidak lagi bergantung pada input manual empGajiPokok
function calculateDendaAbsen() {
    const dendaInput = document.getElementById('empDendaAbsen');
    if (!dendaInput) return;
    // Denda absen dihitung otomatis dari scheme melalui checkSchemaAvailability
    // Jika scheme belum dimuat, tampilkan 'Auto' sebagai placeholder
    if (!dendaInput.value) {
        dendaInput.value = 'Auto (from Scheme)';
    }
}
window.calculateDendaAbsen = calculateDendaAbsen;

// Validasi Keselarasan Skema
async function checkSchemaAvailability() {
    const cid = document.getElementById('empClientId')?.value;
    const divId = document.getElementById('empDivisionId')?.value;
    const deptId = document.getElementById('empDepartmentId')?.value;
    const posId = document.getElementById('empPositionId')?.value;
    const workLocId = document.getElementById('empWorkLocationId')?.value;
    const infoContainer = document.getElementById('schemaInfoContainer');
    
    if (!infoContainer) return;
    
    if (!cid) {
        infoContainer.style.display = 'none';
        return;
    }
    
    infoContainer.style.display = 'block';
    infoContainer.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking scheme availability & automatic calculation...';
    infoContainer.style.color = '#64748b';
    infoContainer.style.background = '#f8fafc';
    
    try {
        let url = `${API}/preview-payroll?client_id=${cid}`;
        if (divId) url += `&division_id=${divId}`;
        if (deptId) url += `&department_id=${deptId}`;
        if (posId) url += `&position_id=${posId}`;
        if (workLocId) url += `&work_location_id=${workLocId}`;
        
        const res = await fetch(url);
        if (res.ok) {
            const data = await res.json();
            if (data.status === 'error') {
                infoContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <b>Scheme Not Configured!</b><br>This employee has no payroll calculation scheme. Please set it up in the Payroll Configuration menu.';
                infoContainer.style.color = '#b91c1c';
                infoContainer.style.background = '#fef2f2';
                infoContainer.style.border = '1px solid #fca5a5';
            } else {
                infoContainer.innerHTML = `<i class="fas fa-check-circle"></i> <b>Scheme Available!</b><br>System uses scheme level: <b>${data.level}</b>.<br><small>${data.description}</small>`;
                infoContainer.style.color = '#15803d';
                infoContainer.style.background = '#f0fdf4';
                infoContainer.style.border = '1px solid #bbf7d0';

                // Auto-fill form fields from scheme data (no more manual gaji_pokok)
                const hariKerjaInput = document.getElementById('empHariKerja');
                const dendaInput = document.getElementById('empDendaAbsen');

                if (hariKerjaInput) {
                     hariKerjaInput.value = data.hari_kerja || 5;
                     hariKerjaInput.style.pointerEvents = 'auto';
                     hariKerjaInput.style.background = '';
                }

                if (dendaInput && data.gaji_pokok > 0) {
                     // Hitung hourly rate dari total fixed salary / 173 jam
                     const hourlyRate = data.gaji_pokok / 173;
                     dendaInput.value = 'Rp ' + Math.round(hourlyRate).toLocaleString('id-ID') + ' /jam';
                } else if (dendaInput) {
                     dendaInput.value = 'Auto (from Scheme)';
                }
            }
        }
    } catch (e) {
        console.error('Error checking schema', e);
        infoContainer.innerHTML = '<i class="fas fa-info-circle"></i> Failed to verify server scheme.';
    }
}
window.checkSchemaAvailability = checkSchemaAvailability;

window.renderClientOrg=renderClientOrg;window.bukaModalOrg=bukaModalOrg;window.tutupModalOrg=tutupModalOrg;window.hapusOrg=hapusOrg;window.toggleNode=toggleNode;window.renderTableKaryawanClient=renderTableKaryawanClient;window.bukaModalKaryawan=bukaModalKaryawan;window.bukaModalKaryawanSpecific=bukaModalKaryawanSpecific;window.tutupModalKaryawan=tutupModalKaryawan;window.hapusKaryawan=hapusKaryawan;window.loadPositions=loadPositions;
window.tambahDeptInline=tambahDeptInline;window.tambahPosisiInline=tambahPosisiInline;

if (document.getElementById('orgNameSelect')) {
    document.getElementById('orgNameSelect').addEventListener('change', function() {
        document.getElementById('orgName').value = this.value;
    });
}
