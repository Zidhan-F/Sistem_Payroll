            <!-- Section: Skema Payroll (Master) -->
            <div id="viewPayroll" class="view-section">
                <!-- Custom Sub-Tabs for Skema Payroll & UMP/UMK -->
                <div class="payroll-main-tabs" style="display: none; gap: 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 25px; background: #f8fafc; padding: 10px 10px 0 10px; border-radius: 8px 8px 0 0;">
                    <button id="subTabSkema" class="payroll-subtab-btn active" onclick="switchPayrollSubTab('skema')" style="background: white; border: 1px solid #e2e8f0; border-bottom: 1px solid white; padding: 12px 24px; font-weight: 600; font-size: 14px; cursor: pointer; color: #0d6efd; margin-bottom: -1px; border-radius: 6px 6px 0 0; z-index: 2; display: flex; align-items: center; gap: 8px; outline: none;">
                        <i class="fas fa-file-invoice-dollar"></i> Payroll Scheme
                    </button>
                    <button id="subTabUmr" class="payroll-subtab-btn" onclick="switchPayrollSubTab('umr')" style="background: transparent; border: 1px solid transparent; border-bottom: none; padding: 12px 24px; font-weight: 600; font-size: 14px; cursor: pointer; color: #475569; margin-bottom: -1px; border-radius: 6px 6px 0 0; z-index: 1; display: flex; align-items: center; gap: 8px; outline: none;">
                        <i class="fas fa-file-upload"></i> UMP/UMK
                    </button>
                </div>

                <!-- Sub-tab 1: Skema Payroll Container -->
                <div id="payrollSkemaContainer">
                    <div class="section-header" style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; color: var(--secondary-color);">Master Payroll Scheme</h3>
                        <button class="btn-add" onclick="bukaModalSkema('tambah')">
                            <i class="fas fa-plus"></i> Add Scheme
                        </button>
                    </div>
                    <div id="payrollSchemesContainer" class="schemes-grid">
                        <!-- Scheme cards will be rendered by app.js -->
                    </div>
                </div>

                <!-- Sub-tab 2: UMP/UMK Container -->
                <div id="payrollUmrContainer" style="display: none;">
                    <div class="content-card" style="padding: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                        <!-- Custom Sub-Tabs for UMP & UMK -->
                        <div class="umr-tabs" style="display: flex; gap: 0; border-bottom: 1px solid #ddd; padding: 15px 20px 0 20px; background: #fafafa;">
                            <button id="tabUmp" class="umr-tab-btn active" onclick="switchUmrTab('UMP')" style="background: white; border: 1px solid #ddd; border-bottom: 1px solid white; padding: 12px 25px; font-weight: 500; font-size: 14px; cursor: pointer; color: #0d6efd; margin-bottom: -1px; border-radius: 4px 4px 0 0; z-index: 2;">UMP</button>
                            <button id="tabUmk" class="umr-tab-btn" onclick="switchUmrTab('UMK')" style="background: transparent; border: 1px solid transparent; border-bottom: none; padding: 12px 25px; font-weight: 500; font-size: 14px; cursor: pointer; color: #0d6efd; margin-bottom: -1px; border-radius: 4px 4px 0 0; z-index: 1;">UMK</button>
                            <button id="tabNominal" class="umr-tab-btn" onclick="switchUmrTab('NOMINAL')" style="display: none;">Nominal</button>
                        </div>

                        <!-- Main Container below tabs -->
                        <div id="umrTableArea" style="padding: 20px;">
                            <!-- Action Buttons and Select filters -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                                
                                <!-- Left Side: Search and Download -->
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <!-- State Tracker for UMR type -->
                                    <input type="hidden" id="selectUmrType" value="UMP">
                                    
                                    <!-- Search Input Bar -->
                                    <div class="search-box" style="width: 240px; margin-bottom: 0;">
                                        <i class="fas fa-search"></i>
                                        <input type="text" id="searchUmr" placeholder="Search Province..." onkeyup="filterUmrTable()">
                                    </div>

                                    <!-- Blue Download Template Button -->
                                    <button class="btn-add" onclick="downloadTemplateUmr()" style="background: #0d6efd; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 500; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </div>

                                <!-- Right Side: Upload Button -->
                                <div>
                                    <button class="btn-add" onclick="bukaModalUploadUmr()" style="background: #ffc107; color: #000; border: none; padding: 10px 20px; border-radius: 4px; font-weight: 500; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </div>
                            </div>

                            <!-- UMR Table -->
                            <div class="table-container" style="overflow-x: auto; background: white; border: 1px solid #ddd;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th id="colUmrCode" style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Province Code</th>
                                            <th id="colUmrName" style="padding: 12px 15px; border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Province</th>
                                            <th style="padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; font-weight: 500; color: white; background: #0d6efd; font-size: 14px;">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelUmrBody">
                                        <!-- Data will be loaded via AJAX from API -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination and Entry summary Footer -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px; flex-wrap: wrap; gap: 15px;">
                                <span id="umrPaginationInfo" style="font-size: 14px; color: var(--text-muted); font-weight: 500;">
                                    Showing 0 - 0 of 0 entries
                                </span>
                                <div id="umrPaginationControls" class="pagination" style="display: flex; gap: 5px; list-style: none;">
                                    <!-- Pagination buttons will be dynamically rendered here -->
                                </div>
                            </div>
                        </div> <!-- End of umrTableArea -->

                        <!-- Nominal Input Area (Hidden by default) -->
                        <div id="umrNominalArea" style="display: none; padding: 40px 20px; text-align: center; background: white;">
                            <div style="background: rgba(40, 167, 69, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fas fa-money-bill-wave" style="font-size: 32px; color: #28a745;"></i>
                            </div>
                            <h3 style="margin-bottom: 10px; color: #2c3e50; font-size: 22px;">Input Agreed Salary (Nominal)</h3>
                            <p style="color: #6c757d; margin-bottom: 30px; font-size: 14px;">Enter the agreed salary amount for simulation purposes.</p>
                            
                            <div style="max-width: 400px; margin: 0 auto; text-align: left;">
                                <label style="font-weight: 600; margin-bottom: 8px; display: block; color: #4a5568;">Agreed Nominal (Rp)</label>
                                <input type="text" id="inputUmrNominal" placeholder="Example: 5000000" onkeyup="formatRupiahInput(this)" style="width: 100%; padding: 14px 16px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 16px; margin-bottom: 24px; transition: border-color 0.3s;" onfocus="this.style.borderColor='#0d6efd'" onblur="this.style.borderColor='#ddd'">
                                
                                <button class="btn-save" onclick="simpanNominalManual()" style="width: 100%; padding: 14px 15px; background: #0d6efd; color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: all 0.3s; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);">
                                    <i class="fas fa-save"></i> Save Nominal
                                </button>

                                <div id="displayNominalTersimpan" style="margin-top: 25px; padding: 15px; border-radius: 8px; background: #e8fdf0; border: 1px solid #d4edda; text-align: center; display: none;">
                                    <span style="font-size: 14px; color: #155724; font-weight: 500;">Currently Active Nominal:</span>
                                    <h4 id="valNominalTersimpan" style="font-size: 22px; color: #2ecc71; margin: 5px 0 0 0; font-weight: 700;">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
