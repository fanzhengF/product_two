<?php
/**
 * 分类模型
 */

class CateModel extends Model{

    /**
     * 根据分类ID获取其下子分类
     *
     * <code>
     * // 获取一级分类
     * $cate = $this->fetchLevelPair();
     * // 获取二级分类
     * $cate = $this->fetchLevelPair(10010000);
     * // 获取三级分类
     * $cate = $this->fetchLevelPair(10010100);
     * </code>
     *
     * @param int $cate 分类ID
     * @return array|string
     */
    public function fetchLevelPair($cate = 0){
        $data = $this->fetchRawArray();
        $res = array();
        $level1 = str_pad(substr($cate, 0, 2), 6, '0', STR_PAD_RIGHT);
        $level2 = str_pad(substr($cate, 0, 4), 6, '0', STR_PAD_RIGHT);

        if ($cate == '0') {
            foreach ($data[0] as $id => $item) {
                $res[$id] = $item['name'];
            }
        } elseif (substr($cate, -4) == '0000') {
            if (isset($data[1][$level1])) {
                foreach ($data[1][$level1] as $id => $item) {
                    $res[$id] = $item['name'];
                }
            }
        } elseif (substr($cate, -2) == '00') {
            if (isset($data[2][$level1]) && isset($data[2][$level1][$level2])) {
                foreach ($data[2][$level1][$level2] as $id => $item) {
                    $res[$id] = $item['name'];
                }
            }
        }

        return $res;
    }

    /**
     * 根据ID反查父级
     *
     * <code>
     * Cate::model()->fetchParents(110100);
     * // 返回
     * array(
     *  '110000' => '装修设计',
     *  '110100' => '家装招标'
     * )
     * </code>
     *
     * @param int $id
     * @param string $type
     * @return array
     */
    public function fetchParents($id, $type = null,$ext = true){
        if(empty($id)) {
            return array();
        }
        $level[] = str_pad(substr($id, 0, 2), 6, '0', STR_PAD_RIGHT);
        $level[] = str_pad(substr($id, 0, 4), 6, '0', STR_PAD_RIGHT);
        $level[] = $id;
        $res = array();

        foreach ($level as $k) {
            $res[$k] = $this->fetchName($k);
            if ($id == $k) {
                break;
            }
        }

        if ($type == 'key') {
            return array_keys($res);
        }

        return $res;
    }

    /**
     * 从数据库中获取分类并组成不同数组，在每个请求中，只查询数据库一次
     *
     * @return array
     */
    public function fetchRawArray(){
        static $data1 = null, $data2 = null, $data3 = null, $all = null;
        $cateModel = M("Cate");
        if ($data1 === null || $data2 === null || $data3 === null || $all === null) {
            $tmp = $cateModel->order('id ASC')->select();
            foreach ($tmp as $item) {
                $all[$item['id']] = $item;
                $level1 = str_pad(substr($item['id'], 0, 2), 6, '0', STR_PAD_RIGHT);
                $level2 = str_pad(substr($item['id'], 0, 4), 6, '0', STR_PAD_RIGHT);
                if (substr($item['id'], -4) == '0000') {
                    $data1[$item['id']] = $item;
                } elseif (substr($item['id'], -2) == '00') {
                    $data2[$level1][$item['id']] = $item;
                } else {
                    $data3[$level1][$level2][$item['id']] = $item;
                }
            }
        }

        return array($data1, $data2, $data3, $all);
    }

    /**
     * 根据分类ID反查分类名
     *
     * @param string $connector 连接符号
     * @param int $id 分类ID号
     * @return varchar
     */
    public function getCatenamechar($id,$connector=' '){
        $arr = $this->fetchParents($id);
        foreach($arr as $val){
            $res.= $val.$connector;
        }
        return rtrim($res,$connector);
    }

    /**
     * 根据分类ID查找分类名字
     *
     * @param int $id   分类ID
     * @param bool true 返回类型 false 返回详细信息
     * @return string 如果没有返回NULL
     */
    public function fetchName($id , $ext=true){
        $data = $this->fetchRawArray();
        if ($ext)
        {
            return @$data[3][$id]['name'];
        }else{
            return @$data[3][$id];
        }

    }

    /**
     * 根据分类ID查找子类的id集合
     *
     * @param int $cate 分类ID
     * @return array
     */
    function getCateArray($cate){
        $data = $this->fetchRawArray();

        $res = array();
        $level1 = str_pad(substr($cate, 0, 2), 6, '0', STR_PAD_RIGHT);
        $level2 = str_pad(substr($cate, 0, 4), 6, '0', STR_PAD_RIGHT);

        if (substr($cate, -4) == '0000') {
            if (isset($data[2][$level1])) {
                foreach ($data[2][$level1] as $id => $item) {
                    $res[] = $id;
                    foreach($item as $key => $val){
                        $res[] = $key;
                    }
                }
            }

        }elseif (substr($cate, -2) == '00') {
            if (isset($data[2][$level1]) && isset($data[2][$level1][$level2])) {
                foreach ($data[2][$level1][$level2] as $id => $item) {
                    $res[] = $id;
                }
            }

        }else{
            $res[] = $cate;
        }

        return $res;
    }


    /**
     * 从数据库中获取二级分类数组，在每个请求中，只查询数据库一次
     *
     * @return array
     *
     * @author haiyan17@jiaju.com
     * @version 2015-7-31 for 7gz辅料商品接口
     */
    public function fetchRawArrayTwo(){
        static $data1 = null, $data2 = null, $data3 = null, $all = null;
        $cateModel = M("Cate");
        if ($data1 === null || $data2 === null || $data3 === null || $all === null) {
            $tmp = $cateModel->order('id ASC')->select();

            foreach ($tmp as $item) {
                $all[$item['id']] = $item;
                $level1 = str_pad(substr($item['id'], 0, 2), 6, '0', STR_PAD_RIGHT);
                $level2 = str_pad(substr($item['id'], 0, 4), 6, '0', STR_PAD_RIGHT);
                if (substr($item['id'], -4) == '0000') {
                    $data1[$item['id']] = $item;
                } elseif (substr($item['id'], -2) == '00') {
                    $data2[$item['id']] = $item['name'];
                } else {
                    $data3[$level1][$level2][$item['id']] = $item;
                }
            }
        }
        return $data2;
    }

    /**
     * 根据三级分类的id，查询分层信息
     *
     * @param array $thirdLevel
     *
     * @return array
     *          [
     *              thirdLevelId => [
     *                  f => [id, name], //第一层分类的
     *                  s => [id, name], //第二层
     *                  t => [id, name]  //第三层
     *              ]
     *          ]
     */
    public function queryLevel($thirdLevel){
        $id = $thirdLevel;
        $levelInfo = array();
        foreach ($thirdLevel as $val) {
            $first = substr($val, 0, 2) . '0000';
            $second = substr($val, 0, 4) . '00';
            $id[] = $first;
            $id[] = $second;
            $levelInfo[$val] = array('f' => $first, 's' => $second);
        }
        $rs = $this->where(array('id' => array('in', $id)))->select();
        $temp = arrayColumn($rs, 'name', 'id');
        $aim = array();
        foreach ($levelInfo as $key => $val) {
            $aim[$key] = array(
                'f' => array($val['f'], $temp[$val['f']]),
                's' => array($val['s'], $temp[$val['s']]),
                't' => array($key, $temp[$key]),
            );
        }
        return $aim;
    }

}