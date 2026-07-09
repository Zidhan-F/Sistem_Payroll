            <!-- Section: Tax & BPJS Scheme Master -->
            <div id="viewPajak" class="view-section">
                <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
                    <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                        <div>
                            <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Tax PPh 21 Scheme Master</h3>
                            <p style="color: #64748b; font-size: 13px; margin: 0;">Manage PPh 21 tax schemes for payroll calculation.</p>
                        </div>
                    </div>

                    <!-- Tab Panel: PPh 21 Schemes -->
                    <div id="taxTabPph21" class="tax-tab-panel" style="display: block;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <input type="text" id="searchPph21Scheme" placeholder="Search PPh 21 scheme..." oninput="filterTaxScheme('pph21')" style="padding: 8px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-size: 14px; width: 250px;">
                            <button class="btn-add" onclick="bukaModalPph21('tambah')" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px;">
                                <i class="fas fa-plus"></i> Add PPh 21 Scheme
                            </button>
                        </div>
                        <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8fafc;">
                                        <th style="text-align: left; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Scheme Name</th>
                                        <th style="text-align: left; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Method</th>
                                        <th style="text-align: left; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">PTKP Status</th>
                                        <th style="text-align: left; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Description</th>
                                        <th style="width: 120px; text-align: center; padding: 14px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pph21SchemesTableBody">
                                    <!-- PPh 21 rows will be rendered by app-tax.js -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
