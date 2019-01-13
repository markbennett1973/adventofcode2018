<?php

//const START_DEPTH = 510;
//const TARGET_X = 10;
//const TARGET_Y = 10;
//const PADDING_X = 5;
//const PADDING_Y = 5;

const START_DEPTH = 9171;
const TARGET_X = 7;
const TARGET_Y = 721;
const PADDING_X = 100;
const PADDING_Y = 200;

const TYPE_ROCKY = 0;
const TYPE_WET = 1;
const TYPE_NARROW = 2;

const EQUIPPED_NONE = 0;
const EQUIPPED_TORCH = 1;
const EQUIPPED_CLIMBING_GEAR = 2;

$map = buildMap();
setMapTypes($map);

print "Part 1: " . part1($map) . "\n";
print "Part 2: " . part2($map) . "\n";

function part1(array $map): int
{
    return getTotalRiskLevel($map);
}

function part2(array $map): int
{
    return getFastestRoute($map);
}

function buildMap(): array
{
    $map = [];

    for ($x = 0; $x <= (TARGET_X + PADDING_X); $x++) {
        for ($y = 0; $y <= (TARGET_Y + PADDING_Y); $y++) {
            $map[$x][$y] = getGeologicalIndex($map, $x, $y);
        }
    }

    return $map;
}

function getGeologicalIndex(array $map, int $x, int $y): int
{
    // Rule 1
    if ($x === 0 && $y === 0) {
        return 0;
    }

    // Rule 2
    if ($x === TARGET_X && $y === TARGET_Y) {
        return 0;
    }

    // Rule 3
    if ($y === 0) {
        return $x * 16807;
    }

    // Rule 4
    if ($x === 0) {
        return $y * 48271;
    }

    // Rule 5
    $geologicalIndex1 = $map[$x - 1][$y];
    $erosionLevel1 = getErosionLevel($geologicalIndex1);
    $geologicalIndex2 = $map[$x][$y - 1];
    $erosionLevel2 = getErosionLevel($geologicalIndex2);
    return $erosionLevel1 * $erosionLevel2;
}

function getErosionLevel(int $geologicalIndex): int
{
    return ($geologicalIndex + START_DEPTH) % 20183;
}

function setMapTypes(array &$map)
{
    foreach ($map as $x => $xData) {
        foreach ($xData as $y => $geologicalIndex) {
            $erosionLevel = getErosionLevel($geologicalIndex);
            $type = $erosionLevel % 3;
            $map[$x][$y] = $type;
        }
    }
}

function getTotalRiskLevel(array $map): int
{
    $totalRiskLevel = 0;
    for ($x = 0; $x <= TARGET_X; $x++) {
        for ($y = 0; $y <= TARGET_Y; $y++) {
            $totalRiskLevel += $map[$x][$y];
        }
    }

    return $totalRiskLevel;
}

function getFastestRoute(array $map): int
{
    $time = 0;

    $startNode = new Node($map, 0, 0, EQUIPPED_TORCH);
    $startNode->populateAdjacentNodes($map);

    /** @var array|Node[] $currentNodes */
    $currentNodes = [$startNode];
    $exploredNodes[$startNode->getNodeKey()] = '';

    while (!isTargetReached($currentNodes)) {
        foreach ($currentNodes as $currentNodeIndex => $currentNode) {
            foreach ($currentNode->adjacentNodes as $adjacentNodeIndex => $adjacentNode) {
                // If we can move into the adjacent node, move into it, making that a current node
                if ($adjacentNode->canNodeBeMovedInto($currentNode)) {
                    $nodeKey = $adjacentNode->getNodeKey();
                    if (!array_key_exists($nodeKey, $exploredNodes)) {
                        $currentNodes[] = $adjacentNode;
                        $adjacentNode->populateAdjacentNodes($map, $exploredNodes);
                        $exploredNodes[$nodeKey] = '';
                    }

                    // Remove that new current node from it's parent
                    unset($currentNode->adjacentNodes[$adjacentNodeIndex]);
                }

                // If the parent has no adjacent nodes left, then we've moved from it, and it's no longer active
                if (count($currentNode->adjacentNodes) === 0) {
                    unset($currentNodes[$currentNodeIndex]);
                }
            }
        }

        $time++;
        printProgress($time, $currentNodes, $exploredNodes);

        checkForPossibleMovements($currentNodes);
    }

    $targetNode = getTargetNode($currentNodes);
    if ($targetNode->equipped !== EQUIPPED_TORCH) {
        // Wait 7 minutes to equip the torch
         $time = $time + 7;
    }

    return $time;
}

/**
 * @param array|Node[] $currentNodes
 * @return bool
 */
function isTargetReached(array $currentNodes): bool
{
    foreach ($currentNodes as $node) {
        if ($node->x === TARGET_X && $node->y === TARGET_Y) {
            return true;
        }
    }

    return false;
}

/**
 * @param int $time
 * @param array|Node[] $currentNodes
 * @param array $exploredNodes
 */
function printProgress(int $time, array $currentNodes, array $exploredNodes)
{
    $closest = 99999;
    $closestNode = reset($currentNodes);

    foreach ($currentNodes as $node) {
        $distance = abs(TARGET_X - $node->x) + abs(TARGET_Y - $node->y);
        if ($distance < $closest) {
            $closest = $distance;
            $closestNode = $node;
        }
    }

    print sprintf(
        "%s: At time %d, closest node is %d, %d (%d away). Current nodes = %d, explored nodes = %d (%d MB used)\n",
        date('H:i:s'),
        $time,
        $closestNode->x,
        $closestNode->y,
        $closest,
        count($currentNodes),
        count($exploredNodes),
        round(memory_get_usage() / (1024 * 1024))
    );
}

/**
 * @param array|Node[] $nodes
 * @throws Exception
 */
function checkForPossibleMovements(array $nodes)
{
    foreach ($nodes as $node) {
        if (count($node->adjacentNodes) > 0) {
            return;
        }
    }

    throw new Exception('No further moves possible');
}

/**
 * @param array|Node[] $nodes
 * @return Node|null
 */
function getTargetNode(array $nodes)
{
    foreach ($nodes as $node) {
        if ($node->x === TARGET_X && $node->y === TARGET_Y) {
            return $node;
        }
    }

    return null;
}

class Node
{
    /** @var int */
    public $x;
    /** @var int  */
    public $y;
    /** @var int  */
    public $equipped;
    /** @var  int */
    public $type;
    /** @var  Node[] */
    public $adjacentNodes;

    /** @var  int */
    private $minutesWaited;

    public function __construct(array $map, int $x, int $y, int $equipped)
    {
        $this->x = $x;
        $this->y = $y;
        $this->type = $map[$x][$y];
        $this->equipped = $equipped;
        $this->adjacentNodes = [];

        $this->minutesWaited = 0;
    }

    /**
     * @param array $map
     * @param array|Node[] $exploredNodes
     */
    public function populateAdjacentNodes(array $map, array $exploredNodes = [])
    {
        if (array_key_exists($this->x - 1, $map)
            && !$this->isNodeExplored($exploredNodes, $this->x - 1, $this->y, $this->equipped)) {
            $this->adjacentNodes[] = new Node($map, $this->x - 1, $this->y, $this->equipped);
        }

        if (array_key_exists($this->x + 1, $map)
            && !$this->isNodeExplored($exploredNodes, $this->x + 1, $this->y, $this->equipped)) {
            $this->adjacentNodes[] = new Node($map, $this->x + 1, $this->y, $this->equipped);
        }

        if (array_key_exists($this->y - 1, $map[$this->x])
            && !$this->isNodeExplored($exploredNodes, $this->x, $this->y - 1, $this->equipped)) {
            $this->adjacentNodes[] = new Node($map, $this->x, $this->y - 1, $this->equipped);
        }

        if (array_key_exists($this->y + 1, $map[$this->x])
            && !$this->isNodeExplored($exploredNodes, $this->x, $this->y + 1, $this->equipped)) {
            $this->adjacentNodes[] = new Node($map, $this->x, $this->y + 1, $this->equipped);
        }
    }

    /**
     * @param array|Node[] $exploredNodes
     * @param int $x
     * @param int $y
     * @param int $equipped
     * @return bool
     */
    private function isNodeExplored(array $exploredNodes, int $x, int $y, int $equipped): bool
    {
        return array_key_exists("$x,$y,$equipped", $exploredNodes);
    }

    public function canNodeBeMovedInto(Node $sourceNode): bool
    {
        $this->minutesWaited++;

        if ($this->minutesWaited === 8) {
            // We've waited 7 minutes to change our equipment
            $this->equipped = $this->changeEquippedTool($sourceNode);

            // Now we can move into this node
            return true;
        }

        // If we've not yet waited 7 minutes, see if our currently equipped tool allows us to move
        switch ($this->type) {
            case TYPE_ROCKY:
                if ($this->equipped === EQUIPPED_CLIMBING_GEAR || $this->equipped === EQUIPPED_TORCH) {
                    return true;
                }
                break;

            case TYPE_WET:
                if ($this->equipped === EQUIPPED_CLIMBING_GEAR || $this->equipped === EQUIPPED_NONE) {
                    return true;
                }
                break;

            case TYPE_NARROW:
                if ($this->equipped === EQUIPPED_TORCH || $this->equipped === EQUIPPED_NONE) {
                    return true;
                }
                break;
        }

        return false;
    }

    public function getNodeKey(): string
    {
        return $this->x . ',' . $this->y . ',' . $this->equipped;
    }

    private function changeEquippedTool(Node $sourceNode): int
    {
        if ($sourceNode->type === TYPE_ROCKY) {
            if ($this->type === TYPE_WET) {
                return EQUIPPED_CLIMBING_GEAR;
            } else {
                return EQUIPPED_TORCH;
            }
        }

        if ($sourceNode->type === TYPE_WET) {
            if ($this->type === TYPE_ROCKY) {
                return EQUIPPED_CLIMBING_GEAR;
            } else {
                return EQUIPPED_NONE;
            }
        }

        if ($sourceNode->type === TYPE_NARROW) {
            if ($this->type === TYPE_ROCKY) {
                return EQUIPPED_TORCH;
            } else {
                return EQUIPPED_NONE;
            }
        }

        return -1;
    }
}