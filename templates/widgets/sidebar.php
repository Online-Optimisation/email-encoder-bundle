<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="eeb-sidebar-card">
	<h3><?php esc_html_e( 'Resources', 'email-encoder-bundle' ); ?></h3>
	<ul class="eeb-resource-list">
		<li>
			<a href="https://wpemailencoder.com/email-protection-checker/" target="_blank" rel="noopener">
				<span class="dashicons dashicons-search"></span>
				<?php esc_html_e( 'Email Protection Checker', 'email-encoder-bundle' ); ?>
			</a>
		</li>
		<li>
			<a href="https://www.wpemailencoder.com/help-files/" target="_blank" rel="noopener">
				<span class="dashicons dashicons-media-text"></span>
				<?php esc_html_e( 'Documentation', 'email-encoder-bundle' ); ?>
			</a>
		</li>
		<li>
			<a href="http://wordpress.org/support/plugin/email-encoder-bundle#postform" target="_blank" rel="noopener">
				<span class="dashicons dashicons-welcome-comments"></span>
				<?php esc_html_e( 'Report a Problem', 'email-encoder-bundle' ); ?>
			</a>
		</li>
		<li>
			<a href="http://wordpress.org/extend/plugins/email-encoder-bundle/faq/" target="_blank" rel="noopener">
				<span class="dashicons dashicons-editor-help"></span>
				<?php esc_html_e( 'FAQ', 'email-encoder-bundle' ); ?>
			</a>
		</li>
	</ul>
</div>

<div class="eeb-sidebar-card eeb-sidebar-rate">
	<span class="dashicons dashicons-star-filled"></span>
	<p>
		<?php esc_html_e( 'Enjoying this plugin?', 'email-encoder-bundle' ); ?>
		<a href="http://wordpress.org/support/view/plugin-reviews/email-encoder-bundle" target="_blank" rel="noopener">
			<strong><?php esc_html_e( 'Leave a review', 'email-encoder-bundle' ); ?></strong>
		</a>
	</p>
</div>

<?php $support_text = ( new \OnlineOptimisation\EmailEncoderBundle\Admin\SupportExport() )->generate(); ?>
<div class="eeb-sidebar-card eeb-sidebar-support">
	<span class="dashicons dashicons-clipboard"></span>
	<p>
		<a href="#" id="eeb-copy-support-info" data-support-text="<?php echo esc_attr( $support_text ); ?>">
			<strong><?php esc_html_e( 'Copy Support Info', 'email-encoder-bundle' ); ?></strong>
		</a>
	</p>
</div>
