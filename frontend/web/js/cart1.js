/*
@功能：购物车页面js
@作者：diamondwang
@时间：2013年11月14日
*/

$(function(){
	
	//减少
	$(".reduce_num").click(function(){
		var amount = $(this).parent().find(".amount");
		if (parseInt($(amount).val()) <= 1){
			alert("商品数量最少为1");
		} else{
			$(amount).val(parseInt($(amount).val()) - 1);
		}
		//小计
		var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(amount).val());
		$(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));

		//使用ajax请求修改后台购物车数据
		var goods_id = $(this).closest('tr').attr('data-id');
		change(goods_id,$(amount).val());
		//总计金额
        totals();
	});

	//增加
	$(".add_num").click(function(){
		var amount = $(this).parent().find(".amount");
		$(amount).val(parseInt($(amount).val()) + 1);
		//小计
		var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(amount).val());
		$(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
        //使用ajax请求修改后台购物车数据
        var goods_id = $(this).closest('tr').attr('data-id');
        change(goods_id,$(amount).val());
		//总计金额
        totals();
	});

	//直接输入
	$(".amount").blur(function(){
		if (parseInt($(this).val()) < 1){
			alert("商品数量最少为1");
			$(this).val(1);
		}
		//小计
		var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(this).val());
		$(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
        //使用ajax请求修改后台购物车数据
        var goods_id = $(this).closest('tr').attr('data-id');
        change(goods_id,$(this).val());
		//总计金额
        totals();

	});
	//页面加载完自动计算总价格
    totals();
});

var totals = function () {
    var total = 0;
    $(".col5 span").each(function(){
        total += parseFloat($(this).text());
    });

    $("#total").text(total.toFixed(2));
    //console.log(total);
}
//修改购物车数量
var change = function (goods_id,amount) {
	$.post("/site/ajax-cart?type=change",{goods_id:goods_id,amount:amount},function (data) {

    });
}