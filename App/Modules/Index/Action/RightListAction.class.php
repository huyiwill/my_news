<?php
//
use \phpspider\core\requests;
use \phpspider\core\selector;

/**
 * 右侧新闻列表详情数据  暂时没有用到
 * @author  <[c@easycms.cc]>
 */
class RightListAction extends CommonAction{
    public $img;
    public $title;
    public $desc;
    public $one_data_;
    public $data_;

    //news.html  页面  右侧新闻列表详情数据
    public function index(){
        $news_content_url = $_GET['url'];
        $index_m = new IndexModel();
        $twnc_m = new TwncModel();
        $arr = $index_m ->bwzg_rd_detail($news_content_url);
        $right_list = $twnc_m -> twnc_list(5);

        $this -> assign('right_list',$right_list);
        $this -> assign('one_news_title',$arr['one_news_title']);
        $this -> assign('one_news_time',$arr['one_news_time']);
        $this -> assign('one_news_imgs_tags',$arr['one_news_imgs_tags']);
        $this -> assign('content_title',$arr['content_title']);
        $this -> assign('content_main',$arr['content_main']);
        $this -> assign('banner',$arr['banner']);
        $this -> assign('news_tag',$arr['news_tag']);
        $this -> display('news');
    }

}
