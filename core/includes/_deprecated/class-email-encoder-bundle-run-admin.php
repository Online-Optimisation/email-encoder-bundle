<?php

/**
 * Class Email_Encoder_Run
 *
 * Thats where we bring the plugin to life
 *
 * @since 2.0.0
 * @package EEB
 * @author Ironikus <info@ironikus.com>
 */

class Email_Encoder_Run{

	private string $page_name;
	private string $page_title;
	private string $pagehook;
	private string $settings_key;
	private array $display_notices = [];

	/**
	 * Our Email_Encoder_Run constructor.
	 */
	function __construct(){
		$this->page_name    = EEB()->settings->get_page_name();
		$this->page_title   = EEB()->settings->get_page_title();
		$this->settings_key = EEB()->settings->get_settings_key();
		// $this->display_notices = array();
		$this->add_hooks();
	}

	/**
	 * Define all of our necessary hooks
	 */
	private function add_hooks(){

		add_action( 'plugin_action_links_' . EEB_PLUGIN_BASE, array($this, 'plugin_action_links') );
		add_action( 'admin_enqueue_scripts',    array( $this, 'enqueue_scripts_and_styles' ) );
		add_action( 'admin_menu', array( $this, 'add_user_submenu' ), 150 );
		add_action( 'admin_init', array( $this, 'save_settings' ), 10 );
	}

	/**
	 * Plugin action links.
	 *
	 * Adds action links to the plugin list table
	 *
	 * Fired by `plugin_action_links` filter.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param array $links An array of plugin action links.
	 *
	 * @return array An array of plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . $this->page_name ), __( 'Settings', 'email-encoder-bundle' ) );

		array_unshift( $links, $settings_link );

		$links['visit_us'] = sprintf( '<a href="%s" target="_blank" style="font-weight:700;color:#f1592a;">%s</a>', 'https://wpemailencoder.com/?utm_source=email-encoder-bundle&utm_medium=plugin-overview-website-button&utm_campaign=WP%20Mailto%20Links', __('Visit us', 'email-encoder-bundle') );

		return $links;
	}

	/**
	 * ######################
	 * ###
	 * #### SCRIPTS & STYLES
	 * ###
	 * ######################
	 */

	/**
	 * Register all necessary scripts and styles
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts_and_styles() {
		if( EEB()->helpers->is_page( $this->page_name ) ) {
			$js_version  = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/js/custom-admin.js' ));
			$css_version = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/css/style-admin.css' ));

			wp_enqueue_script( 'eeb-admin-scripts', EEB_PLUGIN_URL . 'core/includes/assets/js/custom-admin.js', array( 'jquery' ), $js_version, true );
			wp_register_style( 'eeb-css-backend',    EEB_PLUGIN_URL . 'core/includes/assets/css/style-admin.css', false, $css_version );
			wp_enqueue_style ( 'eeb-css-backend' );
		}
	}

	/**
	 * ######################
	 * ###
	 * #### MENU TEMPLATE ITEMS
	 * ###
	 * ######################
	 */

	/**
	 * Add our custom admin user page
	 */
	public function add_user_submenu(){

		if( (string) EEB()->settings->get_setting( 'own_admin_menu', true ) !== '1' ){
			$this->pagehook = add_submenu_page( 'options-general.php', __( $this->page_title, 'email-encoder-bundle' ), __( $this->page_title, 'email-encoder-bundle' ), EEB()->settings->get_admin_cap( 'admin-add-submenu-page-item' ), $this->page_name, array( $this, 'render_admin_menu_page' ) );
		} else {
			$this->pagehook = add_menu_page( __( $this->page_title, 'email-encoder-bundle' ), __( $this->page_title, 'email-encoder-bundle' ), EEB()->settings->get_admin_cap( 'admin-add-menu-page-item' ), $this->page_name, array( $this, 'render_admin_menu_page' ), plugins_url( 'core/includes/assets/img/icon-email-encoder-bundle.png', EEB_PLUGIN_FILE ) );
		}

		add_action( 'load-' . $this->pagehook, array( $this, 'add_help_tabs' ) );
	}

	/**
	 * Render the admin submenu page
	 *
	 * You need the specified capability to edit it.
	 */
	public function render_admin_menu_page(){
		if( ! current_user_can( EEB()->settings->get_admin_cap('admin-menu-page') ) ){
			wp_die( __( EEB()->settings->get_default_string( 'insufficient-permissions' ), 'email-encoder-bundle' ) );
		}

		include( EEB_PLUGIN_DIR . 'core/includes/partials/eeb-page-display.php' );

	}

	/**
	 * ######################
	 * ###
	 * #### SETTINGS LOGIC
	 * ###
	 * ######################
	 */

	 public function save_settings(){

		if( isset( $_POST[ $this->page_name . '_nonce' ] ) ){
			if( ! wp_verify_nonce( $_POST[ $this->page_name . '_nonce' ], $this->page_name ) ){
				wp_die( __( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
			}

			if( ! current_user_can( EEB()->settings->get_admin_cap( 'admin-update-settings' ) ) ){
				wp_die( __( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
			}

			if( isset( $_POST[ $this->settings_key ] ) && is_array( $_POST[ $this->settings_key ] ) ){

				//Strip duplicate slashes before saving
				foreach( $_POST[ $this->settings_key ] as $k => $v ){
					if( is_string( $v ) ){
						$_POST[ $this->settings_key ][ $k ] = stripslashes( $v );
					}
				}

				$check = update_option( $this->settings_key, $_POST[ $this->settings_key ] );
				if( $check ){
					EEB()->settings->reload_settings();
					$update_notice = EEB()->helpers->create_admin_notice( 'Settings successfully saved.', 'success', true );
					$this->display_notices[] = $update_notice;
				} else {
					$update_notice = EEB()->helpers->create_admin_notice( 'No changes were made to your settings with your last save.', 'info', true );
					$this->display_notices[] = $update_notice;
				}
			}

		}

	 }

	/**
	 * ######################
	 * ###
	 * #### HELP TABS TEMPLATE ITEMS
	 * ###
	 * ######################
	 */
	public function add_help_tabs(){
		$screen = get_current_screen();

		$defaults = array(
			'content'   => '',
			'callback'  => array( $this, 'load_help_tabs' ),
		);

		$screen->add_help_tab(wp_parse_args(array(
			'id'        => 'general',
			'title'     => __('General', 'email-encoder-bundle'),
		), $defaults));

		$screen->add_help_tab(wp_parse_args(array(
			'id'        => 'shortcodes',
			'title'     => __('Shortcode', 'email-encoder-bundle'),
		), $defaults));

		$screen->add_help_tab(wp_parse_args(array(
			'id'        => 'template-tags',
			'title'     => __('Template Tags', 'email-encoder-bundle'),
		), $defaults));

		if( EEB()->helpers->is_page( $this->page_name ) ){
			add_meta_box( 'encode_form', __( $this->page_title, 'email-encoder-bundle' ), array( $this, 'show_meta_box_content' ), null, 'normal', 'core', array( 'encode_form' ) );
		}

	}

	public function load_help_tabs($screen, array $args){

		if( ! empty( $args['id'] ) ){
			include( EEB_PLUGIN_DIR . 'core/includes/partials/help-tabs/' . $args['id'] . '.php' );
		}

	}

	/**
	 * Show content of metabox (callback)
	 * @param array $post
	 * @param array $meta_box
	 */
	public function show_meta_box_content( $post, $meta_box ) {
		$key = $meta_box['args'][0];

		if ($key === 'encode_form') {
			?>
			<p><?php _e('If you like you can also create you own secured emails manually with this form. Just copy/paste the generated code and put it in your post, page or template. We choose automatically the best method for you, based on your settings.', 'email-encoder-bundle') ?></p>

			<hr style="border:1px solid #FFF; border-top:1px solid #EEE;" />

			<?php echo EEB()->validate->get_encoder_form(); ?>

			<hr style="border:1px solid #FFF; border-top:1px solid #EEE;"/>

			<?php

			$form_frontend = (bool) EEB()->settings->get_setting( 'encoder_form_frontend', true, 'encoder_form' );
			if( $form_frontend ){
				?>
					<p class="description"><?php _e('You can also put the encoder form on your site by using the shortcode <code>[eeb_form]</code> or the template function <code>eeb_form()</code>.', 'email-encoder-bundle') ?></p>
				<?php
			} else {
				?>
					<p class="description"><?php _e('In case you want to display the Email Encoder form within the frontend, you can activate it inside of the Advanced settings.', 'email-encoder-bundle') ?></p>
				<?php
			}
		}

	}

}
