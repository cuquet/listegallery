$(function() {
// create tree folder
	loadTree();
// accordion
	$('#accordion').accordion({header: 'h3',fillSpace: true,alwaysOpen: false,animated: 'swing'});
	$('#accordionResizer').resizable({resize: function() {$('#accordion').accordion('resize');},minHeight: 140});
// load uploadify
	$('#uploadify').uploadify({
		'uploader': 'swf/uploadify.swf',
		'cancelImg': 'images/cancelbutton.png',
		'queueID' : 'fileQueue',
		'script': 'includes/listen_upload.php',//files.upload,
		'folder': files.newpath,
		'multi': true,
		'method': 'GET',
//		'checkScript': 'check.php',
		'scriptData': {'add' : 1 },
		'wmode':'transparent',
		'buttonImg':'images/add/add.png',
		'height': 14,
		'width': 20,
		'fileDesc': 'image,zip,mp3 and txt',
		'fileExt': '*.jpg; *.jpeg; *.png; *.gif; *.zip; *.mp3; *.txt',
		onOpen:function(event,queueID ,fileObj){
			$(this).uploadifySettings('folder',files.newpath);
		},
		onAllComplete: function(event,data){
			var mb = Math.round((data.allBytesLoaded / 1024000));
			setMsgText(constants.Added+'<b>'+data.filesUploaded+'</b>, <b>'+data.errors+'</b> errors. '+ mb +' Mb uploaded',3000,'check');
			$('#btnGlobalCancel').trigger('click');
		},
		onError: function (event, queueID ,fileObj, errorObj) {
			var msg;
			if (errorObj) { 
				msg = 'Error '+errorObj.type+': '+errorObj.info+' in ' + fileObj;
			}
			setMsgText('<p>'+msg+'</p>', 3000 ,'alert');			
			$('#uploadify ' + queueID).fadeOut(250, function() { $('#uploadify ' + queueID).remove();});
			return false;
		}
	});
// action buttons	
	$('#btnGlobalCancel').click(function() {$('#musicpath').text(files.path);$('#MusicFolder').val(files.path);$('#RenFolder').val('');loadTree();$('#uploadify').uploadifyClearQueue();});
// scan for new music to add form
	$('#submit_scan').click(function(){
		var hasError = false;
		var musicFolder = $('#MusicFolder').val();
		if(musicFolder === '') {
			setMsgText(constants.Rootdir+files.path+constants.WillScan,3000,'alert');
			hasError = true;
			$('#MusicFolder').val(files.path);
			}
		if(hasError === false) {
			$.ajax({
           		type: 'POST',
           		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
           		url:files.upload,
           		data: {	add: 4, 
						PATH: musicFolder, 
						PHPSESSID : sessionuserid
						},
           		beforeSend: function() { $('#formScan').hide(); $('#loading\\[1\\]').append('<img src="images/progress_bar.gif" alt="'+constants.loadingT+'" />');	},
           		success:function(songs) { 
           				var ntime=songs.split('&')[0];
        				var nsongs=songs.split('&')[1];
        				var nerrors=songs.split('&')[2];
           				if(nsongs===0){
           					setMsgText('No New Songs Added to Database. '+ntime+' seconds.',3000,'alert');
           					$('#loading\\[1\\]').text('');$('#MusicFolder').val(files.path);$('#formScan').show(); 
           				}
           				if(nsongs>0 || nerrors>=0){
           					setMsgText(constants.Added+'<b>'+nsongs+constants.Songsadded+'<b> '+nerrors+'</b> errors.<b> '+ntime+'</b> seconds.',3000,'check');
           					$('#loading\\[1\\]').text('');$('#MusicFolder').val(files.path);$('#formScan').show(); 
           				}else{
           					setMsgText(songs,3000,'alert');
           					$('#loading\\[1\\]').text('');$('#MusicFolder').val(files.path);$('#formScan').show();
           				}
           			},
           		error: function(error) { setMsgText(error,3000,'alert'); }
         	}); 
		}
		return false;
	});
});

function createFolder(){
	if ($('#NewFolder').val() === '') { 
		showMsg(constants.FieldEmpty, 'alert'); 
		$('#NewFolder').focus();
		return false;
	 }
	$.ajax({
    	type: 'POST',
    	contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
    	url:files.upload,
    	data: {
				add: 2, 
				FOLDERNAME: $('#NewFolder').val(), 
				PATH: files.newpath,
				PHPSESSID : sessionuserid 
			}, 
		success:function() { setMsgText(constants.MkdirDone,3000,'check'); loadTree(); },
		error: function(error) { setMsgText(error,3000,'alert'); }
 	});
	$('#NewFolder').val('');
}

function renameFolder(){
	if ($('#RenFolder').val() === '') { 
		showMsg(constants.FieldEmpty, 'alert'); 
		$('#RenFolder').focus();
		return false;
	}
	$.ajax({
    	type: 'POST',
    	contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
    	url:files.upload,
    	data: {
    			add: 3,
				OLDNAME: foldername,
				NEWNAME: $('#RenFolder').val(), 
				PATH: files.newpath,
				PHPSESSID : sessionuserid 
		},
		success:function() { setMsgText(constants.RenDone,3000,'check'); loadTree(); }
    });   					  
	$('#musicpath').text(files.path);
	$('#MusicFolder').val(files.path);
	$('#RenFolder').val('');
}

function loadTree(){
	$('#foldertree').fileTree({ 
		root: files.path ,
		script: files.filetree, 
		folderEvent: 'click', 
		expandSpeed: 750, 
		collapseSpeed: 750, 
		expandEasing: 'easeOutBounce', 
		collapseEasing: 'easeOutBounce', 
		loadMessage: constants.loadingT , //'Loading...', 
		multiFolder: false }, 
		function(file) {
			return false;
	});
}

