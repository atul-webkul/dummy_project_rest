<?php

/*
Class Name: WP_SM_Admin_Settings
Author: Andy Ha (support@villatheme.com)
Author URI: http://villatheme.com
Copyright 2015-2017 villatheme.com. All rights reserved.
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_Admin_Settings {
	static $params;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'save_meta_boxes' ) );
		add_action( 'wp_ajax_woomulticurrency_exchange', array( $this, 'woomulticurrency_exchange' ) );
		add_action( 'wp_ajax_nopriv_woomulticurrency_exchange', array( $this, 'woomulticurrency_exchange' ) );
	}

	/**
	 * Get list shortcode
	 * @return array
	 */
	public static function page_callback() {
		self::$params = get_option( 'woo_multi_currency_params', array() );
		?>
        <div class="wrap woocommerce-multi-currency">
            <h2><?php esc_attr_e( 'WooCommerce Multi Currency Settings', 'woocommerce-multi-currency' ) ?></h2>
            <form method="post" action="" class="vi-ui form">
				<?php echo ent2ncr( self::set_nonce() ) ?>

                <div class="vi-ui attached tabular menu">
                    <div class="item active" data-tab="general">
                        <a href="#general"><?php esc_html_e( 'General', 'woocommerce-multi-currency' ) ?></a>
                    </div>
                    <div class="item" data-tab="location">
                        <a href="#location"><?php esc_html_e( 'Location', 'woocommerce-multi-currency' ) ?></a>
                    </div>
                    <div class="item" data-tab="checkout">
                        <a href="#checkout"><?php esc_html_e( 'Checkout', 'woocommerce-multi-currency' ) ?></a>
                    </div>
                    <div class="item" data-tab="design">
                        <a href="#design"><?php esc_html_e( 'Design', 'woocommerce-multi-currency' ) ?></a>
                    </div>
                    <div class="item" data-tab="update">
                        <a href="#update"><?php esc_html_e( 'Update', 'woocommerce-multi-currency' ) ?></a>
                    </div>
                </div>
                <div class="vi-ui bottom attached tab segment active" data-tab="general">
                    <!-- Tab Content !-->
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable' ) ?>">
									<?php esc_html_e( 'Enable', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable' ) ?>"/>
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_fixed_price' ) ?>">
									<?php esc_html_e( 'Fixed Price', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_fixed_price' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_fixed_price' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_fixed_price' ) ?>"/>
                                    <label></label>
                                </div>
                                <p class="description"><?php esc_html_e( 'Set up product price in each currency manually, this price will overwrite the calculated price.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'use_session' ) ?>">
									<?php esc_html_e( 'Use SESSION', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'use_session' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'use_session' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'use_session' ) ?>"/>
                                    <label></label>
                                </div>
                                <p class="description"><?php esc_html_e( 'Use SESSION instead of COOKIE.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'price_switcher' ) ?>">
									<?php esc_html_e( 'Currency Price Switcher', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
								<?php $price_switcher = self::get_field( 'price_switcher', 0 ) ?>
                                <select name="<?php echo self::set_field( 'price_switcher' ) ?>">
                                    <option <?php selected( $price_switcher, 0 ) ?>
                                            value="0"><?php esc_html_e( 'Not Show', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( $price_switcher, 1 ) ?>
                                            value="1"><?php esc_html_e( 'Flag', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( $price_switcher, 2 ) ?>
                                            value="2"><?php esc_html_e( 'Flag + Currency Code', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( $price_switcher, 3 ) ?>
                                            value="3"><?php esc_html_e( 'Flag + Price', 'woocommerce-multi-currency' ) ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'Display a currency switcher under product price in single product pages.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_switch_currency_by_js' ) ?>">
									<?php esc_html_e( 'Switch Currency by JS', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_switch_currency_by_js' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_switch_currency_by_js' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_switch_currency_by_js' ) ?>"/>
                                    <label></label>
                                </div>
                                <p class="description"><?php esc_html_e( 'Currency will be changed but It does not use URL. It is good for SEO. Now It is not compatible with Caching plugins.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row" colspan="2">
                                <label for="<?php echo self::set_field( 'enable_mobile' ) ?>">
									<?php esc_html_e( 'Currency Options', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2">

                                <table class="vi-ui table wmc-currency-options">
                                    <thead>
                                    <tr>
                                        <th class="one wide"><?php esc_html_e( 'Default', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="one wide"><?php esc_html_e( 'Hidden', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="two wide"><?php esc_html_e( 'Currency', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="two wide"><?php esc_html_e( 'Position', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="four wide"
                                            colspan="2"><?php esc_html_e( 'Rate + Exchange Fee', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="two wide"><?php esc_html_e( 'Number of Decimals', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="two wide"><?php esc_html_e( 'Custom symbol', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="one wide"><?php esc_html_e( 'Action', 'woocommerce-multi-currency' ) ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php

									$currencies        = self::get_field( 'currency', array( get_option( 'woocommerce_currency' ) ) );
									$currency_pos      = self::get_field( 'currency_pos', array( get_option( 'woocommerce_currency_pos' ) ) );
									$currency_rate     = self::get_field( 'currency_rate', array( 1 ) );
									$currency_rate_fee = self::get_field( 'currency_rate_fee', array( 0.0000 ) );
									$currency_decimals = self::get_field( 'currency_decimals', array( get_option( 'woocommerce_price_num_decimals' ) ) );
									$currency_custom   = self::get_field( 'currency_custom', array() );
									$currency_hidden   = self::get_field( 'currency_hidden', array() );
									if ( is_array( $currencies ) ) {
										if ( count( array_filter( $currencies ) ) < 1 ) {
											$currencies        = array();
											$currency_pos      = array();
											$currency_rate     = array();
											$currency_rate_fee = array();
											$currency_decimals = array();
											$currency_custom   = array();
										}
									} else {
										$currencies        = array();
										$currency_pos      = array();
										$currency_rate     = array();
										$currency_rate_fee = array();
										$currency_decimals = array();
										$currency_custom   = array();
									}
									$wc_currencies = get_woocommerce_currencies();
									foreach ( $currencies as $key => $currency ) {
										if ( self::get_field( 'currency_default', get_option( 'woocommerce_currency' ) ) == $currency ) {
											$disabled = 'readonly';
										} else {
											$disabled = '';
										}
										?>
                                        <tr class="wmc-currency-data <?php echo $currency . '-currency' ?>">
                                            <td class="collapsing">
                                                <div class="vi-ui toggle checkbox">
                                                    <input type="radio" <?php checked( self::get_field( 'currency_default', get_option( 'woocommerce_currency' ) ), $currency ) ?>
                                                           tabindex="0" class="hidden"
                                                           value="<?php echo esc_attr( $currency ) ?>"
                                                           name="<?php echo self::set_field( 'currency_default' ) ?>"/>
                                                    <label></label>
                                                </div>
                                            </td>
                                            <td class="collapsing"
                                                title="<?php esc_attr_e( 'Hidden currencies on widget, shortcode and sidebar', 'woocommerce-multi-currency' ) ?>"
                                                data-tooltip="<?php esc_attr_e( 'Hidden currencies on widget, shortcode and sidebar', 'woocommerce-multi-currency' ) ?>">
                                                <select name="<?php echo self::set_field( 'currency_hidden', 1 ) ?>">
                                                    <option <?php selected( self::data_isset( $currency_hidden, $key, 0 ), 0 ) ?>
                                                            value="0"><?php esc_html_e( 'No', 'woocommerce-multi-currency' ) ?></option>
                                                    <option <?php selected( self::data_isset( $currency_hidden, $key ), 1 ) ?>
                                                            value="1"><?php esc_html_e( 'Yes', 'woocommerce-multi-currency' ) ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="<?php echo self::set_field( 'currency', 1 ) ?>"
                                                        class="vi-ui select2">
													<?php foreach ( $wc_currencies as $k => $wc_currency ) { ?>
                                                        <option <?php selected( $currency, $k ) ?>
                                                                value="<?php echo esc_attr( $k ) ?>"><?php echo $k . '-' . esc_html( $wc_currency ) . ' (' . get_woocommerce_currency_symbol( $k ) . ')' ?></option>
													<?php } ?>
                                                </select>
                                            <td>
                                                <select name="<?php echo self::set_field( 'currency_pos', 1 ) ?>">
                                                    <option <?php selected( self::data_isset( $currency_pos, $key ), 'left' ) ?>
                                                            value="left"><?php esc_html_e( 'Left $99', 'woocommerce-multi-currency' ) ?></option>
                                                    <option <?php selected( self::data_isset( $currency_pos, $key ), 'right' ) ?>
                                                            value="right"><?php esc_html_e( 'Right 99$', 'woocommerce-multi-currency' ) ?></option>
                                                    <option <?php selected( self::data_isset( $currency_pos, $key ), 'left_space' ) ?>
                                                            value="left_space"><?php esc_html_e( 'Left with space $ 99', 'woocommerce-multi-currency' ) ?></option>
                                                    <option <?php selected( self::data_isset( $currency_pos, $key ), 'right_space' ) ?>
                                                            value="right_space"><?php esc_html_e( 'Right with space 99 $', 'woocommerce-multi-currency' ) ?></option>
                                                </select>
                                            </td>
                                            <td>

                                                <input <?php echo $disabled ?> type="text"
                                                                               name="<?php echo self::set_field( 'currency_rate', 1 ) ?>"
                                                                               value="<?php echo self::data_isset( $currency_rate, $key, '1' ) ?>"/>

                                            </td>
                                            <td>
                                                <div class="vi-ui left icon input"
                                                     data-tooltip="<?php esc_attr_e( 'It is fixed rate. Eg: (Original rate)1.62 + 0.1(Exchange fee rate) = 1.72(End rate)', 'woocommerce-multi-currency' ) ?>">
                                                    <i class="vi-ui icon plus"></i>
                                                    <input <?php echo $disabled ?> type="number"
                                                                                   name="<?php echo self::set_field( 'currency_rate_fee', 1 ) ?>"
                                                                                   value="<?php echo self::data_isset( $currency_rate_fee, $key, '0.0000' ) ?>"
                                                                                   step="0.0001"/>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="<?php echo self::set_field( 'currency_decimals', 1 ) ?>"
                                                       value="<?php echo self::data_isset( $currency_decimals, $key, '2' ) ?>"/>
                                            </td>
                                            <td>
                                                <input type="text" placeholder="eg: CAD $"
                                                       name="<?php echo self::set_field( 'currency_custom', 1 ) ?>"
                                                       value="<?php echo self::data_isset( $currency_custom, $key ) ?>"/>
                                            </td>
                                            <td>
                                                <div class="vi-ui buttons">
                                                    <div class="vi-ui  small icon button wmc-update-rate"
                                                         title="<?php esc_attr_e( 'Update Rate', 'woocommerce-multi-currency' ) ?>"
                                                         data-tooltip="<?php esc_attr_e( 'Update Rate', 'woocommerce-multi-currency' ) ?>">
                                                        <i class="cloud download icon"></i>

                                                    </div>
                                                    <div class="vi-ui  small icon red button wmc-remove-currency"
                                                         title="<?php esc_attr_e( 'Remove', 'woocommerce-multi-currency' ) ?>"
                                                         data-tooltip="<?php esc_attr_e( 'Remove', 'woocommerce-multi-currency' ) ?>">
                                                        <i class="trash icon"></i>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
									<?php } ?>

                                    </tbody>
                                    <tfoot class="full-width">
                                    <tr>

                                        <th colspan="9">
                                            <div class="vi-ui right floated green labeled icon button wmc-add-currency">
                                                <i class="money icon"></i> <?php esc_html_e( 'Add Currency', 'woocommerce-multi-currency' ) ?>
                                            </div>
                                            <div class="vi-ui right floated labeled icon button wmc-update-rates">
                                                <i class="in cart icon"></i> <?php esc_html_e( 'Update All Rates', 'woocommerce-multi-currency' ) ?>
                                            </div>

                                        </th>
                                    </tr>
                                    </tfoot>
                                </table>

                                <p class="vi-ui message yellow"><?php esc_html_e( 'Custom symbol: You can set custom symbol for each currency in your list and how to it will be displayed (used when you have many currency have same symbol). Leave it empty to used default symbol. Example: if you set US$ for US dolar, system will display US$100 instead of $100 like default. Or you can use with pramater #PRICE# to display price in special format, example: if you set US #PRICE# $, system will display: US 100 $.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Location !-->
                <div class="vi-ui bottom attached tab segment" data-tab="location">
                    <!-- Tab Content !-->
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">

                            <th scope="row">
                                <label for="<?php echo self::set_field( 'auto_detect' ) ?>">
									<?php esc_html_e( 'Auto Detect', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <select name="<?php echo self::set_field( 'auto_detect' ) ?>">
                                    <option <?php selected( self::get_field( 'auto_detect' ), 0 ) ?>
                                            value="0"><?php esc_html_e( 'No', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'auto_detect' ), 1 ) ?>
                                            value="1"><?php esc_html_e( 'Auto select currency', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'auto_detect' ), 2 ) ?>
                                            value="2"><?php esc_html_e( 'Approximate Price', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'auto_detect' ), 3 ) ?>
                                            value="3"><?php esc_html_e( 'Language Polylang', 'woocommerce-multi-currency' ) ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">

                            <th scope="row">
                                <label for="<?php echo self::set_field( 'geo_api' ) ?>">
									<?php esc_html_e( 'Geo API', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <select name="<?php echo self::set_field( 'geo_api' ) ?>">
                                    <option <?php selected( self::get_field( 'geo_api' ), 0 ) ?>
                                            value="0"><?php esc_html_e( 'WooCommerce', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'geo_api' ), 1 ) ?>
                                            value="1"><?php esc_html_e( 'External', 'woocommerce-multi-currency' ) ?></option>
                                </select>
                                <p class="description"><?php esc_html_e( 'API will help detect customer country code base on IP address.', 'woocommerce-multi-currency' ) ?></p>

                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_currency_by_country' ) ?>">
									<?php esc_html_e( 'Currency by Country', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_currency_by_country' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_currency_by_country' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_currency_by_country' ) ?>"/>
                                    <label></label>
                                </div>
                                <p class="description"><?php esc_html_e( 'Only working with AUTO SELECT CURRENCY feature. Currency will be selected base on country.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>

                        <tr valign="top">

                            <td colspan="2">
                                <table class="vi-ui table">
                                    <thead>
                                    <tr>
                                        <th class="two wide"><?php esc_html_e( 'Currency', 'woocommerce-multi-currency' ) ?></th>
                                        <th><?php esc_html_e( 'Countries', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="four wide"><?php esc_html_e( 'Actions', 'woocommerce-multi-currency' ) ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php foreach ( $currencies as $key => $currency ) {
										$wc_countries = $countries = WC()->countries->countries;

										?>
                                        <tr>
                                            <td><?php echo esc_html( '(' . get_woocommerce_currency_symbol( $currency ) . ') ' . $currency ) ?></td>
                                            <td>
                                                <select multiple="multiple"
                                                        name="<?php echo self::set_field( $currency . '_by_country', 1 ) ?>"
                                                        class="vi-ui select2-multiple"
                                                        data-placeholder="<?php esc_attr_e( 'Please select countries', 'woocommerce-multi-currency' ) ?>">
													<?php
													$countries_assign = self::get_field( $currency . '_by_country', array() );

													foreach ( $wc_countries as $k => $wc_country ) {
														$selected = '';

														if ( in_array( $k, $countries_assign ) ) {
															$selected = 'selected="selected"';
														}

														?>
                                                        <option <?php echo esc_attr( $selected ) ?>
                                                                value="<?php echo esc_attr( $k ) ?>"><?php echo $wc_country ?></option>
													<?php } ?>
                                                </select>

                                            </td>
                                            <td>
                                                <div class="vi-ui  small button wmc-select-all-countries">
													<?php esc_html_e( 'Select all', 'woocommerce-multi-currency' ) ?>
                                                </div>
                                                <div class="vi-ui  small red button wmc-remove-all-countries">
													<?php esc_html_e( 'Remove All', 'woocommerce-multi-currency' ) ?>
                                                </div>
                                            </td>
                                        </tr>
									<?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!--					Polylang-->
					<?php if ( class_exists( 'Polylang' ) ) { ?>
                        <h3>
							<?php esc_html_e( 'Polylang', 'woocommerce-multi-currency' ) ?>
                        </h3>
                        <table class="optiontable form-table">
                            <tr>
                                <th scope="row">
                                    <label>
										<?php esc_html_e( 'Language switcher', 'woocommerce-multi-currency' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <table class="vi-ui table">
                                        <thead>
                                        <tr>
                                            <th class="four wide"><?php esc_html_e( 'Language', 'woocommerce-multi-currency' ) ?></th>
                                            <th><?php esc_html_e( 'Currency', 'woocommerce-multi-currency' ) ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php
										$languages = pll_languages_list();

										foreach ( $languages as $language ) {

											?>
                                            <tr>
                                                <td><?php echo $language ?></td>
                                                <td>
                                                    <select name="<?php echo self::set_field( $language . '_by_language' ) ?>"
                                                            class="vi-ui"
                                                            data-placeholder="<?php esc_attr_e( 'Please select currency', 'woocommerce-multi-currency' ) ?>">
                                                        <option value="0"><?php echo esc_html__( 'Default', 'woocommerce-multi-currency' ) ?></option>
														<?php
														$l_currency = self::get_field( $language . '_by_language', array() );

														foreach ( $currencies as $currency ) {
															$selected = '';

															if ( $l_currency == $currency ) {
																$selected = 'selected="selected"';
															}

															?>
                                                            <option <?php echo esc_attr( $selected ) ?>
                                                                    value="<?php echo esc_attr( $currency ) ?>"><?php echo $currency . '-' . get_woocommerce_currency_symbol( $currency ) ?></option>
														<?php } ?>
                                                    </select>

                                                </td>
                                            </tr>
										<?php } ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                        </table>
					<?php } ?>
                    <!--					WPML.org-->
					<?php
					if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
						?>
                        <h3>
							<?php esc_html_e( 'WPML.org', 'woocommerce-multi-currency' ) ?>
                        </h3>
                        <table class="optiontable form-table">
                            <tr>
                                <th>
									<?php esc_html_e( 'Enable', 'woocommerce-multi-currency' ) ?>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input id="<?php echo self::set_field( 'enable_wpml' ) ?>"
                                               type="checkbox" <?php checked( self::get_field( 'enable_wpml' ), 1 ) ?>
                                               tabindex="0" class="hidden" value="1"
                                               name="<?php echo self::set_field( 'enable_wpml' ) ?>"/>
                                        <label></label>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'All product fields of WooCommerce Multi Currency will be copied. When you switch language, Currency will change. ', 'woocommerce-multi-currency' ) ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label>
										<?php esc_html_e( 'Language switcher', 'woocommerce-multi-currency' ) ?>
                                    </label>
                                </th>
                                <td>
                                    <table class="vi-ui table">
                                        <thead>
                                        <tr>
                                            <th class="four wide"><?php esc_html_e( 'Language', 'woocommerce-multi-currency' ) ?></th>
                                            <th><?php esc_html_e( 'Currency', 'woocommerce-multi-currency' ) ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php
										$languages = $langs = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
										if ( count( $languages ) ) {
											foreach ( $languages as $key => $language ) {

												?>
                                                <tr>
                                                    <td><?php echo isset( $language['native_name'] ) ? $language['native_name'] : $key ?></td>
                                                    <td>
                                                        <select name="<?php echo self::set_field( $key . '_wpml_by_language' ) ?>"
                                                                class="vi-ui"
                                                                data-placeholder="<?php esc_attr_e( 'Please select currency', 'woocommerce-multi-currency' ) ?>">
                                                            <option value="0"><?php echo esc_html__( 'Default', 'woocommerce-multi-currency' ) ?></option>
															<?php
															$l_currency = self::get_field( $key . '_wpml_by_language', array() );

															foreach ( $currencies as $currency ) {
																$selected = '';

																if ( $l_currency == $currency ) {
																	$selected = 'selected="selected"';
																}

																?>
                                                                <option <?php echo esc_attr( $selected ) ?>
                                                                        value="<?php echo esc_attr( $currency ) ?>"><?php echo $currency . '-' . get_woocommerce_currency_symbol( $currency ) ?></option>
															<?php } ?>
                                                        </select>

                                                    </td>
                                                </tr>
											<?php }
										} ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
					<?php } ?>
                </div>
                <!-- Design !-->
                <div class="vi-ui bottom attached tab segment" data-tab="design">
                    <!-- Tab Content !-->
                    <h3><?php esc_html_e( 'Currencies Bar', 'woocommerce-multi-currency' ) ?></h3>
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_design' ) ?>">
									<?php esc_html_e( 'Enable', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_design' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_design' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_design' ) ?>"/>
                                    <label></label>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Title', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="<?php echo self::set_field( 'design_title' ) ?>"
                                       value="<?php echo self::get_field( 'design_title' ) ?>"/>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'position' ) ?>">
									<?php esc_html_e( 'Position', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui form">
                                    <div class="fields">
                                        <div class="four wide field">
                                            <img src="<?php echo WOOMULTI_CURRENCY_IMAGES . 'position_1.jpg' ?>"
                                                 class="vi-ui centered medium image middle aligned "/>

                                            <div class="vi-ui toggle checkbox center aligned segment">
                                                <input id="<?php echo self::set_field( 'design_position' ) ?>"
                                                       type="radio" <?php checked( self::get_field( 'design_position', 0 ), 0 ) ?>
                                                       tabindex="0" class="hidden" value="0"
                                                       name="<?php echo self::set_field( 'design_position' ) ?>"/>
                                                <label><?php esc_attr_e( 'Left', 'woocommerce-multi-currency' ) ?></label>
                                            </div>

                                        </div>
                                        <div class="two wide field">
                                        </div>

                                        <div class="four wide field">
                                            <img src="<?php echo WOOMULTI_CURRENCY_IMAGES . 'position_2.jpg' ?>"
                                                 class="vi-ui centered medium image middle aligned "/>

                                            <div class="vi-ui toggle checkbox center aligned segment">
                                                <input id="<?php echo self::set_field( 'design_position' ) ?>"
                                                       type="radio" <?php checked( self::get_field( 'design_position' ), 1 ) ?>
                                                       tabindex="0" class="hidden" value="1"
                                                       name="<?php echo self::set_field( 'design_position' ) ?>"/>
                                                <label><?php esc_attr_e( 'Right', 'woocommerce-multi-currency' ) ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_collapse' ) ?>">
									<?php esc_html_e( 'Desktop', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_collapse' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_collapse' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_collapse' ) ?>"/>
                                    <label><?php esc_html_e( 'Enable Collapse', 'woocommerce-multi-currency' ) ?></label>
                                </div>
                                <p class="description"><?php esc_html_e( 'Sidebar will collapse if you have many currencies.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Text color', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <input type="text" class="color-picker"
                                       name="<?php echo self::set_field( 'text_color' ) ?>"
                                       value="<?php echo self::get_field( 'text_color', '#fff' ) ?>"
                                       style="background-color: <?php echo esc_attr( self::get_field( 'text_color', '#fff' ) ) ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Style', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <select name="<?php echo self::set_field( 'sidebar_style' ) ?>">
                                    <option <?php selected( self::get_field( 'sidebar_style' ), 0 ) ?>
                                            value="0"><?php esc_html_e( 'Default', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'sidebar_style' ), 1 ) ?>
                                            value="1"><?php esc_html_e( 'Symbol', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'sidebar_style' ), 2 ) ?>
                                            value="2"><?php esc_html_e( 'Flag', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'sidebar_style' ), 3 ) ?>
                                            value="3"><?php esc_html_e( 'Flag + Currency code', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'sidebar_style' ), 4 ) ?>
                                            value="4"><?php esc_html_e( 'Flag + Currency symbol', 'woocommerce-multi-currency' ) ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Main color', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <input type="text" class="color-picker"
                                       name="<?php echo self::set_field( 'main_color' ) ?>"
                                       value="<?php echo self::get_field( 'main_color', '#f78080' ) ?>"
                                       style="background-color: <?php echo esc_attr( self::get_field( 'main_color', '#f78080' ) ) ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Background color', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <input type="text" class="color-picker"
                                       name="<?php echo self::set_field( 'background_color' ) ?>"
                                       value="<?php echo self::get_field( 'background_color', '#212121' ) ?>"
                                       style="background-color: <?php echo esc_attr( self::get_field( 'background_color', '#212121' ) ) ?>"/>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h3><?php esc_html_e( 'Conditional Tags', 'woocommerce-multi-currency' ) ?></h3>
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'is_checkout' ) ?>">
									<?php esc_html_e( 'Checkout page', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'is_checkout' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'is_checkout' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'is_checkout' ) ?>"/>
                                    <label></label>
                                </div>
                                <p class=""><?php esc_html_e( 'Enable to hide Currencies Bar on Checkout page.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'is_cart' ) ?>">
									<?php esc_html_e( 'Cart page', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'is_cart' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'is_cart' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'is_cart' ) ?>"/>
                                    <label></label>
                                </div>
                                <p class=""><?php esc_html_e( 'Enable to hide Currencies Bar on Cart page.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'conditional_tags' ) ?>">
									<?php esc_html_e( 'Other pages', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <input placeholder="<?php esc_html_e( 'eg: !is_page(array(34,98,73))', 'woocommerce-multi-currency' ) ?>"
                                       type="text"
                                       value="<?php echo htmlentities( self::get_field( 'conditional_tags' ) ) ?>"
                                       name="<?php echo self::set_field( 'conditional_tags' ) ?>"/>

                                <p class="description"><?php esc_html_e( 'Let you adjust which pages will appear using WP\'s conditional tags.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h3><?php esc_html_e( 'Widget', 'woocommerce-multi-currency' ) ?></h3>
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Flag Custom', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <textarea placeholder="Example:&#x0a;EUR,ES&#x0a;USD,VI"
                                          name="<?php echo self::set_field( 'flag_custom' ) ?>"><?php echo self::get_field( 'flag_custom', '' ) ?></textarea>
                                <p class="description"><?php esc_html_e( 'Some countries use the same currency. You can choice flag correctly. Each line is the flag. Structure [currency_code,country_code]. Example: EUR,ES', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h3><?php esc_html_e( 'Custom', 'woocommerce-multi-currency' ) ?></h3>
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'CSS', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <textarea placeholder=".woocommerce-multi-currency{}"
                                          name="<?php echo self::set_field( 'custom_css' ) ?>"><?php echo self::get_field( 'custom_css', '' ) ?></textarea>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Checkout !-->
                <div class="vi-ui bottom attached tab segment" data-tab="checkout">
                    <!-- Tab Content !-->
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_multi_payment' ) ?>">
									<?php esc_html_e( 'Enable', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_multi_payment' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_multi_payment' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_multi_payment' ) ?>"/>
                                </div>
                                <p class="description"><?php esc_html_e( 'Pay in many currencies', 'woo-multi-currency' ) ?></p>

                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_cart_page' ) ?>">
									<?php esc_html_e( 'Enable Cart Page', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_cart_page' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_cart_page' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_cart_page' ) ?>"/>
                                </div>
                                <p class="description">
									<?php esc_html_e( 'Change the currency in cart page to a check out currency.', 'woocommerce-multi-currency' ) ?>
                                </p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'checkout_currency' ) ?>">
									<?php esc_html_e( 'Checkout currency', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>

                            <td>
                                <table class="vi-ui table">
                                    <thead>
                                    <tr>
                                        <th class="two wide"><?php esc_html_e( 'Currency', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="two wide"><?php esc_html_e( 'Default', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="four wide"><?php esc_html_e( 'Checkout Currency', 'woocommerce-multi-currency' ) ?></th>
                                        <th class="ten wide"><?php esc_html_e( 'Payment methods', 'woocommerce-multi-currency' ) ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php

									$currencies             = self::get_field( 'currency', array( get_option( 'woocommerce_currency' ) ) );
									$checkout_currency      = self::get_field( 'checkout_currency', get_option( 'woocommerce_currency' ) );
									$checkout_currency_args = self::get_field( 'checkout_currency_args', array( get_option( 'woocommerce_currency' ) ) );

									if ( is_array( $currencies ) ) {
										if ( count( array_filter( $currencies ) ) < 1 ) {
											$currencies = array();
										}
									} else {
										$currencies = array();
									}
									/*Convert*/
									if ( count( $currencies ) ) {
										//										$payment_medthods = WC()->payment_gateways()->get_available_payment_gateways();
										$payment_medthods = WC()->payment_gateways->payment_gateways();
										foreach ( $currencies as $key => $currency ) {
											if ( in_array( $currency, $checkout_currency_args ) || ! $checkout_currency ) {
												$selected_checkout_currency = 1;
											} else {
												$selected_checkout_currency = 0;
											}

											if ( self::get_field( 'checkout_currency' ) == $currency ) {
												$disabled_currency = 1;
											} else {
												$disabled_currency = 0;
											}

											?>
                                            <tr valign="top">
                                                <td class="collapsing">
													<?php echo esc_html( $currency ) ?>
                                                </td>
                                                <td class="collapsing">
                                                    <div class="vi-ui toggle checkbox">
                                                        <input id="<?php echo self::set_field( 'checkout_currency' ) ?>"
                                                               type="radio" <?php checked( $checkout_currency, $currency ) ?>
                                                               tabindex="0" class="hidden"
                                                               value="<?php echo esc_attr( $currency ) ?>"
                                                               name="<?php echo self::set_field( 'checkout_currency' ) ?>"/>
                                                    </div>
                                                </td>
                                                <td class="collapsing">
                                                    <select name="<?php echo self::set_field( 'checkout_currency_args', 1 ) ?>" <?php echo $disabled_currency ? 'disabled="disabled"' : '' ?>>
                                                        <option value="0" <?php selected( $selected_checkout_currency, 0 ) ?>><?php esc_html_e( 'No', 'woocommerce-multi-currency' ) ?></option>
                                                        <option value="<?php echo esc_attr( $currency ) ?>" <?php selected( $selected_checkout_currency, 1 ) ?>><?php esc_html_e( 'Yes', 'woocommerce-multi-currency' ) ?></option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="<?php echo self::set_field( 'currency_payment_method_' . $currency, 1 ) ?>"
                                                            class="vi-ui select2" multiple="multiple">
														<?php
														if ( $payment_medthods ) {
															$payments = self::get_field( 'currency_payment_method_' . $currency, array() );
															foreach ( $payment_medthods as $k => $payment_medthod ) {

																if ( $payment_medthod->enabled == 'yes' ) {
																	$title_show = ! empty( $payment_medthod->method_title ) ? $payment_medthod->method_title : $payment_medthod->title;
																	?>
                                                                    <option <?php if ( in_array( $k, $payments ) ) {
																		echo 'selected';
																	} ?> value="<?php echo esc_attr( $k ) ?>"><?php echo esc_html( $title_show ) ?></option>
																<?php }
															}
														}
														?>
                                                    </select>
                                                </td>
                                            </tr>
										<?php }
									} ?>

                                    </tbody>
                                </table>

                                <p class="vi-ui message yellow"><?php esc_html_e( 'Payment method depend on Payment Gateway. If Payment Gateway is not support currency, customer can not checkout with currency. Example: Paypal is not support IDR, Customer can not checkout IDR by Paypal.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Update !-->
                <div class="vi-ui bottom attached tab segment" data-tab="update">
                    <!-- Tab Content !-->
                    <table class="optiontable form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Auto Update Exchange Rate', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>

                                <select name="<?php echo self::set_field( 'update_exchange_rate' ) ?>">
                                    <option <?php selected( self::get_field( 'update_exchange_rate', 0 ), '0' ) ?>
                                            value="0"><?php esc_html_e( 'No', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '1' ) ?>
                                            value="1"><?php esc_html_e( '30 Minutes ', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '2' ) ?>
                                            value="2"><?php esc_html_e( '1 Hour ', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '3' ) ?>
                                            value="3"><?php esc_html_e( '6 Hours', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '4' ) ?>
                                            value="4"><?php esc_html_e( '1 Day', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '7' ) ?>
                                            value="7"><?php esc_html_e( '2 Days', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '8' ) ?>
                                            value="8"><?php esc_html_e( '3 Days', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '9' ) ?>
                                            value="9"><?php esc_html_e( '4 Days', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '10' ) ?>
                                            value="10"><?php esc_html_e( '5 Days', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '11' ) ?>
                                            value="11"><?php esc_html_e( '6 Days', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '5' ) ?>
                                            value="5"><?php esc_html_e( '1 Week', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( self::get_field( 'update_exchange_rate' ), '6' ) ?>
                                            value="6"><?php esc_html_e( '1 Month', 'woocommerce-multi-currency' ) ?></option>
                                </select>

                                <p class="description"><?php echo esc_html__( 'Exchange will be updated automatically.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Finance API', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
								<?php
								$selected_api = apply_filters( 'wmc_get_finance_api', self::get_field( 'finance_api' ) );
								?>
                                <select name="<?php echo self::set_field( 'finance_api' ) ?>">
                                    <option <?php selected( $selected_api, '0' ) ?>
                                            value="0"><?php esc_html_e( 'Default', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( $selected_api, '1' ) ?>
                                            value="1"><?php esc_html_e( 'Google Finance', 'woocommerce-multi-currency' ) ?></option>
                                    <option <?php selected( $selected_api, '2' ) ?>
                                            value="2"><?php esc_html_e( 'Yahoo Finance', 'woocommerce-multi-currency' ) ?></option>
									<?php do_action( 'wmc_finance_api_options', $selected_api ) ?>
                                </select>

                                <p class="description"><?php echo esc_html__( 'Exchange rate resources.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Rate Decimals', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <input type="text" name="<?php echo self::set_field( 'rate_decimals' ) ?>"
                                       value="<?php echo self::get_field( 'rate_decimals', 3 ) ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="<?php echo self::set_field( 'enable_send_email' ) ?>">
									<?php esc_html_e( 'Email', 'woocommerce-multi-currency' ) ?>
                                </label>
                            </th>
                            <td>
                                <div class="vi-ui toggle checkbox">
                                    <input id="<?php echo self::set_field( 'enable_send_email' ) ?>"
                                           type="checkbox" <?php checked( self::get_field( 'enable_send_email' ), 1 ) ?>
                                           tabindex="0" class="hidden" value="1"
                                           name="<?php echo self::set_field( 'enable_send_email' ) ?>"/>
                                    <label></label>
                                </div>
                                <p class="description"><?php esc_html_e( 'Send email to admin when exchange rate is updated.', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php esc_html_e( 'Email Custom', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <input type="email" name="<?php echo self::set_field( 'email_custom' ) ?>"
                                       value="<?php echo self::get_field( 'email_custom' ) ?>"/>

                                <p class="description"><?php echo esc_html__( 'If empty, notification will sent to ', 'woocommerce-multi-currency' ) . get_option( 'admin_email' ) ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-multi-currency' ) ?></label>
                            </th>
                            <td>
                                <div class="fields">
                                    <div class="ten wide field">
                                        <input type="text" name="<?php echo self::set_field( 'key' ) ?>"
                                               id="auto-update-key"
                                               class="villatheme-autoupdate-key-field"
                                               value="<?php echo self::get_field( 'key' ) ?>">
                                    </div>
                                    <div class="six wide field">
                                        <span class="vi-ui button green villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="20948446"><?php echo esc_html__( 'Get Key', 'woocommerce-multi-currency' ) ?></span>
                                    </div>
                                </div>

								<?php do_action( 'woocommerce-multi-currency_key' ) ?>
                                <p class="description"><?php echo __( 'Please fill your key what you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a>. You can automatically update WooCommerce Multi Currency plugin. See <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>', 'woocommerce-multi-currency' ) ?></p>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
                <p>
                    <button class="vi-ui button labeled icon primary wmc-submit">
                        <i class="send icon"></i> <?php esc_html_e( 'Save', 'woocommerce-multi-currency' ) ?>
                    </button>
                    <button class="vi-ui button labeled icon wmc-submit"
                            name="<?php echo self::set_field( 'check_key' ) ?>">
                        <i class="send icon"></i> <?php esc_html_e( 'Save & Check Key', 'woocommerce-multi-currency' ) ?>
                    </button>
                </p>
            </form>
        </div>
		<?php
		do_action( 'villatheme_support_woocommerce-multi-currency' );
	}

	/**
	 * Set Nonce
	 * @return string
	 */
	protected static function set_nonce() {
		return wp_nonce_field( 'woo_multi_currency_settings', '_woo_multi_currency_nonce' );
	}

	/**
	 * Set field in meta box
	 *
	 * @param      $field
	 * @param bool $multi
	 *
	 * @return string
	 */
	protected static function set_field( $field, $multi = false ) {
		if ( $field ) {
			if ( $multi ) {
				return 'woo_multi_currency_params[' . $field . '][]';
			} else {
				return 'woo_multi_currency_params[' . $field . ']';
			}
		} else {
			return '';
		}
	}

	/**
	 * Get Post Meta
	 *
	 * @param $field
	 *
	 * @return bool
	 */
	public static function get_field( $field, $default = '' ) {
		global $wmc_settings;
		$params = $wmc_settings;

		if ( self::$params ) {
			$params = self::$params;
		} else {
			self::$params = $params;
		}
		if ( isset( $params[ $field ] ) && $field ) {
			return $params[ $field ];
		} else {
			return $default;
		}
	}

	/**
	 * Check element in array
	 *
	 * @param $arg
	 * @param $index
	 *
	 * @return bool
	 */
	static protected function data_isset( $arg, $index, $default = false ) {
		if ( isset( $arg[ $index ] ) ) {
			return $arg[ $index ];
		} else {
			return $default;
		}
	}

	public function woomulticurrency_exchange() {
		$orginal_price    = filter_input( INPUT_POST, 'original_price', FILTER_SANITIZE_STRING );
		$other_currencies = filter_input( INPUT_POST, 'other_currencies', FILTER_SANITIZE_STRING );
//		$data             = new WOOMULTI_CURRENCY_Data();
		$data             = WOOMULTI_CURRENCY_Data::get_ins();
		$rates            = $data->get_exchange( $orginal_price, $other_currencies );
		wp_send_json( $rates );
		die;
	}

	/**
	 * Save post meta
	 *
	 * @param $post
	 *
	 * @return bool
	 */
	public function save_meta_boxes() {
		if ( ! isset( $_POST['_woo_multi_currency_nonce'] ) || ! isset( $_POST['woo_multi_currency_params'] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( $_POST['_woo_multi_currency_nonce'], 'woo_multi_currency_settings' ) ) {
			return false;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$data                     = $_POST['woo_multi_currency_params'];
		$data['conditional_tags'] = $this->stripslashes_deep( $data['conditional_tags'] );
		$data['custom_css']       = $this->stripslashes_deep( $data['custom_css'] );

		/*Override WooCommerce Currency*/
		if ( isset( $data['currency_default'] ) && $data['currency_default'] && isset( $data['currency'] ) ) {
			update_option( 'woocommerce_currency', $data['currency_default'] );
			$index = array_search( $data['currency_default'], $data['currency'] );
			/*Override WooCommerce Currency*/
			if ( isset( $data['currency_pos'][ $index ] ) && $index && $data['currency_pos'][ $index ] ) {
				update_option( 'woocommerce_currency_pos', $data['currency_pos'][ $index ] );
			}
			if ( isset( $data['currency_decimals'][ $index ] ) ) {
				update_option( 'woocommerce_price_num_decimals', $data['currency_decimals'][ $index ] );
			}
		}
		if ( isset( $data['enable_wpml'] ) && $data['enable_wpml'] ) {
			$wpml_settings                                                                               = get_option( 'icl_sitepress_settings' );
			$wpml_settings['translation-management']['custom_fields_translation']['wmc_order_info']      = 1;
			$wpml_settings['translation-management']['custom_fields_translation']['_regular_price_wmcp'] = 1;
			$wpml_settings['translation-management']['custom_fields_translation']['_sale_price_wmcp']    = 1;
			update_option( 'icl_sitepress_settings', $wpml_settings );
		}

		if ( isset( $data['checkout_currency'] ) && $data['checkout_currency'] ) {
			if ( ! in_array( $data['checkout_currency'], $data['checkout_currency_args'] ) ) {
				$data['checkout_currency_args'][] = $data['checkout_currency'];
			}
		}
		if ( isset( $data['check_key'] ) ) {
			unset( $data['check_key'] );
			delete_transient( '_site_transient_update_plugins' );
			delete_option( 'woocommerce-multi-currency_messages' );
		}
		update_option( 'woo_multi_currency_params', $data );
		delete_transient( 'wmc_update_exchange_rate' );
	}

	private function stripslashes_deep( $value ) {
		$value = is_array( $value ) ? array_map( 'stripslashes_deep', $value ) : stripslashes( $value );

		return $value;
	}
} ?>