<?php

if( ! function_exists( 'eeb_form' ) ) {
    /**
     * @param mixed ...$args
     */
    function eeb_form( ...$args ): string {
        return EEB()->functions->eeb_form( ...$args );
    }
}

if( ! function_exists( 'eeb_email' ) ) {
    /**
     * @param mixed ...$args
     */
	function eeb_email( ...$args ): string {
		return eeb_mailto( ...$args );
	}
}

if ( ! function_exists( 'eeb_mailto' ) ) {
    /**
     * @param mixed ...$args
     */
    function eeb_mailto( ...$args ): string {
		return EEB()->functions->eeb_mailto( ...$args );
    }
}

if( ! function_exists( 'eeb_content' ) ) {
    /**
     * @param mixed ...$args
     */
	function eeb_content( ...$args ): string {
		return eeb_protect_content( ...$args );
	}
}

if ( ! function_exists( 'eeb_protect_content' ) ) {
    /**
     * @param mixed ...$args
     */
    function eeb_protect_content( ...$args ): string {
		return EEB()->functions->eeb_protect_content( ...$args );
    }
}


if ( ! function_exists( 'eeb_email_filter' ) ) {
    /**
     * @param mixed ...$args
     */
	function eeb_email_filter( ...$args ): string {
		return eeb_protect_emails( ...$args );
	}
}

if ( ! function_exists( 'eeb_protect_emails' ) ) {
    /**
     * @param mixed ...$args
     */
    function eeb_protect_emails( ...$args ): string {
		return EEB()->functions->eeb_protect_emails( ...$args );
    }
}
