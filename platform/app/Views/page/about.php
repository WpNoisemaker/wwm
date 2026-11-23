<article class="page wrap prose">
	<h1 class="prose__h"><?= t('about.title.about-us', 'About us') ?></h1>
	<?php $aboutPic = timg('about.image.top', 'photos/couple-dance.jpg'); ?>
	<p class="lede"><?= t('about.intro.nigerian-and-african-weddings-are', 'Nigerian and African weddings are among the biggest celebrations on earth. The websites built for them usually are not.') ?></p>
	<?php if ($aboutPic !== ''): ?><div class="about__pic"><?= sq($aboutPic, 'Wedding Websites & More', 'sq--wide', true) ?></div><?php endif; ?>
	<p><?= tl('about.text.wedding-websites-more-started-with', 'Wedding Websites & More started with a simple observation: a couple would spend a year planning three ceremonies across two cities, then send guests a blurry invitation on WhatsApp and field the same five questions four hundred times. Where is it. What do I wear. Can I bring someone. How do I send a gift from abroad. Is there parking.') ?></p>
	<p><?= tl('about.text.one-link-answers-all-of', 'One link answers all of it, and keeps answering at two in the morning while the couple sleeps.') ?></p>
	<h2><?= t('about.heading.what-we-actually-do', 'What we actually do') ?></h2>
	<p><?= tl('about.text.every-couple-gets-a-page', 'Every couple gets a page: the programs with their venues and dress codes, RSVPs, a cash and gift registry that family abroad can reach, a wall where guests leave notes, and a gallery of what everyone shot on the day. On the paid tiers there are entry passes with QR codes, spraying from the seat, a livestream and printed table cards.') ?></p>
	<p><?= tl('about.text.around-that-sits-the-rest', 'Around that sits the rest of it — the Buzz Chart that ranks pages by what guests actually did, a directory of vendors reviewed by couples who booked them, a marketplace for stays, rides, honeymoons and aso-ebi, and an anonymous community for the parts of getting married nobody posts about.') ?></p>
	<h2><?= t('about.heading.where-we-are', 'Where we are') ?></h2>
	<p><?= tl('about.text.lagos-abuja-kano-enugu-ibadan', 'Lagos, Abuja, Kano, Enugu, Ibadan, Port Harcourt — and Accra, Nairobi, Johannesburg, London, Houston, Toronto. Wherever the family is.') ?></p>
	<p><a class="pill" href="<?= e(App::url('/join')) ?>"><?= t('about.button.make-your-page-free', 'Make your page free') ?></a></p>
</article>
