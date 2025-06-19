<?php
if (!defined('ABSPATH')) {
    exit;
}

class Wild_Dragon_Schema_Generator {

    protected $cache_expiration = 12 * HOUR_IN_SECONDS;
    
    /**
     * Get cached schema or generate new one
     */
    public function get_cached_schema($type) {
        $cache_key = "wild_dragon_schema_{$type}_" . md5(get_the_permalink());
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            return $cached;
        }

        $method = "generate_{$type}_schema";
        if (!method_exists($this, $method)) {
            return '';
        }

        $schema = $this->$method();

        set_transient($cache_key, $schema, $this->cache_expiration);

        return $schema;
    }
    
    /**
     * Generates Organization schema for homepage
     */
    public function generate_homepage_schema() {
        $schema = [
            '@context' => 'https://schema.org', 
            '@type' => 'Organization',
            'name' => apply_filters('the_title', get_option('wild_dragon_organization_name', 'Veirdo')),
            'url' => home_url('/'),
            'logo' => esc_url(get_option('wild_dragon_logo_url', 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png')), 
            'sameAs' => array_filter([
                esc_url(get_option('wild_dragon_facebook_url')),
                esc_url(get_option('wild_dragon_twitter_url')),
                esc_url(get_option('wild_dragon_instagram_url')),
                esc_url(get_option('wild_dragon_youtube_url'))
            ]),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => get_option('wild_dragon_contact_number', '+91-6352449482'),
                'contactType' => get_option('wild_dragon_contact_type', 'Customer Service')
            ]
        ];

        return $this->render_json_ld($schema);
    }

    /**
     * Generates BreadcrumbList + Organization schema for category pages
     */
    public function generate_category_page_schema() {
        global $wp_query;

        $current_term = $wp_query->get_queried_object();

        $breadcrumb = [
            '@context' => 'https://schema.org', 
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => apply_filters('the_title', get_option('wild_dragon_organization_name', 'Veirdo')),
                    'item' => [
                        '@type' => 'Thing',
                        '@id' => home_url('/')
                    ]
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $current_term->name,
                    'item' => [
                        '@type' => 'Thing',
                        '@id' => get_term_link($current_term)
                    ]
                ]
            ]
        ];

        $organization = [
            '@context' => 'https://schema.org', 
            '@type' => 'Organization',
            'name' => apply_filters('the_title', get_option('wild_dragon_organization_name', 'Veirdo')),
            'url' => home_url('/'),
            'logo' => esc_url(get_option('wild_dragon_logo_url', 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png')), 
            'sameAs' => array_filter([
                esc_url(get_option('wild_dragon_facebook_url')),
                esc_url(get_option('wild_dragon_twitter_url')),
                esc_url(get_option('wild_dragon_instagram_url')),
                esc_url(get_option('wild_dragon_youtube_url'))
            ]),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => get_option('wild_dragon_contact_number', '+91-6352449482'),
                'contactType' => get_option('wild_dragon_contact_type', 'Customer Service')
            ]
        ];

        return $this->render_json_ld([
            '@context' => 'https://schema.org', 
            '@graph' => [$breadcrumb, $organization]
        ]);
    }

    /**
     * Generates BreadcrumbList + Organization schema for WooCommerce category pages
     */
    public function generate_wc_category_page_schema() {
        if (!function_exists('is_product_category') || !is_product_category()) {
            return '';
        }

        global $wp_query;
        $current_term = $wp_query->get_queried_object();

        $breadcrumb = [
            '@context' => 'https://schema.org', 
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => apply_filters('the_title', get_option('wild_dragon_organization_name', 'Veirdo')),
                    'item' => [
                        '@type' => 'Thing',
                        '@id' => home_url('/')
                    ]
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $current_term->name,
                    'item' => [
                        '@type' => 'Thing',
                        '@id' => get_term_link($current_term)
                    ]
                ]
            ]
        ];

        $organization = [
            '@context' => 'https://schema.org', 
            '@type' => 'Organization',
            'name' => apply_filters('the_title', get_option('wild_dragon_organization_name', 'Veirdo')),
            'url' => home_url('/'),
            'logo' => esc_url(get_option('wild_dragon_logo_url', 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png')), 
            'sameAs' => array_filter([
                esc_url(get_option('wild_dragon_facebook_url')),
                esc_url(get_option('wild_dragon_twitter_url')),
                esc_url(get_option('wild_dragon_instagram_url')),
                esc_url(get_option('wild_dragon_youtube_url'))
            ]),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => get_option('wild_dragon_contact_number', '+91-6352449482'),
                'contactType' => get_option('wild_dragon_contact_type', 'Customer Service')
            ]
        ];

        return $this->render_json_ld([
            '@context' => 'https://schema.org', 
            '@graph' => [$breadcrumb, $organization]
        ]);
    }

    /**
     * Generates ProductGroup schema for product page (WooCommerce only)
     */
    public function generate_product_page_schema() {
        if (!function_exists('wc_get_product') || !is_product()) {
            return '';
        }

        global $post;

        // Get product object
        $product_id = $post->ID;
        $product = wc_get_product($product_id);

        if (!$product || !$product->is_type('variable')) {
            return '';
        }

        // Build base product group
        $product_group = [
            '@context' => 'https://schema.org', 
            '@type' => 'ProductGroup',
            'name' => $product->get_name(),
            'description' => wp_strip_all_tags(apply_filters('the_content', $product->get_description())),
            'url' => get_permalink($product_id),
            'image' => wp_get_attachment_url($product->get_image_id()),
            'brand' => [
                '@type' => 'Brand',
                'name' => get_option('wild_dragon_organization_name', 'Veirdo')
            ],
            'hasVariant' => []
        ];

        // Get variants
        $variants = $product->get_available_variations();
        foreach ($variants as $variant_data) {
            $variant = new WC_Product_Variation($variant_data['variation_id']);

            $sku = $variant->get_sku();
            $price = $variant->get_price();
            $regular_price = $variant->get_regular_price();
            $stock_status = $variant->is_in_stock() ? 'http://schema.org/InStock' : 'http://schema.org/OutOfStock';
            $permalink = $variant->get_permalink();
            $size = $variant->get_attribute('pa_size');

            $product_variant = [
                '@type' => 'Product',
                'sku' => $sku ?: '',
                'mpn' => $sku ?: '', // MPN same as SKU
                'name' => $product->get_name() . ' - ' . ucwords($size),
                'description' => wp_strip_all_tags(apply_filters('the_content', $product->get_short_description())),
                'image' => wp_get_attachment_url($variant->get_image_id()),
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $permalink,
                    'priceCurrency' => get_woocommerce_currency(),
                    'price' => $price,
                    'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
                    'itemCondition' => 'http://schema.org/NewCondition',
                    'availability' => $stock_status
                ]
            ];

            // Add strikethrough price if regular price exists
            if (!empty($regular_price) && floatval($regular_price) > floatval($price)) {
                $product_variant['offers']['priceSpecification'] = [
                    '@type' => 'UnitPriceSpecification',
                    'priceType' => 'http://schema.org/StrikethroughPrice',
                    'price' => $regular_price,
                    'priceCurrency' => get_woocommerce_currency()
                ];
            }

            $product_group['hasVariant'][] = $product_variant;
        }

        $product_group['productGroupID'] = $product_id;

        // Aggregate Rating (dynamic from WooCommerce)
        $average_rating = $product->get_average_rating();
        $rating_count = $product->get_review_count();

        $aggregate_rating = [
            '@context' => 'https://schema.org', 
            '@type' => 'AggregateRating',
            'ratingValue' => $average_rating ? (string) floatval($average_rating) : null,
            'reviewCount' => $rating_count ? (string) intval($rating_count) : null
        ];

        // Remove null fields if no real data exists
        $aggregate_rating = array_filter($aggregate_rating, function($value) {
            return $value !== null;
        });

        // Organization
        $organization = [
            '@context' => 'https://schema.org', 
            '@type' => 'Organization',
            'name' => apply_filters('the_title', get_option('wild_dragon_organization_name', 'Veirdo')),
            'url' => home_url('/'),
            'logo' => esc_url(get_option('wild_dragon_logo_url', 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png')), 
            'sameAs' => array_filter([
                esc_url(get_option('wild_dragon_facebook_url')),
                esc_url(get_option('wild_dragon_twitter_url')),
                esc_url(get_option('wild_dragon_instagram_url')),
                esc_url(get_option('wild_dragon_youtube_url'))
            ]),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => get_option('wild_dragon_contact_number', '+91-6352449482'),
                'contactType' => get_option('wild_dragon_contact_type', 'Customer Service')
            ]
        ];

        return $this->render_json_ld([
            '@context' => 'https://schema.org',   
            '@graph' => [$product_group, $organization, $aggregate_rating]
        ]);
    }

    /**
     * Generates Article schema for blog posts
     */
    public function generate_article_schema() {
        global $post;

        if (!is_single() || get_post_type() !== 'post') {
            return '';
        }

        // Get post data
        $post_id = $post->ID;
        $title = get_the_title($post_id);
        $content = get_post_field('post_content', $post_id);
        $excerpt = get_the_excerpt($post_id);
        $permalink = get_permalink($post_id);
        $published_date = get_the_date('c', $post_id);
        $modified_date = get_the_modified_date('c', $post_id);
        $author = get_the_author_meta('display_name', $post->post_author);
        $author_url = get_author_posts_url($post->post_author);
        
        // Get featured image
        $featured_image = '';
        $image_width = 0;
        $image_height = 0;
        if (has_post_thumbnail($post_id)) {
            $image_id = get_post_thumbnail_id($post_id);
            $image_data = wp_get_attachment_image_src($image_id, 'full');
            if ($image_data) {
                $featured_image = $image_data[0];
                $image_width = $image_data[1];
                $image_height = $image_data[2];
            }
        }

        // Get meta description from your custom system
        $meta_description = $this->get_custom_meta_description($post_id);
        if (empty($meta_description)) {
            $meta_description = $excerpt ?: wp_trim_words(strip_tags($content), 25);
        }

        // Get categories for keywords
        $categories = get_the_category($post_id);
        $keywords = array_map(function($cat) { return $cat->name; }, $categories);

        // Build Article schema
        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => $permalink . '#/schema/article/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
            'inLanguage' => get_locale(),
            'headline' => $title,
            'description' => $meta_description,
            'datePublished' => $published_date,
            'dateModified' => $modified_date,
            'copyrightYear' => date('Y', strtotime($published_date)),
            'copyrightHolder' => $this->get_organization_schema(),
            'publisher' => $this->get_organization_schema(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $permalink,
                'url' => $permalink,
                'inLanguage' => get_locale(),
                'name' => $title,
                'datePublished' => $published_date,
                'dateModified' => $modified_date,
                'description' => $meta_description,
                'isPartOf' => [
                    '@type' => 'WebSite',
                    '@id' => home_url('/') . '#/schema/website/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
                    'url' => home_url('/'),
                    'name' => get_option('wild_dragon_organization_name', 'Veirdo'),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => home_url('/?s={search_term_string}')
                        ],
                        'query-input' => [
                            '@type' => 'PropertyValueSpecification',
                            'valueRequired' => 'http://schema.org/True',
                            'valueName' => 'search_term_string'
                        ]
                    ]
                ],
                'publisher' => $this->get_organization_schema(),
                'about' => $this->get_organization_schema()
            ],
            'author' => [
                '@type' => 'Person',
                '@id' => $author_url . '#/schema/person/' . base64_encode('user:' . $post->post_author) . '/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
                'name' => $author,
                'url' => $author_url
            ]
        ];

        // Add featured image if available
        if ($featured_image) {
            $image_object = [
                '@type' => 'ImageObject',
                '@id' => $permalink . '#/schema/image/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
                'inLanguage' => get_locale(),
                'url' => $featured_image,
                'contentUrl' => $featured_image,
                'width' => $image_width,
                'height' => $image_height,
                'caption' => $title
            ];

            $article['thumbnailUrl'] = $featured_image;
            $article['image'] = $image_object;
            $article['mainEntityOfPage']['primaryImageOfPage'] = $image_object;
            $article['mainEntityOfPage']['image'] = $image_object;
        }

        // Add keywords if available
        if (!empty($keywords)) {
            $article['keywords'] = implode(',', $keywords);
        }

        // Add breadcrumb
        $breadcrumb = $this->generate_post_breadcrumb($post_id, $title);

        return $this->render_json_ld([
            '@context' => 'https://schema.org',
            '@graph' => [$article, $breadcrumb]
        ]);
    }

    /**
     * Generates NewsArticle schema for news posts
     */
    public function generate_news_article_schema() {
        global $post;

        if (!is_single() || get_post_type() !== 'post') {
            return '';
        }

        // Get post data
        $post_id = $post->ID;
        $title = get_the_title($post_id);
        $content = get_post_field('post_content', $post_id);
        $excerpt = get_the_excerpt($post_id);
        $permalink = get_permalink($post_id);
        $published_date = get_the_date('c', $post_id);
        $modified_date = get_the_modified_date('c', $post_id);
        $author = get_the_author_meta('display_name', $post->post_author);
        $author_url = get_author_posts_url($post->post_author);
        
        // Get featured image
        $featured_image = '';
        $image_width = 0;
        $image_height = 0;
        $image_caption = '';
        if (has_post_thumbnail($post_id)) {
            $image_id = get_post_thumbnail_id($post_id);
            $image_data = wp_get_attachment_image_src($image_id, 'full');
            if ($image_data) {
                $featured_image = $image_data[0];
                $image_width = $image_data[1];
                $image_height = $image_data[2];
                $image_caption = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $title;
            }
        }

        // Get meta description from your custom system
        $meta_description = $this->get_custom_meta_description($post_id);
        if (empty($meta_description)) {
            $meta_description = $excerpt ?: wp_trim_words(strip_tags($content), 25);
        }

        // Get categories for keywords and article section
        $categories = get_the_category($post_id);
        $keywords = array_map(function($cat) { return $cat->name; }, $categories);
        $article_section = !empty($categories) ? $categories[0]->name : 'News';

        // Build NewsMediaOrganization
        $news_media_org = [
            '@type' => 'NewsMediaOrganization',
            'name' => get_option('wild_dragon_organization_name', 'Veirdo'),
            'url' => home_url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => esc_url(get_option('wild_dragon_logo_url', 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png')),
                'width' => 600,
                'height' => 60
            ],
            'sameAs' => array_filter([
                esc_url(get_option('wild_dragon_facebook_url')),
                esc_url(get_option('wild_dragon_twitter_url')),
                esc_url(get_option('wild_dragon_instagram_url')),
                esc_url(get_option('wild_dragon_youtube_url'))
            ]),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => get_option('wild_dragon_contact_number', '+91-6352449482'),
                'contactType' => get_option('wild_dragon_contact_type', 'Customer Service')
            ]
        ];

        // Build NewsArticle schema
        $news_article = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'inLanguage' => substr(get_locale(), 0, 2),
            'headline' => $title,
            'description' => $meta_description,
            'articleSection' => strtolower($article_section),
            'url' => $permalink,
            'datePublished' => $published_date,
            'dateModified' => $modified_date,
            'articleBody' => wp_strip_all_tags($content),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $permalink
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $author,
                'url' => $author_url
            ],
            'publisher' => $news_media_org
        ];

        // Add keywords if available
        if (!empty($keywords)) {
            $news_article['keywords'] = implode(',', $keywords);
        }

        // Add featured image if available
        if ($featured_image) {
            $image_object = [
                '@type' => 'ImageObject',
                'url' => $featured_image,
                'height' => $image_height,
                'width' => $image_width
            ];

            $news_article['image'] = $image_object;
            $news_article['associatedMedia'] = array_merge($image_object, [
                'caption' => $image_caption,
                'description' => $image_caption
            ]);
        }

        return $this->render_json_ld([
            '@context' => 'https://schema.org',
            '@graph' => [$news_media_org, $news_article]
        ]);
    }

    /**
     * Get meta description from your custom Wdseo_Meta_Description system
     */
    private function get_custom_meta_description($post_id) {
        // Check if your custom meta description class exists and get the description
        if (class_exists('Wdseo_Meta_Description')) {
            $custom_desc = get_post_meta($post_id, '_wdseo_meta_description', true);
            if (!empty($custom_desc)) {
                return $custom_desc;
            }
        }
        
        return '';
    }

    /**
     * Get organization schema object
     */
    private function get_organization_schema() {
        return [
            '@type' => 'Organization',
            '@id' => home_url('/') . '#/schema/organization/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
            'name' => get_option('wild_dragon_organization_name', 'Veirdo'),
            'url' => home_url('/'),
            'sameAs' => array_filter([
                esc_url(get_option('wild_dragon_facebook_url')),
                esc_url(get_option('wild_dragon_twitter_url')),
                esc_url(get_option('wild_dragon_instagram_url')),
                esc_url(get_option('wild_dragon_youtube_url'))
            ]),
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => home_url('/') . '#/schema/image/logo/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
                'url' => esc_url(get_option('wild_dragon_logo_url', 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png')),
                'width' => 696,
                'height' => 696,
                'caption' => get_option('wild_dragon_organization_name', 'Veirdo')
            ],
            'image' => [
                '@type' => 'ImageObject',
                '@id' => home_url('/') . '#/schema/image/logo/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
                'url' => esc_url(get_option('wild_dragon_logo_url', 'https://cdn.shopify.com/s/files/1/1982/7331/files/veirdologotrans_180x.png')),
                'width' => 696,
                'height' => 696,
                'caption' => get_option('wild_dragon_organization_name', 'Veirdo')
            ]
        ];
    }

    /**
     * Generate breadcrumb for post
     */
    private function generate_post_breadcrumb($post_id, $title) {
        $breadcrumb_items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => [
                    '@type' => 'Thing',
                    '@id' => home_url('/')
                ]
            ]
        ];

        // Add blog page if it exists
        $blog_page_id = get_option('page_for_posts');
        if ($blog_page_id) {
            $breadcrumb_items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => get_the_title($blog_page_id),
                'item' => [
                    '@type' => 'Thing',
                    '@id' => get_permalink($blog_page_id)
                ]
            ];
            $position = 3;
        } else {
            $position = 2;
        }

        // Add current post
        $breadcrumb_items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $title,
            'item' => [
                '@type' => 'Thing',
                '@id' => get_permalink($post_id)
            ]
        ];

        return [
            '@type' => 'BreadcrumbList',
            '@id' => get_permalink($post_id) . '#/schema/breadcrumb/' . sanitize_title(get_option('wild_dragon_organization_name', 'veirdo')),
            'itemListElement' => $breadcrumb_items
        ];
    }

    /**
     * Renders JSON-LD script tag
     */
    protected function render_json_ld($data) {
        return '<script type="application/ld+json">' .
               wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
               '</script>';
    }
}