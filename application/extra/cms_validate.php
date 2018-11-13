<?php
/**
 * cms验证规则 模块名-控制器
 * 控制器名称（全部小写）对应规则数据
 */
return [
    // 权限
    'permission' => [
        'menusave'  => [
            'name|菜单名称'  => 'require',
            'url|菜单链接'   => 'require',
        ],
        'menudel'  => [
            'id|菜单ID'  => 'require|number',
        ],
        'rolesave'  => [
            'role_name|角色名称'=> 'require',
        ],
        'roledel'  => [
            'id|角色ID'  => 'require|number',
        ],
        'personstatus'  => [
            'id|管理员ID'  => 'require|number',
            'status|管理员状态'  => 'number',
        ],
        'personsave'  => [
            'person_name|名称'  => 'require',
        ],
    ],

    'expert'=>[
        'expertadd'=>[
            'name|名称'  => 'require',
            'photo|头像'  => 'require',
            'price|价格'  => 'require|number',
            'tags|标签'  => 'require',
            'phone|手机号码' => '/^1[34578]\d{9}$/',
            'banner|展示图' => 'require',
            'skill|专家擅长' => 'require',
            'result|专家简介' => 'require',

        ],
    ],

    'tags'=>[
        'tagsadd'=>[
            'name|名称'  => 'require',

        ],
    ],


    'article'=>[
        'articleadd'=>[
            'title|标题'  => 'require',
            'content|资讯内容'  => 'require',
            'author|作者名称'  => 'require',
            'tags|标签'  => 'require',
            'summary|摘要' => 'require',
            'photo|展示图'  => 'require',

        ],
    ],

    'course'=>[
        'save'=>[
            'title|标题'  => 'require',
            'photo|封面'  => 'require',
            'price|价格'  => 'require',
            'tags|标签' => 'require',
            'content|课内容' => 'require',
            'results|收获' => 'require',
            'summary|简介' => 'require',
        ],
        'masave'=>[
            'ma_title|章节名称'  => 'require',

        ],
    ],

    'column'=>[
        'editadd'=>[
            'content_id|内容id'  => 'require',
            'content_type|内容类型'  => 'require',
            'column_id|栏目id'  => 'require',
        ],
        'columnadd'=>[
            'title|栏目标题'  => 'require',

        ],
    ],

    'config'=>[
        'configadd'=>[
            'name|标题'  => 'require',
            'key|字段名'  => 'require',
            'value|值'  => 'require',
        ],
    ],

    'week'=>[
        'weekadd'=>[
            'title|标题'  => 'require',
            'periods|期数'  => 'require',
            'qrcode|二维码'  => 'require',
            'qrcode_title|二维码标题'  => 'require',
            'invites_num|达标人数'  => 'require',
            'price|订阅价格'  => 'require',
            'origin_price|原价'  => 'require',
            'content|总结内容'  => 'require',
        ],
        'editadd' => [
            'content_title|内容标题'  => 'require',
            'points|分值'  => 'require',
        ],

    ],

    'system'=>[
        'systemadd'=>[
            'content|内容'  => 'require',
        ],
    ],

    'show'=>[
        'showadd'=>[
            'storage|存储路径'  => 'require',

        ],
    ],

    'dict'=>[
        'dictadd'=>[
            'class_name|内容'  => 'require',
        ],
    ],

    'banner'=>[
        'save'=>[
            'photo|封面'  => 'require',

        ],
    ],
];