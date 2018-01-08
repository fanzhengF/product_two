<?php
/**清除缓存数据
*城市实体店缓存数据
*实体店所在区域缓存数据
*/
class DelCacheLogicModel
{
    
    /**创建Redis实例
    *return object $redis
    **/
    function newRedis()
    {
            try 
            {
                $redis = NewRedis::master();  
                $redis->ping(); 
            } 
            catch (Exception $e) 
            { 
                $redis=NULL;       
            }
            return $redis;    
        
    }
    
    /**取城市实体店数据Rediskey
    *定义的key在ApiShopCityModel中
    *return array $redisKey
    */
    function GetCityShopRedisKey()
    {
        $redisKey=array();
        $redisKey[]=ApiShopCityModel::ALL_CITY_DATA_REDISKEY;
        $redisKey[]=ApiShopCityModel::SEARCH_CITY_DATA_REDISKEY;
        return $redisKey;
           
    }
   /**
   清除redis实体店数据
   *实体店数据按city存取的
   */
    function DelRedisShopCache()
    {
        
        $keysPrefix=ApiShopModel::SHOP_SEARCHDATA_REDISKEYS.'_';
        return $this->FindAndDeleteRedisKey($keysPrefix);
    }
    /**
    *清除Redis实体店城市数据
    *切换城市页面的缓存
    **/
    function DelRedisCityShopCache()
    {
        $redis=$this->newRedis();
        $redisKeyArr=$this->GetCityShopRedisKey();
        if($redis)
        {
            foreach($redisKeyArr as $redisKey)
            {
                $ret=$redis->del($redisKey);//删除redis中的数据
            }
        }
        return $ret;  
    }
    
   /*清除首页推荐商家的缓存*/
    function DelShopCommandCache()
    {
        return $this->FindAndDeleteRedisKey('SHG:JIAJU:CacheCommandShop');
    }
    /**清除焦点图缓存*/
    function DelFocusMap()
    {
        return $this->DeleteOneRedisKey('SHG:JIAJU:FOCUSMAP');     
    }
    
    /**清除显示地图实体店的缓存
    *缓存的数据主要是实体店名称和地址经度、纬度
    */
    function DeleteShopMap()
    {
        return $this->FindAndDeleteRedisKey('SHG:JIAJU:CacheFileMap');   
    }
    /*搜索RedisKeyAnd删除Key
    *@param string $keysPrefix 要查找Key的前缀
    *@return array 查找出来的
    */
    function FindAndDeleteRedisKey($keysPrefix)
    {
        $redis=$this->newRedis();
        if($redis)
        {
            $redisArray=$redis->keys($keysPrefix.'*'); 
            if($redisArray)
            {
                foreach($redisArray as $redisKey)
                {
                    $ret=$redis->del($redisKey);//删除redis中的缓存 
                }   
            }
        }
        return $redisArray;       
    }
    
    /**删除Redis指定的Key
    *@param string $redisKey 要删除的Key
    *@return boolean
    */
    function DeleteOneRedisKey($RedisKey)
    {
        if(!empty($RedisKey))
        {
            $redis=$this->newRedis();
            if($redis)
            {
                $ret=$redis->del($RedisKey);//删除redis中的缓存 
                return $ret;   
            }   
        }
    }
        
} 

