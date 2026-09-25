function show_msg(type=3, url='', msg='', act=0){
    var icon = '';    var tim = 6000000;
    if(type == 0){  icon = 'error';   }
    else if(type == 1){ icon = 'success';   tim = 20000;   }
    else if(type == 2){ icon = 'warning';   }
    else if(type == 3){ icon = 'info';   }
    else {  icon = 'warning'; msg='Not valid info.'; }    

    var cnfBtn = clsBtn = true;
    if(act==1){// insert
      cnclBtn = false;
      cnfBtnTxt = 'Ok';
      cnlBtnText = '';
    }
    else if(act==2){// update
      cnclBtn = true;
      cnfBtnTxt = 'Yes';
      cnlBtnText = 'No';
    }
    else if(act==3){// delete
      cnclBtn = true;
      cnfBtnTxt = 'Yes, Delete It.';
      cnlBtnText = 'Cancel';
    }
    else if(act==4){ // wait
      cnclBtn = cnfBtn = clsBtn = false;
      cnfBtnTxt = cnlBtnText = 'Cancel';
    }else{ // wrong type
      cnclBtn = false;
      cnfBtnTxt = 'Ok';
      cnlBtnText = '';
    }
    url = url.trim();

    return Swal.fire({
		timer: tim,
      icon: icon,
      //title: 'Oops...',
      showCloseButton: clsBtn,
      showCancelButton: cnclBtn,
      showConfirmButton: cnfBtn,
      html: msg,
      confirmButtonText: cnfBtnTxt,
      cancelButtonText: cnlBtnText,
      showClass: { popup: 'animate__animated animate__fadeInDown'   },
      hideClass: { popup: 'animate__animated animate__fadeOutUp'    },
      willClose: () => {  
        if (type == '1' && url != '') {
          window.location=url;
        }   
      },
    }).then((result) => {
      if (result.value && url != '') {
      window.location=url;
      }
    });
}

function show_msgT(type, msg, url=''){
	type = parseInt(type);
	switch(type){
		case 1:
			toastr.success(msg);
		break;
		case 2:
			toastr.error(msg);
		break;
		case 3:
			toastr.info(msg);
		break;
		case 4:
			toastr.warning(msg);
		break;
		default:
			toastr.error("Invalid alert call.");
		break;
	}
	setTimeout(() => {
		if(url.trim() != ''){
			window.location = url;
		}
	}, 1000);
}
//////////////////////////////////////////////////////////////////
function linkClick(thiss, target=''){
	target = target.toUpperCase();
	if(target == 'O'){
		var link = $(thiss).find('option:selected').attr('data-link');
	}else{
		var link = $(thiss).attr('data-link');
	}

	if(target == 'B'){
		window.open(link, '_blank');
	}else{
		window.location = link;
	}
}
/////////////////////////////////////////////////////////////////
function price_in_words(price) {
	var priceAll    = price.split('.');
	var price       = priceAll[0];
	var decimal     = priceAll[1];
	price           = parseInt(price);
	decimal         = decimal;
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
			words.push(0 != digit ? " " + sglDigit[digit] + " Hundred" + (0 != price[digitIdx + 1] && 0 != price[digitIdx + 2] ? " " : "") : "");
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
	
	var decimalWords = "";
	if (decimal > 0) {
		decimalWords = " And";

		for (var i = 0; i < decimal.length; i++) {
			decimalWords += " " + sglDigit[parseInt(decimal[i])];
		}
		decimalWords = decimalWords+" Paisa";
	}
	return str+decimalWords;
}
//////////////////////////////////////////////////////////////////
function recordsDelete(thiss, idd){
	var cnf = confirm("Are you sure to delete it..?");
	if(cnf){
		var url = $(thiss).attr('data-link');
		$.ajax({
			type: "POST",
			url: url,
			data: {id:idd, _token: "{{ csrf_token() }}"},
			success:function(result){
			  console.log(result);
			  result = result.trim();
			  result = result.split('||');
			  var msg = result[1];
			  var mod = result[2];
			  show_msgT(mod, msg);
			  if(mod == '1'){
				setTimeout(function() {
				  //location = "{{ route('login') }}";
				  location.reload();
				}, 2000);
			  }
			}
		});
	}
}
//////////////////////////////////////////////////////////////////
$(document).on("keypress keyup blur", ".alpnum", function () {
//$('.alpnum').on('keyup', function() {
	var inputValue = $(this).val();
	var alphanumericRegex = /^[a-zA-Z0-9_]+$/;

	if (!alphanumericRegex.test(inputValue)) {
	  $(this).val(inputValue.replace(/[^a-zA-Z0-9_]/g, ''));
	}
});
///////////////////////////////////////////////////////////////////
$(document).on('keyup', 'input[type="text"]', function(){
	var value = $(this).val();
	value = value.trim();
	if(value == ''){
		$(this).val('');
	}
});
//////////////////////////////////////////////////////////////
$('input[type="text"]').on( "focusout", function() {
    var value = $(this).val();
    value = value.trim();
    $(this).val(value);
 } );
////////////////////////////////////////////////////////
$(document).on("keypress keyup blur", ".float", function (event) {
//$(".float").on("keypress keyup blur",function (event) {
	//this.value = this.value.replace(/[^0-9\.]/g,'');
	$(this).val($(this).val().replace(/[^0-9\.]/g,''));
	if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
		event.preventDefault();
	}
	if($(this).val() < 0){		$(this).val('');			}
});
//////////////////////////////////////////////////////////////
//$(".int").on("keypress keyup blur",function (event) {    
$(document).on("keypress keyup blur", ".int", function (event) {
   $(this).val($(this).val().replace(/[^\d].+/, ""));
	if ((event.which < 48 || event.which > 57)) {
		event.preventDefault();
	}
});
////////////////////////////////////////////////////////////
$(document).on("keypress keyup blur", ".char", function (e) {
//$('.char').on("keypress keyup blur",function (e) { 
var key = e.keyCode;
	if (!((key == 8) || (key == 32) || (key == 46) || (key >= 65 && key <= 90) || (key >= 97 && key <= 122) || (e.shiftKey || e.ctrlKey || e.altKey))) {
	  e.preventDefault();
	}	 
});
///////////////////////////////////////////////////////////////////
$(document).on('keyup, keypress', 'input[maxlength]', function(e){
	var maxx = $(this).attr('maxlength');
	var value = $(this).val();
	value = value.trim();
	if(value.length == maxx){
		e.preventDefault();
	}
});
/////////////////////////////////////////////////////////////////////////
$(document).keydown(function(e) {
	// Detect Ctrl + Shift + Y
	if (e.ctrlKey && e.shiftKey && e.key === 'Y') {
		Swal.fire({
			title: "Submit your name",
			input: "password",
			inputAttributes: {
			  autocapitalize: "off",
			  autofocus: false,
			  placeholder: " Enter your name"
			},
			showCancelButton: true,
			confirmButtonText: "Unlock",
			showLoaderOnConfirm: true,
			preConfirm: async (login) => {
			  try {
				var githubUrl = $('body').attr('data-inspact').replace(':action', login);
				const response = await fetch(githubUrl);
				if (!response.ok) {
				  return Swal.showValidationMessage(`${JSON.stringify(await response.json())}`);
				}
				return response.json();
			  } catch (error) {
				Swal.showValidationMessage(`Request failed: ${error}`);
			  }
			},
			allowOutsideClick: () => !Swal.isLoading()
		}).then((result) => {
			if (result.isConfirmed) {
				//console.log("url=", url);
				var status = result.value.status;
				var login = result.value.login;
				if(status == '1'){
					localStorage.setItem("ispct", 1);
					location.reload();
				}
				//console.log("result=", result.value);
			}
		});
	}
});
var ispct = localStorage.getItem("ispct");
if(ispct == undefined || ispct == null){
	ispct = 0;
	localStorage.setItem("ispct", ispct);
}
//console.log("ispct=", ispct);
if(ispct == 0){
	$('a[href]').on('contextmenu', function(e) {
        e.preventDefault();
		var href = $(this).attr('href');
		var pattern = /http|https/;
		if (pattern.test(href)) {
        	window.open(href, '_blank');
		}
      });
	$(document).on('contextmenu', function(event) {
        event.preventDefault();
    });
	$(document).keydown(function(e) {
		var keyCode = e.which || e.keyCode;
		//console.log("keyCode=", keyCode);
		var allowKeyCode = [65,66,67];
		if((e.ctrlKey && e.shiftKey && keyCode === 77) || (keyCode === 91 && e.shiftKey && keyCode === 67) || (e.ctrlKey && e.shiftKey && keyCode === 67) || (e.ctrlKey && e.shiftKey && keyCode === 73) || (e.ctrlKey && keyCode === 85) || keyCode === 123){
			e.preventDefault();
			return false;
		}
	});
}
////////////////////////////////////////////
$('.pan_no, .gst_no, .cin_no').each(function(){
	var placeholder = $(this).attr('placeholder');
	if(placeholder){
		$(this).attr('data-placeholder', placeholder);
	}	
});
////////////////////////////////////////////
$(document).on('mouseenter', '.pan_no, .gst_no, .cin_no', function() {
	var placeholder = $(this).attr('data-placeholder');
	if(placeholder == undefined || placeholder == ''){
		placeholder = ' Enter PAN no.'
	}
    $(this).attr('placeholder', placeholder);
});
//$(".pan_no").inputmask("aaaaa9999a", { 'placeholder': '' });