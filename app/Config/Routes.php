<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('login', 'Home::login');
$routes->get('dashboard', 'Home::dashboard');
$routes->get('migrasi', 'Migrasi::index');
$routes->get('migrasi/seed-ump', 'Migrasi::seedUmp');

// API Routes
$routes->group('api', function($routes) {
    // Auth
    $routes->post('login', 'Api::login');
    
    // Clients
    $routes->get('clients', 'Api::getClients');
    $routes->post('clients', 'Api::createClient');
    $routes->put('clients/(:num)', 'Api::updateClient/$1');
    $routes->delete('clients/(:num)', 'Api::deleteClient/$1');
    
    // Org Structure
    $routes->get('org', 'Api::getOrg');
    $routes->post('divisions', 'Api::createDivision');
    $routes->post('departments', 'Api::createDepartment');
    $routes->post('positions', 'Api::createPosition');
    $routes->put('org/(:alpha)/(:num)', 'Api::updateOrg/$1/$2');
    $routes->delete('org/(:alpha)/(:num)', 'Api::deleteOrg/$1/$2');
    
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

    // Client Configs
    $routes->get('client-configs', 'Api::getClientConfigs');
    $routes->post('client-configs', 'Api::saveClientConfig');

    // PKWT
    $routes->get('pkwt', 'Api::getPKWT');
    $routes->post('pkwt', 'Api::createPKWT');
    $routes->delete('pkwt/(:num)', 'Api::deletePKWT/$1');

    // Payroll Processing
    $routes->get('periods', 'Api::getPeriods');
    $routes->post('periods', 'Api::createPeriod');
    $routes->get('attendance/(:num)', 'Api::getAttendance/$1');
    $routes->post('attendance', 'Api::saveAttendance');
    $routes->post('generate-payroll/(:num)', 'Api::generatePayroll/$1');
    $routes->get('payroll-results/(:num)', 'Api::getPayrollResults/$1');
    $routes->post('approve-payroll/(:num)', 'Api::approvePayroll/$1');
    $routes->get('slip-details/(:num)', 'Api::getSlipDetails/$1');

    // Minimum Wages (UMP/UMK)
    $routes->get('minimum-wages', 'Api::getMinimumWages');
    $routes->post('minimum-wages', 'Api::saveMinimumWages');

    // Employees
    $routes->get('employees', 'Api::getEmployees');
});
$routes->get('login', 'Home::login');

$routes->group('api', function($routes) {
    // Auth
    $routes->post('login', 'Auth::login');

    // Clients
    $routes->get('clients', 'Client::index');
    $routes->post('clients', 'Client::create');
    $routes->put('clients/(:num)', 'Client::update/$1');
    $routes->delete('clients/(:num)', 'Client::delete/$1');

    // Org Hierarchy
    $routes->get('org', 'Org::index');
    $routes->post('divisions', 'Org::createDivision');
    $routes->post('departments', 'Org::createDepartment');
    $routes->post('positions', 'Org::createPosition');
    $routes->put('org/(:segment)/(:num)', 'Org::update/$1/$2');
    $routes->delete('org/(:segment)/(:num)', 'Org::delete/$1/$2');
});
