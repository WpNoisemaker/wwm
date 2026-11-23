<article class="page wrap prose">
	<h1 class="prose__h"><?= t('about.title.about-us', 'About us') ?></h1>
	<?php $aboutPic = timg('about.image.top', 'photos/couple-dance.jpg'); ?>
	<p class="lede"><?= t('about.intro.nigerian-and-african-weddings-are', 'Wedding Websites & More is a company in Abuja. We make wedding websites for African couples, in Nigeria and abroad.') ?></p>
	<?php if ($aboutPic !== ''): ?><div class="about__pic"><?= sq($aboutPic, 'Wedding Websites & More', 'sq--wide', true) ?></div><?php endif; ?>
	<p><?= tl('about.text.wedding-websites-more-started-with', 'Guests ask the same questions before every wedding: where is it, what do I wear, can I bring someone, how do I send a gift from abroad. A wedding page answers all of them on one link.') ?></p>
	<p><?= tl('about.text.one-link-answers-all-of', 'The couple shares the link once. Guests open it on their phone, with no app and no account.') ?></p>
	<h2><?= t('about.heading.what-we-actually-do', 'What we do') ?></h2>
	<p><?= tl('about.text.every-couple-gets-a-page', 'Every couple gets a page with their events, venues and dress codes, RSVP, a cash and gift registry, and a wall where guests leave wishes. Paid plans add entry passes with QR codes, guest photos, spraying, a livestream and printed table cards. On Premium and High Society, our team can also set up the page and keep it updated, if the couple wants that.') ?></p>
	<p><?= tl('about.text.around-that-sits-the-rest', 'We also run the Buzz Chart, which ranks wedding pages by guest activity; a list of vendors with reviews from couples; a marketplace for stays, rides, honeymoons and aso-ebi; and the Anonymous Community, where people ask questions about relationships, weddings, marriage and family without giving their name.') ?></p>
	<h2><?= t('about.heading.where-we-are', 'Where we are') ?></h2>
	<p><?= tl('about.text.lagos-abuja-kano-enugu-ibadan', 'We are based in Abuja. Couples use the site across Nigeria, Ghana, Kenya, South Africa, the UK, the US, Canada and beyond.') ?></p>
	<p><a class="pill" href="<?= e(App::url('/join')) ?>"><?= t('about.button.make-your-page-free', 'Create your page') ?></a></p>
</article>
