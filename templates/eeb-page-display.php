<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$currentScreen = get_current_screen();
$columnCount = (1 == $currentScreen->get_columns()) ? 1 : 2;
$mulsitie_slug = ( is_multisite() ) ? 'network/' : '';

?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if( ! empty( $display_notices ) ) : ?>
        <div class="eeb-admin-notices">
            <?php foreach( $display_notices as $single_notice ) : ?>
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin notices contain safe HTML built by create_admin_notice() with properly escaped content.
                echo $single_notice;
                ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- settings_fields() is a WordPress core function that outputs safe HTML (nonce, action, and option page hidden fields).
        settings_fields( $this->getPageName() );
        ?>

        <input type="hidden" name="<?php echo esc_attr( $this->getPageName() ); ?>_nonce" value="<?php echo esc_attr( wp_create_nonce( $this->getPageName() ) ); ?>">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-<?php echo esc_attr( $columnCount ); ?>">
                <?php include( 'widgets/main.php' ); ?>

                <div id="postbox-container-1" >
                    <div class="postbox-container">
                        <?php include( 'widgets/sidebar.php' ); ?>
                        <?php // include( 'widgets/support.php' ); ?>
                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <?php do_meta_boxes('', 'normal', ''); ?>
                </div>

            </div>
        </div>
    </form>
</div>
