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
        array(
            'name'     => 'news_list',
            'selector' => "//div[contains(@id,'container')]//ul//li[contains(@class,'item')]",
            'required' => true,
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
    $arr = array();
    if($fieldname == 'news_list'){
        if(preg_match('/jrnc/i',$page['url'])){
            $h = pathinfo($page['url']);
            $urls = $h['dirname'];
            if(is_array($data)){
                foreach($data as $k => $v){
                    $arr[$k]['news_cate'] = 'jrnc';
                    $arr[$k]['news_title'] = trim(strip_tags(selector::select($v,"//h3//a")));
                    $arr[$k]['news_desc'] = trim(strip_tags(selector::select($v,"//h5")));
                    $arr[$k]['news_time'] = trim(strip_tags(selector::select($v,"//h6")));
                    $news_content_url = trim(strip_tags(selector::select($v,'/<h3><a[^<>]+href *\= *[\\"\']?([^\'\\"]+).*?/i','regex')));
                    if(!empty($news_content_url)){
                        if(substr($news_content_url, 0, 1) == 'h'){
                            $arr[$k]['news_content_url'] = $news_content_url;
                        }else{
                            $arr[$k]['news_content_url'] = $urls . ltrim($news_content_url, '.');
                        }
                        $href = $arr[$k]['news_content_url'];
                        $u = pathinfo($href);
                        $arr[$k]['news_filename'] = $u['filename'];
                    }else{
                        $arr[$k]['news_content_url'] = '';
                    }
                    $news_img_url = trim(strip_tags(selector::select($v,"//img")));
                    if(!empty($news_img_url)){
                        if(substr($news_img_url, 0, 1) == 'h'){
                            $arr[$k]['news_img_url'] = $news_img_url;
                        }else{
                            $arr[$k]['news_img_url'] = $urls . ltrim($news_img_url, '.');
                        }
                    }else{
                        $arr[$k]['news_img_url'] = '';
                    }
                }
            }
        }elseif(preg_match('/ncsp/i',$page['url'])){
            $h = pathinfo($page['url']);
            $urls = $h['dirname'];
            if(is_array($data)){
                foreach($data as $k => $v){
                    $arr[$k]['news_cate'] = 'ncsp';
                    $arr[$k]['news_title'] = trim(strip_tags(selector::select($v,"//h3//a")));
                    $arr[$k]['news_desc'] = trim(strip_tags(selector::select($v,"//h5")));
                    $arr[$k]['news_time'] = trim(strip_tags(selector::select($v,"//h6")));
                    $news_content_url = trim(strip_tags(selector::select($v,'/<h3><a[^<>]+href *\= *[\\"\']?([^\'\\"]+).*?/i','regex')));
                    if(!empty($news_content_url)){
                        if(substr($news_content_url, 0, 1) == 'h'){
                            $arr[$k]['news_content_url'] = $news_content_url;
                        }else{
                            $arr[$k]['news_content_url'] = $urls . ltrim($news_content_url, '.');
                        }
                        $href = $arr[$k]['news_content_url'];
                        $u = pathinfo($href);
                        $arr[$k]['news_filename'] = $u['filename'];
                    }else{
                        $arr[$k]['news_content_url'] = '';
                    }

                    $news_img_url = trim(strip_tags(selector::select($v,"//img")));
                    if(!empty($news_img_url)){
                        if(substr($news_img_url, 0, 1) == 'h'){
                            $arr[$k]['news_img_url'] = $news_img_url;
                        }else{
                            $arr[$k]['news_img_url'] = $urls . ltrim($news_img_url, '.');
                        }
                    }else{
                        $arr[$k]['news_img_url'] = '';
                    }
                }
            }
        }elseif(preg_match('/szxw/i',$page['url'])){
            $h = pathinfo($page['url']);
            $urls = $h['dirname'];
            if(is_array($data)){
                foreach($data as $k => $v){
                    $arr[$k]['news_cate'] = 'szxw';
                    $arr[$k]['news_title'] = trim(strip_tags(selector::select($v,"//h3//a")));
                    $arr[$k]['news_desc'] = trim(strip_tags(selector::select($v,"//h5")));
                    $arr[$k]['news_time'] = trim(strip_tags(selector::select($v,"//h6")));
                    $news_content_url = trim(strip_tags(selector::select($v,'/<h3><a[^<>]+href *\= *[\\"\']?([^\'\\"]+).*?/i','regex')));
                    if(!empty($news_content_url)){
                        if(substr($news_content_url, 0, 1) == 'h'){
                            $arr[$k]['news_content_url'] = $news_content_url;
                        }else{
                            $arr[$k]['news_content_url'] = $urls . ltrim($news_content_url, '.');
                        }
                        $href = $arr[$k]['news_content_url'];
                        $u = pathinfo($href);
                        $arr[$k]['news_filename'] = $u['filename'];
                    }else{
                        $arr[$k]['news_content_url'] = '';
                    }

                    $news_img_url = trim(strip_tags(selector::select($v,"//img")));
                    if(!empty($news_img_url)){
                        if(substr($news_img_url, 0, 1) == 'h'){
                            $arr[$k]['news_img_url'] = $news_img_url;
                        }else{
                            $arr[$k]['news_img_url'] = $urls . ltrim($news_img_url, '.');
                        }
                    }else{
                        $arr[$k]['news_img_url'] = '';
                    }
                }
            }
        }else{  //'gnxw'  国内新闻
            $h = pathinfo($page['url']);
            $urls = $h['dirname'];
            if(is_array($data)){
                foreach($data as $k => $v){
                    $arr[$k]['news_cate'] = 'gnxw';
                    $arr[$k]['news_title'] = trim(strip_tags(selector::select($v,"//h3//a")));
                    $arr[$k]['news_desc'] = trim(strip_tags(selector::select($v,"//h5")));
                    $arr[$k]['news_time'] = trim(strip_tags(selector::select($v,"//h6")));
                    $news_content_url = trim(strip_tags(selector::select($v,'/<h3><a[^<>]+href *\= *[\\"\']?([^\'\\"]+).*?/i','regex')));
                    if(!empty($news_content_url)){
                        if(substr($news_content_url, 0, 1) == 'h'){
                            $arr[$k]['news_content_url'] = $news_content_url;
                        }else{
                            $arr[$k]['news_content_url'] = $urls . ltrim($news_content_url, '.');
                        }
                        $href = $arr[$k]['news_content_url'];
                        $u = pathinfo($href);
                        $arr[$k]['news_filename'] = $u['filename'];
                    }else{
                        $arr[$k]['news_content_url'] = '';
                    }

                    $news_img_url = trim(strip_tags(selector::select($v,"//img")));
                    if(!empty($news_img_url)){
                        if(substr($news_img_url, 0, 1) == 'h'){
                            $arr[$k]['news_img_url'] = $news_img_url;
                        }else{
                            $arr[$k]['news_img_url'] = $urls . ltrim($news_img_url, '.');
                        }
                    }else{
                        $arr[$k]['news_img_url'] = '';
                    }
                }
            }
        }
    }
    return $arr;
};

//在一个网页的所有field抽取完成之后, 可能需要对field进一步处理, 以发布到自己的网站
$spider->on_extract_page = function ($page, $data){
    $data = $data['news_list'];
        if(is_array($data)){
            foreach($data as  $val){
                $sql = "Select Count(*) As `count` From `easy_news_cate` Where `news_filename`=" . $val['news_filename'];
                $row = db::get_one($sql);
                if(!$row['count']){
                    db::insert("easy_news_cate", $val);
                }
            }
        }
    return $data;
};

$spider->start();