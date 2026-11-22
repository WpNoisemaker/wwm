<?php
$slot = View::capture('auth/parts/sign-in-form', ['to' => $to], null);
$foot = 'New here? <a href="' . e(App::url('/join')) . '">Create a free page</a>.';
echo View::partial('auth/_shell', ['slot' => $slot, 'foot' => $foot]);
