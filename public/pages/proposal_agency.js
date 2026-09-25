$(document).ready(function() { 
    var currentUrl = window.location;
    
    var currentUrl = window.location.href;
    var docs_expired = $('.divProposalCardBody').attr('data-docs_expired');
        docs_expired = parseInt(docs_expired);
    // Check if the URL contains 'add'
    if (currentUrl.includes('add') && docs_expired != 1) {
        // Execute the showPopup function when the page finishes loading
        $('#myModalConfirmReload').modal('show');
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

// Display aspirational district placeholder
$(document).ready(function() {
    $('.aspirational_district_id').select2({
        placeholder: "Select Aspirational District(s)"
    });
});

// Display aspirational district if FPR select 'yes'
// $('select[name="is_aspirational_district"]').change(function () {
//     var getDistrictVal = $(this).val();

//     if (getDistrictVal == 1) {
//         $('.aspirational_district_id').prop('disabled', false);
//     }
//     else{
//         $('.aspirational_district_id').prop('disabled', true);
//     }
// });

// Trigger the change event initially to set the initial state based on the current value
$('select[name="agency_reg_type"]').trigger('change');


window.stepper = new Stepper(document.querySelector('.bs-stepper'));
var stepper = new Stepper(document.querySelector('.bs-stepper'));

function openTab(thiss, n_p){
    $('#custom-tabs-two-tabContent').find('.tab-pane').removeClass('active show');
    $('#custom-tabs-two-tab').find('.nav-link').removeClass('active');
    var div = $(thiss).closest('.tab-pane');
    var idd = div.attr('id');
    var lii = $('#'+idd+'-tab').closest('.nav-item');
    //lii.next('.nav-item').find('.nav-link').trigger('click');
    //lii.prev('.nav-item').find('.nav-link').trigger('click');
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
    addVillage:function(thiss, from=''){
        var propp_id = $('.cd').val();  
        var div = $(thiss).closest('.addVillage');
        var pincode = div.find('.pincode').val();
        var state_cd = div.find('.state_cd').val();
        //var state_nm = div.find('.pincode option:selected').text();
        var district_cd = div.find('.district_cd').val();
        //var district_nm = div.find('.district_cd option:selected').text();
        var block_cd = div.find('.block_cd').val();
        //var village_nm = div.find('.village_cd option:selected').text();
        var village_name = div.find('.village_name').val();

        var budget_percent = div.find('.budget_percent').val();
        
        var url = div.attr('data-url');
        var token = $('.frm_about_project').find('input[name="_token"]').val();
        $.ajax({
            async: false,
            url: url,
            type: 'POST',
            data: { _token: token, pincode:pincode, state_cd:state_cd, district_cd:district_cd, block_cd:block_cd, propp_id:propp_id, village_name:village_name,budget_percent:budget_percent },
            dataType: 'json',
            beforeSend: function() {
                show_msg(3, '', '<b>Please wait...</b>', 4);
            },
            success: function (response) {
                var status  = response.status;
                var message = response.message;
                if(from != 'sv'){
                    show_msgT(status, message);
                }
                if(status == '1'){
                    $('.addVillage').find('.village_name').val('');
                    $('.addVillage').find('.pincode').val('');
                    $('.addVillage').find('.state_cd, .select2').val('');
                    $('.addVillage').find('.state_cd').trigger('change');
                    $('.addVillage').find('.district_cd').val('').trigger('change');
                    $('.addVillage').find('.block_cd').val('').trigger('change');
                    $('.addVillage').find('.budget_percent').val('');
                    pa.listVillage();
                }
                Swal.close();
            }
        });
    },
    listVillage:function(){
        var div = $('.addVillage');
        var url = div.attr('data-url2');
        var token = $('.frm_about_project').find('input[name="_token"]').val();
        var propp_id = $('.cd').val();
        $.ajax({
            async: false,
            url: url,
            type: 'POST',
            data: { _token: token, propp_id:propp_id },
            dataType: 'json',
            success: function (response) {
                //console.log("response="+JSON.stringify(response));
                $('.tFootVillageList').html(response.villages);
                Swal.close();
            }
        });
    },
    removeVillage:function(thiss){
        var trr = $(thiss).closest('tr');
        var row_id = trr.attr('data-id');
        var div = $('.tFootVillageList');
        var url = div.attr('data-link');
        var token = $('.frm_about_project').find('input[name="_token"]').val();
        var propp_id = $('.cd').val();
        $.ajax({
            url: url,
            type: 'POST',
            data: { _token: token, propp_id:propp_id, row_id:row_id },
            beforeSend: function() {
                show_msg(3, '', '<b>Please wait...</b>', 4);
            },
            success: function (response) {
                var status  = response.status;
                var message = response.message;
                if(status == '1'){
                    pa.listVillage();
                }
                Swal.close();
            }
        });
    },
    viewFile:function(file, name){
        var mdl = $('#mdlShowFiles');
        mdl.find('.modal-title').html(name);
        mdl.find('.showFileSrc').attr('src', file);
        mdl.modal('show');
    },
    viewDoc:function(thiss){
        var mdl = $('#mdlShowFiles');
        mdl.find('.showFileSrc').attr('src', '').removeAttr('src');
        var verified = $(thiss).attr('data-verified');
        var remarks = $(thiss).attr('data-remarks');
        var agency_id = $('.agency_id').val();
        var div = $(thiss).closest('.divFile');
        var file = div.find('.fileUpload').attr('data-file');
        var name = div.find('.fileUpload').attr('data-fileName');
        var field_name = div.find('.fileUpload').attr('name');

        $('.paPage').find('.activeFile').removeClass('activeFile');
        $(thiss).closest('div').addClass('activeFile');
        if (field_name.trim() == '') {
            show_msgT(2, 'File/field name not set properly.');
        }
        var proposal_id = $('.prop_id').val();
        var user_type = $('.user_type').val();
        user_type = parseInt(user_type);
        //console.log(user_type);
        mdl.find('.modal-title').html(name);
        setTimeout(function(){
            mdl.find('.showFileSrc').attr('src', file);
        }, 100);
        
        if (user_type == 5 || user_type == 6) {
            mdl.find('.verifyDoc').css('display', 'block');
            mdl.find('.reason').attr('readonly', 'readonly');
            mdl.find('.verify').prop('checked', false);
            mdl.find('.verify[value="'+verified+'"]').prop('checked', true);
            mdl.find('.reason').attr('value', remarks).val(remarks);
            if(verified == '0'){
                mdl.find('.reason').removeAttr('readonly');
            }
            mdl.find('.proposal_id').attr('value', proposal_id).val(proposal_id);
            mdl.find('.field_name').attr('value', field_name).val(field_name);
        }
        $('.prop_agn_id').attr('value', agency_id).val(agency_id);
        mdl.modal('show');
    },
    getPinCodeList:function(thiss, listtype){
        var val = $(thiss).val();
        if(parseInt(val) > 0){
            var url = $('.addVillage').attr('data-link');
            var token = $('.frm_about_project').find('input[name="_token"]').val();
            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: token, code:val, list_type:listtype },
                dataType: 'json',
                beforeSend: function() {
                    show_msg(3, '', '<b>Please wait...', 4);
                },
                success: function (response) {
                    //console.log(response.villageList);
                    var clsName = '.block_cd';
                    if(listtype == 'block'){
                        var clsName = '.block_cd';
                    }else if(listtype == 'district'){
                        var clsName = '.district_cd';
                    }
                    $(clsName).html(response.villageList).trigger('change:select2');
                    Swal.close();
                }
            });
        }
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
    getListAY:function(thiss, className, type) {
        /*
        var fr_year = to_year = select_yr = '';
        var yearRange = year = $(thiss).val();
            yearRange = yearRange.split('-');
        if(type == 'F'){
            fr_year = yearRange[1];
            fr_year = fr_year.trim();
        }else{
            to_year = yearRange[0];
            to_year = to_year.trim();
        }
        var token = $('input[name="_token"]').val();

        var link = $('.divFormProposal').attr('data-ayLink');
        $.ajax({
            type: 'POST',
            url: link,
            data: {fr_year : fr_year, to_year: to_year, select_yr: select_yr, _token: token },
            success: function (response) {
              //console.log(response);
              var lists = response.list;
              $(className).html(lists);
              $(className).trigger('change:select2');
            },
            error: function (xhr, status, error) {
              console.log('Error:', error);
            }
        });*/
    },
    docConfirmList: function(thiss) {
        if ($('.docVS.bg-info').length > 0) {
            var message = "Please verify/unverify all files.";
            show_msgT(2,message);
            return false;
        }
        else{
            var total_opex_amount = $('#total_opex_amount').val();
            var total_capex_amount = $('#total_capex_amount').val();
            var total_project_amt = parseFloat(total_opex_amount) + parseFloat(total_capex_amount);
                total_project_amt = total_project_amt.toFixed(2);
            if(total_project_amt <= 0){
                var msg = "Please enter B5. Project Processed Cost.";
                show_msgT(2, msg);
                activateTab('proposalDetails');
                $('#total_opex_amount').focus();
                return false;
            }
            $('.docListShow').attr('style', 'display: none !important');
            var prop_id = $('.prop_id').val();
            var agency_id = $('.agency_id').val();
            var mdl = $('#mdlDocConfirm');
            var url = mdl.attr('data-link');
            var token = mdl.find('input[name="_token"]').val();
            //console.log(url);
            if (parseInt(prop_id) > 0) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: token,
                        prop_id: prop_id,
                        id: agency_id
                    },
                    beforeSend: function() {
                        show_msg(3, '', '<b>Please wait...</b>', 4);
                    },
                    success: function(response) {
                        //console.log(response);
                        var list = response.result;
                        var pendingCount = response.pendingCount;
                        $('.liDocPendingList').html(list);
                        if(parseInt(pendingCount) > 0){
                            $('.docListShow').attr('style', 'display: inline-grid !important');
                        }
                        Swal.close();
                        mdl.modal('show');
                    }
                });
            }else{
                //$('.assign_to_fpr').html('').trigger('change:select2');
            }
        }
    },
    proposalReEdit: function() {
        var mdl = $('#mdlReEditDoc');
        var form = mdl.find('.frmReEditList');
        var url = form.attr('action'); 
        var token = form.find('input[name="_token"]').val(); 
        mdl.modal('show');
    
        // Handle form submission using AJAX
        form.off('submit').on('submit', function(e) { 
            e.preventDefault(); 
    
            var prop_id = $('.prop_id').val();
            var remarks = $('.re_edit_remarks').val();
    
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: token,
                    prop_id: prop_id,
                    remarks: remarks,
                },
                beforeSend: function() {
                    show_msg(3, '', '<b>Please wait...</b>', 4);
                },
                success: function(response) {
                    if (response && response.status !== undefined) {
                        var status  = response.status;
                        var message = response.message;
                        show_msgT(status, message);
    
                        if (status == 1) {
                            mdl.modal('hide');
                        }
                    } else {
                        console.error('Unexpected response format:', response);
                    }
                    Swal.close();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    show_msgT(2, 'An error occurred while processing your request.');
                }
            });
        });
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
            //console.log(response);
            var status  = response.status;
            var message = response.message;
            show_msgT(status, message);
        },
        error: function (error) {
            console.log(error);
        }
    });
    },
    vipTypeHS: function(thiss) {
        var val = $(thiss).val();
        var slt = $('.vip_constituency');
        var vip_co = slt.attr('data-vip_constituency');
        //vip_co = vip_co.trim();
        slt.val('');
        slt.find('.vipType').css('display', 'none');
        slt.find('.vipType').removeAttr('selected');
        slt.val('').attr('readonly', 'readonly');
        var cls = '';
        $('#name_vp').show();
        $('#div_vip_list').hide();
        //$('.constituency_name, .vip_name').val('');
        if (val == 'MLA') {
            cls = '.mla';
            if (vip_co != '' && (vip_co == 'VS' || vip_co == 'VP')) {
                slt.val(vip_co);
            }
            slt.removeAttr('readonly');
        } else if (val == 'MP') {
            cls = '.mp';
            if (vip_co != '' && (vip_co == 'LS' || vip_co == 'RS')) {
                slt.val(vip_co);
                $('.vip_constituency').trigger('change');
            }
            slt.removeAttr('readonly');
        }
        slt.find(cls).css('display', 'block');
    },
}
///////////////////////////////////////////////////////////////////////////
// Vip-name list script start
$(document).ready(function () {
    // Function to populate vip_name dropdown for 'LS'
    function populateVipNameDropdown(selectedVipType) {
        var url = $('.frmMoPNG').attr('data-urllist');
        $.ajax({
            type: 'GET',
            url: url,
            data: { vip_type: selectedVipType },
            success: function (data, response) {
                //console.log('data='+JSON.stringify((data)));
                //console.log('daresponseta='+response);
                var select = $('#vip_list');
                var vip_nameAdded = select.attr('data-vip_name');
                select.html('');
                $('.divCommitteeName').hide();
                select.append('<option value="">Select</option>');
                $.each(data, function (index, item) {
                    var commiteeNames = item.committee_names;
                    var date = item.date;
                    var status = item.status;
                    var vipData = [];
                    $.each(commiteeNames, function (key) {
                        var commitee = item.committee_names[key];
                        var date =  item.date[key];
                        var status =  item.status[key];
                        if(commitee == null || commitee == undefined){
                            commitee = 'Not available';
                        }
                        commiteeName = commitee+'||'+date+'||'+status;
                        if(status == 0)
                        {
                            vipData[key] = '<span class="text-danger">'+commitee+ '-' +date+'</span>';
                        }
                        else{
                            vipData[key] = '<span class="text-success">'+commitee+'</span>';
                        }
                    });
                    var vipData = vipData.join(', ');
                    var vipData = encodeURIComponent(vipData);
                    var constituencyName = item.constituency_name;
                    var isSelected = (item.vip_name == vip_nameAdded) ? 'selected' : '';
                    select.append('<option data-commiteeName="' + vipData + '" data-constituencyName="' + constituencyName + '" value="' + item.id + '" ' + isSelected + '>' + item.vip_name + '</option>');
                });

                select.trigger('select2:change');
                $('#div_vip_list').show();
                
                $('#name_vp').hide();
                $('#vip_list').trigger('change');
            },
            error: function () {
                console.error('Error fetching data.');
            }
        });
    }

    // On page load, check if the selected VIP type is 'LS'
    var selectedVipType = $('.vip_constituency').val();
    if (selectedVipType == 'LS'|| selectedVipType === 'RS') {
        // Call the function to populate the dropdown
        populateVipNameDropdown(selectedVipType);
    }
    //populateVipNameDropdown(selectedVipType);
    $('.vip_type').change(function () {
        var vip_type = $(this).val();
        var selectedVipType = $('.vip_constituency').val();
        //console.log("vip_type="+vip_type);
        if(vip_type == 'MP'){
            populateVipNameDropdown(selectedVipType);
        }else{
            $('#name_vp').show();
            $('#div_vip_list').hide();
            $('.constituency_name').val('');
        }
    });
    $('.vip_constituency').change(function () {
        var selectedVipType = $(this).val();
        
        var vip_type = $('.vip_type').val();
        if(vip_type == 'MP'){
            populateVipNameDropdown(selectedVipType);
        }else{
            $('#name_vp').show();
            $('#div_vip_list').hide();
            $('.constituency_name').val('');
        }
    });

    $('.vip_list').change(function () {
        var constituency = $(this).find('option:selected').attr('data-constituencyName');
        $('.constituency_name').attr('value', constituency).val(constituency);

        var commiteeName = $(this).find('option:selected').attr('data-commiteeName');
        $('.divCommitteeName').hide();
        if(commiteeName == undefined || commiteeName <= 0){
            commiteeName = '';
        }
        //console.log("commiteeName="+commiteeName);
        if (commiteeName == undefined || commiteeName == null) {
        } else {
            commiteeName = decodeURIComponent(commiteeName);
            $('.divCommitteeName').html('<span class="text-bold">Committee Name : </span>' + commiteeName);
            $('.divCommitteeName').show();
        }
    });

    $('.vip_type').change(function (){
        $('.divCommitteeName').hide();
    })
});

pa.vipTypeHS('.vip_type');
//////////////////////////////////////////////////////////////////////////
$('.amtProposed').each(function(index) {
    var amt = $(this).attr('data-amt');
    var amt = parseFloat(amt).toFixed(2);
    if(amt > 0){
        var amtWord = pa.price_in_words(amt);
        $(this).text(amtWord);
    }
});

/////// Project Cost Js ////
$('.project_cost').on('keyup', function () {
    var amt = $(this).val();

    // format to 2 decimals if needed
    if (/^\d+\.\d{2,}$/.test(amt)) {
        amt = parseFloat(amt).toFixed(2);
        $(this).val(amt);
    }

    var txtField = $(this).closest('td, div').find('.amtInTxt');

    // --- Validate against Project Amount ---
    var agencyAmt = parseFloat($('.proposed_cost').next().attr('data-amt')) || 0;
    var opex = parseFloat($('#total_opex_amount').val()) || 0;
    var capex = parseFloat($('#total_capex_amount').val()) || 0;
    var total = opex + capex;

    $('#project_cost').val(total); // update total

    var expendLimit = 30000000;
    var showExpendLimit = total;

    if (agencyAmt > 0 && total > agencyAmt) {
        show_msgT(2, "Entered amout cannot exceed Project Proposed Cost (" + agencyAmt + ")");
        $(this).val(0); // reset the last entered field

        // recalc again after reset
        opex = parseFloat($('#total_opex_amount').val()) || 0;
        capex = parseFloat($('#total_capex_amount').val()) || 0;
        $('#project_cost').val(opex + capex);
        showExpendLimit = agencyAmt;
    }
    if (agencyAmt > 1) {
        showExpendLimit = agencyAmt;
    }
    if (parseFloat(showExpendLimit) > parseFloat(expendLimit)) {
        $('.showExpendDiv').removeClass('d-none');
    }else{
        $('.showExpendDiv').addClass('d-none');
    }

    // --- Amount in words (if needed) ---
    var amtInWord = pa.price_in_words($(this).val());
    if(amtInWord == 'zero' || amtInWord == ''){
        amtInWord = '&nbsp;';
    }
    txtField.html(amtInWord);

    var ttlAmtInWord = pa.price_in_words($('#project_cost').val());
    if(ttlAmtInWord == 'zero' || ttlAmtInWord == ''){
        ttlAmtInWord = '&nbsp;';
    }
    $('.ttlAmtTxt').html(ttlAmtInWord);
});

// trigger initial calc
$('#total_opex_amount').trigger('keyup');
$('#total_capex_amount').trigger('keyup');


////////////////////////////////////////////////////////////////////////////////////////
// $('.project_cost').on('keyup', function(){
//     var amt = $(this).val();
//     if (/^\d+\.\d{2,}$/.test(amt)) {
//         amt = parseFloat(amt).toFixed(2);
//         $(this).val(amt);
//     }
//     var txtField = $(this).closest('td, div').find('.amtInTxt');
//     //// Below is used for check amt in FPR section ////
//     var processedAmt = $(this).hasClass('processedAmt');
//     var message = '';
//     if(processedAmt){
//         var input_id = $(this).attr('id');
//         if(input_id == 'total_opex_amount'){
//             agencyAmt = $('.agencyOpexAmt').attr('data-amt');
//             message = 'Processed opex amt must equal or less from propossed opex amt.';
//         }else if(input_id == 'total_capex_amount'){
//             agencyAmt = $('.agencyCapexAmt').attr('data-amt');
//             message = 'Processed capex amt must equal or less from propossed capex amt.';
//         }else{
//             agencyAmt = 0;
//             message = 'Processed amt not set.';
//         }
//         var agencyAmt = parseFloat(agencyAmt);
//         if(parseFloat(amt) > 0 && parseFloat(amt) > agencyAmt){
//             amt = parseFloat(agencyAmt).toFixed(2);
//             $(this).val(amt);
//             show_msgT(2, message);
//             //txtField.removeClass('text-success').addClass('text-danger');
//             //txtField.text(message);
//             //return false;
//         }
//     }
//     //// End above is used for check amt in FPR section ////
//     var amtInWord = pa.price_in_words(amt);
//     //txtField.removeClass('text-danger').addClass('text-success');
//     txtField.text(amtInWord);

//     var amt = $('.total_project_cost').val();
//     var amtInWord = pa.price_in_words(amt);
//     $('.ttlAmtTxt').text(amtInWord);
// });
// $('#total_opex_amount').trigger('keyup');
// $('#total_capex_amount').trigger('keyup');
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
$('.btn_aboutAgency').click(function () {
    var usertype = $('.divFormProposal').attr('data-usertype');
    var usertype = parseInt(usertype);
    var fileLen = $('#aboutAgency').find('.docVS.bg-info').length;
    //console.log('fileLen='+fileLen);
    if(parseInt(fileLen) > 0 && (usertype == 5 || usertype == 6)){
        show_msgT(2, "Please verify/unverify all files.");
        return false;
    }
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
            var idd     = response.idd;
            var url     = response.url;
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                var paPage = $('.paPage').attr('date-ae');
                $('.cd').attr('value', idd).val(idd);
                //stepper.next();
                if(paPage == 'add'){
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
    var usertype = $('.divFormProposal').attr('data-usertype');
    var usertype = parseInt(usertype);
    var fileLen = $('#financialDetails').find('.docVS.bg-info').length;
    if(parseInt(fileLen) > 0 && (usertype == 5 || usertype == 6)){
        show_msgT(2, "Please verify/unverify all files.");
        return false;
    }
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
            //console.log(response);
            var status  = response.status;
            var message = response.message;
            var error = response.error;
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
            console.log("Error : "+error);
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                stepper.next();
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
});
/////////////////////////////////////////////////////////////////////////////
$('.budget_percent').on('input', function() {
    var value = parseFloat($(this).val());
    if (value > 100) {
        $(this).val(100); // Set value to max if it exceeds 100
    } else if (value < 0) {
        $(this).val(0); // Set value to min if it is less than 0
    }
});

$('.btnAddVillage').click(function() {
    var totalAmount = 0;
    $('.budget_per').each(function() {
        var value = parseFloat($(this).text()); 
        if (!isNaN(value)) { 
            totalAmount += value; 
        }
    });
    totalAmount = parseFloat(totalAmount).toFixed(2);
    if (totalAmount == 100 ) {
        $('#errorMessage').text(''); 
    } 
})

$('.btn_aboutProject').click(function (event) {
    var usertype = $('.divFormProposal').attr('data-usertype');
    var usertype = parseInt(usertype);
    var fileLen = $('#tabBasicProposalDetails').find('.docVS.bg-info').length;
    //console.log('fileLen='+fileLen);
    if(parseInt(fileLen) > 0 && (usertype == 5 || usertype == 6)){
        show_msgT(2, "Please verify/unverify all files.");
        return false;
    }
    $('.input-error').removeClass('input-error');
    var pincode = $('.pincode').val();
    var state_cd = $('.state_cd').val();
    var district_cd = $('.district_cd').val();
    var budget_percent = $('.budget_percent').val();

    // Get Total budget Percent
    var totalAmount = 0;
    $('.budget_per').each(function() {
        var value = parseFloat($(this).text()); 
        if (!isNaN(value)) { 
            totalAmount += value; 
        }
    });
    
    // Check if totalAmount is less than 100 and display an error message
    //console.log("totalAmount"+totalAmount);
    totalAmount = parseFloat(totalAmount).toFixed(2);
    if (totalAmount != 100 ) {
        $('#errorMessage').text('Budget percent should be 100%.');
        event.preventDefault(); 
        return false;
    } else {
        $('#errorMessage').text(''); 
    }
  
    if(parseInt(pincode) > 0 && parseInt(state_cd) > 0 && parseInt(district_cd) > 0 ){
        pa.addVillage('.btnAddVillage', 'sv');
    }
    var tr_cnt  = $('.tFootVillageList').find('tr').length;
   // console.log(tr_cnt );
    if(parseInt(tr_cnt) < 1){
        show_msgT(2, "Add atleast one project location.");
        return false;
    }
    var thiss = this;
    var formData = new FormData($('.frm_about_project')[0]);
    var url = $('.frm_about_project').attr('action');
    var opex_amount = $('.frm_about_project').find('#total_opex_amount').val();
    var capex_amount = $('.frm_about_project').find('#total_capex_amount').val();
    var total_amount = $('.frm_about_project').find('#project_cost').val();
    $.ajax({
        async: false,
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
            var status  = response.status;
            var message = response.message;
            var error = response.error;
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
            console.log("Error : "+error);
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                openTab(thiss, 'next');
            }

            var amtWord = pa.price_in_words(opex_amount);
            $('.amtProcessedViewOpex').text(opex_amount);
            $('.amtViewProcessedOpex').attr('data-amt', opex_amount).text(amtWord);

            var amtWord = pa.price_in_words(capex_amount);
            $('.amtProcessedViewCapex').text(capex_amount);
            $('.amtViewProcessedCapex').attr('data-amt', capex_amount).text(amtWord);
            
            var amtWord = pa.price_in_words(total_amount);
            $('.amtProcessedViewTotal').text(total_amount);
            $('.amtViewProcessedTotal').attr('data-amt', total_amount).text(amtWord);

        },
        error: function (error) {
            console.log(error);
        }
    });
});
/////////////////////////////////////////////////////////////////////////////
$('.btn_organizationDetails').click(function () {
    var usertype = $('.divFormProposal').attr('data-usertype');
    var usertype = parseInt(usertype);
    var fileLen = $('#tabOrganizationDetails').find('.docVS.bg-info').length;
    //console.log('fileLen='+fileLen);
    if(parseInt(fileLen) > 0 && (usertype == 5 || usertype == 6)){
        show_msgT(2, "Please verify/unverify all files.");
        return false;
    }
    $('.input-error').removeClass('input-error');
    var thiss = this;
    var formData = new FormData($('.frm_organizationDetails')[0]);
    var url = $('.frm_organizationDetails').attr('action');
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
            var status  = response.status;
            var message = response.message;
            var error = response.error;
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
            console.log("Error : "+error);
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                openTab(thiss, 'next');
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
});
/////////////////////////////////////////////////////////////////////////////
$('.btn_projectDetails').click(function () {
    var usertype = $('.divFormProposal').attr('data-usertype');
    var usertype = parseInt(usertype);
    var fileLen = $('#tabProjectDetails').find('.docVS.bg-info').length;
    //console.log('fileLen='+fileLen);
    if(parseInt(fileLen) > 0 && (usertype == 5 || usertype == 6)){
        show_msgT(2, "Please verify/unverify all files.");
        return false;
    }
    $('.input-error').removeClass('input-error');
    var thiss = this;
    var formData = new FormData($('.frm_projectDetails')[0]);
    var url = $('.frm_projectDetails').attr('action');
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
            var status  = response.status;
            var message = response.message;
            var error = response.error;
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
            console.log("Error : "+error);
            show_msgT(status, message);
            Swal.close();
            if(status == '1'){
                openTab(thiss, 'next');
            }
        },
        error: function (error) {
            console.log(error);
        }
    });
});
/////////////////////////////////////////////////////////////////////////////
$('.btn_civilProcurement').click(function () {
    var usertype = $('.divFormProposal').attr('data-usertype');
    var usertype = parseInt(usertype);
    var fileLen = $('#tabCivilProcurement').find('.docVS.bg-info').length;
    var fileUnvr = $('.bs-stepper-content').find('.docVS.bg-danger').length;
    var fileAll = parseInt(fileLen) + parseInt(fileUnvr);
    //console.log('fileLen='+fileLen+', fileUnvr='+fileUnvr);
    if(parseInt(fileAll) > 0 && (usertype == 5 || usertype == 6)){
        show_msgT(2, "Please verify all files of this proposal.");
        return false;
    }
    var u_type = $('#tabCivilProcurement').attr('data-u_type');
    var u_type = parseInt(u_type);
    var save_form = 0;
    if(u_type == 5 || u_type == 6){
        Swal.fire({
            icon: "warning",
            //title: 'Oops...',
            showCloseButton: false,
            showCancelButton: true,
            showConfirmButton: true,
            html: "Are you sure, you want to mark the proposal as verified..?",
            confirmButtonText: "Yes, Verified",
            cancelButtonText: "No, Cancel",
            showClass: { popup: 'animate__animated animate__fadeInDown' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp' },
        }).then((result) => {
            if (result.value) {
                civilProcurementSave();
            } else {
            }
        });
    }else{
        civilProcurementSave();
    }
});
function civilProcurementSave(){
    $('.input-error').removeClass('input-error');
    var formData = new FormData($('.frm_civilProcurement')[0]);
    var url = $('.frm_civilProcurement').attr('action');
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
            var status  = response.status;
            var message = response.message;
            var error = response.error;
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
            console.log("Error : "+error);
            var url     = response.url;
            if(status == '1'){
                show_msg(status, url, message, status);
            }else{
                show_msgT(status, message);
                Swal.close();
            }
        }, 
        error: function (error) {
            console.log(error);
        }
    });
}
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
    //$('.total_focused_target_no').val(total).attr('data-value', total);
    var total_benefeciary_no = $('.total_benefeciary_no').val();
    var other = parseInt(total_benefeciary_no) - parseInt(total);
    if(isNaN(other) || !isFinite(other) || other <= 0){
        other = 0;
    }
    if(isNaN(total_benefeciary_no) || !isFinite(total_benefeciary_no) || total_benefeciary_no <= 0){
        total_benefeciary_no = 0;
    }
    if(isNaN(total) || !isFinite(total) || total <= 0){
        total = 0;
    }
    $('.other_no').val(other);
    //$('.total_focused_target_no').val(total_benefeciary_no);  /// comment on 30-01-26 - ayushi
    var total_focused_target_no = parseInt(other) + parseInt(total);
    $('.total_focused_target_no').val(total_focused_target_no);
    // amountAllocate('.tr_benefeciary');
    amountAllocate('.tr_target','.focused_target_');
}
$('.focused_target_no').on('input', function () {
    updateTotalTarget();
    var number = $(this).val();
    var total_focused_target = $('.total_focused_target_no').val();
    var total_benefeciary = $('.total_benefeciary_no ').val();
    /*
    /// comment on 30-01-26 - ayushi
    if(parseInt(total_focused_target) > parseInt(total_benefeciary)){
        number = parseInt(total_benefeciary) - (parseInt(total_focused_target) - parseInt(number));
        if(isNaN(number) || !isFinite(number) || number <= 0){
            number = 0;
        }
        $(this).val(number);
        updateTotalTarget();
    }*/
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

// var limit = 1000000;
// $('.focused_target_no').on('keyup', function(){
//     var number = $(this).val();
//     if(parseInt(number) > parseInt(limit)){
//     $(this).val(limit );
//     }
// });
    
///////////////////////////////////////////////////////////////////////

// $('.dayCal').on('change', function(){
//     var token = $(this).closest('.frm_about_project').find('input[name="_token"]');
//     var tant_start_date = $('#tant_start_date').val();
//     var tant_end_date   = $('#tant_end_date').val();
//     //console.log(tant_start_date+'--'+tant_end_date);
//     //$('#tant_end_date').attr('disabled', 'disabled');
//     if(tant_start_date.trim() != ''){
//       $('#tant_end_date').attr('min', tant_start_date).removeAttr('disabled');
//     }
//     if(tant_end_date.trim() != ''){
//       $('#tant_start_date').attr('max', tant_end_date).removeAttr('disabled');
//     }
//     if(tant_start_date.trim() != '' && tant_end_date.trim() != ''){
//       var link = $('.tBodyProjectTime').attr('data-link');
//       $.ajax({
//         type: 'POST',
//         url: link,
//         data: {tant_start_date : tant_start_date, tant_end_date: tant_end_date, _token: token },
//         success: function (response) {
//           console.log(response);
//           response = response.split('||');
//           var time = response[1];
//           $('#project_time').attr('value', time).val(time);
//         },
//         error: function (xhr, status, error) {
//           console.log('Error:', error);
//         }
//       });
//     }
//   });


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

function selectedText(selectElement, onload='') {
    var ids = $(selectElement).val();
    if((ids == null || ids == undefined || ids == '') && onload == 'Y'){
        $(selectElement).find('option:eq(0)').attr('selected', 'selected');
        $(selectElement).trigger('change.select2');
        ids = $(selectElement).val();
    }
    var selectedTextAll = [];
    $(ids).each(function(index){
        var sech_id = ids[index];
        var selectedText = $(selectElement).find('option[value="'+sech_id+'"]').attr('data-schedule');
        selectedTextAll[index] = selectedText;
    });
    var selectedTextAll = selectedTextAll.join('<br><br>');

    var showSelectedText = $(selectElement).closest('.form-group').find('.showSelectedText');
    showSelectedText.html("");
    if (selectedTextAll.trim() != "") {
        showSelectedText.html(selectedTextAll);
    }

    /*
    var selectedOption = $(selectElement).find(':selected');
    var selectedText = selectedOption.data('schedule');

    // Disable the selected option in other dropdowns
    $('.proj_sector').not(selectElement).find('option').prop('disabled', false);
    $('.proj_sector').not(selectElement).find('option[value="' + selectedOption.val() + '"]').prop('disabled', true);

    $('.proj_sector2').not(selectElement).find('option').prop('disabled', false);
    $('.proj_sector2').not(selectElement).find('option[value="' + selectedOption.val() + '"]').prop('disabled', true);

    // Your existing code for updating the text
    var showSelectedText = $(selectElement).closest('.form-group').find('.showSelectedText');
    
    if (selectedOption.val() === "") {
        showSelectedText.text("");
    } else {
        showSelectedText.text(selectedText);
    }*/
}

selectedText('.proj_sector', 'Y');
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
///////////////////
var nextStepAgency = sessionStorage.getItem("nextStepAgency");
if(nextStepAgency == '1'){
    stepper.next();
    sessionStorage.setItem("nextStepAgency", '');
}
///////////////////////////////////////////////////////////////
pa.listVillage();
//////////////////////////////////////////////

//$(".financial_certificate").inputmask("99aaaaa9999a9a*", { 'placeholder': '' }).css('text-transform', 'uppercase');
//$(".preNum").inputmask("**", { 'placeholder': '' }).css('text-transform', 'uppercase');
//$(".postNum").inputmask("***", { 'placeholder': '' }).css('text-transform', 'uppercase');
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

////////////////////////////////////////////////////////////
function handleDropdownChange(selector) {
    $(selector).on('change', function(){
        $(selector).find('option').removeAttr('style');
        $(selector).each(function(){
            var value = $(this).val();
            $(selector).find('option[value="'+value+'"]').css('display', 'none');
            $(selector).find('option[value=""]').removeAttr('style');
        });
    });
}

handleDropdownChange('.financial_audit');
handleDropdownChange('.audit_report');
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
///////////////////////////////////////////////////////////////////////////////
$('.btnDocSave').click(function() {
    //event.preventDefault();
    var frm = $(".frmVerifyList");
    var formData = frm.serialize();
    var url = frm.attr('action');
    var verify = $('.doc_verify').prop('checked');

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
//////////////////////////////////////////////////
var timeoutId = 0;
$('.todo_text').on('keyup', function() {
    //clearTimeout(timeoutId);
    //timeoutId = setTimeout(pa.todoSave(), 10000);
});
/////////////////////////////////////////////////////////////
function btnEventChange()
{
    var targetClass = 'docVS';
    var elements = document.getElementsByClassName(targetClass);
    var emptyCount = 0;

    for (var i = 0; i < elements.length; i++) {
        //if (elements[i].classList.contains('text-danger') || elements[i].classList.contains('text-info')) {
        if (elements[i].classList.contains('bg-info')) {
            emptyCount++;
        }
    }
    //console.log(emptyCount);
    if (emptyCount > 0) {
        $('.btnDocConfirm').prop('disabled', true);
    } else {
        $('.btnDocConfirm').prop('disabled', false);
    }

    // If any documents un-verify then only display "Communicate to agency"
    var un_len_verify = $('.docVS.bg-danger').length;
    if(un_len_verify == 0)
    {
        $('.btnDocConfirm').prop('disabled', true);
    }else{
        $('.btnDocConfirm').prop('disabled', false);
    }
}
btnEventChange();
//////////////////////////////////////////////////
function activateTab(tabId) {
    // Deactivate all tabs
    $('.step .step-trigger').attr('aria-selected', 'false').attr('disabled', 'disabled');
    $('.step .step-trigger[aria-controls="'+tabId+'"]').attr('aria-selected', 'true').removeAttr('disabled');
    // Activate the target tab
    $('.step').removeClass('active');
    $('.step[data-target="#' + tabId + '"]').addClass('active');

    // Hide all tab contents
    $('.bs-stepper-content .content').removeClass('active dstepper-block');

    // Show the content of the target tab
    $('#' + tabId).addClass('active dstepper-block');
}
////////////////////////////////////////////////

///////////////////////////////////////////////
$(document).ready(function() {
    var getAgencyWcId = $('.agency_wc_id').val();

    $('.address_to').on('change', function() {
        var selectedWcValue = $(this).val();
        $('#myModalWorkCenter').modal('show');
    });
});
////////////////////////////////////////////////////
pa.enbDisEngFile('#agency_reg_eng', '.agency_reg_eng_file');
pa.enbDisEngFile('#agency_deed_eng', '.agency_deed_eng_file');
pa.enbDisEngFile('#agency_socity_eng', '.agency_socity_eng_file');
//////////////////////////////////////////////
// stepper.next();
// stepper.next();
// stepper.next();
// window.stepper.to(3);
//////////////////////////////////////////////////////////
$('.bs-stepper.d-none').remove();

$('[data-toggle="tooltip"]').tooltip();  // Initialize tooltips


function capitalizeFirstLetter(input) {
    input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
}
//////////////////////////////////////////////////////////////////////////////////////////////////////
$('.updateProcessedAmt').on('click', function(){
    $('#custom-tabs-two-tabContent').find('.tab-pane').removeClass('active show');
    var menuID = $('#custom-tabs-two-tab');
    menuID.find('.nav-link').removeClass('active');

    menuID.find('.nav-item:eq(0) .nav-link').addClass('active');
    var divId = menuID.find('.nav-item:eq(0) .nav-link').attr('data-divId');
    $(divId).addClass('active show');
    $('#total_opex_amount').focus();
});
function convertToUpperCase(input) {
    input.value = input.value.toUpperCase();
}
///////////////////////////////////////////////////////////////////////////////////////////
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
}
*/

function setInputBox(thiss, idd){
    var total_no = $(thiss).val();
    var newCount = parseInt(total_no) - 1;
    var container = $(idd+'Div');
    var currentCount = container.children('.new-row').length;
    var dbRecordsCount = container.find('.saved_record').length || 0;

    if (!newCount) {
        container.empty();
        return;
    };

    // 🔼 IF INCREASE
    if (newCount > currentCount) {
        for (var i = currentCount; i < newCount; i++) {
            container.append(generateInputRow(i, idd));
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
            html: 'Reducing number will remove extra details.<br>Do you want to continue?',
            confirmButtonText: 'Yes, Continue',
            cancelButtonText: 'Cancel',
            }).then((result) => {
                if (result.value) {
                    container.children('.new-row').slice(newCount).remove();
                }else{
                    $(thiss).val(currentCount);
                    return;
                }
            }); 
        }else{
            container.children('.new-row').slice(newCount).remove();
        }
    }
}
function generateInputRow(index, idd) {
    var srNo = index + 2;
    var inputRow = '';
    inputRow = `<div class="col-sm-12 input-group input-group-sm mt-0 new-row">\
                        <div class="input-group-prepend">\
                            <span class="input-group-text">${srNo}.</span>\
                        </div>`;
    if(idd == '#no_of_objectives'){
        inputRow += `<input type="text" class="form-control form-control-sm" id="project_objectives_${srNo}" name="project_objectives_${srNo}" placeholder=" project objectives">`;
    }else{
        inputRow += `<input type="text" class="form-control form-control-sm" id="project_activity_${srNo}" name="project_activity_${srNo}" placeholder=" project activity">`;
    }
    inputRow += `</div>`;
    return inputRow;
}
//////////////////////////////////////////////////////
$(document).on('keyup', '.agency_name', function(){
    var value = $(this).val();
    value = value.trim();
    $('.cnf_agency_name').html(value);
});