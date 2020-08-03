$(function() {
    $('#beautify-btn').click(function (event) {
        event.preventDefault();

        $.post($(this).data('href'), {
            'user_var_dump_form[content]': $('#user_var_dump_form_content').val()
        }, function (data) {
            $('#result').html(data.html);
            $('#result').collapse('show');
        });
    });

    $('#share-btn').click(function (event) {
        event.preventDefault();

        $.post($(this).data('href'), {
            'user_var_dump_form[content]': $('#user_var_dump_form_content').val()
        }, function (data) {
            $('#result').html(data.html);
            $('#result').collapse('show');
        });
    });
});