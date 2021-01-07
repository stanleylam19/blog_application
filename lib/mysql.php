<?php
error_reporting(0);
/*
    @author me
*/

/**
    连接数据库
    
    @return resource 连接成功，返回连接数据库
*/
function mConn() {
    static $conn = null;
    if($conn === null){
        $cfg = require(ROOT . '/lib/config.php');
        $conn = mysqli_connect($cfg['host'], $cfg['user'], $cfg['pwd']);
        mysqli_query($conn, 'use '.$cfg['db']);
        mysqli_query($conn, 'set names '.$cfg['charset']);
    }

    return $conn;
}

/**
* 查询的函数
* @return mixed resource/bool
*/
function mQuery($sql){
    $conn = mConn();
    $rs = mysqli_query($conn, $sql);
    if($rs){
        mLog($sql);
    } else {
        mLog($sql . "\n" . mysqli_error($conn));
    }
    return $rs;
}

/**
* log日志记录功能
* @param str $str 待记录的字符串
*/
function mLog($str){
    $filename = ROOT . '/log/' . date('Ymd') . '.txt';
    $log = "-----------------------------------------\n" . "\n" . $str . "\n" . "-----------------------------------------\n\n";
    file_put_contents($filename, $log, FILE_APPEND);
}


/**
* select 查询多行数据
*
* @param str $sql select 待查询的sql语句
* @return mixed select 查询成功，返回二维数组，失败返回false
*/

function mGetAll($sql){
    $rs = mQuery($sql);
    if(!$rs){
        return false;
    }
    
    $data = array();
    while($row = mysqli_fetch_assoc($rs)){
        $data[] = $row;
    }
    
    return $data;
}


/**
* select 取出一行数据
*
* @param str $sql 待查询的sql语句
* @return arr/false 查询成功 返回一个一维数组
*/

function mGetRow($sql){
    $rs = mQuery($sql);
    if(!$rs){
        return false;
    }
    
    return mysqli_fetch_assoc($rs);
}

/**
* select 查询返回一个结果
*
* @param str $sql 待查询的select语句
* @return mixed 成功，返回结果，失败返回false
*/

function mGetOne($sql){
    $rs = mQuery($sql);
    if(!rs){
        return false;
    }
    
    return mysqli_fetch_row($rs);
}

/**
* 自动拼接 insert 和 update sql语句，并且调用 mQuery() 去执行sql
* @param str $table 表名
* @param arr $data 接收到的数据，一维数组
* @param str $act 动作 默认为 'insert'
* @param bool insert 或者 update 插入成功或失败
*/

function mExec($table, $data, $act='insert', $where=0) {
    if($act == 'insert'){
        $sql = "insert into $table (";
        $sql .= implode(',', array_keys($data)) . ") values ('";
        $sql .= implode("','", array_values($data)) . "')";
        return mQuery($sql);
    } else if ($act == 'update') {
        $sql = "update $table set ";
        foreach($data as $k=>$v) {
            $sql .= $k . "='" . $v . "',";
        }
        
        $sql = rtrim($sql, ',') . "where ".$where;
        return mQuery($sql);
    }
}

/**
* 取得上一步 insert 操作产生的主键id
*/
function getLastId(){
    return mysqli_insert_id(mConn());
}

?>