var imgURL, imgDat;

$(document).ready(function(){
	//$('#footer > #nav').append('<div id="embedded_player" style="width:400px;float:right;height: 30px;" ></div>');
	if($('#closer').length === 0) { $('<a id="closer" class="ui-state-default ui-corner-all closer" href="#"><span class="ui-icon ui-icon-arrowthickstop-1-e"></span></a>').appendTo( '#nav' );}
	$("#closer").toggle(
		function() {$(this).html('<span class="ui-icon ui-icon-arrowthickstop-1-w"></span>');$("#right").hide('size',{to:{width:'1%'}},500, function(){$('.inner-container').animate({ width: "100%"});});},
		function() {$(this).html('<span class="ui-icon ui-icon-arrowthickstop-1-e"></span>');$('.inner-container').animate({ width: "60%"}, function(){$("#right").animate({width: '36%'}, 1000);});}
	);
 
	//$.ajax({ cache: false });
	$('<div id="dummy-element" style="display:none" />').appendTo('body');
	$('#swtheme').switcher({icon:'ui-icon ui-icon-gear',initialText: 'Switch Theme',listclass:'jquery-ui-themeswitcher',onSelect: function(){switchTheme(locStr);}});
	$('#swlang').switcher({icon:'ui-icon ui-icon-flag',initialText: 'Switch Language',listclass:'jquery-ui-langswitcher',onSelect: function(){switchLang(locStr);}});
	$('#swmenu').switcher({icon:'ui-icon ui-icon-home',initialText: 'Switch Menu',listclass:'jquery-ui-menuswitcher',onSelect: function(){switchPage(locStr);}});

//present and future template animation 
/*	$('#stickytip').live('mouseover', function() {  
         if (!$(this).data('init')) {  
             $(this).data('init', true);  
             $(this).hover(function(){ },function(){$(this).fadeOut('fast').remove();});  
             $(this).trigger('mouseover');  
         }  
	});  */
	$('.mvup, .mvdown').live('mouseover', function() {  
         if (!$(this).data('init')) {  
             $(this).data('init', true);
             $(this).mousehold(200, function(i){$(this).trigger('click');}); 
 //            $(this).hover(function(){$(this).addClass('ui-state-hover'); },function(){$(this).removeClass('ui-state-hover');});  
             $(this).trigger('mouseover');  
         }  
	});
	$('.mvup, .mvdown, .remove, .add, .play, .playme, .pladd, .edit, .save, .download, .searchart, li, .refresh, .back, input.btn , input.redbtn, .listmode, .covermode, .flowmode, .closer ')
	.live('mouseover', function() {  
         if (!$(this).data('init')) {  
             $(this).data('init', true);  
             $(this).hover(function(){$(this).addClass('ui-state-hover'); },function(){$(this).removeClass('ui-state-hover');});  
             $(this).trigger('mouseover'); 
         }  
	});
	$('#loading').dialog({
		autoOpen:false,bgiframe: true,modal: true,dialogClass: 'loading',height: 50,minHeight:50,width:220,minWidth:220,resizable:false,show: 'scale',hide: 'scale'
  	});
	loadcontrols();
});

$(window).load(init);

function init(){
	update_Box("pg_",page,0,true);
	return false;
}

function switchTheme(locStr){
	$('body').append('<div id="overlay"></div>');
	$('body').css({height:'100%'});
	$('#overlay')
		.css({display: 'info',position: 'absolute',top:0,left: 0,width: '100%',height: '100%',zIndex: 999999,background: 'black url(images/loading.gif) no-repeat center'})
		.fadeIn(500,function(){
					$('#stylesheet').attr('href','themes/' + locStr + '/css/default.css');
					//var cssLink = $('<link href="themes/'+ locStr +'/css/default.css" type="text/css" rel="Stylesheet" />');
					//$('head').append(cssLink);
					files.imgpath = 'themes/'+ locStr +'/images/';
					//setthemeuser(sessionuserid,locStr);
					setscreen('theme',locStr);
					if ($('#dummy-element').width()==2) {$('#overlay').fadeOut(500,function(){$(this).remove();});
					} else {
					setTimeout(function(){
						if ($('#dummy-element').width()==2) {
							$('#overlay').fadeOut(500,function(){$(this).remove();});
						}
					}, 400);
		}
	});
	return false;
}

function switchLang(locStr){
		$('body').append('<div id="overlay"></div>');
		$('body').css({height:'100%'});
		$('#overlay')
			.css({display: 'info',position: 'absolute',top:0,left: 0,width: '100%',height: '100%',zIndex: 999999,background: 'black url(images/loading.gif) no-repeat center'})
			.fadeIn(500,function(){
						setscreen('lang',locStr.substring(locStr.length-5, locStr.length));
						window.location.href=locStr;
 						setTimeout(function(){$('#overlay').fadeOut(500,function(){$(this).remove();});}, 400);
			});
	return false;
}

function switchPage(newpage){
	//prevpage = page;
	page = newpage;
	update_Box('pg_',newpage,0,false);
	//$('#' + prevpage).parent().toggleClass('ui-state-active');
	//$('#' + page).parent().toggleClass('ui-state-active');
	return false;
}

function preload(imgStr) {
	var i;
	imgURL = new Array();
	imgURL = imgStr.split(',');
	imgDat = new Array();
	for(i=0; i<imgURL.length; i++) { 
		imgDat[i] = new Image();
		imgDat[i].src = imgURL[i];
	}
	return false;
}

function albums_mode(type,itemid) {
	$.getJSON(files.post,{func:'al_mode',type:type,itemid:itemid}, function(data){
		$(wrp.cont).animate({'opacity': 'hide'}, 'slow');
		$(wrp.cont).queue(function () {
			$(this).html(data.contents);
			loadEffects();
//			$(wrp.foot).html(data.foot).show('scale',function(){
					
//			});
			$(this).dequeue();
		}).animate({ 'opacity': 'show'}, 'slow');
	});
	return false;
}

function update_Box(func,type,itemid,loadpl){
	allFields = $([]).add($(wrp.head)).add($(wrp.cont)).add($(wrp.foot));
	switch(type)
	{
		case 'letter':
			bc.parenttype = '';
			bc.parentitem = '';
			break;
		case 'artist':
			if (bc.parenttype !== '' && bc.childtype == 'all'){
				bc.parenttype = bc.childtype;
				bc.parentitem = bc.childitem;	
			}
			break;
		case 'stats':
			bc.parenttype = '';
			bc.parentitem = '';
			itemid = '';
			break;	
		default:
			bc.parenttype = bc.childtype;
			bc.parentitem = bc.childitem;
	}
	bc.childitem = itemid;
	bc.childtype = type;
	if ($('#albumdialog').length){$('#albumdialog').dialog('close');}
	$('#loading').dialog('open').queue(function () {
		allFields.fadeOut('fast');
		$.getJSON(files.post,{func:func,type:type,itemid:itemid,loadpl:loadpl,page: page,parent: bc.parenttype,parentitem:bc.parentitem,child: bc.childtype,childitem: bc.childitem}, function(data){
			if(data.pl===true){
				$('#pl_title').html(data.pl_head);
				$('#pl_info').html(data.pl_info);
				plview(data.pl_contents);
			}
			$(wrp.head).html(data.head).animate({ 'opacity': 'show'}, 'slow',function(){
//				$(wrpcont).html(data.contents).show('scale',function(){
				$(wrp.cont).queue(function () {
					$(this).html(data.contents);
					loadEffects();
					$(this).dequeue();
				});
				$(wrp.cont).animate({ 'opacity': 'show'}, 'slow',function(){
					$(wrp.foot).html(data.foot).animate({ 'opacity': 'show'}, 'slow',function(){
						$("#breadcrumb").html(data.breadcrumb);
						$('#loading').dialog('close');
					});
				});
			});
		});
		$(this).dequeue();
	});
 return false;
}

function loadcontrols() {
	preload('images/player/play_big.png, images/player/pause_big.png');
	var strcontrols =   '	<div id="details" class="details" >' + 
						'		<div id="albumart"  style="float:left;margin-left:5px;" ><img src="images/blankalbum.gif" height="60" width="60" ></div>' + 
						'		<div id="title"></div>' + 
						'		<div id="author"><b>'+constants.nosongplaying+'</b></div>' + 
						'		<div id="album"></div>' + 
						'	</div>'+
						'	<div id="htmlcontrols" class="controls"></div>';

	$('#control').prepend(strcontrols);
	createPlayer('player1', currentPlaylist);
	createControls('htmlcontrols');
	$('.controls a').each(function(){$(this).hover(function() {$(this).css('background-image', $(this).css('background-image').split('.png)').join('_hot.png)'));}, 
    			   		  function() {$(this).css('background-image', $(this).css('background-image').split('_hot.png)').join('.png)'));
	});});
	return false;
}

function loadEffects() {
	if ($('#ul_list').length) {
		loadScroll();
		if ($('li.cover_item').length){
			$('#ul_list').animate({height: '300px'}, 1500 );
				loadAllImages();
			$('li.cover_item').each(function(){
				var id = $(this).attr('rel');
				$(this).tooltip({arrows:false,useElement:'#ctext_'+id,tooltipId:'texttip',xOffset:30,yOffset:20});
			});
		}
	}
	if ($('#myImageFlow').length) {
		var instanceOne = new ImageFlow();
		instanceOne.init({ 
			ImageFlowID:'myImageFlow',
			onClick: function() {loadalbumdialog('albumdialog',$(this).attr('rel'));}, //updateBox('album',$(this).attr('rel'));
			reflections: false,
			reflectionP: 0.0,
			textLoading: constants.loadingimg,
/*			preloadImages: true,
			imageFocusM: 1,
			imagesM: 1,
*/			xStep: 100,
			slider: false
		});
	}
	if ($('#coverimg').length) {$('#coverimg').tooltip({arrows:false,useElement:'#coverbig',tooltipId:'imagetip',xOffset:80,yOffset:150});}
	//if ($('a.stickytip').length) {$('a.stickytip').stickytip();}
	return false;
}

function loadInfodialog(type,itemid) {
	$.getJSON(files.post,{func:'frm_dialog',type:type,itemid:itemid}, function(data){
		$.setForm({id:type,form:data.contents,width:500,title:data.head,success:function(){loadScroll();}});
	});
 	return false;
}

function loadalbumdialog(type,itemid) {
	$.getJSON(files.post,{func:'frm_dialog',type:type,itemid:itemid}, function(data){
		$.setForm({id:type,form:data.contents,width:300,title:data.head,show:'scale',hide:'scale',position:[250,140],success:function(){loadAllImages();}});
	});
 	return false;
}

function loadAllImages() {
    $('.loadable-image').each(function(){       
        var loader = $(this);
        image_src = loader.attr('src');
        loader.html('<img src="' + files.spinner + '" />');
    	var img = new Image();
    	$(img).load(function () {
			$(this).css('display','none');
        	loader.html(this);
        	loader.removeClass('loadable-image');
        	loader.removeAttr('src');
        	$(this).fadeIn('slow');
        	//if($(this).parent().next('.cover_links').length){
	        	$(this).parent().next('.cover_links').show('slide');
			//} 	
 		}).error(function() { $(this).attr('src', files.failimage).show(); }).attr('src', image_src).fadeIn('slow');
  });
  return false;
}

function stopCurrentSong(){
	$('.currentplay').each(function(){$(this).removeClass('currentplay').removeClass('ui-state-active');});
	$('#author').html('<b>'+constants.nosongplaying+'</b>').fadeIn();
	$('#title').html('');
	$('#album').html(''); 
	$('#albumart').html('<img src="images/blankalbum.gif" height="60" width="60">').fadeIn();
	currentPlaylist=null;
	player.sendEvent('LOAD', currentPlaylist);
	return false;
}

function getRandItems(type){
	$('#breadcrumb').empty();
	$.getJSON(files.post, {func:'getrandomitems', randomkind: type }, function(data){$('#rand_items').html(data.contents);});
	return false;
}
		
function deletePlaylist(id,priv){
	$.setConfirmMsg({
		message:constants.QuestionErasePl,
		url:files.post,
		data:{func:'pl_',type: 'deletePlaylist', itemid: id, priv:priv },
		success:function(data){
			bc.clearbc = 0;
			var ul_parent= $('#playlist_'+ id).parent().attr('id');
			$('#'+ul_parent).html(data.pl_contents);
		}
	});
	//$.getJSON(files.post,{func:'pl_',type: 'deletePlaylist', itemid: id, priv:priv },function(data){bc.clearbc = 0;alert(data.pl_contents)});
	return false;
}

function setPlaylist(data) {
	currentPlaylist = null;
	currentPlaylist = new Array();
	for( i=0; i < data.length; i++) {
		currentPlaylist[i] = {  
			file:'stream.php/'+data[i].url, 
			duration:parseInt(data[i].duration, 10),
			author:data[i].artist,
			title:data[i].name,
			album:data[i].album,
			id:data[i].id,
			albumid:data[i].albumid
		}
	}
	player.sendEvent('LOAD', currentPlaylist);
	return false;
}

function plrem(item,index){
	var clip;
	if(currentItem < index-1) {clip=currentItem; } else {clip=(currentItem-1);}
	$.getJSON(files.post, {func:'pl_',type: 'pl_rem', itemid:item}, function(data){
		$('#pl_info').html(data.pl_info);
		$('#' + item).remove();
		var rnd = Math.round(Math.random() * 1000);
		$.getJSON('playlist.php',{type:'pl',id:0,rnd: rnd },function(data){setPlaylist(data);player.sendEvent('ITEM', clip);});
	});
	return false;
}

function plsave(type,data){
	if(type=='open'){
    	var save_form = '<form onsubmit="return plsave(\'save\',this)" method="get" action=""><strong>' + constants.PlName + '</strong><br/><input type="text" name="save_pl_name" id="save_pl_name" size="25" /><br/><input type="checkbox" name="pl_private" id="pl_private" /> '+constants.PlPrivate+'<br/><br/><input type="submit" class="btn '+wrp.button_style+'" value="'+constants.PlSaveBtn+'" /> <input type="button" class="btn '+wrp.button_style+'" onclick="plsave(\'close\',0); return false;\" value="'+constants.PlCancelBtn+'" /></form> ';
    	   $.setForm({form:save_form});
    }
    else if(type=='save'){
    	var pl_name = data.save_pl_name.value;
    	var priv = 0;
    	var ul_id= '#pl_public';
    	if(data.pl_private.checked===true) {priv = 1; ul_id='#pl_private';}
   		$.getJSON(files.post,{func:'pl_',type:'pl_save',itemid: pl_name, priv: priv}, function(data){$(ul_id).html(data.pl_contents);$('#form').dialog('close');});
    }
    else if(type=='close') {$('#form').dialog('close');}
    return false;
}
	
function pladd(type,id){
	$.getJSON(files.post,{func:'pl_add', type:type,itemid: id}, function(data){
		var rnd = Math.round(Math.random() * 1000);
			if ((currentPosition==0)) { 
				$.getJSON('playlist.php',{type:'pl',id:0, rnd: rnd },function(data2){setPlaylist(data2);});
			} else {
				var index =currentItem;
				var second =currentPosition;
				$.getJSON('playlist.php',{type:'pl',id:0, rnd: rnd },function(data2){
					setPlaylist(data2);
					player.sendEvent('ITEM', index);
					player.sendEvent('SEEK', currentPosition);
				});
			}
		$('#pl_info').html(data.pl_info);
		plview(data.pl_contents);
	});
	return false;
}

function plclear(){
	$.getJSON(files.post, {func:'pl_',type: 'pl_clear', itemid:0}, function(data){
		$('#playlist').fadeOut().empty().fadeIn();
		$('#pl_info').html(data.pl_info);
		if (!(currentState == "PLAYING"))  { stopCurrentSong(); } 
	});
	return false;
}

function plrefresh(){
	$.getJSON(files.post,{type:'pl_view', itemid:0}, function(data){
		$('#pl_info').html(data.pl_info);
		plview(data.pl_contents);
	});
	return false;
}

function plview(new_data){
 	$('#playlist').html(new_data).sortable({
 		revert: true,
 		placeholder: 'ui-state-highlight',
 		scroll: true,
 		update: function(event, ui) {
 			$.ajax({
				type: 'GET',
				url: files.post,
				data:'func=pl_&type=pl_order&'+$('#playlist').sortable('serialize',{attribute:'rel'}),
				success: function(){
					$.getJSON('playlist.php',{type:'pl',id:0},function(data){
						if ((currentState == "PLAYING")) {
							var plst = null;
							plst = player.getPlaylist();
							var songid=plst[currentItem].id;
							var songposition=currentPosition;
							setPlaylist(data);
							var playMeIndex = $('ul#playlist li').index( $('li[rel$="song_'+songid+'"]',$('ul#playlist')) );
							//alert(songid+'  '+playMeIndex+'  '+songposition);
							player.sendEvent('ITEM', playMeIndex);
							player.sendEvent('SEEK', songposition);
						} else {
							setPlaylist(data);
							player.sendEvent('STOP');
						}
					});
				}
			});
		}
	});
	$('#playlist').bind('mousewheel', function(event, delta) {
		this.scrollTop -= delta * 10;
        event.preventDefault();
	    //return false;
     })
//    .scrollwheel({accesible: false})
	.serialScroll({
		items:'li', // Selector to the items ( relative to the matched elements, '#sections' in this case )
		prev:'div.listnav a.mvdown',// Selector to the 'prev' button (absolute!, meaning it's relative to the document)
		next:'div.listnav a.mvup',// Selector to the 'next' button (absolute too)
		axis:'y',// The default is 'y' scroll on both ways
		duration:900,
		force:true,
		stop:true,
		lock:false,
		step:3,
		cycle:false, //don't pull back once you reach the end
		easing:'easeOutQuart', //use this easing equation for a funny effect
		jump: true, //click on the images to scroll to them
		lazy:true
	});
	$('#playlist .remove').live('click', function(){var index=$('.remove').index(this);plrem(this.parentNode.id, index);});
	var playMeLink = $('ul#playlist li a.playme');
	playMeLink.click(function(){playMe(playMeLink.index(this));});
	$('#playlist li').each(function(){$(this).hover(function(){$(this).addClass('ui-state-hover');},function(){$(this).removeClass('ui-state-hover');});});
	$('.tip').tooltip({arrows:true});
	return false;
}	

//function breadcrumb(){$.get(callbackurl,{func: "breadcrumb",page: page,parent: bc.parenttype,parentitem:bc.parentitem,child: bc.childtype,childitem: bc.childitem},function(new_data){$("#breadcrumb").html(new_data);});}

function play(type,id){
	var rnd = Math.round(Math.random() * 1000), durationA = 0, durationB = 0;
	$.getJSON('playlist.php',{type:type,id:id,items:id, rnd: rnd },function(data){
		setPlaylist(data);
/*		if (player.isPlaying()) { 
			var plplaying= player.getPlaylist();
			for( i=0; i < plplaying.length; i++) {
				durationA=plplaying[i].duration +durationA;
			}
			for( i=0; i < data.length; i++) {
				durationB=parseInt(data[i].duration, 10) + durationB;
			}
			//alert(durationA+ '  ' +durationB);
			if(durationA!=durationB){
		  		player.stop();player.setPlaylist(data);
        		player.play();
        	} 
      	} else { 
        	player.setPlaylist(data);
        	player.play();
      	} 
 */
	});
	return false;
}

function playMe(pliindex){
	var rnd = Math.round(Math.random() * 1000);
	$.getJSON('playlist.php',{type:'pl',id:0,rnd: rnd },function(data){
		setPlaylist(data);
		player.sendEvent('ITEM',pliindex);
	});
	return false;
}

function randList(form){
	var type = $('#random_type').val();
	if(type === ''){
		setMsgText(constants.MustChRandom,timer, 'info');
		return false;
	}
	var num = $('#random_count').val();
	var items ='';
	if(type !== 'all'){
		$('#items option:selected').each(function(i) {
			items +=$(this).val()+':';
		});
		if(items === ''){
		  setMsgText(constants.MustChRandomItem,timer, 'info');
		  return false;
		}
	}
	var itemid = JSON.stringify([{"name":"type","value":type},{"name":"num","value":num},{"name":"items","value":items}]);
	loadInfodialog('random',itemid);
	loadScroll();
	return false;
}

function insert_art(type,k,i,m){$.ajax({ type: 'GET', url: files.post,dataType:'json',data:{ type: type, k:k, itemid:i, m:m }, success: function(data) {setMsgText(data.contents,timer,data.foot);$('#searchart').dialog('close');},error: function(objeto) {setMsgText('Error: '+objeto,timer,'alert');}});}
function addmusic(form){$('#current').html(form.musicpath.value);return false;}
function adminAddUser(form){
	$('#breadcrumb').empty();
   	if(form!==''){
		var firstname = $('#firstname'),
			lastname = $('#lastname'),
			username = $('#username'),
			email = $('#email'),
			password = $('#password'),
			password2 = $('#password2'),
			allFields = $([]).add(firstname).add(lastname).add(username).add(email).add(password).add(password2),
			bValid = true,
			itemid = null;
			allFields.removeClass('ui-state-error');
	
		bValid = bValid && checkLength(firstname,'firstname',3,16);
		bValid = bValid && checkLength(email,'email',6,80);
		bValid = bValid && checkLength(lastname,'lastname',3,16);
		bValid = bValid && checkLength(username,'username',3,16);
		bValid = bValid && checkLength(password,'username',6,16);
		bValid = bValid && checkRegexp(firstname,/^[a-z]([0-9a-z_])+$/i,'Username may consist of a-z, 0-9, underscores, begin with a letter.');
		// From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
		bValid = bValid && checkRegexp(email,/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,constants.InvalidEmail+' eg. ui@jquery.com');
		bValid = bValid && checkRegexp(lastname,/^[a-z]([0-9a-z_])+$/i,'Username may consist of a-z, 0-9, underscores, begin with a letter.');
		bValid = bValid && checkRegexp(password,/^([0-9a-zA-Z])+$/,'Password field only allow : a-z 0-9');
		bValid = bValid && checkRegexp(username,/^([0-9a-zA-Z])+$/,'Username field only allow : a-z 0-9');
					
		if(password.val() != password2.val() && password2.val()){
			setMsgText(constants.NoMatchPass, timer, 'alert');
			$('#password2').addClass('ui-state-error').val('').focus();
			bValid = bValid && false;
		}
		if (bValid) {
			var item = $('#adduser input,select').serializeArray();
			itemid = JSON.stringify(item);
			$.getJSON(files.post,{func:'frm_dialog',type:'adminAddUser', itemid:itemid}, function(data){
				bc.clearbc=0;
				if(data.foot!==0){
					setMsgText(data.contents, timer, 'check');
					$('#adduser').dialog('close');
					adminEditUsers(0,'','');
				}else{
					setMsgText(data.contents, timer, 'alert');
					$('#username').addClass('ui-state-error').val('').focus();
				}
			});
			//return false;
		}
	}else{
		if ($('#adduser').length){
			$('#adduser').dialog('close');
		}else{
			itemid =JSON.stringify([{"name":"update","value":0}]);
   			$.getJSON(files.post,{func:'frm_dialog',type:'adminAddUser', itemid:itemid}, function(data){$.setForm({id:'adduser',form:data.contents,title:data.head});});
   		}
   	}	
	return false;
}
	
function adminEditUsers(user,action,form){
	var itemid = null;
	$('#breadcrumb').empty();
	if(user!==0){
		switch(action)
		{
			case 'del':
				itemid =JSON.stringify([{"name":"userid","value":user},{"name":"action","value":"del"}]);
				$.setConfirmMsg({
					message:constants.QuestionUserDel,
					url:files.post,
					data:{func:'frm_dialog',type:'adminEditUser', itemid:itemid},
					success:function(data){bc.clearbc=0;if(data.foot==1) { setMsgText(data.contents, timer, 'check');$('#editusers').dialog('close'); } }
				});
				break;
			case 'mod':
				itemid =JSON.stringify([{"name":"userid","value":user},{"name":"action","value":action},{"name":"active","value":form.active.value},{"name":"perms","value":form.perms.value}]);
   				$.getJSON(files.post,{func:'frm_dialog',type:'adminEditUser', itemid:itemid}, function(data){
					if(data.foot==1) {
						setMsgText(data.contents, timer,'check');
						$('#usersedit').dialog('close');
					}
   				});
				break;
			default:
				var ample= $('#editusers').dialog().offset();
				var posicio=ample.left+306;
				if ($('#usersedit').length){ 
					$('#usersedit').dialog('close');
				} else {
					itemid =JSON.stringify([{"name":"userid","value":user},{"name":"action","value":"user"}]);
	   				$.getJSON(files.post,{func:'frm_dialog',type:'adminEditUser', itemid:itemid}, function(data){$.setForm({id:'usersedit',form:data.contents,position:[posicio,'top'],title:data.head});});
				}
				break;
		}
	} else {
			if ($('#editusers').length){
				$('#editusers').dialog('close');
			}else{
				itemid =JSON.stringify([{"name":"userid","value":user}]);
   				$.getJSON(files.post,{func:'frm_dialog',type:'adminEditUser', itemid:itemid}, function(data){$.setForm({id:'editusers',form:data.contents,title:data.head});});
			}
	}
	return false;
}

function setscreen(type,itemid){
	$.getJSON(files.post,{func:'setscreen',type:type, itemid:itemid});
	return false;
}
	
function searchMusic(form,reload){
	if(reload){
		$('#foot').fadeOut('slow').empty();
		$('#searchbox').removeClass('ui-state-error').val('').focus();
	}else{
		if(form.searchbox.value === '' || form.searchbox.value === constants.SearchTerm){
			setMsgText(constants.SearchTermEmpty, timer,'alert');
			$('#searchbox').addClass('ui-state-error').focus();
		}else{
			var loading = $('#loading').clone();
			loading.removeClass();//.css("color","red");
			$('#foot').html('...').append(loading).show('fast');
			$('#breadcrumb').empty();
			$('#searchbox').removeClass('ui-state-error');
			var itemid =JSON.stringify([{"name":"terms","value":form.searchbox.value},{"name":"option","value":form.search_options.value}]);
			$.getJSON(files.post,{type:'searchMusic', itemid:itemid}, function(data){
				$('#foot').animate({ opacity: 0}, 500 ).queue(function () {
					$(this).empty().html('<div id="searchcontents">'+data.contents+'</div><div id="searchfoot">'+data.foot+'</div>');
					loadScroll();
					$(this).dequeue();
				});
				$('#foot').animate({ opacity: 1}, 500 );
			});
   		}
	}
	return false;
}

function searchthemes(){$.getJSON(files.post, { func: 'searchthemes'}, function(data){setMsgText(data, timer, 'none');return false;});}

function clearDB(){
	$.setConfirmMsg({
		message:'<p><span class="ui-icon ui-icon-alert" style="margin: 0pt 7px 20px 0pt; float: left;"></span>'+constants.QuestionDatabaseDel+'</p>',
		url:files.post,
		data:{func: 'resetDatabase'},
		success:function(data){setMsgText(data.contents,timer,data.foot);plrefresh();}
	});
}

function sendInvite(form){
	var email = $('#remail'), bValid = true;
	email.removeClass('ui-state-error');
	bValid = bValid && checkRegexp(email,/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,constants.InvalidEmail+'eg. ui@jquery.com');
	if (bValid) {
		email.removeClass('ui-state-error');
		$.getJSON(files.post,{func:'createInviteCode',email:email.val()}, function(data){setMsgText(data.contents, timer, data.foot);$('#remail').val('');return false;});
	}
	return false;
}

/*function formatTime(time) {
	var m, s;
	m = parseInt(time / 60, 10);
	if (m < 10) { m = '0' + m; }		
	s = parseInt(time % 60, 10);
	if (s < 10) { s = '0' + s; }
	return m + ':' + s;
}*/

function loadScroll() { 
	//.scrollwheel({accesible: false})
	$('#ul_list').css('height', '200px').css('overflow', 'hidden')
	.bind('mousewheel', function(event, delta) {
            	this.scrollTop -= delta * 10;
            	event.preventDefault();
	            return false;
     })
	.serialScroll({
		items:'li', // Selector to the items 
		prev:'p a.mvdown',// Selector to the 'prev' button (absolute!, meaning it's relative to the document)
		next:'p a.mvup',// Selector to the 'next' button (absolute too)
		axis:'y',// The default is 'y' scroll on both ways
		duration:900,
		force:true,
		stop:true,
		lock:false,
		step:3,
		cycle:false, //don't pull back once you reach the end
		easing:'easeOutQuart', //use this easing equation for a funny effect
		jump: true, //click on the images to scroll to them
		lazy:false
	});
	$('#ul_list li').each(function(){$(this).hover(function(){$(this).addClass('ui-state-hover');},function(){$(this).removeClass('ui-state-hover');});});
	$('.tip').tooltip({arrows:true});
	return false;
}

function OpenDialog(id,target,strtitle) {
	var w = null, h = null, item = null;
	var dialog = $('<div id="'+id+'"></div>').insertAfter('#footer'),enable = false, url=null, effect= 'slide', position = ['left','top'],cache=true,add=false,dataType='html';
	switch(id) {
		case 'browse':
			w = 1050; h = 650; item = target; url = target;
			break;
		case 'fwrite':
			w = 700; h = 550; item = target; url = target;
			break;
		case 'download':
			w = 500; h = 300; url ='download.php?id='+target; effect = 'scale';	position = ['center','center'];	enable = true;
			strtitle= decodeURIComponent(strtitle);
			break;
		case 'add':
			w = 800; h = 600; item = target; url = files.post+'?func=openAddForm'; cache = false; add = true; dataType = 'json';
			break;
		case 'searchart':
			w = 800; h = 'auto'; item = target; url = files.post+'?type=searchart&itemid='+target; cache = false; dataType = 'json';
			break;
	}
	
	var loading = $('#loading').clone();
	$(dialog).html(loading).dialog({ modal: false, dialogClass: 'formclass', position:position, width: w, height: h, hide:effect, show:effect, title: strtitle, bgiframe:true,
 			open:function() {$(this).parents('.ui-dialog:first').find('.ui-dialog-titlebar').css('background','none').css('border',0);},
 			close:function(){var removeTemp = function() {$('#'+id).dialog('destroy').remove();if(enable) {$('.iframediv').remove();}}; window.setTimeout(removeTemp,1000);}
        });
    $.ajax({type: 'GET',url: url,dataType:dataType,cache: cache,success: function(html) {    
		$(dialog).animate({ opacity: 0}, 500 );
        $(dialog).queue(function () {
        	if(dataType=='json') { $(this).empty().html(html.contents);$('.ui-dialog-title').html(html.head);}
        	else {$(this).empty().html(html);}
	  		if(add){
				$.getScript('js/jquery.uploadify.min.js');
				$.getScript('js/jquery.FileTree.min.js');
				$.getScript('js/listen_add.js');
			}
			if(enable) {
				window.setTimeout(function(){callIframe('download.php?id='+target+'&d=1');},3000);
			}
            $(this).dequeue();
        });
        $(dialog).animate({ opacity: 1}, 500 );
       /* $(dialog).queue(function () {
            $(this).removeAttr("id");
            $(this).dequeue();
        });*/
	  }
 	});
 	return false;
}

function callIframe(url,callback) {
    $(document.body).append('<div class="iframediv"><IFRAME id="ifdownload" src="#" frameborder="0" height="0" width="0"  name="ifdownload" style="display:none;"></iframe></div>');
    $('#ifdownload').attr('src', url);
    $('#ifdownload').load(function() {
       	$("#loading\\[2\\]").fadeOut().html('Done').fadeIn();
        window.setTimeout(function(){$('#download').dialog('close');$('.iframediv').remove();},3000);
    });
    return false;
}

function OpenIF(id,strtitle) {
	//allow open an iframe
	var dialog = $('<div id="'+id+'"><iframe id="'+id+'frame" style="margin:0;padding:0;border:0;overflow:hidden;" src="add.php" width="100%" height="100%"></iframe></div>').insertAfter('#footer'); 
 	$(dialog).dialog({modal: false, width: 800 , height: 600, hide:'scale', show:'scale', title: strtitle, bgiframe:true,
 		open:function(){
 				// Set specific variable to represent all iframe tags.
				var iFrames = $('#'+id+'frame');
				// Resize heights.
				function iResize(){
				// Iterate through all iframes in the page.
//					for (var i = 0, j = iFrames.length; i < j; i++)	{iFrames[i].style.height = iFrames[i].contentWindow.document.body.offsetHeight + 'px';}
					iFrames.style.height = $('#add').offsetHeight + 'px';
				}
			// Check if browser is Safari or Opera.
			if ($.browser.safari || $.browser.opera){
				// Start timer when loaded.
				$('#'+id+'frame').load(function(){setTimeout(iResize, 0);});
				// Safari and Opera need a kick-start.
					var iSource = iFrames.src;
					iFrames.src = '';
					iFrames.src = iSource;
			} else {
				// For other good browsers.
				$('#'+id+'frame').load(function() {	this.style.height = $('#add').offsetHeight + 'px';});
			}
 		},
		close:function(){var removeTemp = function() {$('#'+id).dialog('destroy').remove();}; window.setTimeout(removeTemp,1000);}
 	});
	return false;
}

function editSettings(form) {
	var itemid = null;
	if(form){
		var item=$('#editSettings input,select').serializeArray();
		itemid = JSON.stringify(item);
		$.getJSON(files.post,{func:'frm_dialog',type:'editSettings', itemid:itemid}, function(data) {bc.clearbc = 0;$('#editSettings').dialog('close');setMsgText(data.contents, timer,'check');});
	}else{
		itemid =JSON.stringify([{"name":"update","value":0}]);
   		$.getJSON(files.post,{func:'frm_dialog',type:'editSettings', itemid:itemid}, function(data) {$.setForm({id:'editSettings',form:data.contents,title:data.head});});
	}
	return false;
}
function editUser(form) {
	var itemid = null;
	if(form){
		var firstname = $('#firstname'),
			email = $('#email'),
			lastname = $('#lastname'),
			allFields = $([]).add(firstname).add(email).add(lastname),
			bValid = true;
		allFields.removeClass('ui-state-error');
		bValid = bValid && checkLength(firstname,'firstname',3,16);
		bValid = bValid && checkLength(email,'email',6,80);
		bValid = bValid && checkLength(lastname,'lastname',3,16);
		bValid = bValid && checkRegexp(firstname,/^[a-z]([0-9a-z_])+$/i,"Username may consist of a-z, 0-9, underscores, begin with a letter.");
		// From jquery.validate.js (by joern), contributed by Scott Gonzalez: http://projects.scottsplayground.com/email_address_validation/
		bValid = bValid && checkRegexp(email,/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,constants.InvalidEmail+'eg. ui@jquery.com');
		bValid = bValid && checkRegexp(lastname,/^[a-z]([0-9a-z_])+$/i,"Username may consist of a-z, 0-9, underscores, begin with a letter.");
		if (bValid) {
			var item=$('#mysettings input,select').serializeArray();
			itemid = JSON.stringify(item);
			$.getJSON(files.post,{func:'frm_dialog',type:'editUser', itemid:itemid}, function(data){allFields.removeClass('ui-state-error');$('#myaccount').dialog('close');setMsgText(data.contents, timer,'info');return false;});
		}
	}else{
		if ($('#myaccount').length){
			$('#myaccount').dialog('close');
		}else{
			itemid =JSON.stringify([{"name":"update","value":0}]);
   			$.getJSON(files.post,{func:'frm_dialog',type:'editUser', itemid:itemid}, function(data){$.setForm({id:'myaccount',form:data.contents,title:data.head});});
	   	}
   	}	
	return false;
}
function editUserPasswd(form) {
	var bValid = true,
		password =$('#password'),
		password2 =$('#password2'),
		old_password = $('#old_password'),
		itemid = null;
	allFields = $([]).add(old_password).add(password).add(password2);
	if(form){
		allFields.removeClass('ui-state-error');
		if(!old_password.val()){
			setMsgText('T_debes escribir la clave anterior', timer, 'alert');
			$('#old_password').addClass('ui-state-error').val('').focus();
			bValid = bValid && false;
		}
		bValid = bValid && checkLength(old_password,'password',5,16);
		bValid = bValid && checkLength(password,'password',5,16);
		if(form.password.value != form.password2.value && form.password2.value){
			setMsgText(constants.NoMatchPass, timer, 'alert');
			$('#password2').addClass('ui-state-error').val('').focus();
			bValid = bValid && false;
		}
		if(bValid) {
			var item=$('#mypasswd input').serializeArray();
			itemid = JSON.stringify(item);
			$.getJSON(files.post,{func:'frm_dialog',type:'editUserPasswd', itemid:itemid}, function(data){
				bc.clearbc = 0;
				if(data.foot!==0) {
					setMsgText(data.contents, timer,'info');
					allFields.val('').removeClass('ui-state-error');
					$('#mypasswd').dialog('close');
				} else {
					setMsgText(data.contents, timer,'alert');
					$('#old_password').addClass('ui-state-error').val('').focus();
				}
			});
		}
	}else{
		var ample= $('#myaccount').dialog().offset();
		var posicio=ample.left+306;
		if ($('#mypasswd').length){
			$('#mypasswd').dialog('close');
		}else{
			itemid =JSON.stringify([{"name":"update","value":0}]);
   			$.getJSON(files.post,{func:'frm_dialog',type:'editUserPasswd', itemid:itemid}, function(data){$.setForm({id:'mypasswd',form:data.contents,position:[posicio,'top'],title:data.head});});
		}
	}
	return false;
}

function checkLength(o,n,min,max) {
	if ( o.val().length > max || o.val().length < min ) {
		o.addClass('ui-state-error');
		setMsgText('Length of ' + n + ' must be between '+min+' and '+max+'.',timer,'alert');
		return false;
	} else {
		return true;
	}
}

function checkRegexp(o,regexp,n) {
	if ( !( regexp.test( o.val() ) ) ) {
		o.addClass('ui-state-error');
		setMsgText(n,timer,'alert');
		return false;
	} else {
		return true;
	}
}

function writeFileTags(form) {
		var data = $('#'+form.id).serialize();
			$.ajax({ 
				type: 'POST',
				url: files.post,
				data: 'func=writeFileTags&'+data,
				success: function(newdata) { setMsgText( newdata, timer ,'info');return false;}
			});
		return false;
}

function setMsgText(text,time,type) {
	var spanclass;
	switch(type) {
		case 'check':
			spanclass = '<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>';
			break;
		case 'alert':
			spanclass = '<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>';		
			break;
		case 'info':
			spanclass = '<span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 50px 0;"></span>';
			break;
		default:
			spanclass = '';		
	}
	var msg='<p>'+spanclass+text +'</p>';
	$('#message').html(msg).dialog({
			open:function() {
				$(this).parents('.ui-dialog:first').find('.ui-dialog-titlebar').removeClass('ui-widget-header');
				$(this).parents('.ui-dialog:first').find('.ui-dialog-buttonpane').css('border',0);
				$(this).parents('.ui-dialog:first').css('background-image','info');
  			},
  			close:function(){
				var removeTemp = function() {$('#message').dialog('destroy');};
				window.setTimeout(removeTemp,1000);
			},
			bgiframe: true,show: 'scale',hide: 'scale',shadow: false,resizable:false
		});
	if(time!==0){
		window.setTimeout(function() { $('#message').dialog('close');return false;},time);
	}
	return false;
}

(function($) {
	$.extend({
 		setConfirmMsg: function(options){
    		var opt = {	message: null, url: null, data:null, success: null }; //,type:'GET', cache:false, error: null
  			$.extend(opt,options);
   			$('<div id="confirmation"><p>'+opt.message+'</p></div>').appendTo('body').dialog({
				title: constants.ConfirmTitle,
				resizable:false,
				width: 350,
				height: 160,
				modal: true,
				bgiframe:true,
				shadow: false,
				buttons: {
					Ok: function() {
						//$.ajax({url: opt.url,data:opt.data,type: opt.type,cache: opt.cache,timeout: 0,error: opt.error,success: opt.success});
						$.getJSON(opt.url,opt.data,opt.success);
						$(this).dialog('destroy').remove();
					},
					Cancel: function() {$(this).dialog('destroy').remove();}
				}
			});
  		}
	});
})(jQuery);

(function($) {
	$.extend({
		setForm: function(options){
			var opt = { form: null, id: 'form', url: null, data:null, type:'GET', position:['left','top'], show:'slide',
				hide:'slide', width:null, height:undefined, cache:false, error: null, success: null, title: undefined, buttons:null
			};
			$.extend(opt,options);
			$('<div id="'+opt.id+'">'+opt.form+'</div>').appendTo('body').dialog({
				open:function() {
						$(this).parents('.ui-dialog:first').find('.ui-dialog-titlebar').removeClass('ui-widget-header').css('padding',0);
						$(this).parents('.ui-dialog:first').find('.ui-dialog-buttonpane').css('border',0);
						$(this).parents('.ui-dialog:first').css('background-image','info');
						//$(this).css({'margin-top':'-3px', 'padding': '1px'});
				}, 
				dialogClass: 'formclass',
				resizable:false,
				position:opt.position,
				width: opt.width,
				height: opt.height,
				show:opt.show,
				hide:opt.hide,
				bgiframe:true,
				minHeight: 80,
				title: opt.title,
				shadow: false,
				focus: opt.success,
				close:function(){
					var removeTemp = function() {$('#'+opt.id).dialog('destroy').remove();};
					window.setTimeout(removeTemp,1000);
				},
				buttons:opt.buttons
			});
		}
   });
})(jQuery);