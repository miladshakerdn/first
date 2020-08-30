<?php

function dd()
{
    $arguments = func_get_args();

    foreach ($arguments as $argument) {
        echo '<pre style="background: #f2f2f2; border:1px solid #aaa; padding: 1em">';
        print_r($argument);
        echo '</pre>';
    }

    exit;
}
function d()
{
    $arguments = func_get_args();

    foreach ($arguments as $argument) {
        echo '<pre style="background: #f2f2f2; border:1px solid #aaa; padding: 1em">';
        print_r($argument);
        echo '</pre>';
    }
}
function getRange ($max = 10) {
    for ($i = 1; $i < $max; $i++) {
        yield $i;
    }
foreach (getRange(100) as $range) {
    echo "Dataset {$range} <br>";
}}
