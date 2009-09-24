(function($){
	$.fn.switcher = function(settings){
		var options = jQuery.extend({
			initialText: 'Switch',
			icon: 'ui-icon ui-icon-gear',
			listclass:'jquery-ui-switcher',
			width: 160,
			height: 200,
			closeOnSelect: true,
			buttonHeight: 16,
			onOpen: function(){},
			onClose: function(){},
			onSelect: function(){}
		}, settings);
		
		return this.each(function() {
			/* markup */
			var button = $('<a href="#" id="'+options.listclass+'-trigger" class="'+options.listclass+'-trigger" title="'+ options.initialText +'" ><span class="'+ options.icon +'"></span></a>');
			var switcherpane = $("."+options.listclass).find('div').removeAttr('id');
		
			/* button events */
			button.click(
				function(){
					var posY = $(this).offset().top;
					var posX = $(this).offset().left;
					if(switcherpane.is(':visible')){ switcherpane.spHide();$(this).css(button_default).removeClass('ui-state-active ui-state-hover ui-corner-bottom').addClass('ui-state-default ui-corner-all');}
					else{ $(this).addClass('ui-state-active ui-corner-bottom').removeClass('ui-corner-all');switcherpane.spShow(posY,posX);switcherpane.removeClass('ui-corner-all').addClass('ui-corner-top ui-corner-br'); }
					return false;
				}
			);
		
			//switcherpane.hover(
			//	function(){},
			//	function(){if(switcherpane.is(':visible')){$(this).spHide();button.css(button_default).addClass('ui-state-default ui-corner-all').removeClass('ui-corner-bottom ui-state-active ui-state-hover');}
			//});
		
			/* show/hide panel functions */
			$.fn.spShow = function(posY,posX){$(this).css({top: posY + options.buttonHeight - options.height - 27, left: posX /* -options.width + 24*/}).show('slide',{ direction: 'down' },'fast');options.onOpen(); };
			$.fn.spHide = function(){ $(this).hide('slide',{ direction: 'down' }, function(){options.onClose();});};
			switcherpane.find('a').click(function(){
				locStr=$(this).attr('rel');
				switcherpane.find('li').removeClass('ui-state-active');
				$(this).parent().toggleClass('ui-state-active');
				if(options.closeOnSelect && switcherpane.is(':visible')){ switcherpane.spHide();button.css(button_default).removeClass('ui-state-active ui-state-hover ui-corner-bottom').addClass('ui-state-default ui-corner-all'); }
				options.onSelect();
				return false;
			});
			/* Inline CSS */
			/*---------------------------------------------------------------------*/
			var button_default = {padding: '0.5em 3px',width: '16px', display: 'block',	height: options.buttonHeight,outline: '0'};
			//button css
			button.addClass('ui-state-default').removeClass('ui-corner-bottom').addClass('ui-corner-all').css(button_default)
			.hover(function(){$(this).addClass('ui-state-hover');},function(){ 
				 if( !switcherpane.is(':animated') && switcherpane.is(':hidden') ){
					$(this).addClass('ui-state-default').removeClass('ui-state-hover').addClass('ui-corner-all').removeClass('ui-corner-bottom').css(button_default);
				 }
			});
			//pane css
			switcherpane.addClass('ui-corner-all ui-widget-content')
			.css({position: 'absolute',	float: 'left', fontSize: '12px',padding: '8px 3px 3px',	zIndex: 5999, width: options.width-6 })
			.find('ul').css({ listStyle: 'none',margin: '0',padding: '0',overflow: 'auto',height: options.height })
			.end()
			.find('li').hover(function(){ $(this).addClass('ui-state-hover');}, function(){ $(this).removeClass('ui-state-hover');})
			.css({ width: options.width-30,	height: '',	padding: '2px', margin: '1px', clear: 'left', float: 'left'	})
			.end()
			.find('a').css({float: 'left', width: '100%', outline: '0 none'	})
			.end()
			.find('img').css({float: 'left', margin: '0 2px'})
			.end()
			.find('.Name').css({float: 'left', margin: '3px 0'})
			.end();
		
			$(this).prepend(button);
			$('body').append(switcherpane);
			switcherpane.hide();
			return this;
		});
	};
})(jQuery);
