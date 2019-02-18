/**
 * 提示js
 */
var Message = {
    success: function(msg, timeout) {
        return this.open({
            cls: 'success',
            iconCls: 'fa-check',
            msg: msg,
            timeout: timeout
        });
    },
    error: function(msg, timeout) {
        return this.open({
            cls: 'danger',
            iconCls: 'fa-times',
            msg: msg,
            timeout: timeout
        });
    },
    loading: function(msg, timeout) {
        return this.open({
            cls: 'info',
            iconCls: 'fa-spinner fa-pulse',
            msg: msg || '处理中，请稍后...',
            timeout: false
        });
    },
    hide: function() {
        $('#msg').remove();
    },
    open: function(options) {
        this.hide();
        var html =
            '<div id="msg" class="alert alert-' + options.cls + '">' +
                '<i class="fa ' + options.iconCls + '"></i> ' +
                 options.msg +
            '</div>';
        var element = $(html).appendTo('body');
        element.css('margin-left', -element.width() / 2);
        if (false !== options.timeout) {
            setTimeout(
                function() {
                    element.remove();
                },
                options.timeout || 2000
            );
        }
    }
};
