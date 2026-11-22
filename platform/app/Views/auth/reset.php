<?php
$slot = View::capture('auth/parts/reset-form', ['token' => $token], null);
echo View::partial('auth/_shell', ['slot' => $slot, 'foot' => '']);
