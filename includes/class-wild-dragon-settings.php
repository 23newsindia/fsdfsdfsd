<?php
if (!defined('ABSPATH')) {
    exit;
}

class Wild_Dragon_Settings {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }

    public static function add_admin_menu() {
        add_options_page(
            'Wild Dragon Schema Settings',
            'Schema Settings',
            'manage_options',
            'wild-dragon-schema',
            [__CLASS__, 'render_settings_page']
        );
    }

    public static function register_settings() {
        // Organization settings
        register_setting('wild_dragon_schema', 'wild_dragon_organization_name', [
            'type' => 'string',
            'default' => 'Veirdo',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_logo_url', [
            'type' => 'string',
            'default' => 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png', 
            'sanitize_callback' => 'esc_url_raw'
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_facebook_url', [
            'type' => 'string',
            'default' => 'https://www.facebook.com/profile.php?id=100076398736432',
            'sanitize_callback' => 'esc_url_raw'
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_twitter_url', [
            'type' => 'string',
            'default' => 'https://twitter.com/VeirdoVenture',
            'sanitize_callback' => 'esc_url_raw'
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_instagram_url', [
            'type' => 'string',
            'default' => 'https://www.instagram.com/veirdo.in/',
            'sanitize_callback' => 'esc_url_raw'
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_youtube_url', [
            'type' => 'string',
            'default' => 'https://www.youtube.com/channel/UCZUkqeonhghcFbLS9VxD8EQ',
            'sanitize_callback' => 'esc_url_raw'
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_contact_number', [
            'type' => 'string',
            'default' => '+91-6352449482',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_contact_type', [
            'type' => 'string',
            'default' => 'Customer Service',
            'sanitize_callback' => 'sanitize_text_field'
        ]);

        // Post schema settings
        register_setting('wild_dragon_schema', 'wild_dragon_news_categories', [
            'type' => 'array',
            'default' => [],
            'sanitize_callback' => [__CLASS__, 'sanitize_category_array']
        ]);

        register_setting('wild_dragon_schema', 'wild_dragon_default_post_schema', [
            'type' => 'string',
            'default' => 'article',
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }

    public static function sanitize_category_array($input) {
        if (!is_array($input)) {
            return [];
        }
        return array_map('intval', $input);
    }

    public static function render_settings_page() {
        if (isset($_POST['submit'])) {
            // Clear all schema caches when settings are updated
            Wild_Dragon_Schema_Cache::clear_all_schema_caches();
            echo '<div class="notice notice-success"><p>Settings saved and schema cache cleared!</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>Wild Dragon Schema Settings</h1>
            
            <div class="nav-tab-wrapper">
                <a href="#organization" class="nav-tab nav-tab-active" onclick="switchTab(event, 'organization')">Organization</a>
                <a href="#posts" class="nav-tab" onclick="switchTab(event, 'posts')">Posts & Articles</a>
                <a href="#cache" class="nav-tab" onclick="switchTab(event, 'cache')">Cache</a>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('wild_dragon_schema');
                do_settings_sections('wild_dragon_schema');
                ?>

                <!-- Organization Tab -->
                <div id="organization" class="tab-content">
                    <h2>Organization Information</h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="wild_dragon_organization_name">Organization Name</label></th>
                                <td>
                                    <input name="wild_dragon_organization_name" type="text"
                                           id="wild_dragon_organization_name"
                                           class="regular-text"
                                           value="<?php echo esc_attr(get_option('wild_dragon_organization_name')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="wild_dragon_logo_url">Logo URL</label></th>
                                <td>
                                    <input name="wild_dragon_logo_url" type="text"
                                           id="wild_dragon_logo_url"
                                           class="regular-text"
                                           value="<?php echo esc_url(get_option('wild_dragon_logo_url')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="wild_dragon_facebook_url">Facebook URL</label></th>
                                <td>
                                    <input name="wild_dragon_facebook_url" type="text"
                                           id="wild_dragon_facebook_url"
                                           class="regular-text"
                                           value="<?php echo esc_url(get_option('wild_dragon_facebook_url')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="wild_dragon_twitter_url">Twitter URL</label></th>
                                <td>
                                    <input name="wild_dragon_twitter_url" type="text"
                                           id="wild_dragon_twitter_url"
                                           class="regular-text"
                                           value="<?php echo esc_url(get_option('wild_dragon_twitter_url')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="wild_dragon_instagram_url">Instagram URL</label></th>
                                <td>
                                    <input name="wild_dragon_instagram_url" type="text"
                                           id="wild_dragon_instagram_url"
                                           class="regular-text"
                                           value="<?php echo esc_url(get_option('wild_dragon_instagram_url')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="wild_dragon_youtube_url">YouTube Channel URL</label></th>
                                <td>
                                    <input name="wild_dragon_youtube_url" type="text"
                                           id="wild_dragon_youtube_url"
                                           class="regular-text"
                                           value="<?php echo esc_url(get_option('wild_dragon_youtube_url')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="wild_dragon_contact_number">Contact Number</label></th>
                                <td>
                                    <input name="wild_dragon_contact_number" type="text"
                                           id="wild_dragon_contact_number"
                                           class="regular-text"
                                           value="<?php echo esc_attr(get_option('wild_dragon_contact_number')); ?>">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="wild_dragon_contact_type">Contact Type</label></th>
                                <td>
                                    <input name="wild_dragon_contact_type" type="text"
                                           id="wild_dragon_contact_type"
                                           class="regular-text"
                                           value="<?php echo esc_attr(get_option('wild_dragon_contact_type')); ?>">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Posts Tab -->
                <div id="posts" class="tab-content" style="display:none;">
                    <h2>Posts & Articles Schema</h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="wild_dragon_default_post_schema">Default Post Schema</label></th>
                                <td>
                                    <select name="wild_dragon_default_post_schema" id="wild_dragon_default_post_schema">
                                        <option value="article" <?php selected(get_option('wild_dragon_default_post_schema', 'article'), 'article'); ?>>Article</option>
                                        <option value="news_article" <?php selected(get_option('wild_dragon_default_post_schema', 'article'), 'news_article'); ?>>News Article</option>
                                    </select>
                                    <p class="description">Default schema type for new posts when auto-detection is enabled.</p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label>News Categories</label></th>
                                <td>
                                    <?php
                                    $categories = get_categories(['hide_empty' => false]);
                                    $news_categories = get_option('wild_dragon_news_categories', []);
                                    
                                    if (!empty($categories)) {
                                        echo '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">';
                                        foreach ($categories as $category) {
                                            $checked = in_array($category->term_id, $news_categories) ? 'checked' : '';
                                            echo '<label style="display: block; margin-bottom: 5px;">';
                                            echo '<input type="checkbox" name="wild_dragon_news_categories[]" value="' . $category->term_id . '" ' . $checked . '> ';
                                            echo esc_html($category->name) . ' (' . $category->count . ' posts)';
                                            echo '</label>';
                                        }
                                        echo '</div>';
                                    } else {
                                        echo '<p>No categories found.</p>';
                                    }
                                    ?>
                                    <p class="description">Select categories that should use NewsArticle schema instead of regular Article schema.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Cache Tab -->
                <div id="cache" class="tab-content" style="display:none;">
                    <h2>Schema Cache Management</h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">Clear Schema Cache</th>
                                <td>
                                    <button type="button" class="button" onclick="clearSchemaCache()">Clear All Schema Cache</button>
                                    <p class="description">Clear all cached schema data. This will force regeneration of schema markup on next page load.</p>
                                    <div id="cache-status"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <style>
        .nav-tab-wrapper {
            margin-bottom: 20px;
        }
        .tab-content {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-top: none;
        }
        .nav-tab-active {
            background: #fff !important;
            border-bottom: 1px solid #fff !important;
        }
        </style>

        <script>
        function switchTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("nav-tab");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("nav-tab-active");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("nav-tab-active");
        }

        function clearSchemaCache() {
            var statusDiv = document.getElementById('cache-status');
            statusDiv.innerHTML = '<p>Clearing cache...</p>';
            
            jQuery.post(ajaxurl, {
                action: 'wild_dragon_clear_cache',
                nonce: '<?php echo wp_create_nonce('wild_dragon_clear_cache'); ?>'
            }, function(response) {
                if (response.success) {
                    statusDiv.innerHTML = '<p style="color: green;">✓ Cache cleared successfully!</p>';
                } else {
                    statusDiv.innerHTML = '<p style="color: red;">✗ Error clearing cache.</p>';
                }
                setTimeout(function() {
                    statusDiv.innerHTML = '';
                }, 3000);
            });
        }
        </script>
        <?php
    }

    public static function get_default_settings() {
        return [
            'wild_dragon_organization_name' => 'Veirdo',
            'wild_dragon_logo_url' => 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png',
            'wild_dragon_facebook_url' => 'https://www.facebook.com/profile.php?id=100076398736432',
            'wild_dragon_twitter_url' => 'https://twitter.com/VeirdoVenture',
            'wild_dragon_instagram_url' => 'https://www.instagram.com/veirdo.in/',
            'wild_dragon_youtube_url' => 'https://www.youtube.com/channel/UCZUkqeonhghcFbLS9VxD8EQ',
            'wild_dragon_contact_number' => '+91-6352449482',
            'wild_dragon_contact_type' => 'Customer Service',
            'wild_dragon_news_categories' => [],
            'wild_dragon_default_post_schema' => 'article'
        ];
    }
}

Wild_Dragon_Settings::init();

// AJAX handler for clearing cache
add_action('wp_ajax_wild_dragon_clear_cache', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'wild_dragon_clear_cache')) {
        wp_die('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    Wild_Dragon_Schema_Cache::clear_all_schema_caches();
    wp_send_json_success();
});