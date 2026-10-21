<?php
declare(strict_types=1);
page_start(__('Privacy'), ['hero' => ['title' => __('Privacy policy'), 'crumbs' => [[__('Privacy'), null]], 'image' => 'assets/img/photos/hero-hill.jpg']]);
?>
<section class="section"><div class="container" style="max-width:820px"><div class="prose">
  <p class="alert alert-info"><?php _e('Draft for legal review before launch.'); ?></p>
  <h2><?php _e('What we collect'); ?></h2><p><?php _e('Only what you give us: names and contact details on forms, content you contribute (photos, recordings, text, locations), donation pledges and newsletter sign-ups. We do not sell personal data.'); ?></p>
  <h2><?php _e('How we use it'); ?></h2><p><?php _e('To run the Lango archive, review contributions, reply to you, administer events and programs, and report on our impact. Donor names are shown publicly only if you choose to be recognised.'); ?></p>
  <h2><?php _e('Photos and location'); ?></h2><p><?php _e('Uploaded photos are re-encoded to remove hidden metadata. GPS coordinates are stored only when you add them yourself, and exact locations of sensitive sites or endangered species can be hidden by our moderators before publication.'); ?></p>
  <h2><?php _e('Your rights'); ?></h2><p><?php _e('You can ask to see, correct or delete your data at any time by contacting us. We aim to follow international data-protection standards, including GDPR principles.'); ?></p>
  <h2><?php _e('Security'); ?></h2><p><?php _e('Passwords are stored hashed, forms are protected against forgery, uploads are validated, and the database is backed up regularly.'); ?></p>
  <h2><?php _e('Contact'); ?></h2><p><a href="<?= e(url('contact')) ?>"><?php _e('Contact us'); ?></a></p>
</div></div></section>
<?php page_end(); ?>
