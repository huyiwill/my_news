<?php

/**
 * 新闻类别
 * @author  <[c@easycms.cc]>
 */
class CatenewsAction extends CommonAction{
    public $img;
    public $title;
    public $desc;
    public $one_data_;
    public $data_;
    public $cate = array(
        'jrnc' => '今日南昌', //今日南昌
        'ncsp' => '南昌时评', // 南昌时评
        'szxw' => '时政新闻', //时政新闻
        'gnxw' => '国内新闻',//国内新闻http://www.ncnews.com.cn/xwzx/gnxw/
    );

    //cate info  wrap wrap_gray pt20
    public function cate_list(){
        $domain = trim($_GET['domain']);
        if(!in_array($domain, array_keys($this->cate))){
            $this->redirect("Index/Index/index");
        }
        $cate_name = $this->cate[$domain];
        $m_catenews = new CatenewsModel();
        $cate_data  = $m_catenews->getCateInfoByName(array('news_cate' => $domain));

        $this -> assign('cate_name',$cate_name);
        $this -> assign('cate_data',$cate_data);
        $this->display('Index/cate');
    }

    //获取新闻详情
    public function get_news_detail(){
        $url = trim($_GET['url']);
        $id = trim($_GET['id']);
        $m_catenews = new CatenewsModel();
        $news_detail = $m_catenews -> getNewsDetailByUrl($id,$url);

        $this -> assign('banner',$news_detail['banner']);
        $this -> assign('main',$news_detail['main']);
        $this -> assign('news_cate_info',$news_detail['news_cate_info']);
        $this -> display('Index/news_detail');
    }

    //news.html  页面  右侧新闻列表详情数据
    public function index2(){
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

}
