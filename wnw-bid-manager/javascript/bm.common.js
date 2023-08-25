jQuery(function ($) {
    $('img#map_example').hide();
    $("#map_toggle").click(function () {
        $('img#map_example').toggle("slow");
    });

    /* $('#conPastBids, #average_for_bids, #conAccepted, #conSupResponse, #conActiveBids, #supActiveBids, #bm_responses_list, #supBidsInArea, #supBidHistory, #bm_report_table, #noResponsesGiven')
        .on('init.dt', function (evt, settings) {
            if (settings && settings.aLengthMenu && settings.fnRecordsTotal && settings.fnRecordsTotal() < settings.aLengthMenu[0]) {
                // hide pagination controls, fewer records than minimum length
                $(settings.nTableWrapper).find('.dataTables_paginate, .dataTables_length, .dataTables_info').hide();
            }
        })
        .DataTable(); */
		
		$('#conPastBids, #average_for_bids, #conAccepted, #conSupResponse, #conActiveBids, #supActiveBids, #bm_responses_list, #supBidsInArea, #supBidHistory, #bm_report_table, #noResponsesGiven').DataTable({
			responsive: true,
			 
		});

    // Date picker for the following IDs
    $('input[type=date]').datepicker();

    // This sets up AJAX to update the " . BM_USERMETA . " table when the user opts to hide a message
    $('.hide_notification_checkbox').click(function () {
        var elementid = this.id;
        var data = {
            'action': 'notification_actions',
            'inputid': elementid     // We pass php values differently!
        };
        // We can also pass the url value separately from ajaxurl for front end AJAX implementations
        jQuery.post(ajax_object.ajax_url, data, '');

        $('p.bm_notification_' + elementid).hide();
        $(this).closest('p.bm_note').hide();
    });

    // Create custom email address fields

    $(function () {
        var maxfields = 4;
        var scntDiv = $('.sc_email_wrap');
        var i = $('.sc_email_wrap div').size() + 1;

        $('#addScnt').on('click', function () {
            if (i < maxfields) {
                i++;
                $('<div><input type="email" id="sup_invite_email" class="sup_invite_email" size="50" name="sup_invite_email_' + i + '" value="" placeholder="ex. jondoe@jondoe.com" /> <a href="#" id="sc_remove_email">Remove</a></div>').appendTo(scntDiv);

                return false;
            }

            if (i >= maxfields) {
                alert('You may only send 3 emails at a time.');
            }
        });

        $('#sc_remove_email').on('click', function () {
            if (i > 2) {
                $(this).parent('div').remove();
                i--;
            }
            return false;
        });
    });

});