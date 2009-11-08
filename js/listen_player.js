var currentPlaylist = null;
var currentLength = 0;
var currentItem = -1; 
var previousItem = -1; 
var currentMute = false; 
var currentVolume = 80; 
var currentPosition = 0; 
var currentState = 'NONE';
var currentLoaded = 0;
var currentRemain = 0;
var player = null;
var controls = {
	trackId: 'track',
	volumeId: 'slider',
	playClass: 'pplay',
	pauseClass: 'pause',
	bufferClass: 'buffer',
	prevClass: 'prev',
	nextClass: 'next',
	stopClass: 'stop',
	timeClass: 'time',
	muteClass: 'mute',
	duration: 0 
};
var	template=   '<a class="'+controls.prevClass+'">prev</a>'+
				'<a class="'+controls.playClass+'">play</a>'+
				'<a class="'+controls.nextClass+'">next</a>'+
				'<a class="'+controls.stopClass+'">stop</a>'+
				'<div id="'+controls.trackId+'"></div>'+
				'<div class="'+controls.timeClass+'"></div>'+
				'<a class="'+controls.muteClass+'"><span class="ui-icon ui-icon-volume-on"></span></a>'+
				'<div style="display:none;" id="'+controls.volumeId+'_callout"></div>' +
				'<div id="'+controls.volumeId+'"></div>';

function playerReady(thePlayer) {
		player = window.document[thePlayer.id];
		addListeners();
		initVolume();
		initTrack();
}

function createPlayer(thePlayer, theFile) {
	var flashvars = {
		file:theFile, 
		autostart:"true",
		repeat:"list",
		playlist:"none"
	}

	var params = {
		allowfullscreen:"false", 
		allowscriptaccess:"always"
	}

	var attributes = {
		id:thePlayer,  
		name:thePlayer
	}

	swfobject.embedSWF("swf/player.swf", "embedded_player", "0", "0", "9.0.115", false, flashvars, params, attributes);
}

function createControls(wrap, options) {	
	if (typeof wrap == 'string') {
		wrap = document.getElementById(wrap);
	}
	if (!wrap) { return;	}
	// inner HTML
	if (!wrap.innerHTML.replace(/\s/g, '')) {
		wrap.innerHTML = template;		
	}	 
	$('.'+controls.playClass).click(function(){player.sendEvent('PLAY');});
	$('.'+controls.muteClass).click(function(){player.sendEvent('MUTE');});
	$('.'+controls.nextClass).click(function(){player.sendEvent('NEXT');});
	$('.'+controls.prevClass).click(function(){player.sendEvent('PREV');});
	$('.'+controls.stopClass).click(function(){player.sendEvent('STOP');stopCurrentSong();});
	return false;
}

function addListeners() {
	if (player) { 
		player.addControllerListener("ITEM", "itemListener");
		player.addControllerListener("MUTE", "muteListener");

		player.addModelListener("LOADED", "loadedListener");
		player.addModelListener("STATE", "stateListener");
		player.addModelListener("TIME", "positionListener");

		player.addViewListener("VOLUME", "volumeListener");
	} else {
		setTimeout("addListeners()",100);
	}
	return false;
}

function stateListener(obj) { //IDLE, BUFFERING, PLAYING, PAUSED, COMPLETED
	currentState = obj.newstate; 
	previousState = obj.oldstate; 
	if ((currentState == "PLAYING")) {
		$('.'+controls.playClass).css('background-image','url('+imgDat[1].src+')');
	}
	if ((currentState == "PAUSED")||(currentState == "IDLE")) {
		$('.'+ controls.playClass).css('background-image','url('+imgDat[0].src+')'); 
	}
/*	var tmp = document.getElementById("stat");
	if (tmp) { 
		tmp.innerHTML = "current state: " + currentState + 
		"<br>previous state: " + previousState; 
	}*/
	return false;
}

function positionListener(obj) { 
	currentPosition = obj.position; 
	$('.'+ controls.timeClass).html('<span>' + toTime(currentPosition) + '</span> | <strong>' + toTime(obj.duration) + '</strong>');	
	var trackWidth = $('#'+ controls.trackId).width();
	var x = parseInt(Math.min(currentPosition / obj.duration * trackWidth, trackWidth), 10);
	$('#'+ controls.trackId).slider('option', 'value', x);

/*	var tmp = document.getElementById("tim");
	if (tmp) { tmp.innerHTML = "position: " + x ; }*/
	return false;
}

function loadedListener(obj) { 
	currentLoaded = obj.loaded; 
	currentRemain = obj.total - currentLoaded;
	var trackWidth = $('#'+ controls.trackId).width();
	var x = parseInt(Math.min(currentLoaded / obj.total * trackWidth, trackWidth), 10);
	$('.'+ controls.bufferClass).width(x);
	
	/*var tmp = document.getElementById("ldd");
	if (tmp) { 
		tmp.innerHTML = "bytes loaded: " + currentLoaded +
				"<br>bytes remaining: " + currentRemain; 
	}*/
	return false;
}

function volumeListener(obj) { 
	currentVolume = obj.percentage; 
/*	var tmp = document.getElementById("vol");
	if (tmp) { tmp.innerHTML = "volume: " + currentVolume; } */
	return false;
}

function muteListener(obj) { 
	currentMute = obj.state; 
	if (currentMute){
		$('.'+controls.muteClass).html('<span class="ui-icon ui-icon-volume-off"></span>');
	} else {
		$('.'+controls.muteClass).html('<span class="ui-icon ui-icon-volume-on"></span>');
	}
/*	var tmp = document.getElementById("mut");
	if (tmp) { tmp.innerHTML = "mute: " + currentMute; }*/
	return false;
}

function itemListener(obj) { 
	if (obj.index != currentItem) {
 		previousItem = currentItem;
		currentItem = obj.index;

		if (previousItem == -1) { getPlaylistData(); }

/*		var tmp = document.getElementById("itm");
		if (tmp) { 
			tmp.innerHTML = "current item: " + currentItem +
				"<br>previous item: " + previousItem;
		}

		var tmp = document.getElementById("item");
		if (tmp) { tmp.innerHTML = "item: " + currentItem; }

		var tmp = document.getElementById("pid"); 
		if (tmp) { 
			tmp.innerHTML = "(received from the player with the id: <i><b>" + obj.id + "</b></i>)"; 
		} */

		printItemData(currentItem);
	}
	return false;
}

function printItemData(theIndex) {
	var plst = null;
	plst = player.getPlaylist();

	if (plst) {
		currentItem = theIndex;
		$('#albumart').html('<a href="#" onclick="update_Box(\'pg_\',\'album\','+plst[theIndex].albumid+',false);return false"><img src="'+files.cover+'?id='+plst[theIndex].albumid+'&thumb=4" /></a>');
		$('#author').html(''+ constants.author + ':<b>' + plst[theIndex].author + '</b>');
		$('#title').html('' + constants.title + ':<b>' + plst[theIndex].title + '</b>');
		$('#album').html('<a href="#" onclick="update_Box(\'pg_\',\'album\','+plst[theIndex].albumid+',false);return false">Album:<b>' + plst[theIndex].album + '</b></a>');
		$('.currentplay').each(function(){$(this).removeClass('currentplay').removeClass('ui-state-active');});
		$('#song_' + plst[theIndex].id).parent('li').toggleClass('ui-state-active').addClass('currentplay');
		$('#lsong_' + plst[theIndex].id).parent('li').toggleClass('ui-state-active').addClass('currentplay');
	} 	
	return false;
}

function getPlaylistData() { 
	var plst = null;
	plst = player.getPlaylist();

	if (plst) { 
		currentPlaylist = plst; 

/*		var txt = ''; 
		for(var i in currentPlaylist) { 
			txt += '<input type="checkbox" id="cb' + i + '" checked="checked" /> &nbsp; ';
			txt += currentPlaylist[i].title;
			txt += '<br />';
		}
		var tmp = document.getElementById("plstDat");
		if (tmp) { tmp.innerHTML = txt; }*/
	}
	return false;
}


/*function loadCheckedPlaylistData() { 
	if (currentPlaylist) { 
		var j = 0; 
		var lst = new Array();
		for(var i in currentPlaylist) { 
			if(document.getElementById('cb' + i).checked) {
				lst[j] = {
					author:currentPlaylist[i].author,
					description:currentPlaylist[i].description,
					duration:currentPlaylist[i].duration,
					file:currentPlaylist[i].file,
					link:currentPlaylist[i].link,
					image:currentPlaylist[i].image,
					start:currentPlaylist[i].start,
					title:currentPlaylist[i].title,
					type:currentPlaylist[i].type
				}
				j++;
			}
		}
		if(lst.length > 0) { player.sendEvent('LOAD', lst); }
	}	
}*/

function initTrack() {
	$('#'+controls.trackId).slider({
		range: 'min',
		min: 0,
		max: 100,
		value: 0,
		slide: function(event, ui) {
			if (player) {
				var plst = null;
				plst = player.getPlaylist();
				if (plst) { 
					var to = parseInt(ui.value / 100  * plst[currentItem].duration, 10);
					player.sendEvent('SEEK', to)		
				}
			} 
		}
	});
	$('#'+controls.trackId).prepend('<div class="buffer ui-corner-all"></div>');
	return false;
}
function initVolume() {
	$('#'+controls.volumeId+'_callout').hide();
	var calloutVisible = false;
	player.sendEvent('VOLUME', currentVolume);
	$('#'+controls.volumeId).slider({
		range: 'min',
		min: 0,
		max: 100,
		value: currentVolume,
		start: function(event, ui) {
			$('#'+controls.volumeId+'_callout').fadeIn('fast', function() { calloutVisible = true;});
		},
		stop: function(event, ui) { 
			if (calloutVisible === false) {
				$('#'+controls.volumeId+'_callout').fadeIn('fast', function() { calloutVisible = true;});
				$('#'+controls.volumeId+'_callout').css('left', $('a.ui-slider-handle').offset().left- $('#control').offset().left).text(Math.round(ui.value) +' %');
			}
			$('#'+controls.volumeId+'_callout').fadeOut('fast', function() { calloutVisible = false; });
		},
		slide: function(event, ui) {
			player.sendEvent('VOLUME', ui.value);
			$('#'+controls.volumeId+'_callout').css('left', $('#'+controls.volumeId+' a.ui-slider-handle').offset().left- $('#control').offset().left).text(Math.round(ui.value) +' %');
		}
	});
	$('#'+controls.volumeId+'_callout').text($('#'+controls.volumeId).slider('value') +' %');
	return false;
}

function getLength() { currentLength = player.getPlaylist().length; return(currentLength); };
function loadFile(theFile) { currentItem = -1; previousItem = -1; player.sendEvent('LOAD', theFile); };
function pad(val) {val = parseInt(val, 10); return val >= 10 ? val : '0' + val; };
function toTime(sec) {
// display seconds in hh:mm:ss format
	var h = Math.floor(sec / 3600);
	var min = Math.floor(sec / 60);
	sec = sec - (min * 60);
	if (h >= 1) {
		min -= h * 60;
		return pad(h) + ':' + pad(min) + ':' + pad(sec);
	}
	return pad(min) + ':' + pad(sec);
};
