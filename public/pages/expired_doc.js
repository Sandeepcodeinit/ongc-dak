var list_url = $('.sectionContent').attr('data-list_url');
ed = {
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
    enbDisEngFile:function(thiss, inputCls){
        var cked = $(thiss).prop('checked');
        var file = $(thiss).attr('data-file');
        //console.log(cked);
        $(inputCls).attr('readonly', 'readonly');
        if(cked && file.trim() == ''){
            $(inputCls).removeAttr('readonly');
        }
    },
    viewFile:function(thiss){
        //var div = $(thiss).closest('.divFile');
        var div = $(thiss);
        var name = div.attr('data-fileName');
        var file = div.attr('data-filePath');
        var mdl = $('#mdlShowFiles');
        mdl.find('.modal-title').html(name);
        mdl.find('.showFileSrc').attr('src', file);
        mdl.modal('show');
    },
    viewDoc:function(thiss){
        var verified = $(thiss).attr('data-verified');
        var remarks = $(thiss).attr('data-remarks');
        var progress_status = $('.progress_status').val();
        var agency_id = $('.agency_id').val();
        var exp_id = $('.exp_id').val();
        var div = $(thiss).closest('.divFile');
        var file = div.find('.fileUpload').attr('data-file');
        var name = div.find('.fileUpload').attr('data-fileName');
        var field_name = div.find('.fileUpload').attr('name');

        var name = $(thiss).attr('data-fileName');
        var file = $(thiss).attr('data-filePath');
        var field_name = $(thiss).attr('data-fieldName');

        $('.paPage').find('.activeFile').removeClass('activeFile');
        $(thiss).closest('div').addClass('activeFile');
        if (field_name.trim() == '') {
            show_msgT(2, 'File/field name not set properly.');
        }
        var user_type = $('.user_type').val();
        user_type = parseInt(user_type);
        //console.log(user_type);
        var mdl = $('#mdlShowFiles');
        mdl.find('.modal-title').html(name);
        mdl.find('.showFileSrc').attr('src', file);
        progress_status = parseInt(progress_status);
        if ((user_type == 5 || user_type == 6) && (progress_status != 1 && progress_status == 3)) {
            mdl.find('.verifyDoc').css('display', 'block');
            mdl.find('.reason').attr('readonly', 'readonly');
            mdl.find('.verify').prop('checked', false);
            mdl.find('.verify[value="'+verified+'"]').prop('checked', true);
            mdl.find('.reason').attr('value', remarks).val(remarks);
            if(verified == '0'){
                mdl.find('.reason').removeAttr('readonly');
            }
            mdl.find('.proposal_id').attr('value', 0).val(0);
            mdl.find('.field_name').attr('value', field_name).val(field_name);
            mdl.find('.agency_id').attr('value', exp_id).val(exp_id);
            mdl.find('.doc_type').attr('value', 'agency_expired').val('agency_expired');
        }
        mdl.modal('show');
    },
    docConfirmList: function(thiss) {
        if ($('.docVS.bg-info').length > 0) {
            var message = "Please verify/unverify all files.";
            show_msgT(2,message);
            return false;
        }
        else{
            $('.docListShow').attr('style', 'display: none !important');
            var unVerifiedDocs = $('.docVS.bg-danger').length;
            if(parseInt(unVerifiedDocs) <= 0){
                show_msgT(2, 'communicate with agency only when any file unverified.');
                return false;
            }
            var id = $('.exp_id').val();
            var mdl = $('#mdlDocConfirm');
            var url = mdl.attr('data-link');
            var token = mdl.find('input[name="_token"]').val();
            if (parseInt(id) > 0) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: token,
                        id: id,
                        doc_type: 'agency_expired'
                    },
                    success: function(response) {
                        var list = response.result;
                        //console.log(list);
                        var pendingCount = response.pendingCount;
                        $('.liDocPendingList').html(list);
                        if (parseInt(pendingCount) > 0) {
                            $('.docListShow').attr('style', 'display: inline-grid !important');
                        }
                        Swal.close();
                        mdl.modal('show');
                    }
                });
            } else {
                //$('.assign_to_fpr').html('').trigger('change:select2');
            }
        }
    },
}
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
/////////////////////////////////////////////////////////////////////////////
$('.btnExpDocs').click(function () {
    $('.input-error').removeClass('input-error');
    var formData = new FormData($('.frmExpDocs')[0]);
    var url = $('.frmExpDocs').attr('action');
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
            //console.log(response); 
            var inputFields  = response.inputFields;
            if(inputFields != '' && inputFields != undefined){
                inputFields = inputFields.split(',');
                inputFields.forEach(function(field) {
                    $('*[name="' + field + '"]').addClass('input-error');
                    var hasCls = $('*[name="' + field + '"]').hasClass('select2');
                    if(hasCls){
                        $('*[name="' + field + '"]').next('span.select2').find('.select2-selection').addClass('input-error');
                    }
                });
            }
            var status  = response.status;
            var message = response.message;
            //var url     = response.url;
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                setTimeout(function(){
                    window.location = list_url;
                }, 1000);
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
});
////////////////////////////////////////////////////////////
function dropzoneSetting(file_type, thiss=''){
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
            var updateNewFile = response.updateNewFile;
            console.log("Error : "+error);
            show_msgT(status, message);
            if(status == '1'){
                if(updateNewFile == '1'){
                    var div = $('.fileUpload.activeFile').closest('.input-group');
                    div.find('.docVS').removeClass('bg-success').addClass('bg-danger');
                }
                $('.activeFile').attr('value', file_name).val(file_name);
                $('.activeFile').attr('data-file', file_path);
                $('.fileUpload').removeClass('activeFile');
                $('#mdlUploadFiles').modal('hide'); 

                if(thiss != ''){
                    var ck = $(thiss).hasClass('input-error');
                    if(ck){
                        $(thiss).removeClass('input-error');
                    }
                }
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
    myDropzone = dropzoneSetting(fileType, thiss);
    mdl.modal('show');

    $('.fileUpload').removeClass('activeFile');
    thiss.addClass('activeFile');
    //console.log(name);
}).on('keydown', function(e) {
    e.preventDefault();
});
/////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////
function handleSelect2Select(e) {
    var idd = e.params.data.id;
    //console.log(idd);
    var url = $(this).attr('data-link');
    if (parseInt(idd) > 0 || idd == '') {
        var token = $('.agencyAssign').find('input[name="_token"]').val();
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: token,
                tml_idd: idd
            },
            success: function(response) {
                //console.log(response);
                $('.assign_to_fpr').html(response.list).trigger('change:select2');
                var fprId = $('.assign_to_fpr').attr('data-fprId');
                var f_len = $('.assign_to_fpr').find('option[value="' + fprId + '"]').length;
                $('.assign_to_fpr').val('').trigger('change:select2');
                if (parseInt(fprId) > 0 && parseInt(f_len) > 0) {
                    $('.assign_to_fpr').val(fprId).trigger('change:select2');
                }
                Swal.close();
            }
        });
    } else {
        $('.assign_to_fpr').html('').trigger('change:select2');
    }
}
$('.assign_to_tm').on('select2:select', handleSelect2Select);
//////////////////////////////////////////////////////////////////////////
var assignToTm = $('.assign_to_tm').val();
if (parseInt(assignToTm) > 0) {
    handleSelect2Select.call($('.assign_to_tm').get(0), {
        params: {
            data: {
                id: assignToTm
            }
        }
    });
}
////////////////////////////////////////////////////////////
function checkReassignAgency(event) {
    event.preventDefault();
    var agencyverified = $('.agencyAssign').attr('data-agencyverified');
    if (agencyverified == '1') {
        Swal.fire({
            icon: "warning",
            showCloseButton: false,
            showCancelButton: true,
            showConfirmButton: true,
            html: "This agency is verified, Are you sure you want to re-assign this agency ?",
            confirmButtonText: "Yes, Re-assign",
            cancelButtonText: "No, Leave it",
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            },
        }).then((result) => {
            if (result.value) {
                $('.agencyAssign').submit();
            }
        });
    }else{
        $('.agencyAssign').submit();
    }
    return false;
}
/////////////////////////////////////////////////////////////////////
$('.verify').on('click', function() {
    var vl = $(this).val();
    $('.reason').attr('readonly', 'readonly');
    if (vl == '0') {
        $('.reason').removeAttr('readonly');
    }
});

///////////////////////////////////////////////////////////////////////////////
$('.btnSubmitVerify').click(function() {
    //event.preventDefault();
    var frm = $(".frmDocVerify");
    var formData = frm.serialize();
    var url = frm.attr('action');
    var verify = $('.verify').prop('checked');
    var reason = $('.reason').val();
    //console.log(verify);

    $.ajax({
        type: "post",
        url: url,
        data: formData,
        beforeSend: function() {
            show_msg(3, '', '<b>Please wait...<br>Saving in progress.</b>', 4);
        },
        success: function(responseData) {
            //console.log(responseData);
            var status = responseData.status;
            var message = responseData.message;
            if (status == '1') {
                var mdl = $('#mdlShowFiles');
                mdl.find('.reason').val('').attr('readonly', 'readonly');
                mdl.find('.verify').prop('checked', false);
                mdl.find('.proposal_id').attr('value', '').val('');
                mdl.find('.field_name').attr('value', '').val();
                var verifyText = verify === true ? 'Verified' : 'Unverified';
                var verifyClas = verify === true ? 'bg-success' : 'bg-danger';
                //$('.activeFile').find('.docVS').text(verifyText).removeClass('text-success text-danger').addClass(verifyClas);
                $('.activeFile').find('.docVS').removeClass('bg-success bg-danger bg-info').addClass(verifyClas);
                
                var verifyNum = verify === true ? 1 : 0;
                $('.activeFile').find('.docVS').attr('data-verified', verifyNum).attr('data-remarks', reason);
                mdl.modal('hide');
            }
            Swal.close();
            show_msgT(status, message);
            btnEventChange();
        },
        error: function(responseData) {
            console.log(responseData);
        }
    });
});
//////////////////////////////////////////////////////////////////////////////
$('.btnAgencyVerify').on('click', function(){
    var mdl = $("#myModal");
    // data-toggle="modal" data-target="#myModal"
    var len_docVS = $('.docVS').length;
    var len_verify = $('.docVS.bg-success').length;
    //jconsole.log("len_docVS="+len_docVS+", len_verify="+len_verify);
    var user_type = $('.user_type').val();
    var btnSucTxt = title = html_msg = "";
    if(user_type == '3'){
        html_msg = "Are you sure you want to proceed without verifying all the documents..?";
        btnSucTxt = title = "Verify";
    }else{
        html_msg = "Are you sure you want to forward this Application without Verifying all the documents..?";
        btnSucTxt = "Forward";
        if(user_type == '4'){
            title = "Forward/Return to Chief";
        }else{
            //title = "Forward/Return to TL";
            title = "Verify";
            ////// below add for final verify from FPR from date 08-10-24
            if(len_docVS != len_verify){
                show_msgT(2, "Please verify all documents.");
                return false;
            }
        }
    }
    mdl.find('.modal-title').text(title);
    if(len_docVS != len_verify){
        Swal.fire({
           icon: "warning",
           showCloseButton: false,
           showCancelButton: true,
           showConfirmButton: true,
           html: html_msg,
           confirmButtonText: "Yes "+btnSucTxt,
           cancelButtonText: "Cancel",
           showClass: { popup: 'animate__animated animate__fadeInDown'   },
           hideClass: { popup: 'animate__animated animate__fadeOutUp'    },
        }).then((result) => {
           if (result.value) {
                var forwardCnfTxt = 'I hereby certify that the documents and information are not correct.';
                mdl.find('.forwardCnfTxt').text(forwardCnfTxt);
                mdl.find('.log_type').attr('value', 2).val(2);
                mdl.modal('show');
           }else{
                var forwardCnfTxt = 'I hereby certify that the documents and information pertaining to the agency have been thoroughly reviewed and found to be accurate and reliable.';
                mdl.find('.forwardCnfTxt').text(forwardCnfTxt);
                mdl.find('.log_type').attr('value', 1).val(1);
           }
        });
    }else{
        var forwardCnfTxt = 'I hereby certify that the documents and information pertaining to the agency have been thoroughly reviewed and found to be accurate and reliable.';
        mdl.find('.forwardCnfTxt').text(forwardCnfTxt);
        mdl.find('.log_type').attr('value', 1).val(1);
        $("#myModal").modal('show');
    }
});

///////////////////////////////////////////////////////////////////////////////
$('.btnDocSave').click(function() {
    //event.preventDefault();
    var frm = $(".frmVerifyList")
    var formData = frm.serialize();
    var url = frm.attr('action');
    var verify = $('.doc_verify').prop('checked');
    //var getValue = $

    $.ajax({
        type: "post",
        url: url,
        data: formData,
        beforeSend: function(){
            show_msg(3, '', 'Please wait...', 4);
        },
        success: function(responseData) {
            var status = responseData.status;
            var message = responseData.message;
            show_msgT(status, message);
            if (status == '1') {
                setTimeout(function() {
                    //window.location.href = '/agency/index';
                    location.reload();
                }, 2000);
            }
            Swal.close();
        },
        error: function(responseData) {
            console.log(responseData);
        }
    });
});
////////////////////////////////////////////////
$('.btnForwardAgency').click(function() {
    var frm = $(".frmAgencyVerify");
    var formData = frm.serialize();
    var url = frm.attr('action');
    $.ajax({
        type: "post",
        url: url,
        data: formData,
        beforeSend: function(){
            show_msg(3, '', 'Please wait...', 4);
        },
        success: function(responseData) {
            //console.log(responseData);
            var status = responseData.status;
            var message = responseData.message;
            show_msgT(status, message);
            if (status == '1') {
                setTimeout(function() {
                    //window.location = list_url;
                    window.close();
                    //location.reload();
                }, 2000);
            }
            Swal.close();
        },
        error: function(responseData) {
            console.log(responseData);
        }
    });
})