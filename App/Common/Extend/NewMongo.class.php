<?php

class NewMongo{
		private static $MongoObj = null;
		private static $collection = null;
		
		private function __construct(){
			
		}
		
		
		public static function init($table){
			if(empty(self::$MongoObj)){
				self::$MongoObj = new NewMongo;
			}
			$mongo = new Mongo("mongodb://".$_SERVER["SINASRV_MONGO_DB_USER"] . ':' . $_SERVER["SINASRV_MONGO_DB_PASS"] . '@' .
                $_SERVER["SINASRV_MONGO_DB_HOST"] . ':' .
                $_SERVER["SINASRV_MONGO_DB_PORT"] . '/' . $_SERVER["SINASRV_MONGO_DB_NAME"]);
			$db = $mongo->$_SERVER["SINASRV_MONGO_DB_NAME"];
			self::$collection = $db->$table;
			return self::$MongoObj;
		}
		
		/**
		  * http://us.php.net/manual/en/mongocollection.insert.php
		  *	MongoCollection::insert(array $a,array $options)
		  *	array $a 要插入的数组
		  *	array $options 选项
		  *	safe 是否返回操作结果信息
		  *	fsync 是否直接插入到物理硬盘
		 */
		public function insert($title='default' , $message='default'){
			$data = array('title'=>$title,'message'=>$message);
			self::$collection->insert($data);
		}
		
		
		/**
		 * http://us.php.net/manual/en/mongocollection.remove.php
		 *	MongoCollection::remove(array $criteria,array $options)
		 *	array $criteria  条件
		 *	array $options 选项
		 *	safe 是否返回操作结果
		 *	fsync 是否是直接影响到物理硬盘
		 *	justOne 是否只影响一条记录 
		 */
		public function remove( $id ){
			$id = new MongoId($id);
			return self::$collection->remove( array('_id'=> $id) , array('safe'=>true,'justOne'=>true) );
		}
		
		/**
		  * http://us.php.net/manual/en/mongocollection.update.php
		  *	MongoCollection::update(array $criceria,array $newobj,array $options)
		  *	array $criteria  条件
		  *	array $newobj 要更新的内容
		  *	array $options 选项
		  *	safe 是否返回操作结果
		  *	fsync 是否是直接影响到物理硬盘
		  *	upsert 是否没有匹配数据就添加一条新的
		  *	multiple 是否影响所有符合条件的记录，默认只影响一条
		 */
		public function update( $id, $title, $message ){
			$id = new MongoId($id);
			return self::$collection->update(array('_id'=>$id),array('title'=>222,'message'=>333) );
			
		}
		
		/**
		  * http://us.php.net/manual/en/mongocollection.findone.php
		  *	arrayMongoCollection::findOne(array $query,array $fields)
		  *	array $query 条件
		  *	array $fields 要获得的字段
		 */
		public function find( $id ){
			$id = new MongoId($id);
			$where = array('_id'=>$id);
			$result = self::$collection->findOne($where);
			return $result;
		}
		
		//根据where条件获取条数
		public function getCount($where){
			//print_r($where);die;
			return self::$collection->count($where);
		}

        /**根据where条件分页获取结果集
		  *array $where 查询条件
		  *int $start 当前页码
		  *int $page_size 每页显示的条数
		  */
		public function getAll($where,$start,$page_size,$sort){
			self::$collection->ensureIndex($sort);
			$cursor = self::$collection->find($where)->limit($page_size);
			foreach ($cursor as $id => $value) {
				$data[] = $value;
			}
			return $data;
		}
}