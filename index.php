<?php
/**
 * Resession - Manages and manipulates session data with built in security.
 *
 * @author 		Miles Johnson - http://milesj.me
 * @copyright	Copyright 2006-2011, Miles Johnson, Inc.
 * @license 	http://opensource.org/licenses/mit-license.php - Licensed under The MIT License
 * @link		http://milesj.me/resources/script/session-manager
 */

// Turn on errors
error_reporting(E_ALL);

function debug($var) {
	echo '<pre>'. print_r($var, true) .'</pre>';
}

// Include class and instantiate
include_once 'resession/Resession.php';

$session = new Resession(array(
	'security' => Resession::SECURITY_HIGH
));

// Set some data; allows for two levels deep
$session
	->set('foo', 'bar')
	->set('key', 'value')
	->set('class.name', 'resession')
	->set('class.version', $session->version);

// Get the ID
debug($session->id());

// Get the data
debug($session->get('foo'));
debug($session->get('class'));
debug($session->get());

// Delete some data
$session
	->clear('key')
	->clear('class.version');

// Check the data
debug($session->get());