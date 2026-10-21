<?php
declare(strict_types=1);

if (is_post()) {
    csrf_check();
    keep_old($_POST);
    if (!empty($_POST['website'])) redirect('diaspora');
    if (!throttle('diaspora', 5)) { flash(__('Too many submissions. Please wait a few minutes.'), 'error'); redirect('diaspora'); }
    $name = post('name'); $email = post('email'); $org = post('organization');
    if ($name === '' || $org === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash(__('Please enter your name, association and a valid email.'), 'error'); redirect('diaspora'); }
    [$clanId, $clanOther] = read_clan_input();
    insert('applications', ['type' => 'diaspora', 'name' => $name, 'email' => $email, 'phone' => post('phone') ?: null, 'organization' => $org, 'country' => post('country') ?: null,
        'clan_id' => $clanId, 'clan_other' => $clanOther, 'details' => mb_substr(post('details'), 0, 8000)]);
    clear_old();
    flash(__('Thank you — your association has been registered. We will be in touch.'));
    redirect('diaspora');
}
page_start(__('Diaspora Partnership'), ['hero' => ['eyebrow' => __('Lango across the world'), 'title' => __('Diaspora partnership portal'), 'sub' => __('Lango associations abroad: register, connect with the institution and contribute to home.'), 'crumbs' => [[__('Programs'), 'programs'], [__('Diaspora'), null]], 'image' => 'assets/img/photos/hero-aerial.jpg']]);
?>
<section class="section"><div class="container article">
  <div>
    <div class="prose"><h2><?php _e('Home is one click away'); ?></h2><p><?php _e('Wherever you live, the Lango Cultural Institution wants to work with you. Register your association to receive institutional updates, join cultural events online, sponsor programs and help the next generation learn Leb Lango.'); ?></p>
      <ul><li><?php _e('Watch festivals and royal events live'); ?></li><li><?php _e('Sponsor a clan archive or a youth tech camp'); ?></li><li><?php _e('Take 360° heritage tours and Leb Lango lessons'); ?></li><li><?php _e('Mentor young Lango professionals'); ?></li></ul></div>
    <form class="formcard form" method="post" style="margin-top:30px"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
      <h3 style="margin:0"><?php _e('Register your association'); ?></h3>
      <div class="fgrid"><div class="field"><label><?php _e('Association name'); ?> *</label><input type="text" name="organization" required value="<?= e(old('organization')) ?>"></div><div class="field"><label><?php _e('Country / city'); ?></label><input type="text" name="country" value="<?= e(old('country')) ?>"></div>
        <div class="field"><label><?php _e('Contact person'); ?> *</label><input type="text" name="name" required value="<?= e(old('name')) ?>"></div><div class="field"><label><?php _e('Email'); ?> *</label><input type="email" name="email" required value="<?= e(old('email')) ?>"></div>
        <div class="field"><label><?php _e('Phone / WhatsApp'); ?></label><input type="tel" name="phone"></div>
        <div class="field"><label for="dc"><?php _e('Your clan'); ?></label><?= clan_select('dc', user()['clan_id'] ?? old('clan_id') ?: null, false, (string)old('clan_other')) ?></div></div>
      <div class="field"><label><?php _e('About your association and how you would like to help'); ?></label><textarea name="details"><?= e(old('details')) ?></textarea></div>
      <div><button class="btn btn--primary"><?= icon('globe') ?> <?php _e('Register'); ?></button></div>
    </form>
  </div>
  <aside class="aside"><div class="box box--dark"><h4><?php _e('Give from abroad'); ?></h4><p><?php _e('Donate in USD with Visa, Mastercard or PayPal, or monthly by standing pledge.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('donate')) ?>"><?php _e('Donate'); ?></a></div></aside>
</div></section>
<?php page_end(); ?>
