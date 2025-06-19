<?php
if (!defined('ABSPATH')) {
    exit;
}

class Wild_Dragon_Schema_Cache {

    public static function clear_schema_cache($key_prefix = '') {
        global $wpdb;

        $like = !empty($key_prefix) ? $wpdb->esc_like($key_prefix) . '%' : 'wild_dragon_schema_%';

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s",
                $like
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE %s",
                $wpdb->esc_like('_transient_timeout_wild_dragon_schema_') . '%'
            )
        );
    }

    public static function clear_all_schema_caches() {
        self::clear_schema_cache();
    }
}