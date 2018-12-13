<?php

const GRID_ID = 1788;
const MAP_SIZE = 300;
const MIN_GRID_SIZE = 3;
const MAX_GRID_SIZE = 300;

// print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): string
{
    $map = getMap(MAP_SIZE, MAP_SIZE);
    $maxCoords = getCoordsOfMax($map, 3);
    return implode(',', $maxCoords);
}

function part2(): string
{
    $gridValues = $gridCoords = [];

    $map = getMap(MAP_SIZE, MAP_SIZE);
    for ($gridSize = MIN_GRID_SIZE; $gridSize <= MAX_GRID_SIZE; $gridSize++) {
        $result = getCoordsOfMax($map, $gridSize);

        $gridCoords[$gridSize] = [
            $result[0],
            $result[1],
        ];
        $gridValues[$gridSize] = $result[2];

        print "Done grid size $gridSize\n";
    }

    $maxes = array_keys($gridValues,max($gridValues));
    $maxGridSize = $maxes[0];
    $maxCoords = $gridCoords[$maxGridSize];

    $output = $maxCoords[0] . ',' . $maxCoords[1] . ',' . $maxGridSize;
    return $output;
}

function getMap(int $rows, int $cols): array
{
    $map = [];
    for ($row = 1; $row <= $rows; $row++) {
        for ($col = 1; $col <= $cols; $col++) {
            $map[$row][$col] = getCellPowerLevel($row, $col);
        }
    }

    return $map;
}

function getCellPowerLevel(int $x, int $y): int
{
    $rackId = $x + 10;
    $power = $rackId * $y;
    $power += GRID_ID;
    $power *= $rackId;
    if ($power > 100) {
        $power = substr($power, -3, 1);
    } else {
        $power = 0;
    }

    return $power - 5;
}

function getCoordsOfMax(array $map, int $gridSize): array
{
    $totals = [];

    $maxRow = count($map) - $gridSize + 1;
    $maxCol = count($map[1]) - $gridSize + 1;
    for ($row = 1; $row <= $maxRow; $row++) {
        for ($col = 1; $col <= $maxCol; $col++) {
            $totals[$row][$col] = getTotalPower($map, $row, $col, $gridSize);
        }
    }

    // Now get the max
    $maxRow = $maxCol = 1;
    $maxValue = $totals[1][1];
    foreach ($totals as $row => $columns) {
        foreach ($columns as $col => $value) {
            if ($value > $maxValue) {
                $maxValue = $value;
                $maxRow = $row;
                $maxCol = $col;
            }
        }
    }

    return [$maxRow, $maxCol, $maxValue];
}

function getTotalPower(array $map, int $firstRow, int $firstCol, int $gridSize): int
{
    $totalPower = 0;
    for ($row = $firstRow; $row < $firstRow + $gridSize; $row++) {
        for ($col = $firstCol; $col < $firstCol + $gridSize; $col++) {
            $totalPower += $map[$row][$col];
        }
    }

    return $totalPower;
}