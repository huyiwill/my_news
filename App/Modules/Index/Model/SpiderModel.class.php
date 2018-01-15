<?php
use \phpspider\core\requests;
use \phpspider\core\selector;

/**
 * Created by PhpStorm.
 * User: huyi
 * Date: 2018/1/5
 * Time: 9:22
 */
class SpiderModel extends Model{
    //爬取图文nanchang list
    //    /201712/t20171229_1131037.html
    public function spider1(){
        $configs = array(
            'name'                => 'nanchang_news',
            'log_show'            => true,
            'domains'             => array(
                'www.ncnews.com.cn'
            ),
            'scan_urls'           => array(
                'http://www.ncnews.com.cn/xwzx/ncxw/twnc/'
            ),
            'list_url_regexes'    => array(
                'http://www.ncnews.com.cn/xwzx/ncxw/twnc/'
            ),
            'content_url_regexes' => array(
                "http://www.ncnews.com.cn/xwzx/ncxw/twnc/\d+/^[a-z0-9_].html"
            ),
            'max_try'             => 3,
            'export'              => array(
                'type' => 'csv',
                'file' => '../../../qiushibaike.csv',
            ),
            'fields'              => array(
                'name'     => 'title',
                'selector' => "//li[@class='item']//h3",
                'required' => true,
            ),

        );
        //
        $spider = new \phpspider\core\phpspider($configs);
        $spider -> on_handle_img = function($fieldname, $img){
            $regex = '/src="(.*?)"/i';
            preg_match($regex, $img, $rs);
            if (!$rs)
            {
                return $img;
            }

            $url = $rs[1];
            $img = $url;
            p($img);
            return $img;
        };
    }
}

//$m = new SpiderModel();
//$m -> spider1();