<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 05/01/19
 * Time: 16:07
 */

// const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day20-input.txt';

const DOOR = 'D';
const ROOM = '.';
const WALL = '#';

$directions = '';
$pos = 0;

solve();

function solve()
{
    global $directions;
    $directions = getDirections();
    $map[0][0] = ROOM;

    applyDirections($map, 0, 0);
    // printMap($map, 0, 0);
    $longestDistance = getLongestDistance($map, 0, 0);
    print "Part 1: $longestDistance\n";

    $distantRooms = getRoomsAtLeastDistance($map, 0, 0, 1000);
    print "Part 2: $distantRooms\n";
}

function getDirections(): string
{
    $directions = file_get_contents(INPUT_FILE);
    $directions = str_replace(['$', '^'], '', $directions);
    return $directions;
}

function applyDirections(array &$map, int $startRow, int $startCol)
{
    global $directions, $pos;

    $row = $startRow;
    $col = $startCol;
    $length = strlen($directions);

    while ($pos < $length) {
        switch ($directions[$pos]) {
            case 'N':
                addCharToMap($map, $row - 1, $col, DOOR);
                addCharToMap($map, $row - 2, $col, ROOM);
                $row = $row - 2;
                break;

            case 'E':
                addCharToMap($map, $row, $col + 1, DOOR);
                addCharToMap($map, $row, $col + 2, ROOM);
                $col = $col + 2;
                break;

            case 'S':
                addCharToMap($map, $row + 1, $col, DOOR);
                addCharToMap($map, $row + 2, $col, ROOM);
                $row = $row + 2;
                break;

            case 'W':
                addCharToMap($map, $row, $col - 1, DOOR);
                addCharToMap($map, $row, $col - 2, ROOM);
                $col = $col - 2;
                break;

            case '(':
                $pos++;
                applyDirections($map, $row, $col);
                break;

            case ')':
                return;

            case '|':
                $row = $startRow;
                $col = $startCol;
                break;
        }

        $pos++;
    }
}

function addCharToMap(array &$map, int $row, int $col, string $char)
{
    if (!array_key_exists($row, $map)) {
        $map[$row] = [];
    }

    $map[$row][$col] = $char;
}

function completeMap(array &$map)
{
    // Find extents
    $rows = array_keys($map);
    $minRow = min($rows);
    $maxRow = max($rows);
    $minCol = $maxCol = 0;

    for ($row = $minRow; $row <= $maxRow; $row++) {
        $colsForRow = array_keys($map[$row]);
        $minColForRow = min($colsForRow);
        $maxColForRow = max($colsForRow);
        $minCol = ($minColForRow < $minCol ? $minColForRow : $minCol);
        $maxCol = ($maxColForRow > $maxCol ? $maxColForRow : $maxCol);
    }

    // Extend to create border wall
    $minRow--;
    $maxRow++;
    $minCol--;
    $maxCol++;

    // Fill in any missing elements as walls
    for ($row = $minRow; $row <= $maxRow; $row++) {
        if (!array_key_exists($row, $map)) {
            $map[$row] = [];
        }

        for ($col = $minCol; $col <= $maxCol; $col++) {
            if (!array_key_exists($col, $map[$row])) {
                $map[$row][$col] = WALL;
            }
        }
    }
}

function getLongestDistance(array $map, int $startRow, int $startCol): int
{
    completeMap($map);
    $explored = ["$startRow|$startCol"];
    $nodesLeft = true;
    $distance = 0;

    $currentNodes = [
        [$startRow, $startCol]
    ];

    while ($nodesLeft) {
        $newNodes = [];
        foreach ($currentNodes as $currentNode) {
            foreach (getAdjacentNodes($map, $currentNode[0], $currentNode[1]) as $node) {
                $nodeKey = $node[0] . '|' . $node[1];

                // If we've not seen this node before, add it to the explored nodes
                if (!in_array($nodeKey, $explored)) {
                    $newNodes[] = $node;
                    $explored[] = $nodeKey;
                }
            }
        }

        if (count($newNodes) === 0) {
            $nodesLeft = false;
        }

        $currentNodes = $newNodes;
        $distance++;
    }

    // We add one more than we really want at the end of the loop
    $distance--;

    // We need to return the number of doors passed through, not the actual distance
    return $distance / 2;
}

function getAdjacentNodes(array $map, int $row, int $col): array
{
    $nodes = [];
    if ($map[$row][$col + 1] !== WALL) {
        $nodes[] = [$row, $col + 1];
    }

    if ($map[$row][$col - 1] !== WALL) {
        $nodes[] = [$row, $col - 1];
    }

    if ($map[$row + 1][$col] !== WALL) {
        $nodes[] = [$row + 1, $col];
    }

    if ($map[$row - 1][$col] !== WALL) {
        $nodes[] = [$row - 1, $col];
    }

    return $nodes;
}

function getRoomsAtLeastDistance(array $map, int $startRow, int $startCol, int $minDistance): int
{
    completeMap($map);
    $explored = ["$startRow|$startCol"];
    $nodesLeft = true;
    $distance = 0;
    $distantRooms = 0;

    // We're given $minDistance in terms of doors, but this algorithm measures actual distance travelled,
    // which is double the number of doors
    $minDistance = ($minDistance * 2) - 1;

    $currentNodes = [
        [$startRow, $startCol]
    ];

    while ($nodesLeft) {
        $newNodes = [];
        foreach ($currentNodes as $currentNode) {
            foreach (getAdjacentNodes($map, $currentNode[0], $currentNode[1]) as $node) {
                $nodeKey = $node[0] . '|' . $node[1];

                // If we've not seen this node before, add it to the explored nodes
                if (!in_array($nodeKey, $explored)) {
                    $newNodes[] = $node;
                    $explored[] = $nodeKey;
                }
            }
        }

        if (count($newNodes) === 0) {
            $nodesLeft = false;
        }

        // Once we're beyond the minimum distance, count the number of rooms we find
        if ($distance >= $minDistance) {
            foreach ($newNodes as $node) {
                if ($map[$node[0]][$node[1]] === ROOM) {
                    $distantRooms++;
                }
            }
        }

        $currentNodes = $newNodes;
        $distance++;
    }

    return $distantRooms;
}

function printMap(array $map, int $startRow = null, int $startCol = null)
{
    completeMap($map);
    // Find extents
    $rows = array_keys($map);
    $minRow = min($rows);
    $maxRow = max($rows);
    $cols = array_keys($map[0]);
    $minCol = min($cols);
    $maxCol = max($cols);

    // Draw the map
    $out = '';
    for ($row = $minRow; $row <= $maxRow; $row++) {
        for ($col = $minCol; $col <= $maxCol; $col++) {
            if ($row === $startRow && $col === $startCol) {
                $out .= 'X';
            } else {
                $out .= $map[$row][$col];
            }
        }
        $out .= "\n";
    }

    print $out . "\n\n";
}