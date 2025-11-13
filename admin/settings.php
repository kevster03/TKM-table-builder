<?php
/**
 * Settings Page - Complete Visual Customization
 */

if (!defined('ABSPATH')) exit;

function tkmtb_settings_page() {
    // Save settings
    if (isset($_POST['tkmtb_save_settings']) && check_admin_referer('tkmtb_settings')) {
        tkmtb_save_settings();
        echo '<div class="notice notice-success"><p><strong>✅ Settings saved successfully!</strong></p></div>';
    }
    
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    ?>
    <div class="wrap">
        <h1>⚙️ Table Builder Settings</h1>
        
        <h2 class="nav-tab-wrapper">
            <a href="?page=tkmtb-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=tkmtb-settings&tab=appearance" class="nav-tab <?php echo $active_tab === 'appearance' ? 'nav-tab-active' : ''; ?>">Appearance</a>
            <a href="?page=tkmtb-settings&tab=performance" class="nav-tab <?php echo $active_tab === 'performance' ? 'nav-tab-active' : ''; ?>">Performance</a>
        </h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('tkmtb_settings'); ?>
            
            <?php if ($active_tab === 'general'): ?>
                <table class="form-table">
                    <tr>
                        <th colspan="2"><h2>Default Columns</h2></th>
                    </tr>
                    <tr>
                        <th>Default Columns</th>
                        <td>
                            <input type="text" name="tkmtb_default_columns_text" value="<?php echo esc_attr(implode(',', tkmtb_get_setting('default_columns', array()))); ?>" class="large-text" placeholder="image,title,grade,subject,type,downloads,button">
                            <p class="description">Comma-separated. Available: image, title, excerpt, grade, subject, level, type, category, version, author, date, downloads, button</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>Clickable Fields (Post Links)</h2></th>
                    </tr>
                    <tr>
                        <th>Clickable Fields</th>
                        <td>
                            <input type="text" name="tkmtb_clickable_fields_text" value="<?php echo esc_attr(implode(',', tkmtb_get_setting('clickable_fields', array()))); ?>" class="large-text" placeholder="title,image">
                            <p class="description">Which columns should link to the document page. Comma-separated: title, image, categories, tags, author</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>Pagination</h2></th>
                    </tr>
                    <tr>
                        <th>Rows Per Page</th>
                        <td>
                            <input type="number" name="tkmtb_rows_per_page" value="<?php echo esc_attr(tkmtb_get_setting('rows_per_page', 10)); ?>" min="5" max="100" style="width:80px">
                            <p class="description">Number of rows before pagination (default: 10)</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Pagination Type</th>
                        <td>
                            <select name="tkmtb_pagination_type">
                                <option value="numbers" <?php selected(tkmtb_get_setting('pagination_type'), 'numbers'); ?>>Page Numbers</option>
                                <option value="load_more" <?php selected(tkmtb_get_setting('pagination_type'), 'load_more'); ?>>Load More Button</option>
                                <option value="infinite" <?php selected(tkmtb_get_setting('pagination_type'), 'infinite'); ?>>Infinite Scroll</option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>Button Settings</h2></th>
                    </tr>
                    <tr>
                        <th>Button Text</th>
                        <td>
                            <input type="text" name="tkmtb_button_text" value="<?php echo esc_attr(tkmtb_get_setting('button_text', 'View Details')); ?>" class="regular-text">
                            <p class="description">Text for the view button in tables</p>
                        </td>
                    </tr>
                </table>
                
            <?php elseif ($active_tab === 'appearance'): ?>
                <table class="form-table">
                    <tr>
                        <th colspan="2"><h2>🎨 Borders</h2></th>
                    </tr>
                    <tr>
                        <th>External Border</th>
                        <td>
                            <input type="text" name="tkmtb_border_external_color" value="<?php echo esc_attr(tkmtb_get_setting('border_external_color', '#b8a5c9')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_border_external_size" value="<?php echo esc_attr(tkmtb_get_setting('border_external_size', 2)); ?>" min="0" max="10" style="width:60px"> px
                        </td>
                    </tr>
                    <tr>
                        <th>Header Border</th>
                        <td>
                            <input type="text" name="tkmtb_border_header_color" value="<?php echo esc_attr(tkmtb_get_setting('border_header_color', '#b8a5c9')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_border_header_size" value="<?php echo esc_attr(tkmtb_get_setting('border_header_size', 2)); ?>" min="0" max="10" style="width:60px"> px
                        </td>
                    </tr>
                    <tr>
                        <th>Horizontal Cell Border</th>
                        <td>
                            <input type="text" name="tkmtb_border_hcell_color" value="<?php echo esc_attr(tkmtb_get_setting('border_hcell_color', '#e0d4ed')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_border_hcell_size" value="<?php echo esc_attr(tkmtb_get_setting('border_hcell_size', 1)); ?>" min="0" max="10" style="width:60px"> px
                        </td>
                    </tr>
                    <tr>
                        <th>Vertical Cell Border</th>
                        <td>
                            <input type="text" name="tkmtb_border_vcell_color" value="<?php echo esc_attr(tkmtb_get_setting('border_vcell_color', '#e0d4ed')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_border_vcell_size" value="<?php echo esc_attr(tkmtb_get_setting('border_vcell_size', 1)); ?>" min="0" max="10" style="width:60px"> px
                        </td>
                    </tr>
                    <tr>
                        <th>Bottom Border</th>
                        <td>
                            <input type="text" name="tkmtb_border_bottom_color" value="<?php echo esc_attr(tkmtb_get_setting('border_bottom_color', '#b8a5c9')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_border_bottom_size" value="<?php echo esc_attr(tkmtb_get_setting('border_bottom_size', 2)); ?>" min="0" max="10" style="width:60px"> px
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>🎨 Background Colors</h2></th>
                    </tr>
                    <tr>
                        <th>Header Background</th>
                        <td>
                            <input type="text" name="tkmtb_bg_header" value="<?php echo esc_attr(tkmtb_get_setting('bg_header', '#faf8fc')); ?>" class="tkmtb-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th>Cell Background</th>
                        <td>
                            <input type="text" name="tkmtb_bg_cell" value="<?php echo esc_attr(tkmtb_get_setting('bg_cell', '#ffffff')); ?>" class="tkmtb-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th>Cell Hover Background</th>
                        <td>
                            <input type="text" name="tkmtb_bg_cell_hover" value="<?php echo esc_attr(tkmtb_get_setting('bg_cell_hover', '#f5f5f5')); ?>" class="tkmtb-color-picker">
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>🎨 Fonts</h2></th>
                    </tr>
                    <tr>
                        <th>Header Font</th>
                        <td>
                            <input type="text" name="tkmtb_font_header_color" value="<?php echo esc_attr(tkmtb_get_setting('font_header_color', '#2c2c2c')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_font_header_size" value="<?php echo esc_attr(tkmtb_get_setting('font_header_size', 16)); ?>" min="10" max="30" style="width:60px"> px
                        </td>
                    </tr>
                    <tr>
                        <th>Cell Font</th>
                        <td>
                            <input type="text" name="tkmtb_font_cell_color" value="<?php echo esc_attr(tkmtb_get_setting('font_cell_color', '#555555')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_font_cell_size" value="<?php echo esc_attr(tkmtb_get_setting('font_cell_size', 15)); ?>" min="10" max="30" style="width:60px"> px
                        </td>
                    </tr>
                    <tr>
                        <th>Hyperlink Font</th>
                        <td>
                            <input type="text" name="tkmtb_font_link_color" value="<?php echo esc_attr(tkmtb_get_setting('font_link_color', '#c92651')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_font_link_size" value="<?php echo esc_attr(tkmtb_get_setting('font_link_size', 15)); ?>" min="10" max="30" style="width:60px"> px
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>🎨 Button Backgrounds</h2></th>
                    </tr>
                    <tr>
                        <th>Main Button</th>
                        <td>
                            <input type="text" name="tkmtb_button_bg" value="<?php echo esc_attr(tkmtb_get_setting('button_bg', '#c92651')); ?>" class="tkmtb-color-picker">
                            <span style="margin-left:20px">Font:</span>
                            <input type="text" name="tkmtb_button_font_color" value="<?php echo esc_attr(tkmtb_get_setting('button_font_color', '#ffffff')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_button_font_size" value="<?php echo esc_attr(tkmtb_get_setting('button_font_size', 14)); ?>" min="10" max="20" style="width:60px"> px
                        </td>
                    </tr>
                    <tr>
                        <th>Main Button Hover</th>
                        <td>
                            <input type="text" name="tkmtb_button_bg_hover" value="<?php echo esc_attr(tkmtb_get_setting('button_bg_hover', '#a01d3f')); ?>" class="tkmtb-color-picker">
                        </td>
                    </tr>
                    
                    <tr>
                        <th colspan="2"><h2>🎨 Dropdowns (Filters)</h2></th>
                    </tr>
                    <tr>
                        <th>Dropdown Style</th>
                        <td>
                            Background: <input type="text" name="tkmtb_dropdown_bg" value="<?php echo esc_attr(tkmtb_get_setting('dropdown_bg', '#ffffff')); ?>" class="tkmtb-color-picker">
                            <br><br>
                            Font: <input type="text" name="tkmtb_dropdown_font" value="<?php echo esc_attr(tkmtb_get_setting('dropdown_font', '#2c2c2c')); ?>" class="tkmtb-color-picker">
                            <input type="number" name="tkmtb_dropdown_size" value="<?php echo esc_attr(tkmtb_get_setting('dropdown_size', 15)); ?>" min="10" max="20" style="width:60px"> px
                            <br><br>
                            Border: <input type="text" name="tkmtb_dropdown_border" value="<?php echo esc_attr(tkmtb_get_setting('dropdown_border', '#b8a5c9')); ?>" class="tkmtb-color-picker">
                        </td>
                    </tr>
                </table>
                
            <?php elseif ($active_tab === 'performance'): ?>
                <table class="form-table">
                    <tr>
                        <th colspan="2"><h2>⚡ Performance Settings</h2></th>
                    </tr>
                    <tr>
                        <th>Enable Caching</th>
                        <td>
                            <label>
                                <input type="checkbox" name="tkmtb_enable_caching" value="yes" <?php checked(tkmtb_get_setting('enable_caching'), 'yes'); ?>>
                                Cache table queries for faster loading
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>Cache Duration</th>
                        <td>
                            <input type="number" name="tkmtb_cache_duration" value="<?php echo esc_attr(tkmtb_get_setting('cache_duration', 3600)); ?>" min="300" max="86400" style="width:100px"> seconds
                            <p class="description">How long to cache results (default: 3600 = 1 hour)</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Lazy Load Images</th>
                        <td>
                            <label>
                                <input type="checkbox" name="tkmtb_lazy_load" value="yes" <?php checked(tkmtb_get_setting('lazy_load'), 'yes'); ?>>
                                Enable lazy loading for table images
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th>Clear Cache</th>
                        <td>
                            <a href="?page=tkmtb-settings&tab=performance&clear_cache=1" class="button" onclick="return confirm('Clear all table cache?')">Clear All Cache Now</a>
                            <p class="description">Use this if tables show outdated data</p>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
            
            <p class="submit">
                <button type="submit" name="tkmtb_save_settings" class="button button-primary button-large">💾 Save Settings</button>
            </p>
        </form>
    </div>
    <?php
    
    // Handle cache clear
    if (isset($_GET['clear_cache']) && check_admin_referer('tkmtb_settings')) {
        tkmtb_clear_cache();
        echo '<div class="notice notice-success"><p><strong>✅ Cache cleared!</strong></p></div>';
    }
}

function tkmtb_save_settings() {
    $settings = array(
        'default_columns', 'clickable_fields', 'rows_per_page', 'pagination_type', 'button_text',
        'enable_caching', 'cache_duration', 'lazy_load',
        'border_external_color', 'border_external_size', 'border_header_color', 'border_header_size',
        'border_hcell_color', 'border_hcell_size', 'border_vcell_color', 'border_vcell_size',
        'border_bottom_color', 'border_bottom_size',
        'bg_header', 'bg_cell', 'bg_cell_hover',
        'font_header_color', 'font_header_size', 'font_cell_color', 'font_cell_size',
        'font_link_color', 'font_link_size',
        'button_bg', 'button_bg_hover', 'button_font_color', 'button_font_size',
        'dropdown_bg', 'dropdown_font', 'dropdown_size', 'dropdown_border'
    );
    
    foreach ($settings as $setting) {
        $key = 'tkmtb_' . $setting;
        
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            
            // Special handling for arrays from text input
            if ($setting === 'default_columns' && isset($_POST['tkmtb_default_columns_text'])) {
                $value = array_map('trim', explode(',', sanitize_text_field($_POST['tkmtb_default_columns_text'])));
            } elseif ($setting === 'clickable_fields' && isset($_POST['tkmtb_clickable_fields_text'])) {
                $value = array_map('trim', explode(',', sanitize_text_field($_POST['tkmtb_clickable_fields_text'])));
            } elseif (strpos($setting, 'color') !== false || strpos($setting, 'bg') !== false) {
                $value = sanitize_hex_color($value);
            } elseif (strpos($setting, 'size') !== false || $setting === 'rows_per_page' || $setting === 'cache_duration') {
                $value = intval($value);
            } elseif ($setting === 'enable_caching' || $setting === 'lazy_load') {
                $value = isset($_POST[$key]) ? 'yes' : 'no';
            } else {
                $value = sanitize_text_field($value);
            }
            
            update_option($key, $value);
        } else {
            if ($setting === 'enable_caching' || $setting === 'lazy_load') {
                update_option($key, 'no');
            }
        }
    }
}

function tkmtb_clear_cache() {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'tkmtb_cache_%'");
}
