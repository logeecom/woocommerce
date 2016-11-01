jQuery(document).ready(function($) {
	$('#ce-admin-refresh-description-button').click(function(t){
		// add html textarea content to data to send back
		ce_admin_data['content'] = $('textarea[name="content"]').val();
		jQuery.post(ce_admin_data.ajax_url, ce_admin_data, function(response) {
			if(typeof(response.description) !== 'undefined'){
				$('textarea[name="_channel_engine_description"]').val(response.description);
			}
		});
	});
});