<?php

const DUPLICATES = -1;

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $coords = getCoords();
    $map = buildMap($coords);
    return findLargestArea($map);
}

function part2(): int
{
    $coords = getCoords();
    $map = buildDistanceMap($coords);
    return countPointsAboveThreshold($map, 10000);
}

function getCoords(): array
{
    $coords = [];
    foreach (explode("\n", file_get_contents('day6-input.txt')) as $line) {
        if ($coord = Coord::createFromString($line)) {
            $coords[] = $coord;
        }
    }

    return $coords;
}

/**
 * @param array|Coord[] $coords
 * @return Coord
 */
function getGridExtents(array $coords): Coord
{
    $maxX = $maxY = 0;
    foreach ($coords as $coord) {
        if ($coord->x > $maxX) {
            $maxX = $coord->x;
        }

        if ($coord->y > $maxY) {
            $maxY = $coord->y;
        }
    }

    return Coord::createFromCoords($maxX, $maxY);
}

/**
 * @param array|Coord[] $coords
 * @return array
 */
function buildMap(array $coords): array
{
    $extents = getGridExtents($coords);
    $map = [];
    for ($row = 0; $row <= $extents->y; $row++) {
        for ($col = 0; $col <= $extents->x; $col++) {
            $map[$row][$col] = findClosestCoord($row, $col, $coords);
        }
    }

    return $map;
}

function findClosestCoord(int $row, int $col, array $coords): int
{
    $distances = [];

    $sourceCoord = Coord::createFromCoords($col, $row);
    foreach ($coords as $index => $targetCoord) {
        $distances[$index] = $sourceCoord->getDistance($targetCoord);
    }

    $mins = array_keys($distances, min($distances));
    if (count($mins) > 1) {
        return DUPLICATES;
    }

    return $mins[0];
}

function findLargestArea(array $map): int
{
    // count how many there are of each index
    $areas = [];
    foreach ($map as $row => $cols) {
        foreach ($cols as $index) {
            $areas[$index]++;
        }
    }

    // remove any with infinite area - i.e. any on the edges
    $maxRow = count($map) - 1;
    $maxCol = count($map[0]) - 1;

    foreach ($map as $row => $cols) {
        foreach ($cols as $col => $index) {
            if ($row === 0 || $row === $maxRow || $col === 0 || $col === $maxCol) {
                if (array_key_exists($index, $areas)) {
                    unset($areas[$index]);
                }
            }
        }
    }

    return max($areas);
}

/**
 * @param array|Coord[] $coords
 * @return array
 */
function buildDistanceMap(array $coords): array
{
    $extents = getGridExtents($coords);
    $map = [];
    for ($row = 0; $row <= $extents->y; $row++) {
        for ($col = 0; $col <= $extents->x; $col++) {
            $map[$row][$col] = findTotalCoordDistance($row, $col, $coords);
        }
    }

    return $map;
}

function findTotalCoordDistance(int $row, int $col, array $coords): int
{
    $sourceCoord = Coord::createFromCoords($col, $row);
    $totalDistance = 0;
    foreach ($coords as $coord) {
        $totalDistance += $sourceCoord->getDistance($coord);
    }

    return $totalDistance;
}

function countPointsAboveThreshold(array $map, int $threshold): int
{
    $points = 0;

    foreach ($map as $row => $cols) {
        foreach ($cols as $value) {
            if ($value < $threshold) {
                $points++;
            }
        }
    }

    return $points;
}

class Coord
{
    public $x;
    public $y;

    /**
     * @param string $string
     * @return Coord|null
     */
    public static function createFromString(string $string)
    {
        $parts = explode(',', $string);

        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
            $coord = new Coord();
            $coord->x = (int)$parts[0];
            $coord->y = (int)$parts[1];
            return $coord;
        }

        return null;
    }

    /**
     * @param int $x
     * @param int $y
     * @return Coord
     */
    public static function createFromCoords(int $x, int $y): Coord
    {
        $coord = new Coord();
        $coord->x = $x;
        $coord->y = $y;
        return $coord;
    }

    /**
     * Simple Manhattan distance between this coord and the passed coord
     *
     * @param Coord $coord
     * @return int
     */
    public function getDistance(Coord $coord): int
    {
        $dx = abs($coord->x - $this->x);
        $dy = abs($coord->y - $this->y);

        return $dx + $dy;
    }
}