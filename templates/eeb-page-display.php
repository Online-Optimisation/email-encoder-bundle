<?php

$currentScreen = get_current_screen();
$columnCount = (1 == $currentScreen->get_columns()) ? 1 : 2;
$mulsitie_slug = ( is_multisite() ) ? 'network/' : '';

?>

<div class="wrap">
    <h1><?php echo get_admin_page_title() ?></h1>

    <?php if( ! empty( $display_notices ) ) : ?>
        <div class="eeb-admin-notices">
            <?php foreach( $display_notices as $single_notice ) : ?>
                <?php echo $single_notice; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php settings_fields( $this->getPageName() ); ?>

        <input type="hidden" name="<?php echo $this->getPageName(); ?>_nonce" value="<?php echo wp_create_nonce( $this->getPageName() ) ?>">

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-<?php echo $columnCount; ?>">
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
