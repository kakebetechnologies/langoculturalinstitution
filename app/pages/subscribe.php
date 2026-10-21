<?php
declare(strict_types=1);
if (!is_post()) redirect('');
csrf_check();
$back = post('back', '/');
if (!str_starts_with($back, BASE . '/') && $back !== BASE) $back = url();
$email = strtolower(post('email'));
if (!throttle('subscribe', 5)) { flash(__('Too many attempts. Please try later.'), 'error'); redirect($back); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { flash(__('Please enter a valid email address.'), 'error'); redirect($back); }
q('INSERT IGNORE INTO subscribers (email) VALUES (?)', [$email]);
flash(__('Thank you for subscribing!'));
redirect($back);
