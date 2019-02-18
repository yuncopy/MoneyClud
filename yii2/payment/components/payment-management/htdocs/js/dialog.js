/**
 * 对话框js
 */
(function() {
    function getSize(msg) {
        if ('string' != typeof(msg)) {
            return;
        } else if (-1 != msg.indexOf('<')) {
            msg = msg.replace(/<[^>]*>/gi);
        }

        if (msg.length > 100) {
            return 'large';
        }
    }

    // 居中
    function positionCenter() {
        var modal = $(this),
            element = modal.find('.modal-content'),
            innerHeight = window.innerHeight
                          || document.documentElement.clientHeight
                          || $('body').clientHeight;
        modal.css('display', 'block');
        return element.css('margin-top', Math.max(0, (innerHeight - element.height()) / 2));
    }

    window.Dialog = $.extend({}, bootbox, {
        success: function(msg, callback) {
            return this.alert({
                message: msg,
                callback: callback,
                title: '成功提示',
                titleIconCls: 'fa-check-circle'
            });
        },

        error: function(msg, callback) {
            return this.alert({
                message: msg,
                callback: callback,
                title: '错误提示',
                className: 'error',
                titleIconCls: 'fa-times-circle'
            });
        },

        info: function(msg, callback) {
            return this.alert(msg, callback);
        },

        warn: function(msg, callback) {
            return this.alert({
                message: msg,
                callback: callback,
                title: '警告',
                titleIconCls: 'fa-warning'
            });
        },
        confirm: function(msg, yes, cancel) {
            return bootbox.confirm({
                message: msg,
                title: '确认信息',
                titleIconCls: 'fa-question-circle',
                callback: function(result) {
                    if (result && 'function' == typeof(yes)) {
                        yes(this);
                    } else if (!result && 'function' == typeof(cancel)) {
                        cancel(this);
                    }
                }
            });
        },
        open: function(options) {
            if (undefined === options.size) {
                options.size = getSize(options.message);
            }
            if (options.contentIconCls) {
                options.message = '<i class="fa ' + options.contentIconCls + ' content-icon"></i> ' + options.message;
            }
            if ($('.modal:visible').length) {
                options.backdrop = false;
            }

            var modal = bootbox.open(options);
            if (undefined === options.titleIconCls || options.titleIconCls) {
                modal.find('.modal-title').prepend(
                    '<i class="fa ' + (options.titleIconCls || 'fa-info-circle') + ' title-icon"></i> '
                );
            }
            if (options.width) {
                modal.find('.modal-dialog').width(options.width);
            }
            if (options.height) {
                modal.find('.modal-dialog').height(options.height);
            }
            return positionCenter.call(modal);
        },
        init: function() {
            // 默认设置
            this.addLocale('zh', {
                OK: '确定',
                CANCEL: '取消',
                CONFIRM: '确定',
            }).setDefaults({
                locale: 'zh',
                title: '提示信息',
                width: 600
            });

            bootbox.open = bootbox.dialog;
            bootbox.dialog = this.open;

            // 居中
            $('.modal').on('show.bs.modal', positionCenter);
            $(window).on('resize', function() {
                $('.modal:visible').each(positionCenter);
            });
            return this;
        }
    }).init();
})();
