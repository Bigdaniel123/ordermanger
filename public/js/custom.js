/**
 * Created by cc on 2017/11/28.
 */


//初始化时候判断是否显示
function selectType() {
    var type = $("#type").val();
    var week = $(".week");
    var weekfotper = $("#weekfotper");
    //只有对期才有
    weekfotper.hide();
    week.hide();

    //期数
    var periods_label = $("#periods");
    var periods_input = $("#periods_input");
    //利息
    var interest_input = $("#interest_input");


    //按期
    if (type == 1) {
        //期数
        week.show();
        weekfotper.show();
        periods_label.text("期数");
        periods_input.text("期");
        //利息
        interest_input.text("期/万");

        //按天
    } else if (type == 2) {
        //期数
        week.show();
        periods_label.text("天数");
        periods_input.text("天");
        //利息
        interest_input.text("天/万");

    } else {
        layer.alert("参数错误");
    }
}

//初始化
$(function(){
    selectType()

});



//计算第一期本金利息金额
$("input[name=firstdate_pay]").focus(function(){
    //借款额度
    var credit_limit = $("input[name=credit_limit]").val();
    //利息
    var interest = $("input[name=interest]").val();
    //分期期数
    var periods = $("input[name=periods]").val();

    var  money = Math.round((credit_limit/periods)+(credit_limit/10000)*interest);

    $(this).val(money);
});

//实际放款
$("input[name=actual_loan]").focus(function(){
    //借款额度
    var credit_limit = $("input[name=credit_limit]").val();
    //扣除总金额
    var total_deduction = $("input[name=total_deduction]").val();

    $(this).val(credit_limit -total_deduction );
});

//打款金额  与实际放款金额相同
$("input[name=paid_money]").focus(function(){
    var actual_loan = $("input[name=actual_loan]").val();
    $(this).val(actual_loan);
});

$("#type").change(function(){


    selectType();
});



