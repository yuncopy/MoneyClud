<?php
namespace app\helpers;

use Yii;
use app\models\PositionView;
use app\models\Category;
use app\models\Favorite;
use app\models\Region;
/**
 * 项目助手类
 *
 * 这里的方法，只适合当前项目
 */
class Helper extends BaseHelper
{

    /**
     * 打包下载
     * @param type $files
     * @param type $name
     */
    public function getDownLoadZip($files,$name){
        $tmpFile = $name.'.zip';
        $zip = new \ZipArchive;
        $zip->open($tmpFile, \ZipArchive::CREATE);
        foreach ($files as $file) {
            // download file
            $fileContent = file_get_contents($file);

            $zip->addFromString(basename($file), $fileContent);
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$tmpFile);
        header('Content-Length: ' . filesize($tmpFile));
        readfile($tmpFile);

        unlink($tmpFile);
    }
    
    /**
     * 打包下载
     * @param type $files
     * @param type $name
     */
    public function getDownLoadFile($url,$size='') {
        $file = basename($url);
        if(!$size){
            $header = get_headers($url, 1);
            $size = $header['Content-Length'];
        }

        $fp = fopen($url, 'rb');
        if ($fp === false)
            exit('文件不存在或打开失败');

        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($url);
        return true;

        // 原始程序
        /*
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);

        ob_clean();
        ob_end_flush();
        set_time_limit(0);

        $chunkSize = 1024 * 1024;
        while (!feof($fp)) {
            $buffer = fread($fp, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
        }
        fclose($fp);
        */

    }



}
