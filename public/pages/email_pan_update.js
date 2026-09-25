var loader = $('.loading-container');
// For pan-card format
$(".pan_no").inputmask("aaaaa9999a", { placeholder: "" });

$("#submitBtn").prop("disabled", true);

// Enable or disable the submit button based on pan card input
$("#pan_card").on("input", function () {
    var panCardValue = $(this).val().trim();
    if (panCardValue !== "") {
        $("#submitBtn").prop("disabled", false);
    } else {
        $("#submitBtn").prop("disabled", true);
    }
});

$("#selected_email").val($('input[name="email_update"]:checked').val());
$("#updated_email").val($('input[name="email_update"]:checked').val());

// Update the value of selected_email and updated_email inputs when a radio button is clicked
$('input[name="email_update"]').change(function () {
    var selectedEmail = $(this).val();
    $("#selected_email").val(selectedEmail);
    $("#updated_email").val(selectedEmail);
});

$("#userId").val($(".email_update:checked").data("id"));

$(".email_update").change(function () {
    // Get the value of the data-id attribute of the selected radio button
    var dataId = $(this).data("id");

    // Update the value of the hidden input field with the data-id value
    $("#userId").val(dataId);
});
///////////////////////////////////////////////////////////////////////
$(".pan_update").on("change", function () {
    var checkedPan = $(this).val();
    var panno = $(this).data("id");
    if(checkedPan == "1"){
        $(".panEnDs").prop("disabled", false);
    } else {
        $(".panEnDs").prop("disabled", true).val('');
        $('.fromPanUpdate')[0].reset();
        $('#panfile').text('Choose file');
        $('.updated_pan').val(panno);
    }
    checkPanFile();
});
//////////////////////////////////////////////////////////////////////////////////////
$('.fromPanUpdate').on('submit', function (e) {
    e.preventDefault(); // stop normal form submit

    var formData = new FormData(this);

    $.ajax({
        url: $(this).attr('action'), 
        type: 'POST',
        data: formData,
        processData: false, 
        contentType: false, 
        beforeSend: function() {
            loader.css('display', 'flex');
        },
        success: function (response) {
            //console.log(response); return false;
            var status = parseInt(response.status);
            var error = response.error;
            var message = response.message;
            var url = response.url;

            show_msgT(status, message);
            if (status == 1) {
                setTimeout(function() {
                    window.location.href = url;
                }, 2000); 
            }else{
                console.error(error);
            }
            loader.css('display', 'none');
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            loader.css('display', 'none');
        }
    });
});
/////////////////////////////////////////////////////////
function checkPanFile() {
    var pan_status = $('#pan_status').val();
    if(pan_status == 1){  /// valid file
        $('.panfile').prop('disabled', false);
    } else {  ///invaild file
        $('.panfile').prop('disabled', true).val('');
    }
        $('#panfile').text('Choose file');
}
$('#pan_status').on('change', function() {
    checkPanFile();
});
/////////////////////////////////////////////////////////
pa = {
    viewFile: function(thiss, file, name, field_name) {
        $('.paPage').find('.activeFile').removeClass('activeFile');
        $(thiss).closest('div').addClass('activeFile');
        if (field_name.trim() == '') {
            show_msgT(2, 'File/field name not set properly.');
        }
        var proposal_id = $('.prop_id').val();
        var user_type = $('.user_type').val();
        user_type = parseInt(user_type);
        //console.log(user_type);
        var mdl = $('#mdlShowFiles');
        mdl.find('.modal-title').html(name);
        mdl.find('.showFileSrc').attr('src', file);
        mdl.modal('show');
    },
};

