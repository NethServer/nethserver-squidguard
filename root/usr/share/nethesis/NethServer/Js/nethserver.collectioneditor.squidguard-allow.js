/*
 * NethServer Filter Collection Object -- see Filter.php template
 * 
 * Copyright (C) 2012 Nethesis srl
 */
(function ( $ ) {

    var editor = $('.CollectionEditor.AllowAclList');

    // A translator helper:
    var T = function () {
        return $.Nethgui.Translator.translate.apply($.Nethgui.Translator, Array.prototype.slice.call(arguments, 0));
    };

    var drawPolicy = function(element, ctx) {
        var dir, policyAdd, policyRemove;
        if(ctx.object.type === 'UN') {
            text = 'Do not filter source IP';
        } else if (ctx.object.type == 'DW') {
            text = 'Allow domain';
        } else {
            text = "Allow URL";
        }

        // classes: allow deny
        element.children('.policy').text(T(text));
        element.addClass('allow').removeClass('deny');
    };

    /*
     * Configure the CollectionEditor:
     */
    editor.one('nethguicreate', function (e) {
        editor.CollectionEditor({
            elementActions: [
            {
                name: 'update',
                label: T('Update'),
                view: {
                    template: $('<div class="FilterElement update"><span class="policy"></span> <input type="text" value="" /><div class="actions"></div></div>'),
                    build: function () {                        
                        $('<button type="button">' + T('Done') + '</button>').button({
                            text: false,
                            icons: {
                                primary: 'ui-icon-disk'
                            } 
                        }).appendTo(this.children('.actions'));                                                                        
                        this.on("keydown", "input", {
                            element: this
                        }, function(e) {
                            if(e.keyCode === $.ui.keyCode.ENTER) {
                                editor.CollectionEditor('endElementAction', true);
                                return false;
                            } else if (e.keyCode === $.ui.keyCode.ESCAPE) {
                                editor.CollectionEditor('endElementAction', false);
                                return false;
                            }
                        }).on("click", ".actions button", function() {
                            editor.CollectionEditor('endElementAction');
                        });
                    },
                    read: function (ctx) {
                        ctx.object.address = this.children('input').val();
                    },
                    write: function (ctx) {
                        drawPolicy(this, ctx);
                        this.children('input').val(ctx.object.address).focus().select();
                    }
                }
            },
            {
                name: 'delete',
                label: T('Delete'),
                view: function(ctx) {
                    ctx.element.remove();
                }
            },
            ],
            collectionActions: [            
            {
                name: 'create.UN',
                label: T('Add unfiltered IP'),
                click: function(e) {
                    editor.CollectionEditor('addElement', ':UN', true, 'update');
                }
            },
            {
                name: 'create.DW',
                label: T('Add allowed domain'),
                click: function(e) {
                    editor.CollectionEditor('addElement', ':DW', true, 'update');
                }
            },
            {
                name: 'create.UW',
                label: T('Add allowed URL'),
                click: function(e) {
                    editor.CollectionEditor('addElement', ':UW', true, 'update');
                }
            }
            ],
            serialize: function(e, ctx) {
                ctx.line = ctx.object.address + ':' + ctx.object.type;
            },
            unserialize: function(e, ctx) {
                var fields = ctx.line.split(/\s*:\s*/);

                if(fields.length >= 2) {
                    ctx.object = {
                        address: fields[0],
                        type: fields[1]
                    };
                }
            },
            elementView: {
                template: $('<div class="FilterElement normal"><span class="policy"></span> <a href="#!edit"></a><div class="actions"></div></div>'),
                build: function(ctx) {
                    var closeButton = $('<button type="button">' + T('Delete') + '</button>').button({
                        text: false,
                        icons: {
                            primary: 'ui-icon-close'
                        }
                    });
                    this.on('mouseenter mouseleave', function (e) {
                        ctx.element.toggleClass('hover');
                    });
                    this.on('click', function(e) {
                        editor.CollectionEditor('beginElementAction', 'update', ctx);
                        return false;
                    });
                    closeButton.on('click', function () {
                        editor.CollectionEditor('beginElementAction', 'delete', ctx);
                        return false;
                    });                  
                    this.children('.actions').append(closeButton);
                    drawPolicy(this, ctx);
                },
                write: function(ctx) {
                    this.children('a').text(ctx.object.address);
                }
            }
        });
                
    });
    
} ( jQuery ));




