<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$settings = $this->getSetting();

// Tab persistence via URL: ?tab=advanced. Falls back to General.
$requested_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
$active_tab    = $requested_tab; // validated against $tabs after the array is built below.

$tabs = [
	'general' => [
		'label'       => __( 'General', 'email-encoder-bundle' ),
		'icon'        => 'dashicons-shield',
		'description' => __( 'Configure how email addresses are protected on your site.', 'email-encoder-bundle' ),
		'settings'    => [ 'protect', 'protect_using' ],
	],
	'filters' => [
		'label'       => __( 'Filters', 'email-encoder-bundle' ),
		'icon'        => 'dashicons-filter',
		'description' => __( 'Choose what content types and contexts to apply protection to.', 'email-encoder-bundle' ),
		'settings'    => [ 'filter_body' ],
	],
	'images' => [
		'label'       => __( 'Images', 'email-encoder-bundle' ),
		'icon'        => 'dashicons-format-image',
		'description' => __( 'Customize the appearance of email addresses converted to PNG images.', 'email-encoder-bundle' ),
		'settings'    => [ 'image_settings' ],
	],
	'exclusions' => [
		'label'       => __( 'Exclusions', 'email-encoder-bundle' ),
		'icon'        => 'dashicons-hidden',
		'description' => __( 'Choose what content to leave untouched by the encoder.', 'email-encoder-bundle' ),
		'settings'    => [ 'skip_posts', 'skip_query_parameters' ],
	],
	'advanced' => [
		'label'       => __( 'Advanced', 'email-encoder-bundle' ),
		'icon'        => 'dashicons-admin-tools',
		'description' => __( 'Fine-tune plugin behavior and display options.', 'email-encoder-bundle' ),
		'settings'    => [ 'show_encoded_check', 'protection_text', 'class_name', 'custom_href_attr', 'footer_scripts', 'own_admin_menu' ],
	],
	'tools' => [
		'label'       => __( 'Tools', 'email-encoder-bundle' ),
		'icon'        => 'dashicons-editor-code',
		'description' => __( 'Encoder form and developer tools.', 'email-encoder-bundle' ),
		'settings'    => [],
	],
	'help' => [
		'label'       => __( 'Help', 'email-encoder-bundle' ),
		'icon'        => 'dashicons-editor-help',
		'description' => __( 'Plugin reference, shortcodes, and template tags.', 'email-encoder-bundle' ),
		'settings'    => [],
	],
];

if ( ! isset( $tabs[ $active_tab ] ) ) {
	$active_tab = 'general';
}

?>

<div class="eeb-settings-shell">

	<nav class="eeb-settings-nav">
		<?php foreach ( $tabs as $tab_key => $tab ) : ?>
			<a href="#eeb-tab-<?php echo esc_attr( $tab_key ); ?>"
			   class="eeb-tab<?php echo ( $tab_key === $active_tab ) ? ' eeb-tab-active' : ''; ?>"
			   data-tab="<?php echo esc_attr( $tab_key ); ?>">
				<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
				<span class="eeb-nav-label"><?php echo esc_html( $tab['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="eeb-settings-content">

	<?php foreach ( $tabs as $tab_key => $tab ) : ?>
	<div id="eeb-tab-<?php echo esc_attr( $tab_key ); ?>"
		 class="eeb-panel"
		 <?php echo ( $tab_key !== $active_tab ) ? 'style="display:none;"' : ''; ?>>

	<?php if ( $tab_key === 'tools' ) :
		$encoder_form = $this->getEncoderForm();
	?>
		<?php if ( ! empty( $encoder_form ) ) : ?>
		<div class="eeb-encoder-tool">
			<h3><?php esc_html_e( 'Manual Email Encoder', 'email-encoder-bundle' ); ?></h3>
			<p class="eeb-setting-sublabel"><?php esc_html_e( 'Create protected email links manually. Enter an email address, choose an encoding method, and copy the generated code into your post, page, or template.', 'email-encoder-bundle' ); ?></p>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $encoder_form contains intentional HTML form fields.
			echo $encoder_form;
			?>
			<div class="eeb-embed-callout">
				<span class="dashicons dashicons-info-outline"></span>
				<div class="eeb-embed-callout-body">
					<strong><?php esc_html_e( 'Embed this form on your site', 'email-encoder-bundle' ); ?></strong>
					<div class="eeb-embed-snippets">
						<span><?php esc_html_e( 'Shortcode:', 'email-encoder-bundle' ); ?> <code>[eeb_form]</code></span>
						<span><?php esc_html_e( 'Template tag:', 'email-encoder-bundle' ); ?> <code>&lt;?php eeb_form(); ?&gt;</code></span>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="eeb-settings-list">
		<?php foreach ( $tab['settings'] as $setting_name ) :
			if ( ! isset( $settings[ $setting_name ] ) ) {
				continue;
			}
			$setting = $settings[ $setting_name ];

			$main_settings_value = '';
			if ( isset( $setting['value'] ) ) {
				$main_settings_value = $setting['value'];
			}

			$is_checked = ( $setting['type'] == 'checkbox' && ( $main_settings_value === 1 || $main_settings_value === '1' ) ) ? 'checked' : '';
			$value = ( $setting['type'] != 'checkbox' && $setting['type'] != 'multi-input' ) ? $main_settings_value : '1';

			$setting_modifier = 'eeb-setting--' . ( $setting['type'] === 'multi-input' ? 'multi' : ( $setting['type'] === 'text' ? 'text' : 'bool' ) );
		?>
		<div class="eeb-setting <?php echo esc_attr( $setting_modifier ); ?>" data-setting="<?php echo esc_attr( $setting_name ); ?>">
			<div class="eeb-setting-header">
				<h4>
					<?php echo esc_html( $setting['title'] ); ?>
					<?php
						$tooltip = '';
						if ( isset( $setting['description'] ) && $setting['type'] !== 'multi-input' ) {
							$tooltip = $setting['description'];
						} elseif ( isset( $setting['label'] ) && $setting['type'] === 'multi-input' ) {
							$tooltip = $setting['label'];
						}
					?>
					<?php if ( ! empty( $tooltip ) ) : ?>
						<span class="eeb-tooltip" tabindex="0">
							<span class="dashicons dashicons-info-outline"></span>
							<span class="eeb-tooltip-text"><?php echo esc_html( wp_strip_all_tags( $tooltip ) ); ?></span>
						</span>
					<?php endif; ?>
				</h4>
				<?php if ( $setting['type'] !== 'multi-input' && isset( $setting['label'] ) ) : ?>
					<p class="eeb-setting-sublabel"><?php echo wp_kses_post( $setting['label'] ); ?></p>
				<?php endif; ?>
			</div>
			<div class="eeb-setting-body">
				<?php if ( $setting['type'] === 'multi-input' ) : ?>

					<?php foreach ( $setting['inputs'] as $si_key => $data ) :

						if ( $setting['input-type'] === 'radio' ) {
							$data['value'] = $si_key;
						}

						$mi_is_checked = ( $setting['input-type'] == 'checkbox' && ( isset( $data['value'] ) && ( $data['value'] === 1 || $data['value'] === '1' ) ) ) ? 'checked' : '';
						$mi_value = ( $setting['input-type'] != 'checkbox' ) ? $data['value'] : '1';
						$si_name = $si_key;

						if ( $setting['input-type'] == 'radio' ) {
							$si_name = $setting_name;
							if ( (string) $main_settings_value === (string) $data['value'] ) {
								$mi_is_checked = 'checked';
							}
						}
					?>
						<?php if ( $setting['input-type'] === 'text' ) :
							$text_id = esc_attr( $si_name . '_' . $si_key );
						?>
							<div class="eeb-setting eeb-setting--text">
								<div class="eeb-setting-header">
									<h4><label for="<?php echo $text_id; ?>"><?php echo wp_kses_post( $data['label'] ); ?></label></h4>
									<?php if ( isset( $data['description'] ) ) : ?>
										<p class="eeb-setting-sublabel"><?php echo wp_kses_post( $data['description'] ); ?></p>
									<?php endif; ?>
								</div>
								<div class="eeb-setting-body">
									<input id="<?php echo $text_id; ?>"
										   name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $si_name ); ?>]"
										   type="text"
										   class="eeb-input"
										   value="<?php echo esc_attr( $mi_value ); ?>"
										   <?php if ( ! empty( $data['placeholder'] ) ) : ?>placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>"<?php endif; ?> />
								</div>
							</div>
						<?php elseif ( $setting['input-type'] === 'radio' ) :
							$option_id = esc_attr( $si_name . '_' . $si_key );
							$icon = ! empty( $data['icon'] ) ? $data['icon'] : 'marker';
						?>
							<label class="eeb-radio-card" for="<?php echo $option_id; ?>">
								<input id="<?php echo $option_id; ?>"
									   class="eeb-radio-card-input"
									   name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $si_name ); ?>]"
									   type="radio"
									   value="<?php echo esc_attr( $mi_value ); ?>"
									   <?php echo esc_attr( $mi_is_checked ); ?> />
								<span class="eeb-radio-card-icon">
									<?php if ( $icon === 'shield-off' ) : ?>
										<svg class="eeb-radio-card-svg" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd" />
										</svg>
									<?php else : ?>
										<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
									<?php endif; ?>
								</span>
								<span class="eeb-radio-card-body">
									<strong class="eeb-radio-card-title"><?php echo wp_kses_post( $data['label'] ); ?></strong>
									<?php if ( ! empty( $data['description'] ) ) : ?>
										<span class="eeb-radio-card-desc"><?php echo wp_kses_post( $data['description'] ); ?></span>
									<?php endif; ?>
								</span>
							</label>
						<?php else :
							$option_id = esc_attr( $si_name . '_' . $si_key );
						?>
							<div class="eeb-option-row">
								<label for="<?php echo $option_id; ?>" class="eeb-option-label">
									<?php echo wp_kses_post( $data['label'] ); ?>
								</label>
								<?php if ( isset( $data['description'] ) ) : ?>
									<span class="eeb-tooltip" tabindex="0">
										<span class="dashicons dashicons-info-outline"></span>
										<span class="eeb-tooltip-text"><?php echo esc_html( wp_strip_all_tags( $data['description'] ) ); ?></span>
									</span>
								<?php endif; ?>
								<label class="eeb-option" for="<?php echo $option_id; ?>">
									<?php // Hidden zero submits when checkbox is unchecked, so the saved option always reflects the toggle state. ?>
									<input type="hidden" name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $si_name ); ?>]" value="0" />
									<input id="<?php echo $option_id; ?>"
										   name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $si_name ); ?>]"
										   type="checkbox"
										   value="<?php echo esc_attr( $mi_value ); ?>"
										   <?php echo esc_attr( $mi_is_checked ); ?> />
								</label>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>

				<?php elseif ( $setting['type'] === 'text' ) : ?>

					<input id="<?php echo esc_attr( $setting['id'] ); ?>"
						   name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $setting_name ); ?>]"
						   type="text"
						   class="eeb-input"
						   value="<?php echo esc_attr( $value ); ?>"
						   <?php if ( ! empty( $setting['placeholder'] ) ) : ?>placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>"<?php endif; ?> />

				<?php else : ?>

					<label class="eeb-option" for="<?php echo esc_attr( $setting['id'] ); ?>">
						<?php // Hidden zero submits when checkbox is unchecked, so the saved option always reflects the toggle state. ?>
						<?php if ( $setting['type'] === 'checkbox' ) : ?>
							<input type="hidden" name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $setting_name ); ?>]" value="0" />
						<?php endif; ?>
						<input id="<?php echo esc_attr( $setting['id'] ); ?>"
							   name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $setting_name ); ?>]"
							   type="<?php echo esc_attr( $setting['type'] ); ?>"
							   value="<?php echo esc_attr( $value ); ?>"
							   <?php echo esc_attr( $is_checked ); ?> />
					</label>

				<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $tab_key === 'help' ) : ?>
		<div class="eeb-help-content">
			<?php $allowed_attr_html = $this->getSafeHtmlAttr(); ?>
			<?php include EEB_PLUGIN_DIR . 'templates/help-tabs/general.php'; ?>
			<hr class="eeb-help-divider" />
			<?php include EEB_PLUGIN_DIR . 'templates/help-tabs/shortcodes.php'; ?>
			<hr class="eeb-help-divider" />
			<?php include EEB_PLUGIN_DIR . 'templates/help-tabs/template-tags.php'; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! in_array( $tab_key, [ 'help', 'tools' ], true ) ) : ?>
	<div class="eeb-panel-footer">
		<?php submit_button( __( 'Save Settings', 'email-encoder-bundle' ), 'primary eeb-save-btn', 'submit', false ); ?>
	</div>
	<?php endif; ?>

</div>
<?php endforeach; ?>

	</div>

</div>
