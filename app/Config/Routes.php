<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Admin routes
$routes->get('/admin', 'Admin\Home::index');
$routes->get('/admin/messages/datatable', 'Admin\Message::datatableData');
$routes->get('/admin/messages/stats', 'Admin\Message::stats');
$routes->get('/admin/messages/(:num)', 'Admin\Message::get/$1');
$routes->post('/admin/messages/(:num)/resend', 'Admin\Message::resend/$1');
$routes->delete('/admin/messages/pending', 'Admin\Message::deletePending');
$routes->delete('/admin/messages/(:num)', 'Admin\Message::delete/$1');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping');
$routes->match(['post', 'options'], '/api/message', 'Api\Message::index');

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');
$routes->cli('cli/sendmail/process', 'CLI\Sendmail::process');

// Metrics route
$routes->post('/metrics/receive', 'Metrics::receive');

// Logout route
$routes->get('/logout', 'Auth::logout');

// Unauthorised route
$routes->get('/unauthorised', 'Unauthorised::index');

// Custom 404 route
$routes->set404Override('App\Controllers\Errors::show404');

// Debug routes
$routes->get('/debug', 'Debug\Home::index');
$routes->get('/debug/(:segment)', 'Debug\Rerouter::reroute/$1');
$routes->get('/debug/(:segment)/(:segment)', 'Debug\Rerouter::reroute/$1/$2');