<!-- Section: Log Aktivitas -->
<div id="viewLogAktivitas" class="view-section">
    <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
        <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
            <div>
                <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Activity Log</h3>
                <p style="color: #64748b; font-size: 13px; margin: 0;">Record of system action history and data manipulation.</p>
            </div>
        </div>

        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
            <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Activity / Detail</th>
                        <th style="width: 150px; text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">User</th>
                        <th style="width: 200px; text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Time</th>
                    </tr>
                </thead>
                <tbody id="logAktivitasTableBody">
                    <!-- Data injected by app.js / app-log.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>
