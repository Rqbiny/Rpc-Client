<?php
/**
 *    Redis缓存Key
 *
 * CustomerModel: Redscarf
 * Last Modify: 17/6/19
 * Final Modifier: Redscarf
 */


return [

    /**
     *   混合数据缓存
     */
    'blend_data' => [
        //业务名
        'user' => [
            /**
             *   该缓存的业务作用
             *
             *   返回值： ['aaa','bbb']
             *   作者： renqingbin
             */
            'hash' => $GLOBALS['database'].':HASH:DATA_USER:DATA_ORDER:USER_ID:',
        ],
    ],

    /**
     * 设备型号表
     * @author renqingbin
     */
    'data_device_model' => [
        /*
         * Hash表
         * ['id' => 'model']
         */
        'hash' => ":HASH:DATA_CATEGORY:ID_MODEL"
    ],

    /**
     * 设备监控
     *
     * @author sunchanghao迁移,作者:任庆彬
     */
    'device_situation' => [
        'hash' => [
            /**
             * 所有盒子设备上次回传时间
             * ['device_nume' => 'unix_time,ip']
             */
            'monitor' => ':HASH:DEVICE_MONITOR',
            /**
             * 所有盒子的缓存
             * ['device_nume' => 'device_nume,上次回传距今秒数,ip']
             */
            'situation' => ':HASH:DEVICE_SITUATION',
            /**
             * 所有失联盒子的缓存
             * ['device_nume' => 'device_nume,上次回传距今秒数,ip']
             */
            'lose' => ':HASH:DEVICE_LOSE',
        ],
    ],

    /**
     * 工单统计显示
     */
    'order_count' => [
        /*
        |--------------------------------------------------------------------------
        | 工单统计红标   存储未读工单数量
        |--------------------------------------------------------------------------
        |
        | 作用： 存储未读工单数量
        |
        | KEY = STRING:DISPATCHED_ORDER:[COUNT]     COUNT 未读数量。
        | @author
        */
        'string' => [
            // 已派单
            'dispatched_order' => ':STRING:DISPATCHED_ORDER:COUNT',
            // 未到达工单数量
            'not_achive_order' => ':STRING:DISPATCHED_ORDER:NOT_ACHIVE',
            // 已到达
            'has_achive_order' => ':STRING:DISPATCHED_ORDER:HAS_ACHIVE',
            // 已完成
            'has_over' => ':STRING:DISPATCHED_ORDER:HAS_OVER'
        ]
    ]
];
