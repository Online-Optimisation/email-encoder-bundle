<?php

namespace OnlineOptimisation\EmailEncoderBundle\Front;

use OnlineOptimisation\EmailEncoderBundle\Traits\PluginHelper;

class FrontCore
{
    use PluginHelper;

    private int $protection_method;
    private bool $protect_shortcode_tags;
    private bool $protect_using;


    public function boot(): void {

        add_action( 'init', [ $this, 'register' ] );
    }


    public function register(): void {
		$this->protection_method      = (int) $this->getSetting( 'protect', true );
		$this->protect_shortcode_tags = $this->getSettingBool( 'protect_shortcode_tags', true, 'filter_body' );

		$hook_name = (bool) $this->getSetting( 'filter_hook', true, 'filter_body' ) ? 'init' : 'wp';

        add_action( $hook_name, [ $this, 'register_hooks' ], 100 );
        add_action( $hook_name, [ $this, 'register_rss_hooks' ], 100 );
    }


    public function register_rss_hooks(): void {

        if ( !is_feed() ) {
            return;
        }

		$filter_rss             = $this->getSettingBool( 'filter_rss', true, 'filter_body' );
		$remove_shortcodes_rss  = $this->getSettingBool( 'remove_shortcodes_rss', true, 'filter_body' );

        if ( $filter_rss ) {
            add_filter( $this->getFinalOutputBufferHook(), [ $this, 'filter_rss' ], 100 );
        }

        if ( $remove_shortcodes_rss ) {
            add_filter( $this->getFinalOutputBufferHook(), [ $this, 'remove_shortcodes' ], 100 );
        }

    }


	public function register_hooks(): void {

        $exit_early = $this->protection_method === $this->settings()::PROTECT_DISABLED
            || $this->isQueryParameterExcluded()
            || $this->isPostExcluded()
        ;

		if ( $exit_early ) {
			return;
		}


		if ( $this->protection_method === $this->settings()::PROTECT_FILTERS_ONLY ) {

			$filter_hooks = [
				'the_title',
				'the_content',
				'the_excerpt',
				'get_the_excerpt',

				//Comment related
				'comment_text',
				'comment_excerpt',
				'comment_url',
				'get_comment_author_url',
				'get_comment_author_url_link',

				//Widgets
				'widget_title',
				'widget_text',
				'widget_content',
				'widget_output',
			];

			$filter_hooks = apply_filters( 'eeb/frontend/wordpress_filters', $filter_hooks );

			foreach ( $filter_hooks as $hook ) {
			   add_filter( $hook, [ $this, 'filter_content' ], 100 );
			}
		}
        elseif ( $this->protection_method === $this->settings()::PROTECT_FULL_PAGE ) {

			add_filter( $this->getFinalOutputBufferHook(), [ $this, 'filter_page' ], 100 );
		}

		if ( $this->protect_shortcode_tags ) {
            add_filter( 'do_shortcode_tag', [ $this, 'filter_content' ], 10 );
		}

	}


	public function filter_page( string $content ): string {

		$protect_using = (string) $this->getSetting( 'protect_using', true );
		return $this->filterPage( $content, $protect_using );
	}


	public function filter_content( string $content ): string {

		$protect_using = (string) $this->getSetting( 'protect_using', true );
		return $this->filterContent( $content, $protect_using );
	}


	public function filter_rss( string $content ): string {

		$protection_type = (string) $this->getSetting( 'protect_using', true );
		return $this->validate()->filters->filter_rss( $content, $protection_type );
	}


    // strip shortcodes like [eeb_content], [eeb_form]
	public function remove_shortcodes( string $content ): string {

		return strip_shortcodes( $content );
	}

}
