/**
 * File front.js.
 */

jQuery(document).ready(function($) {
    
	'use strict';
	
	function processScroll(){
				
		$('img[data-src]').each(function(index, element) {
			
			var rect = element.getBoundingClientRect();
			
			if (rect.top >= 0 && rect.left >= 0 && rect.top <= (window.innerHeight || document.documentElement.clientHeight)) {
				
				var copy = $(element).clone();
				copy.attr('src',$(element).data('src'));
				copy.css('opacity',0).removeAttr('data-src');
				$(element).replaceWith(copy);
				copy.animate({'opacity' : 1},2000);
				
			}
			
		});
		
	}
	
	if($('.lazy-load').length === 1){
		
		processScroll();
		$(window).scroll(function() {
			processScroll();
		});
	
	}
	
	if($('.infinite-scroll').length === 1){
		
		var loading = false,
			scrollHandling = {
				allow: true,
				reallow: function() {
					scrollHandling.allow = true;
				},
				delay: 400
			},
			data = {
				action: 'gismo_ajax_load_more',
				nonce: gismo_loadmore.nonce,
				page: $('.fetch-more').data('page'),
				query: gismo_loadmore.query,
			};
	
		$(window).scroll(function(){
			
			if(!loading && scrollHandling.allow || $(window).scrollTop() + $(window).height() == $(document).height()) {
				
				scrollHandling.allow = false;
				setTimeout(scrollHandling.reallow, scrollHandling.delay);
				
				if($('.fetch-more').length === 1 && $('.fetch-more').offset().top - $(window).scrollTop() < $(window).height()) {
					
					loading = true;
					
					load_more(data);
	
				}
				
			}
			
		});
		
		load_more(data);
	
	}
	
	$(window).scroll(function(){
		
		if($(window).scrollTop() > 25){
			
			$('#masthead').addClass('blue-bg shrink');
			
		}else{
			
			$('#masthead').removeClass('blue-bg shrink');
			
		}
	
	});
	
	function load_more(data){
		
		$.post(gismo_loadmore.url, data, function(results) {
					
			if(results.success) {
				
				$('#main').find('.uk-grid').append(results.data);
				$('.fetch-more').replaceWith(results.fetcher);
				
				if($('body').height() < $(window).height() && $('.fetch-more').length === 1){
					data.page = $('.fetch-more').data('page');
					load_more(data);
				}
				
				loading = false;
				
			}
			
		}).fail(function(xhr, textStatus, e) {
			
		});
			
	}
	
	$('ul#gismo-primary-menu').superfish({
		delay:       500,                            // one second delay on mouseout
		animation:   {opacity:'show',height:'show'},  // fade-in and slide-down animation
		speed:       'fast',                          // faster animation speed
		autoArrows:  false                            // disable generation of arrow mark-up
	});
	
});