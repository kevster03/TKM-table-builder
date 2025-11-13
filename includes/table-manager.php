<?php
/**
 * Table Manager - Utility Functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Get available filter options
 */
function tkmtb_get_filter_options() {
    return array(
        'grade' => 'Grade',
        'subject' => 'Subject',
        'level' => 'Education Level',
        'category' => 'Category',
        'version' => 'Version'
    );
}

/**
 * Get available columns
 */
function tkmtb_get_available_columns() {
    return array(
        'image' => 'Featured Image',
        'title' => 'Title',
        'excerpt' => 'Excerpt',
        'grade' => 'Grade',
        'subject' => 'Subject',
        'level' => 'Level',
        'type' => 'File Type',
        'category' => 'Category',
        'version' => 'Version',
        'author' => 'Author',
        'date' => 'Date Published',
        'downloads' => 'Download Count',
        'button' => 'View Button'
    );
}

/**
 * Validate table data
 */
function tkmtb_validate_table($data) {
    $errors = array();
    
    if (empty($data['name'])) {
        $errors[] = 'Table name is required';
    }
    
    if (empty($data['columns']) || !is_array($data['columns'])) {
        $errors[] = 'At least one column must be selected';
    }
    
    return $errors;
}

/**
 * Get table stats
 */
function tkmtb_get_table_stats($table_id) {
    $table = tkmtb_get_table($table_id);
    if (!$table) return false;
    
    $args = tkmtb_build_query($table, 1);
    $args['posts_per_page'] = -1;
    $args['fields'] = 'ids';
    
    $query = new WP_Query($args);
    
    return array(
        'total_documents' => $query->found_posts,
        'filters_enabled' => count(array_filter((array)$table['filters'])),
        'columns_count' => count((array)$table['columns'])
    );
}

/**
 * Duplicate table
 */
function tkmtb_duplicate_table($id) {
    $table = tkmtb_get_table($id);
    if (!$table) return false;
    
    $table['name'] = $table['name'] . ' (Copy)';
    unset($table['id']);
    unset($table['created']);
    unset($table['modified']);
    
    return tkmtb_save_table($table);
}

/**
 * Export table configuration
 */
function tkmtb_export_table($id) {
    $table = tkmtb_get_table($id);
    if (!$table) return false;
    
    unset($table['id']);
    unset($table['created']);
    unset($table['modified']);
    
    return json_encode($table, JSON_PRETTY_PRINT);
}

/**
 * Import table configuration
 */
function tkmtb_import_table($json) {
    $data = json_decode($json, true);
    if (!$data) return false;
    
    $errors = tkmtb_validate_table($data);
    if (!empty($errors)) return false;
    
    return tkmtb_save_table($data);
}
