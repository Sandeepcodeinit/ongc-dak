lu = {
	addNewProposal:function(thiss) {
		var mdl = $('#mdlCreateNewProposal');
		//mdl.find('.email_id, .pan_no, .agency_code').val('').removeAttr('readonly');
		mdl.find('.email_id, .pan_no, .agency_code').val('');
		mdl.find('.divPanCard').css('display', 'none');
		mdl.find('.btnCreateProposl').attr('disabled', 'disabled');
		mdl.modal('show');
	},
	checkPan:function (thiss) {
		var mdl = $('#mdlCreateNewProposal');
		mdl.find('.pan_no').val('').attr('readonly', 'readonly');
		mdl.find('.btnCreateProposl').attr('disabled', 'disabled');
		var email = $(thiss).val().trim();
		if(email != ''){
			var url = mdl.find('.mdlBody').attr('data-pan_url');
	        var token = $('.frmCreateNewProposal').find('input[name="_token"]').val();
			//console.log(url);
	        $.ajax({
	            url: url,
	            type: 'POST',
	            data: { _token: token, email:email },
	            dataType: 'json',
	            beforeSend: function() {
	                show_msg(3, '', '<b>Please wait...</b>', 4);
	            },
	            success: function (response) {
	            	//console.log(response);
	                var status  = response.status;
	                var message = response.message;
	                var pan_no  = response.pan_no;
	                var agency_id = response.agency_id;
	                mdl.find('.agency_code').val('');
	                if(status == 2){
	                	$(thiss).focus();
	                	show_msgT(status, message);
	                }else{
	                	show_msgT(status, message);
						if(parseInt(agency_id) > 0){
	                		mdl.find('.pan_no').val(pan_no).attr('readonly', 'readonly');
							mdl.find('.agency_code').val(agency_id);
						}else{

						}
						/*
	                	if(parseInt(agency_id) > 0){
	                		mdl.find('.pan_no').val(pan_no).attr('readonly', 'readonly');
							mdl.find('.divPanCard').css('display', 'none');
							mdl.find('.pan_card').attr('disabled', 'disabled');
							mdl.find('.agency_code').val(agency_id);
	                	}else{
							mdl.find('.divPanCard').css('display', 'block');
							mdl.find('.pan_card').removeAttr('disabled');
	                		mdl.find('.pan_no').val('').removeAttr('readonly').focus();
	                	}*/
						mdl.find('.btnCreateProposl').removeAttr('disabled');
	                }
	                Swal.close();
	            }
	        });
	    }else{
	    	$(thiss).focus();
	    	show_msgT(2, 'Enter email id.');
	    }
	},
	createNewProposal:function () {
		var mdl = $('#mdlCreateNewProposal');
		var formData = new FormData($('.frmCreateNewProposal')[0]);
	    var url = $('.frmCreateNewProposal').attr('action');
	    $.ajax({
	        url: url,
	        type: 'POST',
	        data: formData,
	        processData: false,
	        contentType: false,
	        beforeSend: function() {
	            show_msg(3, '', '<b>Please wait...</b>', 4);
	        },
	        success: function (response) {
	            //console.log(response); return false;
	            var status  = response.status;
	            var message = response.message;
	            var url     = response.url;
	            //var error = response.error;
	            //console.log("Error : "+error);
	            show_msgT(status, message);
	            Swal.close();
	            
	            if(status == '1'){
	                setTimeout(() => {
	                    window.location = url;
	                }, 1000);
	            }
	        }
	    });
	}
}
/////////////////////////////////////////////////////////////////////////////////////

 $(".pan_no").inputmask("aaaaa9999a", { 'placeholder': '' });