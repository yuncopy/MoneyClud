<?php

return [
    'imageFormat' => [
        'jpg','png','gif','css','html','webp','pdf'
    ],
    'imagePattern'=>[
        'chinesePattern'=>'/[\x{4e00}-\x{9fa5}]+/u',  //中文
        'specialPattern'=>"/[\',:;*?~`!#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/" // 特殊字符
    ]
];
