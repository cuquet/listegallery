$(function() {
// create tree folder
	loadTree();
// accordion
	$('#accordion').accordion({header: 'h3',fillSpace: true,alwaysOpen: false,animated: 'swing'});
	$('#accordionResizer').resizable({resize: function() {$('#accordion').accordion('resize');},minHeight: 140});
// load uploadify
	$('#fileUpload').fileUpload({
		'uploader': 'swf/uploader.swf',
		'cancelImg': 'images/cancelbutton.png',
		'script': files.upload,
		'folder': files.newpath,
		'multi': true,
//		'buttonText': 'Add',
//		'checkScript': 'check.php',
		'scriptData': {'add' : 1 },
		'displayData': 'percentage',
		'wmode':'transparent',
//		'buttonImg':'images/add.png',
//		'height': 14,
//		'width': 18,
		'fileDesc': 'image,zip,mp3 and txt',
		'fileExt': '*.jpg; *.jpeg; *.png; *.gif; *.zip; *.mp3; *.txt',
		onAllComplete: function(event,data){
			setMsgText(constants.Added+'<b>'+data.filesUploaded+'<b>, '+data.errors+'</b> errors.',2000,'check');
			$('#btnGlobalCancel').trigger('click');
		},
		onError: function (event, queueID ,fileObj, errorObj) {
			var msg;
			if (errorObj.status == 404) {
				alert('Could not find upload script. Use a path relative to: '+'<?= getcwd() ?>');
				msg = 'Could not find upload script.';
			} else if (errorObj.type === "HTTP") {
				msg = errorObj.type+": "+errorObj.status;
			} else if (errorObj.type ==="File Size") {
				msg = fileObj.name+'<br>'+errorObj.type+' Limit: '+Math.round(errorObj.sizeLimit/1024)+'KB';
			} else {
				msg = errorObj.type+": "+errorObj.text;
			}
			setMsgText('<p>'+msg+'</p>', 2000 ,'alert');			
			$('#fileUpload' + queueID).fadeOut(250, function() { $('#fileUpload' + queueID).remove();});
			return false;
		}
	});
// uploadify action buttons	
	$('#btnUpload').click(function(){$('#fileUpload').fileUploadSettings('folder',files.newpath);$('#fileUpload').fileUploadStart();});
	$('#btnCancel').click(function(){$('#fileUpload').fileUploadClearQueue();});
	$('#btnGlobalCancel').click(function() {
		$('#musicpath').text(files.path);
		$('#MusicFolder').val(files.path);$('#RenFolder').val('');loadTree();$('#fileUpload').fileUploadClearQueue();
	});
// scan for new music to add form
	$('#submit_scan').click(function(){
		var hasError = false;
		var musicFolder = $('#MusicFolder').val();
		if(musicFolder === '') {
			setMsgText(constants.Rootdir+files.path+constants.WillScan,1000,'alert');
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
           					setMsgText('No New Songs Added to Database. '+ntime+' seconds.',1000,'alert');
           					$('#loading\\[1\\]').text('');$('#MusicFolder').val(files.path);$('#formScan').show(); 
           				}
           				if(nsongs>0 || nerrors>=0){
           					setMsgText(constants.Added+'<b>'+nsongs+constants.Songsadded+'<b> '+nerrors+'</b> errors.<b> '+ntime+'</b> seconds.',1000,'check');
           					$('#loading\\[1\\]').text('');$('#MusicFolder').val(files.path);$('#formScan').show(); 
           				}else{
           					setMsgText(songs,1000,'alert');
           					$('#loading\\[1\\]').text('');$('#MusicFolder').val(files.path);$('#formScan').show();
           				}
           			},
           		error: function(error) { setMsgText(error,1000,'alert'); }
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
		success:function() { setMsgText(constants.MkdirDone,2000,'check'); loadTree(); },
		error: function(error) { setMsgText(error,2000,'alert'); }
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
		success:function() { setMsgText(constants.RenDone,1000,'check'); loadTree(); }
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

