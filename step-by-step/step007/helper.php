<?php

function array_get($arr, $key, $default = '')
{
    if (is_null($key)) {
        return $default;
    }

    if (isset($arr[$key])) {
        return $arr[$key];
    }

    $segs = explode('.', $key);
    while ($seg = array_shift($segs)) {
        if (isset($arr[$seg])) {
            $arr = $arr[$seg];
        } else {
            return $default;
        }
    }

    return $arr;
}

function implodeKeyValue($data)
{
    $str = '';
    foreach ($data as $key => $value) {
        $str .= sprintf("%s:%s " . CRLF, $key, $value);
    }

    return $str;
}
