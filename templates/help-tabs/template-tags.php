<h3><?php echo __( 'Template functions', 'email-encoder-bundle' ); ?></h3>

<h4><code>eeb_mailto( $email [, $display] [, $attrs] [, $method] )</code></h4>
<p><?php echo __( 'Create a protected mailto link:', 'email-encoder-bundle' ); ?></p>
<pre><code><&#63;php
    if (function_exists('eeb_mailto')) {
        echo eeb_mailto('info@somedomain.com', 'Mail Me');
    }
&#63;></code></pre>
<p><?php echo __( 'You can pass 3 optional arguments: <code>$display</code>, <code>$attrs</code> and <code>$method</code>', 'email-encoder-bundle' ); ?></p>
<br><br>
<h4><code>eeb_form()</code></h4>
<p><?php echo __( 'Get Email Encoder Form', 'email-encoder-bundle' ); ?></p>
<pre><code><&#63;php
    if (function_exists('eeb_form')) {
        echo eeb_form();
    }
&#63;></code></pre>
<p><?php echo __( 'This will output the email encoder form on your website.', 'email-encoder-bundle' ); ?></p>
<br><br>
<h4><code>eeb_protect_content( $content )</code></h4>
<p><?php echo __( 'Protects the given content against spambots.', 'email-encoder-bundle' ); ?></p>
<pre><code><&#63;php
    if (function_exists('eeb_protect_content')) {
        echo eeb_protect_content('+12 345 678');
    }
&#63;></code></pre>
<br><br>
<h4><code>eeb_protect_emails( $content )</code></h4>
<p><?php echo __( 'Protects the given content against spambots.', 'email-encoder-bundle' ); ?></p>
<pre><code><&#63;php
    if (function_exists('eeb_protect_emails')) {
        echo eeb_protect_emails('You can parse any kind of text or html in here. All emails like test@test.test will be encoded depending on your plugin settings.');
    }
&#63;></code></pre>
