<?php
/**
 * Plugin Name: Wild Dragon Schema
 * Plugin URI: https://wilddragon.in 
 * Description: Adds structured data (schema.org) for Home, Category pages & Posts/Articles on WordPress sites.
 * Version: 1.1.0
 * Author: Wild Dragon Dev Team
 * Author URI: https://wilddragon.in 
 * License: GPL2+
 * Text Domain: wild-dragon-schema
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('WILD_DRAGON_SCHEMA_VERSION', '1.1.0');
define('WILD_DRAGON_SCHEMA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WILD_DRAGON_SCHEMA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required classes
require_once WILD_DRAGON_SCHEMA_PLUGIN_DIR . 'includes/class-wild-dragon-schema-cache.php';
require_once WILD_DRAGON_SCHEMA_PLUGIN_DIR . 'includes/class-wild-dragon-settings.php';
require_once WILD_DRAGON_SCHEMA_PLUGIN_DIR . 'includes/class-wild-dragon-schema-generator.php';
require_once WILD_DRAGON_SCHEMA_PLUGIN_DIR . 'includes/class-wild-dragon-post-meta.php';

// Enable debug mode?
define('WILD_DRAGON_SCHEMA_DEBUG', false); // Set to true to disable schema output

/**
 * Check if WooCommerce is active
 */
function wild_dragon_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

/**
 * Outputs schema JSON-LD in head section based on current page type.
 */
function wild_dragon_output_schema_jsonld() {
    if (defined('WILD_DRAGON_SCHEMA_DEBUG') && WILD_DRAGON_SCHEMA_DEBUG) {
        return;
    }

    $generator = new Wild_Dragon_Schema_Generator();

    if (is_front_page()) {
        echo $generator->get_cached_schema('homepage');
    } elseif (is_category()) {
        echo $generator->get_cached_schema('category_page');
    } elseif (wild_dragon_is_woocommerce_active() && function_exists('is_product_category') && is_product_category()) {
        echo $generator->get_cached_schema('wc_category_page');
    } elseif (wild_dragon_is_woocommerce_active() && function_exists('is_product') && is_product()) {
        echo $generator->get_cached_schema('product_page');
    } elseif (is_single() && get_post_type() === 'post') {
        // Determine schema type for post
        $post_id = get_the_ID();
        $schema_type = Wild_Dragon_Post_Meta::get_post_schema_type($post_id);
        echo $generator->get_cached_schema($schema_type);
    }
}
add_action('wp_head', 'wild_dragon_output_schema_jsonld');

/**
 * Register activation hook
 */
function wild_dragon_schema_activate() {
    // Ensure default settings exist
    $defaults = Wild_Dragon_Settings::get_default_settings();
    foreach ($defaults as $key => $value) {
        if (get_option($key) === false) {
            update_option($key, $value);
        }
    }
}
register_activation_hook(__FILE__, 'wild_dragon_schema_activate');

/**
 * Clear cache when posts are updated
 */
function wild_dragon_clear_post_cache($post_id) {
    if (get_post_type($post_id) === 'post') {
        $permalink = get_permalink($post_id);
        Wild_Dragon_Schema_Cache::clear_schema_cache('wild_dragon_schema_article_' . md5($permalink));
        Wild_Dragon_Schema_Cache::clear_schema_cache('wild_dragon_schema_news_article_' . md5($permalink));
    }
}
add_action('save_post', 'wild_dragon_clear_post_cache');
add_action('delete_post', 'wild_dragon_clear_post_cache');

/**
 * Add admin notice for successful activation
 */
function wild_dragon_schema_admin_notice() {
    if (get_transient('wild_dragon_schema_activated')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Wild Dragon Schema</strong> has been activated! Configure your settings in <a href="<?php echo admin_url('options-general.php?page=wild-dragon-schema'); ?>">Settings > Schema Settings</a>.</p>
        </div>
        <?php
        delete_transient('wild_dragon_schema_activated');
    }
}
add_action('admin_notices', 'wild_dragon_schema_admin_notice');

// Set activation transient
register_activation_hook(__FILE__, function() {
    set_transient('wild_dragon_schema_activated', true, 30);
});