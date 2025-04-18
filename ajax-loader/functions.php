<?php

add_filter('the_content', 'test_media');
function test_media($content) {
    if(is_page('stories') && isset($_GET['test'])) { 
        $content = sc_load_the_content(['post_type' => 'post', 'posts_per_page' => 1]);
        return $content;
    } else if(is_page('publications') && isset($_GET['test'])) { 
        $content = sc_load_the_content(['post_type' => CF_POST_TYPE_PUBLICATION, 'posts_per_page' => 1]);
        return $content;
    }
    return $content;
}

function sc_load_the_content( $atts ) {
    $atts = shortcode_atts( array(
        'post_type'      => 'post',    
        'posts_per_page' => 10,
    ), $atts, 'posts' );
  
    switch ($atts['post_type']) {
        case 'post':
            $atts['category_name'] = 'news-media,stories';
            return load_items(
              $atts,               
              [
                'action' => 'load_more_items',
                'form' => '#filter-form',
              ],               
            );
        case CF_POST_TYPE_PUBLICATION:            
            return load_items(
              $atts,               
              [
                'action' => 'load_more_items',
                'form' => '#filter-form',
                'secondaryForm' => '#filter-pub-category',
              ],              
            );
        default:
            return '<p>No content available.</p>';
    }
 
} 

function load_items($atts, $js_params) {
   
    $args = build_dynamic_query_args($atts);
    $query = new WP_Query($args);
  
    // Get template, wrapper class, and no-posts message
    $config = get_template_and_wrapper($atts['post_type']);
    $template = $config['template'];
    $wrapper_class = $config['wrapper_class'];
    $no_posts_message = $config['no_posts_message'];
  
    ob_start();

    // Output the appropriate filter template
    get_template_part('partials/filters/' . ($atts['post_type'] === CF_POST_TYPE_PUBLICATION ? 'publication' : 'media'));
    ?>

    <div class="container">
        <div id="tobefilled" class="row">
            <?php render_items($query, $template, $wrapper_class, $no_posts_message); ?>
        </div>

        <?php if ($query->max_num_pages > 1) : ?>
            <div class="text-center mb-4">
                <button
                    id="load-more"
                    data-page="2"
                    data-type="<?php echo esc_attr($atts['post_type']); ?>"
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
                    category: '',
                    year: '',
                    orderby: '',
                },
            });
        });
    </script>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

function render_items($query, $template, $wrapper_class = 'item-box', $no_posts_message = 'No posts found.') {
    if ($query->have_posts()) :
        $counter = 0;
        while ($query->have_posts()) : $query->the_post();
            $counter++;
            $class = $counter <= 6 ? 'col-lg-4 col-md-6 col-12' : 'col-lg-3 col-md-6 col-12';
            ?>
            <div class="<?php echo esc_attr($wrapper_class . ' ' . $class); ?>">
                <?php get_template_part($template); ?>
            </div>
            <?php
        endwhile;
    else :
        ?>
        <p><?php echo esc_html($no_posts_message); ?></p>
        <?php
    endif;
}

/**
 * Handle AJAX requests for loading more items dynamically.
 */
add_action('wp_ajax_load_more_items', 'ajax_load_more_items');
add_action('wp_ajax_nopriv_load_more_items', 'ajax_load_more_items');

function ajax_load_more_items() {
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
    $config = get_template_and_wrapper($atts['post_type']);
    $template = $config['template'];
    $wrapper_class = $config['wrapper_class'];
    $no_posts_message = $config['no_posts_message'];

    $args = build_dynamic_query_args($atts);      
  
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        ob_start();
        render_items($query, $template, $wrapper_class, $no_posts_message);
        $has_more_posts = $query->max_num_pages > $atts['paged'];        
        $next_page = $atts['paged'] + 1;
    
        wp_reset_postdata();     
  
        wp_send_json_success(array(
            'posts' => ob_get_clean(),
            'has_more_posts' => $has_more_posts,
            'next_page' => $next_page, // The next page number to load
        ));       
    } else {
        wp_send_json_error(['message' => '<div class="col text-md text-center mt-5">' . $no_posts_message . '</div>']);
    }

    wp_die();
}

function build_dynamic_query_args( $atts = array(), $paged = 1 ) {
    // Set the default query args
    $args = array(
        'post_type'      => $atts['post_type'],
        'posts_per_page' => $atts['posts_per_page'] ?? 10,
        'paged'          => intval($atts['paged'] ?? 1), // This is dynamic for pagination
        'category_name'  => $atts['category_name'] ?? '', // Fixed categories
        'year'           => $atts['year'] ?? '', // Fixed categories
        'orderby'        => ($atts['orderby'] == 'date-asc' ? 'date' : 'date'),
        'order'          => ($atts['orderby'] == 'date-asc' ? 'ASC' : 'DESC'),
        's'              => isset($atts['search']) ? $atts['search'] : '',
        'post_status'    => 'publish',
        'suppress_filters' => false,
    );

    $mappings = get_query_mapping();

    // Build tax_query dynamically
    if (!empty($mappings['tax_query'])) {
        foreach ($mappings['tax_query'] as $key => $taxonomy_config) {
            if (!empty($_POST[$key])) {
                $args['tax_query'][] = array_merge($taxonomy_config, [
                    'terms' => is_array($_POST[$key]) ? array_map('sanitize_text_field', $_POST[$key]) : sanitize_text_field($_POST[$key]),
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

function get_template_and_wrapper($post_type) {
    switch ($post_type) {
        case 'post':
            return [
                'template' => 'partials/media-item',
                'wrapper_class' => 'item-box media-release',
                'no_posts_message' => 'No media release is found with your search criteria! Please try again.',
            ];

        case CF_POST_TYPE_PUBLICATION:
            return [
                'template' => 'partials/publication-item',
                'wrapper_class' => 'item-box pub',
                'no_posts_message' => 'No publication report is found with your search criteria! Please try again.',
            ];

        default:
            return [
                'template' => '',
                'wrapper_class' => '',
                'no_posts_message' => 'No posts found.',
            ];
    }
}

function get_query_mapping() {
    return [
        'tax_query' => [
            'pub_category' => [
                'taxonomy' => CF_TAXO_PUB_CATEGORY,
                'field'    => 'term_id',
                'operator' => 'IN',
            ],
            'pub_year' => [
                'taxonomy' => CF_TAXO_PUB_YEAR,
                'field'    => 'term_id',
                'operator' => 'IN',
            ],
        ],
        'meta_query' => [
            'custom_meta_key' => [
                'key'     => 'custom_meta_key',
                'compare' => '=',
            ],
        ],
    ];
}
