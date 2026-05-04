<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mulsitie_slug = ( is_multisite() ) ? 'network/' : '';

?>

<div class="wrap eeb-wrap">

	<div class="eeb-page-header">
		<div class="eeb-brand">
			<div class="eeb-brand-logo">
				<img src="<?php echo esc_url( EEB_PLUGIN_URL . 'assets/img/icon-email-encoder.svg' ); ?>" alt="" />
			</div>
			<div class="eeb-brand-meta">
				<h1>
					<?php esc_html_e( 'Email Encoder', 'email-encoder-bundle' ); ?>
					<span class="eeb-brand-version">v<?php echo esc_html( EEB_VERSION ); ?></span>
				</h1>
				<p><?php esc_html_e( 'by EDMON', 'email-encoder-bundle' ); ?></p>
			</div>
		</div>

		<?php $support_text = ( new \OnlineOptimisation\EmailEncoderBundle\Admin\SupportExport() )->generate(); ?>
		<div class="eeb-header-actions">
			<a href="https://wpemailencoder.com/email-protection-checker/"
			   target="_blank" rel="noopener"
			   class="eeb-action-btn">
				<span class="dashicons dashicons-search"></span>
				<span class="eeb-tooltip-text"><?php esc_html_e( 'Email Protection Checker', 'email-encoder-bundle' ); ?></span>
			</a>
			<a href="https://www.wpemailencoder.com/help-files/"
			   target="_blank" rel="noopener"
			   class="eeb-action-btn">
				<span class="dashicons dashicons-book"></span>
				<span class="eeb-tooltip-text"><?php esc_html_e( 'Documentation', 'email-encoder-bundle' ); ?></span>
			</a>
			<a href="http://wordpress.org/support/plugin/email-encoder-bundle#postform"
			   target="_blank" rel="noopener"
			   class="eeb-action-btn">
				<span class="dashicons dashicons-welcome-comments"></span>
				<span class="eeb-tooltip-text"><?php esc_html_e( 'Report a Problem', 'email-encoder-bundle' ); ?></span>
			</a>
			<a href="#"
			   id="eeb-copy-support-info"
			   class="eeb-action-btn"
			   data-support-text="<?php echo esc_attr( $support_text ); ?>">
				<span class="dashicons dashicons-clipboard"></span>
				<span class="eeb-tooltip-text"><?php esc_html_e( 'Copy Support Info', 'email-encoder-bundle' ); ?></span>
			</a>
			<a href="http://wordpress.org/support/view/plugin-reviews/email-encoder-bundle"
			   target="_blank" rel="noopener"
			   class="eeb-action-btn eeb-action-btn--review">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="eeb-tooltip-text"><?php esc_html_e( 'Leave a review', 'email-encoder-bundle' ); ?></span>
			</a>
		</div>
	</div>

	<?php
	// .wp-header-end is the WP-core marker that stops `common.js` from
	// hoisting `.notice` elements next to the first <h1> on the page —
	// which was injecting save banners into our header.
	?>
	<div class="wp-header-end"></div>

	<?php if ( ! empty( $display_notices ) ) :
		// Render save banners as a Divi-style centered toast (icon-only)
		// instead of a top-of-page strip. Detect status from the helper's
		// `notice notice-{type}` class so the icon + tint match.
		$first_notice = (string) reset( $display_notices );
		$toast_status = 'info';
		if ( strpos( $first_notice, 'notice-success' ) !== false ) {
			$toast_status = 'success';
		} elseif ( strpos( $first_notice, 'notice-error' ) !== false ) {
			$toast_status = 'error';
		} elseif ( strpos( $first_notice, 'notice-warning' ) !== false ) {
			$toast_status = 'warning';
		}
		$toast_icons = [
			'success' => 'yes',
			'error'   => 'no-alt',
			'warning' => 'warning',
			'info'    => 'info',
		];
	?>
		<div class="eeb-save-toast" data-status="<?php echo esc_attr( $toast_status ); ?>" role="status" aria-live="polite">
			<span class="dashicons dashicons-<?php echo esc_attr( $toast_icons[ $toast_status ] ); ?>"></span>
		</div>
	<?php endif; ?>

	<form method="post" action="">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- settings_fields() is a WordPress core function that outputs safe HTML.
		settings_fields( $this->getPageName() );
		?>
		<input type="hidden" name="<?php echo esc_attr( $this->getPageName() ); ?>_nonce" value="<?php echo esc_attr( wp_create_nonce( $this->getPageName() ) ); ?>">

		<div class="eeb-layout">
			<div class="eeb-main">
				<?php include( 'widgets/main.php' ); ?>
			</div>
		</div>
	</form>

</div>
