<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php $pluginData = get_plugin_data( EEB_PLUGIN_FILE ); ?>
<h3><i class="dashicons-before dashicons-email"></i>  <?php echo esc_html( $pluginData['Name'] ); ?> - v<?php echo esc_html( $pluginData['Version'] ); ?></h3>
<p>
    <?php echo esc_html__( 'The plugin works out-of-the-box to protect your email addresses. All settings are default set to protect your email addresses automatically.', 'email-encoder-bundle' ); ?>
</p>
<p>
    <?php echo wp_kses( __( 'To report problems or bugs or for support, please use <a href="https://wordpress.org/support/plugin/email-encoder-bundle#postform" target="_new">the official forum</a>.', 'email-encoder-bundle' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?>
</p>
<p>
    <?php echo wp_kses( __( 'You can now also check your website protection using our email checker tool: <a href="https://wpemailencoder.com/email-protection-checker/" target="_blank">https://wpemailencoder.com/email-protection-checker/</a>.', 'email-encoder-bundle' ), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?>
</p>
<p>
    Visit us at <a href="https://wpemailencoder.com" target="_blank" title="Visit us at https://wpemailencoder.com" >https://wpemailencoder.com</a>
    <i class="dashicons-before dashicons-universal-access"></i>
</p>
