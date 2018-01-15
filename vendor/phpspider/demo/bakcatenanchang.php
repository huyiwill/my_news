<?php

require_once __DIR__ . '/../autoloader.php';use \phpspider\core\phpspider;
use \phpspider\core\requests;
use \phpspider\core\selector;
use phpspider\core\db;

/* Do NOT delete this comment */
/* 不要删除这段注释 */

//$main = requests::get("http://www.ncnews.com.cn/xwzx/ncxw/twnc/");
//$titles = selector::select($main,"//div[contains(@id,'container')]//ul//li[contains(@class,'item')]//h3//a");
//print_r($titles);die;

$cate = array(
    'jrnc', //今日南昌
    'ncsp', // 南昌时评
    'szxw', //时政新闻
    'gnxw',//国内新闻http://www.ncnews.com.cn/xwzx/gnxw/

);

$configs = array(
    'name'      => 'news_cate',
    'log_show'  => false,
    'domains'   => array(
        'www.ncnews.com.cn'
    ),
    'scan_urls' => array(  //入口
                           "http://www.ncnews.com.cn/xwzx/ncxw/jrnc/",
                           "http://www.ncnews.com.cn/xwzx/ncxw/ncsp/",
                           "http://www.ncnews.com.cn/xwzx/ncxw/szxw/",
                           "http://www.ncnews.com.cn/xwzx/gnxw/"
    ),

    'content_url_regexes' => array(
        "http://www.ncnews.com.cn/xwzx/ncxw/jrnc/index(_[0-9]{0,2})?.html",
        "http://www.ncnews.com.cn/xwzx/ncxw/ncsp/index(_[0-9]{0,2})?.html",
        "http://www.ncnews.com.cn/xwzx/ncxw/szxw/index(_[0-9]{0,2})?.html",
        "http://www.ncnews.com.cn/xwzx/gnxw/index(_[0-9]{0,2})?.html",
    ),
    'max_try'             => 3,
    'export'              => array(
        'type'  => 'db',
        'table' => 'easy_news_cate',
    ),
    'db_config'           => array(
        'host' => '127.0.0.1',
        'port' => 3306,
        'user' => 'root',
        'pass' => '',
        'name' => 'easycms',
    ),
    'fields'              => array(
        //array(
        //    'name'     => 'news_list',
        //    'selector' => "//div[contains(@id,'container')]//ul//li[contains(@class,'item')]",
        //    'required' => true,
        //    'repeated' => true,
        //),
        array(
            'name'     => 'news_title',
            'selector' => "//div[contains(@id,'container')]//ul//li[contains(@class,'item')]//h3//a",
            'required' => true,
            'repeated' => true,
        ),
        array(
            'name'     => 'news_desc',
            'selector' => "//div[contains(@id,'container')]//ul//li[contains(@class,'item')]//h5",
            'required' => true,
            'repeated' => true,
        ),
        array(
            'name'     => 'news_time',
            'selector' => "//div[contains(@id,'container')]//ul//li[contains(@class,'item')]//h6",
            'required' => true,
            'repeated' => true,
        ),
        array(
            'name'     => 'news_content_url',
            'selector' => '/<h3><a[^<>]+href *\= *[\\"\']?([^\'\\"]+).*?/i',
            'selector_type' => 'regex',
            'required' => true,
            'repeated' => true,
        ),
        array(
            'name'     => 'news_img_url',
            'selector' => "//div[contains(@id,'container')]//ul//li[contains(@class,'item')]//img",
            'required' => false,
            'repeated' => true,
        ),

    ),

);

$spider = new phpspider($configs);

$spider->on_scan_page = function ($page, $content, $phpspider){
    for($i = 0; $i < 2; $i++){
        if($i == 0){
            $url1 = "http://www.ncnews.com.cn/xwzx/ncxw/jrnc/index.html";
            $url2 = "http://www.ncnews.com.cn/xwzx/ncxw/ncsp/index.html";
            $url3 = "http://www.ncnews.com.cn/xwzx/ncxw/szxw/index.html";
            $url4 = "http://www.ncnews.com.cn/xwzx/gnxw/index.html";
        }else{
            $url1 = "http://www.ncnews.com.cn/xwzx/ncxw/jrnc/index_" . $i . ".html";
            $url2 = "http://www.ncnews.com.cn/xwzx/ncxw/ncsp/index_" . $i . ".html";
            $url3 = "http://www.ncnews.com.cn/xwzx/ncxw/szxw/index_" . $i . ".html";
            $url4 = "http://www.ncnews.com.cn/xwzx/gnxw/index_" . $i . ".html";
        }
        $options = array(
            'method' => 'get',
            'params' => array(
                'page' => $i,
            ),
        );
        $phpspider->add_url($url1, $options);
        $phpspider->add_url($url2, $options);
        $phpspider->add_url($url3, $options);
        $phpspider->add_url($url4, $options);
    }
};

$spider->on_extract_field = function ($fieldname, $data, $page){
    if($fieldname == 'news_content_url'){
        $url = pathinfo($page['url']);
        $url = $url['dirname'];
        if(preg_match('/jrnc/i',$page['url'])){
            foreach($data as $k => $v){
                if(substr($v, 0, 1) == 'h'){
                    $data[$k] = $v;
                }else{
                    $data[$k] = $url . ltrim($v, '.');
                }
            }
        }elseif(preg_match('/ncsp/i',$page['url'])){
            foreach($data as $k => $v){
                if(substr($v, 0, 1) == 'h'){
                    $data[$k] = $v;
                }else{
                    $data[$k] = $url . ltrim($v, '.');
                }
            }
        }elseif(preg_match('/szxw/i',$page['url'])){
            foreach($data as $k => $v){
                if(substr($v, 0, 1) == 'h'){
                    $data[$k] = $v;
                }else{
                    $data[$k] = $url . ltrim($v, '.');
                }
            }
        }else{  //'gnxw'  国内新闻
            foreach($data as $k => $v){
                if(substr($v, 0, 1) == 'h'){
                    $data[$k] = $v;
                }else{
                    $data[$k] = $url . ltrim($v, '.');
                }
            }
        }
    }elseif($fieldname == 'news_time'){
        foreach($data as $k => $v){
            $data[$k] = trim(strip_tags($v));
        }
    }
    return $data;
};

//在一个网页的所有field抽取完成之后, 可能需要对field进一步处理, 以发布到自己的网站
$spider->on_extract_page = function ($page, $data){

    if(preg_match('/jrnc/i',$page['url'])){

        foreach($data as $k => $arr){
            if(!empty($data[$k])){
                
            }
        }
    }elseif(preg_match('/ncsp/i',$page['url'])){

    }elseif(preg_match('/szxw/i',$page['url'])){

    }else{  //国内新闻   'gnxw'

    }




    if(isset($data['titles'])){
        foreach($data['titles'] as $v){
            $arr = array(
                'title'      => trim($v['title']),
                'title_imgs' => urlencode($v['title_imgs']),
                'title_desc' => $v['title_desc'],
                'title_link' => urlencode($v['title_link']),
                'title_time' => $v['title_time']
            );
            $sql = "Select Count(*) As `count` From `easy_news_cate` Where `title`=" . $v['title'];
            $row = db::get_one($sql);
            if(!$row['count']){
                db::insert("easy_news_cate", $arr);
            }
        }
        $data = $arr;
    }
    return $data;
};

$spider->start();