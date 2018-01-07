/**
 * File admin.js.
 */

jQuery(document).ready(function($) {
	
	$('#sidebars-form, #menus-form').sortable({
		items: '.item',
		cursor: 'move',
		tolerance: 'pointer',
		scroll: true,
		scrollSensitivity: 30,
		delay: 100,
		revert: 500,
		cancel: ':input,button,[contenteditable="true"]'
	});
    
	$('.save-btn').on('click',function(e){
			
		var $this = $(e.target), defaultValue;
		
		defaultValue = $this.html();
		$this.html('Saving');
		
		$this.closest('form').find('.item').each(function(index, element) {
            
			$(element).find('.input').each(function(i, el) {
			
				var new_value = $(el).attr('name').replace(/\[\d+\]/g, '[' + (index + 1) + ']');
				$(el).attr('name',new_value);
				
			});
			
        });
		
		var $packet = $this.closest('form').serializeArray();
		console.log($packet);
		$.ajax({
			url: ajaxurl,
			data: $packet,
			success:function(data) {
				
				$this.html(defaultValue);
				
				data = JSON.parse(data);
				
				var $options = {
					message : data.msg,
					timeout : 5000,
					pos     : 'top-center'
				};
				
				if(data.error === false){
					
					$options.status = 'success';
					
				}else{
					
					$options.status = 'danger';
					
				}
				
				UIkit.notify($options);
			},
			error: function(errorThrown){
				UIkit.notify({
					message : errorThrown,
					status  : 'danger',
					timeout : 5000,
					pos     : 'top-center'
				});
			}
		});
		
	});
	
	$('input[name="settings[layout][orientation]"]').on('click',function(e){
		if($(e.target).val() === 'horizontal'){
			$('#header-config').hide();
		}else{
			$('#header-config').show();
		}
	});
	
	$('input[name="settings[layout][blog][layout]"]').on('click',function(e){
		if($(e.target).val() === 'grid'){
			$('.grid-option').hide();
		}else{
			$('.grid-option').show();
		}
		$('.grid-option').find('input').removeAttr('checked');
	});
	
	$('.add-btn').on('click',function(e){
			
		var $this = $(e.target),
			wrapper = $this.closest('.add-wrapper'),
			clone = wrapper.clone(true),
			selected = [];
			
		var count = (wrapper.siblings('form').find('.item').length + 2);
		
		wrapper.find('.input').each(function(index, element) {
			
			if($(element).find('option').length > 0){
				selected.push($(element).find('option:selected').index());
			}
			
			var new_value = $(element).attr('name').replace(/\[\d+\]/g, '[' + count + ']');
            $(element).attr('name',new_value);
        });
		
		clone.find('h3').remove();
		clone.find('.add-btn').parent().replaceWith('<div class="uk-push-2-3 uk-margin-top" align="right"><a class="uk-button remove-btn">Remove</a></div>');
		clone.attr('class','item uk-margin-bottom pad-1x rd5').attr('style','border:2px solid #eee');
		
		clone.find('select').each(function(index, element) {
            $(element).find('option').eq(selected[index]).prop('selected',true);
        });
		
		wrapper.siblings('form').find('.save-btn').show();
		wrapper.siblings('form').find('.save-btn').before(clone);
		wrapper.siblings('form').find('.save-btn').trigger('click');
		
		wrapper.find('input[type="text"], select').val('');
		
	});
	
	$('#gismo-switcher').on('click','.remove-btn',function(e){
			
		var $this = $(e.target),
			form = $this.closest('form');
		
		$this.closest('.item').detach();
		form.find('.save-btn').trigger('click');
		if(form.find('.item').length === 0){
			form.find('.save-btn').hide();
		}
		
	});
	
	var frame;
	
	$('.upload-media').on('click', function(event){
	
		event.preventDefault();
		var wrapper = $(event.target).closest('.media');
		
		if(frame){
			frame.open();
			return;
		}
		
		frame = wp.media({
				title: 'Select or Upload an Image',
				button: {
				text: 'Use this image'
			},
			multiple: false
		});
		
		
		frame.on('select', function(){
		
			var attachment = frame.state().get('selection').first().toJSON();
			
			if(wrapper.find('.media-container').hasClass('site-background')){
			
				wrapper.find('.media-container div').html('<div class="rd5" style="display:block; width:100%; height:170px; background:url('+attachment.url+') no-repeat center center; background-size:cover;"></div>');
				
			}else{
				
				if(wrapper.find('.media-container img').length === 0){
					wrapper.find('.media-container').append('<img src="'+attachment.url+'"/>');
				}else{
					wrapper.find('.media-container img').replaceWith('<img src="'+attachment.url+'"/>');
				}
			
			}
		
			wrapper.find('.media-url').val(attachment.url);
		
		});
		
		frame.open();
	
	});
	
	$('.delete-media').on('click', function(event){
	
		event.preventDefault();
		var wrapper = $(event.target).closest('.media');
		
		if(wrapper.find('.media-container').hasClass('site-background')){
			
			wrapper.find('.media-container div').html('<div class="rd5" style="display:block; width:100%; height:165px; line-height:165px; background-color:#fff; text-align:center; font-size:18px; border:2px solid #d9d9d9;">No Background Set</div>');
			
		}else{
			
			wrapper.find('.media-container').html('');
		
		}
	
		wrapper.find('.media-url').val('');
	
	});
	
	$('input[name="settings[layout][header_config]"]').on('click',function(event){
		var value = $(event.target).val();
		
		$('.header-layout').hide();
		$('select').attr('disabled','disabled');
		$('#' + value + '-layout').show();
		$('#' + value + '-layout').find('select').removeAttr('disabled');
	});
	
});