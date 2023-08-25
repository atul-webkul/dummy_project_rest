<?php

/**
 * @author Webkul
 * @version 1.0.0
 * This file handles general data helper functions.
 */

namespace WkMobikulMultivendorApi\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Mkwc_General_Data' ) ) {
	/**
	 * implements Util\Mkwc_General_Data_Interface
	 */
	class Mkwc_General_Data {

		/**
		 * Return $price in proper format as per WooCommerce configuration
		 *
		 * @param $price
		 * @return $price
		 */
		public function mkwc_get_formatted_price( $price = 0, $args = array() ) {
			return html_entity_decode( strip_tags( wc_price( $price, $args ) ) );
		}

		/**
		 * Return $price for variable product in format
		 *
		 * @param $from, $to
		 * @return $formatted_price
		 */
		public function mkwc_get_formatted_price_range( $min_price = 0, $max_price = 0 ) {
			return html_entity_decode( strip_tags( wc_format_price_range( $min_price, $max_price ) ) );
		}

		/**
		 * Return price for variable product
		 *
		 * @param $product_object
		 * @return $formatted_price
		 */
		public function mkwc_get_variable_product_price( $product_data ) {
			$min_price = $product_data->get_variation_price( 'min' );

			$max_price = $product_data->get_variation_price( 'max' );

			$min_regular_price = $product_data->get_variation_regular_price( 'min' );

			$max_regular_price = $product_data->get_variation_regular_price( 'max' );

			$regular_price = ( $product_data->get_variation_sale_price( 'min' ) && $min_regular_price != $max_regular_price ) ? $this->mkwc_get_formatted_price_range( $min_regular_price, $max_regular_price ) : '';

			$price = ( $min_price != $max_price ) ? $this->mkwc_get_formatted_price_range( $min_price, $max_price ) : $this->mkwc_get_formatted_price( $min_price );

			return array(
				'price'         => $price,
				'regular_price' => $regular_price,
			);
		}

		/**
		 * Get dominant color
		 */
		public function mkwc_get_dominant_Color( $url ) {
			return sprintf( '#f6f6f6' );

			if ( $url ) {
				$rTotal    = $gTotal = $bTotal = $total = 0;
				$imageInfo = getimagesize( $url );
				switch ( $imageInfo['mime'] ) {
					case 'image/jpeg':
						$image_create_func = 'imagecreatefromjpeg';
						break;
					case 'image/png':
						$image_create_func = 'imagecreatefrompng';
						break;
					case 'image/gif':
						$image_create_func = 'imagecreatefromgif';
						break;
				}
				$image = $image_create_func( $url );
				for ( $x = 0; $x < imagesx( $image ); $x = $x + 10 ) {
					for ( $y = 0; $y < imagesy( $image ); $y = $y + 10 ) {
						$rgb     = imagecolorat( $image, $x, $y );
						$r       = ( $rgb >> 16 ) & 0xFF;
						$g       = ( $rgb >> 8 ) & 0xFF;
						$b       = $rgb & 0xFF;
						$rTotal += $r;
						$gTotal += $g;
						$bTotal += $b;
						$total++;
					}
				}
				$rAverage = round( $rTotal / $total );
				$gAverage = round( $gTotal / $total );
				$bAverage = round( $bTotal / $total );
				return sprintf( '#%02x%02x%02x', $rAverage, $gAverage, $bAverage );
			} else {
				return sprintf( '#f6f6f6' );
			}
		}
	}
}
