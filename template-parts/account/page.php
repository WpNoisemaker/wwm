<?php
/**
 * Wedding page editor: one form per part.
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
$wd_id   = $args['id'];
$wd_d    = $args['d'];
$wd_prem = wd_is_paid( $wd_id );
// phpcs:ignore WordPress.Security.NonceVerification
$wd_part  = isset( $_GET['part'] ) ? sanitize_key( wp_unslash( $_GET['part'] ) ) : 'basics';
$wd_parts = array(
	'basics'    => __( 'Basics', 'wwmd' ),
	'sections'  => __( 'Sections on/off', 'wwmd' ),
	'events'    => __( 'Invitation & programs', 'wwmd' ),
	'story'     => __( 'Love story', 'wwmd' ),
	'party'     => __( 'Squad', 'wwmd' ),
	'quiz'      => __( 'Quiz', 'wwmd' ),
	'registry'  => __( 'Registry', 'wwmd' ),
	'vendors'   => __( 'Vendors', 'wwmd' ),
	'instagram' => __( 'Instagram', 'wwmd' ),
	'design'    => __( 'Template, colours & fonts', 'wwmd' ),
	'extras'    => __( 'Live, spray & passes', 'wwmd' ),
);
$wd_part  = isset( $wd_parts[ $wd_part ] ) ? $wd_part : 'basics';
$wd_lock  = static function ( $feature ) use ( $wd_id ) {
	return ! wd_can( $wd_id, $feature );
};
?>
<div class="app-head">
	<div><p class="eyebrow"><?php esc_html_e( 'Wedding page', 'wwmd' ); ?></p><h1><?php echo esc_html( $wd_parts[ $wd_part ] ); ?></h1></div>
	<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $wd_id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View page', 'wwmd' ); ?></a>
</div>
<nav class="subnav" aria-label="<?php esc_attr_e( 'Page parts', 'wwmd' ); ?>">
	<?php foreach ( $wd_parts as $wd_k => $wd_l ) : ?>
		<?php $wd_locked = ( 'design' === $wd_k && $wd_lock( 'design' ) ) || ( 'extras' === $wd_k && $wd_lock( 'livestream' ) && $wd_lock( 'spray' ) && $wd_lock( 'entry_pass' ) ); ?>
		<a class="chip<?php echo $wd_k === $wd_part ? ' is-on' : ''; ?>" href="<?php echo esc_url( wd_account_url( 'page', array( 'wid' => $wd_id, 'part' => $wd_k ) ) ); ?>"><?php echo esc_html( $wd_l ); ?><?php echo $wd_locked ? wd_icon( 'lock', 14 ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	<?php endforeach; ?>
</nav>

<?php if ( 'basics' === $wd_part ) : ?>
	<?php wd_form( 'basics', $wd_id, true ); ?>
		<div class="field-row">
			<?php wd_field( 'p1', __( 'First partner’s name', 'wwmd' ), $wd_d['p1'], 'text', 'required' ); ?>
			<?php wd_field( 'p2', __( 'Second partner’s name', 'wwmd' ), $wd_d['p2'], 'text', 'required' ); ?>
			<label class="field"><span><?php esc_html_e( 'The bride is', 'wwmd' ); ?></span><select name="bride"><option value="p1" <?php selected( $wd_d['bride'], 'p1' ); ?>><?php esc_html_e( 'The first partner', 'wwmd' ); ?></option><option value="p2" <?php selected( $wd_d['bride'], 'p2' ); ?>><?php esc_html_e( 'The second partner', 'wwmd' ); ?></option></select></label>
		</div>
		<?php wd_field( 'tagline', __( 'A line under your names (optional)', 'wwmd' ), $wd_d['tagline'], 'text', 'maxlength="120" placeholder="' . esc_attr__( 'Two hearts, one love, one big owambe', 'wwmd' ) . '"' ); ?>
		<div class="field-row">
			<?php wd_field( 'date', __( 'Wedding date', 'wwmd' ), $wd_d['date'], 'date', 'required' ); ?>
			<?php wd_field( 'time', __( 'Start time', 'wwmd' ), $wd_d['time'], 'time' ); ?>
			<?php wd_field( 'hashtag', __( 'Hashtag', 'wwmd' ), $wd_d['hashtag'], 'text', 'placeholder="TolaKay26"' ); ?>
		</div>
		<div class="field-row">
			<?php wd_field( 'venue', __( 'Main venue', 'wwmd' ), $wd_d['venue'] ); ?>
			<?php wd_field( 'city', __( 'City', 'wwmd' ), $wd_d['city'], 'text', 'required' ); ?>
			<?php wd_field( 'country', __( 'Country', 'wwmd' ), $wd_d['country'], 'text', 'required' ); ?>
		</div>
		<div class="field-row">
			<label class="field"><span><?php esc_html_e( 'Gifts are paid in', 'wwmd' ); ?></span><select name="currency"><?php foreach ( wd_currencies() as $wd_c => $wd_cn ) : ?><option value="<?php echo esc_attr( $wd_c ); ?>" <?php selected( $wd_d['currency'], $wd_c ); ?>><?php echo esc_html( $wd_cn[1] . ' (' . $wd_cn[0] . ')' ); ?></option><?php endforeach; ?></select></label>
			<?php wd_field( 'max', __( 'Most guests per RSVP', 'wwmd' ), $wd_d['rsvp']['max'], 'number', 'min="1" max="10"' ); ?>
			<?php wd_field( 'deadline', __( 'RSVP by (optional)', 'wwmd' ), $wd_d['rsvp']['deadline'], 'date' ); ?>
		</div>
		<fieldset class="field checks checks--inline"><legend><?php esc_html_e( 'Wedding style', 'wwmd' ); ?></legend>
			<?php
			$wd_has = wp_get_object_terms( $wd_id, 'wd_style', array( 'fields' => 'slugs' ) );
			foreach ( wd_styles() as $wd_slug => $wd_s ) :
				?>
				<label><input type="checkbox" name="style[]" value="<?php echo esc_attr( $wd_slug ); ?>" <?php checked( in_array( $wd_slug, (array) $wd_has, true ) ); ?>><span><?php echo esc_html( $wd_s[0] ); ?></span></label>
			<?php endforeach; ?>
		</fieldset>
		<div class="field-row">
			<label class="field file-field"><span><?php esc_html_e( 'Cover photo', 'wwmd' ); ?></span>
				<?php if ( $wd_d['cover'] ) : ?><img src="<?php echo esc_url( wd_img_url( $wd_d['cover'], 'wd-card' ) ); ?>" alt=""><?php endif; ?>
				<input type="file" name="cover_file" accept="image/jpeg,image/png,image/webp">
			</label>
			<label class="field file-field"><span><?php esc_html_e( 'Couple photo (round)', 'wwmd' ); ?></span>
				<?php if ( $wd_d['photo'] ) : ?><img src="<?php echo esc_url( wd_img_url( $wd_d['photo'], 'wd-square' ) ); ?>" alt=""><?php endif; ?>
				<input type="file" name="photo_file" accept="image/jpeg,image/png,image/webp">
			</label>
		</div>
		<?php if ( $wd_d['cover'] ) : ?><label class="check"><input type="checkbox" name="remove_cover" value="1"><span><?php esc_html_e( 'Remove the cover photo & use cloth art instead', 'wwmd' ); ?></span></label><?php endif; ?>
		<h2 class="form__h"><?php esc_html_e( 'Invitation wording', 'wwmd' ); ?></h2>
		<?php wd_field( 'hosts', __( 'Hosts line (optional)', 'wwmd' ), $wd_d['invite']['hosts'], 'text', 'placeholder="' . esc_attr__( 'The families of Chief & Mrs Adebayo & Mr & Mrs Okafor', 'wwmd' ) . '"' ); ?>
		<?php wd_field( 'message', __( 'Invitation message', 'wwmd' ), $wd_d['invite']['message'], 'textarea' ); ?>
		<?php if ( $wd_prem ) : ?>
			<h2 class="form__h"><?php echo wd_icon( 'gift', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s plan */ __( 'Was your %s page a gift?', 'wwmd' ), wd_plan_name( wd_plan( $wd_id ) ) ) ); ?></h2>
			<p class="muted"><?php esc_html_e( 'Thank the person who paid for it. Their name shows on your page. Both fields are optional.', 'wwmd' ); ?></p>
			<div class="field-row">
				<?php wd_field( 'gift_by', __( 'Gift from', 'wwmd' ), $wd_d['gift']['by'], 'text', 'maxlength="80" placeholder="' . esc_attr__( 'Aunty Funmi, the bride’s godmother', 'wwmd' ) . '"' ); ?>
				<?php wd_field( 'gift_link', __( 'Link to one of their social pages', 'wwmd' ), $wd_d['gift']['link'], 'url', 'placeholder="https://instagram.com/…"' ); ?>
			</div>
		<?php endif; ?>
		<label class="check"><input type="checkbox" name="hidden" value="1" <?php checked( 'private', get_post_status( $wd_id ) ); ?>><span><?php esc_html_e( 'Make my page private (hidden from the directory, rankings & search)', 'wwmd' ); ?></span></label>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'sections' === $wd_part ) : ?>
	<?php wd_form( 'sections', $wd_id ); ?>
		<p class="muted"><?php esc_html_e( 'Switch off any section you do not want on your page. You can switch it back on any time; nothing you added is lost.', 'wwmd' ); ?></p>
		<div class="toggles">
			<?php foreach ( wd_features() as $wd_f => $wd_label ) : ?>
				<?php
				if ( in_array( $wd_f, array( 'design', 'templates', 'table_qr', 'no_ads', 'no_fees', 'entry_pass' ), true ) ) {
					continue;
				}
				$wd_allowed = wd_can( $wd_id, $wd_f );
				?>
				<label class="toggle<?php echo $wd_allowed ? '' : ' is-locked'; ?>">
					<input type="checkbox" name="sections[<?php echo esc_attr( $wd_f ); ?>]" value="1" <?php checked( $wd_allowed && ( ! isset( $wd_d['sections'][ $wd_f ] ) || $wd_d['sections'][ $wd_f ] ) ); ?> <?php disabled( ! $wd_allowed ); ?>>
					<span class="toggle__ui" aria-hidden="true"></span>
					<span class="toggle__text"><b><?php echo esc_html( $wd_label ); ?></b><?php if ( ! $wd_allowed ) : ?><small><?php echo wd_icon( 'lock', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( sprintf( /* translators: %s plan */ __( 'On %s', 'wwmd' ), wd_plan_name( wd_feature_plan( $wd_f ) ) ) ); ?></small><?php endif; ?></span>
				</label>
			<?php endforeach; ?>
		</div>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save sections', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'events' === $wd_part ) : ?>
	<?php wd_form( 'events', $wd_id ); ?>
		<p class="muted"><?php esc_html_e( 'Many weddings have more than one program: the introduction, traditional wedding, Nikkah, church, court or reception. Add each one with its own date, venue & dress code. Guests see them in date order & pick the ones they are coming to when they RSVP.', 'wwmd' ); ?></p>
		<?php
		wd_repeater(
			'events',
			$wd_d['events'],
			static function ( $n, $r ) {
				echo '<div class="field-row">';
				echo '<label class="field"><span>' . esc_html__( 'Type', 'wwmd' ) . '</span><select name="' . esc_attr( $n . '[type]' ) . '"><option value="">' . esc_html__( 'Work it out from the name', 'wwmd' ) . '</option>';
				foreach ( wd_program_types() as $wd_tk => $wd_tv ) {
					echo '<option value="' . esc_attr( $wd_tk ) . '"' . selected( $r['type'] ?? '', $wd_tk, false ) . '>' . esc_html( $wd_tv[0] ) . '</option>';
				}
				echo '</select></label>';
				wd_field( $n . '[title]', __( 'Program name', 'wwmd' ), $r['title'] ?? '', 'text', 'placeholder="' . esc_attr__( 'Traditional engagement', 'wwmd' ) . '"' );
				wd_field( $n . '[kind]', __( 'Label above the name (optional)', 'wwmd' ), $r['kind'] ?? '', 'text', 'maxlength="40" placeholder="' . esc_attr__( 'Eve of the wedding', 'wwmd' ) . '"' );
				wd_field( $n . '[date]', __( 'Date', 'wwmd' ), $r['date'] ?? '', 'date' );
				wd_field( $n . '[time]', __( 'Time', 'wwmd' ), $r['time'] ?? '', 'time' );
				echo '</div><div class="field-row">';
				wd_field( $n . '[venue]', __( 'Venue', 'wwmd' ), $r['venue'] ?? '' );
				wd_field( $n . '[address]', __( 'Address', 'wwmd' ), $r['address'] ?? '' );
				echo '</div><div class="field-row">';
				wd_field( $n . '[dress]', __( 'Dress code / aso-ebi', 'wwmd' ), $r['dress'] ?? '', 'text', 'placeholder="' . esc_attr__( 'Emerald & gold aso-ebi', 'wwmd' ) . '"' );
				wd_field( $n . '[c1]', __( 'Colour 1', 'wwmd' ), ( $r['c1'] ?? '' ) ? $r['c1'] : '#6B2BD9', 'color' );
				wd_field( $n . '[c2]', __( 'Colour 2', 'wwmd' ), ( $r['c2'] ?? '' ) ? $r['c2'] : '#FFC21A', 'color' );
				echo '</div>';
				wd_field( $n . '[note]', __( 'A short note for guests (optional)', 'wwmd' ), $r['note'] ?? '', 'textarea', 'maxlength="300"' );
				wd_field( $n . '[plan]', __( 'Order of the day (optional): one line each, like “11:00 am | Arrival | Guests are seated”', 'wwmd' ), $r['plan'] ?? '', 'textarea' );
				echo '<label class="check"><input type="checkbox" name="' . esc_attr( $n . '[private]' ) . '" value="1"' . checked( ! empty( $r['private'] ), true, false ) . '><span>' . esc_html__( 'By invitation only: hide the venue & leave it out of the RSVP form', 'wwmd' ) . '</span></label>';
			},
			__( 'Add a program', 'wwmd' ),
			array()
		);
		?>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save programs', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'story' === $wd_part ) : ?>
	<?php wd_form( 'story', $wd_id, true ); ?>
		<p class="muted"><?php esc_html_e( 'How you met, the first date, the proposal. Short & sweet works best.', 'wwmd' ); ?></p>
		<?php
		wd_repeater(
			'story',
			$wd_d['story'],
			static function ( $n, $r, $f ) {
				echo '<div class="field-row">';
				wd_field( $n . '[when]', __( 'When', 'wwmd' ), $r['when'] ?? '', 'text', 'placeholder="' . esc_attr__( 'June 2019', 'wwmd' ) . '"' );
				wd_field( $n . '[title]', __( 'Title', 'wwmd' ), $r['title'] ?? '' );
				echo '</div>';
				wd_field( $n . '[text]', __( 'What happened', 'wwmd' ), $r['text'] ?? '', 'textarea' );
				echo '<label class="field file-field"><span>' . esc_html__( 'Photo (optional)', 'wwmd' ) . '</span>' . ( ! empty( $r['image'] ) ? '<img src="' . esc_url( wd_img_url( $r['image'], 'wd-square' ) ) . '" alt="">' : '' ) . '<input type="file" name="' . esc_attr( $f ) . '" accept="image/*"></label>';
				echo '<input type="hidden" name="' . esc_attr( $n ) . '[image]" value="' . esc_attr( $r['image'] ?? '' ) . '">';
			},
			__( 'Add a moment', 'wwmd' ),
			array()
		);
		?>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save story', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'party' === $wd_part ) : ?>
	<?php
	$wd_wait = wd_squad_pending( $wd_id );
	$wd_open = wd_squad_open( $wd_id );
	$wd_link = wd_squad_link( $wd_id );
	$wd_team = array(
		'bride' => sprintf( /* translators: %s name */ __( 'Team %s', 'wwmd' ), wd_side_name( $wd_d, 'bride' ) ),
		'groom' => sprintf( /* translators: %s name */ __( 'Team %s', 'wwmd' ), wd_side_name( $wd_d, 'groom' ) ),
	);
	?>
	<section class="panel squad-invite">
		<h2><?php echo wd_icon( 'users', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Let your squad add themselves', 'wwmd' ); ?></h2>
		<p class="hint"><?php esc_html_e( 'Send this link to your bridesmaids & groomsmen. Each one adds a photo, their career, relationship status & social handles. Nobody shows on your page until you approve them.', 'wwmd' ); ?></p>
		<?php if ( $wd_open ) : ?>
			<div class="copy-field"><input type="text" readonly value="<?php echo esc_url( $wd_link ); ?>" aria-label="<?php esc_attr_e( 'Your squad invite link', 'wwmd' ); ?>"><button class="btn btn--ink btn--sm" type="button" data-copy="<?php echo esc_url( $wd_link ); ?>"><?php echo wd_icon( 'link', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Copy link', 'wwmd' ); ?></button></div>
		<?php else : ?>
			<p class="squad-invite__off"><?php esc_html_e( 'Your invite link is switched off, so nobody new can add themselves.', 'wwmd' ); ?></p>
		<?php endif; ?>
		<div class="btn-row">
			<?php if ( $wd_open ) : ?>
				<a class="btn btn--wa btn--sm" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( sprintf( /* translators: %s link */ __( 'Hi! Please add yourself to the squad on our wedding page: %s', 'wwmd' ), $wd_link ) ) ); ?>" target="_blank" rel="noopener"><?php echo wd_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Send on WhatsApp', 'wwmd' ); ?></a>
			<?php endif; ?>
			<?php wd_form( 'squad', $wd_id, false, 'inline-form' ); ?><input type="hidden" name="do" value="new"><button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Make a new link', 'wwmd' ); ?></button></form>
			<?php wd_form( 'squad', $wd_id, false, 'inline-form' ); ?><input type="hidden" name="do" value="<?php echo $wd_open ? 'off' : 'on'; ?>"><button class="btn btn--ghost btn--sm" type="submit"><?php echo $wd_open ? esc_html__( 'Switch the link off', 'wwmd' ) : esc_html__( 'Switch it back on', 'wwmd' ); ?></button></form>
		</div>
	</section>
	<?php if ( $wd_wait ) : ?>
		<section class="panel squad-wait" id="squad-waiting">
			<h2><?php echo esc_html( sprintf( /* translators: %d people */ _n( '%d profile waiting for you', '%d profiles waiting for you', count( $wd_wait ), 'wwmd' ), count( $wd_wait ) ) ); ?></h2>
			<ul class="squad-wait__list">
				<?php foreach ( $wd_wait as $wd_w ) : ?>
					<li class="squad-wait__item">
						<span class="squad-wait__pic"><?php if ( $wd_w['image'] ) : ?><img src="<?php echo esc_url( wd_img_url( $wd_w['image'], 'wd-square' ) ); ?>" alt=""><?php endif; ?></span>
						<div class="squad-wait__who">
							<b><?php echo esc_html( $wd_w['name'] ); ?></b>
							<span><?php echo esc_html( $wd_w['role'] . ' · ' . $wd_team[ $wd_w['side'] ] ); ?></span>
							<span><?php echo esc_html( trim( $wd_w['career'] . ( $wd_w['status'] ? ' · ' . wd_rel_statuses()[ $wd_w['status'] ] : '' ), ' ·' ) ); ?></span>
							<span class="squad-wait__social">
								<?php foreach ( wd_squad_links( $wd_w ) as $wd_s ) : ?>
									<a href="<?php echo esc_url( $wd_s[1] ); ?>" target="_blank" rel="noopener nofollow"><?php echo wd_icon( $wd_s[2], 15 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>@<?php echo esc_html( $wd_s[3] ); ?></a>
								<?php endforeach; ?>
							</span>
							<?php if ( $wd_w['line'] ) : ?><small>“<?php echo esc_html( $wd_w['line'] ); ?>”</small><?php endif; ?>
						</div>
						<div class="squad-wait__do">
							<?php wd_form( 'squad', $wd_id, false, 'inline-form' ); ?><input type="hidden" name="do" value="approve"><input type="hidden" name="pid" value="<?php echo esc_attr( $wd_w['id'] ); ?>"><button class="btn btn--ink btn--sm" type="submit"><?php echo wd_icon( 'check', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Approve', 'wwmd' ); ?></button></form>
							<?php wd_form( 'squad', $wd_id, false, 'inline-form' ); ?><input type="hidden" name="do" value="decline"><input type="hidden" name="pid" value="<?php echo esc_attr( $wd_w['id'] ); ?>"><button class="btn btn--ghost btn--sm" type="submit"><?php esc_html_e( 'Decline', 'wwmd' ); ?></button></form>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
	<?php wd_form( 'party', $wd_id, true ); ?>
		<h2 class="form__h"><?php esc_html_e( 'Your squad', 'wwmd' ); ?></h2>
		<p class="muted"><?php esc_html_e( 'Add people yourself too. Each profile shows a photo, career, relationship status & at least one social handle. No photo? We draw them an avatar.', 'wwmd' ); ?></p>
		<?php
		wd_repeater(
			'party',
			$wd_d['party'],
			static function ( $n, $r, $f ) use ( $wd_team ) {
				echo '<div class="field-row">';
				wd_field( $n . '[name]', __( 'Name', 'wwmd' ), $r['name'] ?? '' );
				echo '<label class="field"><span>' . esc_html__( 'Role', 'wwmd' ) . '</span><select name="' . esc_attr( $n ) . '[role]">';
				foreach ( wd_squad_roles() as $role ) {
					printf( '<option %s>%s</option>', selected( $r['role'] ?? '', $role, false ), esc_html( $role ) );
				}
				echo '</select></label><label class="field"><span>' . esc_html__( 'Side', 'wwmd' ) . '</span><select name="' . esc_attr( $n ) . '[side]">';
				foreach ( $wd_team as $k => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $r['side'] ?? '', $k, false ), esc_html( $label ) );
				}
				echo '</select></label></div><div class="field-row">';
				wd_field( $n . '[career]', __( 'Career or job title', 'wwmd' ), $r['career'] ?? '', 'text', 'maxlength="60" placeholder="' . esc_attr__( 'e.g. Lawyer', 'wwmd' ) . '"' );
				echo '<label class="field"><span>' . esc_html__( 'Relationship status', 'wwmd' ) . '</span><select name="' . esc_attr( $n ) . '[status]">';
				foreach ( wd_rel_statuses() as $k => $label ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $r['status'] ?? '', $k, false ), esc_html( $label ) );
				}
				echo '</select></label>';
				wd_field( $n . '[line]', __( 'A line about them', 'wwmd' ), $r['line'] ?? '', 'text', 'maxlength="160"' );
				echo '</div><div class="field-row">';
				wd_field( $n . '[nick]', __( 'Nickname', 'wwmd' ), $r['nick'] ?? '', 'text', 'maxlength="30"' );
				wd_field( $n . '[likes]', __( 'Things they love', 'wwmd' ), $r['likes'] ?? '', 'text', 'maxlength="120" placeholder="' . esc_attr__( 'Baking, football & long voice notes', 'wwmd' ) . '"' );
				echo '</div><p class="field-label">' . esc_html__( 'Social handles (at least one)', 'wwmd' ) . '</p><div class="field-row field-row--socials">';
				foreach ( wd_socials() as $k => $s ) {
					wd_field( $n . '[' . $k . ']', $s[0], $r[ $k ] ?? '', 'text', 'placeholder="@handle"' );
				}
				echo '</div><div class="field-row"><label class="field file-field"><span>' . esc_html__( 'Photo', 'wwmd' ) . '</span>' . ( ! empty( $r['image'] ) ? '<img src="' . esc_url( wd_img_url( $r['image'], 'wd-square' ) ) . '" alt="">' : '' ) . '<input type="file" name="' . esc_attr( $f ) . '" accept="image/*"></label>';
				echo '<input type="hidden" name="' . esc_attr( $n ) . '[image]" value="' . esc_attr( $r['image'] ?? '' ) . '"></div>';
			},
			__( 'Add a person', 'wwmd' ),
			array()
		);
		?>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save squad', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'quiz' === $wd_part ) : ?>
	<?php wd_form( 'quiz', $wd_id ); ?>
		<p class="muted"><?php esc_html_e( 'Up to 15 questions about the two of you. Pick the right answer for each; guests never see it until they finish.', 'wwmd' ); ?></p>
		<?php
		wd_repeater(
			'quiz',
			$wd_d['quiz'],
			static function ( $n, $r ) {
				wd_field( $n . '[q]', __( 'Question', 'wwmd' ), $r['q'] ?? '' );
				echo '<div class="field-row field-row--4">';
				for ( $i = 0; $i < 4; $i++ ) {
					wd_field( $n . '[a' . $i . ']', sprintf( /* translators: %d answer */ __( 'Answer %d', 'wwmd' ), $i + 1 ), $r['a'][ $i ] ?? '' );
				}
				echo '</div><label class="field field--sm"><span>' . esc_html__( 'Right answer', 'wwmd' ) . '</span><select name="' . esc_attr( $n ) . '[c]">';
				for ( $i = 0; $i < 4; $i++ ) {
					printf( '<option value="%1$d" %2$s>%3$s</option>', (int) $i, selected( (int) ( $r['c'] ?? 0 ), $i, false ), esc_html( sprintf( /* translators: %d answer */ __( 'Answer %d', 'wwmd' ), $i + 1 ) ) );
				}
				echo '</select></label>';
			},
			__( 'Add a question', 'wwmd' ),
			array()
		);
		?>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save quiz', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'registry' === $wd_part ) : ?>
	<?php wd_form( 'registry', $wd_id, true ); ?>
		<p class="muted"><?php esc_html_e( 'Cash funds (honeymoon, first home, business) & gifts. Guests give online toward any of them, or buy a gift from the store link.', 'wwmd' ); ?></p>
		<?php if ( wd_fee_rate( $wd_id, 'gift' ) > 0 ) : ?><p class="hint"><?php echo esc_html( sprintf( /* translators: %s percent */ __( 'Your plan carries a %s%% service fee on gifts sent online, which guests can choose to cover. Premium & High Society pages have no fee.', 'wwmd' ), number_format_i18n( wd_fee_rate( $wd_id, 'gift' ), 1 ) ) ); ?></p><?php endif; ?>
		<?php
		wd_repeater(
			'registry',
			$wd_d['registry'],
			static function ( $n, $r, $f ) {
				echo '<input type="hidden" name="' . esc_attr( $n ) . '[id]" value="' . esc_attr( $r['id'] ?? '' ) . '"><input type="hidden" name="' . esc_attr( $n ) . '[image]" value="' . esc_attr( $r['image'] ?? '' ) . '">';
				echo '<div class="field-row"><label class="field field--sm"><span>' . esc_html__( 'Type', 'wwmd' ) . '</span><select name="' . esc_attr( $n ) . '[type]"><option value="fund" ' . selected( $r['type'] ?? '', 'fund', false ) . '>' . esc_html__( 'Cash fund', 'wwmd' ) . '</option><option value="gift" ' . selected( $r['type'] ?? '', 'gift', false ) . '>' . esc_html__( 'Gift', 'wwmd' ) . '</option></select></label>';
				wd_field( $n . '[title]', __( 'Name', 'wwmd' ), $r['title'] ?? '' );
				wd_field( $n . '[price]', __( 'Goal or price', 'wwmd' ), $r['price'] ?? '', 'number', 'min="0"' );
				echo '</div>';
				wd_field( $n . '[note]', __( 'Short note', 'wwmd' ), $r['note'] ?? '' );
				echo '<div class="field-row">';
				wd_field( $n . '[url]', __( 'Store link (optional)', 'wwmd' ), $r['url'] ?? '', 'url' );
				wd_field( $n . '[store]', __( 'Store name', 'wwmd' ), $r['store'] ?? '' );
				echo '<label class="field file-field"><span>' . esc_html__( 'Picture (optional)', 'wwmd' ) . '</span><input type="file" name="' . esc_attr( $f ) . '" accept="image/*"></label></div>';
			},
			__( 'Add a fund or gift', 'wwmd' ),
			array()
		);
		?>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save registry', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'vendors' === $wd_part ) : ?>
	<?php
	wd_form( 'vendors', $wd_id );
	$wd_all   = get_posts( array( 'post_type' => 'wd_vendor', 'posts_per_page' => 300, 'orderby' => 'title', 'order' => 'ASC' ) );
	$wd_types = get_terms( array( 'taxonomy' => 'wd_vtype', 'hide_empty' => false ) );
	?>
		<p class="muted"><?php esc_html_e( 'List the vendors who worked your wedding. Guests can review them from your page & the reviews show on the vendor’s profile. Start typing to pick a listed vendor, or add a new one.', 'wwmd' ); ?></p>
		<datalist id="vendor-names"><?php foreach ( $wd_all as $wd_v ) : ?><option value="<?php echo esc_attr( $wd_v->post_title ); ?>"></option><?php endforeach; ?></datalist>
		<?php
		$wd_rows = array();
		foreach ( $wd_d['vendors'] as $wd_v ) {
			$wd_meta   = wd_vendor( (int) $wd_v['id'] );
			$wd_rows[] = array(
				'name' => get_the_title( (int) $wd_v['id'] ),
				'type' => wd_vendor_type( (int) $wd_v['id'] ),
				'ig'   => $wd_meta['ig'],
				'role' => $wd_v['role'],
			);
		}
		wd_repeater(
			'vendors',
			$wd_rows,
			static function ( $n, $r ) use ( $wd_types ) {
				echo '<div class="field-row">';
				wd_field( $n . '[name]', __( 'Vendor name', 'wwmd' ), $r['name'] ?? '', 'text', 'list="vendor-names" autocomplete="off"' );
				echo '<label class="field"><span>' . esc_html__( 'Type', 'wwmd' ) . '</span><select name="' . esc_attr( $n ) . '[type]">';
				foreach ( is_array( $wd_types ) ? $wd_types : array() as $t ) {
					printf( '<option %s>%s</option>', selected( $r['type'] ?? '', $t->name, false ), esc_html( $t->name ) );
				}
				echo '</select></label>';
				wd_field( $n . '[ig]', __( 'Instagram', 'wwmd' ), $r['ig'] ?? '', 'text', 'placeholder="@handle"' );
				wd_field( $n . '[role]', __( 'What they did (optional)', 'wwmd' ), $r['role'] ?? '', 'text', 'placeholder="' . esc_attr__( 'Bridal makeup', 'wwmd' ) . '"' );
				echo '</div>';
			},
			__( 'Add a vendor', 'wwmd' ),
			array()
		);
		?>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save vendors', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'instagram' === $wd_part ) : ?>
	<?php wd_form( 'instagram', $wd_id ); ?>
		<?php wd_field( 'handle', __( 'Your wedding Instagram page', 'wwmd' ), $wd_d['ig']['handle'], 'text', 'placeholder="@tolakay26"' ); ?>
		<?php if ( wd_ig_ready() ) : ?>
			<div class="panel panel--soft">
				<?php if ( get_post_meta( $wd_id, '_wd_ig_token', true ) ) : ?>
					<p><?php echo wd_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Connected. Your latest posts show on your page & their likes & comments count toward your Buzz score.', 'wwmd' ); ?></p>
					<label class="check"><input type="checkbox" name="disconnect" value="1"><span><?php esc_html_e( 'Disconnect Instagram', 'wwmd' ); ?></span></label>
				<?php else : ?>
					<p><?php esc_html_e( 'Connect your Instagram professional account (creator or business) to show your latest posts automatically.', 'wwmd' ); ?></p>
					<a class="btn btn--ink" href="<?php echo esc_url( wd_ig_connect_url( $wd_id ) ); ?>"><?php echo wd_icon( 'insta', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Connect Instagram', 'wwmd' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php wd_field( 'posts', __( 'Or paste up to 9 post or reel links, one per line', 'wwmd' ), implode( "\n", $wd_d['ig']['posts'] ), 'textarea', 'rows="5" placeholder="https://www.instagram.com/p/…"' ); ?>
		<p class="hint"><?php esc_html_e( 'Likes & comments on your Instagram page count toward your Buzz score. Our team checks the numbers for pages that are not connected.', 'wwmd' ); ?></p>
		<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save Instagram', 'wwmd' ); ?></button>
	</form>

<?php elseif ( 'design' === $wd_part ) : ?>
	<?php if ( $wd_lock( 'design' ) ) : ?>
		<?php get_template_part( 'template-parts/account/upsell', null, array( 'id' => $wd_id, 'what' => __( 'Match your page to your wedding colours & fonts', 'wwmd' ), 'feature' => 'design' ) ); ?>
	<?php else : ?>
		<?php wd_form( 'design', $wd_id, true ); ?>
			<?php if ( $wd_lock( 'templates' ) ) : ?>
				<div class="panel upsell upsell--inline">
					<div>
						<p class="eyebrow"><?php echo wd_icon( 'crown', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( wd_plan_name( wd_feature_plan( 'templates' ) ) ); ?></p>
						<p><?php esc_html_e( 'Your colours & fonts dress the Classic look. The 10 Premium designs give your page its own layout, ornaments & motion, & let you pick where your photo sits & the order of your sections.', 'wwmd' ); ?></p>
						<a class="btn btn--marigold btn--sm" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'plan', 'wid' => $wd_id, 'tier' => wd_feature_plan( 'templates' ) ), wd_url( 'account' ) ) . '#pay' ); ?>"><?php echo esc_html( sprintf( /* translators: %s plan */ __( 'See %s', 'wwmd' ), wd_plan_name( wd_feature_plan( 'templates' ) ) ) ); ?></a>
					</div>
				</div>
			<?php else : ?>
			<h2 class="form__h"><?php esc_html_e( 'Your template', 'wwmd' ); ?></h2>
			<?php $wd_ctpl = wd_custom_tpl( $wd_id ); ?>
			<div class="tpl-pick">
				<?php if ( $wd_ctpl ) : ?>
					<?php $wd_ci = wd_tpl_info( $wd_ctpl ); ?>
					<label class="tpl-card tpl-card--custom">
						<input type="radio" name="template" value="<?php echo esc_attr( $wd_ctpl ); ?>" <?php checked( wd_template( $wd_id, $wd_d ), $wd_ctpl ); ?> data-tpl="<?php echo esc_attr( wp_json_encode( $wd_ci['start'] ) ); ?>">
						<span class="tpl-card__pic"><?php echo wd_tpl_thumb( $wd_ctpl ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<b><?php echo esc_html( $wd_ci['name'] ); ?> <em class="tpl-card__tag"><?php esc_html_e( 'Made for you', 'wwmd' ); ?></em></b>
						<small><?php echo esc_html( $wd_ci['desc'] ); ?></small>
					</label>
				<?php endif; ?>
				<?php foreach ( wd_templates() as $wd_k => $wd_t ) : ?>
					<label class="tpl-card">
						<input type="radio" name="template" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( wd_template( $wd_id, $wd_d ), $wd_k ); ?> data-tpl="<?php echo esc_attr( wp_json_encode( $wd_t[2] ) ); ?>">
						<span class="tpl-card__pic"><?php echo wd_tpl_thumb( $wd_k ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<b><?php echo esc_html( $wd_t[0] ); ?></b>
						<small><?php echo esc_html( $wd_t[1] ); ?></small>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="muted"><?php esc_html_e( 'Picking a template fills in its starting colours & fonts. Change them below to match your wedding.', 'wwmd' ); ?></p>
			<?php if ( $wd_ctpl && wd_tpl_info( $wd_ctpl )['fields'] ) : ?>
				<div class="custom-fields">
					<h3 class="form__h3"><?php echo esc_html( sprintf( /* translators: %s template */ __( 'Extras for %s', 'wwmd' ), wd_tpl_info( $wd_ctpl )['name'] ) ); ?></h3>
					<?php $wd_cv = wd_custom_vals( $wd_d, $wd_ctpl ); ?>
					<?php foreach ( wd_tpl_info( $wd_ctpl )['fields'] as $wd_k => $wd_f ) : ?>
						<?php if ( 'image' === $wd_f[0] ) : ?>
							<label class="field file-field"><span><?php echo esc_html( $wd_f[1] ); ?></span>
								<?php if ( $wd_cv[ $wd_k ] ) : ?><img src="<?php echo esc_url( wd_img_url( $wd_cv[ $wd_k ], 'wd-card' ) ); ?>" alt=""><?php endif; ?>
								<input type="file" name="custom_<?php echo esc_attr( $wd_k ); ?>" accept="image/jpeg,image/png,image/webp">
							</label>
							<input type="hidden" name="custom[<?php echo esc_attr( $wd_k ); ?>]" value="">
							<?php if ( $wd_cv[ $wd_k ] ) : ?><label class="check"><input type="checkbox" name="custom[<?php echo esc_attr( $wd_k ); ?>_remove]" value="1"><span><?php esc_html_e( 'Remove this photo', 'wwmd' ); ?></span></label><?php endif; ?>
						<?php else : ?>
							<?php wd_field( 'custom[' . $wd_k . ']', $wd_f[1], $wd_cv[ $wd_k ], $wd_f[0], 'textarea' === $wd_f[0] ? 'maxlength="600"' : 'maxlength="120"' ); ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php $wd_lay = wd_layout( $wd_id, $wd_d ); ?>
			<h2 class="form__h"><?php esc_html_e( 'Layout', 'wwmd' ); ?></h2>
			<p class="muted"><?php esc_html_e( 'Pick where your photo sits at the top of your page & the shape of your photos & cards.', 'wwmd' ); ?></p>
			<div class="lay-pick" role="radiogroup" aria-label="<?php esc_attr_e( 'Top of the page', 'wwmd' ); ?>">
				<?php foreach ( wd_hero_layouts() as $wd_k => $wd_l ) : ?>
					<label class="lay-card"><input type="radio" name="hero" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( $wd_lay['hero'], $wd_k ); ?>><span class="lay-card__pic"><?php echo wd_layout_thumb( $wd_k ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><b><?php echo esc_html( $wd_l ); ?></b></label>
				<?php endforeach; ?>
			</div>
			<fieldset class="seg">
				<legend><?php esc_html_e( 'Corners', 'wwmd' ); ?></legend>
				<?php foreach ( wd_corner_styles() as $wd_k => $wd_l ) : ?>
					<label><input type="radio" name="corners" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( $wd_lay['corners'], $wd_k ); ?>><span><i class="seg__shape seg__shape--<?php echo esc_attr( $wd_k ); ?>" aria-hidden="true"></i><?php echo esc_html( $wd_l ); ?></span></label>
				<?php endforeach; ?>
			</fieldset>
			<h3 class="form__h3"><?php esc_html_e( 'Section order', 'wwmd' ); ?></h3>
			<p class="muted"><?php esc_html_e( 'Move sections up or down. Sections you switched off stay hidden.', 'wwmd' ); ?></p>
			<ol class="order-list" data-order>
				<?php foreach ( wd_page_sections( $wd_id, $wd_d ) as $wd_k => $wd_l ) : ?>
					<li><input type="hidden" name="order[]" value="<?php echo esc_attr( $wd_k ); ?>"><span><?php echo esc_html( $wd_l ); ?></span><button type="button" class="order-list__btn" data-move="-1" aria-label="<?php echo esc_attr( sprintf( /* translators: %s section */ __( 'Move %s up', 'wwmd' ), $wd_l ) ); ?>">↑</button><button type="button" class="order-list__btn" data-move="1" aria-label="<?php echo esc_attr( sprintf( /* translators: %s section */ __( 'Move %s down', 'wwmd' ), $wd_l ) ); ?>">↓</button></li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>
			<h2 class="form__h"><?php esc_html_e( 'Colours, fonts & cloth', 'wwmd' ); ?></h2>
			<div class="field-row field-row--4">
				<?php wd_field( 'primary', __( 'Main colour', 'wwmd' ), $wd_d['design']['primary'], 'color' ); ?>
				<?php wd_field( 'accent', __( 'Accent colour', 'wwmd' ), $wd_d['design']['accent'], 'color' ); ?>
				<?php wd_field( 'bg', __( 'Background', 'wwmd' ), $wd_d['design']['bg'], 'color' ); ?>
				<?php wd_field( 'ink', __( 'Text colour', 'wwmd' ), $wd_d['design']['ink'], 'color' ); ?>
			</div>
			<div class="field-row">
				<label class="field"><span><?php esc_html_e( 'Heading font', 'wwmd' ); ?></span><select name="head"><?php foreach ( wd_fonts() as $wd_k => $wd_f ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_d['design']['head'], $wd_k ); ?>><?php echo esc_html( $wd_f[0] ); ?></option><?php endforeach; ?></select></label>
				<label class="field"><span><?php esc_html_e( 'Text font', 'wwmd' ); ?></span><select name="body"><?php foreach ( wd_fonts() as $wd_k => $wd_f ) : ?><?php if ( 'greatvibes' === $wd_k ) { continue; } ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_d['design']['body'], $wd_k ); ?>><?php echo esc_html( $wd_f[0] ); ?></option><?php endforeach; ?></select></label>
				<label class="field"><span><?php esc_html_e( 'Cloth pattern', 'wwmd' ); ?></span><select name="pattern"><?php foreach ( wd_patterns() as $wd_k => $wd_l ) : ?><option value="<?php echo esc_attr( $wd_k ); ?>" <?php selected( $wd_d['design']['pattern'], $wd_k ); ?>><?php echo esc_html( $wd_l ); ?></option><?php endforeach; ?></select></label>
			</div>
			<div class="swatch-presets" data-presets>
				<p class="muted"><?php esc_html_e( 'Or start from a popular combination:', 'wwmd' ); ?></p>
				<?php
				foreach ( array(
					array( __( 'Emerald & gold', 'wwmd' ), '#0F6B4F', '#E8B84A', '#FBF6EA', '#10241C' ),
					array( __( 'Wine & champagne', 'wwmd' ), '#7A1F3D', '#E9C99B', '#FFF7F0', '#2A0E18' ),
					array( __( 'Royal blue & silver', 'wwmd' ), '#1E3A8A', '#C0C7D6', '#F5F7FC', '#0D1633' ),
					array( __( 'Coral & mint', 'wwmd' ), '#E2683C', '#7FD8BE', '#FFF8F2', '#2B1A12' ),
					array( __( 'Lilac & marigold', 'wwmd' ), '#6B2BD9', '#FFC21A', '#FFF6E5', '#1C0B33' ),
				) as $wd_p ) :
					?>
					<button type="button" class="preset" data-preset="<?php echo esc_attr( wp_json_encode( array_slice( $wd_p, 1 ) ) ); ?>"><i style="background:<?php echo esc_attr( $wd_p[1] ); ?>"></i><i style="background:<?php echo esc_attr( $wd_p[2] ); ?>"></i><?php echo esc_html( $wd_p[0] ); ?></button>
				<?php endforeach; ?>
			</div>
			<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save design', 'wwmd' ); ?></button>
		</form>
		<?php if ( ! $wd_lock( 'templates' ) ) : ?>
			<?php get_template_part( 'template-parts/account/custom-design', null, array( 'id' => $wd_id, 'd' => $wd_d ) ); ?>
		<?php endif; ?>
	<?php endif; ?>

<?php elseif ( 'extras' === $wd_part ) : ?>
	<?php if ( $wd_lock( 'livestream' ) && $wd_lock( 'spray' ) && $wd_lock( 'entry_pass' ) ) : ?>
		<?php get_template_part( 'template-parts/account/upsell', null, array( 'id' => $wd_id, 'what' => __( 'Livestream, spraying & entry passes', 'wwmd' ), 'feature' => 'entry_pass' ) ); ?>
	<?php else : ?>
		<?php wd_form( 'extras', $wd_id ); ?>
			<?php if ( ! $wd_lock( 'livestream' ) ) : ?>
				<h2 class="form__h"><?php echo wd_icon( 'live', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Livestream', 'wwmd' ); ?></h2>
				<label class="check"><input type="checkbox" name="live_on" value="1" <?php checked( ! empty( $wd_d['live']['on'] ) ); ?>><span><?php esc_html_e( 'Show the livestream on my page', 'wwmd' ); ?></span></label>
				<?php wd_field( 'live_url', __( 'Stream link (YouTube plays on your page; Instagram, Facebook or Zoom open in a new tab)', 'wwmd' ), $wd_d['live']['url'], 'url' ); ?>
				<?php wd_field( 'live_note', __( 'Note for viewers', 'wwmd' ), $wd_d['live']['note'], 'text', 'placeholder="' . esc_attr__( 'We go live at 11am WAT for the church service', 'wwmd' ) . '"' ); ?>
				<?php wd_field( 'live_opens', __( 'Opens on (optional). Until then guests see a countdown.', 'wwmd' ), $wd_d['live']['opens'], 'date' ); ?>
			<?php endif; ?>
			<?php if ( ! $wd_lock( 'spray' ) ) : ?>
				<h2 class="form__h"><?php echo wd_icon( 'cash', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Spraying', 'wwmd' ); ?></h2>
				<label class="check"><input type="checkbox" name="spray_on" value="1" <?php checked( ! empty( $wd_d['spray']['on'] ) ); ?>><span><?php esc_html_e( 'Let guests spray money on my page', 'wwmd' ); ?></span></label>
				<label class="check"><input type="checkbox" name="show_total" value="1" <?php checked( ! empty( $wd_d['spray']['show_total'] ) ); ?>><span><?php esc_html_e( 'Show the total sprayed on my page & in the rankings', 'wwmd' ); ?></span></label>
				<?php wd_field( 'spray_opens', __( 'Opens on (optional). Until then guests see a countdown.', 'wwmd' ), $wd_d['spray']['opens'], 'date' ); ?>
			<?php endif; ?>
			<?php if ( ! $wd_lock( 'entry_pass' ) ) : ?>
				<h2 class="form__h"><?php echo wd_icon( 'ticket', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Entry passes', 'wwmd' ); ?></h2>
				<label class="check"><input type="checkbox" name="pass_on" value="1" <?php checked( ! empty( $wd_d['pass']['on'] ) ); ?>><span><?php esc_html_e( 'Give every guest who RSVPs an entry pass with a QR code', 'wwmd' ); ?></span></label>
				<?php wd_field( 'pin', __( 'Usher PIN (4 to 8 numbers). Ushers type it once on their phone to check guests in.', 'wwmd' ), $wd_d['pass']['pin'], 'text', 'inputmode="numeric" maxlength="8" pattern="[0-9]{4,8}"' ); ?>
			<?php endif; ?>
			<button class="btn btn--ink btn--lg" type="submit"><?php esc_html_e( 'Save', 'wwmd' ); ?></button>
		</form>
	<?php endif; ?>
<?php endif; ?>
