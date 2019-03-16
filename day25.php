<?php

// 575 is too high

//const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day25-input.txt';

$instructionRegister = null;

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $stars = getStars();
    $constellations = makeConstellations($stars);
    mergeConstellations($constellations);
    return count($constellations);
}

function part2(): int
{
    return 0;
}

/**
 * @return array|Star[]
 */
function getStars(): array
{
    $stars = [];

    foreach (explode("\n", file_get_contents(INPUT_FILE)) as $line) {
        if ($star = Star::createFromPosition($line)) {
            $stars[] = $star;
        }
    }

    return $stars;
}

/**
 * @param array|Star[] $stars
 * @return array|Constellation[]
 */
function makeConstellations(array $stars): array
{
    /** @var Constellation[] $constellations */
    $constellations = [];

    foreach ($stars as $star) {
        $added = false;

        // See if the star can join an existing constellation
        foreach ($constellations as $constellation) {
            if ($constellation->canStarJoinConstellation($star)) {
                $constellation->addStar($star);
                $added = true;
                break;
            }
        }

        if (!$added) {
            // No existing ones are close enough - create a new one for this star
            $constellation = new Constellation();
            $constellation->addStar($star);
            $constellations[] = $constellation;
        }
    }

    return $constellations;
}

/**
 * @param array|Constellation[] $constellations
 */
function mergeConstellations(array &$constellations)
{
    $foundMerge = true;

    while ($foundMerge) {
        print "Checking for merges - " . count($constellations) . " constellations\n";
        $foundMerge = false;
        foreach ($constellations as $index1 => $constellation1) {
            // See if it can merge with any others
            foreach ($constellations as $index2 => $constellation2) {
                if ($index1 !== $index2 && $constellation1->canConstellationMerge($constellation2)) {
                    // merge 1 into 2
                    $constellation1->mergeConstellation($constellation2);
                    unset($constellations[$index2]);
                    $foundMerge = true;
                    break 2;
                }
            }
        }
    }
}

class Star
{
    private $a;
    private $b;
    private $c;
    private $d;

    public static function createFromPosition(string $position): ?Star
    {
        $parts = explode(',', $position);
        if (count($parts) !== 4) {
            return null;
        }

        $star = new Star();
        $star->a = $parts[0];
        $star->b = $parts[1];
        $star->c = $parts[2];
        $star->d = $parts[3];

        return $star;
    }

    public function getDistanceToStar(Star $star): int
    {
        return abs($star->a - $this->a)
            + abs($star->b - $this->b)
            + abs($star->c - $this->c)
            + abs($star->d - $this->d);
    }
}

class Constellation
{
    const MAX_DISTANCE = 3;

    /** @var Star[] */
    private $stars;

    public function canStarJoinConstellation(Star $newStar): bool
    {
        foreach ($this->stars as $star) {
            if ($star->getDistanceToStar($newStar) <= self::MAX_DISTANCE) {
                return true;
            }
        }

        return false;
    }

    public function addStar(Star $star)
    {
        $this->stars[] = $star;
    }

    /**
     * @return array|Star[]
     */
    public function getStars(): array
    {
        return $this->stars;
    }

    public function canConstellationMerge(Constellation $constellation): bool
    {
        // Constellations can merge if any star from 1 is close to any star from 2
        foreach ($this->getStars() as $star1) {
            foreach ($constellation->getStars() as $star2) {
                if ($star1->getDistanceToStar($star2) <= self::MAX_DISTANCE) {
                    return true;
                }
            }
        }

        return false;
    }

    public function mergeConstellation(Constellation $constellation)
    {
        $this->stars = array_merge($this->stars, $constellation->getStars());
    }
}
