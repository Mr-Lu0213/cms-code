/**
 * 扩展summernote，增加自定义功能
 * 依赖option配置：serverUrl
 * 服务端需提供三个方法：index、save、delete
 * Created by zhaowh on 12/01/2018.
 */
(function (factory) {
    /* global define */
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {
    $.extend($.summernote.plugins, {
        'template': function (context) {
            this.$body = $(document.body);
            this.$editor = context.layoutInfo.editor;
            this.options = context.options;
            this.$container = this.$editor;
            var self = this;
            var ui = $.summernote.ui;

            //插入模板
            context.memo('button.inserttemplate', function () {
                // create button
                var button = ui.button({
                    contents: '<i class="glyphicon glyphicon-floppy-save"/>',
                    tooltip: '插入模板',
                    click: function () {
                        self.show('insert');
                    }
                });
                return button.render();
            });

            //插入模板
            context.memo('button.selecttemplate', function () {
                var button = ui.button({
                    contents: '<i class="glyphicon glyphicon-list-alt"/>',
                    tooltip: '选择模板',
                    click: function () {
                        self.show('list');
                    }
                });
                return button.render();
            });

            this.initialize = function () {
                this.$container = this.options.dialogsInBody ? this.$body : this.$editor;
                this.$insertDialog = this.initInsertDialog();
                this.$listDialog = this.initListDialog();
            };

            this.destroy = function () {
                this.$insertDialog.remove();
                this.$insertDialog = null;
                this.$listDialog.remove();
                this.$listDialog = null;
            };

            this.initInsertDialog = function () {
                var body = [
                    '<div class="form-group note-form-group row-fluid">',
                    '<label class="note-form-label">模板标题</label>',
                    '<input class="form-control note-form-control note-input" type="text" id="templateName" />',
                    '</div>'
                ].join('');
                var footer = '<a href="#" class="btn btn-primary" id="btnInsert">插入</a>';

                return ui.dialog({
                    title: '插入模板',
                    fade: this.options.dialogsFade,
                    body: body,
                    footer: footer
                }).render().appendTo(self.$container);
            };

            this.initListDialog = function () {
                var body = '<div class="form-group note-form-group row-fluid" id="divTemplateListBody" style="height: 300px;overflow: auto;padding-left:3px"></div>';
                var footer = '<a href="#" class="btn btn-danger" id="btnDelete">删除</a><a href="#" class="btn btn-primary" id="btnSelect">选择</a>';

                return ui.dialog({
                    title: '选择模板',
                    fade: this.options.dialogsFade,
                    body: body,
                    footer: footer
                }).render().appendTo(self.$container);
            };

            this.showInsertDialog = function (){
                $('#templateName').val('');
                ui.showDialog(this.$insertDialog);
                $('#btnInsert').one('click',function (e) {
                    e.preventDefault();
                    var name = $('#templateName').val();
                    var content =  context.invoke('code');
                    var source = this;
                    if(!name){
                        self.showMsg(source, '标题不能为空！');
                    }else if(content === '<p><br></p>'){
                        self.showMsg(source, '请先在富文本编辑器中编辑要保存的模板内容！');
                    }else {
                        $.post(self.options.serverUrl  + 'save', {
                            'name': name,
                            'content': content
                        }, function (res) {
                            if(res.code){
                                this.$listDialog = self.initListDialog();
                                ui.hideDialog(self.$insertDialog);
                            }else{
                                self.showMsg(source, res.result ? res.result : res);
                                setTimeout(function () {
                                    ui.hideDialog(self.$insertDialog);
                                }, 2000);
                            }
                        });
                    }
                });
                ui.onDialogHidden(this.$insertDialog, function () {
                    $('.alert').remove();
                });
            };

            this.showListDialog = function (){
                var itemTpl = '<div class="radio"><label><input type="radio" name="optionsTemplate" value="{id}">{name}</label></div>';
                var $divTemplateListBody = $('#divTemplateListBody');
                $.post(self.options.serverUrl, {} ,function (res) {
                   if(res.code && res.result){
                       var content = '';
                       $.each(res.result, function (index, item) {
                           content += itemTpl.replace('{name}', item.name).replace('{id}', item.template_id);
                       });
                       $divTemplateListBody.html(content);
                       ui.showDialog(self.$listDialog);
                   }
                });
                $('#btnSelect').one('click',function (e) {
                    e.preventDefault();
                    var selected = $('input:radio[name="optionsTemplate"]:checked').val();
                    if(selected) {
                        $.post(self.options.serverUrl + 'get/id/' + selected, {}, function (res) {
                            if (res.code) {
                                context.invoke('code', context.invoke('code') + res.result);
                                $divTemplateListBody.html('');
                                ui.hideDialog(self.$listDialog);
                            } else {
                                self.showMsg(this, res);
                            }
                        });
                    }
                });
                $('#btnDelete').one('click',function (e) {
                    e.preventDefault();
                    var selected = $('input:radio[name="optionsTemplate"]:checked').val();
                    if(selected) {
                        $.post(self.options.serverUrl + 'delete/id/' + selected, {}, function (res) {
                            if (res.code) {
                                $('input:radio[name="optionsTemplate"]:checked').parent().remove();
                                setTimeout(function () {
                                    ui.hideDialog(self.$listDialog);
                                }, 2000);
                            } else {
                                self.showMsg(this, res);
                            }
                        });
                    }
                });
                ui.onDialogHidden(this.$listDialog, function () {
                    $('.alert').remove();
                });
            };

            this.show = function (type) {
                if(type === 'insert') {
                    this.showInsertDialog();
                }else{
                    this.showListDialog();
                }
            };

            this.showMsg = function (source, msg) {
                var msgTpl = [
                    '<div class="alert alert-danger alert-dismissable" style="margin-top: 7px">',
                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">',
                    '&times;',
                    '</button>',
                    '{msg}',
                    '</div>'
                ].join('');
                $('.alert').remove();
                $(source).parent().append(msgTpl.replace('{msg}', msg));
            }
        }
    });
}));