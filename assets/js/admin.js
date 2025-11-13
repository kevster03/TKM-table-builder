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

    // Dynamic grade and subject dropdowns based on level selection
    if (typeof tkmtbLevels !== 'undefined') {
        var $levelSelect = $('#prefilter_levels');
        var $gradeSelect = $('#prefilter_grades');
        var $subjectSelect = $('#prefilter_subjects');

        if ($levelSelect.length && $gradeSelect.length && $subjectSelect.length) {
            // Store original selected values
            var originalGrades = getSelectedValues($gradeSelect);
            var originalSubjects = getSelectedValues($subjectSelect);

            $levelSelect.on('change', function() {
                var selectedLevels = getSelectedValues($(this));

                // Update grades dropdown
                updateGradesDropdown(selectedLevels, originalGrades);

                // Update subjects dropdown
                updateSubjectsDropdown(selectedLevels, originalSubjects);
            });

            function getSelectedValues($select) {
                var values = [];
                $select.find('option:selected').each(function() {
                    values.push($(this).val());
                });
                return values;
            }

            function updateGradesDropdown(selectedLevels, keepSelected) {
                var grades = [];
                var gradesAdded = {};

                if (selectedLevels.length > 0) {
                    // Add grades from selected levels
                    selectedLevels.forEach(function(level) {
                        if (tkmtbLevels.levels[level] && tkmtbLevels.levels[level].grades) {
                            tkmtbLevels.levels[level].grades.forEach(function(grade) {
                                if (!gradesAdded[grade]) {
                                    grades.push(grade);
                                    gradesAdded[grade] = true;
                                }
                            });
                        }
                    });
                } else {
                    // Show all grades if no level selected
                    for (var level in tkmtbLevels.levels) {
                        if (tkmtbLevels.levels[level].grades) {
                            tkmtbLevels.levels[level].grades.forEach(function(grade) {
                                if (!gradesAdded[grade]) {
                                    grades.push(grade);
                                    gradesAdded[grade] = true;
                                }
                            });
                        }
                    }
                }

                // Rebuild dropdown
                $gradeSelect.empty();
                grades.forEach(function(grade) {
                    var isSelected = keepSelected.indexOf(grade) !== -1;
                    $gradeSelect.append($('<option></option>')
                        .attr('value', grade)
                        .prop('selected', isSelected)
                        .text(grade));
                });
            }

            function updateSubjectsDropdown(selectedLevels, keepSelected) {
                var subjects = [];
                var subjectsAdded = {};

                if (selectedLevels.length > 0) {
                    // Add subjects from selected levels
                    selectedLevels.forEach(function(level) {
                        if (tkmtbLevels.subjects[level]) {
                            tkmtbLevels.subjects[level].forEach(function(subject) {
                                if (!subjectsAdded[subject]) {
                                    subjects.push(subject);
                                    subjectsAdded[subject] = true;
                                }
                            });
                        }
                    });
                } else {
                    // Show all subjects if no level selected
                    for (var level in tkmtbLevels.subjects) {
                        if (tkmtbLevels.subjects[level]) {
                            tkmtbLevels.subjects[level].forEach(function(subject) {
                                if (!subjectsAdded[subject]) {
                                    subjects.push(subject);
                                    subjectsAdded[subject] = true;
                                }
                            });
                        }
                    }
                }

                // Rebuild dropdown
                $subjectSelect.empty();
                subjects.forEach(function(subject) {
                    var isSelected = keepSelected.indexOf(subject) !== -1;
                    $subjectSelect.append($('<option></option>')
                        .attr('value', subject)
                        .prop('selected', isSelected)
                        .text(subject));
                });
            }
        }
    }
});
