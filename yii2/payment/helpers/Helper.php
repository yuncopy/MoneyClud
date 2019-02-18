<?php
namespace app\helpers;
use app\modules\admin\models\ActionHistory;
use app\components\Diff;

/**
 * 项目助手类
 *
 * 这里的方法，只适合当前项目
 */
class Helper extends BaseHelper
{
    /**
     * 将一行一个的内容转化为数组
     *
     * - 数组的每一个元素都作了`trim`处理
     * - 过滤掉了数组中等价为`false`的值
     *
     * @param $content
     * @return array
     */
    public function line2array($content)
    {
        if ('' === $content) {
            return [];
        } else {
            return array_filter(array_map(
                'trim',
                explode("\n", $content)
            ));
        }
    }

    /**
     * 添加管理日志
     *
     * @var int $actionTableObjectId
     * @var array|app\components\Diff $diff
     * @var string|app\models\ActionHistory $model
     * @return void
     */
    public function adminlog($actionTableObjectId, $diff, $model = null)
    {        
        if (null === $model) {
            $model = new ActionHistory;
        } elseif (is_string($model)) {
            $model = new $model;
        }
        $obj = new Diff();
        return $model->insertLog(
            $actionTableObjectId,
            is_array($diff) ? $obj->create($diff) : $diff
        );
    }

    /**
     * 站点
     *
     * @param string $site  站点值
     * @param int $type  类型，1获键名，2获取键值
     * @return String
     */
    public function siteInfo($site, $type) {
        $siteArr = [
            '1' => '',
            '2' => '',
            '3' => 'igogo',
            '4' => 'sammydress',
            '5' => 'dresslily',
            '6' => 'gearbest',
            '7' => 'dealsmachine',
            '8' => 'rosewholesale',
            '9' => 'everbuying',
            '10' => 'rosegal',
            '11' => 'dealsmachine',
            '12' => 'bridalpure',
            '13' => '',
            '14' => '',
        ];
        $result = '';
        foreach ($siteArr as $key => $value) {
            if ($type == 1 && $site == $value) { 
                $result = $key;
            }
            if ($type == 2 && $site == $key) {
                $result = $value;
            }
        }  
        return $result;
    }


	/**
	 * 邮箱加密：
	 */
	public static function emailEncrypt($data){
		$params = \Yii::$app->params['emailEncryptParam'];
		return openssl_encrypt($data,'aes-256-cbc',$params['key'],false,$params['iv']);
	}

	/**
	 * 邮箱解密
	 * @param $data
	 * @return string
	 */
	public static function emailDeCrypt($data){
		$params = \Yii::$app->params['emailEncryptParam'];
		return openssl_decrypt($data,'aes-256-cbc',$params['key'],false,$params['iv']);
	}

    /**
     * 根据IP获取国家简码
     * @param  ip   varchar
     * @return  varchar
     */
    public static function getIpCountryCode($ip)
    {
        static $gi;
        include_once(app()->basePath . "/components/geoip/geoip.inc.php");

        // 打开GEOIP数据文件
        if(empty($gi)){
           $gi = geoip_open(app()->basePath . "/components/geoip/GeoIP.dat",GEOIP_STANDARD); 
        }
        if (empty($gi)) {
            return "";
        }

        $countryCode = geoip_country_code_by_addr($gi, $ip);
        if (empty($countryCode)) {
            return "";
        }
        return $countryCode;
    }

    /**
     * @param $fileName
     * @param $log
     * @return bool|int
     */
    public static function saveLog($fileName, $log)
    {
        $dirName = dirname($fileName);
        if (!empty($dirName) && !is_dir($dirName)) {        // 如果不存在目录，则创建目录
            $result = mkdir($dirName, 0777, true);
            if (false === $result) {
                return false;
            }
        }

        $curTime =  app()->bjTime->date("Y-m-d H:i:s");
        $log = "{$curTime} {$log}\n";
        $result = file_put_contents($fileName, $log, FILE_APPEND);
        return $result;
    }

    /**
     * 比较两个数据，由小到大排列
     * @param $left
     * @param $right
     * @return int
     */
    public static function compare($left, $right)
    {
        if ($left === $right) {
            return 0;
        } else if ($left > $right) {
            return 1;
        } else {
            return -1;
        }
    }

    /**
     * 比较两个数据，由大到小排列
     * @param $left
     * @param $right
     * @return int
     */
    public static function reverseCompare($left, $right)
    {
        if ($left === $right) {
            return 0;
        } else if ($left > $right) {
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * 计算比例
     * @param $numerator            分子
     * @param $denominator          分母
     * @return string
     */
    public static function calcRate($numerator, $denominator)
    {
        if (empty($denominator)) {
            return "0%";
        }
        $rate = number_format($numerator / $denominator * 100, 2, '.', '') . "%";
        return $rate;
    }
}
