<?php
declare(strict_types=1);
page_start(__('AI Cultural Assistant'), ['hero' => ['eyebrow' => __('Ask about Lango'), 'title' => __('Cultural Assistant'), 'sub' => __('Questions about Lango history, clans, traditions or language — answered only from reviewed LCI content, with sources.'), 'crumbs' => [[__('Library'), 'library'], [__('Assistant'), null]], 'image' => 'assets/img/photos/hero-site.jpg']]);
?>
<section class="section"><div class="container">
  <div class="chat" id="chat">
    <div class="chat__log" id="chatlog" aria-live="polite">
      <div class="msg bot"><?php _e('Hello! I can answer questions about Lango history, clans, traditions, the dictionary and more. I only use reviewed content from the LCI archive, so if I do not know something I will say so.'); ?>
        <div class="sugg" style="margin-top:12px"><?php foreach (['Who is the clan leader of Otengoro Olang?', 'Tell me about Amolatar', 'What is Lira City known for?', 'Who was Milton Obote?', 'What is an Awitong?', 'What does yoo mean?'] as $s): ?><button type="button"><?= e(__($s)) ?></button><?php endforeach; ?></div></div>
    </div>
    <form class="chat__in" id="chatform"><input type="text" id="chatq" placeholder="<?= e(__('Ask a question…')) ?>" autocomplete="off" maxlength="300" aria-label="<?= e(__('Your question')) ?>"><button class="btn btn--primary"><?php _e('Ask'); ?></button></form>
  </div>
  <p style="text-align:center;color:var(--muted);font-size:.85rem;margin-top:16px"><?php _e('Every answer can be flagged for review by our cultural experts. Leb Lango voice input and a hosted language model can be added later using the same archive.'); ?></p>
</div></section>
<?php page_end(); ?>
