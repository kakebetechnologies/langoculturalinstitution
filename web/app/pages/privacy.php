<?php
declare(strict_types=1);
page_start(__('Privacy'), ['hero' => ['title' => __('Privacy policy'), 'crumbs' => [[__('Privacy'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section"><div class="container" style="max-width:820px"><div class="prose">
  <p><em><?php _e('Last updated: 1 November 2026.'); ?></em></p>
  <p><?php _e('Lango is a free app and website about Lango language, clans, heritage and community, run by Kakebe Technologies Limited, Uganda. This page says what we collect, why, who sees it and how you can delete it.'); ?></p>

  <h2><?php _e('What we collect'); ?></h2>
  <ul>
    <li><?php _e('Account details: your name, email, and (if you add them) phone, country and clan. If you sign in with Google, we receive your name and email from Google. Passwords are stored hashed.'); ?></li>
    <li><?php _e('What you share: words, stories, photos, audio recordings and the place you tag (only if you choose to add one), clan group messages, quiz answers and contributions you send.'); ?></li>
    <li><?php _e('How you use the app: points, badges, streaks, quiz scores and the days you opened it.'); ?></li>
    <li><?php _e('Notifications: if you allow them, a notification address for your phone so we can send event and reply alerts.'); ?></li>
    <li><?php _e('On the website only: donation pledges, newsletter sign-ups and contact messages.'); ?></li>
  </ul>
  <p><?php _e('We do not sell your data, we do not show advertising and we do not track you across other apps or websites.'); ?></p>

  <h2><?php _e('Why we use it'); ?></h2>
  <p><?php _e('To run your account, review what you contribute, show your progress, send the alerts you allow, answer you, keep the service safe, and report in general terms on how Lango is used. A contributor’s first name and clan are shown beside a word only if you tick the credit box. Donor names are shown only if you choose to be recognised.'); ?></p>

  <h2><?php _e('Camera, microphone, photos and location'); ?></h2>
  <p><?php _e('The app asks for these only when you use them: the camera to photograph something to contribute, the microphone to record a story or how a word is said, your photos to attach one, and your location to tag where you saw something. You can say no and still use the rest of the app. Uploaded photos are re-encoded to remove hidden metadata. Moderators can hide the exact place of sensitive sites or endangered species before anything is published.'); ?></p>

  <h2><?php _e('Who else handles your data'); ?></h2>
  <ul>
    <li><?php _e('Our web host, which stores the database and uploads in a secured account.'); ?></li>
    <li><?php _e('Google, if you choose Sign in with Google, under Google’s own privacy policy.'); ?></li>
    <li><?php _e('Expo, which delivers push notifications to your phone.'); ?></li>
    <li><?php _e('Language experts and clan leaders we invite to review contributions, who see the contribution and the contributor’s name only to check it.'); ?></li>
  </ul>
  <p><?php _e('Links to other sites, such as the Lango Bible on bible.com, open in their own pages under their own policies.'); ?></p>

  <h2><?php _e('Keeping it and deleting it'); ?></h2>
  <p><?php _e('We keep your account while you use it. You can delete it any time, in the app (Me, Edit profile, Delete account) or on this website at'); ?> <a href="<?= e(url('account/delete')) ?>"><?= e(url('account/delete')) ?></a>. <?php _e('Deleting removes your personal details, points, streaks, quiz results, messages and sign-in tokens. Contributions already published in the archive stay, without your name, because they belong to the archive; ask us if you want one taken down.'); ?></p>
  <p><?php _e('To have some of your data deleted without deleting your account, or if you cannot sign in, use'); ?> <a href="<?= e(url('account/data-request')) ?>"><?= e(url('account/data-request')) ?></a>.</p>

  <h2><?php _e('Your rights'); ?></h2>
  <p><?php _e('You can see and correct your details in the app (Me, Edit profile), and ask us to export or delete your data. We follow the principles of Uganda’s Data Protection and Privacy Act, 2019, and general international data-protection practice.'); ?></p>

  <h2><?php _e('Children'); ?></h2>
  <p><?php _e('Lango is for a general audience and is not aimed at children under 13. If you think a child has given us personal data, tell us and we will delete it.'); ?></p>

  <h2><?php _e('Security'); ?></h2>
  <p><?php _e('Connections use HTTPS, passwords are hashed, the app keeps a revocable sign-in token in your phone’s secure storage, uploads are checked, and the database is backed up regularly.'); ?></p>

  <h2><?php _e('Changes'); ?></h2>
  <p><?php _e('If we change this policy in a way that matters, we will say so in the app or on the website, and update the date above.'); ?></p>

  <h2><?php _e('Contact'); ?></h2>
  <p><?php _e('Questions or requests about your data:'); ?> <a href="<?= e(url('contact')) ?>"><?php _e('Contact us'); ?></a>.</p>
</div></div></section>
<?php page_end(); ?>
