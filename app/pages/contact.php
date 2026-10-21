<?php
declare(strict_types=1);

if (is_post()) {
    csrf_check(); keep_old($_POST);
    if (!empty($_POST['website'])) redirect('contact');
    if (!throttle('contact', 4)) { flash(__('Too many messages. Please wait a few minutes.'), 'error'); redirect('contact'); }
    $name = post('name'); $email = post('email'); $body = post('body');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($body) < 10) { flash(__('Please enter your name, a valid email and your message.'), 'error'); redirect('contact'); }
    insert('messages', ['name' => mb_substr($name, 0, 150), 'email' => $email, 'phone' => post('phone') ?: null, 'subject' => mb_substr(post('subject'), 0, 190) ?: null, 'body' => mb_substr($body, 0, 5000)]);
    clear_old();
    flash(__('Thank you — your message has been sent. We will reply as soon as we can.'));
    redirect('contact');
}
page_start(__('Contact'), ['hero' => ['eyebrow' => __('Get in touch'), 'title' => __('Contact the institution'), 'sub' => __('Questions, partnerships, press or research — we would love to hear from you.'), 'crumbs' => [[__('Contact'), null]], 'image' => 'assets/img/photos/hero-road.jpg']]);
?>
<section class="section"><div class="container article article--form">
  <form class="formcard form" method="post"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
    <h2 style="font-size:1.6rem;margin:0"><?php _e('Send us a message'); ?></h2>
    <div class="fgrid"><div class="field"><label><?php _e('Your name'); ?> *</label><input type="text" name="name" required value="<?= e(user()['name'] ?? old('name')) ?>"></div><div class="field"><label><?php _e('Email'); ?> *</label><input type="email" name="email" required value="<?= e(user()['email'] ?? old('email')) ?>"></div>
      <div class="field"><label><?php _e('Phone'); ?></label><input type="tel" name="phone" value="<?= e(old('phone')) ?>"></div><div class="field"><label><?php _e('Subject'); ?></label><input type="text" name="subject" value="<?= e(old('subject')) ?>"></div></div>
    <div class="field"><label><?php _e('Message'); ?> *</label><textarea name="body" required minlength="10" style="min-height:170px"><?= e(old('body')) ?></textarea></div>
    <div><button class="btn btn--primary"><?= icon('mail') ?> <?php _e('Send message'); ?></button></div>
  </form>
  <aside class="aside">
    <div class="box"><h4><?php _e('Office'); ?></h4><dl class="kv" style="grid-template-columns:30px 1fr">
      <dt><?= icon('pin') ?></dt><dd><?= e(setting('address')) ?></dd>
      <dt><?= icon('mail') ?></dt><dd><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></dd>
      <dt><?= icon('phone') ?></dt><dd><a href="tel:<?= e(preg_replace('/\s+/', '', setting('phone'))) ?>"><?= e(setting('phone')) ?></a></dd></dl>
      <?php if (setting('whatsapp')): ?><p style="margin:14px 0 0"><a class="btn btn--outline btn--sm" href="<?= e(setting('whatsapp')) ?>" target="_blank" rel="noopener"><?= icon('chat') ?> WhatsApp</a></p><?php endif; ?></div>
    <div class="box box--dark"><h4><?php _e('Looking for something specific?'); ?></h4><p style="line-height:2"><a href="<?= e(url('apply/volunteer')) ?>" style="color:var(--gold)"><?php _e('Volunteer'); ?></a><br><a href="<?= e(url('apply/partner')) ?>" style="color:var(--gold)"><?php _e('Partner with LCI'); ?></a><br><a href="<?= e(url('developers')) ?>" style="color:var(--gold)"><?php _e('Data & research access'); ?></a></p></div>
  </aside>
</div></section>
<?php page_end(); ?>
