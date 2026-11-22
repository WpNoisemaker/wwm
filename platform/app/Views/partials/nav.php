<?php
/** The one list of places a visitor can go. The top bar, the phone tab bar and the account rail
 *  all read it, so a section is never added in one place and forgotten in another. */
return [
	['home',  'Home',       '/',          'home'],
	['rings', 'Weddings',   '/weddings',  'weddings'],
	['chart', 'Buzz Chart', '/rankings',  'rankings'],
	['store', 'Vendors',    '/vendors',   'vendors'],
	['cash',  'Market',     '/market',    'market'],
	['talk',  'Community',  '/community', 'community'],
	['book',  'Stories',    '/blog',      'blog'],
];
