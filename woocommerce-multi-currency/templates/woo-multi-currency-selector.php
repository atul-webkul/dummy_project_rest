<?php
/**
 * Show widget
 *
 * This template can be overridden by copying it to yourtheme/woo-currency/woo-currency_widget.php.
 *
 * @author        Cuong Nguyen
 * @package       Woo-currency/Templates
 * @version       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$currencies       = $settings->get_list_currencies();
$current_currency = $settings->get_current_currency();
$links            = $settings->get_links();
$currency_name    = get_woocommerce_currencies();
?>
<div class="woocommerce-multi-currency shortcode">
    <div class="wmc-currency">
        <select class="wmc-nav"
                onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
			<?php
			foreach ( $links as $code => $link ) { ?>

                <option <?php selected( $current_currency, $code ) ?>
                        value="<?php echo esc_url( $link ) ?>"><?php echo esc_html( $currency_name[ $code ] ) ?></option>

			<?php } ?>
        </select>
    </div>
</div>