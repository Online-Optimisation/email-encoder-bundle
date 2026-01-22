<div id="support" class="postbox">
    <div class="inside">

        <?php $data = $this->plugin()->settings->get_saved() ?>
        <?php $encoded = base64_encode( gzencode( json_encode( $data ), 9 ) ) ?>
        <?php $decoded = json_decode( gzdecode( base64_decode( $encoded ) ) ) ?>

        <?php print_r( $encoded ) ?>
        <?php print_r( $decoded ) ?>

    </div>
</div>