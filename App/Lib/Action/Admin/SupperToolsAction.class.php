<?php
/**
 * 超级管理员工具
 */

class SupperToolsAction extends AdminAction{

    public function cache() {
        $data = RedisCacheModel::getCacheList();
        $this->assign("data", $data);
        $this->display();
    }

    public function delCache(){
        try {
            $key = I('get.cache');
            $cache_list = arrayColumn(RedisCacheModel::getCacheList(),'cache_key');

            if(in_array($key,$cache_list)){
                $fun = 'del' . RedisCacheModel::getCacheList()[$key]['cache_type'];
                RedisCacheModel::$fun($key);
            }else{
                throw new Exception("不存在的key", 1);
            }
            $this->success("清除成功");
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }






}