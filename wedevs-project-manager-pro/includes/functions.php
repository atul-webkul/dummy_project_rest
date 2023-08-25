<?php
/**
 * Enqueue locale scripts for fullcalendar
 *
 * @since 1.6.6
 *
 * @return void
 */
function cpm_pro_enqueue_fullcalendar_locale() {
    $locale = get_locale();
    $script = '';

    // no need to add locale for en_US
    if ( 'en_US' === $locale ) {
        return;
    }

    $locale = explode( '_', $locale );

    // make sure we have two segments - 1.lang, 2.country
    if ( count( $locale ) < 2 ) {
        return;
    }

    $lang = $locale[0];
    $country = strtolower( $locale[1] );

    if ( $lang === $country ) {
        $script = $lang;
    } else {
        $script = $lang . '-' . $country;
    }

    if ( file_exists( CPM_PRO_PATH . "/assets/js/lang/{$script}.js" ) ) {
        wp_enqueue_script( 'cpm-pro-fullcalendar-locale', CPM_PRO_URL . "/assets/js/lang/{$script}.js", array( 'fullcalendar' ), null, true );
    }
}

