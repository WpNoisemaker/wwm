<?php
$slot = View::capture('auth/parts/join-form', [], null);
$foot = 'Already have an account? <a href="' . e(App::url('/sign-in')) . '">Sign in</a>.';
echo View::partial('auth/_shell', ['slot' => $slot, 'foot' => $foot]);
