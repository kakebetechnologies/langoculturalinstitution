<?php
declare(strict_types=1);
if (!is_post()) redirect('news');
csrf_check();
$post = one("SELECT id, slug FROM posts WHERE id=? AND status='published'", [(int)post('post_id')]);
if (!$post) not_found();
$back = 'news/' . $post['slug'] . '#comments';
if (!empty($_POST['website'])) redirect($back);
if (!throttle('comment', 5)) { flash(__('Too many comments. Please wait a few minutes.'), 'error'); redirect($back); }
$name = post('name'); $body = post('body'); $email = post('email');
if ($name === '' || mb_strlen($body) < 2 || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) { flash(__('Please enter your name and a comment.'), 'error'); redirect($back); }
insert('comments', ['post_id' => $post['id'], 'name' => mb_substr($name, 0, 100), 'email' => $email ?: null, 'body' => mb_substr($body, 0, 2000)]);
flash(__('Thank you! Your comment will appear once a moderator has approved it.'));
redirect($back);
