<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
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
    $routes->resource('employees', ['controller' => 'Employee']);

    // PKWT / Contracts
    $routes->get('contracts/client/(:num)', 'Contract::getByClient/$1');
    $routes->get('contracts/(:num)', 'Contract::show/$1');
    $routes->post('contracts', 'Contract::create');
    $routes->put('contracts/(:num)', 'Contract::update/$1');
    $routes->post('contracts/terminate/(:num)', 'Contract::terminate/$1');

    // Payroll
    $routes->get('payroll/status', 'Payroll::getStatus');
    $routes->post('payroll/process-bulk', 'Payroll::processBulk');
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
});
