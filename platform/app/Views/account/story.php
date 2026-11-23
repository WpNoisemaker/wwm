<?php
/**
 * "Our story" as diary cards: each one a moment, in the couple's own words, with a photo if they
 * like. They write as many as they want, up to eight, a few lines each, and put them in order.
 *
 * @var array $w @var array $cards @var array $signs
 */
$max = AccountController::DIARY_MAX;
$words = AccountController::DIARY_WORDS;
$card = static function (int $i, array $c) use ($signs, $words): string {
	[$when, $title, $text, $sign, $img] = array_pad($c, 5, '');
	ob_start(); ?>
	<fieldset class="diary-edit glass glass--plain glass--tight" data-diary-card>
		<div class="diary-edit__head">
			<span class="diary-edit__n" data-diary-n>Card <?= $i + 1 ?></span>
			<span class="diary-edit__acts">
				<button class="cbtn cbtn--sm" type="button" data-diary-up aria-label="Move this card up"><?= icon('chev-up') ?></button>
				<button class="cbtn cbtn--sm" type="button" data-diary-down aria-label="Move this card down"><?= icon('chev-down') ?></button>
				<button class="cbtn cbtn--sm" type="button" data-diary-del aria-label="Remove this card"><?= icon('trash') ?></button>
			</span>
		</div>
		<div class="form-grid form-grid--2">
			<label class="field"><span>When</span><input class="input" name="cards[<?= $i ?>][when]" value="<?= e($when) ?>" maxlength="60" placeholder="Summer 2021, or The very beginning"></label>
			<label class="field"><span>A title</span><input class="input" name="cards[<?= $i ?>][title]" value="<?= e($title) ?>" maxlength="80" placeholder="The day we met"></label>
			<label class="field span-2"><span>What happened</span>
				<textarea class="input" name="cards[<?= $i ?>][text]" rows="4" data-words="<?= $words ?>" placeholder="A few lines, in your own words."><?= e($text) ?></textarea>
				<span class="help diary-edit__count" data-words-left><?= $words ?> words at most</span>
			</label>
			<label class="field"><span>Signed by</span>
				<select class="input" name="cards[<?= $i ?>][sign]">
					<option value="">No signature</option>
					<?php foreach ($signs as $s): ?><option<?= $sign === $s ? ' selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
				</select>
			</label>
			<div class="field">
				<span>A photo, if you like</span>
				<?php if ($img !== ''): ?>
					<div class="diary-edit__pic"><?= sq($img, $title, 'diary-edit__thumb') ?><label class="check"><input type="checkbox" name="cards[<?= $i ?>][img_off]" value="1"> Remove</label></div>
					<input type="hidden" name="cards[<?= $i ?>][img]" value="<?= e($img) ?>">
				<?php endif; ?>
				<label class="photo-pick"><input type="file" name="photo[<?= $i ?>]" accept="image/jpeg,image/png,image/webp" data-photo-input><?= icon('camera') ?><span data-photo-name><?= $img !== '' ? 'Choose a different photo' : 'Add a photo' ?></span></label>
			</div>
		</div>
	</fieldset>
	<?php return (string) ob_get_clean();
};
?>
<?= View::partial('account/_head', ['h' => 'Our story', 'w' => $w, 'sub' => 'Your story as diary cards, in your own words', 'now' => 'story']) ?>
	<form method="post" class="form acct-form" enctype="multipart/form-data" data-diary>
		<?= Guard::field() ?>
		<p class="note"><?= icon('book') ?><span>Each card is one moment: when it was, a title, up to <?= $words ?> words and a photo if you like. You can add up to <?= $max ?> cards. Once you have cards, they replace the single story on your page.</span></p>
		<div class="acct-list" data-diary-list data-max="<?= $max ?>">
			<?php foreach ($cards as $i => $c): ?><?= $card($i, (array) $c) ?><?php endforeach; ?>
			<?php if (count($cards) < $max): ?><?= $card(count($cards), []) ?><?php endif; ?>
		</div>
		<template data-diary-blank><?= $card(99, []) ?></template>
		<button class="pill pill--glass pill--block" type="button" data-diary-add hidden><?= icon('plus') ?>Add another card</button>
		<div class="form-actions"><button class="pill" type="submit">Save the story</button><a class="pill pill--glass" href="<?= e(App::url('/w/' . $w['slug'] . '#story')) ?>">View it on the page</a></div>
	</form>
</div>
