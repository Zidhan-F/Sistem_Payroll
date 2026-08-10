<!-- Modal Detail Artikel & Dokumentasi Pajak -->
<div id="modalTaxDetail" class="modal-skema" style="display: none; width: 850px; max-width: 95vw; max-height: 90vh; z-index: 2050; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); flex-direction: column; background: white;">
    <!-- Modal Header (Orange Theme) -->
    <div style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); padding: 20px 24px; color: white; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.22); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                <i class="fas fa-book-open" id="taxModalIcon"></i>
            </div>
            <div>
                <span id="taxModalBadge" style="background: rgba(255,255,255,0.28); color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Edukasi Pajak</span>
                <h3 id="taxModalTitle" style="margin: 4px 0 0 0; font-size: 18px; font-weight: 800; color: white; line-height: 1.3;">Judul Artikel Pajak</h3>
            </div>
        </div>
        <button type="button" onclick="closeTaxDetailModal()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            <i class="fas fa-times" style="font-size: 16px;"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <div style="padding: 24px; overflow-y: auto; flex: 1; color: #334155; font-size: 14px; line-height: 1.6;" id="taxModalBodyContent">
        <!-- Content dynamically injected by app-tax-edu.js -->
    </div>

    <!-- Modal Footer -->
    <div style="padding: 16px 24px; background: #fffbe6; border-top: 1px solid #fed7aa; display: flex; align-items: center; justify-content: space-between;">
        <div style="font-size: 12px; color: #78350f; display: flex; align-items: center; gap: 6px;">
            <i class="fas fa-shield-alt" style="color: #f39c12;"></i> Dokumen Resmi Kebijakan & Edukasi Perpajakan Internal
        </div>
        <button type="button" onclick="closeTaxDetailModal()" style="padding: 9px 22px; border-radius: 8px; font-weight: 600; cursor: pointer; background: #f39c12; color: white; border: none; font-size: 13px; transition: background 0.2s;" onmouseover="this.style.background='#e67e22'" onmouseout="this.style.background='#f39c12'">
            Tutup
        </button>
    </div>
</div>
