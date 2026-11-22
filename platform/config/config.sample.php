<?php
/**
 * Copy to config.php and fill in. config.php is never served (see .htaccess) and never committed.
 */
return array(
	'db'   => array(
		'host' => 'localhost',
		'name' => 'your_db',
		'user' => 'your_user',
		'pass' => '',
		'port' => 3306,
	),
	'url'  => 'https://weddingwebsites.io',
	'name' => 'Wedding Websites & More',
	// 32+ random characters. Used to sign cookies and tokens.
	'key'  => '',
	'mail' => array( 'from' => 'more@weddingwebsites.io', 'name' => 'Wedding Websites & More' ),
	'debug' => false,
);
