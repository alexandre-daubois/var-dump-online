$(function() {
    $('#beautify-btn').click(function (event) {
        event.preventDefault();

        $.post($(this).data('href'), {
            'user_var_dump_form[content]': $('#user_var_dump_form_content').val(),
            'user_var_dump_form[_token]': $('#user_var_dump_form__token').val()
        }, function (data) {
            $('#result').html(data.html);
            $('#result').collapse('show');
            $('#export-container').collapse('show');
        });
    });

    $('#share-btn').click(function (event) {
        event.preventDefault();

        $.post($(this).data('href'), {
            'user_var_dump_form[content]': $('#user_var_dump_form_content').val(),
            'user_var_dump_form[_token]': $('#user_var_dump_form__token').val()
        }, function (data) {
            $('#share-id-input-container').collapse('show');
            $('#share-id-input').val("https://vardumpformatter.io" + data.link);
            $('#share-id-input').focus(function() { $(this).select(); });
        });
    });

    $('#export-btn').click(function (event) {
        event.preventDefault();

        $.post($(this).data('href').replace('__format__', $('#export-format-input').val()), {
            'user_var_dump_form[content]': $('#user_var_dump_form_content').val(),
            'user_var_dump_form[_token]': $('#user_var_dump_form__token').val()
        }, function (data) {
            $('#export-result-container').val(data.exportResult);
            $('#exportModal').modal('show');
        });
    });

    $('#copy-export-btn').click(function (event) {
        let copyText = document.getElementById("export-result-container");

        copyText.select();
        copyText.setSelectionRange(0, 99999); /*For mobile devices*/

        document.execCommand("copy");
        $(this).html("<i class='material-icons'>check_circle</i> Copied!");
        $(this).removeClass("btn-primary").addClass("btn-success");
    });

    $('#exportModal').on('hidden.bs.modal', function (event) {
        $('#copy-export-btn').html("<i class='material-icons'>content_copy</i> Copy");
        $('#copy-export-btn').removeClass("btn-success").addClass("btn-primary");
    });
});