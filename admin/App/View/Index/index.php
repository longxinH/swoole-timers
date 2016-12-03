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
                        定时任务
                    </h1>
                </div>


                <div class="row">
                    <div class="col-xs-12">
                        <div class="no-border no-padding-left no-border-right">
                            <div class="profile-user-info profile-user-info-striped">
                                <div class="profile-info-row">
                                    <div class="profile-info-name"> 服务信息 </div>

                                    <div class="profile-info-value">
                                        <span>host : <?php echo C('server')['host'] ?> <br> port : <?php echo C('server')['port'] ?></span>
                                    </div>
                                </div>

                                <div class="profile-info-row">
                                    <div class="profile-info-name"> 运行状态 </div>

                                    <div class="profile-info-value">
                                        <span><?php echo $server_status ? '<span class="label label-md label-success">运行</span>' : '<span class="label label-md label-danger">关闭</span>' ?></span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        </div>
                    </div>

                    <div class="space-4"></div>

                    <?php
                        if (!$server_status) {
                    ?>
                        <div class="col-xs-12">
                            <div class="alert alert-danger">
                                <span class="badge badge-transparent tooltip-error" title="" data-original-title="2 Important Events">
                                    <i class="ace-icon fa fa-exclamation-triangle red bigger-130"></i>
                                </span>
                                无法连接到swoole服务端，请检查连接
                            </div>
                        </div>
                    <?php
                        }
                    ?>

                    <div class="col-xs-12">
                        <ul class="nav nav-tabs" id="myTab">
                            <li <?php echo $plan === 0 ? 'class="active"' : '' ?> >
                                <a href="/?plan=0">
                                    <i class=" ace-icon fa fa-list "></i>
                                    全部
                                </a>
                            </li>

                            <li <?php echo $plan === 1 ? 'class="active"' : '' ?>>
                                <a href="/?plan=1">
                                    <i class=" ace-icon fa fa-list "></i>
                                    循环执行
                                </a>
                            </li>

                            <li <?php echo $plan === 2 ? 'class="active"' : '' ?>>
                                <a href="/?plan=2">
                                    <i class=" ace-icon fa fa-list "></i>
                                    仅执行一次
                                </a>
                            </li>

                            <li>
                                <a href="/?a=add">
                                    <i class=" fa fa-edit "></i>
                                    添加
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content tab-content no-border no-padding-left no-border-right" style="padding: 20px 0" >
                            <div class="tab-pane active" id="list">
                                <table id="simple-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>任务ID</th>
                                            <th>任务名称</th>

                                            <?php if ($plan === 0) { ?>
                                                <th>执行计划</th>
                                            <?php } ?>

                                            <th>创建时间</th>
                                            <th>计划执行时间</th>
                                            <th>最后执行时间</th>

                                            <?php if ($plan !== 2) { ?>
                                                <th>间隔时间(秒)</th>
                                                <th>执行次数</th>
                                            <?php } ?>

                                            <th>执行任务</th>
                                            <th>任务说明</th>

                                            <?php if ($plan !== 2) { ?>
                                                <th>运行状态</th>
                                            <?php } ?>

                                            <th class="col-md-1"></th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                    <?php
                                        if (!empty($list['data'])) {
                                            foreach ($list['data'] as $key => $val) {
                                    ?>
                                            <tr>
                                                <td>
                                                    <?php echo $val['unid'] ?>
                                                </td>
                                                <td><?php echo $val['name'] ?></td>

                                                <?php if ($plan === 0) { ?>
                                                    <td><?php echo $val['plan'] == 1 ? '循环执行' : '仅执行一次' ?></td>
                                                <?php } ?>

                                                <td><?php echo $val['addtime'] ?></td>

                                                <?php if ($val['plan'] === 1) { ?>
                                                    <td><?php echo $val['start_time'] . ' 至 ' . ($val['end_time'] ? $val['end_time'] : '/') ?></td>
                                                <?php } else if ($val['plan'] === 2) { ?>
                                                    <td><?php echo $val['start_time'] ?></td>
                                                <?php } ?>

                                                <td><?php echo $val['last_run_start'] ?> - <?php echo $val['last_run_end'] ?></td>

                                                <?php if ($plan !== 2) { ?>
                                                    <td><?php echo $val['plan'] === 1 ? $val['interval'] : '/' ?></td>
                                                    <td><?php echo $val['plan'] === 1 ? $val['run_number'] : '/' ?></td>
                                                <?php } ?>

                                                <td><?php echo $val['task'] ?></td>
                                                <td><?php echo $val['description'] ?></td>

                                                <?php if ($plan !== 2) { ?>
                                                    <td>
                                                        <?php
                                                            if ($val['plan'] == 1) {
                                                        ?>
                                                            <label class="inline">
                                                                <input data-id="<?php echo $val['unid']; ?>" data-name="status" data-act="<?php echo $val['status'] == 1 ? 0 : 1 ?>" <?php echo $server_status && $val['status'] == 1 ? 'checked' : '' ?> <?php echo $server_status ? '' : 'disabled' ?> type="checkbox" class="ace ace-switch ace-switch-3 point">
                                                                <span class="lbl middle"></span>
                                                            </label>
                                                        <?php
                                                            }
                                                        ?>
                                                    </td>
                                                <?php } ?>

                                                <td>
                                                    <div class="hidden-sm hidden-xs btn-group">
                                                        <?php
                                                            if ($val['plan'] === 1 || $val['run_number'] === 0) {
                                                        ?>
                                                            <a href="/?a=edit&unid=<?php echo $val['unid'] ?>"
                                                               class="btn btn-xs btn-info">
                                                                <i class="ace-icon fa fa-pencil bigger-120"></i>
                                                            </a>
                                                        <?php
                                                            } else {
                                                        ?>
                                                            <span class="label label-md label-info">完成</span>
                                                        <?php } ?>

<!--                                                        <a data-id="--><?php //echo $val['unid']; ?><!--"-->
<!--                                                           class="btn btn-xs btn-danger rowdel">-->
<!--                                                            <i class="ace-icon fa fa-trash-o bigger-120"></i>-->
<!--                                                        </a>-->
                                                    </div>

                                                    <div class="hidden-md hidden-lg">
                                                        <div class="inline pos-rel">
                                                            <button
                                                                class="btn btn-minier btn-primary dropdown-toggle"
                                                                data-toggle="dropdown" data-position="auto">
                                                                <i class="ace-icon fa fa-cog icon-only bigger-110"></i>
                                                            </button>

                                                            <ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close">
                                                                <?php
                                                                    if ($val['plan'] === 1 || $val['run_number'] === 0) {
                                                                ?>
                                                                    <li>
                                                                        <a href="/?a=edit&unid=<?php echo $val['unid'] ?>" class="tooltip-info" data-rel="tooltip" title="修改" data-original-title="修改">
                                                                        <span class="blue">
                                                                            <i class="ace-icon fa fa-pencil bigger-120"></i>
                                                                        </span>
                                                                        </a>
                                                                    </li>
                                                                <?php
                                                                    }
                                                                ?>

<!--                                                                <li>-->
<!--                                                                    <a href="javascript:void(0)" class="tooltip-error rowdel" data-id="--><?php //echo $val['id']; ?><!--" data-rel="tooltip" title="" data-original-title="删除">-->
<!--                                                                    <span class="red">-->
<!--                                                                        <i class="ace-icon fa fa-trash-o bigger-120"></i>-->
<!--                                                                    </span>-->
<!--                                                                    </a>-->
<!--                                                                </li>-->
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php
                                            }
                                        }
                                    ?>

                                    </tbody>
                                </table>
                            </div>
                                <?php
                                    if (!empty($list['pager'])) {
                                        $this->tplInclude('Public/page', ['list' => $list]);
                                    }
                                ?>
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

    <script type="text/javascript">
        $(function() {

            $(".point").on('click', function(){
                var id = $(this).data('id');
                var act = $(this).data('act');
                var name = $(this).data('name');
                var _this = $(this);

                var data = 'unid=' + id + '&' + 'status=' + act;

                $.ajax({
                    type: 'post',
                    datatype: 'json',
                    url: '/?a=status',
                    data: data,
                    success: function (json) {
                        if (json.code === 0){
                            if (act){
                                _this.data('act', 0);
                            } else {
                                _this.data('act', 1);
                            }

                            last_gritter = $.gritter.add({
                                title: '操作成功',
                                text: json.message,
                                class_name: 'gritter-success gritter-center',
                                time: 1000
                            });

                        } else {
                            last_gritter = $.gritter.add({
                                title: '操作失败',
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




