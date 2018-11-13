/**
 *  公共函数
 */

$(function () {
    // 返回上一页
    $(".back-page").click(function (e) {
        e.preventDefault();
        window.history.back();
    })

})


/**
 * ajax的http请求
 * @param url
 * @param data
 * @param type
 * @param asynctype
 */
function http_service(url, data='', type='GET', async=false)
{
    try{
        if(url == ''){
            return response_json(0, 'url不能为空');
        }
        //var token = http_token(data);
        var response_data;
        layer.load(1);
        $.ajax({
            url: url,
            type: type,
            data: data,
            dataType: 'json',
            async: async,
            beforeSend: function () {
            },
            success: function(result){
                layer.closeAll();
                response_data = result;
            },
            error: function () {
                layer.closeAll();
                response_data = response_json(-1, '请求出错');
            }
        });
    }catch (err){
        console.log(err.message);
        response_data = response_json(-2, err.message);
    }
    return response_data;

}

/**
 * 文件
 * @param file
 * @returns {*}
 */
function upload_file(file, url) {
    try{
        var response_data;
        if(!url){
            url = cms_http_url + '/index/uploadImg';
        }

        var formdata = new FormData();
        formdata.append('item', file);
        $.ajax({
            url: url,
            type: 'POST',
            data: formdata,
            dataType: 'json',
            async: false,
            contentType: false,
            processData: false,
            success: function(result){
                response_data = result;
            },
            error: function () {
                response_data = response_json(-1, '请求出错');
            }
        });
    }catch (err){
        console.log(err.message);
        response_data = response_json(-2, err.message);
    }
    layer.closeAll();
    return response_data;
}


/**
 * 组装标准的json返回值
 * @param code
 * @param msg
 * @param data
 */
function response_json(code=0, msg='', data='')
{
    var json_string = '{"code":"'+code+'", "msg":"'+msg+'", "data":"'+data+'"}';
    return JSON.parse(json_string);
}

/**
 * 创建请求token
 * @param data
 * @returns {string}
 */
function http_token(data='')
{
    return '';
}

/**
 * 建立一個可存取到該file的url
 * @param file
 * @returns {*}
 */
function get_object_uRL(file) {
    var url = null ;
    if (window.createObjectURL!=undefined) { // basic
        url = window.createObjectURL(file) ;
    } else if (window.URL!=undefined) { // mozilla(firefox)
        url = window.URL.createObjectURL(file) ;
    } else if (window.webkitURL!=undefined) { // webkit or chrome
        url = window.webkitURL.createObjectURL(file) ;
    }
    return url ;
}

function formData_toJson(formData) {
    var objData = {};

    formData.forEach((value, key) => objData[key] = value);

    return JSON.stringify(objData);
}

function summerSendFile(file, object, url, res) {
    data = new FormData();
    data.append("file", file);
    $.ajax({
        dataType : "JSON",
        type: "POST",
        url: url,
        data: data,
        processData:false,
        contentType: false,
        success: function(data) {
            var url = res + data.data.photo;
            console.log(url);
            $("#"+object).summernote('insertImage', url);
        }
    });
}