<?php

const INPUT_FILE = 'day15-input.txt';
//const INPUT_FILE = 'small.txt';
const DEBUG = false;
const ELF_ATTACK_POWER = 23;

/**
 * Part 2 attack power results:
 * 10 => elf died after 32 rounds; 12 goblins left
 * 20 => elf died after 43 rounds, 1 goblin left
 * 21 => same
 * 22 => same
 * 25 => elves won, 61280
 * 24 => elves won, 59720
 * 23 => elves won, 59720
 */

ini_set('memory_limit', '3G');

// print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    /** @var Fighter[] $fighters */
    $map = $fighters = [];
    readData($map, $fighters);

    // printBoard($map, $fighters);

    $rounds = 0;
    printProgress($rounds, $fighters);

    while (!isGameOver($fighters)) {
        // play game
        debug("Starting round " . ($rounds + 1));

        sortByReadOrder($fighters);
        $currentFighter = 1;
        foreach ($fighters as $fighter) {
            debug("Moving fighter $currentFighter of " . count($fighters));
            if (!$fighter->isDead()) {
                $fighter->doMove($map, $fighters);
            }
            $currentFighter++;
        }
        debug("Moved all fighters");

        removeDeadFighters($map, $fighters);

        $rounds++;

        //print "\nResults after round $rounds:\n";
        //printBoard($map, $fighters);
        printProgress($rounds, $fighters);
    }

    $rounds--;
    return getFinalScore($rounds, $fighters);
}

function part2(): int
{
    /** @var Fighter[] $fighters */
    $map = $fighters = [];
    readData($map, $fighters);

    $initialElves = count(getFightersOfType($fighters, Fighter::TYPE_ELF));
    $rounds = 0;
    printProgress($rounds, $fighters);

    while (!isGameOver($fighters, $initialElves)) {
        // play game
        debug("Starting round " . ($rounds + 1));

        sortByReadOrder($fighters);
        $currentFighter = 1;
        foreach ($fighters as $fighter) {
            debug("Moving fighter $currentFighter of " . count($fighters));
            if (!$fighter->isDead()) {
                $fighter->doMove($map, $fighters);
            }

            if (areAllGoblinsDead($fighters)) {
                break;
            }

            $currentFighter++;
        }
        debug("Moved all fighters");

        removeDeadFighters($map, $fighters);

        $rounds++;
        printProgress($rounds, $fighters);
    }

    // $rounds--;

    //print "\nResults after round $rounds:\n";
    //printBoard($map, $fighters);


    if (count(getFightersOfType($fighters, Fighter::TYPE_ELF)) < $initialElves) {
        print "An elf died - try a higher initial elf attack score\n";
    } else {
        print "All elves survived, but can you do it with a lower initial elf attack score?\n";
    }

    return getFinalScore($rounds, $fighters);
}

function readData(array &$map, array &$fighters)
{
    $row = 0;
    foreach (explode("\n", file_get_contents(INPUT_FILE)) as $line) {
        for ($col = 0; $col < strlen($line); $col++) {
            $map[$row][$col] = ($line[$col] === '.' ? ' ' : $line[$col]);

            if ($line[$col] === 'E') {
                $fighters[] = new Fighter($row, $col, Fighter::TYPE_ELF);
            } elseif ($line[$col] === 'G') {
                $fighters[] = new Fighter($row, $col, Fighter::TYPE_GOBLIN);
            }
        }

        $row++;
    }
}

/**
 * @param array|Fighter[]|Coord[] $fighters
 */
function sortByReadOrder(array &$fighters)
{
    usort($fighters, function ($a, $b) {
       if ($a->row === $b->row) {
           return $a->col <=> $b->col;
       }

       return $a->row <=> $b->row;
    });
}

/**
 * @param array|Fighter[] $fighters
 * @param int $initialElves
 * @return bool
 */
function isGameOver(array $fighters, int $initialElves = null): bool
{
    $currentElves =count(getFightersOfType($fighters, Fighter::TYPE_ELF));
    if ($currentElves === 0) {
        // No elves left - game over
        return true;
    }

    if ($initialElves !== null && $currentElves < $initialElves) {
        // we've lost a single elf
        return true;
    }

    if (count(getFightersOfType($fighters, Fighter::TYPE_GOBLIN)) === 0) {
        // No goblins left - game over
        return true;
    }

    return false;
}

/**
 * @param array|Fighter[] $fighters
 * @param int $type
 * @return array
 */
function getFightersOfType(array $fighters, int $type): array
{
    return array_filter($fighters, function (Fighter $fighter) use ($type) {
        return $fighter->type === $type;
    });
}

/**
 * @param array $map
 * @param array $fighters
 */
function removeDeadFighters(array &$map, array &$fighters)
{
    foreach ($fighters as $index => $fighter) {
        if ($fighter->isDead()) {
            unset($fighters[$index]);
            $map[$fighter->row][$fighter->col] = ' ';
        }
    }
}

/**
 * @param array|Fighter[] $fighters
 * @return bool
 */
function areAllGoblinsDead(array $fighters): bool
{
    $goblins = getFightersOfType($fighters, Fighter::TYPE_GOBLIN);
    foreach ($goblins as $goblin) {
        if (!$goblin->isDead()) {
            return false;
        }
    }

    return true;
}

/**
 * @param int $rounds
 * @param array|Fighter[] $fighters
 * @return int
 */
function getFinalScore(int $rounds, array $fighters): int
{
    $totalHitPoints = 0;
    foreach ($fighters as $fighter) {
        $totalHitPoints += $fighter->hitPoints;
    }

    return $totalHitPoints * $rounds;
}

/**
 * @param array $map
 * @param array $fighters
 */
function printBoard($map, $fighters)
{
    $rows = count($map);
    $cols = count($map[0]);

    $out = '';

    for ($row = 0; $row < $rows; $row++) {
        for ($col = 0; $col < $cols; $col++) {
            $out .= $map[$row][$col];
        }
        $out .= "\n";
    }

    $out .= "\n";
    foreach ($fighters as $fighter) {
        $out .= (($fighter->type === Fighter::TYPE_ELF ? 'E' : 'G') . ': ' . $fighter->hitPoints . "\n");
    }

    print $out;
}

/**
 * @param int $rounds
 * @param array|Fighter[] $fighters
 */
function printProgress(int $rounds, array $fighters)
{
    static $start;
    if ($start === null) {
        $start = time();
    }

    $elves = $elvesHp = $goblins = $goblinsHp = 0;
    foreach ($fighters as $fighter) {
        if ($fighter->type === Fighter::TYPE_ELF) {
            $elves++;
            $elvesHp += $fighter->hitPoints;
        } else {
            $goblins++;
            $goblinsHp += $fighter->hitPoints;
        }
    }

    $elapsed = time() - $start;

    print "{$elapsed}s - After $rounds rounds, $elves elves have $elvesHp HP left and $goblins goblins have $goblinsHp HP left\n";
}

function debug(string $message)
{
    if (DEBUG) {
        print "Debug: $message\n";
    }
}

class CoordBase
{
    const MAX_DISTANCE = 9999999;

    public $row;
    public $col;

    public function __construct(int $row, int $col)
    {
        $this->row = $row;
        $this->col = $col;
    }

    /**
     * @return string
     */
    public function getCoordAsString(): string
    {
        return $this->row . ',' . $this->col;
    }
}

class Fighter extends CoordBase
{
    const INITIAL_ATTACK_POWER = 3;
    const INITIAL_HIT_POINTS = 200;
    const TYPE_ELF = 0;
    const TYPE_GOBLIN = 1;

    public $type;
    public $hitPoints;
    public $attackPower;
    public $id;

    public function __construct(int $row, int $col, int $type)
    {
        static $id = 0;

        parent::__construct($row, $col);

        $this->type = $type;
        $this->hitPoints = self::INITIAL_HIT_POINTS;

        if ($type === Fighter::TYPE_ELF) {
            $this->attackPower = ELF_ATTACK_POWER;
        } else {
            $this->attackPower = self::INITIAL_ATTACK_POWER;
        }

        $this->id = $id;
        $id++;
    }

    /**
     * @param array $map
     * @param array|Fighter[] $fighters
     */
    public function doMove(array &$map, array &$fighters)
    {
        $enemies = $this->getEnemies($fighters);
        if (!$this->getTargetInRange($enemies)) {
            if ($destination = $this->chooseDestination($map, $enemies)) {
                $this->moveTowardsDestination($map, $destination);
            }
        }

        if ($target = $this->getTargetInRange($enemies)) {
            $this->attackTarget($target);
        }
    }

    /**
     * @param array|Fighter[] $fighters
     * @return array|Fighter[]
     */
    private function getEnemies(array $fighters): array
    {
        $enemies = [];
        foreach ($fighters as $fighter) {
            if ($fighter->type !== $this->type && !$fighter->isDead()) {
                $enemies[] = $fighter;
            }
        }

        return $enemies;
    }

    /**
     * Get the first target in range
     * @param array|Fighter[] $targets
     * @return Fighter|null
     */
    private function getTargetInRange(array $targets)
    {
        /** @var Fighter[] $inRange */
        $inRange = [];
        foreach ($targets as $target) {
            if ($this->getSimpleDistanceToTarget($target) === 1) {
                $inRange[] = $target;
            }
        }

        if (count($inRange) === 0) {
            // Nobody is in range
            return null;
        }

        $minHitPoints = self::INITIAL_HIT_POINTS;
        foreach ($inRange as $target) {
            if ($target->hitPoints < $minHitPoints) {
                $minHitPoints = $target->hitPoints;
            }
        }

        // Now remove any with more than the min hit points
        foreach ($inRange as $index => $target) {
            if ($target->hitPoints > $minHitPoints) {
                unset($inRange[$index]);
            }
        }

        // Return the first one, sorted in reading order
        sortByReadOrder($inRange);
        return reset($inRange);
    }

    /**
     * Move towards the nearest target
     * @param array $map
     * @param array|Fighter[] $targets
     * @return Coord|null
     */
    private function chooseDestination(array $map, array $targets)
    {
        /** @var Coord[] $possibleDestinations */
        $possibleDestinations = [];
        foreach($targets as $target) {
            $possibleDestinations = array_merge($possibleDestinations, Coord::getAdjacentSpaces($map, $target));
        }

        foreach ($possibleDestinations as $possibleDestination) {
            $possibleDestination->setDistance($map, $this->row, $this->col);
        }

        $closestDistance = Coord::MAX_DISTANCE;
        foreach ($possibleDestinations as $possibleDestination) {
            if ($possibleDestination->distance < $closestDistance) {
                $closestDistance = $possibleDestination->distance;
            }
        }

        if ($closestDistance === Coord::MAX_DISTANCE) {
            // we've not found any possible destinations = can't move
            return null;
        }

        // Remove all except the closest destinations
        foreach ($possibleDestinations as $index => $possibleDestination) {
            if ($possibleDestination->distance !== $closestDistance) {
                unset($possibleDestinations[$index]);
            }
        }

        // Get the first of the closest
        sortByReadOrder($possibleDestinations);
        debug('Desination chosen');
        return reset($possibleDestinations);
    }

    /**
     * @param array $map
     * @param Coord $destination
     */
    private function moveTowardsDestination(array &$map, Coord $destination)
    {
        debug('Moving towards destination');
        // Clear the current space on the map
        $map[$this->row][$this->col] = ' ';

        // There may be multiple possible first steps - choose the first by read order
        $steps = $this->getFirstStepsToDestination($map, $destination);
        sortByReadOrder($steps);
        $firstStep = reset($steps);

        $this->row = $firstStep->row;
        $this->col = $firstStep->col;

        // Update the new space on the map
        $map[$this->row][$this->col] = ($this->type === Fighter::TYPE_ELF ? 'E' : 'G');
    }

    /**
     * @param array $map
     * @param Coord $destination
     * @return array|Coord[]
     */
    private function getFirstStepsToDestination(array $map, Coord $destination): array
    {
        debug('Getting first steps');
        $startNode = new Node($this->row, $this->col);
        /** @var Node[] $currentNodes */
        $currentNodes = [$startNode];
        $exploredNodes = [$startNode->getCoordAsString()];

        // We already know the max distance to travel
        for ($i = 0; $i < $destination->distance; $i++) {
            debug("Getting first step - loop $i of " . $destination->distance . '. Total nodes: ' . count($exploredNodes));
            /** @var Node[] $nextSteps */
            $nextSteps = [];
            foreach ($currentNodes as $node) {
                foreach (Coord::getAdjacentSpaces($map, $node) as $step) {
                    if (!array_key_exists($step->getCoordAsString(), $exploredNodes)) {
                        $nextSteps[] = Node::createFromCoord($step, $node);
                    }
                }
            }

            // Add new steps to explored nodes
            foreach ($nextSteps as $nextStep) {
                $exploredNodes[$nextStep->getCoordAsString()] = '';
            }

            $currentNodes = $nextSteps;
        }

        // Now remove any nodes which don't finish at the target location
        foreach ($currentNodes as $index => $node) {
            if ($node->getCoordAsString() !== $destination->getCoordAsString()) {
                unset($currentNodes[$index]);
            }
        }

        // We now have all the paths which are the required distance and finish at the target destination
        $firstSteps = [];

        foreach ($currentNodes as $node) {
            while ($node->parentNode) {
                if ($node->parentNode->getCoordAsString() === $startNode->getCoordAsString()) {
                    $firstSteps[] = Coord::createFromNode($node);
                }

                $node = $node->parentNode;
            }
        }

        debug('Found first steps');
        return $firstSteps;
    }

    /**
     * Attack a target
     * @param Fighter $target
     */
    private function attackTarget(Fighter $target)
    {
        $target->hitPoints -= $this->attackPower;
    }

    /**
     * Is this fighter dead?
     * @return bool
     */
    public function isDead(): bool
    {
        return $this->hitPoints <= 0;
    }

    /**
     * Get a simple straight distance to a target, irrespective of whether it can actually be reached
     * @param Fighter $target
     * @return int
     */
    private function getSimpleDistanceToTarget(Fighter $target): int
    {
        return abs($this->row - $target->row) + abs($this->col - $target->col);
    }
}

class Coord extends CoordBase
{
    const MAX_DISTANCE = 9999999;

    public $distance;

    public static function createFromNode(Node $node): Coord
    {
        return new Coord($node->row, $node->col);
    }

    public function setDistance(array $map, int $startRow, int $startCol)
    {
        // work out distance from coord to target using Djikstra's algorithm
        $explored = [];

        /** @var Coord[] $currentNodes */
        $currentNodes = [new Coord($startRow, $startCol)];
        $explored[] = $this->getCoordAsString();
        $distance = 0;

        while (true) {
            $distance++;
            $newNodes = [];
            foreach ($currentNodes as $node) {
                foreach (Coord::getAdjacentSpaces($map, $node) as $newNode) {
                    if ($newNode->row === $this->row && $newNode->col === $this->col) {
                        // This is the first time we've reached the target, so this is the shortest distance
                        $this->distance = $distance;
                        return;
                    }

                    if (!in_array($newNode->getCoordAsString(), $explored)) {
                        $newNodes[] = $newNode;
                        $explored[] = $newNode->getCoordAsString();
                    }
                }
            }

            if (count($newNodes) === 0) {
                // we found no new unexplored nodes, and we've not reached the target
                $this->distance = self::MAX_DISTANCE;
                return;
            }

            // Set our new nodes to be the current nodes, and repeat
            $currentNodes = $newNodes;
        }
    }

    /**
     * @param array $map
     * @param CoordBase $target
     * @return array|Coord[]
     */
    public static function getAdjacentSpaces(array $map, CoordBase $target): array
    {
        $freeSpaces = [];
        if ($map[$target->row][$target->col - 1] === ' ') {
            $freeSpaces[] = new Coord($target->row, $target->col - 1);
        }

        if ($map[$target->row][$target->col + 1] === ' ') {
            $freeSpaces[] = new Coord($target->row, $target->col + 1);
        }

        if ($map[$target->row - 1][$target->col] === ' ') {
            $freeSpaces[] = new Coord($target->row - 1, $target->col);
        }

        if ($map[$target->row + 1][$target->col] === ' ') {
            $freeSpaces[] = new Coord($target->row + 1, $target->col);
        }

        return $freeSpaces;
    }
}

class Node extends CoordBase
{
    public $parentNode;
    public $childNode;

    public function __construct(int $row, int $col, Node $parentNode = null)
    {
        parent::__construct($row, $col);

        $this->parentNode = $parentNode;
        if ($parentNode instanceof Node) {
            $parentNode->childNode = $this;
        }
    }

    public static function createFromCoord(Coord $coord, Node $parentNode = null)
    {
        return new Node($coord->row, $coord->col, $parentNode);
    }
}