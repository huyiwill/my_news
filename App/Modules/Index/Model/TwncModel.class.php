<?php

/**
 * Created by PhpStorm.
 * User: will5451
 * 图文南昌列表新闻
 */
class TwncModel extends Model{
    //list
    public function twnc_list($limit=1000){
        $twnc_list = M('news_twnc')->limit($limit)->order('unix_timestamp(title_time) desc')->order('RAND()')->select();
        return $twnc_list;
    }
    //
}