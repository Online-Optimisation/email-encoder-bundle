<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="support" class="postbox">
    <div class="inside">
        <ul>
            <li>
                <a href="https://wpemailencoder.com/email-protection-checker/" target="_blank">
                    <i class="dashicons-before dashicons-search"></i>
                    Email checker
                </a>
            </li>
            <li>
                <a href="https://wpemailencoder.com/docs/article-categories/email-encoder/" target="_blank">
                    <i class="dashicons-before dashicons-media-text"></i>
                    Documentation
                </a>
            </li>
            <li>
                <a href="http://wordpress.org/support/plugin/email-encoder-bundle#postform" target="_blank">
                    <i class="dashicons-before dashicons-welcome-comments"></i>
                    Report a problem
                </a>
            </li>
            <li>
                <a href="http://wordpress.org/extend/plugins/email-encoder-bundle/faq/" target="_blank">
                    <i class="dashicons-before dashicons-editor-help"></i>
                    FAQ
                </a>
            </li>
        </ul>

        <hr>
        <p>
            <a href="http://wordpress.org/support/view/plugin-reviews/email-encoder-bundle" target="_blank">
                <i class="dashicons-before dashicons-thumbs-up"></i>
                <strong>Rate the plugin!</strong>
            </a>
        </p>

        <hr>
        <?php $support_text = ( new \OnlineOptimisation\EmailEncoderBundle\Admin\SupportExport() )->generate(); ?>
        <p>
            <a href="#" id="eeb-copy-support-info" data-support-text="<?php echo esc_attr( $support_text ); ?>">
                <i class="dashicons-before dashicons-clipboard"></i>
                <strong><?php esc_html_e( 'Copy Support Info', 'email-encoder-bundle' ); ?></strong>
            </a>
        </p>

    </div>

</div>
