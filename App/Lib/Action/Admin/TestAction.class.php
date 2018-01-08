<?php
// 本类由系统自动生成，仅供测试用途
class TestAction extends AdminAction {

    public function index() {
		//phpinfo();die;
        var_dump($_SERVER);
    }
	public function phpin(){
		var_dump($_SERVER);
	}

	/*
	 *
	 *
	 *  if (isset($_GET['daochu'])) {
            print_r($list);exit;
            $title = array(
                array(
                    'title'=>'会议名称'
                ),
                array(
                    'title'=>'用户'
                ),
                array(
                    'title'=>'会议类型'
                ),
                array(
                    'title'=>'会议等级'
                ),
                array(
                    'title'=>'举办机构'
                ),
                array(
                    'title'=>'参会人员类型'
                ),
                array(
                    'title'=>'会议时间'
                )
            );
            $this->commonExportCvs($list,$title);
        }
	 *
	 * */
    public function db()
    {
        $pw = $this->_request('pw');
        $write = $this->_request('write');//不可写库
        $down = false;
        //$write = false;
        if ($pw != md5(date('Y-m-d'))) {
            echo 'key error!';
            exit();
        }
		$sql = $_POST['sql'];
        //$sql = str_replace("\\",' ',$_POST['sql']);
        if (!$down) {
            header("Content-type:text/html;charset=utf-8");
            echo '请输入SQL';
            echo '<form name="DB" action="" method="post">
    		sql: <textarea name="sql" rows="26" cols="90" >' . $sql . '</textarea>
    		<br />
    		<input type="submit" value="search" name="submit" />
    		<input type="hidden" value="' . $pw . '" name="pw" />
    	</form>
		';
        }
        if (IS_POST) {
            $Model = new Model();
            if ($write) {
                $res = $Model->db(1)->execute($sql);
                if (!$res) {
                    var_dump($Model->getDbError());
                }
                var_dump($res);
            } else {
                $res = $Model->query($sql);
                if ($res == false) {
                    var_dump($Model->getDbError());
                }

                foreach ($res as $key => $value) {
                    if($key==0){
                        $result.="<tr>";
                        foreach ($value as $k => $v) {
                            $result.="<td>" . $k . "</td>";
                        }
                        $result.="</tr>";
                    }
                    $result.="<tr>";
                    foreach ($value as $k => $v) {
                        $result.="<td>" . $v . "</td>";
                    }
                    $result.="</tr>";
                }
                echo "  <style type=\"text/css\">table{border-collapse: collapse;border: none;width: 200px;}td{border: solid #000 1px;}</style><table border=0 cellpadding=2 cellspacing=0>" . $result . "</table>";
            }
        }
    }
}