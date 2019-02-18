$(function() {
    tree($('#sidebar > ul'));
    if (window.localStorage) {
        var element = $('#toggle-sidebar').show().on('click', function() {
            element.toggleClass('fa-angle-double-right');
            window.localStorage.setItem(
                'treeStatus',
                $(this).hasClass('fa-angle-double-right') ? 'close' : 'open'
            );
            $('body').toggleClass('toggle');
        });
        if ('close' === window.localStorage.getItem('treeStatus')) {
            element.click();
        }
    }
});

/**
 * 左侧菜单树
 *
 * @param {object} element
 * @return void
 */
function tree(element) {
    element = $(element);
    element.find('li').has('ul').children('a').on('click', function(e) {
        var me = $(this);
        if ('#' == me.attr('href') || $(e.target).is('.arrow')) {
            e.preventDefault();
        }
        me.parent().toggleClass('active').children('ul').collapse('toggle')
            .end().siblings().removeClass('active').children('ul.in').collapse('hide');
    }) // return a
    .append('<i class="fa arrow"></i>');

    var selected = element.find('#nav-item-' + GLOBALS.currentMenuId).addClass('active');
    if (selected.length) {
        selected.find('a').addClass('active');
        selected.parent().addClass('in');
        $.each(('' + selected.data('node')).split(','), function(index, id) {
            $('#nav-item-' + id).addClass('active').parent().addClass('in');
        });
    }
}