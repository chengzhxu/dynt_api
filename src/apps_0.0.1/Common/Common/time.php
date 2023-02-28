<?php

/**
 * 计算日期差
 *
 *  w - weeks
 *  d - days
 *  h - hours
 *  m - minutes
 *  s - seconds
 * @static
 * @access public
 * @param date $date1 要比较的日期
 * @param date $date2 要比较的日期
 * @param string $elaps  比较跨度
 * @return integer
 */
function dateDiff($date1, $date2, $elaps = 'd') {
    $object = new \Org\Util\Date($date1);
    return $object->dateDiff($date2, $elaps);
}

/**
 * 取得指定间隔日期
 *
 *    yyyy - 年
 *    q    - 季度
 *    m    - 月
 *    y    - day of year
 *    d    - 日
 *    w    - 周
 *    ww   - week of year
 *    h    - 小时
 *    n    - 分钟
 *    s    - 秒
 * @access public
 * @param integer $number 间隔数目
 * @param string $interval  比较类型
 * @return Date
 */
function dateAdd($date, $number = 0, $interval = 'd') {
    $object = new \Org\Util\Date($date);
    return $object->dateAdd($number, $interval);
}
