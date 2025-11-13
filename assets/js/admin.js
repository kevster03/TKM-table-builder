/**
 * Admin JS - Color pickers and interface
 */
jQuery(document).ready(function($) {
    // Initialize color pickers
    if ($.fn.wpColorPicker) {
        $('.tkmtb-color-picker').wpColorPicker();
    }
    
    // Confirm delete
    $('.wp-list-table a[href*="action=delete"]').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this table?')) {
            e.preventDefault();
        }
    });
    
    // Select all columns checkbox
    var columnsCheckboxes = $('input[name="columns[]"]');
    if (columnsCheckboxes.length) {
        var selectAllBtn = $('<button type="button" class="button" style="margin-bottom:10px">Select All</button>');
        var deselectAllBtn = $('<button type="button" class="button" style="margin-bottom:10px;margin-left:10px">Deselect All</button>');
        
        selectAllBtn.on('click', function() {
            columnsCheckboxes.prop('checked', true);
        });
        
        deselectAllBtn.on('click', function() {
            columnsCheckboxes.prop('checked', false);
        });
        
        columnsCheckboxes.first().closest('td').prepend(selectAllBtn, deselectAllBtn);
    }
    
    // Auto-save reminder
    var formChanged = false;
    $('form').on('change', 'input, select, textarea', function() {
        formChanged = true;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    $('form').on('submit', function() {
        formChanged = false;
    });
});
