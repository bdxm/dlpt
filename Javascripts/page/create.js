jQuery(document).ready(function() {
    //优惠券
    $("#Coupons").change(function() {
        var Coupons = $(this).val();
        if (Coupons != '') {
            $.get("index.php", {module: "ApiModel", action: "GetCoupons", code: Coupons},
            function(data) {
                if (data > 0) {
                    $('#CouponsPri').val(data);
                } else if (data == -1) {
                    $('#CouponsPri').val('优惠券已过期');
                } else if (data == -2) {
                    $('#CouponsPri').val('优惠券已使用');
                } else {
                    $('#CouponsPri').val('无此优惠券');
                }
            })
        }
    });

    //客户提交
    $('select#c_customer').change(function() {
        if ($(this).val() == 'new') {
            $("input[type=text]").val("");
            $('input[name=email]').removeAttr("disabled");
            $('.userdata-content').eq(2).hide('slow', function() {
                $('.userdata-content').eq(1).show('slow');
                $('.Btn2').attr('value', '下一页');
                $('.Btn3').attr('value', '创建客户');
            });
        } else {
            $.get("Apps?module=Gbaopen&action=Operation&type=modify&cus=" + $(this).val(), function(result) {
                if (result.err == 0) {
                    var crelist = $('.userdata-content');
                    result = result.data;
                    $.each(result, function(i, v) {
                        if (i == 'remark') {
                            $("textarea[name='remark']").val(v[1]);
                        } else
                            $('input[name=' + i + ']').val(v[1]);
                    })
                    $('input[name=email]').attr("disabled", "true");
                    crelist.eq(1).hide('slow', function() {
                        crelist.eq(2).show('slow');
                        $('.Btn2').attr('value', '上一页');
                        $('.Btn3').attr('value', '创建并开通');
                    });
                } else {
                    Msg(2, result.msg);
                }
            });
        }
    });

    //文本框点击复制事件
    $('.crelist textarea .info').click(function() {
        $('.crelist textarea .info').select();
        document.execCommand("Copy");
        alert('已复制！');
    });
    //页面大小初始化
    windowchange();
    
    //模板选择
    $("input[name='pcdomain']").focus(function() {
        $(this).val("http://" + $("input[name='account']").val() + $("#companyFTP option:selected").attr("content"));
    });
    $("input[name='mobiledomain']").focus(function() {
        $(this).val("http://m." + $("input[name='account']").val() + $("#companyFTP option:selected").attr("content"));
    });
    $("input[type='radio'][name='pc_mobile']").change(function() {
        changetype($(this).val());
    });
    
    $("#companyFTP").change(function(){
        var pcdomain=$("input[name='pcdomain']").val();
        var mobiledomain=$("input[name='mobiledomain']").val();
        if(/\.5067\.org/.test(pcdomain)&&$("input[name='pcdomain']").val()!=''){
            $("input[name='pcdomain']").val("http://" + $("input[name='account']").val() + $("#companyFTP option:selected").attr("content"));
        }
        if(/\.5067\.org/.test(mobiledomain)&&$("input[name='mobiledomain']").val()!=''){
            $("input[name='mobiledomain']").val("http://m." + $("input[name='account']").val() + $("#companyFTP option:selected").attr("content"));
        }
    });
    //ftp选择
    $("input[type='radio'][name='ftp_c']").change(function() {
        if ($(this).val() == 1) {
            $("#companyFTP").show();
            $(".ownftp").hide();
        } else {
            $(".ownftp").show();
            $("#companyFTP").hide();
        }
    });
    $("#companyFTP select").change(function() {
        content = $(this).children('option:selected').attr("content");
        $("#companyFTP span.as").text("需要客户手动以Cname解析到c" + content);
    });
    
    //外域勾选
    $(".crelist input[type='checkbox']").click(function() {
        var _this = $(this);
        if (_this.is(":checked")) {
            if (_this.attr("name") == "outpc_add")
                _this.siblings("textarea.info").val(_this.siblings("textarea.infoblnd").val().replace("$$", $("input[name='mobiledomain']").val())).show();
            else
                _this.siblings("textarea.info").val(_this.siblings("textarea.infoblnd").val().replace("$$", $("input[name='pcdomain']").val())).show();
            _this.siblings("span.as").text("外域域名需要插入左边文本框内的脚本");
        } else {
            _this.siblings("textarea.info").hide();
            _this.siblings("span.as").text("");
        }
    })
    
    //上下页页面切换
    $('.Btn2').click(function() {
        if ($(this).attr('value') == '下一页') {
            $(this).attr('value', '上一页');
            $('.Btn3').attr('value', '创建并开通');
            $('.userdata-content').eq(1).hide('slow', function() {
                $('.userdata-content').eq(2).show('slow');
            });
        } else {
            $(this).attr('value', '下一页');
            if ($('select#c_customer').val() == 'new')
                $('.Btn3').attr('value', '创建客户');
            $('.userdata-content').eq(2).hide('slow', function() {
                $('.userdata-content').eq(1).show('slow');
            });
        }
    });

    //提交数据初始审核
    $('.Btn3').click(function() {
        var html = '<div class="userdata-content"><p style="font-size:20px;color:red;">请确认下面的信息，一旦创建，不可修改！！！！</p>\n';
        if ($(this).attr('value') == '创建并开通') {
            if (/.*[\u4e00-\u9fa5]+.*$/.test($(".userdata-content input[name='account']").val()) || ($(".userdata-content input[name='account']").val() == '')) {
                Msg(1, '账号不能为空或含有中文');
                return false;
            }
            if(/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/.test($(".userdata-content input[name='account']").val()) == false){
                Msg(1, '账号只能由数字，字母，分隔号构成。首字符和尾字符只能是数字或字母');
                return false;
            }
            html += '<p><span>邮箱地址：</span><span class="major">'+$(".userdata-content input[name='email']").val()+'</span></p>\n';
            html += '<p><span>客户账号：</span><span class="major">'+$(".userdata-content input[name='account']").val()+'</span></p></div>';
        } else if ($(this).attr('value') == '创建客户') {
            html += '<p><span>邮箱地址：</span><span class="major">'+$(".userdata-content input[name='email']").val()+'</span></p>\n';
        } else {
            Msg(2, '非法请求');
            return false;
        }
        popup(html);
    });
    
    //ajax请求
    $(".dialog-content a.dia-ok").click(function() {
        var data = '{', input;
        var r_let = /^[A-Za-z]+$/, r_num = /^[0-9\-]+$/;
        var text = $("textarea[name='remark']");
        if($('.Btn3').val() == '创建并开通'){
            var cus = $(".crelist .userdata-content").eq(0).find("select").val();
            var ftp = $(".crelist .userdata-content").eq(2).find("select").val();
            input = $("input[type!='submit']");
            $.each(input, function(i, v) {
                if (!(v.checked) && (v.type == 'radio'))
                    return true;
                if (v.type == 'checkbox')
                    data += '"' + v.name + '":"' + v.checked + '",';
                else
                    data += '"' + v.name + '":"' + v.value + '",';
            });
            data += '"type":"cuspro","cus":"' + cus + '","ftp":' + ftp + ',';
        } else if($('.Btn3').val() == '创建客户'){
            input = $(".crelist .userdata-content").eq(1).find("input");
            $.each(input, function(i, v) {
                data += '"' + v.name + '":"' + v.value + '",';
            })
            data += '"type":"cus",';
        } else {
            $('#dialog-box').toggle("slow",function(){
                $("#dialog-overlay").slideUp("fast");
            });
            $('#dialog-message').html('');
            Msg(2, '非法请求');
            return false;
        }
        data += '"' + text.attr('name') + '":"' + text.val() + '"}';
        data = $.parseJSON(data);
        if (!r_num.test(data['tel'])) {
            Msg(1, '您输入的电话号码不正确');
        }else{
            if (data["name"] && data["companyname"] && data["email"] && data["tel"]) {
                Msg(1, '<span>正在处理，请稍等...</span><span class="flower-loader" style="opacity: 1;"></span>');
                $.post("Apps?module=Gbaopen&action=NewCus", data, function(result) {
                    if (result.err == 0) {
                        Msg(3, result.msg);
                    } else {
                        Msg(2, result.msg);
                    }
                });
            } else {
                Msg(1, '公司名称,联系人姓名,联系电话,Email---是必填选项，请检查');
            }
        }
        $('#dialog-box').toggle("slow",function(){
            $("#dialog-overlay").slideUp("fast");
        });
        $('#dialog-message').html('');
    });
});

//类型选择 相应文本框展示
function changetype(num) {
    var m = $(".modelchoose");
    m.hide();
    if (num == 1) {
        $("#pc").show();
        $("#domain_outmobile").show();
        $("#pc").find("span").eq(0).show();
        $("#pc").find("span").eq(1).show();
    } else if (num == 2) {
        $("#mobile").show();
        $("#domain_outpc").show();
        $("#mobile").find("span").eq(0).show();
        $("#mobile").find("span").eq(1).show();
    } else if (num == 3) {
        $("#pc").show();
        $("#mobile").show();
        $("#pc").find("span").eq(0).show();
        $("#pc").find("span").eq(1).show();
        $("#mobile").find("span").eq(0).show();
        $("#mobile").find("span").eq(1).show();
    } else {
        $("#pk").show();
        $("#pc").show().find("span").eq(0).hide();
        $("#pc").find("span").eq(1).hide();
        $("#mobile").show().find("span").eq(0).hide();
        $("#mobile").find("span").eq(1).hide();
    }
}
;

function windowchange() {
    $(".crelist").height($(window).height() - 84);
    $(".crelist").css("overflow-y", "auto");
}