<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Email_Encoder_Integration_Hive_Press' ) ) {

    /**
     * Class Email_Encoder_Integration_Divi
     *
     * This class integrates support for the divi themes https://www.elegantthemes.com/gallery/divi/
     *
     * @since 2.0.0
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_Hive_Press {

        /**
         * Our Email_Encoder_Run constructor.
         */
        function __construct() {
            $this->add_hooks();
        }

        /**
         * Define all of our necessary hooks
         */
        private function add_hooks() {
            add_filter( 'eeb/settings/fields', array( $this, 'deactivate_logic' ), 10 );
        }

        /**
         * ######################
         * ###
         * #### HELPERS
         * ###
         * ######################
         */

        public function is_active() {
            return defined( 'HP_FILE' );
        }

        /**
         * ######################
         * ###
         * #### SCRIPTS & STYLES
         * ###
         * ######################
         */

        public function deactivate_logic( $fields ) {

            if ( $this->is_active() ) {
                $uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';

                if ( preg_match( '#/account/listings/(\d+)/?$#', $uri ) ) {
                    if ( is_array( $fields ) ) {
                        if ( isset( $fields[ 'protect' ] ) ) {
                            if ( isset( $fields[ 'protect' ]['value'] ) ) {
                                $fields[ 'protect' ]['value'] = 3;
                            }
                        }
                    }
                }
            }

            return $fields;

        }


    }

    new Email_Encoder_Integration_Hive_Press();
}
