
// define a component that uses this mixin
var CPM_List_Form_Minxin = {
    /**
     * Initial data for this component
     * 
     * @return obj
     */
    data: function() {
        return {
            tasklist_privacy: this.list.private == 'on' ? true : false,
        };
    },

    watch: {
        tasklist_privacy: function(new_val) {
            this.list_form_data.tasklist_privacy = new_val ? 'on' : 'no';
        }

    },

    computed: {
        /**
         * Checking, is todo list view private 
         * 
         * @return boolen
         */
        tdolist_view_private: function() {

            if ( ! this.$store.state.init.hasOwnProperty('premissions')) {
                return true;
            }

            if ( this.$store.state.init.premissions.hasOwnProperty('tdolist_view_private')) {
                return this.$store.state.init.premissions.tdolist_view_private
            }

            return true;
        },
    },

};



var CPM_Text_Editor_Mixin = {
    data: function () {
        var self = this;
          
        return {
            tinyMCE_settings: {
                setup: function (editor) {
                    editor.on('change', function () {
                        self.content.html = editor.getContent();
                    });
                    editor.on('keyup', function (event) {
                        self.content.html = editor.getContent();
                    });
                    editor.on('NodeChange', function () {
                        self.content.html = editor.getContent();
                    });
                    
                    editor.on('keydown', function (event) {
                        var key = event.keyCode,
                            allowedKeys = [37, 38, 39, 40],
                            node = this.selection.getNode(),
                            $ = tinymce.dom.DomQuery;
                            dom = $(node);

                        if (allowedKeys.indexOf(key) === -1 && dom.attr('data-user')) {
                            dom.removeAttr('data-user');
                            dom.removeAttr('style');
                        }
                    });
                },

                external_plugins: {
                    'placeholder' : CPM_Vars.CPM_URL + '/assets/js/tinymce/plugins/placeholder/plugin.min.js',
                    'mention' : CPM_Vars.CPM_URL + '/assets/js/tinymce/plugins/mention/plugin.min.js'
                },

                plugins: 'placeholder textcolor colorpicker wplink wordpress mention',
                mentions: {
                    source: self.$store.state.project_users,
                    queryBy: 'name',
                    delimiter: ['@', '#'],
                    delay: 0,
                    items: 10,
                    insert: function(item) {
                        var mentionElement = [
                                '<span style="color: #0073aa;" data-user=":',
                                item.login_name,
                                ':">',
                                '@',
                                item.name,
                                '</span>&nbsp;',
                            ].join('');

                        return mentionElement;
                    }
                }
            }
        };
    }
};




