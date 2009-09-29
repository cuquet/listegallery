var wrapperId 	=	'#wrap';		// main container
var waitId		=	'#wait';		// wait message container
var postFile	=	'includes/install_post.php';	// post handler

$(document).ready(function(){ 
	// hide first
	$(waitId).hide(); 
	$(wrapperId).hide();
	// get request to load form
	$.getJSON(postFile,{step:0}, function(data){
		if(data.status===true) {
			window.location=data.url;
		} else {
			// show form
			$(waitId).html(data.message).fadeIn('slow',function(){
				$('#footer').hide().prepend(data.footer).fadeIn();
				$(wrapperId).html(data.content).slideDown('slow',function(){
					if(data.status===false) {
						$('#step').slideDown('slow', function(){
							//$('#btnreload').fadeOut('slow');
						});
					}
				});
			});
		}
	 });
});
function LoadStep(step) {
	$.getJSON(postFile,{step:step}, function(data){
/*		$(wrapperId).slideUp('slow',function(){
			$(waitId).fadeOut('slow').html(data.message).fadeIn('slow',function(){
				$(wrapperId).html(data.content).slideDown('slow');*/
		$(waitId).fadeOut('slow',function(){
			$(wrapperId).slideUp('slow',function(){
				$(waitId).html(data.message).fadeIn('slow',function(){
					$(wrapperId).html(data.content).slideDown('slow');
					$('#installform input[type=submit]').attr('disabled', true);
					if(step==1){
						$('#step').hide();
						$('#driver').ready(function(){
							$('#driver').change(function () {
								var driver =$('#driver option:selected').val();
								$.getJSON(postFile, { driver:driver,step:step}, function(data) {
									$(waitId).hide().html(data.message).fadeIn('slow',function(){
										$('#installform input[type=submit]').attr('disabled', false);
										$('#basedetails').hide().html(data.content);
										eHeight = $('#basedetails').height();
										iHeight= $('#content').height();
										if(iHeight < eHeight) {
											$('#content').animate({height: 100+eHeight}, 1000,function(){
												$('#basedetails').slideDown('slow');
											});
										} else {
											$('#basedetails').slideDown('slow');
										}
										$('#installform').submit( function() { 
											var db_access = $('#installform select[name=db_access[]] ,input[name=db_access[]]').serialize();
											var prefix = $('#db_prefix').val();
											$.post(postFile, { db_access: db_access,db_prefix:prefix}, function(data) {
												$(waitId).hide().html(data.message).animate({'opacity': 'toggle'}); //.fadeIn('fast');
												$('#step').slideDown('slow', function(){
													if(data.status==false) {$('#installform input[type=submit]').attr('disabled', true);}
												});
											},'json');
											return false;
										});
									});
								});
							}) ;
						});
					}
					if(step==2){
						$('#step').hide();
						$('#install2form').submit( function() { 
							var settings = $(this).serialize();										
							$.post(postFile, settings, function(data) {
								$(waitId).hide().html(data.message).animate({'opacity': 'toggle'}); //.fadeIn('fast');
								$('#step').slideDown('slow', function(){
									$('#install2form input[type=submit]').attr('disabled', true);
								});
							},'json');
							return false;
						});
					}
				});
			});
		});
	});
	return false;
}
function DownloadConfig() {
	callIframe(postFile+'?download=1');
	return false;
}
function callIframe(url,callback) {
    $(document.body).append('<div class="iframediv"><IFRAME id="ifdownload" src="#" frameborder="0" height="0" width="0"  name="ifdownload" style="display:none;"></iframe></div>');
    $('#ifdownload').attr('src', url);
    $('#ifdownload').load(function() {
        window.setTimeout(function(){$('.iframediv').remove();},3000);
    });
   return false;
}
function CopyConfig() {
	$(waitId).fadeOut('slow',function(){
		$.getJSON(postFile, { copy:1 }, function(data) {
			$(waitId).html(data.message).fadeIn('slow');
		});
	});
	return false;
}
function checkRegexp(o,regexp,n) {
	if ( !( regexp.test( o.val() ) ) ) {
		o.addClass('ui-state-error');
		$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html('<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert" ></span>'+n+'</p>').fadeIn('fast');
	}
	return true;
}
function checkLength(o,n,min,max) {
	if ( o.val().length > max || o.val().length < min ) {
		o.addClass('ui-state-error');
		$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html('<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert" ></span>T_Length of ' + n + ' must be between '+min+' and '+max+'.</p>').fadeIn('fast');
	}
	return false;
}
function submitpwdform(form){
	var email = $('#e'),
	bValid = true;
	email.removeClass('ui-state-error');
	bValid = bValid && checkLength(email,'email',6,80);
	bValid = bValid && checkRegexp(email,/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,constants.InvalidEmail+' eg. ui@jquery.com'); //constants.EmptyEmail
	if (bValid) {
		$.post(postFile, { formtype: 'formpwd', e: form.e.value }, function(data) {
			$(waitId).removeClass('ui-state-error').addClass('ui-state-highlight').html(data.message).fadeIn('fast',function(){
				setTimeout(function(){window.location.reload( true );},5000);
			});
		},'json');
		return false;
	}
	$('input').focus(function () {$(this).removeClass('ui-state-error');$(waitId).fadeOut('slow').html();});
	return false;
}
function submitinvform(form){
	var firstname = $('#firstname'),
		lastname = $('#lastname'),
		username = $('#username'),
		email = $('#email'),
		password = $('#password'),
		password2 = $('#password2'),
		code= $('#code').val(),
		allFields = $([]).add(firstname).add(lastname).add(username).add(email).add(password).add(password2),
		bValid = true;
		allFields.removeClass('ui-state-error');
		bValid = bValid && checkLength(firstname,'firstname',3,16);
		bValid = bValid && checkLength(email,'email',6,80);
		bValid = bValid && checkLength(lastname,'lastname',3,16);
		bValid = bValid && checkLength(username,'username',3,16);
		bValid = bValid && checkLength(password,'password',6,16);
		bValid = bValid && checkLength(password2,'password repeat',6,16);
		bValid = bValid && checkRegexp(firstname,/^[a-z]([0-9a-z_])+$/i,'Username may consist of a-z, 0-9, underscores, begin with a letter.');
		// From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
		bValid = bValid && checkRegexp(email,/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,constants.InvalidEmail+' eg. ui@jquery.com');
		bValid = bValid && checkRegexp(lastname,/^[a-z]([0-9a-z_])+$/i,'Username may consist of a-z, 0-9, underscores, begin with a letter.');
		bValid = bValid && checkRegexp(password,/^([0-9a-zA-Z])+$/,'Password field only allow : a-z 0-9');
		bValid = bValid && checkRegexp(username,/^([0-9a-zA-Z])+$/,'Username field only allow : a-z 0-9');	
		if(password.val() != password2.val()){
			$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html('<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert" ></span>' + constants.NoMatchPass + '</p>').fadeIn('fast');
			$('#password2').addClass('ui-state-error').val('');
			bValid = bValid && false;
		}
		
		if (bValid) {
			var register = $('#'+form.id +' input[name=register[]]').serialize();
//			console.log(code);
		$.post(postFile, { formtype: 'forminv', register: register, code: code}, function(data) {
//			console.log(data.back);
			$(waitId).removeClass('ui-state-error').addClass('ui-state-highlight').html(data.message).fadeIn('fast',function(){
				if(data.back){
					setTimeout(function(){window.location='login.php?username='+username.val();},5000);
				}else{
					username.addClass('ui-state-error');
				}
			});
		},'json');
	}
	$('input').focus(function () {$(this).removeClass('ui-state-error');$(waitId).fadeOut('slow').html();});
	return false;
}
function loadform(name){
	$(waitId).removeClass('ui-state-error').addClass('ui-state-highlight').html(waitNote).fadeIn('fast', function(){
		$(wrapperId).slideUp('fast',function(data){
			$.getJSON(postFile,{formtype:name},function(data){
				$(wrapperId).html(data.content).slideDown('slow',function(){
					$(waitId).fadeOut('fast').html();
				});
			});
		});
	});
	return false;
}
