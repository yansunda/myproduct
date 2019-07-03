<?php
/**
 * Created by phpStrom.
 * User: yansunda
 * Date: 2019/6/22
 * Time: 14:57
 * Version 1.
 * Desc : 用户上传文件的安全认证
 */
//做上传图片程序处理
/**
 * 限制条件
 * 大小不大于512k
 * 必须使用bmp,jpj,png,jpeg,gif
 */
require_once './defineData.php';
require_once './uploadFunction.php';
$info = $_FILES;
validImage($info);