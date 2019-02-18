<?php
/**
 * Created by PhpStorm.
 * User: Liujianlin
 * Date: 2017/12/5
 * Time: 17:38
 */

// 站点类型
$siteTypeArr = [
    'electron' => [
        "name" => "电子站",
        "site" => ['gearbest'],
    ],
    "dress" => [
        "name" => "服装站",
        "site" => [
            'buyinggoods','boynewyork',
            'dealsmachine','digbest','dressfo','dezzal','dresslily','dizener',
            'everbuying',
            'gamiss',
            'igogo',
            'nastydress','nextmia',
            'oksells',
            'pasymoon',
            'rosewholesale','rosegal',
            'sammydress',
            'twinkledeals','trendsgal',
            'volumebest',
            'yoshop',
            'zaful','zanstyle','zanbase'
        ],
    ],
    "sale" => [
        "name" => "分销站",
        "site" => ['chinabrands', 'chinabrands_cn'],
    ]
];

// 按顺序重排站点类型
foreach ($siteTypeArr as $key => $value) {
    sort($siteTypeArr[$key]['site']);
}

return $siteTypeArr;
