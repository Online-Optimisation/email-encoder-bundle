<?php
 /**
 * Plugin Name:    Email Encoder - Protect Email Addresses
 * Version:        2.2.4
 * Plugin URI:     https://wordpress.org/plugins/email-encoder-bundle/
 * Description:    Protect email addresses on your site and hide them from spambots. Easy to use & flexible.
 * Author:         Online Optimisation
 * Author URI:     https://wpemailencoder.com/
 * License:        Dual licensed under the MIT and GPL licenses
 * Text Domain:    email-encoder-bundle
 *
 * License: GPL2
 *
 * You should have received a copy of the GNU General Public License
 * along with TMG User Filter. If not, see <http://www.gnu.org/licenses/>.
 */

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

// Plugin name.
define( 'EEB_NAME',           'Email Encoder' );

// Plugin version.
define( 'EEB_VERSION',        '2.2.4' );

// Determines if the plugin is loaded
define( 'EEB_SETUP',          true );

// Plugin Root File.
define( 'EEB_PLUGIN_FILE',    __FILE__ );

// Plugin base.
define( 'EEB_PLUGIN_BASE',    plugin_basename( EEB_PLUGIN_FILE ) );

// Plugin Folder Path.
define( 'EEB_PLUGIN_DIR',     plugin_dir_path( EEB_PLUGIN_FILE ) );

// Plugin Folder URL.
define( 'EEB_PLUGIN_URL',     plugin_dir_url( EEB_PLUGIN_FILE ) );

// Plugin Root File.
define( 'EEB_TEXTDOMAIN',     'email-encoder-bundle' );

/**
 * Load the main instance for our core functions
 */
require_once EEB_PLUGIN_DIR . 'core/class-email-encoder-bundle.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @return object|Email_Encoder
 */
function EEB() {
	return Email_Encoder::instance();
}

EEB();
