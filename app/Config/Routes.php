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

// API Routes
$routes->group('api', function($routes) {
    // Clients
    $routes->get('clients', 'Api::getClients');
    $routes->post('clients', 'Api::createClient');
    $routes->put('clients/(:num)', 'Api::updateClient/$1');
    $routes->delete('clients/(:num)', 'Api::deleteClient/$1');
    
    // Org Structure handled in Org.php below
    
    // Logs
    $routes->get('logs', 'Api::getLogs');

    // Payroll
    $routes->get('payroll-schemes', 'Api::getPayrollSchemes');
    $routes->post('payroll-schemes', 'Api::createPayrollScheme');
    $routes->put('payroll-schemes/(:num)', 'Api::updatePayrollScheme/$1');
    $routes->delete('payroll-schemes/(:num)', 'Api::deletePayrollScheme/$1');
    
    $routes->post('payroll-components', 'Api::createPayrollComponent');
    $routes->put('payroll-components/(:num)', 'Api::updatePayrollComponent/$1');
    $routes->delete('payroll-components/(:num)', 'Api::deletePayrollComponent/$1');

    // Tax Schemes
    $routes->get('tax-schemes', 'Api::getTaxSchemes');
    $routes->post('tax-schemes', 'Api::createTaxScheme');
    $routes->put('tax-schemes/(:num)', 'Api::updateTaxScheme/$1');
    $routes->delete('tax-schemes/(:num)', 'Api::deleteTaxScheme/$1');

    // Compensation Schemes
    $routes->get('compensation-schemes', 'Api::getCompensationSchemes');
    $routes->post('compensation-schemes', 'Api::createCompensationScheme');
    $routes->put('compensation-schemes/(:num)', 'Api::updateCompensationScheme/$1');
    $routes->delete('compensation-schemes/(:num)', 'Api::deleteCompensationScheme/$1');
    
    $routes->post('compensation-components', 'Api::createCompensationComponent');
    $routes->put('compensation-components/(:num)', 'Api::updateCompensationComponent/$1');
    $routes->delete('compensation-components/(:num)', 'Api::deleteCompensationComponent/$1');

    // Schedule Templates
    $routes->get('schedule-templates', 'Api::getScheduleTemplates');
    $routes->post('schedule-templates', 'Api::createScheduleTemplate');
    $routes->put('schedule-templates/(:num)', 'Api::updateScheduleTemplate/$1');
    $routes->delete('schedule-templates/(:num)', 'Api::deleteScheduleTemplate/$1');

    // Client Configs
    $routes->get('client-configs', 'Api::getClientConfigs');
    $routes->get('client-configs-mapping/(:num)', 'Api::getClientConfigMappings/$1');
    $routes->post('client-configs', 'Api::saveClientConfig');
    $routes->delete('client-configs/(:num)', 'Api::deleteClientConfig/$1');

    // PKWT
    $routes->get('pkwt', 'Api::getPKWT');
    $routes->post('pkwt', 'Api::createPKWT');
    $routes->delete('pkwt/(:num)', 'Api::deletePKWT/$1');
    $routes->get('sync-employees-pkwt', 'Api::syncEmployeesPKWTEndpoint');

    // Payroll Processing
    $routes->get('periods', 'Api::getPeriods');
    $routes->post('periods', 'Api::createPeriod');
    $routes->get('attendance/(:num)', 'Api::getAttendance/$1');
    $routes->post('attendance', 'Api::saveAttendance');
    $routes->post('attendance-bulk', 'Api::saveAttendanceBulk');
    $routes->post('generate-payroll/(:num)', 'Api::generatePayroll/$1');
    $routes->get('payroll-results/(:num)', 'Api::getPayrollResults/$1');
    $routes->post('approve-payroll/(:num)', 'Api::approvePayroll/$1');
    $routes->post('approve-payroll-bulk', 'Api::approvePayrollBulk');
    $routes->get('slip-details/(:num)', 'Api::getSlipDetails/$1');
    $routes->get('export-payroll/(:num)', 'Api::exportPayrollCsv/$1');
    $routes->get('payroll-export/(:num)', 'Api::exportExcel/$1');

    // Minimum Wages (UMP/UMK)
    $routes->get('minimum-wages', 'Api::getMinimumWages');
    $routes->post('minimum-wages', 'Api::saveMinimumWages');
    $routes->get('seed-umk', 'UmkSeeder::index');

    // Client Compensations
    $routes->get('client-compensations/(:num)', 'Api::getCompensations/$1');
    $routes->post('client-compensations', 'Api::createCompensation');
    $routes->put('client-compensations/(:num)', 'Api::updateCompensation/$1');
    $routes->delete('client-compensations/(:num)', 'Api::deleteCompensation/$1');

    // Client Absence Config
    $routes->get('client-absence-config/(:num)', 'Api::getAbsenceConfig/$1');
    $routes->post('client-absence-config', 'Api::saveAbsenceConfig');
    $routes->get('check-schema', 'Api::checkSchema');
    $routes->get('notifications', 'Api::getNotifications');
    $routes->get('preview-payroll', 'Api::previewPayroll');

    // Attendance Logs
    $routes->get('attendance-logs', 'Api::getAttendanceLogs');
    $routes->post('attendance-logs', 'Api::createAttendanceLog');
    $routes->post('attendance-logs/bulk', 'Api::createAttendanceBulk');
    $routes->put('attendance-logs/(:num)', 'Api::updateAttendanceLog/$1');
    $routes->delete('attendance-logs/(:num)', 'Api::deleteAttendanceLog/$1');

    // Overtime Logs
    $routes->get('overtime-logs', 'Api::getOvertimeLogs');
    $routes->post('overtime-logs', 'Api::createOvertimeLog');
    $routes->put('overtime-logs/(:num)', 'Api::updateOvertimeLog/$1');
    $routes->delete('overtime-logs/(:num)', 'Api::deleteOvertimeLog/$1');
    $routes->post('overtime-logs/approve/(:num)', 'Api::approveOvertimeLog/$1');
    $routes->post('overtime-logs/reject/(:num)', 'Api::rejectOvertimeLog/$1');
    $routes->post('overtime-logs/bulk-approve', 'Api::bulkApproveOvertimeLogs');
    $routes->post('overtime-logs/bulk-reject', 'Api::bulkRejectOvertimeLogs');
    $routes->post('overtime-logs/import', 'Api::importOvertimeLogs');

    // Holiday Calendar
    $routes->get('holidays', 'Api::getHolidays');
    $routes->post('holidays', 'Api::createHoliday');
    $routes->post('holidays/sync', 'Api::syncGoogleHolidays');
    $routes->put('holidays/(:num)', 'Api::updateHoliday/$1');
    $routes->delete('holidays/(:num)', 'Api::deleteHoliday/$1');

    // System Settings
    $routes->get('settings', 'Api::getSettings');
    $routes->post('settings', 'Api::saveSettings');

    // Employees
});
$routes->get('login', 'Home::login');

$routes->group('api', function($routes) {
    // Auth
    $routes->post('login', 'Auth::login');

    // Clients & Schema
    $routes->get('clients', 'Client::index');
    $routes->post('clients', 'Client::create');
    $routes->put('clients/(:num)', 'Client::update/$1');
    $routes->delete('clients/(:num)', 'Client::delete/$1');
    $routes->get('clients/schema/(:num)', 'Client::getSchema/$1');
    $routes->post('clients/schema', 'Client::saveSchema');

    // Payroll Components (Custom Tunjangan/Potongan per Klien)
    $routes->get('clients/components/(:num)', 'Client::getComponents/$1');
    $routes->post('clients/components', 'Client::saveComponent');
    $routes->delete('clients/components/(:num)', 'Client::deleteComponent/$1');

    // Employees
    $routes->get('employees/next-employ-id', 'Employee::nextEmployId');
    $routes->resource('employees', ['controller' => 'Employee']);


    // Work Locations
    $routes->resource('work-locations', ['controller' => 'WorkLocation']);

    // PKWT / Contracts
    $routes->get('contracts/client/(:num)', 'Contract::getByClient/$1');
    $routes->get('contracts/(:num)', 'Contract::show/$1');
    $routes->post('contracts', 'Contract::create');
    $routes->put('contracts/(:num)', 'Contract::update/$1');
    $routes->post('contracts/terminate/(:num)', 'Contract::terminate/$1');

    // Payroll
    $routes->get('payroll/status', 'Payroll::getStatus');
    $routes->post('payroll/process-bulk', 'Payroll::processBulk');
    $routes->get('payroll/attendance-summary', 'Payroll::getAttendanceSummary');
    $routes->get('payroll/check', 'Payroll::checkCutOff');
    $routes->post('payroll/approve/(:num)', 'Payroll::approve/$1');
    $routes->post('payroll/approve-all', 'Payroll::approveAll');
    $routes->delete('payroll/reject/(:num)', 'Payroll::reject/$1');
    $routes->get('payroll/slip/(:num)', 'Payroll::getSlip/$1');

    // Org Hierarchy
    $routes->get('org', 'Org::index');
    $routes->get('positions/client/(:num)', 'Org::getPositionsByClient/$1');
    $routes->post('divisions', 'Org::createDivision');
    $routes->post('departments', 'Org::createDepartment');
    $routes->post('positions', 'Org::createPosition');
    $routes->put('org/(:segment)/(:num)', 'Org::update/$1/$2');
    $routes->delete('org/(:segment)/(:num)', 'Org::delete/$1/$2');

    // Global STO
    $routes->get('global-divisions', 'GlobalSto::getDivisions');
    $routes->post('global-divisions', 'GlobalSto::createDivision');
    $routes->put('global-divisions/(:num)', 'GlobalSto::updateDivision/$1');
    $routes->delete('global-divisions/(:num)', 'GlobalSto::deleteDivision/$1');

    $routes->get('global-departments', 'GlobalSto::getDepartments');
    $routes->post('global-departments', 'GlobalSto::createDepartment');
    $routes->put('global-departments/(:num)', 'GlobalSto::updateDepartment/$1');
    $routes->delete('global-departments/(:num)', 'GlobalSto::deleteDepartment/$1');

    $routes->get('global-positions', 'GlobalSto::getPositions');
    $routes->post('global-positions', 'GlobalSto::createPosition');
    $routes->put('global-positions/(:num)', 'GlobalSto::updatePosition/$1');
    $routes->delete('global-positions/(:num)', 'GlobalSto::deletePosition/$1');

    // Payroll Scheme Templates (Multiple schemes per org structure)
    $routes->get('payroll-schemes', 'PayrollScheme::index');
    $routes->get('payroll-schemes/by-org', 'PayrollScheme::getByOrgStructure');
    $routes->get('payroll-schemes/for-employee', 'PayrollScheme::getSchemeForEmployee');
    $routes->get('payroll-schemes/(:num)', 'PayrollScheme::show/$1');
    $routes->post('payroll-schemes', 'PayrollScheme::create');
    $routes->put('payroll-schemes/(:num)', 'PayrollScheme::update/$1');
    $routes->delete('payroll-schemes/(:num)', 'PayrollScheme::delete/$1');
    $routes->post('payroll-schemes/toggle-active/(:num)', 'PayrollScheme::toggleActive/$1');
    // Shift Schemes
    $routes->get('shift-schemes', 'Api::getShiftSchemes');
    $routes->post('shift-schemes', 'Api::createShiftScheme');
    $routes->put('shift-schemes/(:num)', 'Api::updateShiftScheme/$1');
    $routes->delete('shift-schemes/(:num)', 'Api::deleteShiftScheme/$1');

    // Employee Shifts
    $routes->get('employee-shifts', 'Api::getEmployeeShifts');
    $routes->post('employee-shifts', 'Api::assignEmployeeShift');
    $routes->delete('employee-shifts/(:num)', 'Api::deleteEmployeeShift/$1');

    // AI Assistant & Summarizer
    $routes->post('ai/summarize-dashboard', 'Ai::summarizeDashboard');
    $routes->post('ai/summarize-payroll', 'Ai::summarizePayroll');
    $routes->post('ai/chat', 'Ai::chat');
});
