<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
*/

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
| -------------------------------------------------------------------------
| API Routes
| -------------------------------------------------------------------------
*/

// Authentication routes
$route['api/auth/login'] = 'api/auth/login';
$route['api/auth/register'] = 'api/auth/register';
$route['api/auth/profile'] = 'api/auth/profile';
$route['api/auth/update-profile'] = 'api/auth/update_profile';
$route['api/auth/change-password'] = 'api/auth/change_password';

// User routes
$route['api/users'] = 'api/auth/users';
$route['api/users/(:num)'] = 'api/auth/user/$1';

// Category routes
$route['api/categories'] = 'api/category/index';
$route['api/categories/(:num)'] = 'api/category/show/$1';
$route['api/categories/create'] = 'api/category/create';
$route['api/categories/update/(:num)'] = 'api/category/update/$1';
$route['api/categories/delete/(:num)'] = 'api/category/delete/$1';

// Poster routes
$route['api/posters'] = 'api/poster/index';
$route['api/posters/(:num)'] = 'api/poster/show/$1';
$route['api/posters/create'] = 'api/poster/create';
$route['api/posters/update/(:num)'] = 'api/poster/update/$1';
$route['api/posters/delete/(:num)'] = 'api/poster/delete/$1';
$route['api/posters/trending'] = 'api/poster/trending';
$route['api/posters/category/(:num)'] = 'api/poster/by_category/$1';