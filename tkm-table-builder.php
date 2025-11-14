<?php
/**
 * Plugin Name: TeachersKE Table Builder
 * Description: Lightning-fast filterable document tables. Mobile-first, SEO-optimized, beautifully designed.
 * Version: 1.0.3
 * Author: TeachersKE
 * Text Domain: tkm-tables
 */

if (!defined('ABSPATH')) exit;

define('TKMTB_VERSION', '1.0.0');
define('TKMTB_DIR', plugin_dir_path(__FILE__));
define('TKMTB_URL', plugin_dir_url(__FILE__));

// Check parent plugin - compatible with all versions
// Runs after all plugins are loaded to ensure functions are available
add_action('plugins_loaded', 'tkmtb_check_parent', 20);
function tkmtb_check_parent() {
    // Check if teacher_document post type exists
    if (!post_type_exists('teacher_document')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>Table Builder</strong> requires <strong>TeachersKE File Manager</strong> plugin to be active.</p></div>';
        });
        // Don't deactivate - just show warning
        return;
    }
    
    // Everything is fine - plugin is compatible
}

// Include files
require_once TKMTB_DIR . 'includes/database.php';
require_once TKMTB_DIR . 'includes/table-manager.php';
require_once TKMTB_DIR . 'includes/shortcode.php';
require_once TKMTB_DIR . 'includes/ajax.php';
require_once TKMTB_DIR . 'admin/settings.php';
require_once TKMTB_DIR . 'admin/tables-list.php';

// Compatibility functions - only load if not already defined by File Manager
add_action('plugins_loaded', 'tkmtb_load_compatibility', 30);
function tkmtb_load_compatibility() {
    // Load tkm_get_levels if not available
    if (!function_exists('tkm_get_levels')) {
        function tkm_get_levels() {
            return array(
                'early_years' => array('label' => 'Early Years', 'grades' => array('PP1', 'PP2')),
                'primary' => array('label' => 'Primary', 'grades' => array('Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8')),
                'secondary' => array('label' => 'Secondary', 'grades' => array('Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'))
            );
        }
    }

    // Load tkm_get_document_image if not available
    if (!function_exists('tkm_get_document_image')) {
        function tkm_get_document_image($post_id, $size = 'thumbnail') {
            if (has_post_thumbnail($post_id)) {
                $img = get_the_post_thumbnail_url($post_id, $size);
                if ($img) return $img;
            }
            // Fallback placeholder
            return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="60" height="60"%3E%3Crect fill="%23e0d4ed" width="60" height="60"/%3E%3Ctext x="50%25" y="50%25" font-size="24" fill="%23b8a5c9" text-anchor="middle" dy=".3em"%3E📄%3C/text%3E%3C/svg%3E';
        }
    }
}

// Activation
register_activation_hook(__FILE__, 'tkmtb_activate');
function tkmtb_activate() {
    tkmtb_create_tables();
    tkmtb_set_defaults();
    flush_rewrite_rules();
}

// Admin menu
add_action('admin_menu', 'tkmtb_menu');
function tkmtb_menu() {
    add_menu_page('Table Builder', 'Table Builder', 'manage_options', 'tkmtb-tables', 'tkmtb_tables_page', 'dashicons-grid-view', 30);
    add_submenu_page('tkmtb-tables', 'All Tables', 'All Tables', 'manage_options', 'tkmtb-tables', 'tkmtb_tables_page');
    add_submenu_page('tkmtb-tables', 'Add New', 'Add New', 'manage_options', 'tkmtb-new', 'tkmtb_new_table_page');
    add_submenu_page('tkmtb-tables', 'Settings', 'Settings', 'manage_options', 'tkmtb-settings', 'tkmtb_settings_page');
}

// Enqueue admin assets
add_action('admin_enqueue_scripts', 'tkmtb_admin_assets');
function tkmtb_admin_assets($hook) {
    if (strpos($hook, 'tkmtb') === false) return;
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('tkmtb-admin', TKMTB_URL . 'assets/css/admin.css', array(), TKMTB_VERSION);
    wp_enqueue_script('tkmtb-admin', TKMTB_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), TKMTB_VERSION, true);
}

// Enqueue frontend assets
add_action('wp_enqueue_scripts', 'tkmtb_frontend_assets');
function tkmtb_frontend_assets() {
    if (!tkmtb_has_shortcode()) return;
    wp_enqueue_style('tkmtb-frontend', TKMTB_URL . 'assets/css/frontend.css', array(), TKMTB_VERSION);
    wp_enqueue_script('tkmtb-frontend', TKMTB_URL . 'assets/js/frontend.js', array(), TKMTB_VERSION, true);
    wp_localize_script('tkmtb-frontend', 'tkmtbData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tkmtb_filter')
    ));
}

// Check if page has shortcode
function tkmtb_has_shortcode() {
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'tkm_table')) {
        return true;
    }
    return false;
}

// Helper: Get setting
function tkmtb_get_setting($key, $default = '') {
    $value = get_option('tkmtb_' . $key, $default);

    // Ensure array values are properly returned
    if (in_array($key, array('default_columns', 'clickable_fields')) && !is_array($value)) {
        // If it's a string, try to convert to array
        if (is_string($value) && !empty($value)) {
            $value = array_map('trim', explode(',', $value));
        } else {
            $value = $default;
        }
    }

    return $value;
}
