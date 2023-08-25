(function($) {

	$(document).on('cpm.markDone.after', function( event, res, self ) {

	    var wrap = $('.my-tasks'),
	    header = self.closest('ul.cpm-uncomplete-mytask');

	    if( $('.cpm-uncomplete-mytask').children('li').length <= 1 ) {
	        $('.cpm-no-task').fadeIn(1500);
	    }
	    if( header.children('li').length <= 1 ) {
	        header.closest('li').remove();
	    }
	    wrap.find('.cpm-mytas-current').text( res.current_task );
	    wrap.find('.cpm-mytas-outstanding').text( res.outstanding);
	    wrap.find('.cpm-mytas-complete').text(res.complete);
	});

	$(document).on('cpm.markUnDone.after', function(e, res, self) {

	    var wrap = $('.my-tasks'),
	    header = $('.cpm-my-todolists').children('li');
	    $.map( header, function( value, key ) {
	    	var li = $(value),
	    		length = li.find('.cpm-todo-completed').find('li').length;
	    	if( length == 0) {
	    		li.remove();
	    	}
	    });
	    if( $('.cpm-todo-completed').children('li').length <= 0 ) {
	        $('.cpm-no-task').fadeIn(1500);
	    }
	    wrap.find('.cpm-mytas-current').text( res.current_task );
	    wrap.find('.cpm-mytas-outstanding').text( res.outstanding);
	    wrap.find('.cpm-mytas-complete').text(res.complete );
	});

    $("body").on('change', ".cpm-mytask-switch-user", function(){
        var uid = $(this).val() ;
        var tab = $(this).attr('data-tab') ;

        var url = window.location.pathname+"?page=cpm_task&user_id="+uid+"&tab="+tab ;
         window.location.href =  url;

    });

    $("body").on('change', "#mytask-change-range", function(e){
         e.preventDefault();
         var v = $(this).val() ;
         var user = $('option:selected', this).attr('data-user') ;
         var data = {
                action: 'user_line_graph',
                range : v,
                user : user,
                _wpnonce: CPM_Vars.nonce
            };
            $.post(CPM_Vars.ajaxurl, data, function(resp) {
                if (resp) {

                   $('#mytask-line-graph').html(resp);
                }
            });
    });

    $("body").on('click', ".cpm-load-more-ua", function(e){
        e.preventDefault();

        var self = $(this),
            total = self.data('total'),
            start = parseInt(self.data('start')),
            data = {
                user_id: self.data('user_id'),
                offset: start,
                action: 'get_user_activity',
                _wpnonce: CPM_Vars.nonce
            };
        self.append('<div class="cpm-loading">Loading...</div>');
        $.get(CPM_Vars.ajaxurl, data, function(res) {
            res = $.parseJSON(res);
            if (res.success) {
                start = res.count + start;
                self.prev('.cpm_activity_list').append(res.content);
                self.data('start', start);
            } else {
                self.remove();
            }

            $('.cpm-loading').remove();
        });
    });

    $("body").on('click', 'input.cpm-uncomplete', function(event) {
        var element = this,
            task = $(element).next(),
            assignedUser = task.next(),
            timeLine = task.siblings('.cpm-current-date, .cpm-due-date'),
            spinner = '<span id="my-task-spinner" class="cpm cpm-spinner" style="height: 16px; width: 16px; margin-right: 5px; margin-top: -4px"></span>';

        $.post({
            url: CPM_Vars.ajaxurl,
            data: {
                action: 'cpm_task_complete',
                project_id: $(this).attr('data-project'),
                task_id: $(this).val(),
                _wpnonce: CPM_Vars.nonce,
                before: function() {
                    $(element).replaceWith(spinner);
                }
            }
        }).done(function (response) {
            if (response.success) {
                toastr.success(response.data.success);

                $(element).removeClass('cpm-uncomplete');
                $(element).addClass('cpm-complete');

                $("#my-task-spinner").replaceWith($(element));

                task.css({
                    'text-decoration' : 'line-through'
                });

                timeLine.css({
                    'background' : '#0090D9'
                });

                console.log(timeLine.attr('class'));

                var currentTaskTab = $('.cpm-my-currenttask div'),
                    currentTaskCount = parseInt(currentTaskTab.text()),
                    outstandingTaskTab = $('.cpm-my-outstandigntask div'),
                    outstandingTaskCount = parseInt(outstandingTaskTab.text());
                    completedTaskTab = $('.cpm-my-completetask div'),
                    completedTaskCount = parseInt(completedTaskTab.text());

                if (timeLine.length === 0 || (timeLine.attr('class').indexOf('cpm-current-date') !== -1)) {
                    currentTaskTab.text(--currentTaskCount);
                } else if(timeLine.length && (timeLine.attr('class').indexOf('cpm-due-date') !== -1)) {
                    outstandingTaskTab.text(--outstandingTaskCount);
                }

                completedTaskTab.text(++completedTaskCount);
            }
        });
    });

    $("body").on('click', 'input.cpm-complete', function(event) {
        var element = $(this),
            task = element.next(),
            completedBy = task.siblings('.cpm-completed-by'),
            assignedUser = task.siblings('.cpm-assigned-users'),
            timeLine = task.siblings('.cpm-current-date, .cpm-due-date'),
            spinner = '<span id="my-task-spinner" class="cpm cpm-spinner" style="height: 16px; width: 16px; margin-right: 5px; margin-top: -4px"></span>';

        $.post({
            url: CPM_Vars.ajaxurl,
            data: {
                action: 'cpm_task_open',
                project_id: element.attr('data-project'),
                task_id: element.val(),
                _wpnonce: CPM_Vars.nonce,
                before: function() {
                    element.replaceWith(spinner);
                }
            }
        }).done(function (response) {
            if (response.success) {
                toastr.success(response.data.success);

                element.removeClass('cpm-complete');
                element.addClass('cpm-uncomplete');

                $("#my-task-spinner").replaceWith($(element));

                completedBy.hide();
                assignedUser.show();
                timeLine.show();
                
                timeLine.css({
                    'background' : ''
                });

                task.css({
                    'text-decoration' : 'none'
                });

                task.find('.cpm-todo-text').css({
                    'text-decoration' : 'none'
                });

                var currentTaskTab = $('.cpm-my-currenttask div'),
                    currentTaskCount = parseInt(currentTaskTab.text()),
                    outstandingTaskTab = $('.cpm-my-outstandigntask div'),
                    outstandingTaskCount = parseInt(outstandingTaskTab.text());
                    completedTaskTab = $('.cpm-my-completetask div'),
                    completedTaskCount = parseInt(completedTaskTab.text());

                console.log(timeLine);

                if (timeLine.length === 0 || (timeLine.attr('class').indexOf('cpm-current-date') !== -1)) {
                    currentTaskTab.text(++currentTaskCount);
                } else if(timeLine.length && (timeLine.attr('class').indexOf('cpm-due-date') !== -1)) {
                    outstandingTaskTab.text(++outstandingTaskCount);
                }

                completedTaskTab.text(--completedTaskCount);

            }
        });
    });
})(jQuery);