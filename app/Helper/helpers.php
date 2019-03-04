<?php
/**
 * Created by PhpStorm.
 * User: zhulinjie
 * Date: 23/03/2018
 * Time: 1:29 PM
 */

/**
 * 打印调试
 *
 * @param $content
 * @author zhulinjie
 */
function h_console_log($content)
{
    echo '<pre>';
    print_r($content);
    exit;
}

/**
 * @param array $param
 * @param array $need
 * @param bool $isParamConvert
 * @return array
 * @author renqingbin
 */
function h_dynamic_where(array $param = [], array $need = [], $isParamConvert = false)
{
    $where = [];
    foreach ($need as $column) {
        if (preg_match('/(?<srcColumn>[a-z_]+)\s+as\s+(?<dstColumn>[a-z_\.]+)/i', $column, $matchs)) {
            if (!empty($param[$matchs['srcColumn']])) {
                $where[$matchs['dstColumn']] = $param[$matchs['srcColumn']];
            }
        } else {
            if (!empty($param[$column])) {
                $where[$column] = $param[$column];
            }
        }
    }
    return $isParamConvert ? h_param_convert($where) : $where;
}

/**
 * 将类似['id' => 1]格式的参数转换成['id', '=', 1]格式，以便于多条件混合查询
 *
 * @param array $param
 * @return array
 * @author renqingbin
 */
function h_param_convert(array $param)
{
    $where = [];
    foreach ($param as $key => $value) {
        $where[] = [$key, '=', $value];
    }
    return $where;
}

/**
 * where条件转换
 *
 * @param array $param
 * @return array
 * @author renqingbin
 */
function h_param_where(array $param)
{
    $where = [];
    foreach ($param as $key => $value) {
        if ($value) {
            $where[] = [$key, '=', $value];
        }
    }
    return $where;
}

/**
 * whereIn和whereNotIn条件转换
 *
 * @param array $param
 * @return array
 * @author renqingbin
 */
function h_param_whereIn(array $param)
{
    $where = [];
    foreach ($param as $key => $value) {
        if ($value) {
            $where[$key] = $value;
        }
    }
    return $where;
}

/**
 * 大于小于和大于等于和小于等于的条件转换
 *
 * @param array $param
 * @return array
 * @author renqingbin
 */
function h_param_Interval(array $param)
{
    $where = [];
    // 大于的条件
    if (isset($param['greaterThan']) && $param['greaterThan']) {
        foreach ($param['greaterThan'] as $key => $value) {
            if ($value) {
                $where[] = [$key, '>', $value];
            }
        }
    }
    // 小于的条件
    if (isset($param['lessThan']) && $param['lessThan']) {
        foreach ($param['lessThan'] as $key => $value) {
            if ($value) {
                $where[] = [$key, '<', $value];
            }
        }
    }
    // 大于等于的条件
    if (isset($param['greaterThanEqual']) && $param['greaterThanEqual']) {
        foreach ($param['greaterThanEqual'] as $key => $value) {
            if ($value) {
                $where[] = [$key, '>=', $value];
            }
        }
    }
    // 小于等于的条件
    if (isset($param['lessThanEqual']) && $param['lessThanEqual']) {
        foreach ($param['lessThanEqual'] as $key => $value) {
            if ($value) {
                $where[] = [$key, '<=', $value];
            }
        }
    }

    return $where;
}

/**
 * 排序条件转换
 *
 * @param array $param
 * @return array
 * @author renqingbin
 */
function h_param_orderBy(array $param)
{
    $where = [];
    foreach ($param as $key => $value) {
        if ($value) {
            $where[$key] = $value;
        }
    }
    return $where;
}

/**
 * 高级的参数转化成where条件
 *
 * @param array $param
 * @return mixed
 * @author renqingbin
 */
function h_param_advanced_convert(array $param)
{
    $where = [];
    // 如果有where存在就调用where的转换方法
    if (isset($param['where']) && $param['where']) {
        $whereResult = h_param_where($param['where']);
        if ($whereResult) {
            $where['where'] = $whereResult;
        }
    }

    // 如果有whereIn存在就调用whereIn的转换方法
    if (isset($param['whereIn']) && $param['whereIn']) {
        $whereInReulst = h_param_whereIn($param['whereIn']);
        if ($whereInReulst) {
            $where['whereIn'] = $whereInReulst;
        }
    }

    // 如果有whereNotIn存在就调用whereNotIn的转换方法
    if (isset($param['whereNotIn']) && $param['whereNotIn']) {
        $whereNotInReulst = h_param_whereIn($param['whereNotIn']);
        if ($whereNotInReulst) {
            $where['whereNotIn'] = $whereNotInReulst;
        }
    }

    // 大于小于和大于等于和小于等于的条件封装
    if (isset($param['interval']) && $param['interval']) {
        $interval = h_param_Interval($param['interval']);
        // 判断是否为空
        if ($interval) {
            if (isset($where['where'])) {
                $where['where'] = array_merge($where['where'], $interval);
            } else {
                $where['where'] = $interval;
            }
        }
    }

    // 排序
    if (isset($param['orderBy']) && $param['orderBy']) {
        $orderByReulst = h_param_orderBy($param['orderBy']);
        if ($orderByReulst) {
            $where['orderBy'] = $orderByReulst;
        }
    }

    return $where;
}

/**
 * 时间戳格式化
 *
 * @param $time
 * @return string
 * @author renqingbin
 */
function h_time_format($time)
{
    $format = '';

    // 天
    $day = floor($time / 86400);
    if ($day) {
        $format = $day < 10 ? '0' . $day . '天' : $day . '天';
    }

    // 小时
    $hour = floor(($time % 86400) / 3600);
    if ($hour) {
        $format .= $hour < 10 ? '0' . $hour . '小时' : $hour . '小时';
    }

    // 分钟
    $minute = floor(($time % 86400 % 3600) / 60);
    if ($minute) {
        $format .= $minute < 10 ? '0' . $minute . '分钟' : $minute . '分钟';
    }

    // 秒
    $second = $time % 86400 % 3600 % 60;
    if ($second) {
        $format .= $second < 10 ? '0' . $second . '秒' : $second . '秒';
    }

    return !$format ? '0秒' : $format;
}