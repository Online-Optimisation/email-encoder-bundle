<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$settings = $this->getSetting();
$advanced_settings = (bool) $this->getSetting( 'advanced_settings', true );

?>
<div id="post-body-content">
    <div class="postbox">
        <div class="inside">
            <fieldset>
                <table class="form-table">
                    <?php foreach( $settings as $setting_name => $setting ) :
                    $hide_main_layer = '';

                    if( ! $advanced_settings && isset( $setting['advanced'] ) && $setting['advanced'] === true ){
                        $hide_main_layer = 'style="display:none !important;"';
                    }

                    //Hide foggy email integration
                    if( $setting['title'] === 'Foggy Email API Key' && ! isset( $_GET['displayfoggy'] ) ){
                        continue;
                    }

                    //Validate settings value
                    $main_settings_value = '';
                    if( isset( $setting['value'] ) ){
                        $main_settings_value = $setting['value'];
                    }

                    $is_checked = ( $setting['type'] == 'checkbox' && ( $main_settings_value === 1 || $main_settings_value === '1' ) ) ? 'checked' : '';
                    $value = ( $setting['type'] != 'checkbox' && $setting['type'] != 'multi-input' ) ? $main_settings_value : '1';

                    ?>
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $hide_main_layer is a controlled inline style string built internally. ?>
                        <tr valign="top" <?php echo $hide_main_layer; ?>>
                            <th scope="row">
                                <?php echo esc_html( $setting['title'] ); ?>
                            </th>
                            <td scope="row" valign="top">
                                <p>
                                    <?php if( $setting['type'] === 'multi-input' ) : ?>
                                        <?php foreach( $setting['inputs'] as $si_key => $data ) :
                                            $hide_sub_layer = '';

                                            if( ! $advanced_settings && isset( $data['advanced'] ) && $data['advanced'] === true ){
                                                $hide_sub_layer = 'style="display:none !important;"';
                                            }

                                            //Always set the radio value of single inputs to their key
                                            if( $setting['input-type'] === 'radio' ){
                                                $data['value'] = $si_key;
                                            }

                                            $mi_is_checked = ( $setting['input-type'] == 'checkbox' && ( isset( $data['value'] ) && ( $data['value'] === 1 || $data['value'] === '1' ) ) ) ? 'checked' : '';
                                            $mi_value = ( $setting['input-type'] != 'checkbox' ) ? $data['value'] : '1';
                                            $si_name = $si_key;

                                            //Re-validate for radio inputs
                                            if( $setting['input-type'] == 'radio' ){
                                                $si_name = $setting_name;

                                                //Check radio button
                                                if( (string) $main_settings_value === (string) $data['value'] ){
                                                    $mi_is_checked = 'checked';
                                                }
                                            }
                                            ?>
                                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $hide_sub_layer is a controlled inline style string built internally. ?>
                                            <p <?php echo $hide_sub_layer; ?>>
                                                <input id="<?php echo esc_attr( $si_name . '_' . $si_key ); ?>" name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $si_name ); ?>]" type="<?php echo esc_attr( $setting['input-type'] ); ?>" class="regular-text" value="<?php echo esc_attr( $mi_value ); ?>" <?php echo esc_attr( $mi_is_checked ); ?> />
                                                <label for="<?php echo esc_attr( $si_name . '_' . $si_key ); ?>">
                                                    <?php echo wp_kses_post( $data['label'] ); ?>
                                                </label>
                                            </p>
                                            <?php if( isset( $data['description'] ) ) : ?>
                                            <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $hide_sub_layer is a controlled inline style string built internally. ?>
                                            <p class="description" <?php echo $hide_sub_layer; ?>>
                                                <?php if( in_array( $setting['input-type'], array( 'checkbox', 'radio' ) ) ) : ?>
                                                    <input name="email-encoder-bundle-hidden-margin" type="radio" class="regular-text" value="" style="visibility:hidden !important;pointer-events:none !important;"/>
                                                <?php endif; ?>
                                                <?php echo wp_kses_post( $data['description'] ); ?>
                                            </p>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <input id="<?php echo esc_attr( $setting['id'] ); ?>" name="<?php echo esc_attr( $this->getSettingsKey() ); ?>[<?php echo esc_attr( $setting_name ); ?>]" type="<?php echo esc_attr( $setting['type'] ); ?>" class="regular-text" value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $is_checked ); ?> />
                                        <?php if( isset( $setting['label'] ) ) : ?>
                                            <label for="<?php echo esc_attr( $setting_name ); ?>">
                                                <?php echo wp_kses_post( $setting['label'] ); ?>
                                            </label>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>
                                <?php if( isset( $setting['description'] ) ) : ?>
                                    <p class="description">
                                        <?php echo wp_kses_post( $setting['description'] ); ?>
                                    </p>
                                <?php endif; ?>
                            </td>
                            <td>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </fieldset>

            <p>
                <?php submit_button( __( 'Save all', 'email-encoder-bundle' ) ); ?>
            </p>
        </div>
    </div>
</div>
