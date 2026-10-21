<?php
declare(strict_types=1);

$forms = [
    'scholarship' => ['Scholarship application', 'Apply for LCI education support', 'Tell us about your studies and why you need support.', 'assets/img/photos/hero-field.jpg'],
    'grant'       => ['Research grant application', 'Grants for students and researchers of Lango heritage', 'Describe your research topic, supervisor and how it benefits Lango heritage.', 'assets/img/photos/hero-site.jpg'],
    'volunteer'   => ['Volunteer with LCI', 'Tech camps, innovation challenges, internships and field work', 'Tell us your skills, availability and what you would like to help with.', 'assets/img/photos/hero-aerial.jpg'],
    'partner'     => ['Partner with LCI', 'Organisations that support Lango heritage', 'Describe your organisation and how you would like to partner.', 'assets/img/photos/hero-hill.jpg'],
    'clan'        => ['Register your clan', 'Join the digital clan register', 'Tell us the clan name, the Awitong (clan leader), the district and your role in the clan.', 'assets/img/photos/hero-site.jpg'],
];
$type = $seg[1] ?? '';
if (!isset($forms[$type])) {
    page_start(__('Get involved'), ['hero' => ['title' => __('Get involved'), 'crumbs' => [[__('Programs'), 'programs'], [__('Apply'), null]]]]);
    echo '<section class="section"><div class="container"><div class="grid g3">';
    foreach ($forms as $k => [$t, $s]) echo '<a class="tile" href="' . e(url('apply/' . $k)) . '"><span class="ico">' . icon('arrow') . '</span><h3>' . e(__($t)) . '</h3><p>' . e(__($s)) . '</p></a>';
    echo '</div></div></section>';
    page_end(); return;
}
[$title, $sub, $hint, $img] = $forms[$type];

if (is_post()) {
    csrf_check();
    keep_old($_POST);
    if (!empty($_POST['website'])) redirect('apply/' . $type);
    if (!throttle('apply', 5)) { flash(__('Too many submissions. Please wait a few minutes.'), 'error'); redirect('apply/' . $type); }
    $name = post('name'); $email = post('email'); $details = post('details');
    $askClan = in_array($type, ['scholarship', 'grant', 'volunteer'], true);
    [$clanId, $clanOther] = $askClan ? read_clan_input() : [null, null];
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($details) < 20) {
        flash(__('Please enter your name, a valid email and a few sentences of detail (at least 20 characters).'), 'error'); redirect('apply/' . $type);
    }
    if ($askClan && (post('clan_id') === '' || (post('clan_id') === 'other' && !$clanOther))) { flash(__('Please choose your clan, or “My clan is not listed” and type its name.'), 'error'); redirect('apply/' . $type); }
    insert('applications', ['type' => $type, 'name' => mb_substr($name, 0, 150), 'email' => $email, 'phone' => post('phone') ?: null, 'organization' => post('organization') ?: null, 'country' => post('country') ?: null,
        'clan_id' => $clanId, 'clan_other' => $clanOther, 'details' => mb_substr($details, 0, 8000)]);
    clear_old();
    flash(__('Thank you — your application has been received. We will contact you by email.'));
    redirect('apply/' . $type);
}

page_start(__($title), ['hero' => ['eyebrow' => __('Get involved'), 'title' => __($title), 'sub' => __($sub), 'crumbs' => [[__('Programs'), 'programs'], [__($title), null]], 'image' => $img]]);
?>
<section class="section"><div class="container" style="max-width:780px">
  <form class="formcard form" method="post"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
    <div class="fgrid"><div class="field"><label><?php _e('Full name'); ?> *</label><input type="text" name="name" required value="<?= e(user()['name'] ?? old('name')) ?>"></div><div class="field"><label><?php _e('Email'); ?> *</label><input type="email" name="email" required value="<?= e(user()['email'] ?? old('email')) ?>"></div>
      <div class="field"><label><?php _e('Phone'); ?></label><input type="tel" name="phone" value="<?= e(old('phone')) ?>"></div><div class="field"><label><?= $type === 'partner' ? e(__('Organisation')) : e(__('Institution / organisation (if any)')) ?></label><input type="text" name="organization" value="<?= e(old('organization')) ?>"></div>
      <div class="field"><label><?php _e('Country'); ?></label><input type="text" name="country" value="<?= e(old('country', 'Uganda')) ?>"></div>
      <?php if (in_array($type, ['scholarship', 'grant', 'volunteer'], true)): ?><div class="field"><label for="ac"><?php _e('Your clan'); ?> *</label><?= clan_select('ac', user()['clan_id'] ?? old('clan_id') ?: null, true, (string)old('clan_other', user()['clan_other'] ?? '')) ?></div><?php endif; ?></div>
    <div class="field"><label><?php _e('Details'); ?> *</label><textarea name="details" required minlength="20" style="min-height:190px" placeholder="<?= e(__($hint)) ?>"><?= e(old('details')) ?></textarea><small><?= e(__($hint)) ?></small></div>
    <div><button class="btn btn--primary"><?php _e('Submit application'); ?></button></div>
  </form>
</div></section>
<?php page_end(); ?>
