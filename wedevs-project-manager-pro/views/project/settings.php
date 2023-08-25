<?php
$updated = false;
if ( isset( $_POST['sub_settings'] ) ) {
    unset( $_POST['_wpnonce'] );
    unset( $_POST['_wp_http_referer'] );

    $updated        = true;
    $saved_settings = apply_filters( 'cpm_project_settings_data', $_POST, $project_id );

    update_post_meta( $project_id, '_settings', $saved_settings );
}

$settings = get_post_meta( $project_id, '_settings', true );
$labels   = cpm_settings_label();
cpm_get_header( __( 'Settings', 'cpm-pro' ), $project_id );
?>

<?php if ( $updated ) { ?>
    <div class="updated">
        <p><?php _e( 'Changes saved successfully.', 'cpm-pro' ); ?></p>
    </div>
<?php } ?>

<form id="cpm-settings" method="post" action="">

    <?php wp_nonce_field( 'cpm_settings_nonce' ); ?>

    <table class="widefat cpm-table">
        <thead >
            <th><?php _e( 'Co-worker', 'cpm-pro' ); ?></th>
            <th><?php _e( 'Client', 'cpm-pro' ); ?></th>
        </thead>
        <tbody>
            <?php
            foreach ( $labels as $section => $name ) {
                $tr_class = str_replace( ' ', '-', strtolower( $section ) );
                ?>
                <tr class="<?php echo $tr_class; ?>">
                    <thead><th colspan="2"><?php _e($section, 'cpm-pro') ; ?></th></thead>
                </tr>

            <?php foreach ( $name as $key => $field ) { ?>
                <tr class="<?php echo $tr_class; ?>">
                    <td>
                        <label>
                            <?php $settings['co_worker'][$key] = isset( $settings['co_worker'][$key] ) ? $settings['co_worker'][$key] : ''; ?>
                            <input type="checkbox" <?php checked( 'yes', $settings['co_worker'][$key] ); ?> value="yes" name="co_worker[<?php echo $key; ?>]">
                            <?php echo _e($field, 'cpm-pro'); ?>
                        </label>
                    </td>
                    <td>
                        <label>
                            <?php $settings['client'][$key]    = isset( $settings['client'][$key] ) ? $settings['client'][$key] : ''; ?>
                            <input type="checkbox" <?php checked( 'yes', $settings['client'][$key] ); ?> value="yes" name="client[<?php echo $key; ?>]">
                            <?php echo _e($field, 'cpm-pro'); ?>
                        </label>
                    </td>
                </tr>
                <?php
            }
        }

        do_action( 'cpm_project_settings', $project_id, $settings );
        ?>

        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="submit">
                    <input type="submit" class="button-primary" name="sub_settings" value="<?php echo esc_attr_e( 'Save Changes', 'cpm-pro' ); ?>">
                </td>
            </tr>
        </tfoot>
    </table>
</form>

