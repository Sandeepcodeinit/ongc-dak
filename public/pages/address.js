$('.amount').on('keyup', function(){
    var amount_ttl = 0;
    $('.amount').each(function(index){
        var amount = $(this).val();
        if(parseFloat(amount) <= 0 || amount == '' || amount == undefined){
            amount = 0;
        }
        amount_ttl = parseFloat(amount) + parseFloat(amount_ttl);
    });
    $('.budget_total').attr('value', amount_ttl).val(amount_ttl);
});
//////////////////////////////////////////////////////////////////////////////
var adto_id = $('.adto_id').val();
wc = {
    budgetSave:function(){
        var frm = $('.frm_budgeting');
        var url = frm.attr('action');
        var formData = frm.serialize();
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                show_msg(3, '', '<b>Please wait...<br>Saving in progress.</b>', 4);
            },
            success: function (response) {
                //console.log(response);
                var status  = response.status;
                var message = response.message;
                show_msgT(status, message);
                if(status == '1'){
                    frm.find('.budget_total').attr('value', '').val('');
                    frm.find('.financial_year').val('').trigger('change');
                    frm.find('.budget_id').val('');
                    frm.find('.btnSave').text("Save");
                    frm.get(0).reset();
                    wc.budgetList();
                }
                Swal.close();
            },
            error: function (error) {
                console.log(error);
            }
        });
    },
    budgetList:function(page){
        var frm = $('.frm_budgeting');
        var url = frm.attr('data-list_url');
        var wc_cd = frm.find('.wc_cd').val();
        var token = frm.find('input[name="_token"]').val();
        $.ajax({
            url: url,
            type: 'POST',
            data: { page: page, wc_cd: wc_cd, _token: token },
            beforeSend: function() {
                show_msg(3, '', '<b>Please wait...', 4);
            },
            success: function (response) {
                $('.divBudgetLists').html(response);
                Swal.close();
            },
            error: function (error) {
                console.log(error);
            }
        });
    },
    editBudget:function(thiss){
        var trr = $(thiss).closest('.trr');
        var row = trr.attr('data-row');
        var row = $.parseJSON(decodeURIComponent(row));
        var budget_id = row.budget_id;
        var budget_financial_year = row.budget_financial_year;
        var budget_ad22 = row.budget_ad22;
        var budget_nad22 = row.budget_nad22;
        var budget_23 = row.budget_23;
        var budget_total = row.budget_total;
        //console.log("budget_financial_year="+budget_financial_year);
        
        var frm = $('.frm_budgeting');
        var url = frm.attr('data-list_url');
        var wc_cd = frm.find('.budget_id').val(budget_id);
        var wc_cd = frm.find('.financial_year').val(budget_financial_year).trigger('change');
        var wc_cd = frm.find('.budget_ad22').val(budget_ad22);
        var wc_cd = frm.find('.budget_nad22').val(budget_nad22);
        var wc_cd = frm.find('.budget_23').val(budget_23);
        var wc_cd = frm.find('.budget_total').val(budget_total);
        var wc_cd = frm.find('.btnSave').text("Update");
    },
}
/////////////////////////////////////////////////////////
if(parseInt(adto_id) > 0){
    wc.budgetList(1);

    $(document).on('click', '#pagination a', function (event) {
        event.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        wc.budgetList(page);
    });
}