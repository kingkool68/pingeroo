jQuery(document).ready(function($) {
	var $message = $('#message');
	$message.on('keyup', function() {
		var t = $(this);
		$('#character-count').html(t.val().length);
	});
	
	$('form').on('submit', function(e) {
		e.preventDefault();
		
		var theMessage = $.trim( $message.val() );
		if( !theMessage ) {
			return;
		}
	});
	
	$('#the-services select').on('change', function(e) {
		$this = $(this);
		var val = $this.val();
		if( val == '-1' ) {
			return;
		}
		
		if( val == 'all' ) {
			$('#the-services input[type="checkbox"]').prop('checked', true);
			$(':selected', $this).prop('value', 'none').text('None');
			resetTheServicesSelect()
		}
		
		if( val == 'none' ) {
			$('#the-services input[type="checkbox"]').prop('checked', false);
			$(':selected', $this).prop('value', 'all').text('All');
			resetTheServicesSelect()
		}
		
		if( val == '+1' ) {
			createNewPingerooGroup();
		}
	});
	
	$('#the-services').on('change', 'input[type="checkbox"]', function() {
		//Is this part of a group? 
		$this = $(this);
		var $parent = $this.parents().eq(3);
		if( $parent.is('li') ) {
			var totalCheckBoxes = $parent.find('input[name="pingeroo-services[]"]');
			var checkedCheckBoxes = $parent.find('input[name="pingeroo-services[]"]:checked');
			var makeParentChecked = false;
			if( checkedCheckBoxes.length == totalCheckBoxes.length ) {
				makeParentChecked = true;
			}
			$parent.find('.has-children').prop('checked', makeParentChecked);
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
		
		//Select the default option
		resetTheServicesSelect()
	}
	
	function resetTheServicesSelect() {
		$( '#the-services option[value="-1"]' ).prop('selected', true);
	}
});