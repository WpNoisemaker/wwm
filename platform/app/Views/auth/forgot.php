<?php
$slot = View::capture('auth/parts/forgot-form', ['link' => $link], null);
$foot = '<a href="' . e(App::url('/sign-in')) . '">Back to sign in</a>';
echo View::partial('auth/_shell', ['slot' => $slot, 'foot' => $foot]);
