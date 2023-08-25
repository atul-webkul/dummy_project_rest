$wk_mu = jQuery.noConflict();

(function($wk_mu) {
    
    $wk_mu(document).ready(function() { 

    	$wk_mu('.upload-csv-btn').on('click', function() {

    		$wk_mu("#upload_csv").trigger("click");

    	});

    	$wk_mu('.upload-zip-btn').on('click', function() {

    		$wk_mu("#upload_zip").trigger("click");

    	});

    	$wk_mu('#upload_csv').change(function() {
	        
	        var fullpath = $wk_mu('#upload_csv').val();

	        var filename = fullpath.split('\\').pop();
	        
	        $wk_mu('.csv_filename').val(filename);
	    
	    });

	    $wk_mu('#upload_zip').change(function() {
	        
	        var fullpath = $wk_mu('#upload_zip').val();

	        var filename = fullpath.split('\\').pop();
	        
	        $wk_mu('.zip_filename').val(filename);
	    
	    });

	    $wk_mu('#select_admin').on('change', function() {
    	
		 	if ($wk_mu(this).is(":checked")) {
	
		 		$wk_mu('.seller-select-field').hide();
	
		 	}
	
		 	else {
	
		 		$wk_mu('.seller-select-field').show();
	
		 	}
	
		});

    });

})($wk_mu);