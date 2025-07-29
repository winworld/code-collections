<?php
class AjaxLoader
{
    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('wp_ajax_load_more_items', [$this, 'ajax_load_more']);
        add_action('wp_ajax_nopriv_load_more_items', [$this, 'ajax_load_more']);
    }

    /**
     * Load content dynamically based on attributes.
     *   
     */
    public function display_items($atts)
    {
        $atts = shortcode_atts([
            'post_type' => 'post',
            'posts_per_page' => 10,
            'category_name' => '',
        ], $atts, 'posts');

        switch ($atts['post_type']) {
            case 'post':
                return $this->get_items($atts, [
                    'action' => 'load_more_items',
                    'form' => '#filter-form',
                ]);
            case CF_POST_TYPE_PUBLICATION:
                return $this->get_items($atts, [
                    'action' => 'load_more_items',
                    'form' => '#filter-form',
                    'secondaryForm' => '#filter-pub-category',
                ]);
            default:
                return '<p>No content available.</p>';
        }
    }

    /**
     * Load items dynamically for the frontend.
     *
     * @param array $atts Attributes for the query.
     * @param array $js_params JavaScript parameters for AJAX.
     * @return string Rendered HTML.
     */
    private function get_items($atts, $js_params)
    {
        $args = $this->build_query($atts);
        $query = new WP_Query($args);

        // Get template, wrapper class, and no-posts message
        $config = $this->get_template_and_wrapper($atts['post_type']);
        $template = $config['template'];
        $wrapper_class = $config['wrapper_class'];
        $no_item_message = $config['no_item_message'];

        ob_start();

        // Output the appropriate filter template
        get_template_part('partials/filters/' . ($atts['post_type'] === CF_POST_TYPE_PUBLICATION ? 'publication' : 'media'));
        ?>

        <div class="container">
            <div id="tobefilled" class="row">
                <?php $this->render($query, $template, $wrapper_class, $no_item_message); ?>
            </div>

            <?php if ($query->max_num_pages > 1): ?>
                <div class="text-center mb-4">
                    <button id="load-more" data-page="2" data-type="<?php echo esc_attr($atts['post_type']); ?>"
                        class="load-more-btn cta-button cta-button--secondary-dark-green cta-button--medium">
                        LOAD MORE <?php echo ($atts['post_type'] === CF_POST_TYPE_PUBLICATION ? 'PUBLICATIONS' : 'NEWS'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <script>
            jQuery(document).ready(function ($) {
                $('#tobefilled').ajaxLoader({
                    loadMoreButton: '#load-more',
                    form: '<?php echo $js_params['form'] ?? ''; ?>',
                    secondaryForm: '<?php echo $js_params['secondaryForm'] ?? ''; ?>',
                    queryVars: {
                        nonce: rugbySettings.nonce,
                        action: '<?php echo ($js_params['action']); ?>',
                        post_type: '<?php echo esc_js($atts['post_type']); ?>',
                        posts_per_page: <?php echo intval($atts['posts_per_page']); ?>,
                    },
                });
            });
        </script>

        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render items dynamically based on the query.     
     */
    private function render($query, $template, $wrapper_class = 'item-box', $no_item_message = 'No posts found.')
    {
        if ($query->have_posts()):
            $counter = 0;
            while ($query->have_posts()):
                $query->the_post();
                $counter++;
                $class = $counter <= 6 ? 'col-lg-4 col-md-6 col-12' : 'col-lg-3 col-md-6 col-12';
                ?>
                <div class="<?php echo esc_attr($wrapper_class . ' ' . $class); ?>">
                    <?php get_template_part($template); ?>
                </div>
                <?php
            endwhile;
        else:
            ?>
            <p><?php echo esc_html($no_item_message); ?></p>
            <?php
        endif;
    }

    /**
     * Handle AJAX requests for loading more items dynamically.
     */
    public function ajax_load_more()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cfrugby_nonce')) {
            wp_send_json_error(['message' => 'Permission Denied']);
        }

        $atts = [
            'post_type' => sanitize_text_field($_POST['post_type'] ?? 'post'),
            'posts_per_page' => intval($_POST['posts_per_page'] ?? 10),
            'paged' => intval($_POST['page'] ?? 1),
            'category_name' => sanitize_text_field($_POST['category_name'] ?? ''),
            'year' => sanitize_text_field($_POST['year'] ?? ''),
            'orderby' => sanitize_text_field($_POST['orderby'] ?? 'date'),
            'search' => sanitize_text_field($_POST['search'] ?? ''),
        ];

        // Get template, wrapper class, and no-posts message
        $config = $this->get_template_and_wrapper($atts['post_type']);
        $template = $config['template'];
        $wrapper_class = $config['wrapper_class'];
        $no_item_message = $config['no_item_message'];

        $args = $this->build_query($atts);
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            ob_start();
            $this->render($query, $template, $wrapper_class, $no_item_message);
            $has_more_posts = $query->max_num_pages > $atts['paged'];
            $next_page = $atts['paged'] + 1;

            wp_reset_postdata();

            wp_send_json_success([
                'posts' => ob_get_clean(),
                'has_more_posts' => $has_more_posts,
                'next_page' => $next_page,
            ]);
        } else {
            wp_send_json_error(['message' => '<div class="col text-md text-center mt-5">' . $no_item_message . '</div>']);
        }

        wp_die();
    }

    /**
     * Build dynamic query arguments for WP_Query.
     *
     * @param array $atts Attributes for the query.
     * @return array Query arguments.
     */
    private function build_query($atts)
    {
        $args = [
            'post_type' => $atts['post_type'],
            'posts_per_page' => $atts['posts_per_page'] ?? 10,
            'paged' => intval($atts['paged'] ?? 1),
            'orderby' => (!empty($atts['orderby']) && $atts['orderby'] == 'date-asc' ? 'date' : 'date'),
            'order' => (!empty($atts['orderby']) && $atts['orderby'] == 'date-asc' ? 'ASC' : 'DESC'),
            's' => $atts['search'] ?? '',
            'post_status' => 'publish',
            'suppress_filters' => false,
        ];

        $mappings = $this->get_query_mapping($atts);

        // Add valid keys dynamically
        foreach (['category_name', 'year'] as $key) {
            if (!empty($mappings[$key])) {
                $args[$key] = $mappings[$key];
            }
        }

        // Build tax_query dynamically
        if (!empty($mappings['tax_query'])) {
            foreach ($mappings['tax_query'] as $key => $taxonomy_config) {
                if (!empty($_POST[$key])) {
                    $args['tax_query'][] = array_merge($taxonomy_config, [
                        'terms' => is_array($_POST[$key])
                            ? array_map('sanitize_text_field', $_POST[$key])
                            : sanitize_text_field($_POST[$key]),
                    ]);
                }
            }
        }

        // Build meta_query dynamically
        if (!empty($mappings['meta_query'])) {
            foreach ($mappings['meta_query'] as $key => $meta_config) {
                if (!empty($_POST[$key])) {
                    $args['meta_query'][] = array_merge($meta_config, [
                        'value' => sanitize_text_field($_POST[$key]),
                    ]);
                }
            }
        }

        return $args;
    }

    /**
     * Get query mappings for, unique keys, tax_query and meta_query.
     *
     * @return array
     */
    private function get_query_mapping($atts)
    {
        $mappings = [
            'tax_query' => $this->get_taxonomies(),
            'meta_query' => $this->get_meta_queries(),
        ];

        // Add singular keys dynamically
        foreach (['category_name', 'year'] as $key) {
            if (!empty($atts[$key])) {
                $mappings[$key] = $atts[$key];
            }
        }

        return $mappings;
    }

    private function get_taxonomies()
    {
        return [
            'pub_category' => $this->build_tax_query(CF_TAXO_PUB_CATEGORY),
            'pub_year' => $this->build_tax_query(CF_TAXO_PUB_YEAR),
        ];
    }

    private function build_tax_query($taxonomy, $field = 'term_id')
    {
        return [
            'taxonomy' => $taxonomy,
            'field' => $field, // Dynamically set the field type
            'operator' => 'IN',
        ];
    }

    private function get_meta_queries()
    {
        return [
            'custom_meta_key' => $this->build_meta_query('custom_meta_key'),
        ];
    }

    private function build_meta_query($meta_key)
    {
        return [
            'key' => $meta_key,
            'compare' => '=',
        ];
    }

    /**
     * Get template, wrapper class, and no-posts message based on post type.    
     */
    private function get_template_and_wrapper($post_type)
    {
        switch ($post_type) {
            case 'post':
                return [
                    'template' => 'partials/media-item',
                    'wrapper_class' => 'item-box media-release',
                    'no_item_message' => 'No media release is found with your search criteria! Please try again.',
                ];

            case CF_POST_TYPE_PUBLICATION:
                return [
                    'template' => 'partials/publication-item',
                    'wrapper_class' => 'item-box pub',
                    'no_item_message' => 'No publication report is found with your search criteria! Please try again.',
                ];

            default:
                return [
                    'template' => '',
                    'wrapper_class' => '',
                    'no_item_message' => 'No posts found.',
                ];
        }
    }

    public static function get_years($post_type = 'post')
    {
        $years = [];

        $args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        $posts = get_posts($args);

        foreach ($posts as $post_id) {
            $year = get_the_date('Y', $post_id);
            if (!in_array($year, $years)) {
                $years[] = $year;
            }
        }

        return $years;
    }
}

add_action('init', function () {
    AjaxLoader::get_instance();
});

?>
<?php get_header(); ?>

<div class="container pd-lg">
    <div class="row">
        <div class="col-12">
            <div class="entry-content">
                <?php
                if (have_posts()):
                    while (have_posts()):
                        the_post();
                        the_content();
                    endwhile;
                endif;
                ?>
            </div>
        </div>
    </div>

</div>

<?php get_footer(); ?>