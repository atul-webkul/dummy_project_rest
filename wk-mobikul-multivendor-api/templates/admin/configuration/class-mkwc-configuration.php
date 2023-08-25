<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles configuration template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_Configuration' ) ) {
    /**
     *
     */
    class Mkwc_Configuration
    {
        /**
         * Configuration form
         */
        function mkwc_configuration_form()
        {
            ?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php echo __( 'Configuration', 'mobikul_marketplace' ); ?></h1>
                <?php
                if ( isset( $_GET['settings-updated'] ) ) {
                    echo '<div class="notice notice-success is-dismissible"><p><strong>';

                    echo __( 'Setting save successfully.', 'mobikul_marketplace' ).'</strong></p>';

                    echo '</div>';
                }
                ?>
                <form method="POST" action="options.php">
                    <?php

                    settings_fields( 'mkwc_api_setting' );

                    do_settings_sections( 'mkwc_api_setting' );

                    ?>

                    <table class="form-table">
                      	<tbody>
                            <tr valign="top">
                  	            <th scope="row" class="titledesc" style="min-width:300px;">
                                    <label for="mkwc-google-key"><?php echo __( 'Google Authorization Key', 'mobikul_marketplace' ); ?></label>
                  	            </th>

                  	            <td>
                                    <input type="text" id="mkwc-google-key" name="mkwc_api_google_auth_code" value="<?php echo get_option( 'mkwc_api_google_auth_code' ); ?>" style="min-width:350px;" />
                  	            </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row" class="titledesc" style="min-width:300px;">
                                    <label for="mkwc-api-username"><?php echo __( 'User ID', 'mobikul_marketplace' ); ?></label>
                  	            </th>

                  	            <td>
                                    <input type="text" id="mkwc-api-username" name="mkwc_api_user_name" value="<?php echo get_option( 'mkwc_api_user_name' ); ?>" required style="min-width:350px;" />
                  	            </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row" class="titledesc" style="min-width:300px;">
                                    <label for="mkwc-api-password"><?php echo __( 'Password', 'mobikul_marketplace' ); ?></label>
                                </th>

                                <td>
                                    <input type="text" id="mkwc-api-password" name="mkwc_api_user_key" value="<?php echo $this->mkwc_generate_random_password( 30 ); ?>" required style="min-width:350px;" />
                                </td>
                            </tr>
                      	</tbody>
                    </table>

                    <?php submit_button(); ?>

                </form>
            </div>
            <?php
        }

        /**
         * Generate random password
         * @param $length
         * @return $password
         */
        function mkwc_generate_random_password( $length ) {
            if ( '' == get_option( 'mkwc_api_user_key' ) ) {
                $password = substr( str_shuffle( str_repeat( $x = 'fdfe012tswtrwerwe345etrte6789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );

                return $password;
            } else {
                $password = get_option( 'mkwc_api_user_key' );

                return $password;
            }
        }
    }
}
