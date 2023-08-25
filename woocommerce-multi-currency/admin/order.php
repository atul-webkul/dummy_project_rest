<?php

/*
Class Name: WOOMULTI_CURRENCY_Admin_Order
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015-2017 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_Admin_Order {

	function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ), 1 ); //add_meta_boxes replace for admin_init
	}

	/**
	 * Add metabox to order post
	 */
	public function add_metabox() {
		add_meta_box( 'wmc_order_metabox', __( 'Currency Information', 'woocommerce-multi-currency' ), array(
			$this,
			'order_metabox'
		), 'shop_order', 'side', 'default' );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'currency_columns' ), 2 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'order_total_in_base_currency' ), 12 );
		add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'get_formatted_order_total' ), 10, 4 );
	}

	public function currency_columns( $col ) {
		global $post, $the_order;


		if ( $col == 'order_total' ) {
			if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
				$the_order = wc_get_order( $post->ID );
			}
			?>
            <div class="wmc-order-currency">
				<?php echo esc_html__( 'Currency: ', 'woocommerce-multi-currency' ) . get_post_meta( $the_order->get_id(), '_order_currency', true ); ?>
            </div>
			<?php
		}
	}

	public function order_total_in_base_currency( $col ) {
		global $post, $the_order;


		if ( $col == 'order_total' ) {
			if ( empty( $the_order ) || $the_order->get_id() !== $post->ID ) {
				$the_order = wc_get_order( $post->ID );
			}
			$order_currency = get_post_meta( $post->ID, '_order_currency', true );
			$wmc_order_info = get_post_meta( $post->ID, 'wmc_order_info', true );
			if ( is_array( $wmc_order_info ) && count( $wmc_order_info ) ) {
				foreach ( $wmc_order_info as $code => $currency_info ) {
					if ( isset( $currency_info['is_main'] ) && $currency_info['is_main'] == 1 && isset( $wmc_order_info[ $order_currency ] ) ) {
						if ( $order_currency != $code ) {
							$price_in_base_currency = $the_order->get_total() / $wmc_order_info[ $order_currency ]['rate'];

							?>
                            <p style="color:red">
								<?php echo $code . ': ' ?><span><?php echo wc_price( $price_in_base_currency, array(
										'currency' => $code,
										'decimals' => isset( $wmc_order_info[ $order_currency ]['decimals'] ) ? $wmc_order_info[ $order_currency ]['decimals'] : 2
									) ) ?>
                                </span>
                            </p>
							<?php
						}
						break;
					}
				}
			}

		}
	}

	/**
	 * @param $post
	 */
	public function order_metabox( $post ) {
		$order = new WC_Order( $post->ID );
		global $wpdb;

		$query = "SELECT * FROM {$wpdb->postmeta} WHERE post_id = 134";

		$result = $wpdb->get_results($query);

		$order_currency = get_post_meta( $order->get_id(), '_order_currency', true );
		$wmc_order_info = get_post_meta( $order->get_id(), 'wmc_order_info', true );

		//		$rate           = 0;
		$has_info = 1;
		if ( ! isset( $wmc_order_info ) || ! is_array( $wmc_order_info ) ) {
			$has_info = 0;
		}

		?>
        <div id="wmc_order_metabox">
			<?php if ( ! $has_info ) {
				$wmc_order_base_currency = $order_currency;
				$rate                    = 1;
			} else {
				foreach ( $wmc_order_info as $code => $currency_info ) {
					if ( isset( $currency_info['is_main'] ) && $currency_info['is_main'] == 1 ) {
						$wmc_order_base_currency = $code;
						break;
					}
				}

				$rate = $wmc_order_info[ $order_currency ]['rate'];
			}
			?>
            <div id="wmc_order_currency_text">
                <p>
					<?php esc_html_e( 'Currency', 'woocommerce-multi-currency' ); ?> :
                    <span><?php echo $order_currency; ?></span>
                </p>
            </div>
            <div id="wmc_order_base_currency">
                <p>
					<?php esc_html_e( 'Base on Currency', 'woocommerce-multi-currency' ); ?>
                    : <span><?php echo $wmc_order_base_currency; ?></span>
                </p>
            </div>
            <div id="wmc_order_base_currency">
                <p>
					<?php esc_html_e( 'Currency Rate', 'woocommerce-multi-currency' ); ?>
                    : <span><?php echo $rate; ?></span>
                </p>
            </div>
			<?php ?>
        </div>
		<?php
		do_action( 'wmc_after_currency_information', $post );
	}


	public function get_formatted_order_total( $formatted_total, $order, $tax_display, $display_refunded ) {
		if ( ! get_post_meta( $order->get_id(), 'wmc_order_info', true ) ) {
			return $formatted_total;
		}
		$order_currency  = get_post_meta( $order->get_id(), '_order_currency', true );
		$wmc_order_info  = get_post_meta( $order->get_id(), 'wmc_order_info', true );
		$total           = get_post_meta( $order->get_id(), '_order_total', true );
		$decimal         = intval( $wmc_order_info[ $order_currency ]['decimals'] );
		$formatted_total = wc_price( $total, array(
			'currency' => $order_currency,
			'decimals' => $decimal
		) );

		$order_total    = $order->get_total();
		$total_refunded = $order->get_total_refunded();
		$tax_string     = '';

		// Tax for inclusive prices.
//        if ($tax_display){
		if ( wc_tax_enabled() && 'incl' === $tax_display ) {
			$tax_string_array = array();
			$tax_totals       = $order->get_tax_totals();

//			if ($display_refunded){

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( $tax_totals as $code => $tax ) {
					$tax_amount         = ( $total_refunded && $display_refunded ) ? wc_price( WC_Tax::round( $tax->amount - $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ) ), array(
						'currency' => $order->get_currency(),
						'decimals' => $decimal
					) ) : $tax->formatted_amount;
					$tax_string_array[] = sprintf( '%s %s', $tax_amount, $tax->label );
				}
			} elseif ( ! empty( $tax_totals ) ) {
				$tax_amount         = ( $total_refunded && $display_refunded ) ? $order->get_total_tax() - $order->get_total_tax_refunded() : $order->get_total_tax();
				$tax_string_array[] = sprintf( '%s %s', wc_price( $tax_amount, array(
					'currency' => $order->get_currency(),
					'decimals' => $decimal
				) ), WC()->countries->tax_or_vat() );
			}

			if ( ! empty( $tax_string_array ) ) {
				/* translators: %s: taxes */
				$tax_string = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) ) . '</small>';
			}
		}

		if ( $total_refunded && $display_refunded ) {
			$formatted_total = '<del>' . strip_tags( $formatted_total ) . '</del> <ins>' . wc_price( $order_total - $total_refunded, array(
					'currency' => $order->get_currency(),
					'decimals' => $decimal
				) ) . $tax_string . '</ins>';
		} else {
			$formatted_total .= $tax_string;
		}
//		}
//		}

		/**
		 * Filter WooCommerce formatted order total.
		 *
		 * @param string $formatted_total Total to display.
		 * @param WC_Order $order Order data.
		 * @param string $tax_display Type of tax display.
		 * @param bool $display_refunded If should include refunded value.
		 */

		return $formatted_total;
	}
}
