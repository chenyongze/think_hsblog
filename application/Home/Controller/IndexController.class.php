<?php
namespace Home\Controller;
use Common\Controller\HomeBaseController;
class IndexController extends HomeBaseController {
    private $db_Articles,$db_Article_category,$db_Tag,$db_Article_tag;
    public function __construct(){
        parent::__construct();
        $this->db_Tag = D ('Tag');
        $this->db_Articles = D ('Articles');
        $this->db_Article_tag = D ('Article_tag');
        $this->db_Article_category = D ('Article_category');

    }
    public function index(){
        //默认读取全部
        if(!isset($_GET['tagid']) &&  !isset($_GET['caid']) ){
            $show_array['a_state'] = array('eq',C('ARTICLE_NORMAL'));
            $count = $this->db_Articles->where($show_array)->count();
            $Page       =  new \Think\Page($count,4);
            $show       =  $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
            $article_list = $this->db_Articles->where($show_array)->order(array('a_id'=>'desc'))
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
        }
        //标签目录
        $tag_id = I('get.tagid');
        if(!empty($tag_id) && isset($tag_id) ){
            $tag_info = $this->db_Tag->getByHt_id($tag_id);
            if(empty($tag_info)){
                $this->error("无法获取原标签",U('Home/Index/index'),'',1);
            }
            $where['at.at_tag_id'] = $tag_id;
            $where['a.a_state'] = array('eq',C('ARTICLE_NORMAL'));
            $count = $this->db_Article_tag
                ->alias('at')
                ->join('__ARTICLES__ a ON at.at_article_id = a.a_id')
                ->where($where)
                ->count();
            $Page       =  new \Think\Page($count,4);
            $show       =  $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
            $article_list = $this->db_Article_tag
                ->alias('at')
                ->join('__ARTICLES__ a ON at.at_article_id = a.a_id')
                ->where($where)
                ->order('a.a_add_time desc')
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
            $this->assign("tag_info",$tag_info);
        }
        //分类目录
        $ac_id = I('get.caid');
        if(!empty($ac_id) && isset($ac_id) ){
            $category_info = $this->db_Article_category->getByAc_id($ac_id);
            if(empty($category_info)){
                $this->error("无法获取原分类",U('Home/Index/index'),'',1);
            }
            $where['ac.ac_id'] = $ac_id;
            $where['a.a_state'] = array('eq',C('ARTICLE_NORMAL'));
            $count = $this->db_Article_category
                ->alias('ac')
                ->join('__ARTICLES__ a ON ac.ac_id = a.a_category_id')
                ->where($where)
                ->count();
            $Page       =  new \Think\Page($count,4);
            $show       =  $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出
            $article_list = $this->db_Article_category
                ->alias('ac')
                ->join('__ARTICLES__ a ON ac.ac_id = a.a_category_id')
                ->where($where)
                ->order('a.a_add_time desc')
                ->limit($Page->firstRow.','.$Page->listRows)
                ->select();
            $this->assign("category_info",$category_info);
        }

        if(!empty($article_list) && is_array($article_list)){
            foreach ($article_list as $k => $v){
                //分类
                $ac_array['ac_id'] = array('eq',$v['a_category_id']);
                $category_info = $this->db_Article_category->where($ac_array)->find();
                $article_list[$k]['category_info'] =  $category_info;
                //标签
                $tag_array['at_article_id'] = $v['a_id'];
                $tag_id_list = $this->db_Article_tag->where($tag_array)->select();
                $tag_id_str = '';
                foreach ($tag_id_list as $key => $val){
                    $tag_id_str .= $val['at_tag_id'].',';
                }
                $tag_id_str =  rtrim($tag_id_str, ',');

                $tag_list_array['ht_id'] = array('in',$tag_id_str);
                $tag_list = $this->db_Tag->where($tag_list_array)->select();
                $article_list[$k]['tag_info'] =  $tag_list;
            }
        }

        $this->assign("article_list",$article_list);
        $this->display();
    }
    public function article(){
        $a_id = I('get.id');
        if(empty($a_id)){
            $this->error("无法获取原文章",U('Home/Index/index'),'',1);
        }
        $article_info = $this->db_Articles->getByA_id($a_id);
        if(empty($article_info)){
            $this->error("无法获取原文章",U('Home/Index/index'),'',1);
        }
        $category_list = $this->db_Article_category->select();
        $this->assign("category_list",$category_list);
        $this->assign("article_info",$article_info);
        $this->display();
    }
    
}