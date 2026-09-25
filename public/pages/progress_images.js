////////////////////////////////////////////////////////////
var myDropzone;
function dropzoneSetting(no_of_files=10, thiss=''){
    var milestone_sr_no = $('.milestone_sr_no').val();
    var sr_no = parseInt(milestone_sr_no);
    no_of_files = parseInt(no_of_files);
    file_type = '.jpg,.jpeg,.png';
    Dropzone.autoDiscover = false;
    var url_link = $('.frmUpload').attr('action');
    var csrfToken = $('.frmUpload').find('input[name="_token"]').val();
    var acceptedExtensions = file_type.split(',');

    myDropzone = new Dropzone("#myDropzone", {
      url: url_link,
      maxFiles: no_of_files, // Limit to a single file
      acceptedFiles: file_type, 
      headers: {
        'X-CSRF-TOKEN': csrfToken 
      },
      init: function() {
        this.on("addedfile", function(file) {
            if(sr_no <= 0 || milestone_sr_no == ''){
                show_msgT(2, "Please select milestone serial no.");
                $('.milestone_sr_no').focus();
                myDropzone.removeAllFiles(true);
                return null;
            }
        });
        this.on("success", function(file, response) {
            var status = response.status;
            var message = response.message;
            var delLink = response.delLink;
            var error = response.error;
            var remain_pics = response.remain_pics;
            var exception = response.exception;
            //console.log("status : "+status+", remain_pics : "+remain_pics+", delLink : "+delLink+", exception : "+exception);
            show_msgT(status, message);
            if(status == '1'){
                var remain_pics = response.remain_pics;
                if(parseInt(remain_pics) >= 0){
                    $('.milestone_sr_no').find('option:selected').attr('data-pics_upload', remain_pics);
                    $('.fntRemainImages').text(remain_pics);
                    setPicsInfo('.milestone_sr_no', 0);
                }
                if(parseInt(remain_pics) <= 0){
                    $('.milestone_sr_no').find('option:selected').css('display', 'none');
                    $('.milestone_sr_no').val('');
                }
                var removeLink = file.previewElement.querySelector("[data-dz-remove]");
                var dynamicHref = delLink; 
                removeLink.setAttribute("href", dynamicHref);
            }else{
                //myDropzone.removeAllFiles(true);
                var errorMessageSpan = file.previewElement.querySelector("[data-dz-errormessage]");
                errorMessageSpan.textContent = message;
            }
            //$('.dropzone.dz-started .dz-message').css('display', 'block');
        });
        this.on("error", function(file, errorMessage, xhr) {
            var errorMsg = "Something problem occur.";
            if (xhr && xhr.status === 503) {
                errorMsg = "Server temporarily unavailable, please try again later.";
            }else if (xhr && xhr.status === 413) {
                errorMsg = "Request Entity Too Large.";
            } else {
                errorMsg = errorMessage;
            }
            //console.log(errorMessage);
        });
        this.on("sending", function(file, xhr, formData) {
          // Your function before uploading the file
            var size_byte = 2 * 1024 * 1024; // Convert MB to bytes (1 MB = 1024 * 1024 bytes)
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
            var removeLink = file.previewElement.querySelector("[data-dz-remove]");
            var hrefValue = removeLink.getAttribute("href");
            var hrefValue = hrefValue.trim();
            if (hrefValue != '' && hrefValue != 'undefined') {
                $.ajax({
                    async:false,
                    url: hrefValue,
                    type: "GET",
                    success: function(response) {
                        var remain_pics = response.remain_pics;
                        if (response.status == '1') {
                            console.log('File removed successfully');
                            $('.milestone_sr_no').find('option[value="'+sr_no+'"]').css('display', 'block');
                            $('.milestone_sr_no').val(sr_no);
                            $('.milestone_sr_no').find('option:selected').attr('data-pics_upload', remain_pics);
                            $('.fntRemainImages').text(remain_pics);
                            
                            setPicsInfo('.milestone_sr_no', 0);
                            imageList();
                        } else {
                            console.log('Error removing file: ' + response.message);
                        }
                    }
                });
            }
        });
        this.on("queuecomplete", function() {
            show_msgT(1, "All files have been uploaded.");
            imageList();
        });
      },
      accept: function(file, done) {
        var extension = file.name.split('.').pop().toLowerCase();
        if (acceptedExtensions.indexOf('.'+extension) > -1) {
            done();
        } else {
            done("Invalid file type.");
        }
      },
      dictDefaultMessage: "Choose File",
      maxFilesize: 2, // Maximum filesize in MB
      addRemoveLinks: true,
      dictInvalidFileType: "Invalid file type. Please upload files with "+file_type+" extensions.",
    });
    return myDropzone;
}
//myDropzone.destroy();
////////////////////////////////////////////////////////////////////////////
function setPicsInfo(thiss, reset=1){
    var milestone_sr_no = $(thiss).val();
    var opt_len = $(thiss).find('option').length;
    //console.log("opt_len="+opt_len);
    /*
    if(opt_len <= 1){
        $('.dispImg').css('display', 'none');
        return false;
    }*/
    var no_of_files = $(thiss).find('option:selected').attr('data-pics_upload');
    if(parseInt(milestone_sr_no) > 0){
        if(reset == 1){
            myDropzone.files.forEach(function(file) {
                var removeLink = file.previewElement.querySelector("[data-dz-remove]");
                if (removeLink) {
                    removeLink.setAttribute("href", "");
                }
            });
            myDropzone.destroy();
            dropzoneSetting(no_of_files);
        }
        var sr_no = parseInt(milestone_sr_no);
        $('.sr_no').attr('value', sr_no).val();
    }else{
        myDropzone.destroy();
        no_of_files = 0;
    }
    $('.fntRemainImages').text(no_of_files);
}
///////////////////////////////////////////////////////////////////////////////////
$('#myDropzone').on('click', function(e) {
    e.preventDefault();
    var milestone_sr_no = $('.milestone_sr_no').val();
    var sr_no = parseInt(milestone_sr_no);
    if(sr_no <= 0 || milestone_sr_no == ''){
        show_msgT(2, "Please select milestone serial no.");
        $('.milestone_sr_no').focus();
    }
});
//////////////////////////////////////////////////////////////////////
function imageList(){
    var tbody = $('.tBodyFiles');
    var link = tbody.attr('data-link');
    $.ajax({
        url: link,
        type: "GET",
        success: function(response) {
            var status = response.status;
            var result = response.result;
            tbody.html(result);
        }
    });
}
imageList();
////////////////////////////////////////////////////////
var inner = $("#imageSliderInner");
$(document).on('click', '.trImages', function() {
    var trr = $(this).closest('tr');
    $('.trImages').closest('tr').removeClass('trActive');
    trr.addClass('trActive');
    var files = trr.attr('data-files');
    var title = trr.find('td:eq(0)').text();
    renderCarousel(files);
    var mdl = $('#mdlShowImages');
    mdl.find('.modal-title').text("Milestone "+title);
    mdl.modal({backdrop: 'static', keyboard: false}, 'show');
});
function renderCarousel(files) {
    var mdl = $('#mdlShowImages');
    inner.empty();
    //imageUrls = files.split(',');
    var files = decodeURIComponent(files);
    var imageUrls = JSON.parse(files);
    imageCount = imageUrls.length;
    imageUrls.forEach(function(row, index) {
        var url = row.pi_image;
        var pid = row.pi_id;
        var itemClass = index === 0 ? "carousel-item active" : "carousel-item";
        var img = $('<img>').addClass('d-block').attr('src', url);
        var serialNo = $('<div>').addClass('carousel-caption d-none w-100 d-md-block bg-primary').text("Image: " +imageCount+ "\\" + (index+1));
        var deleteIcon = $('<span>').addClass('delete-icon text-danger').html('<i class="fa fa-times-circle fa-lg"></i>').attr('title', 'Delete').attr('data-toggle', 'tooltip');
        var item = $('<div>').addClass(itemClass).append(img).append(serialNo).append(deleteIcon);
        inner.append(item);

        deleteIcon.click(function() {
            var baseUrl = mdl.find('#imageSlider').attr('data-delUrl');
            var urlDel = baseUrl.replace('__ID__', pid);
            Swal.fire({
              icon: "warning",
              //title: 'Oops...',
              showCloseButton: false,
              showCancelButton: true,
              showConfirmButton: true,
              html: "Are you sure to delete this image..?",
              confirmButtonText: "Yes, Delete",
              cancelButtonText: "No, Leave",
              showClass: { popup: 'animate__animated animate__fadeInDown'   },
              hideClass: { popup: 'animate__animated animate__fadeOutUp'    },
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: urlDel,
                        type: "GET",
                        success: function(response) {
                            var status = parseInt(response.status);
                            show_msgT(status, response.message);
                            if (status == 1) {
                                imageUrls.splice(index, 1);
                                mdl.find('#imageSlider').attr('data-reload', 1);
                                var files = JSON.stringify(imageUrls); 
                                var files = encodeURIComponent(files);
                                renderCarousel(files);
                                //$('.trImages .trActive').attr('data-files', files);
                                // Activate the next item
                                var nextIndex = index < imageUrls.length ? index : imageUrls.length - 1;
                                $('#imageSlider').carousel(nextIndex);
                            }
                        }
                    });
                }else{

                }
            });
        });
    });
}
$('#imageSlider').carousel({
    interval: false // Disable auto-slide
});
///////////////////////////////////////////////////////////
function closeMdlImg(thiss){
    var mdl = $('#mdlShowImages');
    var reload = mdl.find('#imageSlider').attr('data-reload');
    if(parseInt(reload) == 1){
        location.reload();
    }else{
        mdl.modal('hide');
    }
}
////////////////////////////////////////////////////////
var innerDiv = $("#imageReorder");
$(document).on('click', '.trReorder', function() {
    innerDiv.empty();
    var trr = $(this).closest('tr');
    $('.trImages').closest('tr').removeClass('trActive');
    trr.addClass('trActive');
    var sr_no = trr.attr('data-sr_no');
    var files = trr.attr('data-files');
    var title = trr.find('td:eq(0)').text();
    
    var files = decodeURIComponent(files);
    var imageUrls = JSON.parse(files);
    imageCount = imageUrls.length;
    imageUrls.forEach(function(row, index) {
        var url = row.pi_image;
        var pid = row.pi_id;
        var verifyStatus = row.pi_verify_status;
        var verifyRemarks = row.pi_verify_by_remarks;
            verifyRemarks = verifyRemarks == null ? '': verifyRemarks;
            verifyRemarks = decodeURIComponent(verifyRemarks.replace(/\+/g, ' '));
        var img = $('<img>').addClass('img_sort showFileSrc').attr('src', url);
        var remarks = $('<p>').addClass('img_remarks mb-0').text(verifyRemarks);
        var input = $('<input>').addClass('img_sort_pid').attr('type', 'hidden').attr('name', 'pid[]').attr('value', pid);
        var serialNo = $('<label>').addClass('img_sort_srno mb-0 w-100 text-bold text-center').text("Image: " + (index+1));
        var deleteIcon = "";
        if(parseInt(verifyStatus) == 0){
            deleteIcon = $('<span>').addClass('delete-icon text-danger').html('<i class="fa fa-times-circle fa-lg"></i>').attr('title', 'Delete').attr('data-toggle', 'tooltip');
            deleteIcon.click(function() {
                var baseUrl = $('#imageSlider').attr('data-delUrl');
                var urlDel = baseUrl.replace('__ID__', pid);
                Swal.fire({
                icon: "warning",
                //title: 'Oops...',
                showCloseButton: false,
                showCancelButton: true,
                showConfirmButton: true,
                html: "Are you sure to delete this image..?",
                confirmButtonText: "Yes, Delete",
                cancelButtonText: "No, Leave",
                showClass: { popup: 'animate__animated animate__fadeInDown'   },
                hideClass: { popup: 'animate__animated animate__fadeOutUp'    },
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            async:false,
                            url: urlDel,
                            type: "GET",
                            success: function(response) {
                                var status = parseInt(response.status);
                                show_msgT(status, response.message);
                                if (status == 1) {
                                    imageUrls.splice(index, 1);
                                    mdl.find('#imageSlider').attr('data-reload', 1);
                                    var files = JSON.stringify(imageUrls); 
                                    var files = encodeURIComponent(files);
                                    renderCarousel(files);
                                    imageList();
                                    // Activate the next item
                                    deleteIcon.closest('.ui-state-default').remove();
                                }
                            }
                        });
                    }else{

                    }
                });
            });
        }
        var maxIcon = $('<span>').addClass('btn btn-xs btn-outline-light text-primary btnMaxMin maximizeButton').html('<i class="fas fa-expand maximizeIcon"></i>').attr('title', 'Maximize').attr('data-toggle', 'tooltip');
        var itemImg = $('<div>').addClass("sort_divs mdlPdfFile").append(serialNo).append(img).append(input).append(deleteIcon).append(maxIcon).append(remarks);
        //var minIcon = $('<span>').addClass('btn btn-xs btn-outline-light text-primary btnMaxMin').html('<i id="maximizeIcon" class="fas fa-expand"></i>').attr('id', 'minimizeButton').attr('title', 'Minimize').attr('data-toggle', 'tooltip');
        var item = $('<div>').addClass("col-sm-2 mb-2 pr-0 ui-state-default").append(itemImg);
        innerDiv.append(item);

    });
    var mdl = $('#mdlReorderImages');
    mdl.find('.modal-title').text("Reorder # Milestone "+title);
    mdl.find('.serial_no').attr("value", sr_no).val(sr_no);
    mdl.modal('show');
});
if(innerDiv.length > 0){
    innerDiv.sortable();
}
///////////////////////////////////////////////////////////////////////
function saveReorder(){
    var frm = $(".frmReorder");
    var action = frm.attr('action');
    var frmData = frm.serialize();
    $.ajax({
        url: action,
        type: "POST",
        data: frmData,
        beforeSend: function(){
            show_msg(3, '', 'Please wait...', 4);
        },
        success: function(response) {
            //console.log(response);
            var status = response.status;
            var message = response.message;
            show_msgT(status, message);
            if(status == '1'){
                location.reload();
            }
            Swal.close();
        }
    });
}
///////////////////////////////////////////////////
var dispImgBlock = $('.divMainPimg').attr('data-dispImgBlock');
if(parseInt(dispImgBlock) > 0){
    dropzoneSetting(10);
    setPicsInfo('.milestone_sr_no');
}
///////////////////////////////////////////////////////////////
var inner = $("#imageSliderInner");
$(document).on('click', '.trImagesVerify', function() {
    var trr = $(this).closest('tr');
    $('.trImages').closest('tr').removeClass('trActive');
    trr.addClass('trActive');
    var files = trr.attr('data-files');
    var title = trr.find('td:eq(0)').text();
    inner.empty();
    //imageUrls = files.split(',');
    var files = decodeURIComponent(files);
    var imageUrls = JSON.parse(files);
    imageCount = imageUrls.length;
    imageUrls.forEach(function(row, index) {
        var url = row.pi_image;
        var pid = row.pi_id;
        var verify = row.pi_verify_status;
            verified = verify == 1 ? 'checked': '';
            unverified = verify == 2 ? 'checked': '';
        var remarks = row.pi_verify_by_remarks;
            remarks = remarks == null ? '': remarks;
            remarks = decodeURIComponent(remarks.replace(/\+/g, ' '));
        var upload_through = row.upload_through;
        var latitude = row.latitude;
        var longitude = row.longitude;
        var itemClass = index === 0 ? "carousel-item active" : "carousel-item";
        var img = $('<img>').addClass('d-block').attr('src', url);
        var wdHdr = 100;
        var googleMap = '';
        if(parseInt(upload_through) == 1){
            wdHdr = 50;
            $googleMapUrl = "http://maps.google.com/maps?q="+latitude+","+longitude+"&z=19";
            //$googleMapUrl = 'https://www.google.com/maps?q=30.6602357,76.8604908&hl=es;z=14&output=embed';
            googleMap = '<font class="w-50 mb-0 googleMapView" style="border-left: 1px solid #fff;" data-url="'+$googleMapUrl+'">View Google Map</font>';
        }
        var srNoTxt = '<font class="w-'+wdHdr+' mb-0">Image: ' +imageCount+ '\\' + (index+1)+'</font>';
        
        var serialNo = $('<div>').addClass('carousel-caption d-none w-100 d-flex bg-primary').html(srNoTxt + googleMap);
        var verifyInfo = $('<div>').addClass('divVerify row border-bottom mb-1 pb-1').html('<label class="col-sm-12" for="">Verify Document status</label>\
            <div class="col-sm-3">\
              <div class="icheck-success d-inline">\
                <input type="radio" name="verify_'+pid+'" '+verified+' id="verify1_'+pid+'" class="verify" value="1">\
                <label for="verify1_'+pid+'">Verified</label>\
              </div>\
              <div class="icheck-danger d-inline">\
                <input type="radio" name="verify_'+pid+'" '+unverified+' id="verif0_'+pid+'" class="verify" value="2">\
                <label for="verif0_'+pid+'">Unverified</label>\
              </div>\
            </div>\
            <div class="col-sm-7" style="">\
              <input type="text" name="reason_'+pid+'" class="form-control form-control-sm reason" placeholder="Enter reason" readonly="readonly" value="'+remarks+'">\
            </div>\
            <div class="col-sm-2" style="">\
              <button data-pid="'+pid+'" type="button" name="submit_'+pid+'" class="btn btn-sm btn-success btn-block btnSubmitVerify">Submit</button>\
            </div>');
        var item = $('<div>').addClass(itemClass).append(verifyInfo).append(serialNo).append(img);
        inner.append(item);
    });
    var mdl = $('#mdlShowImages');
    mdl.find('.modal-title').text("Milestone "+title);
    mdl.modal({backdrop: 'static', keyboard: false}, 'show');
});
////////////////////////////////////////////////////
$(document).on('click', '.btnSubmitVerify', function() {
    var frm = $('.frmImgVerify');
    var action = frm.attr('action');
    var proposal_id = frm.find('.proposal_id').val();
    var token = frm.find('[name="_token"]').val();
    var div = $(this).closest('.divVerify');
    var pid = $(this).attr('data-pid');
    var verifyStatus = div.find('.verify:checked').val();
    var reason = div.find('.reason').val();
    if(verifyStatus == undefined || verifyStatus == ''){
        show_msgT(2, "Please tick any one checkbox.");
        return false;
    }

    // Perform your action here
    console.log("PID: " + pid);
    console.log("Verify Status: " + verifyStatus);
    console.log("Reason: " + reason);

    $.ajax({
        url: action,  
        method: 'POST',
        data: {
            _token: token,
            proposal_id: proposal_id,
            pid: pid,
            verify_status: verifyStatus,
            reason: reason
        },
        beforeSend: function(){
            show_msg(3, '', 'Please wait...', 4);
        },
        success: function(response) {
            console.log(response);
            var status = response.status;
            var message = response.message;
            show_msgT(status, message);
            if(status == '1'){
                imageList();
            }
            Swal.close();
        },
        error: function(error) {
            // Handle error response
            console.log(error);
        }
    });
});
/////////////////////////////////////////////////////////////////////
$(document).on('click', '.verify', function() {
    var vl = $(this).val();
    var div = $(this).closest('.divVerify');
    div.find('.reason').attr('readonly', 'readonly');
    if (vl == '2') {
        div.find('.reason').removeAttr('readonly');
    }
});
/////////////////////////////////////////////////////////////////////
$(document).on('click', '.googleMapView', function() {
    var mapUrl = $(this).attr('data-url');
    window.open(mapUrl, '_blank');
    //var mdl = $('#mdlGoogleMap');
    //mdl.find('.bodyViewGoogleMap').html(mapUrl);
    //mdl.modal('show');
});