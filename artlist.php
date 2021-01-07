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
        
        $sql = 'select * from cat';
        $cats = mGetAll($sql);

        if(empty($_POST)){
            include(ROOT . '/view/admin/artlist.html');
        } else {
            $art['title'] = trim($_POST['title']);
            if($art['title'] == ''){
                error('标题不能为空');
            }
            
            $art['cat_id'] = $_POST['cat_id'];
            if(!is_numeric($art['cat_id'])){
                error('栏目不合法');
            }
            
            $art['content'] = trim($_POST['content']);
            if($art['content'] == ''){
                error('内容不能为空');
            }
            
            //判断是否有图片上传 且error是否序0
            if( !($_FILES['pic']['name'] == '') && $_FILES['pic']['error'] == 0) {
                $filename = createDir() . '/' . randStr() . getExt($_FILES['pic']['name']);
                if(move_uploaded_file($_FILES['pic']['tmp_name'], ROOT . $filename)){
                    $art['pic'] = $filename;
                    $art['thumb'] = makeThumb($filename);
                }
            }

            //插入发布时间
            $art['pubtime'] = time();
            
            //收集tag
            $art['arttag'] = trim($_POST['tag']);
            
            //插入内容到art表
            if(!mExec('art', $art)){
                error('文章发布失败');
            } else {
                //判断是否有tag
                $art['tag'] = trim($_POST['tag']);
                if($art['tag'] == ''){
                    //将cat 的 num 字段 当前栏目下的文章数 +1
                    $sql = "update cat set num=num+1 where cat_id=$art[cat_id]";
                    mQuery($sql);
                    succ('文章添加成功');
                } else {
                    //获取上次 insert 操作产生的主键id
                    $art_id = getLastId();
                    //插入tag 到tag表
                    $tag = explode(',', $art['tag']);
                    $sql = "insert into tag (art_id,tag) values ";
                    foreach($tag as $v) {
                        $sql .= "(" . $art_id . ",'" . $v . "') ,";
                    }
                    $sql = rtrim($sql, ",");
                    if(mQuery($sql)){
                        //将cat 的 num 字段 当前栏目下的文章数 +1
                        $sql = "update cat set num=num+1 where cat_id=$art[cat_id]";
                        mQuery($sql);
                        succ('文章添加成功');
                    } else {
                        //tag 添加失败 删除原文章
                        $sql = "delete from art where art_id=$art_id";
                        //将cat 的 num 字段 当前栏目下的文章数 -1
                        $sql = "update cat set num=num-1 where cat_id=$art[cat_id]";
                        mQuery($sql);
                        if(mQuery($sql)){
                            error('文章添加失败');
                        }
                    }
                }
            }
        }
    }
    else if($op == 'edit'){
        $showpage = 'edit';
        $art_id = $_GET['art_id'];

        if(!is_numeric($art_id)){
            error('文章id不合法');
        }

        $sql = "select * from art where art_id=$art_id";
        if(!mGetRow($sql)){
            error('文章不存在');
        }
        
        $sql = "select * from cat";
        $cats = mGetAll($sql);

        if(empty($_POST)){
            $sql = "select title,content,cat_id,arttag from art where art_id=$art_id";
            $art = mGetRow($sql);
            include(ROOT . '/view/admin/artlist.html');
        } else {
            $art['title'] = trim($_POST['title']);
            if($art['title'] == ''){
                error('标题不能为空');
            }
            
            $art['cat_id'] = $_POST['cat_id'];
            if(!is_numeric($art['cat_id'])){
                error('栏目不合法');
            }
            
            $art['content'] = trim($_POST['content']);
            if($art['content'] == ''){
                error('内容不能为空');
            }
            
            $art['arttag'] = trim($_POST['tag']);
            
            $art['lastup'] = time();
            if(!mExec('art', $art, 'update', "art_id=$art_id")){
                error('文章修改失败');
            } else {
                //删除tag表的所有tag 再insert插入新的tag
                $sql = "select tag.tag from tag where tag.art_id = $art_id";
                $rs = mQuery($sql);
                $arr = array();
                while($row = mysqli_fetch_assoc($rs)){
                     $arr[] = $row;
                }
                foreach ($arr as $a){
                    $t = $a['tag'];
                    $sql = "delete from tag where tag='$t' AND art_id = $art_id";
                    mQuery($sql);
                }
                $tag = explode(',', $art['arttag']);
                $sql = "insert into tag (art_id,tag) values ";
                foreach($tag as $v) {
                        $sql .= "(" . $art_id . ",'" . $v . "') ,";
                }
                $sql = rtrim($sql, ",");
                if(mQuery($sql)){
                    succ('文章修改成功');
                }
                else {
                    error('文章修改失败');
                }
            }
        }
    }
    else if($op == 'del'){
        $showpage = 'del';
        
        $art_id = $_GET['art_id'];

        if(!is_numeric($art_id)){
            error('文章id不合法');
        }

        $sql = "select * from art where art_id=$art_id";
        if(!mGetRow($sql)){
            error('文章不存在');
        }

        $sql = "delete from art where art_id=$art_id";
        if(!mQuery($sql)){
            error('文章刪除失败');
        } else {
            succ('文章刪除成功');
            header('Location: artlist.php');
        }
    }
}
else {
    $showpage = 'none';
    //分页代码
    $sql = "select count(*) from art"; //获取总的文章数
    $num = mGetOne($sql)[0]; //总的文章数
    $curr = isset($_GET['page']) ? $_GET['page'] : 1;
    $cnt = 5; //每页显示条数
    $page = getPage($num, $curr, $cnt);

    //查询所有的文章
    $sql = "select art_id,title,pubtime,comm,catname from art left join cat on art.cat_id = cat.cat_id order by art_id desc limit " . ($curr-1)*$cnt . ',' . $cnt;
    $arts = mGetAll($sql);

    include(ROOT . '/view/admin/artlist.html');
}

?>