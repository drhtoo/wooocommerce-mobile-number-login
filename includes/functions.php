<?php 

if( ! function_exists( 'get_user_ids_by_phone_number' ) ) {
    function get_user_ids_by_phone_number( $phone_number ) {
        global $wpdb;
        
        return $wpdb->get_col( $wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key = 'phone_number' AND meta_value = '%s'",
            array( wc_sanitize_phone_number( $phone_number ) )
            )
        );
    }
}

if( ! function_exists( 'is_phone_number_exists' ) ) {
    function is_phone_number_exists( $phone_number ) {
        return ! empty( get_user_ids_by_phone_number( $phone_number ) );
    }
}