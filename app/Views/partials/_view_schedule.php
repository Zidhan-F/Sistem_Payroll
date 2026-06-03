<!-- Section: Master Payroll Schedule -->
<div id="viewSchedule" class="view-section">
    <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
        <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
            <div>
                <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Payroll Schedule</h3>
                <p style="color: #64748b; font-size: 13px; margin: 0;">Configure global templates for pay dates and cut-off ranges.</p>
            </div>
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <button class="btn-add" onclick="bukaModalUploadAbsensi()" style="background: #27ae60; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                    <img src="https://cdn-icons-png.flaticon.com/512/337/337947.png" style="width: 16px; height: 16px; object-fit: contain; filter: brightness(0) invert(1);" alt="Upload Icon"> Upload Attendance Excel
                </button>
                <button class="btn-add" onclick="bukaModalSchedule('tambah')" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                    <img src="https://cdn-icons-png.flaticon.com/512/992/992651.png" style="width: 16px; height: 16px; object-fit: contain; filter: brightness(0) invert(1);" alt="Add Icon"> Add Schedule
                </button>
            </div>
        </div>

        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
            <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Schedule Name</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Pay Day (Date)</th>
                        <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Cut-off Period</th>
                        <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Description</th>
                        <th style="width: 150px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="masterSchedulesContainer">
                    <!-- Rows will be dynamically rendered here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
