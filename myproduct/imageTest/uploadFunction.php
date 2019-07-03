<?php
/**
 * Created by ${localhost}.
 * User: yansunda
 * Date: 2019/6/22
 * Time: 16:46
 * Version 1.0
 */
require_once './defineData.php';
/**
 * 根据getimagesize的第三个索引获取，获取图片的真实扩展名字
 * @param $num
 */
function getSuffix($num) {
    switch ($num) {
        case 6:
            $suffix = 'bmp';
            break;
        case 2:
            $suffix = 'jpg';
            break;
        case 4:
            $suffix = 'jpg';
            break;
        case 1:
            $suffix = 'gif';
            break;
        case 3:
            $suffix = 'png';
            break;
        default:
            $suffix = 'png';
            break;
    }
    return $suffix;
}

/**
 * 文件存储规则;
 * 用户id%10000/用户id/imageId%100/imageId.$trueSuffix
 * 解决如果一个文件夹下文件太多io会卡住的问题
 * @param $uid 用户id
 * @param $imageId  插入图片后的id
 * @param $trueSuffix 图片后缀
 */
function getPerfectPath($uid, $imageId, $trueSuffix) {
    return IMAGE_PATH  .($uid%10000). '/' . $uid .'/' . ($imageId%100) . '/' . $imageId . '.'.$trueSuffix;
}

/**
 * 注意：在集群的环境下要考虑一下sync到其他机器
 * @param $path
 */
function makepath($path, $mode = '0755')
{
    if (!is_dir($path)) {
        makepath(dirname($path), $mode);
        mkdir($path, $mode);
    }
    return true;
}

/**文件上传函数
 * @param $info
 */
function validImage($info)
{
    global $allowSuffix;//引用defineData.php的的全局变量
    $imageId = 0;
    $uid = 100;
    if (!empty($info)) {
        foreach ($info as $k => $imageInfo) {
            var_dump($imageInfo);die;
            if (empty($imageInfo['size']) || !empty($imageInfo)) {//文件大小和报错类型
                unset($info[$k]);
                @unlink($imageInfo['tmp_name']);//删除一下临时文件
                continue;
            }
            var_dump($imageInfo);die;
            $suffix = pathinfo($imageInfo['name'], PATHINFO_EXTENSION);//获取文件后缀名字
            $descInfo = getimagesize($imageInfo['tmp_name']);
            //var_dump($descInfo,!in_array($suffix, $allowSuffix),$suffix,$imageInfo['size']/1024);
            if (!is_uploaded_file($imageInfo['tmp_name'])//判断用户是HTTP_POST上传的安全验证
                || $imageInfo['size']/1024 > MAXIMAGESIZE//不能大于512kb
                || !in_array($suffix, $allowSuffix)//后缀是限定的白名单
                || ($descInfo == false)//必须是图片类型
            )
            {
                unset($info[$k]);
                @unlink($imageInfo['tmp_name']);//删除一下临时文件
            } else {
                //var_dump($imageInfo);
                //取一下图片的真实的后缀名字
                $trueSuffix = getSuffix($descInfo[2]);
                //....这里先进行入库操作，返回插入的imageId
                /**
                 * 文件存储规则;
                 * 用户id%10000/用户id/imageId%100/imageId.$trueSuffix
                 * 解决如果一个文件夹下文件太多io会卡住的问题
                 */
                $trueSuffix = empty($trueSuffix) ? $imageInfo['suffix'] : $trueSuffix;
                $imageId++;
                $uid += 100;
                $fileLocation =getPerfectPath($uid, $imageId, $trueSuffix);//根据上述规则获取健全的图片地址
                makepath(pathinfo($fileLocation, PATHINFO_DIRNAME));//递归的创建一下文件夹，注意：在集群的环境下要考虑一下sync到其他机器
                if (move_uploaded_file($imageInfo['tmp_name'], $fileLocation)) {

                } else {
                    //把刚才插入的数据给置空
                }
            }
        }
    }
}