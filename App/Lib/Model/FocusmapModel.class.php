<?php

/**
 * 焦点图模型
 */

class FocusmapModel extends Model{

    /**
     * @var 自动验证字段
     */
    protected $_validate=array(
            array("title","require","图片名字不能为空"),
            array("url","url","请填写正确的url地址"),
            array("description","require","描述不能为空"),
    );


    /**
     * @var 查找
     */
    public function getOne(){
        $fields = 'id,title,url,src,description';
        $list   = $this->field($fields)
                       ->order("id desc")
                       ->find();

        return $list;
    }



}