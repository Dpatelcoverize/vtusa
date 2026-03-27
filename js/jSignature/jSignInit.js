$(document).ready(function() {
    $(".signature").css('border', '1px solid #c7c8c9').after('<button class="btn btn-sm btn-primary clear-sign">Clear</button>');
    $(".signature").jSignature();
	$(".signature").after('<textarea style="display:none" class="sign_hash" name="signature"></textarea>');
    $(".signature").after('<textarea style="display:none" class="base30" name="base30"></textarea>');
})

$(document).on('click', '.clear-sign', function(e){
    e.preventDefault();
    let sign = $(this).parent().find('.signature');
    sign.jSignature('reset');
});

$(document).on('submit', ".ajax-form", function (e) {
    e.preventDefault();

    var form = $(this);
    let signatures = $('.signature');
    var formDataToUpload = new FormData(this);

    $(signatures).each(function () {
        let single_sign = $(this).jSignature("getData");
        formDataToUpload.append("image[]", single_sign);
    });

    $.ajax({
        type: 'POST',
        url: form.attr("data-action"),
        data: formDataToUpload,
        contentType: false,
        cache: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
            if (data.status == 200) {
                $(".ajax-form").trigger('reset');
                //$("#signature").attr('src', 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
                let signatures = $('.signature');
                $(signatures).each(function () {
                    $(this).jSignature("reset");
                });

                // toastr.success(data.message)
                $('#success_error').html(data.message);
                $('#success_box').show()
                $('html, body').animate({
                    scrollTop: $("#dealer_agreement").offset().top
                }, 1000);
            } else {
                $('#danger_error').html(data.message);
                $('#danger_box').show()
                $('html, body').animate({
                    scrollTop: $("#dealer_agreement").offset().top
                }, 1000);
                // toastr.error(data.message)
            }
        }

    })
});