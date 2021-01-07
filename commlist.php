<?php

require('./lib/init.php');

// 如果用户没有登入，跳转到登录界面
if(!acc()){
    header('Location: login.php');
}

//判断地址栏是否有cat_id，如果有就执行删除操作
if(isset($_GET['comment_id'])){
    $comment_id = $_GET['comment_id'];
    //获取当前评论的 art_id
    $sql = "select art_id from comment where comment_id=" . $comment_id;
    $art_id = mGetOne($sql)[0];

    //删除评论表这条评论
    $sql = "delete from comment where comment_id=" . $comment_id;
    $rs = mQuery($sql);
    //如果获取art_id成功 更改art表的comm评论数
    if($art_id){
        $sql = "update art set comm=comm-1 where art_id=" . $art_id;
        mQuery($sql);
    }
    
    //分页代码
    $sql = "select count(*) from comment"; //获取总的文章数
    $num = mGetOne($sql)[0]; //总的文章数
    $curr = isset($_GET['page']) ? $_GET['page'] : 1;
    $cnt = 5; //每页显示条数
    $page = getPage($num, $curr, $cnt);
    
    $sql = "select * from comment order by comment_id desc limit " . ($curr-1)*$cnt . ',' . $cnt; //获取总的文章数
    $comms = mGetAll($sql);
        
    header('Location: commlist.php');
} else {
    //分页代码
    $sql = "select count(*) from comment"; //获取总的文章数
    $num = mGetOne($sql)[0]; //总的文章数
    $curr = isset($_GET['page']) ? $_GET['page'] : 1;
    $cnt = 5; //每页显示条数
    $page = getPage($num, $curr, $cnt);
    
    $sql = "select * from comment order by comment_id desc limit " . ($curr-1)*$cnt . ',' . $cnt; //获取总的文章数
    $comms = mGetAll($sql);

    require(ROOT . '/view/admin/commlist.html');
}

?>