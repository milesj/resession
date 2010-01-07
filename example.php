<?php

// Debug testing
error_reporting(E_ALL);

function debug($var) {
	echo '<pre>'. print_r($var, 1) .'</pre>';
}

// Load session
require('session.php');

// Configure class
Session::config(array(
	'name' => 'RESESSION',
	'cookies' => true,
	'security' => 'high'
));

// Create fake session array
$user = array(
	'id' => 1,
	'username' => 'milesj',
	'website' => 'http://www.milesj.me',
	'time' => time()
);

// Set it and print to see results
Session::set('User', $user);
debug(Session::get('User'));

// Test getting a single value
debug(Session::get('User.username'));

// Overwrite parts of the original
Session::set('User.username', 'miles');
debug(Session::get('User.username'));

// Destroy session and see results
Session::destroy();
debug(Session::get('User'));
