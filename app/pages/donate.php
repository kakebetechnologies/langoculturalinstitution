<?php
declare(strict_types=1);

$programs = all("SELECT id, title FROM programs WHERE status='published' AND accepts_donations=1 ORDER BY sort, id");
$sub = $seg[1] ?? '';
$methods = ['mtn_momo' => 'MTN Mobile Money', 'airtel_money' => 'Airtel Money', 'visa' => 'Visa', 'mastercard' => 'Mastercard', 'paypal' => 'PayPal', 'bank' => 'Bank transfer'];

if ($sub === 'thanks') {
    $d = one('SELECT * FROM donations WHERE reference=?', [strtoupper(get('ref'))]);
    if (!$d) redirect('donate');
    page_start(__('Thank you'), ['hero' => ['eyebrow' => __('Pledge received'), 'title' => __('Thank you for your generosity'), 'crumbs' => [[__('Donate'), 'donate'], [__('Thank you'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
    ?>
    <section class="section"><div class="container" style="max-width:680px"><div class="formcard" style="border-top-color:var(--leaf)">
      <h2><?= e(money((float)$d['amount'], $d['currency'])) ?> <?= $d['recurring'] !== 'once' ? '· ' . e(__(ucfirst($d['recurring']))) : '' ?></h2>
      <p><?php _e('Your pledge has been recorded. Online payment is being connected; until then, the LCI finance office will contact you with payment instructions for your chosen method:'); ?> <b><?= e($methods[$d['method']] ?? $d['method']) ?></b>.</p>
      <p><?php _e('Your reference'); ?>: <b style="font:700 1.6rem var(--fd);letter-spacing:.12em;color:var(--clay)"><?= e($d['reference']) ?></b></p>
      <p style="color:var(--muted);font-size:.92rem"><?php _e('Keep this reference. Quote it if you pay by mobile money or bank transfer.'); ?></p>
      <a class="btn btn--primary" href="<?= e(url('transparency')) ?>"><?php _e('See how funds are used'); ?></a>
    </div></div></section>
    <?php page_end(); return;
}

if (is_post()) {
    csrf_check();
    keep_old($_POST);
    if (!empty($_POST['website'])) redirect('donate');
    if (!throttle('donate', 6)) { flash(__('Too many attempts. Please wait a few minutes.'), 'error'); redirect('donate'); }
    $amount = (float)str_replace(',', '', post('amount')); $cur = post('currency') === 'USD' ? 'USD' : 'UGX';
    $name = post('donor_name'); $email = post('email');
    $min = $cur === 'UGX' ? 1000 : 1;
    $err = null;
    if ($amount < $min) $err = sprintf(__('Please enter an amount of at least %s.'), money($min, $cur));
    elseif ($name === '' && post('anonymous') !== '1') $err = __('Please enter your name, or choose to give anonymously.');
    elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err = __('That email address does not look right.');
    elseif (post('email') === '' && post('phone') === '') $err = __('Please give an email or phone number so we can send payment instructions.');
    if ($err) { flash($err, 'error'); redirect('donate'); }
    $ref = 'LCI' . new_code(7);
    insert('donations', [
        'reference' => $ref, 'donor_name' => $name !== '' ? $name : 'Anonymous', 'email' => $email ?: null, 'phone' => post('phone') ?: null, 'country' => post('country') ?: null,
        'amount' => $amount, 'currency' => $cur, 'method' => isset($methods[post('method')]) ? post('method') : 'mtn_momo',
        'program_id' => (int)post('program_id') ?: null, 'recurring' => in_array(post('recurring'), ['monthly', 'yearly'], true) ? post('recurring') : 'once',
        'anonymous' => post('anonymous') === '1' ? 1 : 0, 'message' => mb_substr(post('message'), 0, 500) ?: null,
    ]);
    clear_old();
    redirect('donate/thanks?ref=' . $ref);
}

$preProg = (int)get('program');
page_start(__('Donate'), ['hero' => ['eyebrow' => __('Foundation support'), 'title' => __('Give to Lango heritage'), 'sub' => setting('donation_note'), 'crumbs' => [[__('Programs'), 'programs'], [__('Donate'), null]], 'image' => 'assets/img/photos/hero-aerial.jpg']]);
?>
<section class="section"><div class="container article" style="grid-template-columns:minmax(0,1fr) 360px">
  <form class="formcard form" method="post">
    <?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
    <h2 style="font-size:1.6rem;margin:0"><?php _e('Make a donation'); ?></h2>
    <div class="field"><label><?php _e('Currency'); ?></label><select name="currency"><option value="UGX">UGX — Ugandan shilling</option><option value="USD" <?= old('currency') === 'USD' ? 'selected' : '' ?>>USD — US dollar</option></select></div>
    <div class="donate-amts"><?php foreach ([20000, 50000, 100000, 250000] as $a): ?><label><input type="radio" name="preset" value="<?= $a ?>"><span><?= number_format($a) ?></span></label><?php endforeach; ?></div>
    <div class="field"><label for="amount"><?php _e('Amount'); ?> *</label><input id="amount" type="number" name="amount" min="1" step="any" required value="<?= e(old('amount')) ?>"></div>
    <div class="fgrid"><div class="field"><label><?php _e('Give'); ?></label><select name="recurring"><option value="once"><?php _e('Once'); ?></option><option value="monthly"><?php _e('Every month'); ?></option><option value="yearly"><?php _e('Every year'); ?></option></select></div>
      <div class="field"><label><?php _e('Fund a specific program'); ?></label><select name="program_id"><option value=""><?php _e('Where it is needed most'); ?></option><?php foreach ($programs as $p): ?><option value="<?= (int)$p['id'] ?>" <?= $preProg === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['title']) ?></option><?php endforeach; ?></select></div></div>
    <div class="field"><label><?php _e('Payment method'); ?></label><div class="method"><?php foreach ($methods as $k => $l): ?><label><input type="radio" name="method" value="<?= e($k) ?>" <?= $k === 'mtn_momo' ? 'checked' : '' ?>><span><?= e($l) ?></span></label><?php endforeach; ?></div></div>
    <div class="fgrid"><div class="field"><label><?php _e('Your name'); ?></label><input type="text" name="donor_name" value="<?= e(user()['name'] ?? old('donor_name')) ?>"></div><div class="field"><label><?php _e('Country'); ?></label><input type="text" name="country" value="<?= e(old('country', 'Uganda')) ?>"></div>
      <div class="field"><label><?php _e('Email'); ?></label><input type="email" name="email" value="<?= e(user()['email'] ?? old('email')) ?>"></div><div class="field"><label><?php _e('Phone (mobile money)'); ?></label><input type="tel" name="phone" value="<?= e(old('phone')) ?>"></div></div>
    <div class="field"><label><?php _e('Message (optional)'); ?></label><textarea name="message" style="min-height:80px" maxlength="500"></textarea></div>
    <label class="check"><input type="checkbox" name="anonymous" value="1"> <span><?php _e('Give anonymously — do not show my name in donor recognition'); ?></span></label>
    <button class="btn btn--primary btn--block"><?= icon('heart') ?> <?php _e('Submit my pledge'); ?></button>
    <small style="color:var(--muted)"><?php _e('Online card and mobile-money processing is being connected. Your pledge is recorded securely and our finance office will confirm payment details with you.'); ?></small>
  </form>
  <aside class="aside">
    <div class="box box--dark"><h4><?php _e('Where your gift goes'); ?></h4><p><?php _e('Clan archives, the Lango dictionary, youth tech camps, scholarships and tree planting across the nine districts.'); ?></p><a class="btn btn--gold btn--sm" href="<?= e(url('transparency')) ?>"><?php _e('Transparency dashboard'); ?></a></div>
    <div class="box"><h4><?php _e('Other ways to help'); ?></h4><p style="line-height:2"><a href="<?= e(url('apply/volunteer')) ?>"><?php _e('Volunteer your skills'); ?></a><br><a href="<?= e(url('diaspora')) ?>"><?php _e('Diaspora partnership'); ?></a><br><a href="<?= e(url('contribute')) ?>"><?php _e('Contribute to the archive'); ?></a></p></div>
  </aside>
</div></section>
<?php page_end(); ?>
