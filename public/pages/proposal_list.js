pl = {
    advFilterModal:function() {
        var mdl = $('#mdlAdvFilter');
        mdl.modal('show');
    },
    addQuery:function(thiss) {
        var div = $(thiss).closet('.divAddQuery');
        var column_where = div.find('.column_where').val();
        var column_name = div.find('.column_name').val();
        var operator = div.find('.operator').val();
    },
    getPinCodeList:function(thiss, listtype){
        var val = $(thiss).val();
        if(parseInt(val) > 0){
            var url = $('.frmSearch').attr('data-link');
            var token = $('.frmSearch').find('input[name="_token"]').val();
            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: token, code:val, list_type:listtype },
                dataType: 'json',
                beforeSend: function() {
                    show_msg(3, '', '<b>Please wait...', 4);
                },
                success: function (response) {
                    $('.district_cd').html(response.villageList).trigger('change:select2');
                    Swal.close();
                }
            });
        }
    },
    sdDistrictList:function(thiss){
        var val = $(thiss).val();
        var val = val.trim();
        if(val != ''){
            var url = $('.frmSearch').attr('data-link_spldist');
            var token = $('.frmSearch').find('input[name="_token"]').val();
            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: token, session:val },
                beforeSend: function() {
                    show_msg(3, '', '<b>Please wait...', 4);
                },
                success: function (response) {
                    console.log(response);
                    var niti_ayog_district = response.niti_ayog_district;
                    var ongc_district = response.ongc_district;
                    $('.sd_niti_district').html(niti_ayog_district).trigger('change:select2');
                    $('.sd_ongc_district').html(ongc_district).trigger('change:select2');
                    Swal.close();
                }
            });
        }
    },
}