<?php
/**
 * Tables List Page - Shows all created tables with shortcodes
 */

if (!defined('ABSPATH')) exit;

function tkmtb_tables_page() {
    // Handle delete
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        check_admin_referer('delete_table_' . $_GET['id']);
        tkmtb_delete_table($_GET['id']);
        echo '<div class="notice notice-success"><p><strong>✅ Table deleted!</strong></p></div>';
    }
    
    $tables = tkmtb_get_all_tables();
    ?>
    <div class="wrap">
        <h1>📊 Document Tables
            <a href="?page=tkmtb-new" class="page-title-action">➕ Add New Table</a>
        </h1>
        
        <?php if (empty($tables)): ?>
            <div class="notice notice-info">
                <p><strong>No tables yet!</strong> <a href="?page=tkmtb-new">Create your first table</a> to display documents in a beautiful, filterable format.</p>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:40px">ID</th>
                        <th>Table Name</th>
                        <th>Shortcode</th>
                        <th>Filters</th>
                        <th>Columns</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $table): 
                        $filters = maybe_unserialize($table['filters']);
                        $columns = maybe_unserialize($table['columns']);
                    ?>
                    <tr>
                        <td><strong>#<?php echo esc_html($table['id']); ?></strong></td>
                        <td>
                            <strong><?php echo esc_html($table['name']); ?></strong>
                        </td>
                        <td>
                            <input type="text" value='[tkm_table id="<?php echo esc_attr($table['id']); ?>"]' readonly class="code" style="width:100%;background:#f0f0f1;padding:8px;border:1px solid #c3c4c7;border-radius:4px" onclick="this.select()">
                            <button type="button" class="button button-small" onclick="navigator.clipboard.writeText(this.previousElementSibling.value);alert('Shortcode copied!');" style="margin-top:5px">📋 Copy</button>
                        </td>
                        <td>
                            <?php 
                            $filter_list = array();
                            if (!empty($filters['grade'])) $filter_list[] = 'Grade';
                            if (!empty($filters['subject'])) $filter_list[] = 'Subject';
                            if (!empty($filters['level'])) $filter_list[] = 'Level';
                            if (!empty($filters['category'])) $filter_list[] = 'Category';
                            if (!empty($filters['version'])) $filter_list[] = 'Version';
                            echo !empty($filter_list) ? esc_html(implode(', ', $filter_list)) : '<em>No filters</em>';
                            ?>
                        </td>
                        <td><?php echo is_array($columns) ? count($columns) . ' columns' : 'Default'; ?></td>
                        <td><?php echo esc_html(date('M j, Y', strtotime($table['created']))); ?></td>
                        <td>
                            <a href="?page=tkmtb-new&edit=<?php echo esc_attr($table['id']); ?>" class="button button-small">✏️ Edit</a>
                            <a href="<?php echo wp_nonce_url('?page=tkmtb-tables&action=delete&id=' . $table['id'], 'delete_table_' . $table['id']); ?>" class="button button-small" onclick="return confirm('Delete this table?');">🗑️ Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="margin-top:30px;padding:20px;background:#fff;border-left:4px solid #72aee6">
            <h3 style="margin-top:0">💡 How to Use Tables</h3>
            <ol>
                <li><strong>Create a table:</strong> Click "Add New Table" above</li>
                <li><strong>Configure filters:</strong> Choose which filters to show (Grade, Subject, etc.)</li>
                <li><strong>Select columns:</strong> Pick which document data to display</li>
                <li><strong>Copy shortcode:</strong> Get the shortcode from the list above</li>
                <li><strong>Paste anywhere:</strong> Add shortcode to any post/page to display the table</li>
            </ol>
            <p><strong>Example shortcode:</strong> <code>[tkm_table id="1"]</code></p>
        </div>
    </div>
    <?php
}

function tkmtb_new_table_page() {
    $editing = false;
    $table_data = array(
        'id' => 0,
        'name' => '',
        'filters' => array(),
        'columns' => tkmtb_get_setting('default_columns', array('image', 'title', 'grade', 'subject', 'type', 'downloads', 'button')),
        'settings' => array()
    );
    
    // Load existing table if editing
    if (isset($_GET['edit']) && $_GET['edit'] > 0) {
        $existing = tkmtb_get_table($_GET['edit']);
        if ($existing) {
            $table_data = $existing;
            $editing = true;
        }
    }
    
    // Save table
    if (isset($_POST['tkmtb_save_table']) && check_admin_referer('tkmtb_save_table')) {
        $save_data = array(
            'id' => isset($_POST['table_id']) ? intval($_POST['table_id']) : 0,
            'name' => sanitize_text_field($_POST['table_name']),
            'filters' => array(
                'grade' => isset($_POST['filter_grade']),
                'subject' => isset($_POST['filter_subject']),
                'level' => isset($_POST['filter_level']),
                'category' => isset($_POST['filter_category']),
                'version' => isset($_POST['filter_version'])
            ),
            'columns' => isset($_POST['columns']) ? array_map('sanitize_text_field', $_POST['columns']) : array(),
            'settings' => array()
        );
        
        $saved_id = tkmtb_save_table($save_data);
        
        echo '<div class="notice notice-success"><p><strong>✅ Table saved!</strong> Shortcode: <code>[tkm_table id="' . $saved_id . '"]</code></p></div>';
        
        $table_data = tkmtb_get_table($saved_id);
        $editing = true;
    }
    
    $all_columns = array(
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
        'date' => 'Date',
        'downloads' => 'Downloads',
        'button' => 'View Button'
    );
    ?>
    <div class="wrap">
        <h1><?php echo $editing ? '✏️ Edit Table' : '➕ Create New Table'; ?></h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('tkmtb_save_table'); ?>
            <input type="hidden" name="table_id" value="<?php echo esc_attr($table_data['id']); ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row">Table Name</th>
                    <td>
                        <input type="text" name="table_name" value="<?php echo esc_attr($table_data['name']); ?>" class="regular-text" required placeholder="e.g., Grade 7 Mathematics Resources">
                        <p class="description">Internal name to identify this table</p>
                    </td>
                </tr>
                
                <tr>
                    <th colspan="2"><h2>🔍 Enable Filters</h2></th>
                </tr>
                <tr>
                    <td colspan="2">
                        <label><input type="checkbox" name="filter_grade" value="1" <?php checked(!empty($table_data['filters']['grade'])); ?>> <strong>Grade Filter</strong> (PP1, PP2, Grade 1, etc.)</label><br>
                        <label><input type="checkbox" name="filter_subject" value="1" <?php checked(!empty($table_data['filters']['subject'])); ?>> <strong>Subject Filter</strong> (Mathematics, English, etc.)</label><br>
                        <label><input type="checkbox" name="filter_level" value="1" <?php checked(!empty($table_data['filters']['level'])); ?>> <strong>Level Filter</strong> (Early Years, Primary, Secondary)</label><br>
                        <label><input type="checkbox" name="filter_category" value="1" <?php checked(!empty($table_data['filters']['category'])); ?>> <strong>Category Filter</strong> (Schemes, Notes, etc.)</label><br>
                        <label><input type="checkbox" name="filter_version" value="1" <?php checked(!empty($table_data['filters']['version'])); ?>> <strong>Version Filter</strong> (2025 Edition, etc.)</label>
                        <p class="description">Selected filters will show as dropdowns above the table</p>
                    </td>
                </tr>
                
                <tr>
                    <th colspan="2"><h2>📋 Select Columns</h2></th>
                </tr>
                <tr>
                    <td colspan="2">
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
                            <?php foreach ($all_columns as $key => $label): ?>
                                <label style="padding:10px;background:#f0f0f1;border-radius:4px">
                                    <input type="checkbox" name="columns[]" value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, (array)$table_data['columns'])); ?>>
                                    <strong><?php echo esc_html($label); ?></strong>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="description">Select which information to display in table columns</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="tkmtb_save_table" class="button button-primary button-large">💾 Save Table</button>
                <a href="?page=tkmtb-tables" class="button button-large">Cancel</a>
            </p>
        </form>
        
        <?php if ($editing && $table_data['id'] > 0): ?>
        <div style="margin-top:30px;padding:20px;background:#d4edda;border-left:4px solid #28a745;border-radius:4px">
            <h3 style="margin-top:0">✅ Table Saved! Use This Shortcode:</h3>
            <input type="text" value='[tkm_table id="<?php echo esc_attr($table_data['id']); ?>"]' readonly class="code" style="width:100%;padding:12px;font-size:16px;background:#fff;border:2px solid #28a745;border-radius:4px" onclick="this.select()">
            <button type="button" class="button button-primary" onclick="navigator.clipboard.writeText(this.previousElementSibling.value);alert('Shortcode copied! Paste it in any post or page.');" style="margin-top:10px">📋 Copy Shortcode</button>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
