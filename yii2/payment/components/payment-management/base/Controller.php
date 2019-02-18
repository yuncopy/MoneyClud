<?php
namespace app\base;

use yii;
use yii\web\Controller as YiiController;

/**
 * 基础控制器
 */
class Controller extends YiiController
{
	protected $request;

	protected $requestResult;   // 请求页面的结果

    /**
     * @var array $_admin_info 管理员信息
     */
    protected $_admin_info = array();


	public function __construct($id, $module, $config = []){
		parent::__construct($id, $module, $config);
		$this->request = Yii::$app->request;
	}

    public function beforeAction($action) {
        $controller_id  = Yii::$app->controller->id;
        $action_id      = Yii::$app->controller->action->id;
        $not_login      = array(//不需要登录的写到这里
            'index'      => array('login'=>1,'loginout'=>1,'synadmin'=>1,'error'=>1),
            'api'       => array('action'=>'all'),//整个控制器都不需要登录的配置
        );
        if ((isset($not_login[$controller_id]['action']) && $not_login[$controller_id]['action']=='all') || (isset($not_login[$controller_id][$action_id]) && $not_login[$controller_id][$action_id]==1))
        {
            return parent::beforeAction($action);
        } else {
            
            $session    = Yii::$app->session;
            if(!isset($session['admin_info']) || $session['admin_info']=='')
            {
               $this->go_login();
            }
            $cache          = Yii::$app->cache;
            $admin_ses      = $session['admin_info'];
            $this->_admin_info = $admin_ses;//管理员信息

            //模拟不登录功能的模拟管理员数据          
            if(isset($_GET['stresstest']))
            {
                $admin_ses = [
                    'id' => '71',
                    'username' => 'hexiang',
                    'password' => 'password',
                    'real_name' => '何翔',
                    'department_id' => '21',
                    'is_owner' => '0',
                    'powers' => 'all',
                    'allow_web' => 'all',
                    'action_web' => 'sys_set',
                ];
                $session['admin_info'] = $admin_ses;
            }


            $admin_data     = $cache->get('admin');
            $now_admin_inf  = $admin_data[$admin_ses['id']];
            if ($admin_ses['powers'] != $now_admin_inf['powers'] || $admin_ses['allow_web'] != $now_admin_inf['allow_web']) { // session 有变动,重新登录
                $this->go_login();
            }
            $site = Yii::$app->request->get('site');
            if (!empty($site)) {
                if ($admin_ses['powers'] != 'all') { //非管理员的时候判断是否有传的站点的权限
                    if (empty($admin_ses['allow_web'])) {
                        $this->sysMsg('警告', '你的账号暂无站点权限!',$params = [ 'type' => 'warning', 'links' => [ ['name'=>'返回上一页' , 'url'=>'javascript:history.go(-1)'] ] ]);
                    }
                    $tmpAdminWeb    = explode(',',$admin_ses['allow_web']);
                    if (!in_array($site,$tmpAdminWeb)) {
                        $this->sysMsg('警告', '你的账号暂无此站点权限!',$params = [ 'type' => 'warning', 'links' => [ ['name'=>'返回上一页' , 'url'=>'javascript:history.go(-1)'] ] ]);
                    }
                }
            }
            $no_pow = array( //登录后不需要判断权限的写在这里,针对对象:ajax请求,网站首页
                'site'          => array('index'=>1,'switchweb'=>1,'about'=>1),
                'department'    => array('ajaxdata'=>1),
                'menu'          => array('sub'=>1),
                'index'         => array('index'=>1, 'switchweb'=>1, 'about'=>1),
            );
            $menu_list      = $cache->get('menu');
            //$menu_list      = null;
            if(!$menu_list)
            {
                $menu_list  = $this->made_menu_cache();
            }
            $mid_arr        = [];
            $pow_id         = 0;
            if (!empty($menu_list)) {
                foreach ($menu_list as $k=>$v) {
                    if(ACTION_NAME == $action_id)
                    {
                        if ($v['pwd'] == $controller_id.'-'.$action_id) {
                            if (intval($v['parent_id']) > 0) {
                                $mid_arr[]  = $v['parent_id'];
                            }
                            $mid_arr[]  = $v['id'];
                            $pow_id     = $v['id'];
                        }                        
                    }
                }
            }
            $now_addr = '';
            if (!empty($mid_arr)) {
                foreach ($mid_arr as $k=>$v) {
                    $now_addr   .= isset($menu_list[$v])?'<li>'.$menu_list[$v]['menu_name'].'</li>':'';
                }
            }
            if (!empty($now_addr)) {
                Yii::$app->params['now_addr']   = $now_addr;
            }
            // 需要登录的才判断权限
            if (false && $admin_ses['powers'] != 'all') {
                if ($admin_ses['powers'] == '') {
                    $this->sysMsg('警告', '你的账号没有权限!',$params = [ 'type' => 'error', 'links' => [ ['name'=>'返回上一页' , 'url'=>'javascript:history.go(-1)'] ] ]);
                }
                if (isset($no_pow[$controller_id][$action_id]) && $no_pow[$controller_id][$action_id]==1) {
                } else {
                    $haveNotPower         = false;
                    $pow_id_arr     = explode(',',$admin_ses['powers']);
                    if (!in_array($pow_id,$pow_id_arr)) {
                        $haveNotPower = true;
                    }
                    if ($haveNotPower) {
                        if (Yii::$app->request->getIsAjax()) {
                            $this->returnJson(0, "没有权限");
                        } else {
                            $this->sysMsg('警告', '没有权限!',$params = [ 'type' => 'error', 'links' => [ ['name'=>'返回上一页' , 'url'=>'javascript:history.go(-1)'] ] ]);
                        }
                    }
                }
            }
        }
        return parent::beforeAction($action);
    }

    public function go_login()
    {
        $referer    = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $checkurl   = 'http://'.$_SERVER['HTTP_HOST']."/admin/index/login";
        $strurli    = base64_encode($checkurl.'|'.$referer);
        $red_url = Yii::$app->params['sso_server_url'].'/login/index/sso/?struli='.$strurli;
        header('Location:'.$red_url);
        exit;
    }

    protected function _get( $keyName = '', $defaultValue = '' )
    {
        return Yii::$app->request->get($keyName, $defaultValue);
    }
    
    protected function _post( $keyName = '', $defaultValue = '' )
    {
        return Yii::$app->request->post($keyName, $defaultValue);
    }
    
    protected function _method( $keyName = '', $defaultValue = '' )
    {
        $ret = $this->_post($keyName, null);
        if( $ret === null )
        {
            $ret = $this->_get( $keyName, $defaultValue );
        }
        else
        {
            empty( $ret ) && $ret = $defaultValue;
        }

        return $ret;
    }

    protected function _request( $keyName = '', $defaultValue = '' )
    {
        $post = Yii::$app->request->post();
        $get = Yii::$app->request->get();
        $requestData = array_merge($get, $post);
        return $requestData;
    }

    //查询数据库的所有数据
    protected function _queryAll( $sql )
    {
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        return $data;
    }

    //查询数据库的单条数据
    protected function _queryOne( $sql )
    {
        $data = Yii::$app->db->createCommand($sql)->queryOne();
        return $data;
    }

    //修改数据库的数据
    protected function _update( $tableName = '', $updata = [], $where = [] )
    {
        if( !empty($tableName) && !empty($updata) && !empty($where) )
        {
            Yii::$app->db->createCommand()->update($tableName, $updata, $where)->execute();            
        }
    }

    //新增数据库的数据
    protected function _insert( $tableName = '', $updata = [] )
    {
        if( !empty($tableName) && !empty($updata) )
        {
            Yii::$app->db->createCommand()->insert($tableName, $updata)->execute();            
        }
    }

    //新增多个数据到数据库
    protected function _insertAll( $tableName = '', $updata = [] )
    {
        if( !empty($tableName) && !empty($updata) )
        {
            $values = '';
            foreach ($updata as $key => $value) {
                $columns = " (`". implode("`,`", array_keys($updata[$key])) ."`),";
                $values .= " ('". implode("','", $value) ."'),";
            }
            $columns = substr($columns, 0, -1);
            $values = substr($values, 0, -1);

            Yii::$app->db->createCommand("insert into {$tableName} {$columns} values {$values} ")->execute();
        }
    }

    //删除数据库的数据
    protected function _delete( $tableName = '', $where = [] )
    {
        if( !empty($tableName) && !empty($where) )
        {
            Yii::$app->db->createCommand()->delete($tableName, $where)->execute();
        }
    }

    //replace单个数据到数据库
    protected function _replace( $tableName = '', $updata = [] )
    {
        if( !empty($tableName) && !empty($updata) )
        {
            $columns = " (`". implode("`,`", array_keys($updata)) ."`),";
            $columns = substr($columns, 0, -1);
            $values  = " ('". implode("','", $updata) ."'),";
            $values = substr($values, 0, -1);
            Yii::$app->db->createCommand("replace into {$tableName} {$columns} values {$values} ")->execute();
        }
    }

    //replace多个数据到数据库
    protected function _replaceAll( $tableName = '', $updata = [] )
    {
        if( !empty($tableName) && !empty($updata) )
        {
            $values = '';
            foreach ($updata as $key => $value) {
                $columns = " (`". implode("`,`", array_keys($updata[$key])) ."`),";
                $values .= " ('". implode("','", $value) ."'),";
            }
            $columns = substr($columns, 0, -1);
            $values = substr($values, 0, -1);

            Yii::$app->db->createCommand("replace into {$tableName} {$columns} values {$values} ")->execute();
        }
    }

    //time
    protected function _time()
    {
        return Yii::$app->bjTime->time();
    }

    //date
    protected function _date($format='', $time = null)
    {
        if (null === $time) {
            return Yii::$app->bjTime->date($format);
        }
        return Yii::$app->bjTime->date($format, $time);
    }

    //strtotime
    protected function _strtotime( $str = '' )
    {
        return Yii::$app->bjTime->strtotime( $str );
    }

    /**
    * 自定义打印方法
    * @param  [type] $data [description]
    * @return [type]       [description]
    */
    public function p($data = '', $data2 = '', $data3 = '', $data4 = '') 
    {
        echo "<pre>";print_r($data);echo "\n";
        if($data2)
        {
            echo "<pre>";print_r($data2);echo "\n";
        }

        if($data3)
        {
            echo "<pre>";print_r($data3);echo "\n";
        }

        if($data4)
        {
            echo "<pre>";print_r($data4);echo "\n";
        }

        die;
    }

    /**
     * 操作成功后提示并跳转
     */
    public function _redirect($msg = '操作成功', $rtn_url = null)
    {
        sleep(1);
        setcookie( "page-tip", $msg, time() + 3600, "/" );
        if(!empty($rtn_url))
        {
            header( "Location: {$rtn_url}" );            
            die();
        }
    }

    /**
     * 将字符转义成mysql认识的字符
     */
    public function escape($str)
    {
        $search=array("\\","\0","\n","\r","\x1a","'",'"');
        $replace=array("\\\\","\\0","\\n","\\r","\Z","\'",'\"');
        return str_replace($search,$replace,$str);
    }

    /** ajax方式返回数据到客户端
     *
     * @author          liu21st <liu21st@gmail.com>
     *
     * @param mixed  $success 返回状态或返回数组
     * @param string $msg     提示信息
     * @param mixed  $data    要返回的数据
     * @param mixed  $total   总数
     * @param string $type    ajax返回类型,默认json
     *
     * @todo 支持更多返回类型,如xml等
     *
     * @return void 无返回值
     */
    public function _ajaxReturn($success = true, $msg = '', $data = null, $total = null, $type = '') {

        if (is_array($success)) {
            $result = $success;
        }
        else {
            $result = array(
                'success' => $success,
                'msg'     => $msg,
                'data'    => $data,
            );

            if (!$success && !$msg) {
                $result['msg'] = "服务器出错！";
            }

            if (null !== $total) {
                $result['total'] = $total;
            }
        }

        $type = strtolower($type ? $type : 'json');

        //返回json数据格式到客户端 包含状态信息
        if ('json' == $type)
        {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);

            exit();
        }
    }//end _ajaxReturn

    public function request($url = '', $data = array(), $headers = array(), $dataType = 'xml')
    {
        if( empty( $url ) )
            return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3600); // 5秒超时
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名  
        
        if( $headers )
        {
            $temp = array();
            foreach( $headers as $key => $val )
                $temp[] = "{$key}: {$val}";
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $temp );
        }

        
        if( $data )
        {
            curl_setopt($ch, CURLOPT_POST, 1);
            if( $dataType == 'xml' || $dataType == 'json' )
            {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            else
            {
                $postData = $data;
                if( is_array( $postData ) )
                {
                    $postData = array();
                    foreach( $data as $key => $val )
                    {
                        $postData[] = $key . "=" . urlencode( $val );
                    }
                    $postData = implode( "&", $postData );

                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }            
        }

        
        $output = curl_exec( $ch );
        $error = curl_error( $ch );
        $httpInfo = curl_getinfo( $ch );
        curl_close($ch);

        $this->requestResult['http_info'] = $httpInfo;
        $this->requestResult['error'] = $error;
        $this->requestResult['output'] = $output;

        $output = trim( $output );

        if( $error && empty( $output ) )
        {
            $output = $error;
        }
        else
        {
            $startVar = substr( $output, 0, 1 );
            $endVar = substr( $output, strlen($output) - 1, 1 );
            if( $startVar == '{' && $endVar == '}' || $startVar == '[' && $endVar == ']' )
            {
                $temp = @json_decode( $output, true );
                if( $temp !== false )
                    $output = $temp;
            }
        }

        $this->requestResult['data'] = $output;
        return $output;
    }

	// 生成缓存 菜单缓存/权限缓存 防止缓存被整清
    public function made_menu_cache()
    {
        Yii::$app->db->enableSlaves = false;    // 禁用从库。只使用主库来查询，防止查询出来的数据主从不一致
        $Ydb        = Yii::$app->db;
        $query      = new \yii\db\Query();
        $res        = $query->from('menu')->orderBy('parent_id asc')->all($Ydb);
        $data       = array();
        if ($res) {
            $cache  = Yii::$app->cache;
            foreach ($res as $k=>$v) {
                $v['pwd']           = $v['menu_ctl'] . '-' . $v['menu_act'];
                $data[$v['id']]     = $v;
            }
            //此缓存只做权限判断使用
            $cache->set('menu',$data);
        }
        
        return $data;
    }

    /**
     * 获取GoogleClient组件
     *
     * 比如有请求：?site=Rosewholesale
     *
     * 优先取`app\modules\gsc\components\RosewholesaleGoogleClient`，
     * 否则取`app\modules\gsc\components\GoogleClient`
     *
     * @param array $params
     * @return \app\modules\gsc\components\GoogleClient
     */
    protected function getClientComponent(array $params = [])
    {
        $params['class'] = sprintf(
            'app\modules\gsc\components\%sGoogleClient',
            ucfirst(app()->params['site'])
        );
        if (!class_exists($params['class'])) {
            // app\modules\gsc\components\GoogleClient
            $params['class'] = 'app\modules\gsc\components\GoogleClient';
        }
        return yii::createObject($params);
    }

    /**
     * 系统提示信息
     * @param string $title 消息标题
     * @param string $msg   消息内容
     * @param array $params   消息处理参数
     * 例如： $params = [
     *                  'type' => 'success',    消息类型， success：成功、error：错误、warning：警告
     *                  'links' => [['name'=>'返回上一页',['url'=>'javascript:history.go(-1)']],        页面跳转链接
     *                  ]
     */
   public function sysMsg($title, $msg, $params = [ 'type' => 'success', 'links' => [ ['name'=>'返回上一页' , 'url'=>'javascript:history.go(-1)'] ] ])
   {
       echo $this->render('@app/modules/admin/views/site/msg.php', 
                        [
                            'title' => $title,
                            'msg' => $msg,
                            'params' => $params,
                        ]
                     );
       exit();
   }

	/**
	 * 转换json数据，与前端js约定通用
	 * @param $data mixed 数据
	 * @param int $status 状态码 0 成功 其他失败 如需特殊处理，请自行约定状态码
	 * @param string $msg 成功或者失败消息 看请求是否需要提示
	 * @return string
	 */
	protected static function toJson($data,$status=0,$msg='',$output=true){
		$data = [
			'data'  => $data,
			'status'=> $status,
			'msg'   => $msg,
		];
		if($output){
			echo json_encode($data);
		}else{
			return json_encode($data);
		}
	}

    /**
     * 返回json数据
     * @param int $status           状态码
     * @param string $msg           消息
     * @param array $data           数据
     */
	protected function returnJson($status, $msg = '', $data = [])
    {
        $jsonData = ["status" => $status, "msg" => $msg, "data" => $data];
        echo json_encode($jsonData);
        exit();
    }


    /**
     * 获取可访问的站点
     * @return array
     */
	protected function getAllowWebsite()
    {
        $session    = Yii::$app->session;
        if (empty($session['admin_info'])) {
            return array();
        }
        $adminSes      = $session['admin_info'];
        if (empty($adminSes['allow_web'])) {
            return array();
        }
        if ('all' == $adminSes['allow_web']) {
            $allowSite = app()->params['webSites'];
        } else {
            $allowSite = explode(",", $adminSes['allow_web']);     // 可访问网站
        }

        return $allowSite;
    }

    /**
     * 处理可用站点
     */
    protected function getAllowSiteTypeArr()
    {
        $allowWebsite = $this->getAllowWebsite();     // 可访问网站
        $siteTypeArr = app()->params['site_type'];

        // 通过allowSite修正siteTypeArr，用于页面显示可选择的站点
        $unsetKey = [];
        foreach ($siteTypeArr as $key => $value) {
            $siteArr = [];
            foreach ($value['site'] as $site) {
                if (in_array($site, $allowWebsite)) {
                    $siteArr[] = $site;
                }
            }
            if (empty($siteArr)) {
                $unsetKey[] = $key;
            }
            $siteTypeArr[$key]['site'] = $siteArr;
        }

        foreach ($unsetKey as $key) {
            unset($siteTypeArr[$key]);
        }
        return $siteTypeArr;
    }

    /**
     * 获取允许访问的支付方式
     * @return array
     */
    protected function getAllowPaymentMethod()
    {

        $paymentMethod = [];
        $paymentTypeArr = app()->params['payment_type'];
        foreach ($paymentTypeArr as $key => $value) {
            $paymentMethod = array_merge($paymentMethod, $value['payment']);
        }
        sort($paymentMethod);

        return $paymentMethod;
    }

    /**
     * 获取允许访问的支付类型数组
     * @param $allowPaymentMethod array  允许访问的支付方式数组，数组每一个元素都是一个支付方式字符串
     * @return mixed
     */
    protected function getAllowPaymentTypeArr($allowPaymentMethod = [])
    {
        if (empty($allowPaymentMethod)) {
            $allowPaymentMethod = $this->getAllowPaymentMethod();
        }

        $paymentTypeArr = app()->params['payment_type'];

        // 通过allowPaymentMethod修正paymentTypeArr，用于页面显示可选择的站点
        $unsetKey = [];     // 用于记录不允许访问的key，后面将其从数组中删掉
        foreach ($paymentTypeArr as $key => $value) {
            $paymentArr = [];       // 用于记录当前key下，有哪些是允许访问的支付方式
            foreach ($value['payment'] as $payment) {
                if (in_array($payment, $allowPaymentMethod)) {
                    $paymentArr[] = $payment;
                }
            }

            if (empty($paymentArr)) {   // 如果当前key下，没有允许访问的支付方式，那么先把此key记下，后面再将其从数组中删掉
                $unsetKey[] = $key;
            }
            $paymentTypeArr[$key]['payment'] = $paymentArr;
        }

        // 删除不允许访问的key
        foreach ($unsetKey as $key) {
            unset($paymentTypeArr[$key]);
        }

        return $paymentTypeArr;
    }

    /*
     *获取货币
    */
    protected function getCurrency(){
        $rate = Yii::$app->cache->get('soa_rate_currency');
        if(empty($rate)){
            $rateData = Yii::$app->MongoAdapter->findOne(\app\modules\admin\models\DataCollect::COLLECTION_RATE,['rateType'=>\app\models\SoaPaymentReport::RATE_EXTERNAL,'rateSource'=>'yahoo']);
            $rateData = json_decode($rateData['rateData'],true);
            if(!empty($rateData)){
                foreach($rateData as $key=>$row){
                    $rate[] = $key;
                }
                Yii::$app->cache->set('soa_rate_currency',$rate);
            }
        }
        return $rate;
    }

    /**
     * 获取操作者名字
     * @return string
     */
    protected function getOperatorName()
    {
        $session = Yii::$app->session;
        $operatorName = "";
        if (isset($session['admin_info']) && $session['admin_info'] != '') {
            $operatorName = $session['admin_info']['real_name'];//操作者
        }
        return $operatorName;
    }

    /**
     * 返回运行失败，并中止运行
     * @param $msg
     */
    protected function returnError($msg)
    {
        if (app()->request->isAjax) {
            $this->returnJson(0, $msg);
        } else {
            $params = [ 'type' => 'warning', 'links' => [ ['name'=>'返回上一页' , 'url'=>'javascript:history.go(-1)'] ] ];
            $this->sysMsg('警告', $msg,$params);
        }
    }
}
