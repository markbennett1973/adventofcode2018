<?php

// const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day10-input.txt';

// convert input file to json

$points = [];
foreach (explode("\n", file_get_contents(INPUT_FILE)) as $line) {
//    $line = str_replace('position=<', '', $line);
//    $line = str_replace('> velocity=<', ',', $line);
//    $line = str_replace('>', '', $line);

    $line = str_replace(['position=<', '> velocity=<', '>'], ['', ',', ''], $line);
    $parts = explode(',', $line);
    if (count($parts) === 4) {
        $points[] = [
            'x' => $parts[0],
            'y' => $parts[1],
            'dx' => $parts[2],
            'dy' => $parts[3],
        ];
    }
}

$fileContent = "rawPoints = " . json_encode($points, JSON_PRETTY_PRINT) . ";";

file_put_contents('day10-input.js', $fileContent);