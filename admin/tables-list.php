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
                        $prefilters = maybe_unserialize($table['prefilters']);
                        $columns = maybe_unserialize($table['columns']);

                        // For backward compatibility, check for old 'filters' column
                        if (!is_array($prefilters) || empty($prefilters)) {
                            $prefilters = array(
                                'levels' => array(),
                                'grades' => array(),
                                'subjects' => array(),
                                'categories' => array(),
                                'versions' => array()
                            );
                        }
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
                            $filter_summary = array();
                            if (!empty($prefilters['levels'])) $filter_summary[] = count($prefilters['levels']) . ' Level(s)';
                            if (!empty($prefilters['grades'])) $filter_summary[] = count($prefilters['grades']) . ' Grade(s)';
                            if (!empty($prefilters['subjects'])) $filter_summary[] = count($prefilters['subjects']) . ' Subject(s)';
                            if (!empty($prefilters['categories'])) $filter_summary[] = count($prefilters['categories']) . ' Category(s)';
                            if (!empty($prefilters['versions'])) $filter_summary[] = count($prefilters['versions']) . ' Version(s)';

                            if (!empty($filter_summary)) {
                                echo '<small>' . esc_html(implode(', ', $filter_summary)) . '</small>';
                            } else {
                                echo '<em style="color:#999">No filters</em>';
                            }
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
        'prefilters' => array(
            'levels' => array(),
            'grades' => array(),
            'subjects' => array(),
            'categories' => array(),
            'versions' => array()
        ),
        'columns' => tkmtb_get_setting('default_columns', array()),
        'settings' => array(
            'show_title' => 'no'
        )
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
        // Collect prefilters
        $prefilters = array(
            'levels' => isset($_POST['prefilter_levels']) && is_array($_POST['prefilter_levels'])
                ? array_map('sanitize_text_field', $_POST['prefilter_levels'])
                : array(),
            'grades' => isset($_POST['prefilter_grades']) && is_array($_POST['prefilter_grades'])
                ? array_map('sanitize_text_field', $_POST['prefilter_grades'])
                : array(),
            'subjects' => isset($_POST['prefilter_subjects']) && is_array($_POST['prefilter_subjects'])
                ? array_map('sanitize_text_field', $_POST['prefilter_subjects'])
                : array(),
            'categories' => isset($_POST['prefilter_categories']) && is_array($_POST['prefilter_categories'])
                ? array_map('sanitize_text_field', $_POST['prefilter_categories'])
                : array(),
            'versions' => isset($_POST['prefilter_versions']) && is_array($_POST['prefilter_versions'])
                ? array_map('sanitize_text_field', $_POST['prefilter_versions'])
                : array()
        );

        // Validate: At least one filter value must be selected
        $has_filter = false;
        foreach ($prefilters as $values) {
            if (!empty($values)) {
                $has_filter = true;
                break;
            }
        }

        if (!$has_filter) {
            echo '<div class="notice notice-error"><p><strong>❌ Error:</strong> Please select at least one filter value (Level, Grade, Subject, Category, or Version).</p></div>';
        } else {
            $save_data = array(
                'id' => isset($_POST['table_id']) ? intval($_POST['table_id']) : 0,
                'name' => sanitize_text_field($_POST['table_name']),
                'prefilters' => $prefilters,
                'columns' => isset($_POST['columns']) ? array_map('sanitize_text_field', $_POST['columns']) : array(),
                'settings' => array(
                    'show_title' => isset($_POST['show_title']) ? 'yes' : 'no'
                )
            );

            $saved_id = tkmtb_save_table($save_data);

            echo '<div class="notice notice-success"><p><strong>✅ Table saved!</strong> Shortcode: <code>[tkm_table id="' . $saved_id . '"]</code></p></div>';

            $table_data = tkmtb_get_table($saved_id);
            $editing = true;
        }
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
                    <th colspan="2"><h2>🔍 Pre-Filter Documents</h2></th>
                </tr>
                <tr>
                    <td colspan="2">
                        <p class="description" style="margin-top:0;background:#fff3cd;padding:10px;border-left:4px solid #ffc107">
                            <strong>⚠️ Required:</strong> Select at least one filter value below. The table will show ONLY documents matching your selections.
                        </p>
                    </td>
                </tr>

                <?php
                // Get data for dropdowns
                $levels = tkm_get_levels();
                $all_categories = get_terms(array('taxonomy' => 'file_category', 'hide_empty' => false));
                $all_versions = tkmtb_get_unique_meta('_tkm_version');

                // Get selected levels for grade/subject population
                $selected_levels = !empty($table_data['prefilters']['levels']) ? $table_data['prefilters']['levels'] : array();
                ?>

                <tr>
                    <th scope="row">
                        <label for="prefilter_levels">Education Level</label>
                    </th>
                    <td>
                        <select name="prefilter_levels[]" id="prefilter_levels" multiple size="5" style="width:100%;max-width:500px">
                            <?php foreach ($levels as $key => $data): ?>
                                <option value="<?php echo esc_attr($key); ?>" <?php selected(in_array($key, $selected_levels)); ?>>
                                    <?php echo esc_html($data['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Hold Ctrl (Windows) or Cmd (Mac) to select multiple levels</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="prefilter_grades">Grade</label>
                    </th>
                    <td>
                        <select name="prefilter_grades[]" id="prefilter_grades" multiple size="8" style="width:100%;max-width:500px">
                            <?php
                            // Populate grades based on selected levels
                            $selected_grades = !empty($table_data['prefilters']['grades']) ? $table_data['prefilters']['grades'] : array();
                            $grades_shown = array();

                            if (!empty($selected_levels)) {
                                foreach ($selected_levels as $level) {
                                    if (isset($levels[$level]['grades'])) {
                                        foreach ($levels[$level]['grades'] as $grade) {
                                            if (!in_array($grade, $grades_shown)) {
                                                $grades_shown[] = $grade;
                                                ?>
                                                <option value="<?php echo esc_attr($grade); ?>" <?php selected(in_array($grade, $selected_grades)); ?>>
                                                    <?php echo esc_html($grade); ?>
                                                </option>
                                                <?php
                                            }
                                        }
                                    }
                                }
                            } else {
                                // Show all grades if no level selected
                                foreach ($levels as $level_data) {
                                    foreach ($level_data['grades'] as $grade) {
                                        if (!in_array($grade, $grades_shown)) {
                                            $grades_shown[] = $grade;
                                            ?>
                                            <option value="<?php echo esc_attr($grade); ?>" <?php selected(in_array($grade, $selected_grades)); ?>>
                                                <?php echo esc_html($grade); ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                        <p class="description">Hold Ctrl (Windows) or Cmd (Mac) to select multiple grades. Changes dynamically based on selected level.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="prefilter_subjects">Subject</label>
                    </th>
                    <td>
                        <select name="prefilter_subjects[]" id="prefilter_subjects" multiple size="8" style="width:100%;max-width:500px">
                            <?php
                            // Populate subjects based on selected levels
                            $selected_subjects = !empty($table_data['prefilters']['subjects']) ? $table_data['prefilters']['subjects'] : array();
                            $subjects_shown = array();

                            if (!empty($selected_levels)) {
                                foreach ($selected_levels as $level) {
                                    $level_subjects = tkm_get_subjects_for_level($level);
                                    foreach ($level_subjects as $subject) {
                                        if (!in_array($subject, $subjects_shown)) {
                                            $subjects_shown[] = $subject;
                                            ?>
                                            <option value="<?php echo esc_attr($subject); ?>" <?php selected(in_array($subject, $selected_subjects)); ?>>
                                                <?php echo esc_html($subject); ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                }
                            } else {
                                // Show all subjects if no level selected
                                foreach ($levels as $key => $level_data) {
                                    $level_subjects = tkm_get_subjects_for_level($key);
                                    foreach ($level_subjects as $subject) {
                                        if (!in_array($subject, $subjects_shown)) {
                                            $subjects_shown[] = $subject;
                                            ?>
                                            <option value="<?php echo esc_attr($subject); ?>" <?php selected(in_array($subject, $selected_subjects)); ?>>
                                                <?php echo esc_html($subject); ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                        <p class="description">Hold Ctrl (Windows) or Cmd (Mac) to select multiple subjects. Changes dynamically based on selected level.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="prefilter_categories">Category</label>
                    </th>
                    <td>
                        <select name="prefilter_categories[]" id="prefilter_categories" multiple size="6" style="width:100%;max-width:500px">
                            <?php
                            $selected_categories = !empty($table_data['prefilters']['categories']) ? $table_data['prefilters']['categories'] : array();
                            if (!empty($all_categories) && !is_wp_error($all_categories)):
                                foreach ($all_categories as $cat): ?>
                                    <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected(in_array($cat->slug, $selected_categories)); ?>>
                                        <?php echo esc_html($cat->name); ?>
                                    </option>
                                <?php endforeach;
                            endif;
                            ?>
                        </select>
                        <p class="description">Hold Ctrl (Windows) or Cmd (Mac) to select multiple categories</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="prefilter_versions">Version</label>
                    </th>
                    <td>
                        <select name="prefilter_versions[]" id="prefilter_versions" multiple size="5" style="width:100%;max-width:500px">
                            <?php
                            $selected_versions = !empty($table_data['prefilters']['versions']) ? $table_data['prefilters']['versions'] : array();
                            if (!empty($all_versions)):
                                foreach ($all_versions as $version): ?>
                                    <option value="<?php echo esc_attr($version); ?>" <?php selected(in_array($version, $selected_versions)); ?>>
                                        <?php echo esc_html($version); ?>
                                    </option>
                                <?php endforeach;
                            endif;
                            ?>
                        </select>
                        <p class="description">Hold Ctrl (Windows) or Cmd (Mac) to select multiple versions</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Display Options</th>
                    <td>
                        <label>
                            <input type="checkbox" name="show_title" value="1" <?php checked(!empty($table_data['settings']['show_title']) && $table_data['settings']['show_title'] === 'yes'); ?>>
                            <strong>Show table title on frontend</strong>
                        </label>
                        <p class="description">Display the table name above the table on your site</p>
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
