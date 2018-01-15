<?php

require_once __DIR__ . '/../autoloader.php';
use \phpspider\core\phpspider;
use \phpspider\core\requests;
use \phpspider\core\selector;
use phpspider\core\db;

/* Do NOT delete this comment */
/* 不要删除这段注释 */

//$main = requests::get("http://www.ncnews.com.cn/xwzx/ncxw/twnc/");
//$titles = selector::select($main,"//div[contains(@id,'container')]//ul//li[contains(@class,'item')]//h3//a");
//print_r($titles);die;

$configs = array(
    'name'      => 'nanchang_news',
    'log_show'  => true,
    'domains'   => array(
        'www.ncnews.com.cn'
    ),
    'scan_urls' => array(  //入口
                           "http://www.ncnews.com.cn/xwzx/ncxw/twnc/"
    ),

    //'list_url_regexes' => array(
    //    "http://www.ncnews.com.cn/xwzx/ncxw/twnc/index(_[0-9]{0,2})?.html",
    //),

    'content_url_regexes' => array(
        "http://www.ncnews.com.cn/xwzx/ncxw/twnc/index(_[0-9]{0,2})?.html",
        //"http://www.ncnews.com.cn/xwzx/ncxw/twnc/\d+/[a-z0-9_]{0,30}.html",
        //"http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/tpyc/\d+/[a-z0-9_]{0,30}.html",
        //\d+/^[a-z0-9_].html  index(_[0-9]{0,2})?.html  (index([_][0-9])?).html
    ),
    'max_try'             => 5,
    //'export'              => array(
    //    'type' => 'csv',
    //    'file' => './nanchang.csv',
    //),
    'export'              => array(
        'type'  => 'db',
        'table' => 'easy_news_twnc',
    ),
    'db_config'           => array(
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'pass' => 'aa',
        'name' => 'easycms',
    ),
    'fields'              => array(
        array(
            'name'     => 'titles',
            'selector' => "//div[contains(@id,'container')]//ul//li[contains(@class,'item')]",
            'required' => true,
            'repeated' => true,
        ),
        //array(
        //    'name'     => 'title_content',//align="justify"
        //    //'selector' => "//div[contains(@class,'read')]//h3",
        //    'selector' => "//div[contains(@class,'TRS_Editor')]",
        //    'required' => false,
        //    'repeated' => true,
        //),
    ),

);
//
$spider = new phpspider($configs);

$spider->on_start = function ($phpspider){
    requests::set_header('Referer', 'http://www.ncnews.com.cn/xwzx/ncxw/twnc/index.html');
};

$spider->on_scan_page = function ($page, $content, $phpspider){
    for($i = 0; $i < 3; $i++){
        if($i == 0){
            $url = "http://www.ncnews.com.cn/xwzx/ncxw/twnc/index.html";
        }else{
            $url = "http://www.ncnews.com.cn/xwzx/ncxw/twnc/index_{$i}.html";
        }
        $options = array(
            'method' => 'get',
            'params' => array(
                'page' => $i,
            ),
        );
        $phpspider->add_url($url, $options);
    }
};

//$spider -> on_list_page=function ($page, $content, $phpspider){
//
//};

//$spider -> on_list_page = function($page, $content, $phpspider){
//    $url   = "http://www.ncnews.com.cn/xwzx/ncxw/twnc";
//    $p          = '/<h3><a[^<>]+href *\= *[\"\']?([^\'\"]+).*?/i';
//    $title_url  = selector::select($content, $p, 'regex');
//    foreach($title_url as $v){
//        if(substr($v, 0, 1) == 'h'){
//            $title_link = $v;
//        }else{
//            $title_link = $url . ltrim($v, '.');
//        }
//        $options = array(
//            'method' => 'get',
//        );
//        $phpspider->add_url($title_link, $options);
//    }
//    //$sql = "Select title_link From `news_twnc`";
//    //$row = db::get_all($sql);
//};
//当一个field的内容被抽取到后进行的回调, 在此回调中可以对网页中抽取的内容作进一步处理
$spider->on_extract_field = function ($fieldname, $data, $page){
    $arr = array();
    if($fieldname == 'titles'){
        if(is_array($data)){
            foreach($data as $k => $v){
                $img = selector::select($v, "//img");
                if(empty($img)){
                    unset($data[$k]);
                }else{
                    $url   = "http://www.ncnews.com.cn/xwzx/ncxw/twnc";
                    $title = trim(selector::select($v, "//h3//a"));
                    if(substr(selector::select($v, "//img"), 0, 1) == 'h'){
                        $title_imgs = selector::select($v, "//img");
                    }else{
                        $title_imgs = $url . ltrim(selector::select($v, "//img"), '.');
                    }
                    $title_desc = trim(selector::select($v, "//h5"));
                    $p          = '/<h3><a[^<>]+href *\= *[\"\']?([^\'\"]+).*?/i';
                    $title_url  = selector::select($v, $p, 'regex');
                    if(substr($title_url, 0, 1) == 'h'){
                        $title_link = $title_url;
                    }else{
                        $title_link = $url . ltrim($title_url, '.');
                    }

                    $title_time = strip_tags(selector::select($v, "//h6"));

                    $arr[$k] = array(
                        'title'      => $title,
                        'title_imgs' => $title_imgs,
                        'title_desc' => $title_desc,
                        'title_link' => $title_link,
                        'title_time' => $title_time
                    );
                }
            }
        };
    }
    return $arr;
};

//在一个网页的所有field抽取完成之后, 可能需要对field进一步处理, 以发布到自己的网站
$spider->on_extract_page = function ($page, $data){
    //if(isset($data['title_content'])){//$fieldname == 'title_content'
    //    print_r($data['title_content']);die;
    //}
    if(isset($data['titles'])){
        foreach($data['titles'] as $v){
            $arr = array(
                'title'      => trim($v['title']),
                'title_imgs' => urlencode($v['title_imgs']),
                'title_desc' => $v['title_desc'],
                'title_link' => urlencode($v['title_link']),
                'title_time' => $v['title_time']
            );
            $sql = "Select Count(*) As `count` From `easy_news_twnc` Where `title`=".$v['title'];
            $row = db::get_one($sql);
            if(!$row['count']){
                db::insert("easy_news_twnc", $arr);
            }
        }
        $data = $arr;
    }
    return $data;
};

$spider->start();