<!-- Section: Schedule Hub (Unified Master Schedule, Holiday Calendar, Attendance, Overtime) -->
<div id="viewSchedule" class="view-section">
    <!-- Sub Tabs for Schedule Hub -->
    <div class="sub-tabs-container" style="display: flex; gap: 8px; border-bottom: 2px solid #f1f5f9; margin-bottom: 20px; padding-bottom: 2px;">
        <button class="sub-tab-btn" id="subTabScheduleMaster" onclick="switchScheduleSubTab('master')" style="display: none;">Master Schedule</button>
        <button class="sub-tab-btn active" id="subTabScheduleHoliday" onclick="switchScheduleSubTab('holiday')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: var(--primary-color); cursor: pointer; border-bottom: 2px solid var(--primary-color); margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Holiday Calendar</button>
        <?php if (($_COOKIE['user_role'] ?? '') === 'admin'): ?>
        <button class="sub-tab-btn" id="subTabScheduleAttendance" onclick="switchScheduleSubTab('attendance')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Attendance</button>
        <button class="sub-tab-btn" id="subTabScheduleOvertime" onclick="switchScheduleSubTab('overtime')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Overtime</button>
        <button class="sub-tab-btn" id="subTabScheduleEarlyArrival" onclick="switchScheduleSubTab('earlyArrival')" style="padding: 8px 16px; border: none; background: none; font-weight: 600; font-size: 13px; color: #64748b; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s ease; outline: none;">Early Arrival</button>
        <?php endif; ?>
    </div>

    <!-- Panel 1: Master Schedule -->
    <div id="panelScheduleMaster" class="schedule-subpanel" style="display: none;">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 id="scheduleTableTitle" style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Master Payroll Schedule</h3>
                    <p style="color: #64748b; font-size: 13px; margin: 0;">Configure global templates for pay dates and cut-off ranges.</p>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 600; color: #64748b;">Year:</span>
                        <select id="scheduleYearSelect" onchange="pilihTahunSchedule(this.value)" style="padding: 10px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 14px; color: #334155; background: white; font-weight: 600; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); min-width: 130px;">
                            <!-- Dynamically rendered or static years -->
                        </select>
                    </div>
                    <button onclick="tambahPeriodeTahunan()" style="background: #eff6ff; border: 1px dashed #3b82f6; color: #1d4ed8; border-radius: 8px; padding: 10px 16px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                        <i class="fas fa-plus"></i> Add Year
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
                            <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Title</th>
                            <th style="text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Date</th>
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

    <!-- Panel 2: Holiday Calendar -->
    <div id="panelScheduleHoliday" class="schedule-subpanel">
        <div class="content-card" style="box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; border-radius: 12px; padding: 25px; background: white;">
            <div class="section-header" style="margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px;">
                <div>
                    <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 4px 0;">Holiday Calendar</h3>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 4px;">
                        <p style="color: #64748b; font-size: 13px; margin: 0;">Manage national holidays and special holiday dates.</p>
                        <span style="font-size: 12px; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="far fa-calendar-alt"></i> Today: <span id="holidayTodayDateLabel">-</span>
                        </span>
                    </div>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <!-- Toggle View Buttons -->
                    <div style="display: inline-flex; background: #f1f5f9; border-radius: 8px; padding: 4px; border: 1px solid #e2e8f0; margin-right: 8px;">
                        <button type="button" id="btnHolidayViewCalendar" onclick="switchHolidayView('calendar')" style="padding: 6px 14px; border-radius: 6px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: var(--primary-color); color: white; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-calendar-alt"></i> Calendar
                        </button>
                        <button type="button" id="btnHolidayViewList" onclick="switchHolidayView('list')" style="padding: 6px 14px; border-radius: 6px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: transparent; color: #64748b; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-list"></i> Table List
                        </button>
                    </div>
                    <button id="btnSyncGoogleCalendar" onclick="syncGoogleCalendar()" style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; padding: 10px 20px; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background='#e0f2fe'">
                        <i class="fas fa-sync-alt"></i> Sync Google Calendar
                    </button>
                    <button class="btn-add" onclick="bukaModalHoliday()" style="background: var(--primary-color); display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-plus"></i> Add Holiday
                    </button>
                </div>
            </div>

            <!-- Calendar View Container -->
            <div id="holidayCalendarContainer" style="display: block;">
                <!-- Monthly Navigator -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="button" onclick="navigateHolidayMonth(-1)" style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 14px; cursor: pointer; color: #475569; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h4 id="holidayCurrentMonthYear" style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; min-width: 160px; text-align: center;">Juni 2026</h4>
                        <button type="button" onclick="navigateHolidayMonth(1)" style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 14px; cursor: pointer; color: #475569; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button type="button" onclick="goToTodayHoliday()" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 8px 14px; cursor: pointer; color: #1e40af; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                            <i class="far fa-calendar-check"></i> Today
                        </button>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <select id="holidayMonthSelect" onchange="onHolidayMonthYearChange()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; font-weight: 600; color: #334155; cursor: pointer; background: white;">
                            <option value="0">January</option>
                            <option value="1">February</option>
                            <option value="2">March</option>
                            <option value="3">April</option>
                            <option value="4">May</option>
                            <option value="5">June</option>
                            <option value="6">July</option>
                            <option value="7">August</option>
                            <option value="8">September</option>
                            <option value="9">October</option>
                            <option value="10">November</option>
                            <option value="11">December</option>
                        </select>
                        
                        <select id="holidayYearSelect" onchange="onHolidayMonthYearChange()" style="padding: 8px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; font-weight: 600; color: #334155; cursor: pointer; background: white; min-width: 90px;">
                            <?php 
                            $currentYear = intval(date('Y'));
                            for ($y = $currentYear - 2; $y <= $currentYear + 3; $y++) {
                                $selected = ($y === $currentYear) ? 'selected' : '';
                                echo "<option value=\"$y\" $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Main Layout: Grid Calendar & Side Summary -->
                <div style="display: grid; grid-template-columns: 1fr 280px; gap: 20px; align-items: start;">
                    <!-- Calendar Grid Wrapper -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.02); min-width: 0;">
                        <!-- Days of Week Headers (Sunday is first column) -->
                        <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; text-align: center;">
                            <div style="padding: 14px 8px; font-weight: 700; color: #ef4444; font-size: 13px; text-transform: uppercase;">Sun</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Mon</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Tue</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Wed</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Thu</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Fri</div>
                            <div style="padding: 14px 8px; font-weight: 600; color: #64748b; font-size: 13px; text-transform: uppercase;">Sat</div>
                        </div>
                        
                        <!-- Days Grid -->
                        <div id="holidayCalendarGrid" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); background: #e2e8f0; gap: 1px;">
                            <!-- Filled dynamically by JS -->
                        </div>
                    </div>

                    <!-- Right Side: Monthly Holiday Summary -->
                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; background: #fafafa; min-height: 250px; border-left: 4px solid var(--primary-color); min-width: 0;">
                        <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle" style="color: var(--primary-color);"></i> Holiday List
                        </h4>
                        <div id="holidaySideSummary" style="display: flex; flex-direction: column; gap: 10px;">
                            <!-- Filled dynamically by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View Container (Hidden by default) -->
            <div id="holidayListContainer" style="display: none;">
                <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: white;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="width: 60px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">No</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Date</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Day</th>
                                <th style="text-align: left; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Description</th>
                                <th style="width: 120px; text-align: center; padding: 16px; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 14px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="holidayTableBody">
                            <!-- Dynamic rows -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (($_COOKIE['user_role'] ?? '') === 'admin'): ?>
    <!-- Panel 3: Attendance -->
    <div id="panelScheduleAttendance" class="schedule-subpanel" style="display: none;">
        <?= view('partials/_view_attendance') ?>
    </div>

    <!-- Panel 4: Overtime -->
    <div id="panelScheduleOvertime" class="schedule-subpanel" style="display: none;">
        <?= view('partials/_view_overtime') ?>
    </div>

    <!-- Panel 5: Early Arrival -->
    <div id="panelScheduleEarlyArrival" class="schedule-subpanel" style="display: none;">
        <?= view('partials/_view_early_arrival_panel') ?>
    </div>
    <?php endif; ?>

</div>
