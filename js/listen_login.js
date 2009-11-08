var wrapperId 	=	'#wrap';		// main container
var waitId		=	'#wait';		// wait message container
var postFile	=	'includes/login_post.php';	// post handler
var autoRedir	=	true;			// auto redirect on success or wellcome page
var redirTime	=	'3000'; 
var redirURL 	= 	'login.php'; 

$(document).ready(function(){ 
	// hide first
	$(waitId).hide(); 
	$(wrapperId).hide();
	// FirstLoad
	loadform('loginform',0,0);
});

function checkdata(data) {
	if(data.installed!==true) {
		$(wrapperId).html(data.content).slideDown('slow',function(){
			$(waitId).fadeOut('fast').html();
		});
	}
	if(data.status==1) {
		if(autoRedir){ 
			$(waitId).hide().removeClass('ui-state-error').addClass('ui-state-highlight').html(redirNote).fadeIn('fast', function(){
				window.location=data.url;
			});
		} else {
			$(waitId).fadeOut('slow', function(){ 
				$(wrapperId).slideUp('slow',function(){
					$(this).html(data.content).slideDown();
				}); 
			}).html();
		}
	} else {
		if(data.message!==false && reload==false){
			$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html(data.message).fadeIn('fast', function(){  
				$(this).queue(function(){
        			var self = this;
        			setTimeout(function(){$.dequeue(self);},3000);
   				}).fadeOut('slow').queue(function(){
					if(data.user!==false){
						loadform('loginform',0,data.user);
						$('#p').focus();
					} else {
						loadform('loginform',0,0);$('#u').focus();
					}
					$(this).dequeue();
				}).html();
			});
		}
		if(reload){ 
			$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html(outNote).fadeIn('fast', function(){
				setTimeout(function(){window.location=redirURL;},redirTime);
			});
		}
		if(data.content!==false && reload==false){
			$(wrapperId).html(data.content).slideDown('slow',function(){
				$(waitId).fadeOut('fast').html();
				if ($('#pwdreminder').length){
					$('#pwdreminder').click(function(){loadform('formpwd',0,0);});
					$('#invitemode').click(function(){loadform('forminv',0,0);});
				} else {
					$('#loginback').click(function(){loadform('loginform',0,0);});
				}
				if(data.user!==false){$('#u').val(data.user); $('#p').focus();} // just recent user add
			});
		}
	}
	return false;
}

function loadform(name,code,user){
	$(waitId).removeClass('ui-state-error').addClass('ui-state-highlight').html(waitNote).fadeIn('fast', function(){
		$(wrapperId).slideUp('fast',function(data){
			$.getJSON(postFile,{formtype:name,code:code,user:user},function(data){
				checkdata(data);
			});
//			$.get(postFile,{formtype:name,code:code,user:user},function(data){checkdata($.evalJSON(data));});
/*			var thing = {formtype:name,code:code,user:user};
			var encoded = $.toJSON(thing);    
			$.get(postFile,{data:encoded},function(encdata){checkdata($.evalJSON(encdata));});*/
		});
	});
	return false;
}

function checkRegexp(o,regexp,n) {
	if ( !( regexp.test( o.val() ) ) ) {
		o.addClass('ui-state-error');
		$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html('<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert" ></span>'+n+'</p>').fadeIn('fast');
		return false;
	}
	return true;
}
function checkLength(o,n,min,max) {
	if ( o.val().length > max || o.val().length < min ) {
		o.addClass('ui-state-error');
		$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html('<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert" ></span>'+ constants.LengthOf + n + constants.MustBeBet + min + constants.And + max +'.</p>').fadeIn('fast');
		return false;
	}
	return true;
}
function submitloginform(form) {
	$(waitId).html(waitNote).fadeIn();
	var _u = $('#u').val();	// form user
	var _p = $('#p').val();	// form id
	var _r = '';
	if($('#r').is(':checked')) { _r = $('#r').val();}
	$.post(postFile, { formtype:'loginform',u: _u, p: _p, r: _r }, function(data) {checkdata(data);},'json');
	return false;
}
function submitpwdform(form){
	//$(waitId).html(waitNote).fadeIn();
	var email = $('#e'); 
	var bValid = true;
	email.removeClass('ui-state-error');
		bValid = bValid && checkRegexp(email,/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,constants.InvalidEmail+'eg. ui@jquery.com');
	if (bValid) {
		$.post(postFile, { formtype: 'formpwd', e: email.val() }, function(data) {
			$(waitId).removeClass('ui-state-error').addClass('ui-state-highlight').html(data.message).fadeIn('fast',function(){
				//setTimeout(function(){window.location.reload( true );},5000);
				setTimeout(function(){loadform('loginform',0,0);},redirTime);
			});
		},'json');
	}
	$('input').focus(function () {$(this).removeClass('ui-state-error');$(waitId).fadeOut('slow').html();});
	return false;
}
function submitcodeform(form){
	var code = $('#code');
	loadform('forminv',code.val(),0);
	return false;
}
function submitinvform(form){
	//$(waitId).html(waitNote).fadeIn();
	var firstname = $('#firstname'),
		lastname = $('#lastname'),
		username = $('#username'),
		email = $('#email'),
		password = $('#password'),
		password2 = $('#password2'),
		code= $('#code'),
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
	bValid = bValid && checkRegexp(email,/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,constants.InvalidEmail+'eg. ui@jquery.com');
	bValid = bValid && checkRegexp(lastname,/^[a-z]([0-9a-z_])+$/i,'Lastname may consist of a-z, 0-9, underscores, begin with a letter.');
	bValid = bValid && checkRegexp(password,/^([0-9a-zA-Z])+$/,'Password field only allow : a-z 0-9');
	bValid = bValid && checkRegexp(username,/^([0-9a-zA-Z])+$/,'Username field only allow : a-z 0-9');	
	if(password.val() != password2.val()){
		$(waitId).removeClass('ui-state-highlight').addClass('ui-state-error').html('<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert" ></span>' + constants.NoMatchPass + '</p>').fadeIn('fast');
		$('#password2').addClass('ui-state-error').val('');
		bValid = bValid && false;
	}
		//alert(bValid);
	if (bValid) {
			var register = $('#'+form.id +' input[name=register[]]').serialize();
			//console.log(code);
		$.post(postFile, { formtype: 'forminv', register: register, code: code.val()}, function(data) {
			//console.log(data.back);
			$(waitId).removeClass('ui-state-error').addClass('ui-state-highlight').html(data.message).fadeIn('fast',function(){
				if(data.back!==false){
					//setTimeout(function(){window.location='login.php?username='+username.val();},5000);
					loadform('loginform',0,data.user);
					//alert(data.user);
				}else{
					username.addClass('ui-state-error');
				}
			});
		},'json');
	}
	$('input').focus(function () {$(this).removeClass('ui-state-error');$(waitId).fadeOut('slow').html();});
	return false;
}
function switchLang(locStr){
		$('body').append('<div id="overlay"></div>');
		$('body').css({height:'100%'});
		$('#overlay')
			.css({display: 'info',position: 'absolute',top:0,left: 0,width: '100%',height: '100%',zIndex: 999999,background: 'black url(images/loading.gif) no-repeat center'})
			.fadeIn(500,function(){
						window.location.href=locStr;
//						setlanguser(sessionuserid,locStr);
 						setTimeout(function(){$('#overlay').fadeOut(500,function(){$(this).remove();});}, 400);
			});
	return false;
}