<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
*/

$active_group = 'default';
$query_builder = TRUE;
 
$db['default'] = array(
    'dsn'    => '',
    'hostname' => 'sdb-89.hosting.stackcp.net',
    'username' => 'bannerbox_API_v2-3531313702d5',
    'password' => 'z4tno1yseb',
    'database' => 'bannerbox_API_v2-3531313702d5',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);