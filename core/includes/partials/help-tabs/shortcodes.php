<?php
/** @var array $allowed_attr_html */
?>
<h3><?php echo __( 'Shortcodes', 'email-encoder-bundle' ); ?></h3>

<p><?php echo __( 'You can protect those addresses using the following shortcode. Everything you add within these both tags, will be checked for emails automatically.', 'email-encoder-bundle' ); ?></p>

<p><code>[eeb_protect_emails protect_using="with_javascript"]…[/eeb_protect_emails]</code>
</p>
<p><?php echo __( 'Create a protected mailto link:', 'email-encoder-bundle' ); ?></p>
<p><code>[eeb_mailto email="info@myemail.com" extra_attrs="target='_blank'" method="rot13" display="Custom Text"]</code></p>
<p><?php echo __( 'For security reasons, we only allow specific HTML tags within the display arguments. You will find a full list below.', 'email-encoder-bundle' ); ?></p>
<p><?php echo __( 'You can also protect phone numbers and any kind of text by using the following shortcode:', 'email-encoder-bundle' ); ?></p>
<p><code>[eeb_protect_content protection_text="I am a noscript text" method="rot13" do_shortcode="yes"]My Email[/eeb_protect_content]</code></p>
<p><?php echo __( 'Display the encoder form', 'email-encoder-bundle' ); ?></p>
<p><code>[eeb_form]</code></p>

<p><strong><?php echo __( 'Allowed HTML attributes + arguments', 'email-encoder-bundle' ); ?></strong>

<ul>

<?php
foreach ( $allowed_attr_html as $tag => $attributes ) {
    echo '<li><strong>' . htmlentities( '<' . $tag . '>' ) . '</strong>: ';

    if (!empty($attributes)) {
        echo trim( implode( ', ', array_keys( $attributes ) ), ', ' );
    } else {
        echo 'n/a';
    }

    echo '</li>';
}
?>
</ul>

</p>
