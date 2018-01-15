<?php

if($_SERVER['SERVER_ADDR'] == '120.26.38.109'){
    $host = $_SERVER['HTTP_HOST'] . '/www/my_news/';
}else{
    $host = $_SERVER['HTTP_HOST'];
}

$action = 'http://' . $host . '/index.php?g=index&m=catenews&a=cate_list&domain=';

$data['news_list'] = array(
    'jrnc' => array(
        'name'   => '今日南昌',
        'domain' => 'jrnc',
        'url'    => $action . 'jrnc',
    ), //今日南昌
    'ncsp' => array(
        'name'   => '南昌时评',
        'domain' => 'ncsp',
        'url'    => $action . 'ncsp'
    ),// 南昌时评
    'szxw' => array(
        'name'   => '时政新闻',
        'domain' => 'szxw',
        'url'    => $action . 'szxw'
    ), //时政新闻
    'gnxw' => array(
        'name'   => '国内新闻',
        'domain' => 'gnxw',
        'url'    => $action . 'gnxw'
    ),//国内新闻
);
return $data;
?>
