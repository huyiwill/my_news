<?php

/**
 * Created by PhpStorm.
 * User: will5451
 * 分类
 */
use \phpspider\core\selector;

class CatenewsModel extends Model{
    //list
    public function getCateInfoByName($where, $limit){
        $news_cate = M('news_cate')->where($where)->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        return $news_cate;
    }

    //随机抽取各个新闻
    public function getCateNewsByRand($limit){
        $news1 = M('news_cate')->where(array('news_cate' => 'jrnc'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news2 = M('news_cate')->where(array('news_cate' => 'ncsp'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news3 = M('news_cate')->where(array('news_cate' => 'szxw'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news4 = M('news_cate')->where(array('news_cate' => 'gnxw'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news  = array_merge($news1, $news2, $news3, $news4);
        foreach($news as $k => $v){
            switch(trim($v['news_cate'])){
                case 'jrnc':
                    $news[$k]['news_cate'] = '今日南昌';
                    break;
                case 'ncsp':
                    $news[$k]['news_cate'] = '南昌时评';
                    break;
                case 'szxw':
                    $news[$k]['news_cate'] = '时政新闻';
                    break;
                case 'gnxw':
                    $news[$k]['news_cate'] = '国内新闻';
                    break;
                default:
                    $news[$k]['news_cate'] = '其他';
                    break;
            }
        }
        return $news;
    }

    //every  news  section
    public function getEveryNewsSection($limit){
        $news['jrnc']['news_cate_data'] = M('news_cate')->where(array('news_cate' => 'jrnc'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news['ncsp']['news_cate_data'] = M('news_cate')->where(array('news_cate' => 'ncsp'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news['szxw']['news_cate_data'] = M('news_cate')->where(array('news_cate' => 'szxw'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news['gnxw']['news_cate_data'] = M('news_cate')->where(array('news_cate' => 'gnxw'))->order('unix_timestamp(news_time) desc')->limit($limit)->select();
        $news['jrnc']['news_cate']      = '今日南昌';
        $news['ncsp']['news_cate']      = '南昌时评';
        $news['szxw']['news_cate']      = '时政新闻';
        $news['gnxw']['news_cate']      = '国内新闻';
        return $news;
    }

    //根据url抓取新闻详细内容  只抓内容
    public function getNewsDetailByUrl($id, $url){
        $host           = pathinfo($url);
        $img_url_       = $host['dirname'];
        $news_cate_info = M('news_cate')->where(array('id' => $id))->find();
        $news_detail  = \phpspider\core\requests::get($url);
        $main   = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//p");
        $span        = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//span");
        $img_long = selector::select($news_detail, "//center//img");
        $banner = array();
        $mains = array();
        if(empty($main)){
            if(!empty($span) && is_array($span)){
                $mains = $span;
            }elseif(!empty($span) && !is_array($span)){
                $mains[] = $span;
            }
        }else{
            if(!is_array($main)){
                $mains[] = $main;
            }else{
                if(!empty($span) && is_array($span)){
                    $main = array_merge($span,$main);
                }elseif(!empty($span) && !is_array($span)){
                    $main[] = $span;
                }
                $mains = $main;
            }
        }
        $mainss = array();
        if(is_array($mains) && !empty($mains)){
            foreach($mains as $k => $val){
                $img = selector::select($val, "//img");
                if(($img && !empty($img))){
                    if(substr($img, 0, 1) == 'h'){
                        $mains[$k] = trim($img);
                    }else{
                        $mains[$k] = $img_url_ . ltrim($img, '.');
                    }
                    $banner[] = $mains[$k];
                }else{
                    $mains[$k] = strip_tags(trim($val));
                }
            }
            foreach($mains as $k => $val){
                $mainss[] = $val;
                if(!empty($img_long)){
                    if(is_array($img_long)){
                        if(preg_match('/(jpg|png)/i',$img_long[$k])){
                            if(substr(trim($img_long[$k]), 0, 1) == 'h'){
                                $mainss[] = trim($img_long[$k]);
                            }else{
                                $mainss[] = $img_url_ . ltrim($img_long[$k], '.');
                            }
                        }
                    }else{
                        if(substr(trim($img_long), 0, 1) == 'h'){
                            $mainss[] = trim($img_long);
                        }else{
                            $mainss[] = $img_url_ . ltrim($img_long, '.');
                        }
                    }

                }
            }
        }

        $arr = array(
            'main'           => $mainss,
            'news_cate_info' => $news_cate_info,
            'banner'         => isset($banner[0]) && !empty($banner) ? $banner[0] : '',
        );
        return $arr;
    }

    //抓取类别新闻详情
    public function getCateDetailByUrl($new_cate, $id, $url){
        $host           = pathinfo($url);
        $img_url_       = $host['dirname'];
        $news_cate_info = M('news_cate')->where(array('id' => $id))->find();
        $news_title     = $news_cate_info['news_title'];
        $news_time      = $news_cate_info['news_time'];
        switch($new_cate){
            case 'jrnc':
                $news_detail  = \phpspider\core\requests::get($url);
                $main   = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//p");
                $span        = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//span");
                $img_long = selector::select($news_detail, "//center//img");
                $banner = array();
                $mains = array();
                if(empty($main)){
                    if(!empty($span) && is_array($span)){
                        $mains = $span;
                    }elseif(!empty($span) && !is_array($span)){
                        $mains[] = $span;
                    }
                }else{
                    if(!is_array($main)){
                        $mains[] = $main;
                    }else{
                        if(!empty($span) && is_array($span)){
                            $main = array_merge($span,$main);
                        }elseif(!empty($span) && !is_array($span)){
                            $main[] = $span;
                        }
                        $mains = $main;
                    }
                }
                $mainss = array();
                if(is_array($mains) && !empty($mains)){
                    foreach($mains as $k => $val){
                        $img = selector::select($val, "//img");
                        if(($img && !empty($img))){
                            if(substr($img, 0, 1) == 'h'){
                                $mains[$k] = trim($img);
                            }else{
                                $mains[$k] = $img_url_ . ltrim($img, '.');
                            }
                            $banner[] = $mains[$k];
                        }else{
                            $mains[$k] = strip_tags(trim($val));
                        }
                    }
                    foreach($mains as $k => $val){
                        $mainss[] = $val;
                        if(!empty($img_long)){
                            if(is_array($img_long)){
                                if(preg_match('/(jpg|png)/i',$img_long[$k])){
                                    if(substr(trim($img_long[$k]), 0, 1) == 'h'){
                                        $mainss[] = trim($img_long[$k]);
                                    }else{
                                        $mainss[] = $img_url_ . ltrim($img_long[$k], '.');
                                    }
                                }
                            }else{
                                if(substr(trim($img_long), 0, 1) == 'h'){
                                    $mainss[] = trim($img_long);
                                }else{
                                    $mainss[] = $img_url_ . ltrim($img_long, '.');
                                }
                            }

                        }
                    }
                }

                $arr = array(
                    'main'           => $mainss,
                    'news_cate_info' => $news_cate_info,
                    'banner'         => isset($banner[0]) && !empty($banner) ? $banner[0] : '',
                );
                return $arr;
                break;
            case 'ncsp':
                $news_detail  = \phpspider\core\requests::get($url);
                $main   = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//p");
                $span        = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//span");
                $img_long = selector::select($news_detail, "//center//img");
                $banner = array();
                $mains = array();
                if(empty($main)){
                    if(!empty($span) && is_array($span)){
                        $mains = $span;
                    }elseif(!empty($span) && !is_array($span)){
                        $mains[] = $span;
                    }
                }else{
                    if(!is_array($main)){
                        $mains[] = $main;
                    }else{
                        if(!empty($span) && is_array($span)){
                            $main = array_merge($span,$main);
                        }elseif(!empty($span) && !is_array($span)){
                            $main[] = $span;
                        }
                        $mains = $main;
                    }
                }
                $mainss = array();
                if(is_array($mains) && !empty($mains)){
                    foreach($mains as $k => $val){
                        $img = selector::select($val, "//img");
                        if(($img && !empty($img))){
                            if(substr($img, 0, 1) == 'h'){
                                $mains[$k] = trim($img);
                            }else{
                                $mains[$k] = $img_url_ . ltrim($img, '.');
                            }
                            $banner[] = $mains[$k];
                        }else{
                            $mains[$k] = strip_tags(trim($val));
                        }
                    }
                    foreach($mains as $k => $val){
                        $mainss[] = $val;
                        if(!empty($img_long)){
                            if(is_array($img_long)){
                                if(preg_match('/(jpg|png)/i',$img_long[$k])){
                                    if(substr(trim($img_long[$k]), 0, 1) == 'h'){
                                        $mainss[] = trim($img_long[$k]);
                                    }else{
                                        $mainss[] = $img_url_ . ltrim($img_long[$k], '.');
                                    }
                                }
                            }else{
                                if(substr(trim($img_long), 0, 1) == 'h'){
                                    $mainss[] = trim($img_long);
                                }else{
                                    $mainss[] = $img_url_ . ltrim($img_long, '.');
                                }
                            }

                        }
                    }
                }

                $arr = array(
                    'main'           => $mainss,
                    'news_cate_info' => $news_cate_info,
                    'banner'         => isset($banner[0]) && !empty($banner) ? $banner[0] : '',
                );
                return $arr;
                break;
            case 'szxw':
                $news_detail  = \phpspider\core\requests::get($url);
                $main   = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//p");
                $span        = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//span");
                $img_long = selector::select($news_detail, "//center//img");
                $banner = array();
                $mains = array();
                if(empty($main)){
                    if(!empty($span) && is_array($span)){
                        $mains = $span;
                    }elseif(!empty($span) && !is_array($span)){
                        $mains[] = $span;
                    }
                }else{
                    if(!is_array($main)){
                        $mains[] = $main;
                    }else{
                        if(!empty($span) && is_array($span)){
                            $main = array_merge($span,$main);
                        }elseif(!empty($span) && !is_array($span)){
                            $main[] = $span;
                        }
                        $mains = $main;
                    }
                }
                $mainss = array();
                if(is_array($mains) && !empty($mains)){
                    foreach($mains as $k => $val){
                        $img = selector::select($val, "//img");
                        if(($img && !empty($img))){
                            if(substr($img, 0, 1) == 'h'){
                                $mains[$k] = trim($img);
                            }else{
                                $mains[$k] = $img_url_ . ltrim($img, '.');
                            }
                            $banner[] = $mains[$k];
                        }else{
                            $mains[$k] = strip_tags(trim($val));
                        }
                    }
                    foreach($mains as $k => $val){
                        $mainss[] = $val;
                        if(!empty($img_long)){
                            if(is_array($img_long)){
                                if(preg_match('/(jpg|png)/i',$img_long[$k])){
                                    if(substr(trim($img_long[$k]), 0, 1) == 'h'){
                                        $mainss[] = trim($img_long[$k]);
                                    }else{
                                        $mainss[] = $img_url_ . ltrim($img_long[$k], '.');
                                    }
                                }
                            }else{
                                if(substr(trim($img_long), 0, 1) == 'h'){
                                    $mainss[] = trim($img_long);
                                }else{
                                    $mainss[] = $img_url_ . ltrim($img_long, '.');
                                }
                            }

                        }
                    }
                }

                $arr = array(
                    'main'           => $mainss,
                    'news_cate_info' => $news_cate_info,
                    'banner'         => isset($banner[0]) && !empty($banner) ? $banner[0] : '',
                );
                return $arr;
                break;
            case 'gnxw':
                $news_detail  = \phpspider\core\requests::get($url);
                $main   = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//p");
                $span        = selector::select($news_detail, "//div[contains(@class,'TRS_Editor')]//span");
                $img_long = selector::select($news_detail, "//center//img");
                $banner = array();
                $mains = array();
                if(empty($main)){
                    if(!empty($span) && is_array($span)){
                        $mains = $span;
                    }elseif(!empty($span) && !is_array($span)){
                        $mains[] = $span;
                    }
                }else{
                    if(!is_array($main)){
                        $mains[] = $main;
                    }else{
                        if(!empty($span) && is_array($span)){
                            $main = array_merge($span,$main);
                        }elseif(!empty($span) && !is_array($span)){
                            $main[] = $span;
                        }
                        $mains = $main;
                    }
                }
                $mainss = array();
                if(is_array($mains) && !empty($mains)){
                    foreach($mains as $k => $val){
                        $img = selector::select($val, "//img");
                        if(($img && !empty($img))){
                            if(substr($img, 0, 1) == 'h'){
                                $mains[$k] = trim($img);
                            }else{
                                $mains[$k] = $img_url_ . ltrim($img, '.');
                            }
                            $banner[] = $mains[$k];
                        }else{
                            $mains[$k] = strip_tags(trim($val));
                        }
                    }
                    foreach($mains as $k => $val){
                        $mainss[] = $val;
                        if(!empty($img_long)){
                            if(is_array($img_long)){
                                if(preg_match('/(jpg|png)/i',$img_long[$k])){
                                    if(substr(trim($img_long[$k]), 0, 1) == 'h'){
                                        $mainss[] = trim($img_long[$k]);
                                    }else{
                                        $mainss[] = $img_url_ . ltrim($img_long[$k], '.');
                                    }
                                }
                            }else{
                                if(substr(trim($img_long), 0, 1) == 'h'){
                                    $mainss[] = trim($img_long);
                                }else{
                                    $mainss[] = $img_url_ . ltrim($img_long, '.');
                                }
                            }

                        }
                    }
                }

                $arr = array(
                    'main'           => $mainss,
                    'news_cate_info' => $news_cate_info,
                    'banner'         => isset($banner[0]) && !empty($banner) ? $banner[0] : '',
                );
                return $arr;
                break;
        }
    }

    //计算中文长度
    public function mb_str_len($str){
        if(function_exists('mb_strlen')){
            return mb_strlen($str, 'utf-8');
        }else{
            preg_match_all("/./u", $str, $ar);
            return count($ar[0]);
        }
    }
}