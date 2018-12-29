<?php

//const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day17-input.txt';

$minCol = $minRow = PHP_INT_MAX;
$maxCol = $maxRow = PHP_INT_MIN;

ini_set('xdebug.max_nesting_level', 1024);

// print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $map = buildMap();
    addWater($map);
    printMap($map);
    return getWaterVolume($map);
}

function part2(): int
{
    $map = buildMap();
    addWater($map);
    removeOverflowedWater($map);
    printMap($map);
    return getWaterVolume($map);
}

function buildMap(): array
{
    $map = [];
    $lines = explode("\n", file_get_contents(INPUT_FILE));
    foreach ($lines as $line) {
        if ($line) {
            addLineToMap($line, $map);
        }
    }

    // Add one to the min/max columns to allow water to go around the edges
    global $minCol, $maxCol, $minRow, $maxRow;
    $minCol--;
    $maxCol++;

    // Fill in blank parts on the map
    for ($row = $minRow; $row <= $maxRow; $row++) {
        if (!array_key_exists($row, $map)) {
            $map[$row] = [];
        }

        for ($col = $minCol; $col <= $maxCol; $col++) {
            if (!array_key_exists($col, $map[$row])) {
                $map[$row][$col] = ' ';
            }
        }
    }
    return $map;
}

function addLineToMap(string $line, array &$map)
{
    $parts = explode(', ', $line);

    // Get the x and y components - could be either way round
    if (substr($parts[0], 0, 1) === 'x') {
        $x = $parts[0];
        $y = $parts[1];
    } else {
        $x = $parts[1];
        $y = $parts[0];
    }

    // strip the prefixes
    $x = substr($x, 2);
    $y = substr($y, 2);

    if (strpos($x, '..') !== false) {
        $xParts = explode('..', $x);
        addHorizontalLineToMap($xParts[0], $xParts[1], $y, $map);
    } else {
        $yParts = explode('..', $y);
        addVerticalLineToMap($x, $yParts[0], $yParts[1], $map);
    }
}

function addHorizontalLineToMap(int $xMin, int $xMax, int $y, array &$map)
{
    for ($x = $xMin; $x <= $xMax; $x++) {
        $map[$y][$x] = '#';
    }

    // Update max map extents if required
    global $minCol, $maxCol, $minRow, $maxRow;
    $minCol = ($xMin < $minCol) ? $xMin : $minCol;
    $maxCol = ($xMax > $maxCol) ? $xMax : $maxCol;
    $minRow = ($y < $minRow) ? $y : $minRow;
    $maxRow = ($y > $maxRow) ? $y : $maxRow;
}

function addVerticalLineToMap(int $x, int $yMin, int $yMax, array &$map)
{
    for ($y = $yMin; $y <= $yMax; $y++) {
        $map[$y][$x] = '#';
    }

    // Update max map extents if required
    global $minCol, $maxCol, $minRow, $maxRow;
    $minCol = ($x < $minCol) ? $x : $minCol;
    $maxCol = ($x > $maxCol) ? $x : $maxCol;
    $minRow = ($yMin < $minRow) ? $yMin : $minRow;
    $maxRow = ($yMax > $maxRow) ? $yMax : $maxRow;
}

function printMap(array $map)
{
    global $minCol, $minRow, $maxCol, $maxRow;

    $rowNumberLength = strlen($maxRow);
    $out = '';
    $out .= str_repeat('-', $maxCol - $minCol);
    $out .= "\n\n\n";

    for ($row = $minRow; $row <= $maxRow; $row++) {
        $rowLabel = substr(str_repeat(' ', $rowNumberLength) . $row, -$rowNumberLength) . ': ';
        $out .= $rowLabel;

        for ($col = $minCol; $col <= $maxCol; $col++) {
            $out .= $map[$row][$col];
        }
        $out .= "\n";
    }

    print $out;
}

function getWaterVolume(array $map): int
{
    global $minCol, $maxCol, $minRow, $maxRow;

    $volume = 0;

    for ($row = $minRow; $row <= $maxRow; $row++) {
        for ($col = $minCol; $col <= $maxCol; $col++) {
            $char = $map[$row][$col];
            if ($char !== ' ' && $char !== '#') {
                $volume++;
            }
        }
    }

    return $volume;
}

function addWater(array &$map)
{
    global $minRow;
    addFall($map, $minRow, 500);
}

/**
 * Add a fall of water from a specific location
 *
 * @param array $map
 * @param int $row
 * @param int $col
 */
function addFall(array &$map, int $row, int $col)
{
    global $maxRow;

    $map[$row][$col] = '|';
    while ($row < $maxRow && $map[$row + 1][$col] !== '#') {
        $row++;
        $map[$row][$col] = '|';

        if ($row < $maxRow && $map[$row + 1][$col] === '#') {
            fillContainer($map, $row, $col);
        }
    }
}

/**
 * Fill a container with water starting from a specific location
 *
 * @param array $map
 * @param int $row
 * @param int $col
 */
function fillContainer(array &$map, int $row, int $col)
{
    while (true) {
        $overflowLeft = fillRowOfContainerToOneSide($map, $col, $row, true);
        $overflowRight = fillRowOfContainerToOneSide($map, $col, $row, false);

        if ($overflowLeft || $overflowRight) {
            return;
        }

        $row--;
    }
}

/**
 * @param array $map
 * @param int $startCol
 * @param int $row
 * @param bool $fillToLeft
 * @return bool
 *   true if row overflowed
 *   false if row did not overflow
 */
function fillRowOfContainerToOneSide(array &$map, int $startCol, int $row, bool $fillToLeft): bool
{
    global $minCol, $maxCol;

    $tempCol = $startCol;
    while (true) {
        if ($map[$row][$tempCol] === '#') {
            // we've hit the side - stop
            return false;
        }

        $map[$row][$tempCol] = '~';

        if ($map[$row + 1][$tempCol] === ' ') {
            // we've overflowed the container - add a fall, then break out of filling this row
            addFall($map, $row, $tempCol);
            return true;
        }

        if ($map[$row + 1][$tempCol] === '|') {
            // we've overflowed the container, but have already processed this fall
            return true;
        }

        if ($fillToLeft) {
            $tempCol--;
        } else {
            $tempCol++;
        }

        if ($tempCol < $minCol || $tempCol > $maxCol) {
            return true;
        }
    }

    return false;
}

function removeOverflowedWater(array &$map)
{
    global $minCol, $maxCol, $minRow, $maxRow;

    for ($row = $minRow; $row <= $maxRow; $row++) {
        for ($col = $minCol; $col <= $maxCol; $col++) {
            if ($map[$row][$col] === '|') {
                $map[$row][$col] = ' ';
            }

            if ($map[$row][$col] === '~' && isUnconstrained($map, $row, $col)) {
                $map[$row][$col] = ' ';
            }
        }
    }
}

function isUnconstrained(array &$map, int $row, int $col): bool
{
    // find first non-water to the left
    $tempCol = $col;
    while ($map[$row][$tempCol] === '~') {
        $tempCol--;
    }

    if ($map[$row][$tempCol] !== '#') {
        // this row is unconstrained to the left
        return true;
    }

    // Check again to the right
    $tempCol = $col;
    while ($map[$row][$tempCol] === '~') {
        $tempCol++;
    }

    if ($map[$row][$tempCol] !== '#') {
        // this row is unconstrained to the right
        return true;
    }

    return false;
}
