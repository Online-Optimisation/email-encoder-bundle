<?php

return [

	'protect' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'protect',
		'type'        => 'multi-input',
		'input-type'  => 'radio',
		'title'       => __( 'Protect emails', 'email-encoder-bundle' ),
		'inputs' 	  => [
			1 => [
				'label' => __( 'Full-page scan', 'email-encoder-bundle' ),
				'description' => __('This will check the whole page against any mails and secures them.', 'email-encoder-bundle' )
			],
			2 => [
				'label' => __( 'Wordpress filters', 'email-encoder-bundle' ),
				'description' => __('Secure only mails that occur within WordPress filters. (Not recommended)', 'email-encoder-bundle' ),
				'advanced' 	  => true,
			],
			3 => [
				'label' => __( 'Don\'t do anything.', 'email-encoder-bundle' ),
				'description' => __('This turns off the protection for emails. (Not recommended)', 'email-encoder-bundle')
			],
		],
		'required'    => false
	],

	'protect_using' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'protect_using',
		'type'        => 'multi-input',
		'input-type'  => 'radio',
		'title'       => __( 'Protect emails using', 'email-encoder-bundle' ),
		'inputs' 	  => [
			'with_javascript' => [
				'label' => __( 'automatically the best method (including javascript)', 'email-encoder-bundle' )
			],
			'without_javascript' => [
				'label' => __( 'automatically the best method (excluding javascript)', 'email-encoder-bundle' ),
			],
			'strong_method' => [
				'label' => __( 'a strong method that replaces all emails with a "*protection text*".', 'email-encoder-bundle' ),
				'description' => __('You can configure the protection text within the advanced settings.', 'email-encoder-bundle')
			],
			'char_encode' => [
				'label' => __( 'simple HTML character encoding.', 'email-encoder-bundle' ),
				'description' => __('Offers good (but not the best) protection, which saves you in most scenarios.', 'email-encoder-bundle')
			],
		],
		'required'    => false
	],

	'filter_body' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'filter_body',
		'type'        => 'multi-input',
		'input-type'  => 'checkbox',
		'advanced' 	  => true,
		'title'       => __( 'Protect...', 'email-encoder-bundle' ),
		'label'       => __( 'Customize what this plugin protects.', 'email-encoder-bundle' ),
		'inputs' 	  => [
			'filter_rss' => [
				'advanced' 	  => true,
				'label' => __( 'RSS feed', 'email-encoder-bundle' ),
				'description' => __( 'Activating this option results in protecting the rss feed based on the given protection method.', 'email-encoder-bundle' )
			],
			'ajax_requests' => [
				'advanced' 	  => true,
				'label' => __( 'Ajax requests', 'email-encoder-bundle' ),
				'description' => __( 'By default, ajax requests can send clear emails in some situations. Activating this settings will apply encoding to ajax-relate requests.', 'email-encoder-bundle' )
			],
			'admin_requests' => [
				'advanced' 	  => true,
				'label' => __( 'Admin requests', 'email-encoder-bundle' ),
				'description' => __( 'By default, we only protect frontend requests (Everything people see on your website). Activating this setting will also protect the backend of your website (The admin area).', 'email-encoder-bundle' )
			],
			'remove_shortcodes_rss' => [
				'advanced' 	  => true,
				'label' => __( 'Remove all shortcodes from the RSS feeds', 'email-encoder-bundle' ),
				'description' => __( 'Activating this option results in protecting the rss feed based on the given protection method.', 'email-encoder-bundle' )
			],
			'input_strong_protection' => [
				'advanced' 	  => true,
				'label' => __( 'input form email fields using strong protection.', 'email-encoder-bundle' ),
				'description' => __( 'Warning: this option could conflict with certain form plugins. Test it first. (Requires javascript)', 'email-encoder-bundle' )
			],
			'encode_mailtos' => [
				'advanced' 	  => true,
				'label' => __( 'plain emails by converting them to mailto links', 'email-encoder-bundle' ),
				'description' => __( 'Plain emails will be automatically converted to mailto links where possible.', 'email-encoder-bundle' )
			],
			'convert_plain_to_image' => [
				'advanced' 	  => true,
				'label' => __( 'plain emails by converting them to png images', 'email-encoder-bundle' ),
				'description' => __( 'Plain emails will be automatically converted to png images where possible.', 'email-encoder-bundle' )
			],
			'protect_shortcode_tags' => [
				'advanced' 	  => true,
				'label' => __( 'shortcode content', 'email-encoder-bundle' ),
				'description' => __( 'Protect every shortcode content separately. (This may slows down your site)', 'email-encoder-bundle' )
			],
			'filter_hook' => [
				'advanced' 	  => true,
				'label' => __( 'emails from "init" hook', 'email-encoder-bundle' ),
				'description' => __( 'Check this option if you want to register the email filters on the "init" hook instead of the "wp" hook.', 'email-encoder-bundle' )
			],
			'deactivate_rtl' => [
				'advanced' 	  => true,
				'label' => __( 'mailto links without CSS direction', 'email-encoder-bundle' ),
				'description' => __( 'Check this option if your site does not support CSS directions.', 'email-encoder-bundle' )
			],
			'no_script_tags' => [
				'advanced' 	  => true,
				'label' => __( 'no script tags', 'email-encoder-bundle' ),
				'description' => __( 'Check this option if you face issues with encoded script tags. This will deactivate protection for script tags.', 'email-encoder-bundle' )
			],
			'no_attribute_validation' => [
				'advanced' 	  => true,
				'label' => __( 'html attributes without soft encoding.', 'email-encoder-bundle' ),
				'description' => __( 'Do not soft-filter all html attributes. This might optimizes the performance, but can break the site if other plugins use your email in attribute tags.', 'email-encoder-bundle' )
			],
		],
		'required'    => false,
	],

	'image_settings' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'image_settings',
		'type'        => 'multi-input',
		'input-type'  => 'text',
		'advanced' 	  => true,
		'title'       => __( 'Image settings', 'email-encoder-bundle' ),
		'label'       => __( 'Customize the settings for dynamically created images.', 'email-encoder-bundle' ),
		'inputs' 	  => [
			'image_color' => [
				'advanced' 	  => true,
				'label' => __( 'Image Colors', 'email-encoder-bundle' ),
				'description' => __( 'Please include RGB colors, comme saparated. E.g.: 0,0,255', 'email-encoder-bundle' )
			],
			'image_background_color' => [
				'advanced' 	  => true,
				'label' => __( 'Image Background Colors', 'email-encoder-bundle' ),
				'description' => __( 'Please include RGB colors, comme saparated. E.g.: 0,0,255', 'email-encoder-bundle' )
			],
			'image_text_opacity' => [
				'advanced' 	  => true,
				'label' => __( 'Text Opacity', 'email-encoder-bundle' ),
				'description' => __( 'Change the text opacity for the created images. 0 = not transparent - 127 = completely transprent', 'email-encoder-bundle' )
			],
			'image_background_opacity' => [
				'advanced' 	  => true,
				'label' => __( 'Background Opacity', 'email-encoder-bundle' ),
				'description' => __( 'Change the background opacity for the created images. 0 = not transparent - 127 = completely transprent', 'email-encoder-bundle' )
			],
			'image_font_size' => [
				'advanced' 	  => true,
				'label' => __( 'Font Size', 'email-encoder-bundle' ),
				'description' => __( 'Change the font size of the image text. Default: 4 - You can choose from 1 - 5', 'email-encoder-bundle' )
			],
			'image_underline' => [
				'advanced' 	  => true,
				'label' => __( 'Text Underline', 'email-encoder-bundle' ),
				'description' => __( 'Adds a line beneath the text to highlight it as a link. empty or 0 deactivates the border. 1 = 1px', 'email-encoder-bundle' )
			],
		],
		'required'    => false,
	],

	'skip_posts' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'skip_posts',
		'type'        => 'text',
		'advanced' 	  => true,
		'title'       => __('Exclude post id\'s from protection', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('By comma separating post id\'s ( e.g. 123,4535,643), you are able to exclude these posts from the logic protection.', 'email-encoder-bundle')
	],

	'skip_query_parameters' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'skip_query_parameters',
		'type'        => 'text',
		'advanced' 	  => true,
		'title'       => __('Exclude URL parameters from protection', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('By comma separating URL (Query) parameters ( e.g. param1,param2), you are able to exclude URLs with these parameters from the protection. URL or Query parameters are found at the end of your URL (e.g. domain.com?param1=test)', 'email-encoder-bundle')
	],

	'protection_text' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'protection_text',
		'type'        => 'text',
		'advanced' 	  => true,
		'title'       => __('Set protection text *', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('This text will be shown for protected email addresses and within noscript tags.', 'email-encoder-bundle')
	],

	'class_name' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'class_name',
		'type'        => 'text',
		'advanced' 	  => true,
		'title'       => __('Additional classes', 'email-encoder-bundle'),
		'label'       => __('Add extra classes to mailto links.', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('Leave blank for none', 'email-encoder-bundle')
	],

	'custom_href_attr' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'custom_href_attr',
		'type'        => 'text',
		'advanced' 	  => true,
		'title'       => __('Protect custom href attributes', 'email-encoder-bundle'),
		'label'       => __('Protect href atrributes such as tel:, ftp:, file:, etc.', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('Add the href attributes you want to protect as a comme-separated list. E.g. tel,file,ftp', 'email-encoder-bundle')
	],

	'footer_scripts' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'footer_scripts',
		'type'        => 'checkbox',
		'advanced' 	  => true,
		'title'       => __('Load scripts in footer', 'email-encoder-bundle'),
		'label'       => __('Check this button if you want to load all frontend scripts within the footer.', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('This forces every script to be enqueued within the footer.', 'email-encoder-bundle')
	],

	'show_encoded_check' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'show_encoded_check',
		'type'        => 'checkbox',
		'title'       => __('Security Check', 'email-encoder-bundle'),
		'label'       => __('Mark emails on the site as successfully encoded', 'email-encoder-bundle') . '<i class="dashicons-before dashicons-lock" style="color:green;"></i>',
		'placeholder' => '',
		'required'    => false,
		'description' => __('Only visible for admin users. If your emails look broken, simply deactivate this feature. This also loads the dashicons style.', 'email-encoder-bundle')
	],

	'own_admin_menu' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'own_admin_menu',
		'type'        => 'checkbox',
		'advanced' 	  => true,
		'title'       => __('Admin Menu', 'email-encoder-bundle'),
		'label'       => __('Show this page in the main menu item', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('Otherwise it will be shown in "Settings"-menu.', 'email-encoder-bundle')
		],

	'encoder_form' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'encoder_form',
		'type'        => 'multi-input',
		'input-type'  => 'checkbox',
		'advanced' 	  => true,
		'title'       => __( 'Encoder form settings', 'email-encoder-bundle' ),
		'inputs' 	  => [
			'encoder_form_frontend' => [
				'label' => __( 'Encoder form frontend', 'email-encoder-bundle' ),
				'description' => __( 'Activate this to use the [eeb_form] shortcode or the PHP template function eeb_form() within the frontend.', 'email-encoder-bundle' )
			],
			'powered_by' => [
				'label' => __( 'Show a "powered by" link on bottom of the encoder form', 'email-encoder-bundle' ),
			],
		],
		'required'    => false
	],

	'advanced_settings' => [
		'fieldset'    => [ 'slug' => 'main', 'label' => 'Label' ],
		'id'          => 'advanced_settings',
		'type'        => 'checkbox',
		'title'       => __('Advanced Settings', 'email-encoder-bundle'),
		'label'       => __('Show advanced settings for more configuration possibilities.', 'email-encoder-bundle'),
		'placeholder' => '',
		'required'    => false,
		'description' => __('Activate the advanced settings in case you want to customize the default logic or you want to troubleshoot the plugin.', 'email-encoder-bundle')
	],

];