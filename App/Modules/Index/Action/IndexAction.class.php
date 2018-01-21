<?php
//
use \phpspider\core\requests;
use \phpspider\core\selector;

/**
 * 前台首页
 * @author  <[c@easycms.cc]>
 */
class IndexAction extends CommonAction{
    public $img;
    public $title;
    public $desc;
    public $one_data_;
    public $data_;
    public $cate = array(
        'jrnc', //今日南昌
        'ncsp', // 南昌时评
        'szxw', //时政新闻
        'gnxw',//国内新闻http://www.ncnews.com.cn/xwzx/gnxw/
    );


    //news index
    public function index(){
        $url        = "http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/"; //本网原创
        $index_m    = new IndexModel();
        $m_catenews = new CatenewsModel();
        $url        = "http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/index.html";
        $res        = $this->check_url($url);
        // 新闻  最新资讯
        $popular_news = $m_catenews->getCateNewsByRand(2);
        // every_news_section div
        $every_news_section = $m_catenews->getEveryNewsSection(4);
        //pr($every_news_section);
        $this->assign('every_news_section', $every_news_section);
        $this->assign('popular_news', $popular_news);
        $this->assign('one_data', $res['one_data']);
        $this->assign('data', $res['data']);
        $this->assign('datas', $res['data']);
        $this->display('index');
    }

    //cate  nav cate_list
    public function cate_list(){
        $c = include './App/Conf/news.cate.config.php';
        return $c;
    }

    //新闻详情
    public function index_news_detail(){
        $news_content_url = $_GET['url'];
        $index_m          = new IndexModel();
        $twnc_m           = new TwncModel();
        $arr              = $index_m->bwzg_rd_detail($news_content_url);
        $right_list       = $twnc_m->twnc_list(5);

        $this->assign('right_list', $right_list);
        $this->assign('one_news_title', $arr['one_news_title']);
        $this->assign('one_news_time', $arr['one_news_time']);
        $this->assign('one_news_imgs_tags', $arr['one_news_imgs_tags']);
        $this->assign('content_title', $arr['content_title']);
        $this->assign('content_main', $arr['content_main']);
        $this->assign('banner', $arr['banner']);
        $this->assign('news_tag', $arr['news_tag']);
        $this->display('news');
    }

    //递归取出含有图片的新闻
    public function check_url($url, $i = 0){
        $arr     = array();
        $bool    = false;
        $index_m = new IndexModel();
        $result  = $index_m->bwzg_rd($url);

        if(empty($result['one_data']['title']) || empty($result['one_data']['img_href'])){
            if($i == 0){
                $url = "http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/index.html";
            }else{
                $url = "http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/index_" . $i . ".html";
            }
            $this->check_url($url, $i + 1);
        }elseif(!empty($result['one_data']['img_href'])){

            if(empty($this->one_data_)){
                $this->one_data_ = $result['one_data'];
            }

            if(empty($result['data'])){
                if($i == 0){
                    $url = "http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/index.html";
                }else{
                    $url = "http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/index_" . $i . ".html";
                }
                $this->check_url($url, $i + 1);
            }else{
                foreach($result['data'] as $v){
                    if(count($this->data_) < 2){
                        $this->data_[] = $v;
                    }
                }

                if(count($this->data_) < 2){
                    $url = "http://www.ncnews.com.cn/xwzx/ncxw/bwzg_rd/index_" . $i . ".html";
                    $this->check_url($url, $i + 1);
                }
            }
        }

        if(count($this->data_) == 2){
            $bool = true;
            $arr  = array(
                'one_data' => $this->one_data_,
                'data'     => $this->data_
            );
            return $arr;
        }
        return $arr;
    }

    public function notFound(){
        $this->display('404');
    }

    //江西都市
    public function jx(){
        $url = "http://www.jxntv.cn/live/jxtv2.shtml";
        //$main = requests::get($url);
        //$main = file_get_contents($url);
        //pr($main);
        $this->display('jiangxi');
    }

    //定位到街道地址
    public function getAddress(){
        $ip = GetIp();
        if($ip == '127.0.0.1' || preg_match('/^10.211*/i',$ip) || $ip == 'localhost' || preg_match('/^192.168*/i',$ip)){
            $ip = '218.64.55.198';
        }
        $res = $this -> GetIpxy($ip);
        pr($res);
    }
    //定位到街道地址d
    public function GetIpxy($ip){
        //$content = @file_get_contents("http://api.map.baidu.com/location/ip?ak=Gvg7MZ5VYnmZOHW09muMxgXb&ip={$ip}&coor=bd09ll");
        $content = @file_get_contents("http://api.map.baidu.com/location/ip?ak=40GXLRoBSegdclkcR7jx0opT34L1tHhs&ip={$ip}&coor=bd09ll");
        $json = json_decode($content);
        $info = array();
        $info['xy'] = $json->{'content'}->{'point'}->{'x'}.','.$json->{'content'}->{'point'}->{'y'};
        $info['address'] = $json->{'content'}->{'address'};
        return $info;
    }


}
