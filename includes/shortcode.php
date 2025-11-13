<?php
/**
 * Shortcode Handler - Displays tables on frontend
 */

if (!defined('ABSPATH')) exit;

add_shortcode('tkm_table', 'tkmtb_render_table');

function tkmtb_render_table($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts);
    
    if (!$atts['id']) return '<p><strong>Error:</strong> Table ID required. Use: [tkm_table id="1"]</p>';
    
    $table = tkmtb_get_table($atts['id']);
    if (!$table) return '<p><strong>Error:</strong> Table not found.</p>';
    
    // Check cache (cache key based on table ID and page number only, not $_GET)
    $paged = isset($_GET['tpage']) ? intval($_GET['tpage']) : 1;
    $cache_key = 'tkmtb_cache_' . $atts['id'] . '_page_' . $paged;
    if (tkmtb_get_setting('enable_caching') === 'yes') {
        $cached = get_transient($cache_key);
        if ($cached) return $cached;
    }
    
    ob_start();
    
    // Inject CSS variables for styling
    echo '<style>:root{';
    echo '--tkmtb-border-external:' . tkmtb_get_setting('border_external_color', '#b8a5c9') . ';';
    echo '--tkmtb-border-external-size:' . tkmtb_get_setting('border_external_size', 2) . 'px;';
    echo '--tkmtb-border-header:' . tkmtb_get_setting('border_header_color', '#b8a5c9') . ';';
    echo '--tkmtb-border-header-size:' . tkmtb_get_setting('border_header_size', 2) . 'px;';
    echo '--tkmtb-border-hcell:' . tkmtb_get_setting('border_hcell_color', '#e0d4ed') . ';';
    echo '--tkmtb-border-hcell-size:' . tkmtb_get_setting('border_hcell_size', 1) . 'px;';
    echo '--tkmtb-border-vcell:' . tkmtb_get_setting('border_vcell_color', '#e0d4ed') . ';';
    echo '--tkmtb-border-vcell-size:' . tkmtb_get_setting('border_vcell_size', 1) . 'px;';
    echo '--tkmtb-border-bottom:' . tkmtb_get_setting('border_bottom_color', '#b8a5c9') . ';';
    echo '--tkmtb-border-bottom-size:' . tkmtb_get_setting('border_bottom_size', 2) . 'px;';
    echo '--tkmtb-bg-header:' . tkmtb_get_setting('bg_header', '#faf8fc') . ';';
    echo '--tkmtb-bg-cell:' . tkmtb_get_setting('bg_cell', '#ffffff') . ';';
    echo '--tkmtb-bg-hover:' . tkmtb_get_setting('bg_cell_hover', '#f5f5f5') . ';';
    echo '--tkmtb-font-header:' . tkmtb_get_setting('font_header_color', '#2c2c2c') . ';';
    echo '--tkmtb-font-header-size:' . tkmtb_get_setting('font_header_size', 16) . 'px;';
    echo '--tkmtb-font-cell:' . tkmtb_get_setting('font_cell_color', '#555555') . ';';
    echo '--tkmtb-font-cell-size:' . tkmtb_get_setting('font_cell_size', 15) . 'px;';
    echo '--tkmtb-font-link:' . tkmtb_get_setting('font_link_color', '#c92651') . ';';
    echo '--tkmtb-font-link-size:' . tkmtb_get_setting('font_link_size', 15) . 'px;';
    echo '--tkmtb-button-bg:' . tkmtb_get_setting('button_bg', '#c92651') . ';';
    echo '--tkmtb-button-hover:' . tkmtb_get_setting('button_bg_hover', '#a01d3f') . ';';
    echo '--tkmtb-button-font:' . tkmtb_get_setting('button_font_color', '#ffffff') . ';';
    echo '--tkmtb-button-font-size:' . tkmtb_get_setting('button_font_size', 14) . 'px;';
    echo '--tkmtb-dropdown-bg:' . tkmtb_get_setting('dropdown_bg', '#ffffff') . ';';
    echo '--tkmtb-dropdown-font:' . tkmtb_get_setting('dropdown_font', '#2c2c2c') . ';';
    echo '--tkmtb-dropdown-size:' . tkmtb_get_setting('dropdown_size', 15) . 'px;';
    echo '--tkmtb-dropdown-border:' . tkmtb_get_setting('dropdown_border', '#b8a5c9') . ';';
    echo '}</style>';
    
    // Get documents (paged already set above for cache key)
    $args = tkmtb_build_query($table, $paged);
    $query = new WP_Query($args);

    // Show table title if enabled
    if (!empty($table['settings']['show_title']) && $table['settings']['show_title'] === 'yes') {
        echo '<h2 class="tkmtb-title">' . esc_html($table['name']) . '</h2>';
    }

    if (!$query->have_posts()) {
        echo '<p>No documents found matching the criteria.</p>';
        return ob_get_clean();
    }

    // Render table (no frontend filters - admin pre-filters only)
    tkmtb_render_table_html($query, $table);
    
    // Render pagination
    if ($query->max_num_pages > 1) {
        tkmtb_render_pagination($query->max_num_pages, $paged);
    }
    
    wp_reset_postdata();
    
    $output = ob_get_clean();
    
    // Cache output
    if (tkmtb_get_setting('enable_caching') === 'yes') {
        set_transient($cache_key, $output, tkmtb_get_setting('cache_duration', 3600));
    }
    
    return $output;
}

function tkmtb_build_query($table, $paged = 1) {
    $args = array(
        'post_type' => 'teacher_document',
        'posts_per_page' => tkmtb_get_setting('rows_per_page', 10),
        'paged' => $paged,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $meta_query = array('relation' => 'AND');
    $tax_query = array('relation' => 'AND');

    // Apply admin pre-filters (NOT frontend filters - those are removed)
    $prefilters = isset($table['prefilters']) && is_array($table['prefilters']) ? $table['prefilters'] : array();

    // Level filter (meta field)
    if (!empty($prefilters['levels']) && is_array($prefilters['levels'])) {
        $meta_query[] = array(
            'key' => '_tkm_level',
            'value' => $prefilters['levels'],
            'compare' => 'IN'
        );
    }

    // Grade filter (meta field)
    if (!empty($prefilters['grades']) && is_array($prefilters['grades'])) {
        $meta_query[] = array(
            'key' => '_tkm_grade',
            'value' => $prefilters['grades'],
            'compare' => 'IN'
        );
    }

    // Subject filter (meta field)
    if (!empty($prefilters['subjects']) && is_array($prefilters['subjects'])) {
        $meta_query[] = array(
            'key' => '_tkm_subject',
            'value' => $prefilters['subjects'],
            'compare' => 'IN'
        );
    }

    // Version filter (meta field)
    if (!empty($prefilters['versions']) && is_array($prefilters['versions'])) {
        $meta_query[] = array(
            'key' => '_tkm_version',
            'value' => $prefilters['versions'],
            'compare' => 'IN'
        );
    }

    // Category filter (taxonomy)
    if (!empty($prefilters['categories']) && is_array($prefilters['categories'])) {
        $tax_query[] = array(
            'taxonomy' => 'file_category',
            'field' => 'slug',
            'terms' => $prefilters['categories'],
            'operator' => 'IN'
        );
    }

    // Add queries to args
    if (count($meta_query) > 1) {
        $args['meta_query'] = $meta_query;
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    return $args;
}

function tkmtb_render_filters($filters) {
    echo '<div class="tkmtb-filters">';
    
    if (!empty($filters['grade'])) {
        $grades = tkmtb_get_unique_meta('_tkm_grade');
        if (!empty($grades)) {
            echo '<select name="tkmtb_grade" class="tkmtb-filter">';
            echo '<option value="">All Grades</option>';
            foreach ($grades as $grade) {
                $selected = (isset($_GET['tkmtb_grade']) && $_GET['tkmtb_grade'] === $grade) ? ' selected' : '';
                echo '<option value="' . esc_attr($grade) . '"' . $selected . '>' . esc_html($grade) . '</option>';
            }
            echo '</select>';
        }
    }
    
    if (!empty($filters['subject'])) {
        $subjects = tkmtb_get_unique_meta('_tkm_subject');
        if (!empty($subjects)) {
            echo '<select name="tkmtb_subject" class="tkmtb-filter">';
            echo '<option value="">All Subjects</option>';
            foreach ($subjects as $subject) {
                $selected = (isset($_GET['tkmtb_subject']) && $_GET['tkmtb_subject'] === $subject) ? ' selected' : '';
                echo '<option value="' . esc_attr($subject) . '"' . $selected . '>' . esc_html($subject) . '</option>';
            }
            echo '</select>';
        }
    }
    
    if (!empty($filters['level'])) {
        $levels = tkm_get_levels();
        echo '<select name="tkmtb_level" class="tkmtb-filter">';
        echo '<option value="">All Levels</option>';
        foreach ($levels as $key => $data) {
            $selected = (isset($_GET['tkmtb_level']) && $_GET['tkmtb_level'] === $key) ? ' selected' : '';
            echo '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($data['label']) . '</option>';
        }
        echo '</select>';
    }
    
    if (!empty($filters['category'])) {
        $categories = get_terms(array('taxonomy' => 'file_category', 'hide_empty' => true));
        if (!empty($categories) && !is_wp_error($categories)) {
            echo '<select name="tkmtb_category" class="tkmtb-filter">';
            echo '<option value="">All Categories</option>';
            foreach ($categories as $cat) {
                $selected = (isset($_GET['tkmtb_category']) && $_GET['tkmtb_category'] === $cat->slug) ? ' selected' : '';
                echo '<option value="' . esc_attr($cat->slug) . '"' . $selected . '>' . esc_html($cat->name) . '</option>';
            }
            echo '</select>';
        }
    }
    
    if (!empty($filters['version'])) {
        $versions = tkmtb_get_unique_meta('_tkm_version');
        if (!empty($versions)) {
            echo '<select name="tkmtb_version" class="tkmtb-filter">';
            echo '<option value="">All Versions</option>';
            foreach ($versions as $version) {
                $selected = (isset($_GET['tkmtb_version']) && $_GET['tkmtb_version'] === $version) ? ' selected' : '';
                echo '<option value="' . esc_attr($version) . '"' . $selected . '>' . esc_html($version) . '</option>';
            }
            echo '</select>';
        }
    }
    
    echo '<button type="button" class="tkmtb-filter-btn">🔍 Filter</button>';
    echo '<button type="button" class="tkmtb-reset-btn">↻ Reset</button>';
    echo '</div>';
}

function tkmtb_render_table_html($query, $table) {
    $columns = !empty($table['columns']) ? $table['columns'] : tkmtb_get_setting('default_columns', array());
    $clickable = tkmtb_get_setting('clickable_fields', array());
    $lazy = tkmtb_get_setting('lazy_load') === 'yes';
    
    echo '<div class="tkmtb-wrapper"><table class="tkmtb-table"><thead><tr>';
    
    // Headers
    foreach ($columns as $col) {
        echo '<th>' . esc_html(tkmtb_get_column_label($col)) . '</th>';
    }
    echo '</tr></thead><tbody>';
    
    // Rows
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        
        echo '<tr>';
        foreach ($columns as $col) {
            echo '<td>';
            tkmtb_render_cell($col, $post_id, $clickable, $lazy);
            echo '</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody></table></div>';
}

function tkmtb_render_cell($col, $post_id, $clickable, $lazy) {
    $link_start = in_array($col, $clickable) ? '<a href="' . get_permalink($post_id) . '" class="tkmtb-link">' : '';
    $link_end = in_array($col, $clickable) ? '</a>' : '';
    
    switch ($col) {
        case 'image':
            $img = tkm_get_document_image($post_id, 'thumbnail');
            $loading = $lazy ? 'loading="lazy"' : '';
            echo $link_start . '<img src="' . esc_url($img) . '" alt="" class="tkmtb-img" ' . $loading . '>' . $link_end;
            break;
        case 'title':
            echo $link_start . '<strong>' . get_the_title() . '</strong>' . $link_end;
            break;
        case 'excerpt':
            echo wp_trim_words(get_the_excerpt(), 15);
            break;
        case 'grade':
            echo esc_html(get_post_meta($post_id, '_tkm_grade', true));
            break;
        case 'subject':
            echo esc_html(get_post_meta($post_id, '_tkm_subject', true));
            break;
        case 'level':
            $level = get_post_meta($post_id, '_tkm_level', true);
            $levels = tkm_get_levels();
            echo isset($levels[$level]) ? esc_html($levels[$level]['label']) : esc_html($level);
            break;
        case 'type':
            $ext = get_post_meta($post_id, '_tkm_file_ext', true);
            echo esc_html(strtoupper($ext));
            break;
        case 'category':
            $cats = wp_get_post_terms($post_id, 'file_category', array('fields' => 'names'));
            echo !empty($cats) ? esc_html($cats[0]) : '-';
            break;
        case 'version':
            echo esc_html(get_post_meta($post_id, '_tkm_version', true));
            break;
        case 'author':
            echo '<a href="' . get_author_posts_url(get_the_author_meta('ID')) . '">' . get_the_author() . '</a>';
            break;
        case 'date':
            echo get_the_date();
            break;
        case 'downloads':
            echo number_format(intval(get_post_meta($post_id, '_tkm_download_count', true)));
            break;
        case 'button':
            $btn_text = tkmtb_get_setting('button_text', 'View Details');
            echo '<a href="' . get_permalink($post_id) . '" class="tkmtb-btn">' . esc_html($btn_text) . '</a>';
            break;
    }
}

function tkmtb_render_pagination($max_pages, $current) {
    $type = tkmtb_get_setting('pagination_type', 'numbers');
    
    echo '<div class="tkmtb-pagination">';
    
    if ($type === 'numbers') {
        for ($i = 1; $i <= $max_pages; $i++) {
            $class = ($i === $current) ? 'tkmtb-page current' : 'tkmtb-page';
            $url = add_query_arg('tpage', $i);
            echo '<a href="' . esc_url($url) . '" class="' . $class . '">' . $i . '</a>';
        }
    } elseif ($type === 'load_more' && $current < $max_pages) {
        echo '<button class="tkmtb-load-more" data-page="' . ($current + 1) . '">Load More</button>';
    }
    
    echo '</div>';
}

function tkmtb_get_unique_meta($key) {
    global $wpdb;
    $results = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' ORDER BY meta_value",
        $key
    ));
    return $results;
}

function tkmtb_get_column_label($col) {
    $labels = array(
        'image' => 'Image', 'title' => 'Title', 'excerpt' => 'Excerpt',
        'grade' => 'Grade', 'subject' => 'Subject', 'level' => 'Level',
        'type' => 'Type', 'category' => 'Category', 'version' => 'Version',
        'author' => 'Author', 'date' => 'Date', 'downloads' => 'Downloads',
        'button' => 'Action'
    );
    return isset($labels[$col]) ? $labels[$col] : ucfirst($col);
}
