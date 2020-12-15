$(function() {
    $('#beautify-btn').click(function (event) {
        event.preventDefault();

        if ($('#user_var_dump_form_content').val().trim().length === 0) {
            return;
        }

        $.post($(this).data('href'), {
            'user_var_dump_form[content]': $('#user_var_dump_form_content').val(),
            'user_var_dump_form[_token]': $('#user_var_dump_form__token').val()
        }, function (data) {
            $('#alert-invalid-output, #alert-cant-parse-output, #alert-cant-share').addClass('d-none');

            if (data.error) {
                $('#share-id-input-container').collapse('hide');
                if (data.html.length === 0) {
                    $('#alert-cant-parse-output').removeClass('d-none');
                    $('#result').html('');
                    $('#result').collapse('hide');
                    $('#export-container').collapse('hide');
                } else {
                    $('#alert-invalid-output').removeClass('d-none');
                }
            }

            if (data.html.length > 0) {
                $('#result').html(data.html);
                $('#result').collapse('show');
                $('#export-container').collapse('show');
            }
        });
    });

    $('#share-btn').click(function (event) {
        event.preventDefault();
        $.post($(this).data('href'), {
            'user_var_dump_form[content]': $('#user_var_dump_form_content').val(),
            'user_var_dump_form[_token]': $('#user_var_dump_form__token').val()
        }, function (data) {
            $('#alert-invalid-output, #alert-cant-parse-output, #alert-cant-share').addClass('d-none');
            $('#share-id-input-container').collapse('show');

            $('#share-id-input').val("https://vardumpformatter.io" + data.link);
            $('#share-id-input').focus(function() { $(this).select(); });
        }).fail(function (data) {
            if (data.status === 400) {
                $('#alert-cant-share').removeClass('d-none');
                $('#share-id-input-container').collapse('hide');
            }
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