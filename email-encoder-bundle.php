<?php
 /**
 * Plugin Name:    Email Encoder - Protect Email Addresses
 * Version:        2.2.5
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
defined( 'ABSPATH' ) || exit;

define( 'EEB_NAME',           'Email Encoder' );
define( 'EEB_VERSION',        '2.2.5' );
define( 'EEB_SETUP',          true );
define( 'EEB_PLUGIN_FILE',    __FILE__ );
define( 'EEB_PLUGIN_BASE',    plugin_basename( EEB_PLUGIN_FILE ) );
define( 'EEB_PLUGIN_DIR',     plugin_dir_path( EEB_PLUGIN_FILE ) );
define( 'EEB_PLUGIN_URL',     plugin_dir_url( EEB_PLUGIN_FILE ) );
define( 'EEB_TEXTDOMAIN',     'email-encoder-bundle' );

add_action( 'eeb_ready', fn () => error_log( '--> HELLO! <--' ), 9999 );

# Load the main instance for our core functions
require_once EEB_PLUGIN_DIR . 'core/class-email-encoder-bundle.php';
require_once EEB_PLUGIN_DIR . 'core/includes/functions/template-tags.php';

# COMPOSER AUTOLOAD
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require __DIR__ . '/vendor/autoload.php';
}

# RUN IT
add_action( 'plugins_loaded', 'EEB' );


/**
 * The main function to load the only instance
 * of our master class.
 *
 * @return object|Email_Encoder
 */
function EEB() {

	return \Legacy\EmailEncoderBundle\Email_Encoder::instance();
}
