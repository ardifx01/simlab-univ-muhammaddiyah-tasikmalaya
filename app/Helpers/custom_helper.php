<?php
function tgl_wkt($datetime)
{
    $bln = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    $timestamp = strtotime($datetime);
    $t = date('Y', $timestamp);
    $b = date('n', $timestamp) - 1;
    $h = date('d', $timestamp);
    $j = date('H', $timestamp);
    $m = date('i', $timestamp);

    $tgl_wkt = "$h {$bln[$b]} $t $j:$m";
    return $tgl_wkt;
}