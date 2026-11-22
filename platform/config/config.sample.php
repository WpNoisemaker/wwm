<?php
/**
 * Copy this file to config.php and fill it in on the server. config.php itself is never committed,
 * so a deploy cannot overwrite the live database credentials or the app key.
 */
return array(
	'db'    => array('host' => 'localhost', 'name' => '', 'user' => '', 'pass' => '', 'port' => 3306),
	'url'   => 'https://example.com',
	'name'  => 'Wedding Websites & More',
	// 32+ random characters. Changing it signs every signed-in visitor out.
	'key'   => '',
	'mail'  => array('from' => '', 'name' => 'Wedding Websites & More'),
	'debug' => false,
	'tz'    => 'Africa/Lagos',
);
