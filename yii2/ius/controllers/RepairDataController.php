<?php


/**
 * 迁移数据入库
 */


namespace app\controllers;

use Yii;


class RepairDataController extends \app\base\Controller{



    /**
     * 实例
    */
    public static $_instance = null;

    /**
     *
     * 数据库资源
     *
    */
    public static function  getInstance(){

        if(is_null(self::$_instance)) {
            try{
                $db = Yii::$app->components['db'];
                $dsn = $db['dsn'];
                $username = $db['username'];
                $password = $db['password'];
                $pdo = new \PDO($dsn, $username, $password);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);  //抛出异常
                $pdo->setAttribute(\PDO::ATTR_PERSISTENT, true); //设置长链接

            } catch (\PDOException $e) {
                die('ERROR: ' . $e->getMessage()) ;
            }

            self::$_instance = $pdo;
        }
        return self::$_instance;
    }


    /**
     *
     * 修改后缀名称
     * @author chenhuian
     *
    */
    public function actionSuffix(){

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);

        $everyRow = Yii::$app->request->get('n', 200);


        //查询图片集名称
        $selectQuery = function ($start) use ($everyRow) {
            $position  = "SELECT position_id FROM source_position LIMIT {$start},{$everyRow}";
            $results = $this->sqlSelect($position);
            //echo $position.'<br>';
            return  $results ? $results : [];
        };

        //查询图片列表
        $everyRows =  $everyRow * 5;
        $selectQueryImage = function ($start,$position_id) use ($everyRows) {
            $position = "SELECT position_id,image_url FROM source_position_set WHERE position_id = {$position_id} LIMIT {$start},{$everyRows}";
            $results = $this->sqlSelect($position);
            //echo $position.'<br>';
            return  $results ? $results : [];
        };


        //生成器
        $makeData = function ($results){
            foreach ($results as $key =>$row) {
                yield $key => $row;
            }
        };



        //第一种使用生成器情况
        /*
        $start_time=microtime(true);
        $start = 0;
        $suffix = [];
        while ($result = $selectQuery($start)){
            $generator = $makeData($result);
            foreach ($generator as $value) {
                $position_id = $value['position_id'];
                $startImage = 0;
                while ($resultImage = $selectQueryImage($startImage,$position_id)){
                    $generatorImage = $makeData($resultImage);
                    foreach ($generatorImage as $kb=>$vb){
                        $image_url = $vb['image_url'];
                        $sf = '.'.pathinfo($image_url, PATHINFO_EXTENSION);
                        if(!isset($suffix[$position_id])){
                            $suffix[$position_id][] = $sf;
                        }else{
                            if(!in_array($sf,$suffix[$position_id])){
                                $suffix[$position_id][] = $sf;
                            }
                        }
                    }
                    $startImage += $everyRows;
                }
            }
            $start += $everyRow;
        }

        $end_time=microtime(true);
        echo "time: ", bcsub($end_time, $start_time, 4), "s\n";
        echo "memory: ", memory_get_peak_usage(true)/1024/1024, "MB\n";

        */


        //没有使用生成器

        $start_time = microtime(true);
        $start = 0;
        $suffix = [];
        while ($result = $selectQuery($start)){
            foreach ($result as $value) {
                $position_id = $value['position_id'];
                $startImage = 0;
                while ($resultImage = $selectQueryImage($startImage,$position_id)){
                    foreach ($resultImage as $kb=>$vb){
                        $image_url = $vb['image_url'];
                        $sf = '.'.pathinfo($image_url, PATHINFO_EXTENSION);
                        if(!isset($suffix[$position_id])){
                            $suffix[$position_id][] = $sf;
                        }else{
                            if(!in_array($sf,$suffix[$position_id])){
                                $suffix[$position_id][] = $sf;
                            }
                        }
                    }

                    $startImage += $everyRows;
                }
            }
            $start += $everyRow;
        }

        //执行脚本数据
        if($suffix){

            $suffix = array_map(function($row){
                return implode(',',$row);
            },$suffix);

            $sql_i = "UPDATE source_position SET suffix = CASE position_id ";
            $ids = '';
            foreach ($suffix as $position_id => $count) {
                $sql_i .= " WHEN {$position_id} THEN '{$count}' ";
                $ids .= "{$position_id},";
            }
            $ids = rtrim($ids,',');
            $sql_i .= "END WHERE position_id IN ($ids)";
            $result = $this->sqlQuery($sql_i);
        }

        $end_time=microtime(true);
        echo "time: ", bcsub($end_time, $start_time, 4), "s\n";
        echo "memory: ", memory_get_peak_usage(true)/1024/1024, "MB\n";
        
    }



    /**
     * 统计源文件数据量
     * @author chenhuian
     *
    */
    public function actionSourceCount(){

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);


        $everyRow = Yii::$app->request->get('n', 200);

        $start = $count_s = $rows_s = 0;
        $selectQuery = function ($start) use ($everyRow) {
            $position  = "SELECT position_id FROM source_position LIMIT {$start},{$everyRow}";
            $result = $this->sqlSelect($position);
            return !empty($result) ? $result : false;
        };

        //生成器
        $getGenerator = function ($data) {
            foreach ($data as $key => $value) {
                yield $key => $value;
            }
        };

        // 获取当前时间戳，精确到毫秒
        $getCurrentTime = function () {
            list ($msec, $sec) = explode(" ", microtime());
            return (float)$msec + (float)$sec;
        };


        while ($result = $selectQuery($start)){

            $begin = $getCurrentTime(); // 记录开始时间
            $generator = $getGenerator($result);  // 使用生成器

            $file_count = [];
            foreach ($generator as  $row){
                $position_id = $row['position_id'];
                $sql_c = "SELECT COUNT(1) AS num FROM source_position_set WHERE position_id = {$position_id}";
                if ($results = $this->sqlSelect($sql_c)) {
                    $count = $results[0]['num'];
                    $file_count[$position_id] = $count;
                }
            }


            //执行脚本数据
            if($file_count){

                $sql_i = "UPDATE source_position SET file_count = CASE position_id ";
                $ids = '';
                foreach ($file_count as $position_id => $count) {
                    $sql_i .= " WHEN {$position_id} THEN '{$count}' ";
                    $ids .= "{$position_id},";
                }
                $ids = rtrim($ids,',');
                $sql_i .= "END WHERE position_id IN ($ids)";
                $result = $this->sqlQuery($sql_i);
            }

            $start +=$everyRow;
            $end = $getCurrentTime();
            $spend = $end - $begin;
            $logContent = '脚本执行时间为:'.$spend;
            echo $logContent.'<br>';
        }
    }



    /**
     *
     * 统计图片集大小
     *
     * @author chenhuian
     *
     *
     */
    public function actionImageSize(){

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);

        $everyRow = Yii::$app->request->get('n', 1000); // 修改表

        $selectQuery = function ($start) use ($everyRow) {
            $querySql = "SELECT image_id,position_id,image_space FROM source_position_set LIMIT {$start},{$everyRow}";
            $result = $this->sqlSelect($querySql);
            return !empty($result) ? $result : false;
        };


        //生成器
        $getGenerator = function ($data) {
            foreach ($data as $key => $value) {
                yield $key => $value;
            }
        };

        // 获取当前时间戳，精确到毫秒
        $getCurrentTime = function () {
            list ($msec, $sec) = explode(" ", microtime());
            return (float)$msec + (float)$sec;
        };

        $start = $count_s = $rows_s = 0;

        $imageSize = [];
        while ($result = $selectQuery($start)){
            $begin = $getCurrentTime(); // 记录开始时间
            $generator = $getGenerator($result);  // 使用生成器
            foreach ($generator as  $row){
                if(is_numeric($row['image_space'])){
                    if(isset($imageSize[$row['position_id']])){
                        $imageSize[$row['position_id']] += floatval($row['image_space']);
                    }else{
                        $imageSize[$row['position_id']] = floatval($row['image_space']);
                    }
                }
            }

            $start +=$everyRow;
            $end = $getCurrentTime();
            $spend = $end - $begin;
            $logContent = '脚本执行时间为:'.$spend;
            echo $logContent.'<br>';
        }

        //echo '<pre>';
        //var_dump($imageSize);exit;

        if($imageSize){

            $sql_i = "UPDATE source_position SET image_size = CASE position_id ";
            $ids = '';
            foreach ($imageSize as $position_id => $size) {
                $sql_i .= " WHEN {$position_id} THEN '{$size}' ";
                $ids .= "{$position_id},";
            }
            $ids = rtrim($ids,',');
            $sql_i .= "END WHERE position_id IN ($ids)";
            $rows = $this->sqlQuery($sql_i);
            var_dump($rows);
        }
    }



    /**
     * 更新单位，统一单位
     *
     * @author chenhuian
     * @return mixed
     *
     *
    */
    public function actionImageSizeByte()
    {

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);

        $everyRow = Yii::$app->request->get('n', 10); // 修改表

        $selectQuery = function ($start) use ($everyRow) {
            $querySql = "SELECT image_id,position_id,image_space FROM source_position_set LIMIT {$start},{$everyRow}";
            echo $querySql;
            $result = $this->sqlSelect($querySql);
            return !empty($result) ? $result : false;
        };

        //生成器
        $getGenerator = function ($data) {
            foreach ($data as $key => $value) {
                yield $key => $value;
            }
        };

        // 获取当前时间戳，精确到毫秒
        $getCurrentTime = function () {
            list ($msec, $sec) = explode(" ", microtime());
            return (float)$msec + (float)$sec;
        };

        $start = $count_s = $rows_s = 0;

        while ($result = $selectQuery($start)){

            $begin = $getCurrentTime(); // 记录开始时间

            $generator = $getGenerator($result);  // 使用生成器
            $sql_s = "UPDATE source_position_set SET image_space = CASE image_id ";
            $ids = '';
            foreach ($generator as  $row){
                $pos = strpos($row['image_space'], 'KB'); //防止重新修改
                if($pos){
                    $image_id = $row['image_id'];
                    $image_space = substr($row['image_space'], 0, -2);
                    $image_size_byte = floatval($image_space) * 1024;
                    $sql_s .= " WHEN {$image_id} THEN '{$image_size_byte}' ";
                    $ids .= "{$image_id},";
                }
            }
            if($ids){
                $ids = rtrim($ids,',');
                $sql_s .= "END WHERE image_id IN ($ids)";
                $rows = $this->sqlQuery($sql_s);
                var_dump($rows);
            }

            $start +=$everyRow;
            $end = $getCurrentTime();
            $spend = $end - $begin;
            $logContent = '脚本执行时间为:'.$spend;
            echo $logContent.'<br>';
        }
    }


    /**
     *
     * 更新封面数据封面数据
     * @author chenhuian
     *
     * @return mixed
     *
     * 请求地址：
     * /repair-data/front-cover?id=1&table=source_position&field=pic,image_size&value=11,4
     *
    */
    public function actionFrontCover(){

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);

        //参数数据
        $table   = Yii::$app->request->get('table', false); // 修改表
        $field   = Yii::$app->request->get('field', false); // 修改字段
        $value   = Yii::$app->request->get('value', false); // 修改值
        $id      = Yii::$app->request->get('id', false); // 修改表
        try{

            $rows  = $stmt = '404';
            //指定处理SQL
            if($table && $field && $value && $id){

                $fields= explode(',',$field);
                $values= explode(',',$value);
                $fv = array_combine($fields, $values);
                $sql1 = '';
                foreach ($fv as $key => $val){
                    $sql1 .="{$key}='{$val}',";
                }
                $sql1 = trim($sql1,',');
                $query = "UPDATE {$table} SET {$sql1} WHERE  id = {$id}";
                $rows = $this->sqlQuery($query);

            }else if($id == 110){

                //迁移第一张图片作为封面
                //$sql = 'UPDATE source_position AS a,source_position_set AS b SET a.pic = b.image_url WHERE a.position_id=b.position_id';
                $sql = "UPDATE source_position s
                        SET pic = (
                            SELECT image_url FROM source_position_set 
                            WHERE position_id = s.position_id AND image_url != '' LIMIT 1
                        );";
                $rows = $this->sqlQuery($sql);
            }else{
                // TODO
            }
            var_dump($stmt,$rows);
        }catch (\Exception $e){
            var_dump($e->getMessage(),$e->getFile(),$e->getLine());
        }
    }


    /**
     * 初始化数据站点对应的国家站数据
     *
     * @author chenhuian
     * @return mixed
    */
    public function actionInitRegion(){

        set_time_limit(600);    // 允许运行10分钟
        ignore_user_abort(true);

        //查询目前存在的站点数据
        $query = 'select site_id,site_code,site_name from site';
        if($result = $this->sqlSelect($query)){
            $region = \app\services\DataService::regionData();
            $regionData  = [];
            foreach ($result as $key => $row){
                if(isset($region[$row['site_code']])){
                    $rowData =  $region[$row['site_code']];
                }else{
                    $rowData =  $region[\app\services\DataService::QQZ];
                }

                foreach ($rowData as $k => $vData){
                    $regionData[] = array_merge_recursive(
                        $vData,
                        [
                            'site_id'=>$row['site_id'],
                            'site_code'=>$row['site_code']
                        ]
                    );
                }
            }


            $table = 'region';
            $sql = $this->makeInsertSQL($regionData,$table);
            if($sql){

                //先执行删除操作
                $del = "delete from {$table}";
                $this->sqlQuery($del);

                //批量添加入库
                $rows = $this->sqlQuery($sql);
                var_dump($rows);
            }
        }
    }


    /**
     *
     * @author chenhuian
     * @param array $data 接收二维数组
     * @param string $table 表格
     * @return string
     *
    */
    private function makeInsertSQL($data=[],$table=''){

        $filedData = current($data);
        if (count($filedData)==count($filedData, 1) && $table) {
            $filed = join(',',array_keys($filedData));
            $insertSQL = "INSERT INTO {$table}({$filed}) VALUES ";
            foreach ($data as $kb => $vb){
                $sql = '';
                foreach ($vb as $vc){
                    $sql .= is_string($vc) ? "'{$vc}'," : "{$vc},";
                }
                $sql = rtrim($sql, ',');
                $insertSQL.= "($sql),";
            }
            $insertSQL = rtrim($insertSQL, ',');
            return $insertSQL;
        }
        return '';
    }


    /**
     *
     * 删除测试数据
     * @author
    */
    public function actionDelData(){

        //先执行删除操作
        $position_id   = Yii::$app->request->get('position_id', false); // 修改表
        if($position_id){
            $do   = Yii::$app->request->get('do', false);

            $image_table = ['download','favorite'];  // image_id 表
            $position_table = ['position_view','source_position','source_position_set']; // position_id
            $select_sql  =  "select * from source_position_set where position_id={$position_id}";

            echo $select_sql.'<br/><hr/>';
            $delete1 = $delete = 0;
            if($res = $this->sqlSelect($select_sql)){
                foreach ($res as $key => $value){
                    $image_id = $value['image_id'];
                    foreach ($image_table as  $vb){
                        $image_sql = "delete from {$vb} where image_id={$image_id}";
                        $do && $delete1 = $this->sqlQuery($image_sql);
                        echo $image_sql.'<br/>';
                    }

                    foreach ($position_table as $vn){
                        $position_sql = "delete from {$vn} where position_id={$position_id}";
                        $do && $delete = $this->sqlQuery($position_sql);
                        echo $position_sql.'<br/>';
                    }
                    echo '<hr/>';
                }
                $do && var_dump($delete1,$delete);
            }

            echo '<a href="/repair-data/del-data?position_id='.$position_id.'&do=true">确定执行</a>';
        }
    }

    /**
     *
     * 删除数据
     *
    */
    public function actionDelPosition(){

        $position_id   = Yii::$app->request->get('position_id', false); // 修改表
        $image_id   = Yii::$app->request->get('image_id', false); // 修改表
        if($position_id){
            $sql = "delete from source_position where position_id={$position_id}";
            $delete1 = $this->sqlQuery($sql);
            var_dump($delete1);
        }


        if($image_id){
            $sqlq = "delete from source_position_set where image_id={$image_id}";
            $delete1 = $this->sqlQuery($sqlq);
            var_dump($delete1);
        }
    }

    /**
     *
     * @author chenhuian
     *
     *
    */
    public function actionDepartment(){

        $id   = Yii::$app->request->get('id', false); // 修改表
        $state_code   = Yii::$app->request->get('d', false); // 修改表
        if($id && $state_code){
            $table = 'category';
            $query = "UPDATE {$table} SET state_code ='{$state_code}' WHERE  id = {$id}";
            echo $query;
            $rows = $this->sqlQuery($query);
            var_dump($rows);
        }




        /*
        $sql   = Yii::$app->request->get('sql', false); // 修改表
        if($sql){
            try {
                $conn = self::getInstance();
                $sql = "ALTER TABLE `category` ADD COLUMN `code` varchar (30) NOT NULL DEFAULT '' COMMENT '英文简称'";
                $rs = $conn->query($sql);
                var_dump($rs);
            } catch (\PDOException $e) {
                die($e->getMessage());
            }
        }*/
    }



    /**
     * 查询操作
     *
     * @author chenhuian
     * @param string  $sql
     * @return array $data
    */
    private function sqlSelect($sql=''){
        $data = [];
        if($sql){
            $conn = self::getInstance();
            $queryStmt = $conn->prepare($sql);
            $queryStmt->execute();
            $data = $queryStmt->fetchAll(\PDO::FETCH_ASSOC);
            $queryStmt->closeCursor();
        }
        return $data;
    }

    /**
     * 更新，添加，删除操作
     *
     * @author chenhuain
     *
     * @param string  $sql
     * @return int $count
    */
    private function sqlQuery($sql=''){
        $count = 0;
        if($sql){
            $conn = self::getInstance();
            $queryStmt = $conn->prepare($sql);
            $queryStmt && $queryStmt->execute() && $count = $queryStmt->rowCount();
            $queryStmt->closeCursor();
        }
        return $count;
    }
















}