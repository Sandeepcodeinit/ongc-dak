pa = {
    reUploadFile:function(thiss, fileId){
        $(fileId).attr('readonly', 'readonly');
        $(thiss).toggleClass('prev_file');
        var hasCls = $(thiss).hasClass('prev_file');
        var url = $(fileId).attr('data-file');
        var fileName = url.substring(url.lastIndexOf("/") + 1);
        var ftxt = 'Re-upload';
        var fileNameShow = fileName;
        if(hasCls){
            var ftxt = 'Use prev.';
            $(fileId).removeAttr('readonly');
            fileNameShow = 'Upload file';
        }
        if ($(fileId).length) {
            $(fileId).val(fileNameShow);
        }
        $(thiss).text(ftxt);
    },
    viewDoc:function(thiss){
        var verified = $(thiss).attr('data-verified');
        var remarks = $(thiss).attr('data-remarks');
        var div = $(thiss).closest('.divFile');
        var file = div.find('.fileUpload').attr('data-file');
        var name = div.find('.fileUpload').attr('data-fileName');
        var field_name = div.find('.fileUpload').attr('name');

        $('.paPage').find('.activeFile').removeClass('activeFile');
        $(thiss).closest('div').addClass('activeFile');
        if (field_name.trim() == '') {
            show_msgT(2, 'File/field name not set properly.');
        }
        //console.log(user_type);
        var mdl = $('#mdlShowFiles');
        mdl.find('.modal-title').html(name);
        mdl.find('.showFileSrc').attr('src', file);
        mdl.modal('show');
    },
}
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////
function dropzoneSetting(file_type){
    Dropzone.autoDiscover = false;
    var url_link = $('.frmUpload').attr('action');
    var csrfToken = $('.frmUpload').find('input[name="_token"]').val();

    var myDropzone = new Dropzone("#myDropzone", {
      url: url_link,
      maxFiles: 1, // Limit to a single file
      acceptedFiles: file_type, // Accept only PDF files
      headers: {
        'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the request headers
      },
      init: function() {
        this.on("success", function(file, response) {
            var status = response.status;
            var message = response.message;
            var file_name = response.file_name;
            var file_path = response.file_path;
            var error = response.error;
            console.log("Error : "+error);
            show_msgT(status, message);
            if(status == '1'){
                $('.activeFile').attr('value', file_name).val(file_name);
                $('.activeFile').attr('data-file', file_path);
                $('.fileUpload').removeClass('activeFile');
                $('#mdlUploadFiles').modal('hide');
            }else{
                myDropzone.removeAllFiles(true);
            }
            //$('.dropzone.dz-started .dz-message').css('display', 'block');

            var progressBar = $(".progress-bar");
            progressBar.css("width", "0%");
            progressBar.attr("aria-valuenow", "0");
            progressBar.text("0%");
        });
        this.on("error", function(file, errorMessage, xhr) {
            var errorMsg = "Something problem occur.";
            if (xhr.status === 503) {
                errorMsg = "Server temporarily unavailable, please try again later.";
            }else if (xhr.status === 413) {
                errorMsg = "Request Entity Too Large.";
            } else {
                errorMsg = errorMessage;
            }
            console.log(errorMessage);
        });
        this.on("sending", function(file, xhr, formData) {
          // Your function before uploading the file
          //console.log("About to upload file:", file.name);
            var file_size = $('.file_size').val();
            var size_byte = parseInt(file_size) * 1024 * 1024; // Convert MB to bytes (1 MB = 1024 * 1024 bytes)
            if (file.size > parseInt(size_byte)) { 
                show_msgT(2, "File size exceeds the maximum allowed size of "+file_size+" MB");
                myDropzone.removeAllFiles(true);
            }
        });
        this.on("uploadprogress", function(file, progress, bytesSent) {
            // Update the progress bar width
            progress = parseFloat(progress).toFixed(2);
            var progressBar = $(".progress-bar");
            progressBar.css("width", progress + "%");
            progressBar.attr("aria-valuenow", progress);
            progressBar.text(progress + "%");
        });
        this.on("removedfile", function(file) {
            var progressBar = $(".progress-bar");
            progressBar.css("width", "0%");
            progressBar.attr("aria-valuenow", "0");
            progressBar.text("0%");
        });
        /*
        this.on("addedfile", function(file) {
          // Append the remove link to the top of the file preview
          var removeLink = Dropzone.createElement("<a class='dz-remove' href='javascript:undefined;' data-dz-remove>Remove file</a>");
          file.previewElement.prepend(removeLink);
        });*/
      },
      dictDefaultMessage: "Choose File",
      maxFilesize: 300, // Maximum filesize in MB
      addRemoveLinks: true,
      dictInvalidFileType: "Invalid file type. Please upload files with "+file_type+" extensions.",
    });
    return myDropzone;
}
var myDropzone = dropzoneSetting('.pdf');
///////////////////////////////////////////////////////////////////////////////////
$('.fileUpload').on('click', function(){
    myDropzone.removeAllFiles(true);
    myDropzone.destroy();
    var thiss = $(this);
    if (thiss.attr('readonly')) {
        return false;
    }
    var name = thiss.attr('name');
    var fileName = thiss.attr('data-fileName');
    var fileSize = thiss.attr('data-fileSize');
    var fileType = thiss.attr('data-fileType');
    if(fileType == undefined || fileType == ''){
        fileType = '.pdf';
    }else{
        //myDropzone.options.acceptedFiles = fileType;
        //myDropzone.options.dictInvalidFileType = "Invalid file type. Please upload files with "+fileType+" extensions."
    }
    var mdl = $('#mdlUploadFiles');
    mdl.find('.field_name').attr('value', name).val(name);
    mdl.find('.file_size').attr('value', fileSize).val(fileSize);
    mdl.find('.file_type').attr('value', fileType).val(fileType);
    mdl.find('.txtFileName').text(fileName);
    myDropzone = dropzoneSetting(fileType);
    mdl.modal('show');

    $('.fileUpload').removeClass('activeFile');
    thiss.addClass('activeFile');
    //console.log(name);
}).on('keydown', function(e) {
    e.preventDefault();
});
////////////////////////////////////////////////////////////////////////////
$(document).on('change', '.input-error', function() {
    var inputVal = $(this).val();
    var inputValLen = inputVal.trim().length;
    //console.log('inputValLen='+inputValLen);
    if(inputValLen > 0){
        $(this).removeClass('input-error');
        var hsCls = $(this).hasClass('select2');
        if(hsCls){
            $(this).next('.select2-container').find('.select2-selection').removeClass('input-error');
        }
    }
});
////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
$('.btn_reupload_docs').click(function () {
    $('.input-error').removeClass('input-error');
    //var formData = $('.frm_reupload_docs').serialize();
    var formData = new FormData($('.frm_reupload_docs')[0]);
    //console.log(formData); return false;
    var url = $('.frm_reupload_docs').attr('action');
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            show_msg(3, '', '<b>Please wait...<br>Saving in progress.</b>', 4);
        },
        success: function (response) {
            //console.log(response); return false;
            
            var status  = response.status;
            var message = response.message;
            var error = response.error;
            var inputFields  = response.inputFields;
            if(inputFields != '' && inputFields != undefined){
                inputFields = inputFields.split(',');
                inputFields.forEach(function(field) {
                    $('*[name="' + field + '"]').addClass('input-error');
                });
            }
            console.log("Error : "+error);
            show_msgT(status, message);
            if(status == '1'){
                setTimeout(function(){
                    window.location.reload();
                }, 2000);
            }
            Swal.close();
            
        },
        error: function (error) {
            console.log(error);
        }
    });
});
