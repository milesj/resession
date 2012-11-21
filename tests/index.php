<?php
/**
 * @copyright	Copyright 2006-2012, Miles Johnson - http://milesj.me
 * @license		http://opensource.org/licenses/mit-license.php - Licensed under the MIT License
 * @link		http://milesj.me/code/php/resession
 */

// Turn on errors
error_reporting(E_ALL);

function debug($var) {
	echo '<pre>' . print_r($var, true) . '</pre>';
}

// Include class and instantiate
include_once '../Resession.php';

$session = new \mjohnson\resession\Resession(array(
	'security' => \mjohnson\resession\Resession::SECURITY_HIGH
));

// Set some data; allows for two levels deep
$session
	->set('foo', 'bar')
	->set('key', 'value')
	->set('class.name', 'resession');

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