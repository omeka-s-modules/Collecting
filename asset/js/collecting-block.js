$(document).ready(function() {

    $('.collecting-form').hide();

    // Handle form selection when multiple forms are within one block.
    $('#content').on('change', '.collecting-form-select', function(e) {
        var thisSelect = $(this);
        thisSelect.siblings('.collecting-form').hide();
        thisSelect.siblings('.collecting-form-' + thisSelect.val()).show();
    });

    // Add the CKEditor HTML text editor to any element with class="collecting-html"
    $('.collecting-html').ckeditor();

    // Handle multiple fields.
    $('[data-multiple=1]').each(function() {
        var fieldInput = $(this);
        fieldInput.wrap('<div class="form-row value"><div class="col"></div></div>');
        var field = $(this).closest('.form-row.value');
        var addValue = field.parent().append('<a class="add-value" href="#">' + 'Add new value' + '</a>').find('.add-value');
        addValue
            .attr('data-field',
                // Only the first field may be required.
                field[0].outerHTML.replace(/(required|required="required")/g, '')
            );
        if (fieldInput.hasClass('valuesuggest-input')) {
            addValue
                .attr('data-collecting-type', 'value-suggest');
        }
    });

    // Add a value.
    $('.add-value').on('click', function(e) {
        e.preventDefault();
        var addValue = $(this);
        addValue.before(addValue.attr('data-field'));
        if (addValue.attr('data-collecting-type') === 'value-suggest') {
            var suggestInput = $(addValue.prev('.form-row.value').find('input.valuesuggest-input'));
            valueSuggestAutocomplete(suggestInput);
        }
    });

});
