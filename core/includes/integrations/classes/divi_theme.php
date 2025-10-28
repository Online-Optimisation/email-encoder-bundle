<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Email_Encoder_Integration_Divi' ) ){

    /**
     * Class Email_Encoder_Integration_Divi
     *
     * This class integrates support for the divi themes https://www.elegantthemes.com/gallery/divi/
     *
     * @since 2.0.0
     * @package EEB
     * @author Ironikus <info@ironikus.com>
     */

    class Email_Encoder_Integration_Divi{

        /**
         * The main page name for our admin page
         *
         * @var string
         * @since 2.0.0
         */
        private $page_name;

        /**
         * The main page title for our admin page
         *
         * @var string
         * @since 2.0.0
         */
        private $page_title;

        /**
         * Our Email_Encoder_Run constructor.
         */
        function __construct(){
            $this->page_name    = EEB()->settings->get_page_name();
            $this->page_title   = EEB()->settings->get_page_title();
            $this->add_hooks();
        }

        /**
         * Define all of our necessary hooks
         */
        private function add_hooks(){
            add_filter( 'eeb/settings/fields', array( $this, 'deactivate_logic' ), 10 );
        }

        /**
         * ######################
         * ###
         * #### HELPERS
         * ###
         * ######################
         */

        public function is_divi_active(){
            return defined( 'ET_BUILDER_VERSION' );
        }

        /**
         * ######################
         * ###
         * #### SCRIPTS & STYLES
         * ###
         * ######################
         */

        public function deactivate_logic( $fields ){

            if ( $this->is_divi_active() ){
                if ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] == '1' ){
                    if ( is_array( $fields ) ){
                        if ( isset( $fields[ 'protect' ] ) ){
                            if ( isset( $fields[ 'protect' ]['value'] ) ){
                                $fields[ 'protect' ]['value'] = 3;
                            }
                        }
                    }
                }
            }

            return $fields;

        }


    }

    new Email_Encoder_Integration_Divi();
}
