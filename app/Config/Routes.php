<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'Home::login');
$routes->get('dashboard', 'Home::dashboard');
$routes->get('calculator', 'Home::calculator');
$routes->get('migrasi', 'Migrasi::index');
$routes->cli('migrasi', 'Migrasi::index');
$routes->get('migrasi/seed-ump', 'Migrasi::seedUmp');
$routes->get('seed-dummy', 'DummySeeder::run');

// =====================================================================
// PUBLIC API ROUTES — No authentication required
// =====================================================================
$routes->group('api', function($routes) {
    $routes->post('login', 'Api::login');
    $routes->post('auth/register', 'Auth::register');
    $routes->post('auth/forgot-password', 'Auth::forgotPassword');
    $routes->post('auth/reset-password', 'Auth::resetPassword');
    // Auth (new controller)
    $routes->post('login', 'Auth::login');
});

// =====================================================================
// READ-ONLY API ROUTES — Any authenticated & active user
// =====================================================================
$routes->group('api', ['filter' => 'role'], function($routes) {
    // Clients (read-only for all authenticated roles)
    $routes->get('clients', 'Api::getClients');
    $routes->get('clients', 'Client::index');
    $routes->get('clients/schema/(:num)', 'Client::getSchema/$1');
    $routes->get('clients/components/(:num)', 'Client::getComponents/$1');

    // Org Structure (read)
    $routes->get('org', 'Org::index');
    $routes->get('positions/client/(:num)', 'Org::getPositionsByClient/$1');

    // Global STO (read)
    $routes->get('global-divisions', 'GlobalSto::getDivisions');
    $routes->get('global-departments', 'GlobalSto::getDepartments');
    $routes->get('global-positions', 'GlobalSto::getPositions');

    // Payroll Schemes (read)
    $routes->get('payroll-schemes', 'Api::getPayrollSchemes');
    $routes->get('payroll-schemes-templates', 'PayrollScheme::index');
    $routes->get('payroll-schemes-templates/by-org', 'PayrollScheme::getByOrgStructure');
    $routes->get('payroll-schemes-templates/for-employee', 'PayrollScheme::getSchemeForEmployee');
    $routes->get('payroll-schemes-templates/(:num)', 'PayrollScheme::show/$1');

    // Tax Schemes (read)
    $routes->get('tax-schemes', 'Api::getTaxSchemes');

    // Compensation Schemes (read)
    $routes->get('compensation-schemes', 'Api::getCompensationSchemes');

    // Schedule Templates (read)
    $routes->get('schedule-templates', 'Api::getScheduleTemplates');

    // Client Configs (read)
    $routes->get('client-configs', 'Api::getClientConfigs');
    $routes->get('client-configs-mapping/(:num)', 'Api::getClientConfigMappings/$1');

    // PKWT (read)
    $routes->get('pkwt', 'Api::getPKWT');
    $routes->get('sync-employees-pkwt', 'Api::syncEmployeesPKWTEndpoint');

    // Payroll Processing (read)
    $routes->get('periods', 'Api::getPeriods');
    $routes->get('attendance/(:num)', 'Api::getAttendance/$1');
    $routes->get('payroll-results/(:num)', 'Api::getPayrollResults/$1');
    $routes->get('slip-details/(:num)', 'Api::getSlipDetails/$1');
    $routes->get('export-payroll/(:num)', 'Api::exportPayrollCsv/$1');
    $routes->get('payroll-export/(:num)', 'Api::exportExcel/$1');
    $routes->get('payroll/status', 'Payroll::getStatus');
    $routes->get('payroll/attendance-summary', 'Payroll::getAttendanceSummary');
    $routes->get('payroll/check', 'Payroll::checkCutOff');
    $routes->get('payroll/slip/(:num)', 'Payroll::getSlip/$1');

    // Minimum Wages (read)
    $routes->get('minimum-wages', 'Api::getMinimumWages');
    $routes->get('seed-umk', 'UmkSeeder::index');

    // Client Compensations (read)
    $routes->get('client-compensations/(:num)', 'Api::getCompensations/$1');

    // Client Absence Config (read)
    $routes->get('client-absence-config/(:num)', 'Api::getAbsenceConfig/$1');
    $routes->get('check-schema', 'Api::checkSchema');
    $routes->get('preview-payroll', 'Api::previewPayroll');

    // Notifications (read)
    $routes->get('notifications', 'Api::getNotifications');
    $routes->post('notifications/dismiss', 'Api::dismissNotification');

    // Logs (read)
    $routes->get('logs', 'Api::getLogs');

    // Attendance Logs (read)
    $routes->get('attendance-logs', 'Api::getAttendanceLogs');

    // Overtime Logs (read)
    $routes->get('overtime-logs', 'Api::getOvertimeLogs');

    // Early Arrival Logs (read)
    $routes->get('early-arrival', 'Api::getEarlyArrivalLogs');

    // Holidays (read)
    $routes->get('holidays', 'Api::getHolidays');

    // Employees (read)
    $routes->get('employees', 'Employee::index');
    $routes->get('employees/(:num)', 'Employee::show/$1');
    $routes->get('employees/next-employ-id', 'Employee::nextEmployId');

    // Work Locations (read)
    $routes->get('work-locations', 'WorkLocation::index');
    $routes->get('work-locations/(:num)', 'WorkLocation::show/$1');

    // Contracts (read)
    $routes->get('contracts/client/(:num)', 'Contract::getByClient/$1');
    $routes->get('contracts/(:num)', 'Contract::show/$1');

    // Shift Schemes (read)
    $routes->get('shift-schemes', 'Api::getShiftSchemes');

    // Employee Shifts (read)
    $routes->get('employee-shifts', 'Api::getEmployeeShifts');

    // AI Assistant (all authenticated users)
    $routes->post('ai/summarize-dashboard', 'Ai::summarizeDashboard');
    $routes->post('ai/summarize-payroll', 'Ai::summarizePayroll');
    $routes->post('ai/chat', 'Ai::chat');
});

// =====================================================================
// CLIENT CRUD — Admin, Payroll, HC Ops, Recruiter, and Business Development
// =====================================================================
$routes->group('api', ['filter' => 'role:payroll,hc_ops,recruiter,business_development'], function($routes) {
    $routes->post('clients/bulk', 'Api::createClientBulk');
    $routes->post('clients', 'Api::createClient');
    $routes->put('clients/(:num)', 'Api::updateClient/$1');
    $routes->delete('clients/(:num)', 'Api::deleteClient/$1');
    $routes->post('clients', 'Client::create');
    $routes->put('clients/(:num)', 'Client::update/$1');
    $routes->delete('clients/(:num)', 'Client::delete/$1');
});

// =====================================================================
// HC OPS — Manage Client Schema, Org, Scheme, Compensation
// =====================================================================
$routes->group('api', ['filter' => 'role:hc_ops'], function($routes) {
    // Client Config Details (Schema & Components)
    $routes->post('clients/schema', 'Client::saveSchema');
    $routes->post('clients/components', 'Client::saveComponent');
    $routes->delete('clients/components/(:num)', 'Client::deleteComponent/$1');

    // Org Structure CRUD
    $routes->post('divisions', 'Org::createDivision');
    $routes->post('departments', 'Org::createDepartment');
    $routes->post('positions', 'Org::createPosition');
    $routes->put('org/(:segment)/(:num)', 'Org::update/$1/$2');
    $routes->delete('org/(:segment)/(:num)', 'Org::delete/$1/$2');

    // Global STO CRUD
    $routes->post('global-divisions', 'GlobalSto::createDivision');
    $routes->put('global-divisions/(:num)', 'GlobalSto::updateDivision/$1');
    $routes->delete('global-divisions/(:num)', 'GlobalSto::deleteDivision/$1');
    $routes->post('global-departments', 'GlobalSto::createDepartment');
    $routes->put('global-departments/(:num)', 'GlobalSto::updateDepartment/$1');
    $routes->delete('global-departments/(:num)', 'GlobalSto::deleteDepartment/$1');
    $routes->post('global-positions', 'GlobalSto::createPosition');
    $routes->put('global-positions/(:num)', 'GlobalSto::updatePosition/$1');
    $routes->delete('global-positions/(:num)', 'GlobalSto::deletePosition/$1');

    // Payroll Schemes CRUD
    $routes->post('payroll-schemes', 'Api::createPayrollScheme');
    $routes->put('payroll-schemes/(:num)', 'Api::updatePayrollScheme/$1');
    $routes->delete('payroll-schemes/(:num)', 'Api::deletePayrollScheme/$1');
    $routes->post('payroll-components', 'Api::createPayrollComponent');
    $routes->put('payroll-components/(:num)', 'Api::updatePayrollComponent/$1');
    $routes->delete('payroll-components/(:num)', 'Api::deletePayrollComponent/$1');
    $routes->post('payroll-schemes-templates', 'PayrollScheme::create');
    $routes->put('payroll-schemes-templates/(:num)', 'PayrollScheme::update/$1');
    $routes->delete('payroll-schemes-templates/(:num)', 'PayrollScheme::delete/$1');
    $routes->post('payroll-schemes-templates/toggle-active/(:num)', 'PayrollScheme::toggleActive/$1');

    // Tax Schemes CRUD
    $routes->post('tax-schemes', 'Api::createTaxScheme');
    $routes->put('tax-schemes/(:num)', 'Api::updateTaxScheme/$1');
    $routes->delete('tax-schemes/(:num)', 'Api::deleteTaxScheme/$1');

    // Compensation Schemes CRUD
    $routes->post('compensation-schemes', 'Api::createCompensationScheme');
    $routes->put('compensation-schemes/(:num)', 'Api::updateCompensationScheme/$1');
    $routes->delete('compensation-schemes/(:num)', 'Api::deleteCompensationScheme/$1');
    $routes->post('compensation-components', 'Api::createCompensationComponent');
    $routes->put('compensation-components/(:num)', 'Api::updateCompensationComponent/$1');
    $routes->delete('compensation-components/(:num)', 'Api::deleteCompensationComponent/$1');

    // Schedule Templates CRUD
    $routes->post('schedule-templates', 'Api::createScheduleTemplate');
    $routes->put('schedule-templates/(:num)', 'Api::updateScheduleTemplate/$1');
    $routes->delete('schedule-templates/(:num)', 'Api::deleteScheduleTemplate/$1');

    // Client Configs CRUD
    $routes->post('client-configs', 'Api::saveClientConfig');
    $routes->delete('client-configs/(:num)', 'Api::deleteClientConfig/$1');

    // Client Compensations CRUD
    $routes->post('client-compensations', 'Api::createCompensation');
    $routes->put('client-compensations/(:num)', 'Api::updateCompensation/$1');
    $routes->delete('client-compensations/(:num)', 'Api::deleteCompensation/$1');

    // Client Absence Config
    $routes->post('client-absence-config', 'Api::saveAbsenceConfig');
});

// =====================================================================
// RECRUITER & HC OPS — Employee & Contract management
// =====================================================================
$routes->group('api', ['filter' => 'role:recruiter,hc_ops'], function($routes) {
    $routes->post('employees', 'Employee::create');
    $routes->put('employees/(:num)', 'Employee::update/$1');
    $routes->delete('employees/(:num)', 'Employee::delete/$1');

    // PKWT / Contracts
    $routes->post('pkwt', 'Api::createPKWT');
    $routes->delete('pkwt/(:num)', 'Api::deletePKWT/$1');
    $routes->post('contracts', 'Contract::create');
    $routes->put('contracts/(:num)', 'Contract::update/$1');
    $routes->post('contracts/terminate/(:num)', 'Contract::terminate/$1');
});

// =====================================================================
// PAYROLL — Generate salary, manage minimum wages & periods
// =====================================================================
$routes->group('api', ['filter' => 'role:payroll'], function($routes) {
    // Minimum Wages
    $routes->post('minimum-wages', 'Api::saveMinimumWages');

    // Payroll Processing
    $routes->post('periods', 'Api::createPeriod');
    $routes->post('attendance', 'Api::saveAttendance');
    $routes->post('attendance-bulk', 'Api::saveAttendanceBulk');
    $routes->post('generate-payroll/(:num)', 'Api::generatePayroll/$1');
    $routes->post('payroll/process-bulk', 'Payroll::processBulk');
    $routes->post('upload-manual-payroll', 'Api::uploadManualPayroll');
});

// =====================================================================
// HC OPS — Holidays, attendance, shifts, overtime management
// =====================================================================
$routes->group('api', ['filter' => 'role:hc_ops'], function($routes) {
    // Holidays
    $routes->post('holidays', 'Api::createHoliday');
    $routes->post('holidays/sync', 'Api::syncGoogleHolidays');
    $routes->put('holidays/(:num)', 'Api::updateHoliday/$1');
    $routes->delete('holidays/(:num)', 'Api::deleteHoliday/$1');

    // Attendance Logs
    $routes->post('attendance-logs', 'Api::createAttendanceLog');
    $routes->post('attendance-logs/bulk', 'Api::createAttendanceBulk');
    $routes->put('attendance-logs/(:num)', 'Api::updateAttendanceLog/$1');
    $routes->delete('attendance-logs/(:num)', 'Api::deleteAttendanceLog/$1');

    // Overtime Logs
    $routes->post('overtime-logs', 'Api::createOvertimeLog');
    $routes->put('overtime-logs/(:num)', 'Api::updateOvertimeLog/$1');
    $routes->delete('overtime-logs/(:num)', 'Api::deleteOvertimeLog/$1');
    $routes->post('overtime-logs/approve/(:num)', 'Api::approveOvertimeLog/$1');
    $routes->post('overtime-logs/reject/(:num)', 'Api::rejectOvertimeLog/$1');
    $routes->post('overtime-logs/bulk-approve', 'Api::bulkApproveOvertimeLogs');
    $routes->post('overtime-logs/bulk-reject', 'Api::bulkRejectOvertimeLogs');
    $routes->post('overtime-logs/import', 'Api::importOvertimeLogs');

    // Early Arrival Logs
    $routes->post('early-arrival/approve/(:num)', 'Api::approveEarlyArrivalLog/$1');
    $routes->post('early-arrival/reject/(:num)', 'Api::rejectEarlyArrivalLog/$1');
    $routes->post('early-arrival/reset/(:num)', 'Api::resetEarlyArrivalLog/$1');
    $routes->post('early-arrival/bulk-approve', 'Api::bulkApproveEarlyArrivalLogs');
    $routes->post('early-arrival/bulk-reject', 'Api::bulkRejectEarlyArrivalLogs');

    // Shift Schemes
    $routes->post('shift-schemes', 'Api::createShiftScheme');
    $routes->put('shift-schemes/(:num)', 'Api::updateShiftScheme/$1');
    $routes->delete('shift-schemes/(:num)', 'Api::deleteShiftScheme/$1');

    // Employee Shifts
    $routes->post('employee-shifts', 'Api::assignEmployeeShift');
    $routes->delete('employee-shifts/(:num)', 'Api::deleteEmployeeShift/$1');

    // Work Locations
    $routes->post('work-locations', 'WorkLocation::create');
    $routes->put('work-locations/(:num)', 'WorkLocation::update/$1');
    $routes->delete('work-locations/(:num)', 'WorkLocation::delete/$1');
});

// =====================================================================
// CLIENT / SUPERIOR — Approve/reject payroll
// =====================================================================
$routes->group('api', ['filter' => 'role:client_superior'], function($routes) {
    $routes->post('approve-payroll/(:num)', 'Api::approvePayroll/$1');
    $routes->post('approve-payroll-bulk', 'Api::approvePayrollBulk');
    $routes->post('payroll/approve/(:num)', 'Payroll::approve/$1');
    $routes->post('payroll/approve-all', 'Payroll::approveAll');
    $routes->delete('payroll/reject/(:num)', 'Payroll::reject/$1');
});

