jQuery(document).ready(function($) {
	var message = $('#message');
	message.on('keyup', function() {
		var t = $(this);
		$('#character-count').html(t.val().length);
	});
	
	$('form').on('submit', function(e) {
		e.preventDefault();
		
		var message = $.trim( $('#message').val() );
		if( !message ) {
			return;
		}
		
		var services = [];
		$('#services input:checked').each(function(index, element) {
			services.push( $(this).val() );
		});
		if( !services ) {
			return;
		}
		
		var data = {
			action: 'pingeroo',
			data: {
				message: message,
				services: services
			}
		};
		$.post(ajaxurl, data, function(response) {
			console.log(response);
		});
	});
});