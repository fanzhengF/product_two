<?php
/**
 * redis缓存模型
 */

class RedisCacheModel extends Model{

    //供应商所属地方站列表
    const LOCAL_STATION_LIST        = 'shg_local_station_list';

    //首页推荐商家缓存
    const DEL_SHOP_COMMAND_CACHE    = 'DelShopCommandCache';

    //搜索商家全部商家缓存
    const DEL_REDIS_SHOP_CACHE      = 'DelRedisShopCache';

    //切换城市的缓存
    const DEL_REDIS_CITY_SHOP_CACHE = 'DelRedisCityShopCache';

    //焦点图
    const DEL_FOCUSMAP              = 'DelFocusMap';

    //显示地图商家数据
    const DELETE_SHOPMAP            = 'DeleteShopMap';

    /**
     * @var 获取列表
     */
    public static function getCacheList(){
        $data = array(
            self::LOCAL_STATION_LIST =>array(
                'cache_key'  => self::LOCAL_STATION_LIST,//缓存key
                'cache_time' => 0,//缓存时间
                'cache_type' => 'Redis',//缓存类型
                'describe'   => '缓存供应商所属地方站列表',//缓存描述
                ),
            self::DEL_SHOP_COMMAND_CACHE =>array(
                'cache_key'  => self::DEL_SHOP_COMMAND_CACHE,//缓存key
                'cache_time' => 0,//缓存时间
                'cache_type' => 'RedisFun',//缓存类型
                'describe'   => '首页推荐商家缓存',//缓存描述
                ),
            self::DEL_REDIS_SHOP_CACHE =>array(
                'cache_key'  => self::DEL_REDIS_SHOP_CACHE,//缓存key
                'cache_time' => 0,//缓存时间
                'cache_type' => 'RedisFun',//缓存类型
                'describe'   => '搜索商家全部商家缓存',//缓存描述
                ),
            self::DEL_REDIS_CITY_SHOP_CACHE =>array(
                'cache_key'  => self::DEL_REDIS_CITY_SHOP_CACHE,//缓存key
                'cache_time' => 0,//缓存时间
                'cache_type' => 'RedisFun',//缓存类型
                'describe'   => '切换城市的缓存',//缓存描述
                ),
            self::DEL_FOCUSMAP =>array(
                'cache_key'  => self::DEL_FOCUSMAP,//缓存key
                'cache_time' => 0,//缓存时间
                'cache_type' => 'RedisFun',//缓存类型
                'describe'   => '焦点图',//缓存描述
                ),
            self::DELETE_SHOPMAP =>array(
                'cache_key'  => self::DELETE_SHOPMAP,//缓存key
                'cache_time' => 0,//缓存时间
                'cache_type' => 'RedisFun',//缓存类型
                'describe'   => '显示地图商家数据',//缓存描述
                ),
            );

        return $data;
    }

    /**
     * @var 调用删除缓存函数
     */
    public static function delRedisFun($fun){
        $delCacheObj = D('DelCache','LogicModel');
        $delCacheObj->$fun();
    }

    /**
     * @var 删除单键缓存
     */
    public static function delRedis($key){
        $redis = NewRedis::master();
        $redis->ping();

        $redis->del($key);
    }

    /**
     * @var 设置redis缓存
     */
    public static function setKey($key,$data=array(),$time=0){
        $redis = NewRedis::master();
        $result = $redis->set($key,jsonEncode($data),$time);
    }

    /**
     * @var 获取redis缓存
     */
    public static function getKey($key){
        $redis = NewRedis::slave();
        $result = $redis->get($key);
        return $result;
    }
}