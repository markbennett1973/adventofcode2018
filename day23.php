<?php

// const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day23-input.txt';

print "Part 1: " . part1() . "\n";
// print "Part 2: " . part2() . "\n";

function part1(): int
{
    $bots = getBots();
    $strongestBot = findStrongestBot($bots);
    return countBotsInRange($strongestBot, $bots);
}

function part2(): int
{
    $bots = getBots();
    $extents = getExtents($bots);
    $results = getBotsInRangeOfAllCoords($bots, $extents);
    $bestResults = getBestResults($results);
    return getClosestResult($bestResults);
}

/**
 * @return array|Bot[]
 */
function getBots(): array
{
    $bots = [];
    foreach (explode("\n", file_get_contents(INPUT_FILE)) as $line) {
        if ($line) {
            $bots[] = Bot::createFromString($line);
        }
    }

    return $bots;
}

/**
 * @param array|Bot[] $bots
 * @return Bot
 */
function findStrongestBot(array $bots): Bot
{
    $maxStrength = 0;
    $strongestBot = null;

    foreach ($bots as $bot) {
        if ($bot->strength > $maxStrength) {
            $strongestBot = $bot;
            $maxStrength = $bot->strength;
        }
    }

    return $strongestBot;
}

/**
 * @param Bot $sourceBot
 * @param array|Bot[] $bots
 * @return int
 */
function countBotsInRange(Bot $sourceBot, array $bots): int
{
    $botsInRange = 0;
    foreach ($bots as $bot) {
        if ($sourceBot->isCoordInRange($bot)) {
            $botsInRange++;
        }
    }

    return $botsInRange;
}

/**
 * @param array|Bot[] $bots
 * @return array
 */
function getExtents(array $bots): array
{
    $minX = $maxX = $bots[0]->x;
    $minY = $maxY = $bots[0]->y;
    $minZ = $maxZ = $bots[0]->z;

    foreach ($bots as $bot) {
        $minX = $bot->x < $minX ? $bot->x : $minX;
        $maxX = $bot->x > $maxX ? $bot->x : $maxX;

        $minY = $bot->y < $minY ? $bot->y : $minY;
        $maxY = $bot->y > $maxY ? $bot->y : $maxY;

        $minZ = $bot->z < $minZ ? $bot->z : $minZ;
        $maxZ = $bot->z > $maxZ ? $bot->z : $maxZ;
    }

    return compact('minX', 'maxX', 'minY', 'maxY', 'minZ', 'maxZ');
}

/**
 * @param array|Bot[] $bots
 * @param array $extents
 * @return array
 */
function getBotsInRangeOfAllCoords(array $bots, array $extents): array
{
    $results = [];
    for ($x = $extents['minX']; $x <= $extents['maxX']; $x++) {
        for ($y = $extents['minY']; $y <= $extents['maxY']; $y++) {
            for ($z = $extents['minZ']; $z <= $extents['maxZ']; $z++) {
                $coord = new Coord($x, $y, $z);
                $botsInRange = 0;
                foreach ($bots as $bot) {
                    if ($bot->isCoordInRange($coord)) {
                        $botsInRange++;
                    }
                }

                $results[] = compact('coord', 'botsInRange');
            }
        }
    }

    return $results;
}

/**
 * @param array $results
 * @return array
 */
function getBestResults(array $results): array
{
    // Find the highest value
    $mostBots = 0;
    foreach ($results as $result) {
        if ($result['botsInRange'] > $mostBots) {
            $mostBots = $result['botsInRange'];
        }
    }

    // Then get coords with that value
    $bestCoords = [];
    foreach ($results as $result) {
        if ($result['botsInRange'] === $mostBots) {
            $bestCoords[] = $result['coord'];
        }
    }

    return $bestCoords;
}

/**
 * @param array|Coord[] $results
 * @return int
 */
function getClosestResult(array $results): int
{
    $closestDistance = null;

    $source = new Coord(0, 0, 0);
    foreach ($results as $result) {
        $distance = $result->getDistanceToCoord($source);
        if ($closestDistance === null || $distance < $closestDistance) {
            $closestDistance = $distance;
        }
    }

    return $closestDistance;
}

class Coord
{
    public $x;
    public $y;
    public $z;

    public function __construct(int $x, int $y, int $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * Get distance from this coord to another one
     *
     * @param Coord $coord
     * @return int
     */
    public function getDistanceToCoord(Coord $coord): int
    {
        return abs($this->x - $coord->x)
            + abs($this->y - $coord->y)
            + abs($this->z - $coord->z);
    }
}

class Bot extends Coord
{
    public $strength;

    public function __construct(int $x, int $y, int $z, int $strength)
    {
        parent::__construct($x, $y, $z);
        $this->strength = $strength;
    }

    /**
     * @param string $string
     * pos=<1,3,1>, r=1
     * @return Bot
     * @throws Exception
     */
    public static function createFromString(string $string): Bot
    {
        preg_match('/pos=<([\-0-9,]+)>, r=([\-0-9]+)/', $string, $matches);
        if (count($matches) !== 3) {
            throw new \Exception('Bad input string: ' . $string);
        }

        $coords = explode(',', $matches[1]);
        if (count($coords) !== 3) {
            throw new \Exception('Bad input string: ' . $string);
        }

        return new Bot($coords[0], $coords[1], $coords[2], $matches[2]);
    }

    /**
     * Is another bot within range of this one?
     *
     * @param Coord $coord
     * @return bool
     * @internal param Bot $bot
     */
    public function isCoordInRange(Coord $coord): bool
    {
        $distance = $this->getDistanceToCoord($coord);
        if ($distance <= $this->strength) {
            return true;
        }

        return false;
    }
}

