<?php
/**
 * Template Name: Sign in
 *
 * @package WWMD
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Security.NonceVerification
$wd_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'login';
$wd_tab = in_array( $wd_tab, array( 'login', 'join', 'forgot', 'reset' ), true ) ? $wd_tab : 'login';
$wd_to  = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';
$wd_key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$wd_log = isset( $_GET['login'] ) ? sanitize_user( wp_unslash( $_GET['login'] ) ) : '';
// phpcs:enable
if ( isset( $_POST['wd_auth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$wd_tab = sanitize_key( wp_unslash( $_POST['wd_auth'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
}
get_header();
?>
<section class="auth">
	<?php if ( ( 'login' === $wd_tab || 'join' === $wd_tab ) && ( wd_opt( 'auth_title' ) || wd_opt( 'auth_text' ) ) ) : ?>
		<header class="wrap auth-intro">
			<p class="eyebrow"><?php esc_html_e( 'Wedding pages', 'wwmd' ); ?></p>
			<?php if ( wd_opt( 'auth_title' ) ) : ?><h2 class="auth-intro__title"><?php echo esc_html( wd_opt( 'auth_title' ) ); ?></h2><?php endif; ?>
			<?php if ( wd_opt( 'auth_text' ) ) : ?><p class="auth-intro__text"><?php echo esc_html( wd_opt( 'auth_text' ) ); ?></p><?php endif; ?>
			<p class="auth-intro__plans"><?php foreach ( wd_plan_keys() as $wd_pk ) { echo wd_plan_tag( $wd_pk ); } // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
		</header>
	<?php endif; ?>
	<div class="wrap auth__grid">
		<div class="auth__art" aria-hidden="true">
			<?php echo wd_art( 'rings' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p class="auth__quote"><?php esc_html_e( '“We put the link in our bio. Two weeks later we had 240 RSVPs, 600 love notes & aunties spraying from London.”', 'wwmd' ); ?><small><?php esc_html_e( 'What we want every couple to say', 'wwmd' ); ?></small></p>
			<div class="auth__car"><?php echo wd_art( 'bridal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
		<div class="auth__box">
			<?php if ( 'login' === $wd_tab || 'join' === $wd_tab ) : ?>
				<div class="toggle toggle--wide" role="tablist">
					<a role="tab" class="<?php echo 'login' === $wd_tab ? 'is-on' : ''; ?>" aria-selected="<?php echo 'login' === $wd_tab ? 'true' : 'false'; ?>" href="<?php echo esc_url( wd_url( 'sign-in', $wd_to ? array( 'redirect_to' => rawurlencode( $wd_to ) ) : array() ) ); ?>"><?php esc_html_e( 'Sign in', 'wwmd' ); ?></a>
					<a role="tab" class="<?php echo 'join' === $wd_tab ? 'is-on' : ''; ?>" aria-selected="<?php echo 'join' === $wd_tab ? 'true' : 'false'; ?>" href="<?php echo esc_url( wd_url( 'sign-in', array_filter( array( 'tab' => 'join', 'redirect_to' => $wd_to ? rawurlencode( $wd_to ) : '' ) ) ) ); ?>"><?php esc_html_e( 'Create account', 'wwmd' ); ?></a>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $GLOBALS['wd_auth_error'] ) ) : ?><div class="notice notice--err" role="alert"><span><?php echo esc_html( $GLOBALS['wd_auth_error'] ); ?></span></div><?php endif; ?>
			<?php if ( ! empty( $GLOBALS['wd_auth_ok'] ) ) : ?><div class="notice notice--ok" role="status"><span><?php echo esc_html( $GLOBALS['wd_auth_ok'] ); ?></span></div><?php endif; ?>

			<?php if ( 'login' === $wd_tab ) : ?>
				<h1><?php esc_html_e( 'Welcome back', 'wwmd' ); ?></h1>
				<form class="form" method="post">
					<?php wp_nonce_field( 'wd_auth_login' ); ?>
					<input type="hidden" name="wd_auth" value="login"><input type="hidden" name="redirect_to" value="<?php echo esc_attr( $wd_to ); ?>">
					<label class="field"><span><?php esc_html_e( 'Email', 'wwmd' ); ?></span><input name="log" type="text" autocomplete="username" required></label>
					<label class="field"><span><?php esc_html_e( 'Password', 'wwmd' ); ?></span><input name="pwd" type="password" autocomplete="current-password" required></label>
					<div class="form__between"><label class="check"><input type="checkbox" name="remember" value="1" checked><span><?php esc_html_e( 'Keep me signed in', 'wwmd' ); ?></span></label><a href="<?php echo esc_url( wd_url( 'sign-in', array( 'tab' => 'forgot' ) ) ); ?>"><?php esc_html_e( 'Forgot password?', 'wwmd' ); ?></a></div>
					<button class="btn btn--ink btn--block btn--lg" type="submit"><?php esc_html_e( 'Sign in', 'wwmd' ); ?></button>
				</form>
			<?php elseif ( 'join' === $wd_tab ) : ?>
				<?php
				// 1.7.0: couples, users (shop & chat with sellers) & sellers all sign up here.
				$wd_as    = isset( $_REQUEST['as'] ) ? sanitize_key( wp_unslash( $_REQUEST['as'] ) ) : 'couple'; // phpcs:ignore WordPress.Security.NonceVerification
				$wd_as    = in_array( $wd_as, array( 'couple', 'user', 'seller' ), true ) ? $wd_as : 'couple';
				$wd_types = array(
					'couple' => array( 'heart', __( 'Make a wedding page', 'wwmd' ), __( 'Make your wedding page', 'wwmd' ), __( 'Free to start. You can go Premium any time.', 'wwmd' ) ),
					'user'   => array( 'chat', __( 'Chat & buy from sellers', 'wwmd' ), __( 'Create your account', 'wwmd' ), __( 'Free. Chat with sellers, get invoices & keep your orders in one place.', 'wwmd' ) ),
					'seller' => array( 'store', __( 'Sell on the marketplace', 'wwmd' ), __( 'Sell to couples & guests', 'wwmd' ), __( 'Create your account, then tell us about your business.', 'wwmd' ) ),
				);
				?>
				<div class="join-heads">
					<?php foreach ( $wd_types as $wd_k => $wd_ty ) : ?>
						<div class="join-head join-head--<?php echo esc_attr( $wd_k ); ?><?php echo $wd_k === $wd_as ? ' is-on' : ''; ?>"><h1><?php echo esc_html( $wd_ty[2] ); ?></h1><p class="auth__sub"><?php echo esc_html( $wd_ty[3] ); ?></p></div>
					<?php endforeach; ?>
				</div>
				<form class="form form--join" method="post">
					<?php wp_nonce_field( 'wd_auth_join' ); ?>
					<input type="hidden" name="wd_auth" value="join"><input type="hidden" name="redirect_to" value="<?php echo esc_attr( $wd_to ); ?>">
					<fieldset class="join-as">
						<legend><?php esc_html_e( 'I want to', 'wwmd' ); ?></legend>
						<?php foreach ( $wd_types as $wd_k => $wd_ty ) : ?>
							<label class="join-as__opt"><input type="radio" name="as" value="<?php echo esc_attr( $wd_k ); ?>" <?php checked( $wd_as, $wd_k ); ?>><span><?php echo wd_icon( $wd_ty[0], 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><b><?php echo esc_html( $wd_ty[1] ); ?></b></span></label>
						<?php endforeach; ?>
					</fieldset>
					<div class="field-row">
						<label class="field"><span><?php esc_html_e( 'First name', 'wwmd' ); ?></span><input name="first_name" autocomplete="given-name" required></label>
						<label class="field"><span><?php esc_html_e( 'Last name', 'wwmd' ); ?></span><input name="last_name" autocomplete="family-name"></label>
					</div>
					<label class="field"><span><?php esc_html_e( 'Email', 'wwmd' ); ?></span><input name="email" type="email" autocomplete="email" required></label>
					<label class="field"><span><?php esc_html_e( 'WhatsApp number', 'wwmd' ); ?></span><input name="phone" type="tel" autocomplete="tel" placeholder="+234…"></label>
					<label class="field"><span><?php esc_html_e( 'Password (8+ characters)', 'wwmd' ); ?></span><input name="pwd" type="password" minlength="8" autocomplete="new-password" required></label>
					<button class="btn btn--ink btn--block btn--lg" type="submit"><?php esc_html_e( 'Create my account', 'wwmd' ); ?></button>
					<p class="fine"><?php printf( wp_kses( /* translators: 1: terms url 2: privacy url */ __( 'By signing up you agree to our <a href="%1$s">terms</a> & <a href="%2$s">privacy policy</a>.', 'wwmd' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( wd_url( 'terms' ) ), esc_url( wd_url( 'privacy' ) ) ); ?></p>
				</form>
			<?php elseif ( 'forgot' === $wd_tab ) : ?>
				<h1><?php esc_html_e( 'Reset your password', 'wwmd' ); ?></h1>
				<p class="auth__sub"><?php esc_html_e( 'Enter your email & we will send you a link.', 'wwmd' ); ?></p>
				<form class="form" method="post">
					<?php wp_nonce_field( 'wd_auth_forgot' ); ?>
					<input type="hidden" name="wd_auth" value="forgot">
					<label class="field"><span><?php esc_html_e( 'Email', 'wwmd' ); ?></span><input name="log" type="email" autocomplete="email" required></label>
					<button class="btn btn--ink btn--block btn--lg" type="submit"><?php esc_html_e( 'Send reset link', 'wwmd' ); ?></button>
					<p><a href="<?php echo esc_url( wd_url( 'sign-in' ) ); ?>"><?php esc_html_e( 'Back to sign in', 'wwmd' ); ?></a></p>
				</form>
			<?php else : ?>
				<h1><?php esc_html_e( 'Choose a new password', 'wwmd' ); ?></h1>
				<form class="form" method="post">
					<?php wp_nonce_field( 'wd_auth_reset' ); ?>
					<input type="hidden" name="wd_auth" value="reset"><input type="hidden" name="key" value="<?php echo esc_attr( $wd_key ? $wd_key : sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>"><input type="hidden" name="login" value="<?php echo esc_attr( $wd_log ? $wd_log : sanitize_user( wp_unslash( $_POST['login'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>">
					<label class="field"><span><?php esc_html_e( 'New password (8+ characters)', 'wwmd' ); ?></span><input name="pwd" type="password" minlength="8" autocomplete="new-password" required></label>
					<button class="btn btn--ink btn--block btn--lg" type="submit"><?php esc_html_e( 'Save & sign in', 'wwmd' ); ?></button>
				</form>
			<?php endif; ?>
			<p class="auth__help"><?php echo wd_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><a href="<?php echo esc_url( wd_whatsapp( __( 'Hello! I need help with my account.', 'wwmd' ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Need help? Chat with us', 'wwmd' ); ?></a></p>
		</div>
	</div>
</section>
<?php
get_footer();
