<?php
/**
 * The quiz editor: a column for each partner. Each question has up to four answers and a tick on
 * the right one. Empty rows are ignored, so the couple can fill in as many as they like.
 *
 * @var array $w @var array $quizzes @var array $players
 */
$names = ['a' => (string) $w['partner_a'], 'b' => (string) $w['partner_b']];
?>
<?= View::partial('account/_head', ['h' => 'Who knows you best?', 'w' => $w, 'sub' => 'A quiz about each of you', 'now' => 'quiz']) ?>
	<?php if (!Wedding::has($w, 'squad')): ?>
		<p class="note"><?= icon('lock') ?><span>The quiz shows on your page from Pro. Write it now and it appears when you upgrade. </span><?= up_tag('pro', 'The quiz') ?></p>
	<?php endif; ?>
	<p class="help qe-intro">Ask what family and friends should know about each of you: a nickname, a favourite food, a hometown, a fear, a football club. Leave how you met out of it; this is about who knows each of you best. Guests pick the one they know, answer, and land on that person&rsquo;s leaderboard.</p>
	<form method="post" class="qe-form">
		<?= Guard::field() ?>
		<div class="qe-cols">
			<?php foreach ($names as $side => $nm): $rows = $quizzes[$side]; $show = max(5, count($rows) + 1); ?>
				<div class="form glass glass--plain glass--tight acct-card">
					<div class="row row--between"><h2 class="h3">About <?= e($nm) ?></h2><span class="sub"><?= (int) $players[$side] ?> played</span></div>
					<?php for ($i = 0; $i < min(Quiz::MAX_QUESTIONS, $show); $i++):
						$r = $rows[$i] ?? null;
						$hint = str_replace('{name}', $nm, Quiz::PROMPTS[$i % count(Quiz::PROMPTS)]); ?>
						<fieldset class="qe">
							<legend class="sub">Question <?= $i + 1 ?></legend>
							<input class="input" name="<?= $side ?>[<?= $i ?>][q]" value="<?= e($r[0] ?? '') ?>" placeholder="<?= e($hint) ?>" aria-label="<?= e('Question ' . ($i + 1) . ' about ' . $nm) ?>" maxlength="160">
							<div class="qe__opts">
								<?php for ($o = 0; $o < 4; $o++): $right = $r ? (int) $r[2] === $o : $o === 0; ?>
									<label class="qe__o">
										<input type="radio" name="<?= $side ?>[<?= $i ?>][r]" value="<?= $o ?>"<?= $right ? ' checked' : '' ?> aria-label="<?= e('Answer ' . ($o + 1) . ' is right') ?>">
										<input class="input" name="<?= $side ?>[<?= $i ?>][o][<?= $o ?>]" value="<?= e($r[1][$o] ?? '') ?>" placeholder="<?= $o === 0 ? 'The right answer' : 'Another answer' ?>" aria-label="<?= e('Answer ' . ($o + 1)) ?>" maxlength="80">
									</label>
								<?php endfor; ?>
							</div>
						</fieldset>
					<?php endfor; ?>
					<p class="help">Tick the right answer. Two answers is enough; four is more fun. Up to <?= Quiz::MAX_QUESTIONS ?> questions. Guests see the answers in the order you write them, so move the right one around.</p>
					<?php if ((int) $players[$side] > 0): ?>
						<label class="check"><span>Start <?= e($nm) ?>&rsquo;s leaderboard again</span><input type="checkbox" name="restart[<?= $side ?>]" value="1"></label>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="form-actions gap-t-sm"><button class="pill" type="submit">Save both quizzes</button><a class="pill pill--glass" href="<?= e(App::url('/w/' . $w['slug'] . '#quiz')) ?>">See it on your page</a></div>
	</form>
</div>
