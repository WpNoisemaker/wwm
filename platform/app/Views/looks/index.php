<div class="wrap" style="padding:14px 0 0">
	<h1>Four looks</h1>
	<p class="lede" style="margin:8px 0 20px;max-width:46ch">The same home page in four worlds, each built to one of your references. Open each on a phone and on a desktop.</p>
	<div class="grid grid--2">
		<?php foreach ($looks as $k => [$name, $cls, $desc]): ?>
			<a class="card card--pad" href="<?= e(App::url('/looks/' . $k)) ?>">
				<b style="display:block;font-size:1.15rem;font-weight:500;letter-spacing:-.025em"><?= e($name) ?></b>
				<span class="sub" style="display:block;margin-top:5px"><?= e($desc) ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</div>
