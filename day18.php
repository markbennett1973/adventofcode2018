<?php

//const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day18-input.txt';

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $map = buildMap();
    for ($i = 0; $i < 10; $i++) {
        $map = updateMap($map);
    }

    return getResourceValue($map);
}

function part2(): int
{
    $limit = 2000;
    file_put_contents('resources.csv', '');

    $map = buildMap();
    for ($i = 0; $i < $limit; $i++) {
        $map = updateMap($map);
        file_put_contents('resources.csv', getResourceValue($map) . "\n", FILE_APPEND);
    }

    // Looking at resources.csv, there's a repeating pattern of resource values after 410 minutes
    // Extrapolating that pattern to 1000000000 minutes gives a resource value of 195952
    return 195952;
}

function buildMap(): array
{
    $map = [];
    $lines = explode("\n", file_get_contents(INPUT_FILE));
    foreach ($lines as $row => $line) {
        for ($col = 0; $col < strlen($line); $col++) {
            $map[$row][$col] = $line[$col];
        }
    }

    return $map;
}

function updateMap(array $map): array
{
    $newMap = [];

    foreach ($map as $row => $rowData) {
        foreach ($rowData as $col => $colData) {
            $newMap[$row][$col] = getNewContent($map, $row, $col);
        }
    }

    return $newMap;
}

function getNewContent(array $map, int $row, int $col)
{
    switch ($map[$row][$col]) {
        case '.':
            return getNewOpenContent($map, $row, $col);

        case '|':
            return getNewTreeContent($map, $row, $col);

        case '#':
            return getNewLumberyardContent($map, $row, $col);
    }

    return $map[$row][$col];
}

function getNewOpenContent(array $map, int $row, int $col): string
{
    $adjacentCells = getAdjacentCells($map, $row, $col);
    // If there are at least three adjacent trees, we become a tree
    if ($adjacentCells['|'] >= 3) {
        return '|';
    }

    // otherwise we remain open ground
    return '.';
}

function getNewTreeContent(array $map, int $row, int $col): string
{
    $adjacentCells = getAdjacentCells($map, $row, $col);
    // If there are at least three adjacent lumberyards, we become a lumberyard
    if ($adjacentCells['#'] >= 3) {
        return '#';
    }

    // otherwise we remain a tree
    return '|';
}

function getNewLumberyardContent(array $map, int $row, int $col): string
{
    $adjacentCells = getAdjacentCells($map, $row, $col);
    // If there is an adjacent tree and lumberyard, we remain a lumberyard
    if ($adjacentCells['|'] >= 1 && $adjacentCells['#'] >= 1) {
        return '#';
    }

    // otherwise we return to open ground
    return '.';
}

function getAdjacentCells(array $map, int $targetRow, int $targetCol): array
{
    $cells = [
        '.' => 0,
        '#' => 0,
        '|' => 0,
    ];

    for ($row = $targetRow - 1; $row <= $targetRow + 1; $row++) {
        for ($col = $targetCol - 1; $col <= $targetCol + 1; $col++) {
            if ($row === $targetRow && $col === $targetCol) {
                continue;
            }

            if (array_key_exists($row, $map) && array_key_exists($col, $map[$row])) {
                $char = $map[$row][$col];
                $cells[$char]++;
            }
        }
    }

    return $cells;
}

function getResourceValue(array $map): int
{
    $woods = $lumberyards = 0;
    foreach ($map as $row) {
        foreach ($row as $col) {
            if ($col === '|') {
                $woods++;
            }

            if ($col === '#') {
                $lumberyards++;
            }
        }
    }

    return $woods * $lumberyards;
}

function printMap(array $map)
{
    $out = '';
    foreach ($map as $row) {
        $out .= implode('', $row) . "\n";
    }

    $out .= "\n\n";

    print $out;
}

function printProgress(int $done, int $total)
{
    static $start = null;
    if (!$start) {
        $start = microtime(true);
    }

    $elapsed = microtime(true) - $start;
    $elapsed = round($elapsed, 2);
    $progress = round($done * 100 / $total, 2);

    if ($done) {
        $etaSeconds = $elapsed * $total / $done;
        $etaDays = round($etaSeconds / (60 * 60 * 24), 0);
    } else {
        $etaDays = '?';
    }

    print "Done $done = $progress% in $elapsed s. Estimated total time = $etaDays days\n";
}