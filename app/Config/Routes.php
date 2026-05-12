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
