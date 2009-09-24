/*********************************************************
*	@desc:		Listen music web database
*	@authors:	Angel Calleja	(code injection 2008-2009)
*				Cyril Russo 	(code injection 2009)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
*				styckytip plugin for jquery
*				tooltip plugin for jquery
**********************************************************/

(function($) {
	$.fn.tooltip = function(options){
		// default configuration properties
		var defaults = {	
			xOffset: -80,  //10,		
			yOffset: 35, // 25,
			tooltipId: 'tooltip',
			arrows:false,
			content: '',
			useElement: ''
		}; 
		var opt = $.extend(defaults, options);  
		var content, tooltipInner;
		this.each(function() {
			var id = opt.tooltipId;
			tooltipInner = '<div class="tooltip-pointer-down"><div class="tooltip-pointer-down-inner"></div></div>';
			//tooltip = '<div id="' + id+'"></div>';
			var name =$(this).attr('rel');
			var data = $('#'+name).clone(true).show();
			var s = '';
			$(this).mouseover(function(e){											 							   
				content = (opt.content !== '') ? opt.content : data;
				content = (opt.useElement !== '') ? $(opt.useElement).html() : content;
				if (content !== '' && content !== undefined 	&& $('#' + id).length === 0) {	
					$('<div id="' + id+'"></div>').appendTo('body').addClass('tooltip ui-widget ui-state-error ui-corner-all').append(content).append(tooltipInner)
						.css('position','absolute').css('top',(e.pageY - opt.yOffset) + 'px').css('left',(e.pageX + opt.xOffset) + 'px')						
						.css('display','none').css('width','auto').fadeIn('slow');
//alternative anim		.css('position','absolute').css('top',(e.pageY - options.yOffset*5/2) + 'px').css('left',(e.pageX + options.xOffset) + 'px')					
//						.css('display','none').css('width','auto').animate({opacity: 'show', top: (e.pageY - options.yOffset*2) + 'px'}, 'slow');
					fixToolTipColor();
					if (!opt.arrows) {$('#' + id).find('.tooltip-pointer-down').remove();}
				}
			}).mousemove(function(e){
				var border_top = $(window).scrollTop(); 
				var border_right = $(window).width();
				var sourcewidth =$(this).width();
				var left_pos, top_pos;
				var xoffset = opt.xOffset;
				var yoffset = opt.yOffset;
				if(border_right - (xoffset *2) >= $('#' + id).width() + e.pageX){
					left_pos = e.pageX+xoffset;
				} else{
					left_pos = border_right-$('#' + id).width()-xoffset;
				}
				if(border_top + (yoffset *2)>= e.pageY - $('#' + id).height()){
					top_pos = border_top +yoffset;
				} else{
					top_pos = e.pageY-$('#' + id).height()-yoffset;
				}
				if( (left_pos-$(this).offset().left) < (sourcewidth/8)){
					s='tooltip-right';
				} else if ((left_pos-$(this).offset().left) > (sourcewidth*3/4)){
					s='tooltip-left';
				} else {s='';}
				$('#' + id).removeClass('tooltip-right tooltip-left').css({left:left_pos, top:top_pos}).addClass(s);
				
			}).mouseout(function(){	
				$('#' + id).fadeOut('fast').remove();
			});	
		});
	};
	function fixToolTipColor(){
		//grab the bg color from the tooltip content - set top border of pointer to same
		$('.tooltip-pointer-down-inner').each(function(){
			var bWidth = $('.tooltip-pointer-down-inner').css('borderTopWidth');
			var bColor = $(this).parents('.tooltip').css('backgroundColor');
			$(this).css('border-top', bWidth+' solid '+bColor);
		});	
	}
})(jQuery);

/*(function($) {
	$.fn.stickytip = function(options){
		// default configuration properties
		var defaults = {	
			xOffset: 0, 
			yOffset: -10,
			attrib:'rel'
		}; 
		var opt = $.extend(defaults, options);  
		//var stickytipId = opt.stickytipId;
		this.each(function() {
			var name =$(this).attr(opt.attrib);
			var data = $('#'+name).clone(true).show();
			var closeLink = '<div id="stickytip-close" ><a href="#" class="stickytip-close ui-corner-all" style="float:right;"><span class="ui-icon ui-icon-close"></span></a></div>';
			$(this).mouseover(function(e){
				if (data !== '' && data !== undefined  && $('#stickytip').length === 0 ){	
					$('<div id="stickytip"></div>').appendTo('body').addClass('stickytip ui-widget ui-state-error ui-corner-all').prepend(closeLink)
						.css({'position':'absolute','top':(e.pageY - opt.yOffset) + 'px','left':(e.pageX + opt.xOffset) + 'px'})						
						.css('display','none').css('width','auto').fadeIn('slow');
					$('#stickytip-close').click(function(){$(this).parent().fadeOut('fast').remove();});
				}
			});
			$('#stickytip').mouseout(function(){$(this).fadeOut('fast').remove();});
		});
	};
})(jQuery);*/