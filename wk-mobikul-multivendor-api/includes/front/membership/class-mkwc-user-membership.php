<?php
/**
 * Get user membership details.
 */
namespace WkMobikulMultivendorApi\Includes\Front\membership;

if ( ! class_exists( 'Mkwc_User_Membership' ) ) {

	/**
	 * member ship class
	 */
	class Mkwc_User_Membership {

		/**
		 * Get membership list
		 *
		 * @param array $request request param.
		 * @return void
		 */
		public function wkmp_get_membership_list( $request ) {

			global $wpdb, $pmpro_msg, $pmpro_msgt, $current_user;
			$pmpro_levels       = pmpro_sort_levels_by_order( pmpro_getAllLevels( false, true ) );
			$pmpro_levels       = apply_filters( 'pmpro_levels_array', $pmpro_levels );
			$user_id            = $request->get_param( 'user_id' );
			$current_user_level = 0;
			if ( ! empty( $user_id ) ) {
				$levels       = pmpro_getMembershipLevelsForUser( $user_id );
				$select_level = ! empty( $levels[0]->ID ) ? $levels[0]->ID : '';
				$user_meta    = get_userdata( $user_id );
				$user_roles   = $user_meta->roles; // array of roles the user is part of.
				$user_role    = "'" . implode( "','", $user_roles ) . "'";
				if ( in_array( 'subscriber', $user_roles ) ) {
					$assigned_role = $wpdb->get_results( 'SELECT plan_id FROM ' . $wpdb->prefix . "assigned_plans WHERE role IN ('wk_marketplace_seller')" );
				} else {
					$assigned_role = $wpdb->get_results( 'SELECT plan_id FROM ' . $wpdb->prefix . 'assigned_plans WHERE role IN (' . $user_role . ')' );
				}
				$assigned_roles_arr = array();
				foreach ( $assigned_role as $data ) {
					$assigned_roles_arr[] = $data->plan_id;
				}

				$newpmpro_levels = array();
				foreach ( $pmpro_levels as  $data ) {
					if ( in_array( $data->id, $assigned_roles_arr ) ) {
						$newpmpro_levels[] = $data;
					}
				}

				$count            = 0;
				$has_any_level    = false;
				$member_ship_data = array();
				foreach ( $newpmpro_levels as $level ) {

					$user_level = pmpro_getSpecificMembershipLevelForUser( $user_id, $level->id );
					if ( $select_level == $level->id ) {
						$status = true;
					} else {
						$status = false;
					}
					$has_level     = ! empty( $user_level );
					$has_any_level = $has_level ?: $has_any_level;
					$label_name    = $has_level ? $level->name : $level->name;
					$cost_text     = $this->wkmp_pmpro_getLevelCost( $level, true, true );

					$expiration_text = pmpro_getLevelExpiration( $level );

					if ( ! empty( $cost_text ) && ! empty( $expiration_text ) ) {
						$cost_text = $cost_text . ' ' . $expiration_text;
					} elseif ( ! empty( $cost_text ) ) {
						$cost_text = $cost_text;
					} elseif ( ! empty( $expiration_text ) ) {
						$cost_text = $expiration_text;
					}

					$member_ship_data[] = array(
						'label'         => $label_name,
						'id'            => $level->id,
						'cost'          => $cost_text,
						'select_status' => $status,
					);

				}

				if ( ! empty( $member_ship_data ) ) {
					$result = array(
						'status'            => true,
						'membership_status' => $this->wkmp_check_membership_status( $user_id ),
						'data'              => $member_ship_data,
					);
				} else {
					$result = array(
						'status'  => true,
						'message' => 'Record not found',
					);
				}
			} else {
				$result = array(
					'status'  => false,
					'message' => 'All fields required',
				);
			}
			return $result;
		}

		public function mkwc_get_current_membership_id( $user_id ) {
			wp_set_current_user( $user_id );
			$current_user   = wp_get_current_user();
			$current_lavel = $current_user->membership_level->id;
			$current_lavel = ! empty( $current_lavel ) ? $current_lavel : 0;
			return $current_lavel;
		}


		public function wkmp_check_membership_status( $user_id ) {
			global $wpdb;
			$status         = false;
			$user_meta      = get_userdata( $user_id );
			$table_name_one = $wpdb->prefix . 'mpsellerinfo';
			$current_member_level 	= 	$this->mkwc_get_current_membership_id( $user_id );
			$qry1                 = "SELECT user_id,architect_value FROM architect_approve WHERE user_id=$user_id";
			$rs1                  = $wpdb->get_row( $qry1 );

			$qry2       = "SELECT user_id,sp_value FROM service_provider_approval WHERE user_id=$user_id";
			$rs2        = $wpdb->get_row( $qry2 );
			$qry3       = "SELECT user_id,seller_value FROM $table_name_one WHERE user_id=$user_id";
			$rs3        = $wpdb->get_row( $qry3 );
			$user_roles = $user_meta->roles;
			if ( in_array( 'architect', $user_roles ) && $current_member_level != '' && 'Approve' === $rs1->architect_value ) {
				$status = true;
			} elseif ( in_array( 'serviceproviders', $user_roles ) && $current_member_level != '' && 'Approve' === $rs2->sp_value ) {
				$status = true;
			} elseif ( in_array( 'wk_marketplace_seller', $user_roles ) && $current_member_level != '' && 'seller' === $rs3->seller_value ) {
				$status = true;
			} elseif ( in_array( 'customer', $user_roles ) && $current_member_level != '' ) {
				$status = true;
			} elseif ( in_array( 'administrator', $user_roles ) ) {
				$status = true;
			} else {
				$status = false;
			}

			return $status;
		}

		/**
		 * Undocumented function
		 *
		 * @param [type]  $level
		 * @param boolean $tags
		 * @param boolean $short
		 * @return void
		 */
		public function wkmp_pmpro_getLevelCost( &$level, $tags = true, $short = false ) {
			// initial payment
			if ( ! $short ) {
				$r = sprintf( __( 'The price for membership is %s now', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->initial_payment ) );
			} else {
				if ( pmpro_isLevelFree( $level ) ) {
					$r = '' . __( 'Free', 'paid-memberships-pro' ) . '';
				} else {
					$r = sprintf( __( '%s now', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->initial_payment ) );
				}
			}

			// recurring part
			if ( (float) $level->billing_amount > 0 ) {
				if ( $level->billing_limit > 1 ) {
					if ( $level->cycle_number == '1' ) {
						$r .= sprintf( __( ' and then %1$s per %2$s for %3$d more %4$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->billing_amount ), pmpro_translate_billing_period( $level->cycle_period ), $level->billing_limit, pmpro_translate_billing_period( $level->cycle_period, $level->billing_limit ) );
					} else {
						$r .= sprintf( __( ' and then %1$s every %2$d %3$s for %4$d more payments.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->billing_amount ), $level->cycle_number, pmpro_translate_billing_period( $level->cycle_period, $level->cycle_number ), $level->billing_limit );
					}
				} elseif ( $level->billing_limit == 1 ) {
					$r .= sprintf( __( ' and then %1$s after %2$d %3$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->billing_amount ), $level->cycle_number, pmpro_translate_billing_period( $level->cycle_period, $level->cycle_number ) );
				} else {
					if ( $level->billing_amount === $level->initial_payment ) {
						if ( $level->cycle_number == '1' ) {
							if ( ! $short ) {
								$r = sprintf( __( 'The price for membership is %1$s per %2$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->initial_payment ), pmpro_translate_billing_period( $level->cycle_period ) );
							} else {
								$r = sprintf( __( '%1$s per %2$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->initial_payment ), pmpro_translate_billing_period( $level->cycle_period ) );
							}
						} else {
							if ( ! $short ) {
								$r = sprintf( __( 'The price for membership is %1$s every %2$d %3$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->initial_payment ), $level->cycle_number, pmpro_translate_billing_period( $level->cycle_period, $level->cycle_number ) );
							} else {
								$r = sprintf( __( '%1$s every %2$d %3$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->initial_payment ), $level->cycle_number, pmpro_translate_billing_period( $level->cycle_period, $level->cycle_number ) );
							}
						}
					} else {
						if ( $level->cycle_number == '1' ) {
							$r .= sprintf( __( ' and then %1$s per %2$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->billing_amount ), pmpro_translate_billing_period( $level->cycle_period ) );
						} else {
							$r .= sprintf( __( ' and then %1$s every %2$d %3$s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->billing_amount ), $level->cycle_number, pmpro_translate_billing_period( $level->cycle_period, $level->cycle_number ) );
						}
					}
				}
			} else {
				$r .= '.';
			}

			// add a space
			$r .= ' ';

			// trial part
			if ( $level->trial_limit ) {
				if ( (float) $level->trial_amount > 0 ) {
					if ( $level->trial_limit == '1' ) {
						$r .= ' ' . __( 'After your initial payment, your first payment is Free.', 'paid-memberships-pro' );
					} else {
						$r .= ' ' . sprintf( __( 'After your initial payment, your first %d payments are Free.', 'paid-memberships-pro' ), $level->trial_limit );
					}
				} else {
					if ( $level->trial_limit == '1' ) {
						$r .= ' ' . sprintf( __( 'After your initial payment, your first payment will cost %s.', 'paid-memberships-pro' ), $this->wkmp_pmpro_formatPrice( $level->trial_amount ) );
					} else {
						$r .= ' ' . sprintf( __( 'After your initial payment, your first %1$d payments will cost %2$s.', 'paid-memberships-pro' ), $level->trial_limit, $this->wkmp_pmpro_formatPrice( $level->trial_amount ) );
					}
				}
			}

			// taxes part
			$tax_state = pmpro_getOption( 'tax_state' );
			$tax_rate  = pmpro_getOption( 'tax_rate' );

			if ( $tax_state && $tax_rate && ! pmpro_isLevelFree( $level ) ) {
				$r .= sprintf( __( 'Customers in %1$s will be charged %2$s%% tax.', 'paid-memberships-pro' ), $tax_state, round( $tax_rate * 100, 2 ) );
			}

			if ( ! $tags ) {
				$r = strip_tags( $r );
			}

			$r = apply_filters( 'pmpro_level_cost_text', $r, $level, $tags, $short ); // passing $tags and $short since v1.8
			return $r;
		}

		/**
		 * Undocumented function
		 *
		 * @param [type] $price
		 * @return void
		 */
		public function wkmp_pmpro_formatPrice( $price ) {
			global $pmpro_currency, $pmpro_currency_symbol, $pmpro_currencies;
			$pmpro_currency_symbol = $this->wkmp_get_woocommerce_currency_symbol();
			// start with the rounded price
			$formatted = pmpro_round_price( $price );

			$decimals            = isset( $pmpro_currencies[ $pmpro_currency ]['decimals'] ) ? (int) $pmpro_currencies[ $pmpro_currency ]['decimals'] : pmpro_get_decimal_place();
			$decimal_separator   = isset( $pmpro_currencies[ $pmpro_currency ]['decimal_separator'] ) ? $pmpro_currencies[ $pmpro_currency ]['decimal_separator'] : '.';
			$thousands_separator = isset( $pmpro_currencies[ $pmpro_currency ]['thousands_separator'] ) ? $pmpro_currencies[ $pmpro_currency ]['thousands_separator'] : ',';
			$symbol_position     = isset( $pmpro_currencies[ $pmpro_currency ]['position'] ) ? $pmpro_currencies[ $pmpro_currency ]['position'] : 'left';

			// settings stored in array?
			if ( ! empty( $pmpro_currencies[ $pmpro_currency ] ) && is_array( $pmpro_currencies[ $pmpro_currency ] ) ) {
				// format number do decimals, with decimal_separator and thousands_separator
				$formatted = number_format(
					$formatted,
					$decimals,
					$decimal_separator,
					$thousands_separator
				);

				// which side is the symbol on?
				if ( ! empty( $symbol_position ) && $symbol_position == 'left' ) {
					$formatted = $pmpro_currency_symbol . $formatted;
				} else {
					$formatted = $formatted . $pmpro_currency_symbol;
				}
			} else {
				// default to symbol on the left, 2 decimals using . and ,
				$formatted = $pmpro_currency_symbol . number_format( $formatted, pmpro_get_decimal_place() );
			}

			// Trim the trailing zero values.
			$formatted = pmpro_trim_trailing_zeroes( $formatted, $decimals, $decimal_separator, $pmpro_currency_symbol, $symbol_position );

			// filter
			return apply_filters( 'pmpro_format_price', $formatted, $price, $pmpro_currency, $pmpro_currency_symbol );
		}

		public function wkmp_get_woocommerce_currency_symbol( $currency = '' ) {
			if ( ! $currency ) {
				$currency = get_woocommerce_currency();
			}

			$symbols = apply_filters(
				'wkmp_currency_symbols',
				array(
					'AED' => 'د.إ',
					'AFN' => '؋',
					'ALL' => 'L',
					'AMD' => 'AMD',
					'ANG' => 'ƒ',
					'AOA' => 'Kz',
					'ARS' => '$',
					'AUD' => '$',
					'AWG' => 'ƒ',
					'AZN' => 'AZN',
					'BAM' => 'KM',
					'BBD' => '$',
					'BDT' => '৳ ',
					'BGN' => 'лв.',
					'BHD' => '.د.ب',
					'BIF' => 'Fr',
					'BMD' => '$',
					'BND' => '$',
					'BOB' => 'Bs.',
					'BRL' => 'R$',
					'BSD' => '$',
					'BTC' => '฿',
					'BTN' => 'Nu.',
					'BWP' => 'P',
					'BYR' => 'Br',
					'BZD' => '$',
					'CAD' => '$',
					'CDF' => 'Fr',
					'CHF' => 'CHF',
					'CLP' => '$',
					'CNY' => '¥',
					'COP' => '$',
					'CRC' => '₡',
					'CUC' => '$',
					'CUP' => '$',
					'CVE' => '$',
					'CZK' => 'Kč',
					'DJF' => 'Fr',
					'DKK' => 'DKK',
					'DOP' => 'RD$',
					'DZD' => 'د.ج',
					'EGP' => 'EGP',
					'ERN' => 'Nfk',
					'ETB' => 'Br',
					'EUR' => '€',
					'FJD' => '$',
					'FKP' => '£',
					'GBP' => '£',
					'GEL' => 'ლ',
					'GGP' => '£',
					'GHS' => '₵',
					'GIP' => '£',
					'GMD' => 'D',
					'GNF' => 'Fr',
					'GTQ' => 'Q',
					'GYD' => '$',
					'HKD' => '$',
					'HNL' => 'L',
					'HRK' => 'Kn',
					'HTG' => 'G',
					'HUF' => 'Ft',
					'IDR' => 'Rp',
					'ILS' => '₪',
					'IMP' => '£',
					'INR' => '₹',
					'IQD' => 'ع.د',
					'IRR' => '﷼',
					'IRT' => 'تومان',
					'ISK' => 'kr.',
					'JEP' => '£',
					'JMD' => '$',
					'JOD' => 'د.ا',
					'JPY' => '¥',
					'KES' => 'KSh',
					'KGS' => 'сом',
					'KHR' => '៛',
					'KMF' => 'Fr',
					'KPW' => '₩',
					'KRW' => '₩',
					'KWD' => 'د.ك',
					'KYD' => '$',
					'KZT' => 'KZT',
					'LAK' => '₭',
					'LBP' => 'ل.ل',
					'LKR' => 'රු',
					'LRD' => '$',
					'LSL' => 'L',
					'LYD' => 'ل.د',
					'MAD' => 'د.م.',
					'MDL' => 'MDL',
					'MGA' => 'Ar',
					'MKD' => 'ден',
					'MMK' => 'Ks',
					'MNT' => '₮',
					'MOP' => 'P',
					'MRO' => 'UM',
					'MUR' => '₨',
					'MVR' => '.ރ',
					'MWK' => 'MK',
					'MXN' => '$',
					'MYR' => 'RM',
					'MZN' => 'MT',
					'NAD' => '$',
					'NGN' => '₦',
					'NIO' => 'C$',
					'NOK' => 'kr',
					'NPR' => '₨',
					'NZD' => '$',
					'OMR' => 'ر.ع.',
					'PAB' => 'B/.',
					'PEN' => 'S/.',
					'PGK' => 'K',
					'PHP' => '₱',
					'PKR' => '₨',
					'PLN' => 'zł',
					'PRB' => 'р.',
					'PYG' => '₲',
					'QAR' => 'ر.ق',
					'RMB' => '¥',
					'RON' => 'lei',
					'RSD' => 'дин.',
					'RUB' => '₽',
					'RWF' => 'Fr',
					'SAR' => 'ر.س',
					'SBD' => '$',
					'SCR' => '₨',
					'SDG' => 'ج.س.',
					'SEK' => 'kr',
					'SGD' => '$',
					'SHP' => '£',
					'SLL' => 'Le',
					'SOS' => 'Sh',
					'SRD' => '$',
					'SSP' => '£',
					'STD' => 'Db',
					'SYP' => 'ل.س',
					'SZL' => 'L',
					'THB' => '฿',
					'TJS' => 'ЅМ',
					'TMT' => 'm',
					'TND' => 'د.ت',
					'TOP' => 'T$',
					'TRY' => '₺',
					'TTD' => '$',
					'TWD' => 'NT$',
					'TZS' => 'Sh',
					'UAH' => '₴',
					'UGX' => 'UGX',
					'USD' => '$',
					'UYU' => '$',
					'UZS' => 'UZS',
					'VEF' => 'Bs F',
					'VND' => '₫',
					'VUV' => 'Vt',
					'WST' => 'T',
					'XAF' => 'Fr',
					'XCD' => '$',
					'XOF' => 'Fr',
					'XPF' => 'Fr',
					'YER' => '﷼',
					'ZAR' => 'R',
					'ZMW' => 'ZK',
				)
			);

			$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';
			return $currency_symbol;

		}

		/**
		 * Get level details.
		 *
		 * @param object $param Request param.
		 * @return void
		 */
		public function wkmp_get_level_details( $param ) {
			$level        = $param->get_param( 'level' );
			$result       = array();
			$pmpro_levels = pmpro_sort_levels_by_order( pmpro_getAllLevels( false, true ) );
			if ( ! empty( $level ) ) {

				if ( ! empty( $pmpro_levels[ $level ] ) ) {
					$pmpro_level = $pmpro_levels[ $level ];
					$result      = array(
						'status' => true,
						'data'   => array( 'select_lavel' => sprintf( __( 'You have selected the <strong>%s</strong> membership level.', 'paid-memberships-pro' ), $pmpro_level->name ) ),
					);
				} else {
					$result = array(
						'status'  => false,
						'message' => 'Please provide correct details',
					);
				}
			} else {
				$result = array(
					'status'  => false,
					'message' => 'All fields required',
				);
			}

			return $result;
		}

		/**
		 * Send mail to admin regarding the new customization.
		 *
		 * @param [type] $parm
		 * @return void
		 */
		public function wkmp_create_custom_design_according_to_user( $parm ) {
			$data   = $parm->get_json_params();
			$result = array();
			if ( empty( $data ) ) {
				$result = array(
					'status'  => false,
					'message' => 'Please provide correct details',
				);
			} elseif ( empty( $data['name'] ) || empty( $data['email'] ) || empty( $data['subject'] ) || empty( $data['volume_number'] ) || empty( $data['folio_number'] ) || empty( $data['about_house'] ) ) {
				$result = array(
					'status'  => false,
					'message' => 'Please provide correct details',
				);
			} else {
				$result = array(
					'status'  => true,
					'message' => 'Thank you for your message. It has been sent.',
				);
			}

			return $result;

		}



		public function wkmp_get_all_services_provider_list( $parm ) {
			global $wpdb;
			$request_data             = $parm->get_params();
			$categories               = array();
			$gallery_items            = array();
			$includeServiceCategories = true;
			$pageno                   = ! empty( $request_data['pageno'] ) ? $request_data['pageno'] : 1;
			$no_of_records_per_page   = 16;

			if ( 'engineers' === $request_data['type'] ) {
				$result = $this->mkwc_get_engineers( $pageno, $no_of_records_per_page );
			} elseif ( 'all-architects' === $request_data['type'] ) {
				$result = $this->mkwc_get_all_architects( $pageno, $no_of_records_per_page );
			} elseif ( 'contractors' === $request_data['type'] ) {
				$result = $this->mkwc_get_all_contractors( $pageno, $no_of_records_per_page );
			} elseif ( 'quantity-surveyors' === $request_data['type'] ) {
				$result = $this->mkwc_get_all_quantity_surveyors( $pageno, $no_of_records_per_page );
			} elseif ( 'interior-designers' === $request_data['type'] ) {
				$result = $this->mkwc_get_all_interior_designers( $pageno, $no_of_records_per_page );
			} elseif ( 'landscape-designers' === $request_data['type'] ) {
				$result = $this->mkwc_get_all_landscape_designers( $pageno, $no_of_records_per_page );
			} elseif ( 'kitchen-suppliers' === $request_data['type'] ) {
				$result = $this->mkwc_get_all_kitchen_suppliers( $pageno, $no_of_records_per_page );
			} elseif ( 'land-surveyors' === $request_data['type'] ) {
				$result = $this->wkmp_get_land_surveyors( $pageno, $no_of_records_per_page );
			} else {
				$result = array(
					'status'  => false,
					'message' => 'please provide all parameters',
				);
			}

			return $result;
		}

		public function wkmp_get_land_surveyors( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$no_of_records_per_page = 16;
			$offset                 = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12             = new \MP_Form_Handler();
			$total_pages_sql        = "SELECT COUNT(*) FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='land_surveyors' AND service_provider_approval.sp_value='Approve'";
			$total_rows             = $wpdb->get_var( $total_pages_sql );
			$total_pages            = ceil( $total_rows / $no_of_records_per_page );
			$sps                    = "SELECT service_provider_approval.user_id FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='land_surveyors' AND service_provider_approval.sp_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_sps                = $wpdb->get_results( $sps );

			foreach ( $all_sps as $sps ) {

				$com_logo          = $wpmp_obj12->get_user_avatar( $sps->user_id, 'avatar' );
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $sps->user_id );
				$user_meta_arr     = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}

				$fullName       = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta_arr ) {
								return isset( $user_meta_arr[ $metaName ] ) ? $user_meta_arr[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);
				$galleryItems[] = array(
					'category'           => '',
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'shop_address'       => isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '',
					'Company'            => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'seller_id'          => isset( $sps->user_id ) ? $sps->user_id : '',
					'url'                => site_url() . '/seller/store/' . $user_meta_arr['shop_address'],
					'show_name_on_photo' => true,
				);
			}

			$result = array(
				'status'       => true,
				'current_page' => $pageno,
				'total_row'    => $total_rows,
				'total_page'   => $total_pages,
				'data'         => $galleryItems,
			);
			return $result;

		}

		public function mkwc_get_all_kitchen_suppliers( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$no_of_records_per_page = 16;
			$offset                 = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12             = new \MP_Form_Handler();
			$total_pages_sql        = "SELECT COUNT(*) FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='kitchen_suppliers' AND service_provider_approval.sp_value='Approve'";
			$total_rows             = $wpdb->get_var( $total_pages_sql );
			$total_pages            = ceil( $total_rows / $no_of_records_per_page );
			$sps                    = "SELECT service_provider_approval.user_id FROM service_provider_approval
			INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='kitchen_suppliers' AND service_provider_approval.sp_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_sps                = $wpdb->get_results( $sps );
			foreach ( $all_sps as $sps ) {

				$com_logo          = $wpmp_obj12->get_user_avatar( $sps->user_id, 'avatar' );
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $sps->user_id );
				$user_meta_arr     = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}

				$fullName       = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta_arr ) {
								return isset( $user_meta_arr[ $metaName ] ) ? $user_meta_arr[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);
				$galleryItems[] = array(
					'category'           => '',
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'shop_address'       => isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '',
					'Company'            => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'seller_id'          => isset( $sps->user_id ) ? $sps->user_id : '',
					'url'                => site_url() . '/seller/store/' . $user_meta_arr['shop_address'],
					'show_name_on_photo' => true,
					'roll'               => 'kitchen_suppliers',
				);
			}

			$result = array(
				'status'       => true,
				'current_page' => $pageno,
				'total_row'    => $total_rows,
				'total_page'   => $total_pages,
				'data'         => $galleryItems,
			);
			return $result;

		}
		public function mkwc_get_all_landscape_designers( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$no_of_records_per_page = 16;
			$offset                 = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12             = new \MP_Form_Handler();
			$total_pages_sql        = "SELECT COUNT(*) FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='landscape_designers' AND service_provider_approval.sp_value='Approve'";
			$total_rows             = $wpdb->get_var( $total_pages_sql );
			$total_pages            = ceil( $total_rows / $no_of_records_per_page );
			$sps                    = "SELECT service_provider_approval.user_id FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='landscape_designers' AND service_provider_approval.sp_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_sps                = $wpdb->get_results( $sps );

			foreach ( $all_sps as $sps ) {
				$com_logo          = $wpmp_obj12->get_user_avatar( $sps->user_id, 'avatar' );
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $sps->user_id );
				$user_meta_arr     = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}

				$fullName       = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta_arr ) {
								return isset( $user_meta_arr[ $metaName ] ) ? $user_meta_arr[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);
				$galleryItems[] = array(
					'category'           => '',
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'shop_address'       => isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '',
					'Company'            => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'seller_id'          => isset( $sps->user_id ) ? $sps->user_id : '',
					'url'                => site_url() . '/seller/store/' . $user_meta_arr['shop_address'],
					'show_name_on_photo' => true,
					'roll'               => '',
				);
			}

			$result = array(
				'status'       => true,
				'current_page' => $pageno,
				'total_row'    => $total_rows,
				'total_page'   => $total_pages,
				'data'         => $galleryItems,
			);
			return $result;

		}

		public function mkwc_get_all_interior_designers( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$no_of_records_per_page = 16;
			$offset                 = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12             = new \MP_Form_Handler();
			$total_pages_sql        = "SELECT COUNT(*) FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='interior_designers' AND service_provider_approval.sp_value='Approve'";
			$total_rows             = $wpdb->get_var( $total_pages_sql );
			$total_pages            = ceil( $total_rows / $no_of_records_per_page );
			$sps                    = "SELECT service_provider_approval.user_id FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='interior_designers' AND service_provider_approval.sp_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_sps                = $wpdb->get_results( $sps );

			foreach ( $all_sps as $sps ) {

				$com_logo          = $wpmp_obj12->get_user_avatar( $sps->user_id, 'avatar' );
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $sps->user_id );
				$user_meta_arr     = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}

				$fullName       = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta_arr ) {
								return isset( $user_meta_arr[ $metaName ] ) ? $user_meta_arr[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);
				$galleryItems[] = array(
					'category'           => '',
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'shop_address'       => isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '',
					'Company'            => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'seller_id'          => isset( $sps->user_id ) ? $sps->user_id : '',
					'url'                => site_url() . '/seller/store/' . $user_meta_arr['shop_address'],
					'show_name_on_photo' => true,
					'roll'               => 'interior_designers',
				);
			}

			$result = array(
				'status'       => true,
				'current_page' => $pageno,
				'total_row'    => $total_rows,
				'total_page'   => $total_pages,
				'data'         => $galleryItems,
			);
			return $result;

		}

		public function mkwc_get_all_quantity_surveyors( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$no_of_records_per_page = 16;
			$offset                 = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12             = new \MP_Form_Handler();
			$total_pages_sql        = "SELECT COUNT(*) FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='quantity_surveyors' AND service_provider_approval.sp_value='Approve'";
			$total_rows             = $wpdb->get_var( $total_pages_sql );
			$total_pages            = ceil( $total_rows / $no_of_records_per_page );

			$sps     = "SELECT service_provider_approval.user_id FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='quantity_surveyors' AND service_provider_approval.sp_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_sps = $wpdb->get_results( $sps );
			foreach ( $all_sps as $sps ) {

				$com_logo          = $wpmp_obj12->get_user_avatar( $sps->user_id, 'avatar' );
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $sps->user_id );
				$user_meta_arr     = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					 $user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}
				$fullName       = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta_arr ) {
								return isset( $user_meta_arr[ $metaName ] ) ? $user_meta_arr[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);
				$galleryItems[] = array(
					'category'           => '',
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'shop_address'       => isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '',
					'Company'            => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'seller_id'          => isset( $sps->user_id ) ? $sps->user_id : '',
					'url'                => site_url() . '/seller/store/' . $user_meta_arr['shop_address'],
					'show_name_on_photo' => true,
					'roll'               => 'quantity_surveyors',
				);
			}

			$result = array(
				'status'       => true,
				'current_page' => $pageno,
				'total_row'    => $total_rows,
				'total_page'   => $total_pages,
				'data'         => $galleryItems,
			);
			return $result;

		}

		public function mkwc_get_all_contractors( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$no_of_records_per_page = 16;
			$offset                 = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12             = new \MP_Form_Handler();
			$gallery_items          = array();

			$total_pages_sql = " SELECT COUNT(*) FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='contractors' AND service_provider_approval.sp_value='Approve'";
			$total_rows      = $wpdb->get_var( $total_pages_sql );
			$total_pages     = ceil( $total_rows / $no_of_records_per_page );

			$sps     = "SELECT service_provider_approval.user_id FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='contractors' AND service_provider_approval.sp_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_sps = $wpdb->get_results( $sps );

			foreach ( $all_sps as $sps ) {

				$com_logo     = $wpmp_obj12->get_user_avatar( $sps->user_id, 'avatar' );
				$businessLogo = get_user_meta( $sps->user_id, '_thumbnail_id_company_logo', true );
				if ( ! empty( $businessLogo ) ) {
						$businessLogo = wp_get_attachment_image_url( $businessLogo, 'full' );
				}
				if ( empty( $businessLogo ) && $com_logo ) {
					$businessLogo = content_url() . '/uploads/' . $com_logo[0]->meta_value;
				}
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $sps->user_id );
				$user_meta_arr     = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}
				$fullName       = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta_arr ) {
								return isset( $user_meta_arr[ $metaName ] ) ? $user_meta_arr[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);
				$galleryItems[] = array(
					'category'           => '',
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'shop_address'       => isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '',
					'Company'            => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'seller_id'          => isset( $sps->user_id ) ? $sps->user_id : '',
					'url'                => site_url() . '/seller/store/' . $user_meta_arr['shop_address'],
					'show_name_on_photo' => true,
					'roll'               => 'contractors',
				);
			}

			$result = array(
				'status'       => true,
				'current_page' => $pageno,
				'total_row'    => $total_rows,
				'total_page'   => $total_pages,
				'data'         => $galleryItems,
			);
			return $result;

		}

		public function mkwc_get_all_architects( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$no_of_records_per_page = 16;
			$offset                 = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12             = new \MP_Form_Handler();
			$gallery_items          = array();
			$total_pages_sql        = "SELECT COUNT(*) FROM architect_approve WHERE architect_value='Approve'";
			$total_rows             = $wpdb->get_var( $total_pages_sql );
			$total_pages            = ceil( $total_rows / $no_of_records_per_page );
			$architects             = "SELECT * FROM architect_approve WHERE architect_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_architects         = $wpdb->get_results( $architects );
			foreach ( $all_architects as $architects ) {
				$com_logo     = $wpmp_obj12->get_user_avatar( $architects->user_id, 'avatar' );
				$businessLogo = get_user_meta( $architects->user_id, '_thumbnail_id_company_logo', true );
				if ( ! empty( $businessLogo ) ) {
					$businessLogo = wp_get_attachment_image_url( $businessLogo, 'full' );
				}
				if ( empty( $businessLogo ) && $com_logo ) {
					$businessLogo = content_url() . '/uploads/' . $com_logo[0]->meta_value;
				}
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $architects->user_id );
				$user_meta         = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta[ $value->meta_key ] = $value->meta_value;
				}

				$fullName        = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta ) {
							return isset( $user_meta[ $metaName ] ) ? $user_meta[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);
				$gallery_items[] = array(
					'category'           => '',
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta['shop_name'] ) ? $user_meta['shop_name'] : '',
					'shop_address'       => isset( $user_meta['shop_address'] ) ? $user_meta['shop_address'] : '',
					'Company'            => isset( $user_meta['shop_name'] ) ? $user_meta['shop_name'] : '',
					'seller_id'          => isset( $architects->user_id ) ? $architects->user_id : '',
					'url'                => site_url() . '/seller/store/' . $user_meta['shop_address'],
					'roll'               => 'architechts',
					'show_name_on_photo' => true,
				);

				$result = array(
					'status'       => true,
					'current_page' => $pageno,
					'total_row'    => $total_rows,
					'total_page'   => $total_pages,
					'data'         => $gallery_items,
				);
				return $result;
			}
		}

		public function mkwc_get_engineers( $pageno, $no_of_records_per_page ) {
			global $wpdb;
			$offset     = ( $pageno - 1 ) * $no_of_records_per_page;
			$wpmp_obj12 = new \MP_Form_Handler();

			$total_pages_sql = "SELECT COUNT(*) FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='engineers' AND service_provider_approval.sp_value='Approve'";
			$total_rows      = $wpdb->get_var( $total_pages_sql );
			$total_pages     = ceil( $total_rows / $no_of_records_per_page );
			$sps             = "SELECT service_provider_approval.user_id FROM service_provider_approval INNER JOIN wp_usermeta ON service_provider_approval.user_id = wp_usermeta.user_id AND wp_usermeta.meta_value='engineers' AND service_provider_approval.sp_value='Approve' LIMIT $offset, $no_of_records_per_page";
			$all_sps         = $wpdb->get_results( $sps );
			foreach ( $all_sps as $sps ) {
				$com_logo          = $wpmp_obj12->get_user_avatar( $sps->user_id, 'avatar' );
				$usermeta_row_data = $wpdb->get_results( "select * from $wpdb->usermeta where user_id=" . $sps->user_id );
				$user_meta_arr     = array();
				foreach ( $usermeta_row_data as $key => $value ) {
					$user_meta_arr[ $value->meta_key ] = $value->meta_value;
				}

				$fullName = implode(
					' ',
					array_map(
						function( $metaName ) use ( $user_meta_arr ) {
								return isset( $user_meta_arr[ $metaName ] ) ? $user_meta_arr[ $metaName ] : '';
						},
						array( 'first_name', 'last_name' )
					)
				);

				$gallery_items[] = array(
					'photo_url'          => ! empty( $businessLogo ) ? $businessLogo : WK_MARKETPLACE . '/assets/images/shop-logo.png',
					'name'               => $fullName,
					'shop_name'          => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'shop_address'       => isset( $user_meta_arr['shop_address'] ) ? $user_meta_arr['shop_address'] : '',
					'Company'            => isset( $user_meta_arr['shop_name'] ) ? $user_meta_arr['shop_name'] : '',
					'url'                => site_url() . '/seller/store/' . $user_meta_arr['shop_address'],
					'show_name_on_photo' => true,
					'seller_id'          => isset( $sps->user_id ) ? $sps->user_id : '',
				);
			}
			$result = array(
				'status'       => true,
				'current_page' => $pageno,
				'total_row'    => $total_rows,
				'total_page'   => $total_pages,
				'data'         => $gallery_items,
			);
			return $result;
		}


	}


}
