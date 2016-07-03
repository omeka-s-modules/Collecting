/**
 * Populate a prompt row with the provided data.
 *
 * @param {Object} promptData
 */
var populatePromptRow = function(promptData) {

    // Detect whether a row is currently being edited. If one is, populate that
    // one. If one isn't, create a new row using the row template, populate it,
    // and append it to the prompts table.
    var promptRows = $('#prompts');
    var promptRow = promptRows.children('.prompt-editing');
    if (!promptRow.length) {
        var index = promptRows.children('.prompt').length;
        var promptRowTemplate = $('#prompts-span').data('promptRowTemplate');
        promptRow = $(promptRowTemplate.replace(/__INDEX__/g, index));
        promptRow.find('.prompt-id').val(promptData['o:id']);
        promptRows.append(promptRow);
    }

    // Populate the visual elements.
    promptRow.find('.prompt-type-span').html(promptData['o-module-collecting:type']);
    promptRow.find('.prompt-text-span').html(promptData['o-module-collecting:text']);

    // Populate the hidden inputs.
    promptRow.find('.prompt-type').val(promptData['o-module-collecting:type']);
    promptRow.find('.prompt-text').val(promptData['o-module-collecting:text']);
    promptRow.find('.prompt-input-type').val(promptData['o-module-collecting:input_type']);
    promptRow.find('.prompt-select-options').val(promptData['o-module-collecting:select_options']);
    promptRow.find('.prompt-media-type').val(promptData['o-module-collecting:media_type']);
    if (promptData['o:property']) {
        promptRow.find('.prompt-property-id').val(promptData['o:property']['o:id']);
    }
}

/**
 * Reset the sidebar to its default state (i.e. no selected type).
 */
var resetSidebar = function() {
    $('#prompt-type').prop('selectedIndex', 0)
        .prop('disabled', false).css('background-color', '#ffffff');
    $('#prompt-text').val('').closest('.sidebar-section').hide();
    $('#prompt-property').prop('selectedIndex', 0).closest('.sidebar-section').hide();
    $('#prompt-media-type').prop('selectedIndex', 0).closest('.sidebar-section').hide();
    $('#prompt-input-type').prop('selectedIndex', 0).closest('.sidebar-section').hide();
    $('#prompt-select-options').val('').closest('.sidebar-section').hide();
    $('#prompt-save').hide();
}

/**
 * Set the sidebar to the default state of the provided type and show it.
 *
 * @param {String} type
 */
var setSidebarForType = function(type) {
    resetSidebar();
    switch (type) {
        case 'property':
            $('#prompt-property').closest('.sidebar-section').show();
            $('#prompt-input-type').closest('.sidebar-section').show();
            break;
        case 'media':
            $('#prompt-media-type').closest('.sidebar-section').show();
            break;
        case 'input':
            $('#prompt-input-type').closest('.sidebar-section').show();
            break;
        default:
            // invalid or no prompt type
            return;
    }
    $('#prompt-text').closest('.sidebar-section').show();
    $('#prompt-save').show();
}

$(document).ready(function() {

    // Append existing prompts on load.
    $.each($('#prompts-span').data('promptsData'), function() {
        populatePromptRow(this);
    });

    // Enable prompt sorting.
    new Sortable(document.getElementById('prompts'), {
        handle: '.sortable-handle'
    });

    // Handle changing the prompt's type.
    $('#prompt-type').on('change', function() {
        var typeSelect = $(this);
        var type = typeSelect.val();
        setSidebarForType(type);
        typeSelect.val(type);
    });

    // Handle changing the prompt's input type.
    $('#prompt-input-type').on('change', function() {
        var inputType = $(this).val();
        var selectOptionsSection = $('#prompt-select-options').closest('.sidebar-section');
        if ('select' === inputType) {
            selectOptionsSection.show();
        } else {
            selectOptionsSection.hide();
        }
    });

    // Handle the delete prompt icon.
    $('#prompts').on('click', '.prompt-delete', function(e) {
        e.preventDefault();
        var deleteIcon = $(this);
        var prompt = deleteIcon.closest('.prompt');
        prompt.find(':input').prop('disabled', true);
        prompt.addClass('delete');
        prompt.find('.prompt-undo-delete').show();
        prompt.find('.prompt-edit').hide();
        if (prompt.hasClass('prompt-editing')) {
            Omeka.closeSidebar($('#prompt-sidebar'));
        }
        deleteIcon.hide();
    });

    // Handle the undo delete prompt icon.
    $('#prompts').on('click', '.prompt-undo-delete', function(e) {
        e.preventDefault();
        var undoIcon = $(this);
        var prompt = undoIcon.closest('.prompt');
        prompt.find(':input').prop('disabled', false);
        prompt.removeClass('delete');
        prompt.find('.prompt-delete').show();
        prompt.find('.prompt-edit').show();
        undoIcon.hide();
    });

    // Handle the add prompt button.
    $('#prompt-add').on('click', function(e) {
        e.preventDefault();
        resetSidebar();
        $('#prompts > .prompt').removeClass('prompt-editing');
        Omeka.openSidebar($('#prompt-sidebar'));
    });

    // Handle the edit prompt icon.
    $('#prompts').on('click', '.prompt-edit', function(e) {
        e.preventDefault();

        var prompt = $(this).closest('.prompt');
        var type = prompt.find('.prompt-type').val();
        var text = prompt.find('.prompt-text').val();

        prompt.siblings().removeClass('prompt-editing');
        prompt.addClass('prompt-editing');

        setSidebarForType(type);
        switch (type) {
            case 'property':
                var inputType = prompt.find('.prompt-input-type').val();
                $('#prompt-type').val('property');
                $('#prompt-text').val(text);
                $('#prompt-property').val(prompt.find('.prompt-property-id').val());
                $('#prompt-input-type').val(inputType);
                if ('select' === inputType) {
                    var selectOptions = prompt.find('.prompt-select-options').val();
                    $('#prompt-select-options').val(selectOptions).closest('.sidebar-section').show();
                }
                break;
            case 'media':
                var mediaType = prompt.find('.prompt-media-type').val();
                $('#prompt-type').val('media');
                $('#prompt-text').val(text);
                $('#prompt-media-type').val(mediaType);
                break;
            case 'input':
                var inputType = prompt.find('.prompt-input-type').val();
                $('#prompt-type').val('input');
                $('#prompt-text').val(text);
                $('#prompt-input-type').val(inputType);
                if ('select' === inputType) {
                    var selectOptions = prompt.find('.prompt-select-options').val();
                    $('#prompt-select-options').val(selectOptions).closest('.sidebar-section').show();
                }
                break;
            default:
                // invalid or no prompt type
                return;
        }

        // A prompt type cannot be changed once it's saved.
        $('#prompt-type').prop('disabled', true).css('background-color', '#dfdfdf');
        Omeka.openSidebar($('#prompt-sidebar'));
    });

    // Handle saving the prompt.
    $('#prompt-save').on('click', function(e) {
        e.preventDefault();

        var promptData = {
            'o-module-collecting:type': $('#prompt-type').val(),
            'o-module-collecting:text': $('#prompt-text').val(),
            'o-module-collecting:input_type': $('#prompt-input-type').val(),
            'o-module-collecting:select_options': $('#prompt-select-options').val(),
            'o-module-collecting:media_type': $('#prompt-media-type').val(),
            'o:property': {'o:id': $('#prompt-property').val()},
        };

        // Validate the data before populating the row.
        switch (promptData['o-module-collecting:type']) {
            case 'property':
                if (!$.isNumeric(promptData['o:property']['o:id'])) {
                    alert('You must select a property.');
                    return;
                }
                if (!promptData['o-module-collecting:input_type']) {
                    alert('You must select an input type.');
                    return;
                }
                break;
            case 'media':
                if (!promptData['o-module-collecting:media_type']) {
                    alert('You must select a media type.');
                    return;
                }
                break;
            case 'input':
                if (!promptData['o-module-collecting:text']) {
                    alert('You must provide prompt text.');
                    return;
                }
                if (!promptData['o-module-collecting:input_type']) {
                    alert('You must select an input type.');
                    return;
                }
                break;
            default:
                // invalid or no prompt type
                return;
        }

        populatePromptRow(promptData);
        Omeka.closeSidebar($('#prompt-sidebar'));
    });

});
