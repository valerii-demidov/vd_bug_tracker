/**
 * Created by ocz on 04.05.17.
 */
$(document).ready(function () {

    /**
     * Custom autocomplete -- start
     */
    $("#addmember-form #input-username").autocomplete({
        source: function (request, response) {
            var autocompleteUrl = $("#input-username").attr('autocomplete-url');
            if (autocompleteUrl) {
                var input = $('#addmember-form #input-username').val();
                if (input.length >= 2) {
                    var data = {username: input};
                    $.ajax({
                        type: "POST",
                        url: autocompleteUrl,
                        data: data,
                        dataType: 'json',
                        timeout: 3000,
                        success: function (result) {
                            if (result.success) {
                                var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                                response($.grep(result.members_list, function (value) {
                                    value = value.label || value.value || value;
                                    return matcher.test(value) || matcher.test(value);
                                }));
                            }
                        },
                        error: function () {
                            alert('Error happened!')
                        }
                    });
                }
            }
        }
    });

    $("#addmember-form").submit(function (e) {
        $('#add-project-member').hide();
        $('#addmember-form .loader').show();

        var addMemberUrl = $('#addmember-form').attr('action');
        if (addMemberUrl) {
            var data = $(this).serialize();
            $.ajax({
                type: "POST",
                url: addMemberUrl,
                data: data,
                dataType: 'json',
                timeout: 3000,
                success: function (result) {
                    if (result.success) {
                        $('#members-grid').html(result.members_grid_html);
                        $('#add-project-member').show();
                        $('#addmember-form .loader').hide();
                    }
                },
                error: function () {
                    alert('Error happened!')
                    $('#add-project-member').show();
                    $('#addmember-form .loader').hide();
                }
            });
        }

        return false;
    });
    /**
     * Custom autocomplete -- end
     */
});