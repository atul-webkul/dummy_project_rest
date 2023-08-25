<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles external link template at admin end.
 */

namespace WkMobikulMultivendorApi\Templates\Admin\Externallinks;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_External_links' ) ) {
    /**
     *
     */
    class Mkwc_External_links
    {
        /**
         * Configuration form
         */
        function mkwc_external_links()
        {
            ?>
            <div class="wrap">
                <h1><?php echo __( 'External Links', 'mobikul_marketplace' ); ?></h1>

                <?php settings_errors(); ?>

                <form method="POST" action="options.php">
                    <?php

                    settings_fields( 'apilinkssetting' );

                    do_settings_sections( 'apilinkssetting' );

                    $links = get_option( 'mkwc_external_links' );

                    ?>

                    <p><button id="add-more-external-links" class="button-secondary" data-link-count="<?php
                    if ( $links && isset( $links['links'] ) && $links['links'] )
                        echo count( $links['links'] );
                    else
                        echo 1;
                    ?>"><?php echo __( 'Add Row', 'mobikul_marketplace' ); ?></button></p>

                    <table class="form-table external-links">
                    <thead>
                        <tr>
                        <th></th>
                        <th><?php echo __( 'Title', 'mobikul_marketplace' ); ?></th>
                        <th><?php echo __( 'Link', 'mobikul_marketplace' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( $links && isset( $links['links'] ) && $links['links'] ): ?>
                        <?php foreach ( $links['links'] as $key => $value ) : ?>
                            <?php $j = $key + 1; ?>
                            <tr valign="top">
                            <th scope="row" class="titledesc">
                                <label for="api-external-link-<?php echo $j; ?>"><?php echo __( 'Link ' . $j, 'mobikul_marketplace' ); ?></label>
                            </th>
                            <td>
                                <input type="text" name="mkwc_external_links[title][]" value="<?php echo isset( $links['title'][ $key ] ) ? $links['title'][ $key ] : ''; ?>" />
                            </td>
                            <td>
                                <input type="text" id="api-external-link-<?php echo $j; ?>" name="mkwc_external_links[links][]" value="<?php echo isset( $links['links'][ $key ] ) ? $links['links'][ $key ] : ''; ?>" />
                                <span class="dashicons dashicons-dismiss remove-link-row" style="cursor: pointer"></span>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr valign="top">
                            <th scope="row" class="titledesc">
                            <label for="api-external-link"><?php echo __( 'Link 1', 'mobikul_marketplace' ); ?></label>
                            </th>
                            <td>
                            <input type="text" name="mkwc_external_links[title][]" value="" />
                            </td>
                            <td>
                            <input type="text" id="api-external-link" name="mkwc_external_links[links][]" value="" />
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    </table>

                    <?php submit_button(); ?>
                </form>
                </div>

                <script id="tmpl-wk_api_external_link_row" type="text/html">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                    <label for="api-external-link">Link {{{data.linkCount}}}</label>
                    </th>
                    <td>
                    <input type="text" name="mkwc_external_links[title][]" value="" />
                    </td>
                    <td>
                    <input type="text" id="api-external-link" name="mkwc_external_links[links][]" value="" />
                    <span class="dashicons dashicons-dismiss remove-link-row" style="cursor: pointer"></span>
                    </td>
                </tr>
                </script>
            <?php
        }
    }
}
