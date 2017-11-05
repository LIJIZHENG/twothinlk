<?php
/* @var $this yii\web\View */
?>
    <h1>商品分类管理</h1>
    <div><ul id="treeDemo" class="ztree"></ul></div>
<?php
//注册ztree的静态资源和js
//注册css文件
$this->registerCssFile('@web/ztree/css/zTreeStyle/zTreeStyle.css');
//注册js文件 (需要在jquery后面加载)
$this->registerJsFile('@web/ztree/js/jquery.ztree.core.js',['depends'=>\yii\web\JqueryAsset::className()]);
$this->registerJsFile('@web/ztree/js/jquery.ztree.exedit.js',['depends'=>\yii\web\JqueryAsset::className()]);
$ajax_url = \yii\helpers\Url::to(['ajax']);
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
        // zTree 的参数配置，深入使用请参考 API 文档（setting 配置详解）
        var setting = {
            data: {
                simpleData: {
                    enable: true,
                    idKey: "id",
                    pIdKey: "parent_id",
                    rootPId: 0
                }
            },
            edit:{
                drag:{
                    isCopy : false,
                    isMove : true,
                },
                enable: true,
                renameTitle: "编辑",
                removeTitle: "删除",
            },
            view: {
		        addHoverDom: addHoverDom,
		        removeHoverDom: removeHoverDom,
	        },
            callback: {//事件回调函数
               beforeRemove:function(treeId, treeNode) {
                    return confirm('确定删除分类['+treeNode.name+']吗?');
               },
               onRemove:function(event, treeId, treeNode){
                   $.post(ajax_url+"?filter=del",{id:treeNode.id},function(data){
                       //var json = JSON.parse(data);                       
                   })
               },
               onRename:function(event, treeId, treeNode, isCancel){
                   $.post(ajax_url+"?filter=update",{id:treeNode.id,name:treeNode.name},function(data){
                       //var json = JSON.parse(data);                       
                   })
               },
               onDrop:function(event, treeId, treeNodes, targetNode, moveType){
                   $(treeNodes).each(function(){
                       $.post(ajax_url+"?filter=move",{id:this.id,target_id:targetNode.id,type:moveType},function(data){
                            //console.log(data);                       
                        })
                   });                  
               }
            }
        };
    // zTree 的数据属性，深入使用请参考 API 文档（zTreeNode 节点数据详解）
    $.getJSON(ajax_url,{filter:'getNodes'},function(zNodes){
        zTreeObj = $.fn.zTree.init($("#treeDemo"), setting, zNodes);
        //展开全部节点
        zTreeObj.expandAll(true);
    });   
JS
));
$this->registerCss('.ztree li span.button.add {
        margin-left: 2px;
        margin-right: -1px;
        background-position: -144px 0;
        vertical-align: top;
    }');
$this->registerJs(new \yii\web\JsExpression(
    <<<JS
    var ajax_url = "{$ajax_url}";
    var zTreeObj;
    function addHoverDom(treeId, treeNode) {
        var sObj = $("#" + treeNode.tId + "_span");
        if (treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length>0) return;
        var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
            + "' title='添加子分类' onfocus='this.blur();'></span>";
        sObj.after(addStr);
        var btn = $("#addBtn_"+treeNode.tId);
        if (btn) btn.bind("click", function(){
            //添加节点
            $.post(ajax_url+"?filter=add",{name:'新分类'+treeNode.id,parent_id:treeNode.id},function(node){
                //console.log(data);
                //var node = JSON.parse(data);
                console.log(node);
                zTreeObj.addNodes(treeNode, {id:node.id, parent_id:node.parent_id, name:node.name});
            });
            return false;
        });
    };
    function removeHoverDom(treeId, treeNode) {
        $("#addBtn_"+treeNode.tId).unbind().remove();
    };
JS
),\yii\web\View::POS_END);