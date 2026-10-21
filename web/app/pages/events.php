<?php
declare(strict_types=1);

/* /events/{slug}[/ticket/{code}] */
if (!empty($seg[1])) {
    $ev = one("SELECT * FROM events WHERE slug=? AND status='published'", [$seg[1]]);
    if (!$ev) not_found();
    $ts = strtotime($ev['starts_at']);
    $past = $ts < strtotime('today');
    $taken = (int)val('SELECT COALESCE(SUM(tickets),0) FROM event_registrations WHERE event_id=?', [$ev['id']]);
    $left = $ev['capacity'] ? max(0, (int)$ev['capacity'] - $taken) : null;

    /* ticket view */
    if (($seg[2] ?? '') === 'ticket' && !empty($seg[3])) {
        $r = one('SELECT * FROM event_registrations WHERE code=? AND event_id=?', [$seg[3], $ev['id']]);
        if (!$r) not_found();
        page_start(__('Your ticket'), ['hero' => ['title' => __('You are registered!'), 'sub' => t($ev, 'title'), 'crumbs' => [[__('Events'), 'events'], [t($ev, 'title'), 'events/' . $ev['slug']], [__('Ticket'), null]], 'image' => $ev['image']]]);
        ?>
        <section class="section"><div class="container" style="max-width:640px">
          <div class="formcard" style="text-align:center;border-top-color:var(--leaf)">
            <span style="display:grid;place-items:center;width:72px;height:72px;border-radius:50%;background:#e3f7f0;color:var(--teal);margin:0 auto 12px"><?= icon('check') ?></span>
            <h2 style="margin-bottom:4px"><?= e(t($ev, 'title')) ?></h2>
            <p><?= e(fmt_date($ev['starts_at'], 'l, j F Y · g:i A')) ?><br><?= e((string)$ev['location']) ?></p>
            <hr style="border:0;border-top:2px dashed var(--line);margin:22px 0">
            <p style="margin:0"><?= e($r['name']) ?> · <?= (int)$r['tickets'] ?> <?php _e('ticket(s)'); ?></p>
            <p style="font:700 2.2rem var(--fd);letter-spacing:.2em;color:var(--clay);margin:8px 0"><?= e($r['code']) ?></p>
            <p style="color:var(--muted);font-size:.9rem"><?php _e('Show this code at the entrance. Take a screenshot or print this page.'); ?></p>
            <button class="btn btn--dark" onclick="window.print()"><?php _e('Print ticket'); ?></button>
          </div></div></section>
        <?php page_end(); return;
    }

    /* registration */
    if (is_post()) {
        csrf_check();
        if (!empty($_POST['website'])) redirect('events');
        if (!throttle('evreg', 5)) { flash(__('Too many attempts. Please wait a few minutes.'), 'error'); redirect('events/' . $ev['slug']); }
        $name = post('name'); $email = post('email'); $phone = post('phone'); $tix = max(1, min(10, (int)post('tickets', '1')));
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { flash(__('Please enter your name and a valid email.'), 'error'); redirect('events/' . $ev['slug']); }
        if ($past || !$ev['registration_open']) { flash(__('Registration is closed for this event.'), 'error'); redirect('events/' . $ev['slug']); }
        if ($left !== null && $tix > $left) { flash(__('Sorry, not enough places are left.'), 'error'); redirect('events/' . $ev['slug']); }
        [$clanId, $clanOther] = read_clan_input();
        if (post('clan_id') === '' || (post('clan_id') === 'other' && !$clanOther)) { flash(__('Please choose your clan, or “My clan is not listed” and type its name.'), 'error'); redirect('events/' . $ev['slug']); }
        $code = new_code(8);
        insert('event_registrations', ['event_id' => $ev['id'], 'name' => $name, 'email' => $email, 'phone' => $phone ?: null, 'tickets' => $tix, 'clan_id' => $clanId, 'clan_other' => $clanOther, 'code' => $code]);
        redirect('events/' . $ev['slug'] . '/ticket/' . $code);
    }

    page_start(t($ev, 'title'), ['image' => $ev['image'], 'desc' => $ev['summary'], 'hero' => ['eyebrow' => __('Event'), 'title' => t($ev, 'title'), 'sub' => fmt_date($ev['starts_at'], 'l, j F Y') . ' · ' . $ev['location'], 'crumbs' => [[__('Events'), 'events'], [t($ev, 'title'), null]], 'image' => $ev['image']]]);
    ?>
    <section class="section"><div class="container article">
      <div class="prose"><?= rich(t($ev, 'body')) ?></div>
      <aside class="aside">
        <div class="box"><h4><?php _e('When & where'); ?></h4><dl class="kv"><dt><?php _e('Date'); ?></dt><dd><?= e(fmt_date($ev['starts_at'], 'j F Y')) ?></dd><dt><?php _e('Time'); ?></dt><dd><?= e(fmt_date($ev['starts_at'], 'g:i A')) ?></dd><dt><?php _e('Venue'); ?></dt><dd><?= e((string)$ev['location']) ?></dd><?php if ($left !== null): ?><dt><?php _e('Places left'); ?></dt><dd><?= $left ?></dd><?php endif; ?></dl></div>
        <?php if (!$past && $ev['registration_open'] && ($left === null || $left > 0)): ?>
        <form class="box form" method="post"><?= csrf_field() ?><input class="hp" type="text" name="website" tabindex="-1" autocomplete="off">
          <h4 style="margin:0"><?php _e('Register to attend'); ?></h4>
          <div class="field"><label><?php _e('Full name'); ?></label><input type="text" name="name" required value="<?= e(user()['name'] ?? '') ?>"></div>
          <div class="field"><label><?php _e('Email'); ?></label><input type="email" name="email" required value="<?= e(user()['email'] ?? '') ?>"></div>
          <div class="field"><label for="ec"><?php _e('Your clan'); ?></label><?= clan_select('ec', user()['clan_id'] ?? null, true, (string)(user()['clan_other'] ?? '')) ?></div>
          <div class="fgrid"><div class="field"><label><?php _e('Phone'); ?></label><input type="tel" name="phone"></div><div class="field"><label><?php _e('Tickets'); ?></label><input type="number" name="tickets" min="1" max="10" value="1"></div></div>
          <button class="btn btn--primary btn--block"><?php _e('Get my ticket'); ?></button>
        </form>
        <?php else: ?><div class="box"><p style="margin:0"><?= $past ? e(__('This event has ended.')) : e(__('Registration is closed.')) ?></p></div><?php endif; ?>
      </aside>
    </div></section>
    <?php page_end(); return;
}

/* list */
$view = get('view') === 'past' ? 'past' : 'upcoming';
$rows = $view === 'past'
    ? all("SELECT * FROM events WHERE status='published' AND starts_at<CURDATE() ORDER BY starts_at DESC LIMIT 60")
    : all("SELECT * FROM events WHERE status='published' AND starts_at>=CURDATE() ORDER BY starts_at LIMIT 60");
page_start(__('Events'), ['hero' => ['eyebrow' => __('Events & programs'), 'title' => __('Festivals, gatherings & ceremonies'), 'sub' => __('Register online, receive your ticket, and join the Lango community.'), 'crumbs' => [[__('News'), 'news'], [__('Events'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
?>
<section class="section"><div class="container">
  <div class="filters"><a class="pill <?= $view === 'upcoming' ? 'on' : '' ?>" href="<?= e(url('events')) ?>"><?php _e('Upcoming'); ?></a><a class="pill <?= $view === 'past' ? 'on' : '' ?>" href="<?= e(url('events', ['view' => 'past'])) ?>"><?php _e('Past events'); ?></a></div>
  <?php if ($rows): ?><div class="grid g3">
    <?php foreach ($rows as $ev): $ts = strtotime($ev['starts_at']); ?>
      <article class="card reveal"><a class="card__img" href="<?= e(url('events/' . $ev['slug'])) ?>"><img loading="lazy" src="<?= e(media_url($ev['image'])) ?>" alt=""></a>
        <div class="card__body" style="flex-direction:row;gap:16px">
          <div class="date"><b><?= e(date('M', $ts)) ?></b><span><?= e(date('j', $ts)) ?></span><small><?= e(date('Y', $ts)) ?></small></div>
          <div><h3 style="margin-top:0"><a href="<?= e(url('events/' . $ev['slug'])) ?>"><?= e(t($ev, 'title')) ?></a></h3><p><?= icon('pin') ?> <?= e((string)$ev['location']) ?></p><a class="card__more" href="<?= e(url('events/' . $ev['slug'])) ?>"><?= $view === 'past' ? e(__('View')) : e(__('Details & registration')) ?> <?= icon('arrow') ?></a></div>
        </div></article>
    <?php endforeach; ?></div>
  <?php else: echo empty_state($view === 'past' ? __('No past events yet') : __('No upcoming events right now'), __('New events are announced here and in the newsletter.'), 'calendar'); endif; ?>
</div></section>
<?php page_end(); ?>
