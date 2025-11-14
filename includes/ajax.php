<?php
/**
 * AJAX Handler - For live filtering without page reload
 */

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_tkmtb_filter', 'tkmtb_ajax_filter');
add_action('wp_ajax_nopriv_tkmtb_filter', 'tkmtb_ajax_filter');

function tkmtb_ajax_filter() {
    check_ajax_referer('tkmtb_filter', 'nonce');
    
    $table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;
    if (!$table_id) wp_send_json_error('Invalid table ID');
    
    $table = tkmtb_get_table($table_id);
    if (!$table) wp_send_json_error('Table not found');
    
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    
    // Build query with filters
    $args = tkmtb_build_query($table, $paged);
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        wp_send_json_success(array('html' => '<tr><td colspan="99">No documents found</td></tr>', 'max_pages' => 0));
    }
    
    ob_start();
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $columns = !empty($table['columns']) ? $table['columns'] : tkmtb_get_setting('default_columns', array('image', 'title', 'grade', 'subject', 'type', 'downloads', 'button'));
        $clickable = tkmtb_get_setting('clickable_fields', array('title', 'image'));

        echo '<tr>';
        foreach ($columns as $col) {
            echo '<td>';
            tkmtb_render_cell($col, $post_id, $clickable);
            echo '</td>';
        }
        echo '</tr>';
    }
    $html = ob_get_clean();
    
    wp_reset_postdata();
    
    wp_send_json_success(array('html' => $html, 'max_pages' => $query->max_num_pages));
}
