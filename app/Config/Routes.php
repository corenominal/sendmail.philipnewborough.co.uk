<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping');
$routes->match(['post', 'options'], '/api/message', 'Api\Message::index');

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');
$routes->cli('cli/sendmail/process', 'CLI\Sendmail::process');