<?php
use \phpspider\core\requests;
use \phpspider\core\selector;
/**
 * Created by PhpStorm.
 * User: huyi
 * Date: 2018/1/5
 * Time: 9:22
 */
class IndexModel extends Model{
    //首页
    public function index($url){
        $con    = file_get_contents($url);
        //p(htmlspecialchars($con));
        //匹配头条
        $pattern = '/<div class=\"topnewslist\".*?>.*?<\/div>/ismu';
        //match banner  新闻
        $pattern = '/<ul class=\"headnewslist\">(.*?)<\/ul>/ismu';
        preg_match($pattern, $con, $arr);
        //p($arr);
        //match images
        $pat1 = '/<a href=\"(.*?)\" target=\"_blank\" class=\"pic\">(.*?)<\/a>/iu';
        preg_match_all($pat1, $arr[0], $images);
        $img_href = $images[1];   //1
        //imgages  url  address
        $imgs = array();
        foreach($images[2] as $img_v){
            $pat = '/<img .*? data-original=\"(.*?)\".*?>/iu';
            $pat = '/http:\/\/(.*)(\.jpg|\.png)\"/isu';
            preg_match_all($pat, $img_v, $img);
            $imgs[] = rtrim($img[0][0], '"');   //2
        }

        $pat2 = '/<h3><a href=\"(.*?)\" target=\"_blank\">(.*?)<\/a><\/h3>/';
        preg_match_all($pat2, $arr[0], $titles);
        $title_href = array();

        foreach($titles[1] as $title_v){
            $sub = substr($title_v, 0, 1);
            if($sub == 'h'){
                $title_href[] = $title_v;      //3
            }elseif($sub == '.'){
                $title_v      = ltrim($title_v, '.');
                $title_href[] = $url . $title_v;      //3
            }else{
                $title_v      = ltrim($title_v, '.');
                $title_href[] = $url . $title_v;      //3
            }
        }
        $title = $titles[2];                   //4

        $pat3 = '/<p>(.*?)<\/p>/';
        preg_match_all($pat3, $arr[0], $desc);
        $desc = $desc[1];                       //5

        $data = array();
        //count($title)
        for($i = 1; $i < 4; $i++){
            $data[$i]['img_href']   = $img_href[$i];
            $data[$i]['imgs']       = $imgs[$i];
            $data[$i]['title_href'] = $title_href[$i];
            $data[$i]['title']      = $title[$i];
            $data[$i]['desc']       = $desc[$i];
            $data[$i]['twnc']       = "图文南昌";
            $time                   = pathinfo($img_href[$i])['filename'];
            $time_arr               = explode('_', $time);
            $data[$i]['time']       = date("Y-m-d", strtotime(ltrim($time_arr[0], 't')));
            $data[$i]['idss']       = "message" . $i;
        }

        $time     = pathinfo($img_href[0])['filename'];
        $time_arr = explode('_', $time);
        $one_data = array(
            'img_href'   => $img_href[0],
            'imgs'       => $imgs[0],
            'title_href' => $title_href[0],
            'title'      => $title[0],
            'desc'       => $desc[0],
            'twnc'       => "图文南昌",
            'time'       => date("Y-m-d", strtotime(ltrim($time_arr[0], 't')))
        );
        $arr = array(
            'one_data' => $one_data,
            'data'     => $data
        );
        return $arr;
    }

    //本网原创 首页
    public function bwzg_rd($url){
        $recommend_main = requests::get($url);
        $host = pathinfo($url);
        //pr($host);
        $img_url_ = $host['dirname'];

        $news_list = selector::select($recommend_main,"//ul[contains(@class,'listPicBox clearfix')]");
        $news_li = selector::select($news_list,"//li");
        $lis = array();

        foreach($news_li as $k => $li){
            $img = selector::select($li,"//img");

            if(count($lis) == 3){
                continue;
            }
            if(empty($img)){
                unset($news_li[$k]);
            }else{
                $p = '/<h3><a[^<>]+href *\= *[\"\']?([^\'\"]+).*?<\/h3>/i';
                $li_url = selector::select($li,$p,'regex');
                $li_imgs_url = selector::select($li,"//img");
               if(substr($li_url,0,1) == 'h'){
                   $lis[$k]['li_url'] = trim($li_url);
               }else{
                   $lis[$k]['li_url'] = $img_url_.ltrim($li_url,'.');
               }
                //$lis[$k]['li_imgs_url'] = "http://www.songtreehy.com/www/songtree/Uploads/mid/pig.jpg";
               if(substr($li_imgs_url,0,1) == 'h'){
                   $lis[$k]['li_imgs_url'] = trim($li_imgs_url);
               }else{
                   $lis[$k]['li_imgs_url'] = $img_url_.ltrim($li_imgs_url,'.');
               }
                $lis[$k]['li_h3'] = selector::select($li,"//h3//a");
                $lis[$k]['li_h5'] = selector::select($li,"//h5");
                $lis[$k]['li_h6_span_time'] = strip_tags(selector::select($li,"//h6"));
            }

        }

        $lis = array_merge($lis);
        $one_data = array(
            'img_href'   => $lis[0]['li_url'],
            'imgs'       => $lis[0]['li_imgs_url'],
            'title_href' => $lis[0]['li_url'],
            'title'      => $lis[0]['li_h3'],
            'desc'       => $lis[0]['li_h5'],
            'twnc'       => "本网原创",
            'time'       => $lis[0]['li_h6_span_time']
        );

        $data = array();
        for($i = 1; $i < count($lis); $i++){
            $data[$i]['img_href']   = $lis[$i]['li_url'];
            $data[$i]['imgs']       = $lis[$i]['li_imgs_url'];
            $data[$i]['title_href'] = $lis[$i]['li_url'];
            $data[$i]['title']      = $lis[$i]['li_h3'];
            $data[$i]['desc']       = $lis[$i]['li_h5'];
            $data[$i]['twnc']       = "本网原创";
            $data[$i]['time']       = $lis[$i]['li_h6_span_time'];
            $data[$i]['idss']       = "message" . uniqid($i);
        }
        $arr = array(
            'one_data' => $one_data,
            'data'     => $data
        );

        return $arr;
    }

    //本网原创 详细news
    public function bwzg_rd_detail($news_content_url){
        $host = pathinfo($news_content_url);
        $img_url_ = $host['dirname'];

        $news_detail      = requests::get($news_content_url);
        $news_title       =  selector::select($news_detail,"//div[contains(@class,'headline')]//h1");
        $news_time        = selector::select($news_detail,"//div[contains(@class,'headline')]//span");
        //$news_content     = selector::select($news_detail, "//div[contains(@id,'picWrap')]//p");
        $news_content     = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//p");
        //$news_content     = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]");
        //TRS_Editor  TRS_Editor
        $img_urls = array();
        $news_main = array();
        $news_tag = array();

        if(empty($news_content)){
            return $this -> bwzg_rd_detail2($news_content_url);
        }

        foreach($news_content as $k => $new){
            $h = selector::select($new,"//img");
            if(!empty($h)){
                if(substr($h,0,1) == 'h'){
                    $img_urls[] = trim($h);
                }else{
                    $img_urls[] = $img_url_.ltrim($h,'.');
                }
            }elseif($this->mb_str_len(trim(strip_tags($new))) <=20){
                $news_tag[] = trim(strip_tags($new));
            }else{
                $news_main[] = trim(strip_tags($new));
            }
        }

        $arr = array(
            'one_news_title' => $news_title,
            'one_news_time' => $news_time,
            'one_news_imgs_tags' => $img_urls,
            'news_tag'          => $news_tag,
            //'content_title'     => $news_title,
            'content_main'      => $news_main,
            'banner'            => $img_urls[0]
        );
        return $arr;
    }

    //计算中文长度
    public function mb_str_len($str){
        if(function_exists('mb_strlen')){
            return mb_strlen($str,'utf-8');
        }
        else {
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }

    //
    public function bwzg_rd_detail2($news_content_url){
        $host = pathinfo($news_content_url);
        $img_url_ = $host['dirname'];

        $news_detail      = requests::get($news_content_url);
        $news_content     = selector::select($news_detail, "//div[contains(@class,'newsread')]");

        //p($news_content);
        /****类别内容****/
        //$news_top = requests::get($img_url_);
        //$pos = strrpos($img_url_,'/');
        //$domain_url_  =  substr($img_url_,0,$pos);
        //$news_cate_content = requests::get($domain_url_);
        /****类别内容****/

        //xpath
        $one_news_title = selector::select($news_detail, "//div[contains(@class,'read')]//h3"); //1
        $one_news_top   = selector::select($news_detail, "//div[contains(@class,'newsinfo')]");
        $one_news_top   = selector::remove($one_news_top, "//i[contains(@id,'fontsize')]");
        $one_news_time  = trim($one_news_top);      //2

        $one_news_imgs_tags = selector::select($news_content, "//div[contains(@align,'center')]");
        $pattern            = '/<img[^>]*.?>/i';
        if(!empty($one_news_imgs_tags)){
            foreach($one_news_imgs_tags as $k => $tag){
                if(preg_match($pattern, $tag)){
                    $one_news_imgs_tags[$k] = $img_url_.ltrim(selector::select($tag, "//img"),'.'); //3
                }
            }
        }

        $p = "/<img[^>]*src[=\"\'\s]+([^\"\']*)[\"\']?[^>]*>((?:(?!<img\b)[\s\S])*)/i";
        //$news_content= '<div align="left">34534<p>sdfas</p></div>';
        $p                = '/<div[^>]*?align=\"left\"*?>(.*?)(<p>(.*?)<\/p>)*<\/div>/ism';
        $p                = '/<div[^>]*?align=\"left\"*?>(.*?)(<p[^>]*?>(.*?)<\/p>.*?)?<\/div>/ism';
        //$one_news_content = selector::select($news_content, "//div[contains(@align,'left')]");
        $content          = selector::select($news_content, $p, 'regex');
        $content_title    = isset($content[0]) ? trim($content[0]) : '';
        $content_main     = isset($content[2]) ? trim($content[2]) : '';

        //获取banner
        $banner = array();
        foreach($one_news_imgs_tags as  $imgs_tag){
            $one = substr($imgs_tag,0,1);
            if($one == 'h'){
                $banner[] = $imgs_tag;
            }
        }

        $arr = array(
            'one_news_title' => $one_news_title,
            'one_news_time' => $one_news_time,
            'one_news_imgs_tags' => $one_news_imgs_tags,
            'content_title'     => $content_title,
            'content_main'      => $content_main,
            'banner'            => $banner[0]
        );
        return $arr;
    }
}