<meta charset="utf8">
<?php

require('./lib/init.php');

// 如果用户没有登入，跳转到登录界面
if(!acc()){
    header('Location: login.php');
}

if(isset($_GET['showpage'])){
    $op = $_GET['showpage'];
    if($op == 'add'){ 
        $showpage = 'add';
        
        if (empty($_POST)){
            include('./view/admin/catlist.html');
        } else {
            $cat['catname'] = trim($_POST['catname']);
            if(empty($cat['catname'])){
                error('栏目不能为空');
            }
            
            $sql = "select count(*) from cat where catname='$cat[catname]'";
            $rs = mQuery($sql);
            if(mysqli_fetch_row($rs)[0] != 0){
                error('栏目已经存在');
            }

            if(!mExec('cat', $cat)){
                error('栏目插入失败');
            } else {
                succ('栏目插入成功');
            }
        }
    }
    else if($op == 'edit'){
        $showpage = 'edit';
        
        $cat_id = $_GET['cat_id'];

        if(!is_numeric($cat_id)) {
            error('栏目不合法');
        }

        $sql = "select count(*) from cat where cat_id=$cat_id";
        $rs = mQuery($sql);
        if(mysqli_fetch_row($rs)[0] == 0){
            error('栏目不存在');
        }

        if(empty($_POST)){
            $sql = "select catname from cat where cat_id=$cat_id";
            $rs = mQuery($sql);
            $cat = mysqli_fetch_assoc($rs);
            require('./view/admin/catlist.html');
        } else {
            $sql = "update cat set catname='$_POST[catname]' where cat_id=$cat_id";
            if(!mQuery($sql)){
                error('栏目修改失败');
            } else {
                succ('栏目修改成功');
            }
        }
    }
    else if($op == 'del'){
        $showpage = 'del';
        
        $cat_id = $_GET['cat_id'];

        if(!is_numeric($cat_id)) {
            error('栏目不合法');
            exit();
        }

        $sql = "select count(*) from cat where cat_id=$cat_id";
        $rs = mQuery($sql);
        if(mysqli_fetch_row($rs)[0] == 0){
            error('栏目不存在');
            exit();
        }

        $sql = "select count(*) from art where cat_id=$cat_id";
        $rs = mQuery($sql);
        if(mysqli_fetch_row($rs)[0] != 0){
            error('栏目下有文章，不能删除');
            exit();
        }

        $sql = "delete from cat where cat_id=$cat_id";
        if(!mQuery($sql)){
            error('栏目删除失败');
        } else {
            error('栏目删除成功');
        }
    }
}
else {
    $showpage = 'none';

    $sql = "select * from cat";
    $cat = mGetAll($sql);

    require('./view/admin/catlist.html');
}

?>