<?php

namespace OnlineOptimisation\EmailEncoderBundle\Admin;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class Admin
{
    use PluginHelper;

	private array $display_notices = [];


    public function boot(): void {
        $this->log( __METHOD__ );

        add_action( 'init', [ $this, 'register_hooks' ] );
    }

	# ADMIN METHODS ============================================================

	public function register_hooks() {

		$actions = [
			[ 'plugin_action_links_' . EEB_PLUGIN_BASE, 'plugin_action_links_admin', 20 ],
			[ 'admin_enqueue_scripts', 'enqueue_scripts_and_styles_admin', 20 ],
			[ 'admin_menu', 'add_user_submenu_admin', 150 ],
			[ 'admin_init', 'save_settings_admin', 10 ],
		];

		foreach ( $actions as [ $tag, $method, $priority ] ) {
			add_action( $tag, [ $this, $method ], $priority );
		}
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
	public function plugin_action_links_admin( $links ) {
		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . $this->getPageName() ), __( 'Settings', 'email-encoder-bundle' ) );

		array_unshift( $links, $settings_link );

		$links['visit_us'] = sprintf( '<a href="%s" target="_blank" style="font-weight:700;color:#f1592a;">%s</a>', 'https://wpemailencoder.com/?utm_source=email-encoder-bundle&utm_medium=plugin-overview-website-button&utm_campaign=WP%20Mailto%20Links', __('Visit us', 'email-encoder-bundle') );

		return $links;
	}

	/**
	 * Register all necessary scripts and styles
	 *
	 * @since    2.0.0
	 */
	public function enqueue_scripts_and_styles_admin() {
		if ( $this->helper()->is_page( $this->getPageName() ) ) {
			$js_version  = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/js/custom-admin.js' ));
			$css_version = date( "ymd-Gis", filemtime( EEB_PLUGIN_DIR . 'core/includes/assets/css/style-admin.css' ));

			wp_enqueue_script( 'eeb-admin-scripts', EEB_PLUGIN_URL . 'core/includes/assets/js/custom-admin.js', array( 'jquery' ), $js_version, true );
			wp_register_style( 'eeb-css-backend',    EEB_PLUGIN_URL . 'core/includes/assets/css/style-admin.css', false, $css_version );
			wp_enqueue_style ( 'eeb-css-backend' );
		}
	}

	/**
	 * Add our custom admin user page
	 */
	public function add_user_submenu_admin() {

		if ( (string) $this->getSetting( 'own_admin_menu', true ) !== '1' ) {
			$pagehook = add_submenu_page( 'options-general.php', __( $this->getPageTitle(), 'email-encoder-bundle' ), __( $this->getPageTitle(), 'email-encoder-bundle' ), $this->getAdminCap( 'admin-add-submenu-page-item' ), $this->getPageName(), array( $this, 'render_admin_menu_page' ) );
		} else {
			$pagehook = add_menu_page( __( $this->getPageTitle(), 'email-encoder-bundle' ), __( $this->getPageTitle(), 'email-encoder-bundle' ), $this->getAdminCap( 'admin-add-menu-page-item' ), $this->getPageName(), array( $this, 'render_admin_menu_page' ), plugins_url( 'core/includes/assets/img/icon-email-encoder-bundle.png', EEB_PLUGIN_FILE ) );
		}

		add_action( 'load-' . $pagehook, array( $this, 'add_help_tabs' ) );
	}


	public function save_settings_admin() {

		if ( isset( $_POST[ $this->getPageName() . '_nonce' ] ) ) {
			if ( ! wp_verify_nonce( $_POST[ $this->getPageName() . '_nonce' ], $this->getPageName() ) ) {
				wp_die( __( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
			}

			if ( ! current_user_can( $this->getAdminCap( 'admin-update-settings' ) ) ) {
				wp_die( __( 'You don\'t have permission to update these settings.', 'email-encoder-bundle' ) );
			}

			if ( isset( $_POST[ $this->getSettingsKey() ] ) && is_array( $_POST[ $this->getSettingsKey() ] ) ) {

				//Strip duplicate slashes before saving
				foreach( $_POST[ $this->getSettingsKey() ] as $k => $v ) {
					if ( is_string( $v ) ) {
						$_POST[ $this->getSettingsKey() ][ $k ] = stripslashes( $v );
					}
				}

				$check = update_option( $this->getSettingsKey(), $_POST[ $this->getSettingsKey() ] );
				if ( $check ) {
					$this->reloadSettings();
					$update_notice = $this->helper()->create_admin_notice( 'Settings successfully saved.', 'success', true );
					$this->display_notices[] = $update_notice;
				} else {
					$update_notice = $this->helper()->create_admin_notice( 'No changes were made to your settings with your last save.', 'info', true );
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
	public function add_help_tabs() {
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

		if ( $this->helper()->is_page( $this->getPageName() ) ) {
			add_meta_box( 'encode_form', __( $this->getPageTitle(), 'email-encoder-bundle' ), array( $this, 'show_meta_box_content' ), null, 'normal', 'core', array( 'encode_form' ) );
		}

	}

	public function load_help_tabs( \WP_Screen $screen, array $args ) {

		if ( empty( $args['id'] ) ) {
            return;
        }

        $allowed_attr_html = $this->getSafeHtmlAttr();

        include EEB_PLUGIN_DIR . 'core/includes/partials/help-tabs/' . $args['id'] . '.php';

	}

	/**
	 * Show content of metabox (callback)
	 * @param array $post
	 * @param array $meta_box
	 */
	public function show_meta_box_content( $post, $meta_box ) {
		$key = $meta_box['args'][0];

		if ( $key === 'encode_form' ) {
			?>
			<p><?php _e('If you like you can also create you own secured emails manually with this form. Just copy/paste the generated code and put it in your post, page or template. We choose automatically the best method for you, based on your settings.', 'email-encoder-bundle') ?></p>

			<hr style="border:1px solid #FFF; border-top:1px solid #EEE;" />

			<?php echo $this->validate()->get_encoder_form(); ?>

			<hr style="border:1px solid #FFF; border-top:1px solid #EEE;"/>

			<?php

			$form_frontend = (bool) $this->getSetting( 'encoder_form_frontend', true, 'encoder_form' );
			if ( $form_frontend ) {
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

	/**
	 * Render the admin submenu page
	 *
	 * You need the specified capability to edit it.
	 */
	public function render_admin_menu_page() {
		if ( ! current_user_can( $this->getAdminCap('admin-menu-page') ) ) {
			wp_die( __( $this->settings()->get_default_string( 'insufficient-permissions' ), 'email-encoder-bundle' ) );
		}

		include( EEB_PLUGIN_DIR . 'core/includes/partials/eeb-page-display.php' );

	}
}
