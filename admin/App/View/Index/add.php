<?php
    $this->tplInclude('Public/header');
    $this->tplInclude('Public/navbar');
?>

<!-- /section:basics/navbar.layout -->
<div class="main-container" id="main-container">
    <script type="text/javascript">
        try {
            ace.settings.check('main-container', 'fixed')
        } catch (e) {
        }
    </script>

    <?php
        $this->tplInclude('Public/sitebar');
    ?>

    <!-- /section:basics/sidebar -->
    <div class="main-content">
        <div class="main-content-inner">
            <!-- #section:basics/content.breadcrumbs -->
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try {
                        ace.settings.check('breadcrumbs', 'fixed')
                    } catch (e) {
                    }
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-home home-icon"></i>
                        <a href="/">首页</a>
                    </li>
                </ul><!-- /.breadcrumb -->


            </div>

            <div class="page-content">

                <div class="page-header">
                    <h1>
                        添加任务
                    </h1>
                </div>


                <div class="row">
                    <div class="col-xs-12">
                        <ul class="nav nav-tabs" id="myTab">
                            <li>
                                <a href="/?plan=0">
                                    <i class=" ace-icon fa fa-list "></i>
                                    全部
                                </a>
                            </li>

                            <li>
                                <a href="/?plan=1">
                                    <i class=" ace-icon fa fa-list "></i>
                                    循环执行
                                </a>
                            </li>

                            <li>
                                <a href="/?plan=2">
                                    <i class=" ace-icon fa fa-list "></i>
                                    仅执行一次
                                </a>
                            </li>

                            <li class="active">
                                <a href="/?a=add">
                                    <i class=" fa fa-edit "></i>
                                    添加
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content tab-content no-border no-padding-left no-border-right" style="padding: 20px 0" >
                            <div class="tab-pane active" id="edit-cont">
                                <div class="row">

                                    <div class="col-xs-12">
                                        <form class="form-horizontal" role="form" id="task-form">
                                            <div class="form-group">
                                                <label class="col-sm-3 control-label no-padding-right" for="form-field-1"> 任务名称 </label>

                                                <div class="col-sm-9">
                                                    <input name="name" type="text" id="form-field-1" placeholder="任务名称" class="col-xs-10 col-sm-5">
                                                </div>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group">
                                                <label class="col-sm-3 control-label no-padding-right"> 执行计划 </label>

                                                <div class="checkbox" id="slide-check">
                                                    <label>
                                                        <input name="plan" type="radio" class="ace" value="1" checked>
                                                        <span class="lbl" >循环执行</span>
                                                    </label>
                                                    <label>
                                                        <input name="plan" type="radio" class="ace" value="2">
                                                        <span class="lbl" >仅执行一次</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group" id="task_loop">
                                                <label class="col-xs-3 control-label no-padding-right">计划终始时间</label>

                                                <div class="col-xs-9">
                                                    <span class="label label-lg label-info">如不填写终止时间，默认为一直执行</span>
                                                    <div class="col-xs-2 no-padding">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar bigger-110"></i>
                                                            </span>
                                                            <input class="form-control" style="border-right: none" type="text" id="start_time_loop" name="start_time_loop">
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-2 no-padding-left">
                                                        <div class="input-group">
                                                            <input style="border-left: none" type="text" id="start_time_loop_s" name="start_time_loop_s" class="form-control">
                                                            <span class="input-group-addon">
																<i class="fa fa-clock-o bigger-110"></i>
															</span>
                                                        </div>
                                                    </div>
                                                    <div class="pull-left no-padding" style="margin-top: 5px;">
                                                        <i class="fa fa-exchange"></i>
                                                    </div>
                                                    <div class="col-xs-2 no-padding-right">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar bigger-110"></i>
                                                            </span>
                                                            <input class="form-control" style="border-right: none" type="text" id="end_time_loop" name="end_time_loop">
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-2 no-padding-left">
                                                        <div class="input-group">
                                                            <input style="border-left: none" type="text" id="end_time_loop_s" name="end_time_loop_s" class="form-control">
                                                            <span class="input-group-addon">
																<i class="fa fa-clock-o bigger-110"></i>
															</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="form-group" id="task_once" style="display: none;">
                                                <label class="col-xs-3 control-label no-padding-right">计划执行时间</label>

                                                <div class="col-xs-9">
                                                    <div class="col-xs-2 no-padding">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-calendar bigger-110"></i>
                                                            </span>
                                                            <input class="form-control" style="border-right: none" type="text" id="start_time_once" name="start_time_once">
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-2 no-padding-left">
                                                        <div class="input-group">
                                                            <input style="border-left: none" type="text" id="start_time_once_s" name="start_time_once_s" class="form-control">
                                                            <span class="input-group-addon">
																<i class="fa fa-clock-o bigger-110"></i>
															</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group" id="interval">
                                                <label class="col-sm-3 control-label no-padding-right" for="form-field-2"> 间隔时间 </label>

                                                <div class="col-sm-4">
                                                    <input name="interval" type="text" id="form-field-2" placeholder="间隔时间(秒)" class="col-xs-10 col-sm-5">
                                                </div>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group" id="interval">
                                                <label class="col-sm-3 control-label no-padding-right" for="form-field-3"> 执行任务 </label>

                                                <div class="col-sm-9">
                                                    <input name="task" type="text" id="form-field-3" placeholder="执行任务" class="col-xs-10 col-sm-5">
                                                    &nbsp;&nbsp;
                                                    <span class="label label-lg label-info">可填写http接口地址或本地脚本地址(绝对路径)</span>
                                                </div>
                                            </div>

                                            <div class="space-4"></div>

                                            <div class="form-group" id="interval">
                                                <label class="col-sm-3 control-label no-padding-right" for="form-field-4"> 任务说明 </label>

                                                <div class="col-sm-6">
                                                    <textarea cols="40" rows="8" id="form-field-4" name="description"></textarea>
                                                </div>
                                            </div>

                                            <div class="clearfix form-actions">
                                                <div class="text-center">
                                                    <button class="btn btn-info" id="task-submit" type="button">
                                                        <i class="fa fa-check bigger-110"></i>
                                                        提交
                                                    </button>
                                                    &nbsp; &nbsp; &nbsp;
                                                    <button class="btn" type="reset">
                                                        <i class="fa fa-undo bigger-110"></i>
                                                        重置
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="hr hr-24"></div>

                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- /.page-content -->
            </div><!-- /.main-content -->

        </div><!-- /.main-container-inner -->

        <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
            <i class="icon-double-angle-up icon-only bigger-110"></i>
        </a>
    </div><!-- /.main-container -->

    <?php
        $this->tplInclude('Public/footer');
    ?>

    <link rel="stylesheet" href="/statics/css/datepicker.css" />
    <link rel="stylesheet" href="/statics/css/bootstrap-timepicker.css" />
    <link rel="stylesheet" href="/statics/css/daterangepicker.css" />
    <link rel="stylesheet" href="/statics/css/bootstrap-datetimepicker.css" />
    <link rel="stylesheet" href="/statics/css/colorpicker.css" />

    <script src="/statics/js/date-time/bootstrap-datepicker.js"></script>
    <script src="/statics/js/date-time/bootstrap-timepicker.js"></script>
    <script src="/statics/js/date-time/moment.js"></script>
    <script src="/statics/js/date-time/daterangepicker.js"></script>
    <script src="/statics/js/date-time/bootstrap-datetimepicker.js"></script>

    <script type="text/javascript">
        var _time;
        $(function() {

            $.fn.datepicker.dates['zh'] = {
                days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"],
                daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
                daysMin:  ["日", "一", "二", "三", "四", "五", "六"],
                months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
                monthsShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
                today: "今日",
                clear: "清除"
            };

            $('#start_time_loop').datepicker({
                format : "yyyy-mm-dd",
                startDate: "<?php echo date('Y-m-d') ?>",
                autoclose: true,
                todayHighlight: true,
                language: 'zh'
                //forceParse: false
            }).prev().on(ace.click_event, function(){
                $(this).next().focus();
            });

            $('#start_time_loop').datepicker('setDate', '<?php echo date('Y-m-d') ?>');

            $('#start_time_loop').on('changeDate', function(ev){
                $('#start_time_loop_s').focus();

                $('#end_time_loop').datepicker('setStartDate', $('#start_time_loop').datepicker('getDate'));
            });

            $('#start_time_loop_s').timepicker({
                minuteStep: 1,
                secondStep: 1,
                showSeconds: true,
                showMeridian: false
            }).next().on(ace.click_event, function(){
                $(this).prev().focus();
            });

            $('#end_time_loop').datepicker({
                format : "yyyy-mm-dd",
                startDate: "<?php echo date('Y-m-d') ?>",
                autoclose: true,
                todayHighlight: true,
                language: 'zh',
                forceParse: false
            }).prev().on(ace.click_event, function(){
                $(this).next().focus();
            });

            $('#end_time_loop').on('changeDate', function(ev){
                if (!_time) {
                    $('#end_time_loop_s').timepicker({
                        defaultTime: '00:00:00',
                        minuteStep: 1,
                        secondStep: 1,
                        showSeconds: true,
                        showMeridian: false
                    }).next().on(ace.click_event, function(){
                        $(this).prev().focus();
                    });

                    _time = true;
                }

                $('#end_time_loop_s').focus();
            }).on('changeDate', function(ev) {
                var _s_time = $('#start_time_loop').datepicker('getDate');
                var d2 = new Date(_s_time);

                if (ev.date.valueOf() < d2.getTime()){
                    alert('error');
                    return false;
                }
            });

            $('#start_time_once').datepicker({
                format : "yyyy-mm-dd",
                startDate: "<?php echo date('Y-m-d') ?>",
                autoclose: true,
                todayHighlight: true,
                language: 'zh'
                //forceParse: false
            }).prev().on(ace.click_event, function(){
                $(this).next().focus();
            });

            $('#start_time_once').datepicker('setDate', '<?php echo date('Y-m-d') ?>');

            $('#start_time_once').on('changeDate', function(ev){
                $('#start_time_once_s').focus();
            });

            $('#start_time_once_s').timepicker({
                minuteStep: 1,
                secondStep: 1,
                showSeconds: true,
                showMeridian: false
            }).next().on(ace.click_event, function(){
                $(this).prev().focus();
            });

            $('input[name="plan"]').change(function() {
                var value = $(this).val();
                if (value == 2) {
                    $('#task_loop').hide();
                    $('#interval').hide();
                    $('#task_once').show();
                } else {
                    $('#task_loop').show();
                    $('#interval').show();
                    $('#task_once').hide();
                }
            });


            //添加任务
            $("#task-submit").on('click',function() {
                $.ajax({
                    type : 'post',
                    datatype : 'json',
                    url : '/?a=task&do=add',
                    data : $('#task-form').serialize(),
                    success : function(json) {
                        if (json.code === 0){
                            last_gritter = $.gritter.add({
                                title: '新建成功',
                                text: json.message,
                                class_name: 'gritter-success gritter-center',
                                time: 1000,
                                before_close: function(e, manual_close){
                                    window.location.href = '/';
                                }
                            });

                            setTimeout("window.location.href = '/'", 1000);

                        } else {
                            last_gritter = $.gritter.add({
                                title: '提交失败',
                                text: json.message,
                                class_name: 'gritter-error gritter-center',
                                time: 1200
                            });
                        }
                    }
                })
            });

        });
    </script>


