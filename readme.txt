=== Email Encoder - Protect Email Addresses and Phone Numbers ===
Contributors: onlineoptimisation
Tags: anti spam, protect, encode, encrypt, hide, antispam, phone number, spambot, secure, e-mail, email, mail
Requires at least: 4.7
Requires PHP: 5.1
Tested up to: 6.8.3
Stable tag: 2.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author URI: https://wpemailencoder.com/
Plugin URI: https://wordpress.org/plugins/email-encoder-bundle/
Contributors: onlineoptimisation
Donate link: https://paypal.me/onlineoptimisation

Protect email addresses and phone numbers on your site and hide them from spambots. Easy to use & flexible.

== Description ==

Full site protection for your email addresses from spam-bots, email harvesters and other robots. No configuration needed.
It also protects phone numbers or any other text using our integrated `[eeb_protect_content]` shortcode or href attribute encoding.

= Features =
* Full page protection for all of your emails
* Instant results (No confiruation needed)
* Protects mailto links, plain emails, email input fields, RSS feeds and much more
* Protect phone number links, ftp, skype, file and other custom link attributes
* Autmoatic protection technique detection (Our plugin chooses automatically the best protection technique for each email)
* Exclude posts and pages from protection
* Automatically convert plain emails to mailto-links
* Automatically convert plain emails to png images
* Supports rot13 encoing, escape encoding, CSS directions, entity encoding and much more
* Deactivate CSS directions manually for browser backwards compatibility
* Shortcode support: `[eeb_protect_emails]`, `[eeb_protect_content]`, `[eeb_mailto]`, `[eeb_form]`
* Template tag support: `eeb_protect_emails()`, `eeb_protect_content()`, `eeb_mailto()`, `eeb_form()`
* Protect phone numbers (or any text or html)
* Also supports special chars, like Ã©, Ã¢, Ã¶, Chinese characters etcetera
* Use the Encoder Form to manually create encoded scripts

= Compatibilities =
* The plugin works with mostly any theme and plugin. Some special ones need special treatment. Down below you can learn more about that.
* Compatible with the Maintenance plugin from WP Maintenance
* Divi Theme is fully integrated as well
* Jetpack Image carousel is compatible as well

= Free Website Check  =
We offer you a free tool to test if your website contains unprotected emails. You can use our website checker by [clicking here](https://wpemailencoder.com/email-protection-checker/)

= Easy to use  =
After activating the plugin all email addresses on your website will be protected out-of-the-box.
We also offer custom shortcodes and template functions to protect phone numbers or other text.

= Support =
* Documentation - After plugin activation, check the help tab on the plugin options page
* [Documentation on wpemailencoder.com](https://wpemailencoder.com/)
* [FAQ](http://wordpress.org/extend/plugins/email-encoder-bundle/faq/)

= Like this plugin? =
[Please Review it](http://wordpress.org/support/view/plugin-reviews/email-encoder-bundle)

== Installation ==

1. Go to `Plugins` in the Admin menu
2. Click on the button `Add new`
3. Search for `Email Encoder` and click 'Install Now' or click on the `upload` link to upload `email-encode-bundle.zip`
4. Click on `Activate plugin`
5. You will find the settings page unter "Settings -> Email Encoder" within your admin dashboard

== Frequently Asked Questions ==

= How can I test if an email address (or other content) is encoded? =

You can test this in three different ways.

The easiest (and most efficient) way is to use our website checker, which looks over your website and detects unprotected emails. It is completely free and you can [find it here](https://wpemailencoder.com/email-protection-checker/) on our website.

The second possibility is to enable the plugin option (in the admin panel) called *"Security Check"*.
When you are logged in and look on the page there will be a icon on the right side of each email address confirming it was successfully encoded. (This counts only for emails that are displayed within the body tag as HTML. Emails within data attributes or the header won't show this icon since otherwise the site breaks.)

The third possibility is to check the source code yourself by right-clicking on the page and select *View Source Code* (the exact text depends on the browser).
Now your (real) source code will be shown. Your email address should not be shown in a readable way in the source.

**Important:** in the element inspector of the browser the email address is *nearly always* shown, so don't worry about that. That is because the inspector shows a real time representation of the page. This means an encoded email address is already decoded and made usable for the visitor of the page.

= How do I encode my email address(es)? =

All email addresses are protected automatically by default, so it is not necessary to configure anything else.

In case you wish to customize it, we also offer some neatsettings,  shortcodes and template functions. Please check the settings page within your WordPress website or [our documentation](https://wpemailencoder.com/)

The visitors will see everything as normal, but the source behind it will now be encoded.

For more information, please check out the [following page](https://wpemailencoder.com/what-will-be-protected/)

= How do I encode phone numbers or other text? =

Just use the following shortcode within your posts:
`[eeb_protect_content]35-01235-468113[/eeb_protect_content]`

For other parts of your site you can use the template function `eeb_protect_content()`.

= My website looks broken after activating the plugin. What to do? =

First: Don't panic!
Simply create a support request within the [support forum](http://wordpress.org/support/plugin/email-encoder-bundle#postform) and we will come back to you as soon as possible with help.

= How can I encode content of BBPress, WP e-commerce or other plugins? =

Every content will be automatically protected. In case you find something, that doesn't work from your end, we are very happy to help!
Our plugin is fully compatible with [WP Webhooks](https://wp-webhooks.com/) and plugins created via [Pluginplate](https://pluginplate.com/)

= Can I use special characters (like Chinese)? =

Yes, since version 1.3.0 also special characters are supported.

== Screenshots ==

1. Admin Options Page
1. Check encoded email/content when logged in as admin
1. Email Encoder Form on the Site

== Other Notes
= Credits =
* [Adam Hunter](http://blueberryware.net) for the encode method 'JavaScript Escape' which is taken from his plugin [Email Spam Protection](http://blueberryware.net/2008/09/14/email-spam-protection/)
* [Tyler Akins](http://rumkin.com) for the encode method 'JavaScript ASCII Mixer'
* Title icon on Admin Options Page was made by [Jack Cai](http://www.doublejdesign.co.uk/)

== Changelog ==

= 2.2.4: October 28, 2025 =
* Tweak: Compatibility with HivePress plugin 

= 2.2.3: October 16, 2025 =
* Fix: Fixed some PHP 8.4+ notices
* Fix: Translations loading in time
* Tweak: Compatibility with WP 6.8.3

= 2.2.1: February 11, 2024 =
* Tweak: Security Patch
* Tweak: Provide compatibility if GD Library Not Enabled
* Tweak: Compatibility with WP 6.4.3
* Fix: Fixed some PHP 8.2+ notices
* Fix: Fixed Automatic Translations for WPML: https://wpml.org/errata/email-encoder-protect-email-addresses-and-phone-numbers-there-is-a-problem-connecting-to-automatic-translation/

= 2.2.2: June 15, 2024 =
* Tweak: Minor Security Patch (Multisite)
* Tweak: Add <br> tags to the allowed HTML for shortcodes
* Tweak: Apply content filtering before executing shortcodes for [eeb_protect_content]

= 2.2.1: February 11, 2024 =
* Tweak: Security Patch
* Tweak: Provide compatibility if GD Library Not Enabled
* Tweak: Compatibility with WP 6.4.3
* Fix: Fixed some PHP 8.2+ notices
* Fix: Fixed Automatic Translations for WPML: https://wpml.org/errata/email-encoder-protect-email-addresses-and-phone-numbers-there-is-a-problem-connecting-to-automatic-translation/

= 2.2.0: January 11, 2024 =
* Feature: Exclude pages with query parameters from being protected (Advanced setting: Exclude URL parameters from protection)
* Tweak: Optimized performance for Javascript and Style enqueues
* Dev: Added new filter hook "eeb/frontend/apply_protection" to manually prevent protection of certain pages
* Dev: Added new filter hook "eeb/validate/is_query_parameter_excluded" to filter the query parameter exclusions

= 2.1.10: January 09, 2024 =
* Optimized Helpfiles
* PHP 8.x improvements
* Security Patch

= 2.1.9: November 11, 2023 =
* Optimized wp.org links
* Security Patch

= 2.1.8: August 27, 2023 =
* Security Patch for XSS vulnerability within the [eeb_mailto] shortcode when using the "email" tag (Thanks to Wordfence)

= 2.1.7: June 23, 2023 =
* Tweak: Provide compatibility with the Avada Builder

= 2.1.6: May 10, 2023 =
* Tweak: Provide compatibility with Bricks Builder
* Tweak: Optimized performance for integrations

= 2.1.5: May 10, 2023 =
* Feature: New advanced settings to protect admin and ajax requests
* Feature: New advanced setting to enable the Email Encoder form within the frontend
* Tweak: Better validation for script tags
* Tweak: Changed old links from ironikus.com to wpemailencoder.com
* Tweak: Optimzied the Encoder Form logic to save performance
* Tweak: Various performance optimizations
* Tweak: Deprecated the "Activate the encoder form" setting
* Fix: In some occasions, backenbd sites did not work correctly with buffered content

= 2.1.4: April 06, 2023 =
* Provided compatibility for HTML attributes that start with an @ character ()
* Excluded additional file types from auto-encoding of images containing the @ sign: webp, bmp, tiff, avif
* Deprecated the widget filter as it was of no use anymore
* Fix deprecated preg_split message about the $limit set to null

= 2.1.3: February 04, 2021 =
* Tweak: Email Encoder runs now on its own website: https://wpemailencoder.com/
* Tweak: The eeb_mailto function now supports the default encoding methods if no specific method is given
* Tweak optimized text and descriptions
* Fix: Prevent error with undefined $id_base property

= 2.1.2: July 30, 2021 =
* Fix: Prevent notice on undefined post within the global object
* Fix: Revalidate Display Text for the frontend encoder form (via [eeb_form] or eeb_form();) to prevent userbased cross site scripting
* Fix: Fatal error if the content was not given
* Tweak: Centrlalized encoding icon for a better usability
* Dev: New filter eeb/validate/get_encoded_email_icon to cusotmize the encoding icon

= 2.1.1: April 07, 2021 =
* Tweak: Added svg images to the image exclude list
* Dev: New filter eeb/validate/excluded_image_urls to filter the excluded image list

= 2.1.0 =
* Feature: New advanced setting to automatically protect custom link attributes such as tel:, file:, ftp:, skype:, etc. (Protect custom href attributes)
* Tweak: Adjust JS documentation
* Tweak: Adjust readme file

= 2.0.9 =
* Fix: Issue with not properly validated soft-encoded attribute tags on the dom attributes
* Fix: Issue with not properly validated soft-encoded attributes on special softencoded tags for the content filter
* Tweak: Optimized performance for soft attribute filtering
* Dev: Added new filter to allow customization of the mailto text: https://wpemailencoder.com/filter-email-encoder-mailto-text/

= 2.0.8 =
* Feature: The shortcode [eeb_protect_content] now supports a new attribute called do_shortcode="yes" which allows you to execute all shortcodes within the given content area
* Tweak: Add new link for the Email Checker (Allows you to check if all of your emails are being encoded)
* Tweak: Optimize layout and texts
* Fix: The documentation link on the settings page was not working
* Dev: The eeb/frontend/shortcode/eeb_protect_content filter now contains a new variable called $original_content (https://wpemailencoder.com/filter-eeb_protect_content-shortcode/)

= 2.0.7 =
* Feature: Underline emails that are converted to an image (Cutsomizable)
* Feature: Integration for the Google Site Kit plugin - https://wordpress.org/plugins/google-site-kit/
* Feature: Integration for the events calendar plugin - https://de.wordpress.org/plugins/the-events-calendar/
* Tweak: Softening the regex to recognize spaces before the closing tags

= 2.0.6 =
* Feature: We fully removed all external marketing advertisements! Enjoy our plugin without distrations! :)
* Feature: Full support for Oxygen builder
* Tweak: Optimize PHPDocs and comments
* Tweak: Optimize is_post_excluded functionality
* Dev: New filter: eeb/validate/filter_page_content - https://wpemailencoder.com/filter-to-manipulate-raw-page-content/
* Dev: New filter: eeb/validate/filter_content_content - https://wpemailencoder.com/filter-to-manipulate-raw-hook-content/
* Dev: New filter: eeb/validate/is_post_excluded - https://wpemailencoder.com/filter-excluded-posts-list/
* Dev: New filter: eeb/settings/pre_filter_fields - https://wpemailencoder.com/pre-filter-email-encoder-settings/

= 2.0.5 =
* Feature: Soft-Encode all HTML tags + new settings item (This will prevent complex plugins from breaking)
* Dev: New filter for randomization of javascript escaping methods

= 2.0.4 =
* Feature: Exclude script tags from being encoded
* Fix: Revalidate and strip escape sequences for encode_escape function
* Fix: Return shortcode content of eeb_mailto instead of outputting it

= 2.0.3 =
* Feature: Integration for Divi Theme included
* Tweak: Optimize Jetpack integration to also filter against image attribute description tags
* Tweak: Soft-filter html placeholder tags
* Tweak: Allow template tags to work as well with the plugin settings set to "Do nothing"
* Fix: Only one match of the soft attributes was soft encoded properly
* Fix: The escape js function stripped away all zeros from emails

= 2.0.2 =
* Feature: New settings item to include custom scripts within the footer and not in the header
* Feature: Support for the "Maintenance" plugin from WP Maintenance
* TweaK: Remove our main translation handler to make plugin translations on WordPress.org available again.
* Tweak: Optimize PHP code to WordPress standards
* Tweak: Enqueue dashicons as well if only "show_encoded_check" is checked and protection is set to "Do nothing"
* Tweak: Make eeb_mailto link available with protection set to "Do nothing" as well
* Fix: WP CLI did not work with this plugin in an active state (Due to the active buffer filter)
* Fix: Emails have been not encoded properly if "Do nothing" was chosen as a setting and the eeb_mailto shortcode was used

= 2.0.1 =
* Fix: Include missing template functions requirement
* Tweak: Clear languages

= 2.0.0 =
* PLEASE READ BEFORE UPDATING
* THIS IS A COMPLETELY REFACTORED VERSION OF THE PLUGIN. EVEN WITH INVESTING TONS OF TIME INTO MAKING THIS PLUGIN AS MUCH BACKWARDS COMPATIBLE AS POSSIBLE, WE WOULD STILL APPRECIATE IF YOU TEST THIS VERSION BEFORE YPU UPDATE.
* THE PLUGIN GOT A COMPLETE OVERHAUL AND OFFERS NOW MORE OPTIMIZED FEATURES AND A SUPER SIMPLE USER INTERFACE. PLEASE FIND ALL CHANGES DOWN BELOW.
* Feature: Completely rewritten version of your beloved plugin
* Feature: Introduce FULL SITE PROTECTION (Automatically protect ALL emails within your page)
* Feature: Simplified settings (We cleaned the settings a lot, but you can still get some your old settings page back by activating the advanced checkbox :) )
* Feature: Feature to automatically detect the best protection method
* Feature: Choose from four new settings the strength of your protections
* Feature: Added admin security check icon to encoded input fields and encoded plain emails/texts, as well as to all shortcodes
* Feature: Also protect every single shortcode content
* Feature: Create images out of email addresses
* Feature: Protect header section automatically
* Feature: Added and refactored shortcodes. For a full list of shortcodes, please check this article: https://wpemailencoder.com/available-shortcodes/
* Feature: Setting to deactivate the Encoder Form completely
* Feature: Choose converting plain emails to mailto links as an additional feature
* Feature: Change filter apply from "wp" to "init" (This allows you to also grab some ajax values to parse them directy encoded)
* Feature: Website checker to search your site for unprotected emails. Follow this URL for more information: https://wpemailencoder.com/email-protection-checker/
* Tweak: Backward compatibility to the new plugin settings
* Tweak: Completely performance optimized (We removed everything that is not necessary, included a better object caching and much more)
* Tweak: Optimized filter combinations
* Fix: The old logic broke some email encodings, especially with custom tags. We fixed all of them
* Fix: We fixed tons of bugs from the previous version
* Dev: Code rewritten on the newest WordPress standards
* Dev: Tons of new WordPress filters and actions. For a full list, please check https://wpemailencoder.com/
* Deprecated: We removed the deprecated functions. Please make sure to update your custom logic to the newest standards.

= 1.53 =
* PLEASE READ BEFORE UPDATE
* THIS PLUGIN WILL BE REFACTORED WITH THE NEXT UPDATE
* TO PREPARE YOURSELF, YOU WILL FIND A LIST DOWN BELOW WITH THE CHANGES THAT AWAIT YOU
* - The plugin will be simplified using automatically the best protection for your site
* - The plugin will protect yout site out-of-the-box
* - We introduce a site-wide protection, not only based on WordPress shortcodes (This includes protection for your FULL site)
* - You will be able to choose the protection type. Available will be: Automatically (using Javascript), Automatically (Without Javascript), Protection Text, Entity encode
* - The plugin structure will be optimized using the current WordPress standards
* - Switch between full site protection and only WordPress filters
* - (Optional) Protect emails by converting them to PNG images (where applicable)
* - Round-robin method for javascript based protection (Choose the best method automatically from similar protection methods)
* - Tons of bugfixes
* - All settings will be fully compatible in any combination
* - The encoding form will continue to exists
* - All settings that have been available in the old version and will be available in the new version are backwards compatible
* - Display admin notice to all encoded emails (where applicable)
* ###################
* THE UPDATE WILL BE LAUNCHED WITHIN OCTOBER
* Tweak: Introduce our new partner MailOptin

= 1.5.2 =
* Tweak: Add popup window for admin success message of hidden email
* Fix: Fix bug for non-available antispambot() function
* WP Webhooks takes over development (https://wp-webhooks.com)

= 1.51 =
* 2019-03-25
* minor bug fixes
* 161,000 downloads; 30,000 installs

= 1.4.6 =
* Fixed bug retina png and gif images

= 1.4.5 =
* Fixed ? params bug

= 1.4.4 =
* Fixed skip responsive images containing @

= 1.4.3 =
* Changed content

= 1.4.2 =
* Fixed potential xss vulnerability

= 1.4.1 =
* Fixed [preserving classes on mailto links](https://wordpress.org/support/topic/preserve-link-classes)

= 1.4.0 =
* Fixed bug prefilled email address in input fields
* Added option protection text for encoded content (other than email addresses)

= 1.3.0 =
* Also support special chars for the javascript methods, like Ã©, Ã¢, Ã¶, Chinese chars etcetera
* Fixed bug unchecking options "use shortcode" and "use deprecated"

= 1.2.1 =
* Fixed bug index php error

= 1.2.0 =
* Added filter for Encoder Form content (eeb_form_content)

= 1.1.0 =
* Added filters for changing regular expression for mailto links and email addresses
* Fixed bug don't encode when loading admin panel

= 1.0.2 =
* Fixed bug wrong "settings" link
* Fixed bug removing shortcodes RSS feed

= 1.0.1 =
* Fixed PHP support (same as WordPress)

= 1.0.0 =
* NOW ONLY SUPPORT FOR WP 3.4.0+
* Fixed bug deleting setting values when unregister (will now be deleted on uninstall)
* Fixed bug also possible to set protection text when RSS disabled
* Fixed bug saving metaboxes settings
* Added option support shortcodes in widgets
* Added option removing shortcodes for RSS feed
* Removed "random" method option
* Changed names for action and shortcode (prefixed with eeb_), optional the old names will still be supported
* Added template function for creating the encoder form
* Changed class en id names of the Encoder Form

= 0.80 =
* Added screen settings
* Registered metaboxes
* Fixed bug random method
* Workaround for display with special characters (like Chinese), works only with enc_html

= 0.71 =
* Option to make own menu item (in admin panel) for this plugin
* Option for showing "successfully encoded" check
* Fixed bug showing errors for calling wrong translate function
* Fixed bug always showing encoded check on site (for html encode method)
* Added workaround for saving disabled checkboxes in options table
* Fixed bug where encoded check was also applied on output of encoding form

= 0.70 =
* Fixed bug with extra params
* Changed texts and added help tabs on admin options page
* Changed visual check for encoded mails/content by showing icon and success message
* Solved that all attributes of mailto links remain when encoding

= 0.60 =
* Added hook "init_email_encoder_form" to add custom filters (of other plugins)
* Added JavaScript code encapsulation for ASCII method
* Solved reinstalling bug for setting right encoding method
* Fixed bug shortcodes encoded with HTML method

= 0.50 =
* Added encode method for all kind of contents (template function and shortcode "encode_content")
* Added extra param for additional html attributes (f.e. target="_blank")
* Added option to skip certain posts from being automatically encoded
* Added option custom protection text
* Removed "method" folder. Not possible to add own methods anymore.
* Other small changes and some refactoring

= 0.42 =
* Widget Logic options bug

= 0.41 =
* Fixed bug by improving regular expression for mailto links
* Changed script attribute `language` to `type`
* Script only loaded on options page (hopefully this solves the dashboard toggle problem some people are experiencing)
* Added support for widget_content filter of the Logic Widget plugin

= 0.40 =
* Added option for setting CSS classes
* Improved RSS protection
* Removed Lim_Email_Encoder class (now all handled by the main class)
* Enabled setting checkbox for filtering posts
* Fixed PHP / WP notices
* Added param for encode methods: $obj

= 0.32 =
* Fix IE bug
* Bug plain emails
* Optional "method" param for tag and template function, f.e. [encode_email email="test@domain.com" method="ascii"]
* Small adjustments

= 0.31 =
* Fixed tiny bug (incorrect var-name $priority on line 100 of email-encoder-bundle.php)

= 0.30 =
* Added protection for emails in RSS feeds
* Improved filtering tags [encode_email ... ]
* Improved ASCII and Escape method and added noscript message
* Solved an option bug (encode mailto links VS encode plain emails)
* Made some cosmetical adjustments on the options page
* Code refactoring

= 0.22 =
* First decodes entities before encoding email
* Added more wp filters for encoding

= 0.21 =
* Changed Encoder Form: HTML markup and JavaScript
* Made some minor adjustments and fixed little bugs

= 0.20 =
* Implemented internalization (including translation for nl_NL)
* Improved user-interface of the Admin Settings Page and the Encoder Form
* Added template function: encode_email_filter()
* Kept and added only high-quality encoding methods
* Refactored the code and changed method- and var-names within the classes
* Removed 3rd param $encode_display out of the encoding methods, display should always be encoded
* Added prefix 'lim_email_' to the encoding methods

= 0.12 =
* Nothing changed, but 0.11 had some errors because /methods directory was missing in the repository.

= 0.11 =
* also possible to use encode tag in widgets by activating the "filter widget" option

= 0.10 =
* Works with PHP4 and PHP5
* Methods: default_encode, wp_antispambot, anti_email_spam, email_escape, hide_email
* Use the tags: `[email_encode email=".." display=".."]`, `[email_encoder_form]`
* Template function: `email_encode()`
