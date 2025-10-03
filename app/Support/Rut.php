<?php

namespace App\Support;

class Rut
{
    public static function clean(?string $rut): ?string
    {
        if ($rut === null) return null;
        $r = preg_replace('/[^0-9kK]/', '', $rut);
        if ($r === '') return null;
        $dv  = strtoupper(substr($r, -1));
        $num = preg_replace('/^0+/', '', substr($r, 0, -1));
        return $num . $dv;
    }

    public static function format(?string $rut): ?string
    {
        if (!$rut) return null;
        $r  = preg_replace('/[^0-9kK]/', '', $rut);
        if ($r === '' || strlen($r) < 2) return $rut;

        $dv  = strtoupper(substr($r, -1));
        $num = substr($r, 0, -1);
        $rev = strrev($num);
        $out = '';
        for ($i = 0, $len = strlen($rev); $i < $len; $i++) {
            if ($i > 0 && $i % 3 === 0) $out .= '.';
            $out .= $rev[$i];
        }
        $numFmt = strrev($out);
        return $numFmt . '-' . $dv;
    }
}
