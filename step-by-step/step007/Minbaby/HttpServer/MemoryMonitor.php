<?php

namespace Minbaby\HttpServer;

use Minbaby\HttpServer\Logger;

class MemoryMonitor
{
    private static $map = [];

    private static $hasInit = false;

    private static $startAt = 0;

    private function __construct()
    {
    }

    public static function init()
    {
        if (static::$hasInit) {
            self::log("已经初始化完毕");

            return;
        }

        static::$hasInit = true;
        static::$startAt = time();

        static::$map = array_merge(
            static::$map,
            [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'start_at' => static::$startAt,
            ]
        );

        static::getUsage();
    }

    private static function log($msg)
    {
        Logger::log(__CLASS__, $msg);
    }

    /**
     * 单位 byte
     *
     * @param bool $friendly
     *
     * @return array
     */
    public static function getUsage($friendly = false)
    {
        $tick = time() - static::$startAt;
        $memory_get_real_usage = memory_get_usage(true);
        $memory_get_usage = memory_get_usage();
        $memory_get_real_peak_usage = memory_get_peak_usage(true);
        $memory_get_peak_usage = memory_get_peak_usage();
        if ($friendly) {
            $tick = static::friendlyDate(static::$startAt);
            $memory_get_real_usage = self::convert($memory_get_real_usage);
            $memory_get_usage = self::convert($memory_get_usage);
            $memory_get_real_peak_usage = self::convert($memory_get_real_peak_usage);
            $memory_get_peak_usage = self::convert($memory_get_peak_usage);
        }

        return static::$map = array_merge(
            static::$map,
            [
                'memory_get_peak_usage' => $memory_get_peak_usage, // emalloc 使用的内存
                'memory_get_real_peak_usage' => $memory_get_real_peak_usage, //系统分配的实际内存尺寸
                'memory_get_usage' => $memory_get_usage, // 实际使用的内存量
                'memory_get_real_usage' => $memory_get_real_usage, // 获取系统分配总的内存尺寸，包括未使用的页
                'tick' => $tick
            ]
        );
    }

    private static function convert($size)
    {
        $unit = ['B', 'K', 'M', 'G', 'T', 'P'];

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[intval($i)];
    }

    public static function prettyPrint($ret = false)
    {
        $map = static::getUsage(true);
        $ret =  sprintf(
            "[%s] [%s] [%s/%s]",
            $map['php_version'],
            $map['tick'],
            $map['memory_get_usage'],
            $map['memory_get_peak_usage']
        );

        if ($ret) {
            return $ret;
        }

        echo $ret, PHP_EOL;
        return null;
    }

    private static function friendlyDate($sTime, $type = 'normal', $alt = 'false')
    {
        if (!$sTime) {
            return '';
        }

        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime = time();
        $dTime = $cTime - $sTime;

        $min = $dTime / 60;
        $hours = $min / 60;
        $days = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min = floor($min - ($days * 60 * 24) - ($hours * 60));
        $second = $dTime % 60;

        return sprintf("%03s日%s2时%02s分%02s秒", $days, $hours, $min, $second);
    }
}
