<?php
/**
 * Database Setup
 */

if (!defined('ABSPATH')) exit;

function tkmtb_create_tables() {
    global $wpdb;
    $table = $wpdb->prefix . 'tkmtb_tables';
    $charset = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(200) NOT NULL,
        filters longtext,
        columns longtext,
        settings longtext,
        created datetime DEFAULT CURRENT_TIMESTAMP,
        modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function tkmtb_set_defaults() {
    $defaults = array(
        // Default columns
        'default_columns' => array('image', 'title', 'grade', 'subject', 'type', 'downloads', 'button'),
        
        // Clickable fields
        'clickable_fields' => array('title', 'image'),
        
        // Pagination
        'rows_per_page' => 10,
        'pagination_type' => 'numbers', // numbers, load_more, infinite
        
        // Performance
        'enable_caching' => 'yes',
        'cache_duration' => 3600,
        'lazy_load' => 'yes',
        
        // Colors - Borders
        'border_external_color' => '#b8a5c9',
        'border_external_size' => 2,
        'border_header_color' => '#b8a5c9',
        'border_header_size' => 2,
        'border_hcell_color' => '#e0d4ed',
        'border_hcell_size' => 1,
        'border_vcell_color' => '#e0d4ed',
        'border_vcell_size' => 1,
        'border_bottom_color' => '#b8a5c9',
        'border_bottom_size' => 2,
        
        // Colors - Backgrounds
        'bg_header' => '#faf8fc',
        'bg_cell' => '#ffffff',
        'bg_cell_hover' => '#f5f5f5',
        
        // Colors - Fonts
        'font_header_color' => '#2c2c2c',
        'font_header_size' => 16,
        'font_cell_color' => '#555555',
        'font_cell_size' => 15,
        'font_link_color' => '#c92651',
        'font_link_size' => 15,
        
        // Button
        'button_bg' => '#c92651',
        'button_bg_hover' => '#a01d3f',
        'button_font_color' => '#ffffff',
        'button_font_size' => 14,
        'button_text' => 'View Details',
        
        // Dropdown/Filter
        'dropdown_bg' => '#ffffff',
        'dropdown_font' => '#2c2c2c',
        'dropdown_size' => 15,
        'dropdown_border' => '#b8a5c9'
    );
    
    foreach ($defaults as $key => $value) {
        $option = 'tkmtb_' . $key;
        if (get_option($option) === false) {
            update_option($option, $value);
        }
    }
}

function tkmtb_get_table($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'tkmtb_tables';
    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
    
    if ($result) {
        $result['filters'] = maybe_unserialize($result['filters']);
        $result['columns'] = maybe_unserialize($result['columns']);
        $result['settings'] = maybe_unserialize($result['settings']);
    }
    
    return $result;
}

function tkmtb_save_table($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'tkmtb_tables';
    
    $save_data = array(
        'name' => sanitize_text_field($data['name']),
        'filters' => maybe_serialize($data['filters']),
        'columns' => maybe_serialize($data['columns']),
        'settings' => maybe_serialize($data['settings'])
    );
    
    if (isset($data['id']) && $data['id'] > 0) {
        $wpdb->update($table, $save_data, array('id' => intval($data['id'])));
        return intval($data['id']);
    } else {
        $wpdb->insert($table, $save_data);
        return $wpdb->insert_id;
    }
}

function tkmtb_delete_table($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'tkmtb_tables';
    return $wpdb->delete($table, array('id' => intval($id)));
}

function tkmtb_get_all_tables() {
    global $wpdb;
    $table = $wpdb->prefix . 'tkmtb_tables';
    return $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC", ARRAY_A);
}
