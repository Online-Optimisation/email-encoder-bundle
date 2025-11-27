
<p><?php _e('If you like you can also create you own secured emails manually with this form. Just copy/paste the generated code and put it in your post, page or template. We choose automatically the best method for you, based on your settings.', 'email-encoder-bundle') ?></p>

<hr style="border:1px solid #FFF; border-top:1px solid #EEE;" />

<?= $encoder_form ?>

<hr style="border:1px solid #FFF; border-top:1px solid #EEE;"/>


<?php if ( $is_form_frontend ) : ?>

    <p class="description"><?php _e('You can also put the encoder form on your site by using the shortcode <code>[eeb_form]</code> or the template function <code>eeb_form()</code>.', 'email-encoder-bundle') ?></p>

<?php else : ?>

    <p class="description"><?php _e('In case you want to display the Email Encoder form within the frontend, you can activate it inside of the Advanced settings.', 'email-encoder-bundle') ?></p>

<?php endif ?>