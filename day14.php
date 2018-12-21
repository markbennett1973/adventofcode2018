<?php

const INPUT = "702831";
const LIMIT = 18;
const TARGET = "702831";

// print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): string
{
    $scoreboard = INPUT;
    $maxLength = LIMIT + 10;

    $positions = [];
    for ($i = 0; $i < strlen($scoreboard); $i++) {
        $positions[$i] = $i;
    }

    while (strlen($scoreboard) < $maxLength) {
        $scoreboard = addNewScores($scoreboard, $positions);
        moveElves($scoreboard, $positions);
        // printScoreboard($scoreboard);
    }

    return getNextScores($scoreboard);
}

function part2(): int
{
    $scoreboard = INPUT;
    $positions = [];
    for ($i = 0; $i < strlen($scoreboard); $i++) {
        $positions[$i] = $i;
    }

    while (strpos($scoreboard, TARGET, 1) === false) {
        $scoreboard = addNewScores($scoreboard, $positions);
        moveElves($scoreboard, $positions);
        // printScoreboard($scoreboard);
        if (strlen($scoreboard) % 10000 === 0) {
            print "Scoreboard is " . strlen($scoreboard) . " long\n";
        }
    }

    return strlen($scoreboard) - strlen(TARGET);
}

function addNewScores(string $scoreboard, array $positions): string
{
    $total = 0;
    foreach ($positions as $position) {
        $total += (int) $scoreboard[$position];
    }

    return $scoreboard . $total;
}

function moveElves(string $scoreboard, array &$positions)
{
    $boardLength = strlen($scoreboard);
    foreach ($positions as $index => $position) {
        $currentScore = $scoreboard[$position];
        $positions[$index] = getNewElfPosition($currentScore, $position, $boardLength);
    }
}

function getNewElfPosition(int $currentScore, int $currentPosition, int $boardLength): int
{
    $newPosition = $currentPosition + $currentScore + 1;
    while ($newPosition >= $boardLength) {
        $newPosition -= $boardLength;
    }

    return $newPosition;
}

function getNextScores(string $scoreboard): string
{
    return substr($scoreboard, LIMIT, 10);
}

function printScoreboard(string $scoreboard)
{
    for ($i = 0; $i < strlen($scoreboard); $i++) {
        print $scoreboard[$i] . ' ';
    }
    print "\n";
}