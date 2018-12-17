<?php

const INPUT_FILE = 'day12-input.txt';
const GENERATIONS = 20;
const BIG_GENERATIONS = 50000000000;

$minPot = $maxPot = null;

print "Part 1: " . part1() . "\n";
//print "Part 2: " . part2() . "\n";
testGenerationChanges();

function part1(): int
{
    $pots = getInitialState();
    $rules = getRules();

    for ($i = 0; $i < GENERATIONS; $i++) {
        $pots = getNextGeneration($pots, $rules);
    }

    return getSumOfPots($pots);
}

function part2(): int
{
    $pots = getInitialState();
    $rules = getRules();

    for ($i = 0; $i < BIG_GENERATIONS; $i++) {
        $pots = getNextGeneration($pots, $rules);

        if ($i % 100 === 0) {
            printProgress($i);
        }
    }

    return getSumOfPots($pots);
}

function testGenerationChanges()
{
    $pots = getInitialState();
    $rules = getRules();
    $previousSum = 0;

    for ($i = 0; $i < 500; $i++) {
        $pots = getNextGeneration($pots, $rules);
        $sum = getSumOfPots($pots);
        print "Done = $i, sum = $sum, change = " . ($sum - $previousSum) . "\n";
        $previousSum = $sum;
    }

    // This output shows that after 101 generations, the sum is 6767, and then
    // just increases by 67 each generation
    print "Final sum = " . (6767 + ( (BIG_GENERATIONS - 101) * 67)) . "\n";
}

function getInitialState(): array
{
    global $minPot, $maxPot;

    $lines = explode("\n", file_get_contents(INPUT_FILE));
    $state = str_replace('initial state: ', '', $lines[0]);

    $pots = [];
    $numPots = strlen($state);
    for ($i = 0; $i < $numPots; $i++) {
        $pots[$i] = $state[$i] === '#';
    }

    $minPot = min(array_keys($pots));
    $maxPot= max(array_keys($pots));

    return $pots;
}

function getRules(): array
{
    $rules = [];

    $lines = explode("\n", file_get_contents(INPUT_FILE));
    $lines = array_slice($lines, 2);
    foreach ($lines as $line) {
        if (trim($line)) {
            $key = getRuleKeyFromString($line);
            $rules[$key] = $line[9] === '#';
        }
    }

    return $rules;
}

/**
 * Get a bitwise sum for this string representation of a combination of plants
 * @param string $line
 * @return int
 */
function getRuleKeyFromString(string $line): int
{
    $key = 0;
    $key += ($line[0] === '#' ? 16 : 0);
    $key += ($line[1] === '#' ? 8 : 0);
    $key += ($line[2] === '#' ? 4 : 0);
    $key += ($line[3] === '#' ? 2 : 0);
    $key += ($line[4] === '#' ? 1 : 0);

    return $key;
}

function getRuleKeyFromPots(bool $left2, bool $left1, bool $current, bool $right1, bool $right2): int
{
    $key = 0;
    $key += $left2 ? 16 : 0;
    $key += $left1 ? 8 : 0;
    $key += $current ? 4 : 0;
    $key += $right1 ? 2 : 0;
    $key += $right2 ? 1 : 0;
    return $key;
}

/**
 * @param array $pots
 * @param array|Rule[] $rules
 * @return array
 */
function getNextGeneration(array $pots, array $rules): array
{
    global $minPot, $maxPot;

    $pots = extendPots($pots);

    $nextGenPots = [];
    for ($i = $minPot; $i <= $maxPot; $i++) {
        $ruleKey = getRuleKeyFromPots(
            array_key_exists($i - 2, $pots) ? $pots[$i - 2] : false,
            array_key_exists($i - 1, $pots) ? $pots[$i - 1] : false,
            $pots[$i],
            array_key_exists($i + 1, $pots) ? $pots[$i + 1] : false,
            array_key_exists($i + 2, $pots) ? $pots[$i + 2] : false
        );
        $nextGenPots[$i] = $rules[$ruleKey];
    }

    return $nextGenPots;
}

function extendPots(array $pots): array
{
    global $minPot, $maxPot;

    if ($pots[$minPot]) {
        // extend left by 2
        $pots[$minPot - 1] = false;
        $pots[$minPot - 2] = false;
        $minPot -= 2;
    } elseif ($pots[$minPot + 1]) {
        // extend left by 1
        $pots[$minPot - 1] = false;
        $minPot--;
    }

    if ($pots[$maxPot]) {
        // extend right by 2
        $pots[$maxPot + 1] = false;
        $pots[$maxPot + 2] = false;
        $maxPot += 2;
    } elseif ($pots[$maxPot - 1]) {
        // extend right by 1
        $pots[$maxPot + 1] = false;
        $maxPot++;
    }

    return $pots;
}

function printProgress(int $done)
{
    global $minPot, $maxPot;

    static $start = null;
    if (!$start) {
        $start = microtime(true);
    }

    $elapsed = microtime(true) - $start;
    $elapsed = round($elapsed, 2);
    $progress = round($done * 100 / BIG_GENERATIONS, 2);

    if ($done) {
        $etaSeconds = $elapsed * BIG_GENERATIONS / $done;
        $etaDays = round($etaSeconds / (60 * 60 * 24), 0);
    } else {
        $etaDays = '?';
    }

    $numPots = $maxPot - $minPot;

    print "Done $done = $progress% in $elapsed s. $numPots pots. Estimated total time = $etaDays days\n";
}

function getSumOfPots(array $pots): int
{
    $sum = 0;
    foreach ($pots as $index => $value) {
        if ($value) {
            $sum += $index;
        }
    }

    return $sum;
}

