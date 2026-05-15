// === AUTH ===
const currentUser = JSON.parse(localStorage.getItem('user'));
if (!currentUser) window.location.href = 'login';
const API = '/api';
let clients = [], selectedClientId = null, employees = [], orgData = [];

// === UTILITIES ===
function formatRupiah(n) {
    if (n == null) return '-';
    return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(n);
}
function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;top:20px;right:20px;padding:12px 24px;border-radius:8px;color:white;font-weight:600;z-index:10000;box-shadow:0 4px 12px rgba(0,0,0,0.15);background:${type==='success'?'#10b981':'#ef4444'};transform:translateY(-20px);opacity:0;transition:all 0.3s ease;`;
    t.innerText = msg; document.body.appendChild(t);
    setTimeout(()=>{t.style.transform='translateY(0)';t.style.opacity='1';},10);
    setTimeout(()=>{t.style.transform='translateY(-20px)';t.style.opacity='0';setTimeout(()=>t.remove(),300);},3000);
}
function showConfirm(message) {
    return new Promise(resolve => {
        const m = document.getElementById('modalConfirm'), o = document.getElementById('overlay');
        document.getElementById('confirmMessage').innerText = message;
        m.style.display='block'; o.style.display='block';
        document.getElementById('btnConfirmYes').onclick = ()=>{m.style.display='none';o.style.display='none';resolve(true);};
        document.getElementById('btnConfirmCancel').onclick = ()=>{m.style.display='none';o.style.display='none';resolve(false);};
    });
}

// === HEADER ===
if(currentUser && document.getElementById('headerUserName')) document.getElementById('headerUserName').innerText = currentUser.username;
function logout(){localStorage.removeItem('user');window.location.href='login';}

// === SIDEBAR TOGGLE ===
const sidebar = document.querySelector('.sidebar'), mainContent = document.querySelector('.main-content');
document.getElementById('toggleSidebar')?.addEventListener('click',()=>{sidebar.classList.toggle('collapsed');mainContent.classList.toggle('expanded');});

// === VIEW SWITCHING ===
function switchView(view) {
    document.querySelectorAll('.view-section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.sidebar-menu li').forEach(l=>l.classList.remove('active'));
    if(view==='dashboard'){
        document.getElementById('viewDashboard').classList.add('active');
        document.getElementById('menuDashboard').classList.add('active');
        document.getElementById('viewTitle').innerText='Dashboard';
        updateDashboardStats();
    } else {
        document.getElementById('viewKlien').classList.add('active');
        document.getElementById('menuKlien').classList.add('active');
        document.getElementById('viewTitle').innerText='Client';
        renderTable();
    }
}

// === DASHBOARD STATS ===
async function updateDashboardStats() {
    try {
        if(currentUser) document.getElementById('welcomeName').innerText = currentUser.username;
        const rc = await fetch(`${API}/clients`); const cd = await rc.json();
        document.getElementById('statTotalKlien').innerText = cd.length||0;
        const re = await fetch(`${API}/employees`); const ed = await re.json();
        document.getElementById('statTotalKaryawan').innerText = ed.length||0;
        const ro = await fetch(`${API}/org`); const od = await ro.json();
        document.getElementById('statTotalDivisi').innerText = od.length||0;
    } catch(e){console.error(e);}
}

// === CLIENT TABLE ===
const tabelBody = document.getElementById('tabelKlienBody');
const modal = document.getElementById('modalClient');
const overlay = document.getElementById('overlay');
const formKlien = document.getElementById('formKlien');

async function renderTable() {
    try {
        const r = await fetch(`${API}/clients`);
        clients = await r.json();
        if(!tabelBody) return;
        tabelBody.innerHTML = '';
        if(Array.isArray(clients)) clients.forEach(c => {
            tabelBody.innerHTML += `<tr onclick="selectClient(${c.id},'${(c.nama||'').replace(/'/g,"\\'")}')" style="cursor:pointer;">
                <td style="font-weight:700;color:#7f8c8d;">${c.no_klien||'-'}</td>
                <td style="font-weight:600;color:var(--primary-color);">${c.nama}</td>
                <td><div style="font-size:13px;font-weight:500;">${c.email||'-'}</div><div style="font-size:11px;color:#888;">${c.telepon||'-'}</div></td>
                <td>${c.sektor}</td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${c.alamat}</td>
                <td><span style="padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;background:${c.status==='Aktif'?'#e8fdf0':'#fde8e8'};color:${c.status==='Aktif'?'#2ecc71':'#e74c3c'};">${c.status||'Aktif'}</span></td>
                <td><div class="action-btns">
                    <button class="btn-icon btn-edit" onclick="event.stopPropagation();bukaModal('edit',${c.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn-icon btn-delete" onclick="event.stopPropagation();hapusKlien(${c.id})"><i class="fas fa-trash"></i></button>
                </div></td></tr>`;
        });
    } catch(e){console.error(e);}
}

function bukaModal(mode, id=null) {
    modal.style.display='block'; overlay.style.display='block';
    const btn = document.getElementById('btnSubmit');
    if(mode==='edit' && id){
        document.getElementById('modalTitle').innerText='Edit Data Client'; btn.innerText='Edit';
        const c = clients.find(x=>x.id==id);
        if(c){document.getElementById('clientId').value=c.id;document.getElementById('noKlien').value=c.no_klien||'';document.getElementById('namaKlien').value=c.nama||'';document.getElementById('emailKlien').value=c.email||'';document.getElementById('teleponKlien').value=c.telepon||'';document.getElementById('sektorKlien').value=c.sektor||'';document.getElementById('nib').value=c.nib||'';document.getElementById('npwp').value=c.npwp||'';document.getElementById('tanggalBergabung').value=c.tgl_gabung?c.tgl_gabung.split('T')[0]:'';document.getElementById('alamat').value=c.alamat||'';document.getElementById('statusKlien').value=c.status||'Aktif';}
    } else {
        document.getElementById('modalTitle').innerText='Tambah Data Client'; btn.innerText='Simpan';
        formKlien.reset();document.getElementById('clientId').value='';document.getElementById('noKlien').value='Otomatis';document.getElementById('statusKlien').value='Aktif';
    }
}
function tutupModal(){modal.style.display='none';overlay.style.display='none';}

formKlien?.addEventListener('submit', async(e)=>{
    e.preventDefault();
    const id = document.getElementById('clientId').value;
    const d = {nama:document.getElementById('namaKlien').value,email:document.getElementById('emailKlien').value,telepon:document.getElementById('teleponKlien').value,sektor:document.getElementById('sektorKlien').value,nib:document.getElementById('nib').value,npwp:document.getElementById('npwp').value,tgl_gabung:document.getElementById('tanggalBergabung').value,alamat:document.getElementById('alamat').value,status:document.getElementById('statusKlien').value};
    const r = await fetch(id?`${API}/clients/${id}`:`${API}/clients`,{method:id?'PUT':'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});
    if(r.ok){tutupModal();renderTable();showToast('Data berhasil disimpan!');}
});

async function hapusKlien(id){
    if(!await showConfirm('Yakin ingin menghapus klien ini?'))return;
    const r=await fetch(`${API}/clients/${id}`,{method:'DELETE'});
    if(r.ok){renderTable();showToast('Klien berhasil dihapus');}
}

// === CLIENT DETAIL ===
function selectClient(id, nama){
    selectedClientId=id;
    document.getElementById('tabelKlienContainer').style.display='none';
    document.getElementById('clientOrgDetail').style.display='block';
    document.getElementById('clientDetailTitle').innerText=`Struktur Klien: ${nama}`;
    switchClientTab('struktur');
}
function backToClientList(){
    selectedClientId=null;
    document.getElementById('tabelKlienContainer').style.display='block';
    document.getElementById('clientOrgDetail').style.display='none';
}
function switchClientTab(tab){
    document.querySelectorAll('.tab-item').forEach(t=>{t.classList.remove('active');});
    document.querySelectorAll('.client-tab-content').forEach(c=>{c.style.display='none';});
    if(tab==='struktur'){document.getElementById('tabStruktur').classList.add('active');document.getElementById('contentStruktur').style.display='block';renderClientOrg(selectedClientId);}
    else if(tab==='karyawan'){document.getElementById('tabKaryawan').classList.add('active');document.getElementById('contentKaryawan').style.display='block';renderTableKaryawanClient();loadClientSchema(selectedClientId);}
    else if(tab==='pkwt'){document.getElementById('tabPKWT').classList.add('active');document.getElementById('contentPKWT').style.display='block';renderPKWT();}
    else if(tab==='komponen'){document.getElementById('tabKomponen').classList.add('active');document.getElementById('contentKomponen').style.display='block';renderKomponen();}
    else if(tab==='payroll'){document.getElementById('tabPayroll').classList.add('active');document.getElementById('contentPayroll').style.display='block';filterPayrollByClient();}
}

// === SCHEMA ===
async function loadClientSchema(cid){
    try{const r=await fetch(`${API}/clients/schema/${cid}`);if(r.ok){const s=await r.json();document.getElementById('schemaBpjsKes').value=s.bpjs_kes_percent||0;document.getElementById('schemaBpjsJht').value=s.bpjs_jht_percent||0;document.getElementById('schemaTaxMethod').value=s.tax_method||'Gross';document.getElementById('schemaCutOffStart').value=s.cut_off_start||21;document.getElementById('schemaCutOffEnd').value=s.cut_off_end||20;}}catch(e){console.error(e);}
}
document.getElementById('formSchema')?.addEventListener('submit',async(e)=>{
    e.preventDefault();
    const d={client_id:selectedClientId,bpjs_kes_percent:document.getElementById('schemaBpjsKes').value,bpjs_jht_percent:document.getElementById('schemaBpjsJht').value,tax_method:document.getElementById('schemaTaxMethod').value,cut_off_start:document.getElementById('schemaCutOffStart').value,cut_off_end:document.getElementById('schemaCutOffEnd').value};
    const r=await fetch(`${API}/clients/schema`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)});
    if(r.ok) showToast('Skema Payroll berhasil disimpan!');
});

function tutupSemuaModal(){tutupModal();tutupModalOrg();tutupModalKaryawan();try{tutupModalPKWT();}catch(e){}try{tutupModalKomponen();}catch(e){}try{tutupModalSlip();}catch(e){}}

// Init
updateDashboardStats(); renderTable();

// Exports
window.formatRupiah=formatRupiah;window.showToast=showToast;window.showConfirm=showConfirm;window.logout=logout;window.switchView=switchView;window.renderTable=renderTable;window.bukaModal=bukaModal;window.tutupModal=tutupModal;window.hapusKlien=hapusKlien;window.selectClient=selectClient;window.backToClientList=backToClientList;window.switchClientTab=switchClientTab;window.loadClientSchema=loadClientSchema;window.tutupSemuaModal=tutupSemuaModal;
