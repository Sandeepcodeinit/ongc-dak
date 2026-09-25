var inputField = document.querySelector('.agency_csr');
// Prevent users from deleting the "CSR" prefix
inputField.addEventListener('input', function() {
    var prefix = 'CSR';
    if (!this.value.startsWith(prefix)) {
        this.value = prefix + this.value.substring(prefix.length);
    }
});

// Make feild required
var getVal = $('select[name="agency_reg_type"]').val();
if(getVal == 'A' || getVal == 'D')
{
    $('.divRegSoHd select, .divRegSoHd input').prop('disabled', false);
    $('.textRequred').text('*');
}

$('select[name="agency_reg_type"]').change(function () {
    var getVal = $(this).val();
    if (getVal == 'A' || getVal == 'D') {
        $('.divRegSoHd select, .divRegSoHd input').prop('disabled', false); 
        $('.textRequred').text('*');
    } else {
        $('.divRegSoHd select, .divRegSoHd input').prop('disabled', true);
        $('.textRequred').text('');
    }
});

// Trigger the change event initially to set the initial state based on the current value
$('select[name="agency_reg_type"]').trigger('change');


window.stepper = new Stepper(document.querySelector('.bs-stepper'));
var stepper = new Stepper(document.querySelector('.bs-stepper'));
//window.stepper.to(3);
function openTab(thiss, n_p){
    $('#custom-tabs-two-tabContent').find('.tab-pane').removeClass('active show');
    $('#custom-tabs-two-tab').find('.nav-link').removeClass('active');
    var div = $(thiss).closest('.tab-pane');
    var idd = div.attr('id');
    var lii = $('#'+idd+'-tab').closest('.nav-item');
    if(n_p === 'next'){
        var divId = lii.next('.nav-item').find('.nav-link').attr('data-divId');
        lii.next('.nav-item').find('.nav-link').addClass('active');
    }else{
        var divId = lii.prev('.nav-item').find('.nav-link').attr('data-divId');
        lii.prev('.nav-item').find('.nav-link').addClass('active'); 
    }
    $(divId).addClass('active show');
}
$('.btnFrmNext').on('click', function(){
    openTab(this, 'next');
});
$('.btnFrmPrev').on('click', function(){
    openTab(this, 'prev');
});
///////////////////////////////////////////////////////////////////////////////////////////////
pa = {
    viewFile: function(thiss, file, name, field_name) {
        $('.paPage').find('.activeFile').removeClass('activeFile');
        $(thiss).closest('div').addClass('activeFile');
        if (field_name.trim() == '') {
            show_msgT(2, 'File/field name not set properly.');
        }
        var agency_id = $('.agency_id').val();
        var user_type = $('.user_type').val();

        user_type = parseInt(user_type);
        //console.log(user_type);
        var mdl = $('#mdlShowFiles');
        mdl.find('.modal-title').html(name);
        mdl.find('.showFileSrc').attr('src', file);
        mdl.modal('show');
    },
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
    
    // viewFile:function(file, name){
    //     var mdl = $('#mdlShowFiles');
    //     mdl.find('.modal-title').text(name);
    //     mdl.find('.showFileSrc').attr('src', file);
    //     mdl.modal('show');
    // },
    viewDoc:function(thiss){
        var verified = $(thiss).attr('data-verified');
        var remarks = $(thiss).attr('data-remarks');
        var agency_id = $('.agency_id').val();
        var div = $(thiss).closest('.divFile');
        var file = div.find('.fileUpload').attr('data-file');
        var name = div.find('.fileUpload').attr('data-fileName');
        var showyn = $(thiss).attr('data-showyn');
        var field_name = div.find('.fileUpload').attr('name');

        $('.paPage').find('.activeFile').removeClass('activeFile');
        $(thiss).closest('div').addClass('activeFile');
        if (field_name.trim() == '') {
            show_msgT(2, 'File/field name not set properly.');
        }
        var agency_id = $('.cd').val();
        var user_type = $('.user_type').val();
        user_type = parseInt(user_type);
        console.log("showyn="+showyn);
        var mdl = $('#mdlShowFiles');
        mdl.find('.showFileSrc').attr('src', '');
        mdl.find('.verifyDoc').css('display', 'none');
        mdl.find('.modal-title').html(name);
        mdl.find('.showFileSrc').attr('src', file);
        if ((user_type == 5 || user_type == 6) && showyn != 'N' ) {
            mdl.find('.verifyDoc').css('display', 'block');
            mdl.find('.reason').attr('readonly', 'readonly');
            mdl.find('.verify').prop('checked', false);
            mdl.find('.verify[value="'+verified+'"]').prop('checked', true);
            mdl.find('.reason').attr('value', remarks).val(remarks);
            mdl.find('.doc_type').attr('value', 'agency').val('agency');
            if(verified == '0'){
                mdl.find('.reason').removeAttr('readonly');
            }
            mdl.find('.agency_id').attr('value', agency_id).val(agency_id);
            mdl.find('.field_name').attr('value', field_name).val(field_name);
        }
        $('.prop_agn_id').attr('value', 0).val(0);
        mdl.modal('show');
    },
    price_in_words: function(price) {
        var priceAll    = price.split('.');
        var price       = priceAll[0];
        var decimal     = priceAll[1];
        price           = parseInt(price);
        decimal         = parseInt(decimal);
        var sglDigit = ["Zero", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine"];
        var dblDigit = ["Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
        var tensPlace = ["", "Ten", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
        var handle_tens = function(dgt, prevDgt) {
          return 0 == dgt ? "" : " " + (1 == dgt ? dblDigit[prevDgt] : tensPlace[dgt])
        };
        var handle_utlc = function(dgt, nxtDgt, denom) {
          return (0 != dgt && 1 != nxtDgt ? " " + sglDigit[dgt] : "") + (0 != nxtDgt || dgt > 0 ? " " + denom : "")
        };

        var str = "";
        var digitIdx = digit = nxtDigit = 0;
        var words = [];
        if (price += "", isNaN(parseInt(price))) str = "";
        else if (parseInt(price) > 0 && price.length <= 10) {
            for (digitIdx = price.length - 1; digitIdx >= 0; digitIdx--) 
                switch (digit = price[digitIdx] - 0, nxtDigit = digitIdx > 0 ? price[digitIdx - 1] - 0 : 0, price.length - digitIdx - 1) {
              case 0:
                words.push(handle_utlc(digit, nxtDigit, ""));
                break;
              case 1:
                words.push(handle_tens(digit, price[digitIdx + 1]));
                break;
              case 2:
                words.push(0 != digit ? " " + sglDigit[digit] + " Hundred" + (0 != price[digitIdx + 1] && 0 != price[digitIdx + 2] ? " and" : "") : "");
                break;
              case 3:
                words.push(handle_utlc(digit, nxtDigit, "Thousand"));
                break;
              case 4:
                words.push(handle_tens(digit, price[digitIdx + 1]));
                break;
              case 5:
                words.push(handle_utlc(digit, nxtDigit, "Lakh"));
                break;
              case 6:
                words.push(handle_tens(digit, price[digitIdx + 1]));
                break;
              case 7:
                words.push(handle_utlc(digit, nxtDigit, "Crore"));
                break;
              case 8:
                words.push(handle_tens(digit, price[digitIdx + 1]));
                break;
              case 9:
                words.push(0 != digit ? " " + sglDigit[digit] + " Hundred" + (0 != price[digitIdx + 1] || 0 != price[digitIdx + 2] ? " and" : " Crore") : "")
            }
            str = words.reverse().join("")
        } else str = "";
        /*
        var decimalWords = "";
        if (decimal > 0) {
            decimalWords = " And";

            for (var i = 0; i < decimal.length; i++) {
                decimalWords += " " + sglDigit[parseInt(decimal[i])];
            }
            decimalWords = decimalWords+" Paisa";
        }*/
        return str;
    },
    docConfirmList: function(thiss) {
        if ($('.docVS.bg-info').length > 0) {
            var message = "Please verify/unverify all files.";
            show_msgT(2,message);
            return false;
        }else{
            $('.docListShow').attr('style', 'display: none !important');
            var id = $('.agency_id').val();
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
                        doc_type: 'agency'
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
    todoList: function(thiss) {
        var mdl = $('#mdlToDo');
        mdl.modal('show');
    },
    todoSave: function() {
        var mdl = $('#mdlToDo');
        var frm = $('.frmToDo');
        var formData = frm.serialize();
        var url = frm.attr('action');

        $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        success: function (response) {
            console.log(response);
            var status  = response.status;
            var message = response.message;
            show_msgT(status, message);
        },
        error: function (error) {
            console.log(error);
        }
    });
    },
}
//////////////////////////////////////////////////////////////////////////
$('.amtProposed').each(function(index) {
    var amt = $(this).attr('data-amt');
    var amt = parseFloat(amt).toFixed(2);
    if(amt > 0){
        var amtWord = pa.price_in_words(amt);
        $(this).text(amtWord);
    }
});
////////////////////////////////////////////////////////////////////////////////////////
$('.project_cost').on('keyup', function(){
    var amt = $(this).val();
    if (/^\d+\.\d{2,}$/.test(amt)) {
        amt = parseFloat(amt).toFixed(2);
        $(this).val(amt);
    }
    var amtInWord = pa.price_in_words(amt);
    $(this).closest('td, div').find('.amtInTxt').text(amtInWord);

    var amt = $('.total_project_cost').val();
    var amtInWord = pa.price_in_words(amt);
    $('.ttlAmtTxt').text(amtInWord);
});
$('#total_opex_amount').trigger('keyup');
$('#total_capex_amount').trigger('keyup');
////////////////////////////////////////////////////////////////////////////////////////////////
$('.divPro').on('click', function(){
    var divClass = $(this).attr('data-class');
    var ckInput  = $(this).prop('checked');
    //console.log(ckInput);
    //$('.divProCivil, .divProProcurement').css('display', 'none');
    var showCss = 'none';
    if(ckInput){
        showCss = 'block';
    }
    $(divClass).css('display', showCss);
});
/////////////////////////////////////////////////////////////////////////////
$('.btn_aboutAgency').click(function () {
    $('.input-error').removeClass('input-error');
    var formData = new FormData($('.frm_aboutAgency')[0]);
    var url = $('.frm_aboutAgency').attr('action');
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
            console.log(response);
            var status  = response.status;
            var message = response.message;
            var idd     = response.idd;
            var url     = response.url;
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
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                var paPage = $('.paPage').attr('date-ae');
                $('.cd, .agencyId').attr('value', idd).val(idd);
                //stepper.next();
                if(parseInt(paPage) <= 0 || paPage.trim() == ''){
                    sessionStorage.setItem("nextStepAgency", 1);
                    window.location = url;
                }else{
                    stepper.next();
                }
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
});
/////////////////////////////////////////////////////////////////////////////
$('.btn_financialDetails').click(function () {
    $('.input-error').removeClass('input-error');
    var formData = new FormData($('.frm_financial_details')[0]);
    var url = $('.frm_financial_details').attr('action');
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
            console.log(response);
            var status  = response.status;
            var message = response.message;
            var error = response.error;
            var url = response.url;
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
            if(status == '1'){
                show_msg(status, url, message, status);
            }else{
                show_msgT(status, message);
                Swal.close();
            }
            /*
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                stepper.next();
                window.location.href = '/agency.index';
            }*/
        },
        error: function (error) {
            console.log(error);
        }
    });
});
/////////////////////////////////////////////////////////////////////////////////
setTimeout(() => {
}, 1000);
///////////////////////////////////////////
function amountAllocate(tr_cls, input_cls) {
    var total_project_cost = parseFloat($('.total_project_cost').val()) || 0;
    var total_beneficiary = parseFloat($(input_cls+'total').val()) || 0;
    var divide = total_project_cost/total_beneficiary;
    var total = 0;
    $(tr_cls).each(function () {
       var number = parseFloat($(this).find(input_cls+'no').val());
       if(number>0){
        var amount = divide*number;
        amount = amount.toFixed(2);
        if(isNaN(amount) || !isFinite(amount) || amount <= 0){
            amount = 0;
        }
        $(this).find(input_cls+'amount').val(amount);
       }else{
        $(this).find(input_cls+'amount').val('');
       }
    });
    if(tr_cls == '.tr_target'){
        var other_no = $('.other_no').val();
        var amount = divide * other_no;
        amount = amount.toFixed(2);
        if(isNaN(amount) || !isFinite(amount) || amount <= 0){
            amount = 0;
        }
        $(tr_cls).find('.other_amt').attr('value', amount).val(amount);
    }
}
function updateTotalBeneficiary() {
    var total = 0;
    $('.benefeciary_no').each(function () {
      var inputValue = parseInt($(this).val()) || 0;
      total += inputValue;
    });
    if(isNaN(total) || !isFinite(total) || total <= 0){
        total = 0;
    }
    //console.log(total);
    $('.total_benefeciary_no, .other_no').val(total);
    var total_project_cost = $('.total_project_cost').val();
    total_project_cost = parseFloat(total_project_cost);
    if(isNaN(total_project_cost) || !isFinite(total_project_cost) || total_project_cost <= 0){
        total_project_cost = 0;
    }
    $('.other_amt').attr('value', total_project_cost).val(total_project_cost);
    amountAllocate('.tr_benefeciary', '.benefeciary_');
    updateTotalTarget();
    //amountAllocate('.tr_target','.focused_target_');
}
$('.benefeciary_no').on('input', function () {
    updateTotalBeneficiary();
});
//////////////////////////////////////////////////////////
function updateProjectCost() {
    var total = 0;
    $('.project_cost').each(function () {
      var inputValue = parseInt($(this).val()) || 0;
      total += inputValue;
    });
    if(isNaN(total) || !isFinite(total) || total <= 0){
        total = 0;
    }
    $('.total_project_cost').val(total);
    amountAllocate('.tr_benefeciary', '.benefeciary_');
    amountAllocate('.tr_target','.focused_target_');
}
$('.project_cost').on('input', function () {
    updateProjectCost();
   // amountAllocate('.tr_benefeciary','.benefeciary_');
});
// target
    function updateTotalTarget() {
        var total = 0;
        $('.focused_target_no').each(function () {
            var inputValue = parseInt($(this).val()) || 0;
            total += inputValue;
        });
        //$('.total_focused_target_no').val(total);
        var total_benefeciary_no = $('.total_benefeciary_no').val();
        var other = parseInt(total_benefeciary_no) - parseInt(total);
        if(isNaN(other) || !isFinite(other) || other <= 0){
            other = 0;
        }
        if(isNaN(total_benefeciary_no) || !isFinite(total_benefeciary_no) || total_benefeciary_no <= 0){
            total_benefeciary_no = 0;
        }
        $('.other_no').val(other);
        $('.total_focused_target_no').val(total_benefeciary_no);
        // amountAllocate('.tr_benefeciary');
        amountAllocate('.tr_target','.focused_target_');
    }
    $('.focused_target_no').on('input', function () {
        updateTotalTarget();
        var number = $(this).val();
        var total_focused_target = $('.total_focused_target_no').val();
        var total_benefeciary = $('.total_benefeciary_no ').val();
        if(parseInt(total_focused_target) > parseInt(total_benefeciary)){
            number = parseInt(total_benefeciary) - (parseInt(total_focused_target) - parseInt(number));
            if(isNaN(number) || !isFinite(number) || number <= 0){
                number = 0;
            }
            $(this).val(number);
            updateTotalTarget();
        }
    });

    document.addEventListener('input', function (event) {
    if (event.target.classList.contains('benefeciary_no')) {
        //console.log(event.target.value);
      var enteredValue = event.target.value;
      if (enteredValue.length > 8) {
        enteredValue = enteredValue.slice(0, 8);
        event.target.value = enteredValue;
      }
    }   
    }); 

    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('focused_target_no')) {
            //console.log(event.target.value);
          var enteredValue = event.target.value;
          if (enteredValue.length > 8) {
            enteredValue = enteredValue.slice(0, 8);
            event.target.value = enteredValue;
          }
        }   
    });
///////////////////////////////////////////////////////////////////////

$(document).ready(function(){
    $('.dayCal').on('change', function(){
        var start = $('#tant_start_date').val();
        var end = $('#tant_end_date').val();

        if (start && end) {
            var startDate = new Date(start);
            var endDate = new Date(end);

            var diffMilliseconds = endDate - startDate;
           // console.log(diffMilliseconds);

            if (diffMilliseconds < 0) {
                show_msgT(2, 'End Date is smaller then the Start Date. Please select valid Start & End Date.');
                $('#tant_end_date').val('');
                $('#project_time, .proj_timeline').val('');
            } else {

                var diffYears = Math.floor(diffMilliseconds / (365.25 * 24 * 60 * 60 * 1000));
                var diffMonths = Math.floor((diffMilliseconds % (365.25 * 24 * 60 * 60 * 1000)) / (30.44 * 24 * 60 * 60 * 1000));
                var remainingDays = Math.floor((diffMilliseconds % (30.44 * 24 * 60 * 60 * 1000)) / (24 * 60 * 60 * 1000));

                var formattedDifference = '';

                if (diffYears > 0) {
                    formattedDifference += diffYears +  ' years, ';
                }
                if (diffMonths > 0) {
                    formattedDifference += diffMonths + ' months, ';
                }
                if (remainingDays >= 0) {
                    formattedDifference += remainingDays + ' days';
                }
                $('#project_time, .proj_timeline').val(formattedDifference.trim());
            }
        }
    });
});
////////////////////////////////////////////////////////////////
$('.gst_type').on('change', function(){
  var div = $(this).closest('.form-group');
  var vl = $(this).val();
  if(vl == 'GS'){
    $('.divGstNum').css('display', 'flex');
    div.find('.preNum, .postNum').removeAttr('readonly');
  }else{
    $('.divGstNum').css('display', 'none');
    div.find('.preNum, .postNum').attr('readonly', 'readonly');
  }
});
//////////////////////////////////////////////////////////
var nextStepAgency = sessionStorage.getItem("nextStepAgency");
if(nextStepAgency == '1'){
    stepper.next();
    sessionStorage.setItem("nextStepAgency", '');
}
//////////////////////////////////////////////

$(".postNum, .preNum").css('text-transform', 'uppercase');
$(".preNum, .postNum").on('keyup', function(){
    var div = $(this).closest('.input-group');
    var preNum      = div.find('.preNum').val();
    var postNum     = div.find('.postNum').val();
    var user_pan    = div.find('.user_pan').text();
    if(preNum == '' || preNum == undefined){
        preNum = '';
    }
    if(postNum == '' || postNum == undefined){
        postNum = '';
    }
        preNum      = preNum.toUpperCase().trim();
        postNum     = postNum.toUpperCase().trim();
        user_pan    = user_pan.toUpperCase().trim();
    var fullNumber  = preNum+user_pan+postNum;
    //console.log(fullNumber);

    div.find('.fullNumber').attr('value', fullNumber).val(fullNumber);

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
            console.log("Error : "+error);
            show_msgT(status, message);
            if(status == '1'){
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

////////////////////////////////////////////////////////////
$('.financial_audit').on('change', function(){
    $('.financial_audit').find('option').removeAttr('style');
    $('.financial_audit').each(function(index){
        var value = $(this).val();
        $('.financial_audit').find('option[value="'+value+'"]').css('display', 'none');
        $('.financial_audit').find('option[value=""]').removeAttr('style');
    });
});
////////////////////////////////////////////////////

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
    //console.log(verify);

    $.ajax({
        type: "post",
        url: url,
        data: formData,
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
                $('.activeFile').find('.docVS').removeClass('bg-success bg-danger bg-info').addClass(verifyClas);
                /// .text(verifyText)
                mdl.modal('hide');
            }
            show_msgT(status, message);
            btnEventChange();
        },
        error: function(responseData) {
            console.log(responseData);
        }
    });
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

////////////////////////////////////////////////
$('.btnAgencySave').click(function() {
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
                    var list_url = $('.divMainAgnAdd').attr('data-ag_list');
                    window.location = list_url;
                    // it must be redirect to index page.
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
////////////////////////////////////////////////

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

////////////////////////////////////////////////////////////////////////////
$(document).ready(function(){
    var getUserType = $(".user_type").val();
    var agencyVerified = $(".agencyVerified").val();
    
    if(getUserType == 99 && agencyVerified == 1)
    {
        $(".doc-update").remove();
        $(".fileUpload, input, select, textarea").prop('disabled', true);
    } 
})

////////////////////////////////////////////////////////////////////////////
// Html/text count js start
function btnEventChange() {
    var targetClass = 'docVS';
    var elements = document.getElementsByClassName(targetClass);
    var len_verify = emptyCount = 0;

    for (var i = 0; i < elements.length; i++) {
        if (elements[i].innerHTML.trim() === '') {
            emptyCount++;
        }
    }
    
    if (emptyCount > 0) {
        $('.btnDocConfirm').prop('disabled', true);
    } else {
        $('.btnDocConfirm').prop('disabled', false);
    }
    /*
    var len_docVS = elements.length;
    var len_verify = $('.docVS.text-success').length;
    $('.btnAgencyVerify').prop('disabled', true);
    if(len_docVS == len_verify){
        $('.btnAgencyVerify').prop('disabled', false);
    }
    */
    // If any documents un-verify then only disaply "Communicate to agency"
    var un_len_verify = $('.docVS.bg-danger').length;
    if(un_len_verify == 0)
    {
        $('.btnDocConfirm').prop('disabled', true);
    }else{
        $('.btnDocConfirm').prop('disabled', false);
    }
}
btnEventChange();

$('.btnAgencyVerify').on('click', function(){
    var mdl = $("#myModal");
    // data-toggle="modal" data-target="#myModal"
    var len_docVS = $('.docVS').length;
    var len_verify = $('.docVS.bg-success').length;
    //jconsole.log("len_docVS="+len_docVS+", len_verify="+len_verify);
    if(len_docVS != len_verify){
        Swal.fire({
           icon: "warning",
           showCloseButton: false,
           showCancelButton: true,
           showConfirmButton: true,
           html: "Are you sure you want to forward this Application without Verifying all the documents..?",
           confirmButtonText: "Yes Forward",
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
////////////////////////////////////////////////////
pa.enbDisEngFile('#agency_reg_eng', '.agency_reg_eng_file');
pa.enbDisEngFile('#agency_deed_eng', '.agency_deed_eng_file');
pa.enbDisEngFile('#agency_socity_eng', '.agency_socity_eng_file');


function toggleFileUpload(checkbox, fileSelector) {
    const fileInput = document.querySelector(fileSelector);
    if (checkbox.checked) {
        fileInput.removeAttribute('readonly');
    } else {
        fileInput.setAttribute('readonly', 'readonly');
    }
}

function convertToUpperCase(input) {
    var string = input.value.toUpperCase();
    input.value = string;
    string = string.trim();

    $('.cnf_agency_name').html(string);
}
//////////////////////////////////////////////////////////////////////////
/*
function keyPersonsTrustees() {
    var newCount = parseInt($('#no_of_persons').val());
    var container = $('#person_container');
    var currentCount = container.children('.person-row').length;
    var dbRecordsCount = container.find('.saved_record').length || 0;

    if (!newCount) {
        container.empty();
        return;
    };

    // 🔼 IF INCREASE
    if (newCount > currentCount) {
        for (var i = currentCount; i < newCount; i++) {
            container.append(generateRow(i));
        }
    }

    // 🔽 IF DECREASE
    if (newCount < currentCount) {
        if (dbRecordsCount > 0 && newCount < dbRecordsCount) {
            Swal.fire({
            icon: 'warning',
            //title: 'Oops...',
            showCloseButton: false,
            showCancelButton: true,
            showConfirmButton: true,
            html: 'Reducing number will remove extra persons.<br>Do you want to continue?',
            confirmButtonText: 'Yes, Continue',
            cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.value) {
                    container.children('.person-row').slice(newCount).remove();
                }else{
                    $('#no_of_persons').val(currentCount);
                    return;
                }
            }); 
        }else{
            container.children('.person-row').slice(newCount).remove();
        }
    }
}

function generateRow(index) {
    var srNo = index + 1;
    return `<div class="col-sm-12 input-group input-group-sm mt-1 pr-3 person-row">
        <input type="hidden" name="persons[${index}][id]" value="">
        <div class="input-group-prepend">
            <span class="input-group-text">#${srNo}.</span>
        </div>
        <div class="input-group-prepend">
            <span class="input-group-text">PAN No.<i class="text-danger">*</i></span>
        </div>
        <input type="text" name="persons[${index}][pan]" class="form-control form-control-sm col-2 pan_no alpnum person_pan" placeholder=" Enter PAN No." data-placeholder=" Enter PAN No." required maxlength="10" oninput="convertToUpperCase(this)">

        <div class="input-group-prepend">
            <span class="input-group-text">Aadhaar No.<i class="text-danger">*</i></span>
        </div>
        <input type="text" name="persons[${index}][aadhaar]" class="form-control form-control-sm col-3 int person_aadhaar" placeholder=" Enter Aadhaar No." required maxlength="12">
        <div class="input-group-prepend">
            <span class="input-group-text">Name</span>
        </div>
        <input type="text" name="persons[${index}][name]" class="form-control form-control-sm char person_name" placeholder=" Enter Name">
    </div>`;
}*/
