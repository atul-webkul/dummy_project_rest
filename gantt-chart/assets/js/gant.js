//php5-fpm
(function($) {

    $(function() {

        var chart = [],
            link           = [],
            assigned_users = [],
            chart_link     = ( gantt_link !== 'null' ) ? eval(gantt_link) : '',
            chart_todo     =  ( gantt_todo !== 'null' ) ? eval(gantt_todo) : '',
            chart_task     =  ( gantt_task !== 'null' ) ? eval(gantt_task) : '',
            assign_to      = ( assigned_user !== 'null' ) ? eval(assigned_user) : '',
            chart_avatar   = ( avatars !== 'null' ) ? eval(avatars) : '';

        $.each( assign_to, function( index, user_value ) {
            assigned_users.push(user_value);
        });

        $.each( chart_link, function( index, link_value ) {
            link.push(link_value);
        });

        $.each( chart_todo, function( index, todo_value ) {
            chart.push(todo_value);
        });

        $.each( chart_task, function( index, task_value ) {
            chart.push(task_value);
        });

        ganttData = {
            data : chart,
            links: link
        }

        gantt.attachEvent("onBeforeTaskDrag", function(id, mode, e){
            var task = gantt.getTask(id);
             var ur =  false ;
            if(task.type=='task_list'){
                return false;      //denies dragging if the global task index is odd
            }
            //alert(cproject_id+"-"+id) ;
            var chdata = {
                action: 'cpm_check_task_access',
                _wpnonce: CPM_Vars.nonce,
                task_id: id,
                project_id: cproject_id

            };
            var ura = function () {
            var tmp = null;
            $.ajax({
                'async': false,
                'type': "POST",
                'global': false,
                'dataType': 'json',
                'url': CPM_Vars.ajaxurl,
                'data': chdata,
                'success': function (data) {
                    tmp = data;
                }
            });
            return tmp;
            }();
            ur =  ura ;
            return ur ;

        });

        //simple checkbox control
        gantt.form_blocks['checkbox']={
           render:function(config) {
              return '<div class="dhx_cal_ltext"><label><input type="checkbox" name="'+config.name+'" value="yes">'+gantts.make_private+'</label></div>';
           },
           set_value:function(node,value,ev,config){
              node.checked = !!value;
           },
           get_value:function(node,ev,config){
              return !!node.checked;
           },
           focus:function(node){
           }
        };

        gantt.templates.task_date = function(date){
            var objDate    = new Date(date),
                locale     = "en-us",
                year       = objDate.getFullYear(),
                month      = objDate.toLocaleString(locale, { month: "long" }),
                currentDay = objDate.getDate()-1,
                view_date  = (currentDay +' '+month +' '+ year );

            return view_date;

        };

        gantt.config.lightbox.sections = [
            {name:"title", height:26, map_to:"text", type:"textarea",focus:true},
            {name:"description", height:48, map_to:"description", type:"textarea"},
            {name:"assign_to", height:38, map_to:"assign_to", type:"select", options:assigned_users },
            {name:"private", type:"private_checkbox", map_to:"task_private"},
            {name:"task_start", height:72, type:"duration", map_to:"task_start" },

        ];

        gantt.form_blocks["private_checkbox"] = {
            render:function(sns) {
                return "<div class='dhx_cal_ltext' style='height:25px;'>"
                +"<input type='checkbox' id='gant-private-field'></div>";
            },
            set_value:function(node, value, task,section) {
               
            },
            get_value:function(node, task,section) {
               var private_node = $('#gant-private-field');
               return private_node.prop("checked");
            },
            focus:function(node) {
               
            }
        };

        gantt.locale.labels.section_task_start  = gantts.start_date;

        gantt.locale.labels.section_assign_to  = gantts.assign_to;
        gantt.locale.labels.section_private  = gantts.private;


        gantt.templates.progress_text = function(start, end, task){
            return "<span>"+Math.round(task.progress*100)+ "% </span>";
        };

        gantt.templates.leftside_text = function(start, end, task){
            return task.duration + " days";
        };

        $(".gantt-wrap").dhx_gantt({
            data:ganttData,
            drag_links: !0,
            columns : [
                {name:"text", label:gantts.task_name,  tree:true, width:160},
                {name:"assign_to", label: gantts.assign, align:"center", template:function(obj){
                    var assign_avatar = '';
                    if ( !obj.task_list ) {
                        $.each( chart_avatar, function( index, avatar_value ) {

                            if ( obj.id == avatar_value.task_id ) {
                                assign_avatar = avatar_value.avatar;
                            }
                        });
                    }
                    return assign_avatar;
                }},
                {name:"Add task", label: gantts.add_task, align:"center", template:function(obj){
                    if ( obj.task_list === true ) {
                        return '<div class="gantt_add"></div>';
                    } else {
                        return '';
                    }
                }},
            ],


            details_on_dblclick: false,

        });

        gantt.attachEvent("onLightboxSave", function(id, task, is_new){
            
            /*if (gantts.enable_start_date == 'on') {*/
            var current = new Date( task.task_start.start_date ),
                currentYear  = current.getFullYear(),
                currentMonth = current.getMonth()+1,
                currentDay   = current.getDate(),
                startTime    = currentYear+'-'+currentMonth+'-'+currentDay;
            /*}*/

            var dt = task.task_start.start_date ;
            var adu = (task.duration-1) ;
            var end  = new Date(dt.setDate(dt.getDate() + adu)),
                endYear  = end.getFullYear(),
                endMonth = end.getMonth()+1,
                endDay   = end.getDate(),
                endTime  = endYear+'-'+endMonth+'-'+endDay;

                if( task.text == "" ) {
                    alert(gantts.message_empty);
                    return;
                }

            var data = {
                action : 'gantt_new_task',
                list_id : task.parent,
                task_start : (gantts.enable_start_date == 'on') ? startTime : '',
                task_due : endTime,
                task_title : task.text,
                task_text : task.description,
                task_assign : [ task.assign_to ],
                is_update : is_new,
                task_privacy: task.task_private ? 'yes' : 'no',
                _wpnonce : gantts.nonce
            }

            $.post( gantts.ajaxurl, data );

            return true;
        });


        gantt.attachEvent("onAfterTaskUpdate", function(id,item){
            var current = new Date( item.start_date ),
                currentYear  = current.getFullYear(),
                currentMonth = current.getMonth()+1,
                currentDay   = current.getDate(),
                startTime    = currentYear+'-'+currentMonth+'-'+currentDay;

            var current = new Date( item.end_date ),
                currentYear  = current.getFullYear(),
                currentMonth = current.getMonth()+1,
                currentDay   = current.getDate(),
                endTime      = currentYear+'-'+currentMonth+'-'+currentDay;

            var data = {
                action : 'update_task_date',
                id : id,
                start_date : startTime,
                end_date : endTime,
                progress: item.progress,
                _wpnonce : gantts.nonce
            }

            $.post( gantts.ajaxurl, data );

        });

        gantt.attachEvent("onAfterLinkAdd", function(id,item ){
            var data = {
                action : 'update_link',
                source_id : item.source,
                target_id : item.target,
                _wpnonce : gantts.nonce
            }

            $.post( gantts.ajaxurl, data );
        });

        gantt.attachEvent("onAfterLinkDelete", function(id,item){
            var data = {
                action : 'delete_link',
                source_id : item.source,
                target_id : item.target,
                _wpnonce : gantts.nonce
            }

            $.post( gantts.ajaxurl, data );
        });

    });

})(jQuery);