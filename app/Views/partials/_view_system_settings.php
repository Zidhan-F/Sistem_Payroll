<!-- Section: System Settings -->
<div id="viewSystemSettings" class="view-section">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; margin-bottom: 24px; flex-wrap: wrap;">
        <!-- Card: Form Configuration -->
        <div class="content-card" style="box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; background: white; backdrop-filter: blur(10px);">
            <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 24px;">
                <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-cogs" style="color: var(--primary-color);"></i> System Parameters
                </h3>
                <p style="color: #64748b; font-size: 13px; margin: 0;">Configure global company-wide calculation parameters and rules.</p>
            </div>

            <form onsubmit="saveSystemSettings(event)">
                <!-- Overtime Divisor -->
                <div style="margin-bottom: 20px;">
                    <label for="settingOvertimeDivisor" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px;">Overtime Divisor (Hours / Month)</label>
                    <input type="number" id="settingOvertimeDivisor" name="overtime_divisor" min="1" step="1" 
                           style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s;" 
                           placeholder="e.g. 160">
                    <small style="color: #64748b; font-size: 11px; margin-top: 5px; display: block;">Standard denominator used to compute standard hourly wage = (Base Salary / Divisor).</small>
                </div>

                <!-- Early Arrival Divider/Header -->
                <div style="border-top: 1px solid #f1f5f9; padding-top: 20px; margin-top: 20px; margin-bottom: 16px;">
                    <h4 style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-stopwatch" style="color: #f59e0b;"></i> Early Arrival Settings
                    </h4>
                </div>

                <!-- Enable Early Arrival Switch -->
                <div style="margin-bottom: 20px;">
                    <label for="settingEarlyArrivalEnabled" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px;">Enable Early Arrival Feature</label>
                    <select id="settingEarlyArrivalEnabled" name="early_arrival_enabled" 
                            style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: white; outline: none; transition: border-color 0.2s;">
                        <option value="true">Enabled (Calculate and prompt for approval)</option>
                        <option value="false">Disabled (Ignore check-in earlier than shift start)</option>
                    </select>
                </div>

                <!-- Max Early Arrival Minutes -->
                <div style="margin-bottom: 24px;">
                    <label for="settingMaxEarlyArrivalMinutes" style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px;">Max Eligible Minutes Global</label>
                    <input type="number" id="settingMaxEarlyArrivalMinutes" name="max_early_arrival_minutes" min="0" step="1" 
                           style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s;" 
                           placeholder="e.g. 180">
                    <small style="color: #64748b; font-size: 11px; margin-top: 5px; display: block;">Maximum minutes of early arrival that can be approved per shift. E.g., 180 = 3 Hours.</small>
                </div>

                <!-- Save button -->
                <button type="submit" style="width: 100%; padding: 12px; border: none; border-radius: 8px; background: var(--primary-color); color: white; font-weight: 600; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15); transition: background 0.2s;">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </form>
        </div>

        <!-- Card: List Settings Table -->
        <div class="content-card" style="box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; background: white;">
            <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 24px;">
                <h3 style="font-size: 18px; color: var(--secondary-color); font-weight: 700; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-list-ul" style="color: #10b981;"></i> Active Parameters
                </h3>
                <p style="color: #64748b; font-size: 13px; margin: 0;">Review currently active system variables saved in the database.</p>
            </div>

            <div class="table-container" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 12px;">
                <table style="width: 100%; border-collapse: collapse; min-width: 400px; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="width: 50px; text-align: center; padding: 12px; color: #475569; font-weight: 600;">No</th>
                            <th style="text-align: left; padding: 12px; color: #475569; font-weight: 600;">Parameter</th>
                            <th style="text-align: left; padding: 12px; color: #475569; font-weight: 600;">Database Key</th>
                            <th style="width: 100px; text-align: center; padding: 12px; color: #475569; font-weight: 600;">Value</th>
                            <th style="width: 150px; text-align: center; padding: 12px; color: #475569; font-weight: 600;">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody id="systemSettingsTableBody">
                        <!-- Loaded dynamically via settings module -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
