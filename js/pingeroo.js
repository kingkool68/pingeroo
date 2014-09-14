jQuery(document).ready(function($) {
	
	/**
	* The Message
	*
	**/
	var $message = $('#message');
	$('<textarea id="message-clone" rows="1" />').insertAfter('#message');
	$message.on('keyup', function() {
		$('#character-count').html( this.value.length );
		var clonedMessage = document.getElementById('message-clone')
		clonedMessage.value = this.value;
		this.style.height = clonedMessage.scrollHeight + 'px';
		/*
		this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
		*/
		
	}).focus();
	
	$('form').on('submit', function(e) {
		
		var theMessage = $.trim( $message.val() );
		if( !theMessage ) {
			e.preventDefault();
			return
		}
	});



	/**
	* The Services
	*
	**/
	$('#the-services select').on('change', function(e) {
		$this = $(this);
		var val = $this.val();
		if( val == '-1' ) {
			return;
		}
		
		if( val == 'all' ) {
			$('#the-services input[type="checkbox"]').prop('checked', true);
			$(':selected', $this).prop('value', 'none').text('None');
			resetTheServicesSelect();
			return;
		}
		
		if( val == 'none' ) {
			$('#the-services input[type="checkbox"]').prop({
				'checked': false,
				'indeterminate': false	
			});
			$(':selected', $this).prop('value', 'all').text('All');
			resetTheServicesSelect();
			return;
		}
		
		if( val == '+1' ) {
			createNewPingerooGroup();
			return;
		}
		
		$services = $('#the-services');
		$('input[type="checkbox"]', $services).prop({
			'checked': false,
			'indeterminate': false	
		});
		
		var ids = val.split(',');
		for( i=0; i<ids.length; i++ ) {
			var id = ids[i];
			$('input[value="' + id + '"]', $services).prop('checked', true).change();
		}
	});
	
	$('#the-services').on('change', 'input[type="checkbox"]', function() {
		//Is this part of a group? 
		$this = $(this);
		var $parent = $this.parents().eq(3);
		if( $parent.is('li') ) {
			var totalCheckBoxes = $parent.find('input[name="pingeroo-services[]"]');
			var checkedCheckBoxes = $parent.find('input[name="pingeroo-services[]"]:checked');
			
			var makeParentIndeterminate = true;
			if( !checkedCheckBoxes.length ) {
				makeParentIndeterminate = false;	
			}
			
			var makeParentChecked = false;
			if( checkedCheckBoxes.length == totalCheckBoxes.length ) {
				makeParentChecked = true;
				makeParentIndeterminate = false;
			}
			
			$parent.find('.has-children').prop({
				'indeterminate': makeParentIndeterminate,
				'checked' : makeParentChecked
			});
		}
		
		if( $this.hasClass('has-children') ) {
			var toCheck = false;
			if( $this.is(':checked') ) {
				var toCheck = true;
			}
			
			$this.parent().siblings('ul').find('input[name="pingeroo-services[]"]').each(function(index, element) {
				this.checked = toCheck;
			});
		}
	});
	
	function createNewPingerooGroup() {
		$services = $('#the-services');
		var checked = $( 'input[name="pingeroo-services[]"]:checked', $services );
		if( checked.length < 1 ) {
			alert( 'No accounts are checked. Check some accounts and then select "Create a New Group" again.' );
			resetTheServicesSelect();
			return false;
		}
		
		var values = [];
		checked.each(function(index, element) {
			values.push(this.value);
		});
		var groupName = prompt('What do you want to name your new group?');
		
		var data = {
			'action': 'add_pingeroo_group',
			'name': groupName,
			'values': values.join(','),
			'nonce': $('#pingeroo-create-group-nonce').val()
		};

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: data
		}).success( function( resp ) {
			var data = resp.data;
			var $options = $('option', $services);
			
			$options.slice( 0, $options.length - 2 ).remove();
			$(data.html).prependTo( $services.find('select') );
			
			$('select .' + data.name, $services).prop('selected', true);
			
		}).fail( function( response ) {
			alert('Error: ' + response.responseText );
		});
		
		//Select the default option
		resetTheServicesSelect()
	}
	
	function resetTheServicesSelect() {
		$( '#the-services option[value="-1"]' ).prop('selected', true);
	}
	
	
	/**
	* When to post?
	*
	**/
	Date.prototype.getFormattedTime = function () {
		//var hours = this.getHours() == 0 ? "12" : this.getHours() > 12 ? this.getHours() - 12 : this.getHours();
		var hours = this.getHours();
		if( hours < 10 ) {
		hours = '0' + hours;
		}
		var minutes = (this.getMinutes() < 10 ? "0" : "") + this.getMinutes();
		var ampm = this.getHours() < 12 ? "AM" : "PM";
		var formattedTime = hours + ":" + minutes /*+ " " + ampm;*/
		return formattedTime;
	}
	
	var now = new Date();
	console.log( now.getFormattedTime() );
	$('#time').val( now.getFormattedTime() );
});