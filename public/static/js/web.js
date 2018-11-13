$(function(){


});

//消息弹出框
function showMsg(msg) {
    var $tooltips = $('.js_tooltips');
    if ($tooltips.css('display') != 'none') return;

    $tooltips.text(msg);

    // toptips的fixed, 如果有`animation`, `position: fixed`不生效
    $('.page.cell').removeClass('slideIn');

    $tooltips.css('display', 'block');
    setTimeout(function () {
        $tooltips.css('display', 'none');
    }, 2000);
}

//出现列表页按钮删除
function showListDelete(source) {
    hideListDelete();
    var $source = $(source).parent();
    $source.removeClass('weui-cell_access');
    $source.addClass('weui-cell_swiped');
    $source.children('.weui-cell__bd').css('transform','translateX(-68px)');
    $source.children('.weui-cell__ft').append('<a class="weui-swiped-btn weui-swiped-btn_warn" href="javascript:" onclick="handleListDelete(this)">删除</a>');
}
//隐藏列表页按钮删除
function hideListDelete() {
    var $source = $('.weui-cell');
    $source.removeClass('weui-cell_swiped');
    $source.addClass('weui-cell_access');
    $source.children('.weui-cell__bd').css('transform','');
    $source.children('.weui-cell__ft').empty();
}
//处理删除
function handleListDelete(source) {
    event.stopPropagation();
    var $source = $(source);
    if($source.text() === '删除'){
        $source.css('z-index', '999');
        $source.text('确认删除');
    }else {
        var $container = $(source).parent().parent();
        var id = $container.attr('data');
        var url = $container.attr('url');
        window.location.href = url + 'delete/id/' + id;
    }
}