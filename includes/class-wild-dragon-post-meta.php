<?php
if (!defined('ABSPATH')) {
    exit;
}

class Wild_Dragon_Post_Meta {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_schema_meta_box']);
        add_action('save_post', [__CLASS__, 'save_schema_meta']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
    }

    public static function add_schema_meta_box() {
        add_meta_box(
            'wild_dragon_schema_meta',
            'Schema Settings',
            [__CLASS__, 'render_schema_meta_box'],
            'post',
            'side',
            'default'
        );
    }

    public static function render_schema_meta_box($post) {
        wp_nonce_field('wild_dragon_schema_meta_nonce', 'wild_dragon_schema_meta_nonce');
        
        $schema_type = get_post_meta($post->ID, '_wild_dragon_schema_type', true);
        $auto_detect = get_post_meta($post->ID, '_wild_dragon_auto_detect_schema', true);
        
        // Default to auto-detect if not set
        if (empty($auto_detect)) {
            $auto_detect = 'yes';
        }
        ?>
        <div class="wild-dragon-schema-meta">
            <p>
                <label>
                    <input type="checkbox" name="wild_dragon_auto_detect_schema" value="yes" <?php checked($auto_detect, 'yes'); ?>>
                    Auto-detect schema based on categories
                </label>
            </p>
            
            <div id="manual-schema-selection" style="<?php echo ($auto_detect === 'yes') ? 'display:none;' : ''; ?>">
                <p>
                    <label for="wild_dragon_schema_type"><strong>Schema Type:</strong></label><br>
                    <select name="wild_dragon_schema_type" id="wild_dragon_schema_type" style="width: 100%;">
                        <option value="article" <?php selected($schema_type, 'article'); ?>>Article</option>
                        <option value="news_article" <?php selected($schema_type, 'news_article'); ?>>News Article</option>
                    </select>
                </p>
            </div>
            
            <div id="auto-detect-info" style="<?php echo ($auto_detect !== 'yes') ? 'display:none;' : ''; ?>">
                <p><small>Schema will be automatically selected based on post categories configured in Schema Settings.</small></p>
            </div>
        </div>
        
        <style>
        .wild-dragon-schema-meta label {
            font-weight: 500;
        }
        .wild-dragon-schema-meta select {
            margin-top: 5px;
        }
        </style>
        <?php
    }

    public static function save_schema_meta($post_id) {
        if (!isset($_POST['wild_dragon_schema_meta_nonce']) || 
            !wp_verify_nonce($_POST['wild_dragon_schema_meta_nonce'], 'wild_dragon_schema_meta_nonce')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save auto-detect preference
        $auto_detect = isset($_POST['wild_dragon_auto_detect_schema']) ? 'yes' : 'no';
        update_post_meta($post_id, '_wild_dragon_auto_detect_schema', $auto_detect);

        // Save manual schema type if auto-detect is disabled
        if ($auto_detect === 'no' && isset($_POST['wild_dragon_schema_type'])) {
            $schema_type = sanitize_text_field($_POST['wild_dragon_schema_type']);
            update_post_meta($post_id, '_wild_dragon_schema_type', $schema_type);
        }

        // Clear cache for this post
        Wild_Dragon_Schema_Cache::clear_schema_cache('wild_dragon_schema_article_' . md5(get_permalink($post_id)));
        Wild_Dragon_Schema_Cache::clear_schema_cache('wild_dragon_schema_news_article_' . md5(get_permalink($post_id)));
    }

    public static function enqueue_admin_scripts($hook) {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }

        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                function toggleSchemaSelection() {
                    var autoDetect = $("input[name=\'wild_dragon_auto_detect_schema\']").is(":checked");
                    if (autoDetect) {
                        $("#manual-schema-selection").hide();
                        $("#auto-detect-info").show();
                    } else {
                        $("#manual-schema-selection").show();
                        $("#auto-detect-info").hide();
                    }
                }
                
                $("input[name=\'wild_dragon_auto_detect_schema\']").change(toggleSchemaSelection);
                toggleSchemaSelection();
            });
        ');
    }

    /**
     * Get schema type for a post
     */
    public static function get_post_schema_type($post_id) {
        $auto_detect = get_post_meta($post_id, '_wild_dragon_auto_detect_schema', true);
        
        // Default to auto-detect if not set
        if (empty($auto_detect)) {
            $auto_detect = 'yes';
        }
        
        if ($auto_detect === 'yes') {
            return self::auto_detect_schema_type($post_id);
        } else {
            return get_post_meta($post_id, '_wild_dragon_schema_type', true) ?: 'article';
        }
    }

    /**
     * Auto-detect schema type based on categories
     */
    private static function auto_detect_schema_type($post_id) {
        $categories = get_the_category($post_id);
        $news_categories = get_option('wild_dragon_news_categories', []);
        
        if (!empty($categories) && !empty($news_categories)) {
            foreach ($categories as $category) {
                if (in_array($category->term_id, $news_categories)) {
                    return 'news_article';
                }
            }
        }
        
        return 'article';
    }
}

Wild_Dragon_Post_Meta::init();