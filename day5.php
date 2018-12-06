<?php

// print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $string = trim(file_get_contents('day5-input.txt'));
    // $string = 'dabAcCaCBAcCcaDA';

    $reducedString = reduceString($string);
    print $reducedString . "\n";
    return strlen($reducedString);
}

function part2(): int
{
    $lengths = [];

    $string = trim(file_get_contents('day5-input.txt'));

    for ($i = ord('a'); $i <= ord('z'); $i++) {
        $char = chr($i);
        print "Checking $char...\n";
        $cleanedString = str_replace([$char, strtoupper($char)], '', $string);
        $lengths[$char] = strlen(reduceString($cleanedString));
    }

    $minLength = strlen($string);
    foreach ($lengths as $length) {
        if ($length < $minLength) {
            $minLength = $length;
        }
    }

    return $minLength;
}

function reduceString(string $string): string
{
    while (reduceStringByOne($string)) {}
    return $string;
}

/**
 * @param string $string
 * @return bool
 *   true if we reduced the length
 *   false if we didn't
 */
function reduceStringByOne(string &$string): bool
{
    // Look for a pair to remove
    $length = strlen($string);
    for ($i = 0; $i < $length - 1; $i++) {
        if (isPair($string, $i)) {
            // remove pair
            $string = substr($string, 0, $i) . substr($string, $i + 2);
            return true;
        }
    }

    // if no reduction found:
    return false;
}

function isPair(string $string, int $position): bool
{
    $char1 = $string[$position];
    $char2 = $string[$position + 1];

    if ($char1 !== $char2 && strtolower($char1) === strtolower($char2)) {
        return true;
    }

    return false;
}