<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class VillaTheme_Support_Pro
 * 1.0.1
 */
if ( ! class_exists( 'VillaTheme_Support_Pro' ) ) {


	class VillaTheme_Support_Pro {
		public function __construct( $data ) {
			$this->data               = array();
			$this->data['support']    = $data['support'];
			$this->data['docs']       = $data['docs'];
			$this->data['review']     = $data['review'];
			$this->data['css_url']    = $data['css'];
			$this->data['images_url'] = $data['image'];
			$this->data['slug']       = $data['slug'];
			$this->data['menu_slug']  = $data['menu_slug'];
			$this->data['version']    = isset( $data['version'] ) ? $data['version'] : '1.0.0';
			add_action( 'villatheme_support_' . $this->data['slug'], array( $this, 'villatheme_support' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9999 );
			add_action( 'admin_menu', array( $this, 'admin_init' ) );
		}

		public function admin_init() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			if ( ! isset( $_GET['_villatheme_nonce'] ) ) {
				return;
			}
			if ( wp_verify_nonce( $_GET['_villatheme_nonce'], 'hide_maybe' ) ) {
				set_transient( $this->data['slug'] . $this->data['version'] . 'hide_maybe', 1, 2592000 );
			}
		}

		/**
		 * Add Extension page
		 */
		function admin_menu() {
			add_submenu_page(
				$this->data['menu_slug'], esc_html__( 'Extensions', $this->data['slug'] ), esc_html__( 'Extensions', $this->data['slug'] ), 'manage_options', $this->data['slug'] . '-extensions', array(
					$this,
					'page_callback'
				)
			);
		}

		/**
		 * Extensions page
		 * @return bool
		 */
		public function page_callback() { ?>
            <div class="villatheme-extension-page">
                <div class="villatheme-extension-top">
                    <h2><?php echo esc_html__( 'THE BEST PLUGINS FOR WOOCOMMERCE', $this->data['slug'] ) ?></h2>
                    <p><?php echo esc_html__( 'Our plugins are constantly updated and thanks to your feedback. We add new features on a daily basis. Try our live demo and start increasing the conversions on your ecommerce right away.', $this->data['slug'] ) ?></p>
                </div>
                <div class="villatheme-extension-content">
					<?php
					$feeds = get_transient( 'villatheme_ads' );
					if ( ! $feeds ) {
						@$ads = file_get_contents( 'https://villatheme.com/wp-json/info/v1' );
						set_transient( 'villatheme_ads', $ads, 86400 );
					} else {
						$ads = $feeds;
					}
					if ( $ads ) {
						$ads = json_decode( $ads );
						$ads = array_filter( $ads );
					} else {
						return false;
					}
					if ( count( $ads ) ) {
						foreach ( $ads as $ad ) {
							?>
                            <div class="villatheme-col-4">
								<?php if ( $ad->image ) { ?>
                                    <div class="villatheme-item-image">
                                        <img src="<?php echo esc_url( $ad->image ) ?>">
                                        <div class="villatheme-item-controls">
                                            <div class="villatheme-item-controls-inner">
												<?php if ( @$ad->link ) { ?>
                                                    <a class="villatheme-button villatheme-primary" target="_blank"
                                                       href="<?php echo esc_url( $ad->link ) ?>"><?php echo esc_html__( 'Download', $this->data['slug'] ) ?></a>
												<?php }
												if ( @$ad->demo_url ) { ?>
                                                    <a class="villatheme-button" target="_blank"
                                                       href="<?php echo esc_url( $ad->demo_url ) ?>"><?php echo esc_html__( 'Demo', $this->data['slug'] ) ?></a>
												<?php }
												if ( @$ad->free_url ) { ?>
                                                    <a class="villatheme-button" target="_blank"
                                                       href="<?php echo esc_url( $ad->free_url ) ?>"><?php echo esc_html__( 'Trial', $this->data['slug'] ) ?></a>
												<?php } ?>
                                            </div>
                                        </div>
                                    </div>
								<?php } ?>
								<?php if ( $ad->title ) { ?>
                                    <div class="villatheme-item-title">
                                        <h3>
											<?php if ( @$ad->link ) { ?>
                                            <a class="villatheme-primary-color" target="_blank"
                                               href="<?php echo esc_url( $ad->link ) ?>">
												<?php } ?>
												<?php echo esc_html( $ad->title ) ?>
												<?php if ( @$ad->link ) { ?>
                                            </a>
										<?php } ?>
                                        </h3>
                                    </div>
                                    <div class="villatheme-item-rating">
                                        &#x2606;&#x2606;&#x2606;&#x2606;&#x2606;
                                    </div>
								<?php }
								if ( @$ad->description ) { ?>
                                    <div class="villatheme-item-description"><?php echo strip_tags( $ad->description ) ?></div>
								<?php } ?>

                            </div>
						<?php }
					} ?>
                </div>
            </div>
		<?php }


		/**
		 * Init script
		 */
		public function scripts() {
			wp_enqueue_style( 'villatheme-support', $this->data['css_url'] . 'villatheme-support.css' );
		}

		/**
		 *
		 */
		public function villatheme_support() { ?>

            <div id="villatheme-support" class="vi-ui form segment">

                <div class="fields">
                    <div class="four wide field ">
                        <h3><?php echo esc_html__( 'HELP CENTER', $this->data['slug'] ) ?></h3>
                        <div class="villatheme-support-area">
                            <a target="_blank" href="<?php echo esc_url( $this->data['support'] ) ?>">
                                <img src="<?php echo $this->data['images_url'] . 'support.jpg' ?>">
                            </a>
                        </div>
                        <div class="villatheme-docs-area">
                            <a target="_blank" href="<?php echo esc_url( $this->data['docs'] ) ?>">
                                <img src="<?php echo $this->data['images_url'] . 'docs.jpg' ?>">
                            </a>
                        </div>
                        <div class="villatheme-review-area">
                            <a target="_blank" href="<?php echo esc_url( $this->data['review'] ) ?>">
                                <img src="<?php echo $this->data['images_url'] . 'reviews.jpg' ?>">
                            </a>
                        </div>
                    </div>
					<?php
					if ( ! get_transient( $this->data['slug'] . $this->data['version'] . 'hide_maybe' ) ) {
						$items = $this->get_data( $this->data['slug'] );
						if ( count( $items ) && is_array( $items ) ) {
							shuffle( $items );
							$items = array_slice( $items, 0, 2 );
							foreach ( $items as $k => $item ) { ?>
                                <div class="six wide field">
									<?php if ( $k == 0 ) { ?>
                                        <h3><?php echo esc_html__( 'MAYBE YOU LIKE', $this->data['slug'] ) ?></h3>
									<?php } else { ?>
                                        <h3>&nbsp;</h3>
									<?php } ?>
                                    <div class="villatheme-item">
                                        <a target="_blank" href="<?php echo esc_url( $item->link ) ?>">
                                            <img src="<?php echo esc_url( $item->image ) ?>"/>
                                        </a>
                                    </div>
                                </div>
							<?php }
							?>

						<?php }
					} ?>
                </div>
				<?php if ( ! get_transient( $this->data['slug'] . $this->data['version'] . 'hide_maybe' ) ) { ?>
                    <div class="vi-ui right floated button"><a
                                href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'villatheme-hide-notice', '1' ), 'hide_maybe', '_villatheme_nonce' ) ); ?>"><?php echo esc_html__( 'Close', $this->data['slug'] ) ?>
                    </div>
				<?php } ?>
            </div>
		<?php }

		/**
		 * Get data from server
		 * @return array
		 */
		protected function get_data( $slug = false ) {
			$feeds = get_transient( 'villatheme_ads' );
			if ( ! $feeds ) {
				@$ads = file_get_contents( 'https://villatheme.com/wp-json/info/v1' );
				set_transient( 'villatheme_ads', $ads, 86400 );
			} else {
				$ads = $feeds;
			}
			if ( $ads ) {
				$ads = json_decode( $ads );
				$ads = array_filter( $ads );
			} else {
				return false;
			}
			if ( count( $ads ) ) {
				$theme_select = null;
				foreach ( $ads as $ad ) {
					if ( $slug ) {
						if ( $ad->slug == $slug ) {
							continue;
						}
					}
					$item        = new stdClass();
					$item->title = $ad->title;
					$item->link  = $ad->link;
					$item->thumb = $ad->thumb;
					$item->image = $ad->image;
					$item->desc  = $ad->description;
					$results[]   = $item;
				}
			} else {
				return false;
			}
			if ( count( $results ) ) {
				return $results;
			} else {
				return false;
			}
		}
	}
}
new VillaTheme_Support_Pro(
	array(
		'support'   => 'https://villatheme.com/supports/forum/plugins/woo-multi-currency/',
		'docs'      => 'http://docs.villatheme.com/?item=woocommerce-multi-currency',
		'review'    => 'https://codecanyon.net/downloads',
		'css'       => WOOMULTI_CURRENCY_CSS,
		'image'     => WOOMULTI_CURRENCY_IMAGES,
		'slug'      => 'woocommerce-multi-currency',
		'menu_slug' => 'woocommerce-multi-currency',
		'version'   => WOOMULTI_CURRENCY_VERSION
	)
);