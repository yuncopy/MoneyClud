<?php
/**
 *
 *
 * author: Wei Chang Yue
 * date: 2019/01/24 11:01
 */

namespace app\services;

use Yii;

class DataService
{


    //初始化数据，以简称
    const QQZ = 'HQ';  // 全球站简称
    public static  function regionData()
    {
        $add_time = time();
        return [
            'ZF' => [
                ['region_name' => '全球ZF站', 'region_code' => 'ZF', 'add_time' => $add_time],
                ['region_name' => '法国站', 'region_code' => 'FR', 'add_time' => $add_time],
                ['region_name' => '德国站', 'region_code' => 'DE', 'add_time' => $add_time],
                ['region_name' => '西班牙站', 'region_code' => 'ES', 'add_time' => $add_time],
                ['region_name' => '意大利站', 'region_code' => 'IT', 'add_time' => $add_time],
                ['region_name' => '葡萄牙站', 'region_code' => 'PT', 'add_time' => $add_time],
                ['region_name' => '加拿大站', 'region_code' => 'CA', 'add_time' => $add_time],
                ['region_name' => '英国站', 'region_code' => 'UK', 'add_time' => $add_time],
                ['region_name' => '爱尔兰站', 'region_code' => 'IE', 'add_time' => $add_time],
                ['region_name' => '澳大利亚站', 'region_code' => 'AU', 'add_time' => $add_time],
                ['region_name' => '新西兰站', 'region_code' => 'NZ', 'add_time' => $add_time],
                ['region_name' => '沙特站', 'region_code' => 'AE', 'add_time' => $add_time],
                ['region_name' => '瑞士站', 'region_code' => 'CH', 'add_time' => $add_time],
                ['region_name' => '比利时站', 'region_code' => 'BE', 'add_time' => $add_time],
                ['region_name' => '菲律宾站', 'region_code' => 'PH', 'add_time' => $add_time],
                ['region_name' => '新加坡站', 'region_code' => 'SG', 'add_time' => $add_time],
                ['region_name' => '马来西亚站', 'region_code' => 'MY', 'add_time' => $add_time],
                ['region_name' => '印度站', 'region_code' => 'YN', 'add_time' => $add_time],
                ['region_name' => '南非', 'region_code' => 'ZA', 'add_time' => $add_time],
                ['region_name' => '泰国', 'region_code' => 'TH', 'add_time' => $add_time],
                ['region_name' => '巴西', 'region_code' => 'BR', 'add_time' => $add_time],
                ['region_name' => '台湾', 'region_code' => 'TW', 'add_time' => $add_time],
                ['region_name' => '印度尼西亚', 'region_code' => 'ID', 'add_time' => $add_time],
                ['region_name' => '奥地利', 'region_code' => 'AT', 'add_time' => $add_time],
                ['region_name' => '以色列', 'region_code' => 'IL', 'add_time' => $add_time],
                ['region_name' => '墨西哥', 'region_code' => 'MX', 'add_time' => $add_time],
                ['region_name' => '荷兰', 'region_code' => 'NL', 'add_time' => $add_time],
                ['region_name' => '土耳其', 'region_code' => 'TR', 'add_time' => $add_time],
                ['region_name' => '阿联酋', 'region_code' => 'AE', 'add_time' => $add_time],
                ['region_name' => '芬兰', 'region_code' => 'FI', 'add_time' => $add_time],
                ['region_name' => '瑞典', 'region_code' => 'SE', 'add_time' => $add_time],
                ['region_name' => '丹麦', 'region_code' => 'DK', 'add_time' => $add_time],
                ['region_name' => '挪威', 'region_code' => 'NO', 'add_time' => $add_time],
                ['region_name' => '俄罗斯', 'region_code' => 'RU', 'add_time' => $add_time],
                ['region_name' => '通用ZF所有国家站', 'region_code' => 'ZFALL', 'add_time' => $add_time],
            ],
            'RG'=>[
                ['region_name' => '全球RG站', 'region_code' => 'RG', 'add_time' => $add_time],
                ['region_name' => '法国站', 'region_code' => 'FR', 'add_time' => $add_time],
                ['region_name' => '俄罗斯站', 'region_code' => 'RU', 'add_time' => $add_time],
                ['region_name' => '阿拉伯站', 'region_code' => 'AE', 'add_time' => $add_time],
                ['region_name' => '通用ZF所有国家站', 'region_code' => 'RGALL', 'add_time' => $add_time],
            ],
            'DL'=>[
                ['region_name' => '全球DL站', 'region_code' => 'DL', 'add_time' => $add_time],
                ['region_name' => '法国站', 'region_code' => 'FR', 'add_time' => $add_time],
                ['region_name' => '通用DL所有国家站', 'region_code' => 'DLALL', 'add_time' => $add_time],
            ],
            'GB'=>[
                ['region_name' => '全球站', 'region_code' => 'GB', 'add_time' => $add_time],
                ['region_name' => '通用GB所有国家站', 'region_code' => 'GBALL', 'add_time' => $add_time],
            ],
            'ST'=>[
                ['region_name' => '全球ST站', 'region_code' => 'ST', 'add_time' => $add_time],
            ],
            self::QQZ=>[
                ['region_name' => '全球站', 'region_code' => self::QQZ, 'add_time' => $add_time],
            ]
        ];
    }
}