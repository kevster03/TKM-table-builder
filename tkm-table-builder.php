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
// Runs at init to ensure post types are registered (parent plugin registers at init priority 0)
add_action('init', 'tkmtb_check_parent', 11);
function tkmtb_check_parent() {
    // Check if teacher_document post type exists
    if (!post_type_exists('teacher_document')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p><strong>Table Builder</strong> requires <strong>TeachersKE File Manager</strong> plugin to be active.</p></div>';
        });
        return;
    }

    // Check if critical functions exist
    if (!function_exists('tkm_get_levels') || !function_exists('tkm_get_subjects_for_level')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p><strong>Table Builder</strong> detected an incompatible version of <strong>TeachersKE File Manager</strong>. Please update to the latest version.</p></div>';
        });
        return;
    }
}

// Include files
require_once TKMTB_DIR . 'includes/database.php';
require_once TKMTB_DIR . 'includes/table-manager.php';
require_once TKMTB_DIR . 'includes/shortcode.php';
require_once TKMTB_DIR . 'includes/ajax.php';
require_once TKMTB_DIR . 'admin/settings.php';
require_once TKMTB_DIR . 'admin/tables-list.php';

// Compatibility functions - only load if not already defined by File Manager
// These should NEVER be needed if parent plugin is active, but provide fallbacks just in case
add_action('init', 'tkmtb_load_compatibility', 12);
function tkmtb_load_compatibility() {
    // Load tkm_get_levels if not available (MUST match parent plugin structure exactly)
    if (!function_exists('tkm_get_levels')) {
        function tkm_get_levels() {
            return array(
                'early_years' => array(
                    'label' => 'Early Years / Pre-Primary',
                    'grades' => array('Playgroup', 'PP1', 'PP2')
                ),
                'lower_primary' => array(
                    'label' => 'Lower Primary',
                    'grades' => array('Grade 1', 'Grade 2', 'Grade 3')
                ),
                'upper_primary' => array(
                    'label' => 'Upper Primary',
                    'grades' => array('Grade 4', 'Grade 5', 'Grade 6')
                ),
                'junior_secondary' => array(
                    'label' => 'Junior Secondary',
                    'grades' => array('Grade 7', 'Grade 8', 'Grade 9')
                ),
                'senior_secondary' => array(
                    'label' => 'Senior Secondary',
                    'grades' => array('Grade 10', 'Grade 11', 'Grade 12')
                )
            );
        }
    }

    // Load tkm_get_subjects_for_level if not available
    if (!function_exists('tkm_get_subjects_for_level')) {
        function tkm_get_subjects_for_level($level) {
            $subjects = get_option('tkm_subjects_by_level', array());
            return isset($subjects[$level]) ? $subjects[$level] : array();
        }
    }

    // Load tkm_get_document_image if not available
    if (!function_exists('tkm_get_document_image')) {
        function tkm_get_document_image($post_id, $size = 'thumbnail') {
            if (has_post_thumbnail($post_id)) {
                $img = get_the_post_thumbnail_url($post_id, $size);
                if ($img) return $img;
            }
            // Fallback placeholder (matches theme colors)
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

    // Pass level/grade/subject data to JavaScript for dynamic dropdowns
    if (function_exists('tkm_get_levels')) {
        $levels_data = tkm_get_levels();
        $subjects_data = array();

        foreach ($levels_data as $key => $level) {
            $subjects_data[$key] = tkm_get_subjects_for_level($key);
        }

        wp_localize_script('tkmtb-admin', 'tkmtbLevels', array(
            'levels' => $levels_data,
            'subjects' => $subjects_data
        ));
    }
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
    return get_option('tkmtb_' . $key, $default);
}
