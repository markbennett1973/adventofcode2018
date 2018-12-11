<?php

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    return playGame(486, 70833);
}

function part2(): int
{
    return playGame(486, 7083300);
}

function playGame(int $elves, int $lastMarble): int
{
    $scores = [];
    for ($elf = 0; $elf < $elves; $elf++) {
        $scores[$elf] = 0;
    }

    // Initialise the board with elf 0 playing marble 0
    $currentMarble = new Marble(0);
    $currentMarble->nextMarble = $currentMarble;
    $currentMarble->previousMarble = $currentMarble;
    $currentElf = 1;

    // Just for debugging...
    $firstMarble = $currentMarble;

    // Play game with the rest of the marbles
    for ($marbleScore = 1; $marbleScore <= $lastMarble; $marbleScore++) {
        if ($marbleScore % 23 === 0) {
            $result = doMove2($marbleScore, $currentMarble);
            $scores[$currentElf] += $result['score'];
            $currentMarble = $result['currentMarble'];
        } else {
            $currentMarble = doMove1($marbleScore, $currentMarble);
        }

        $currentElf = getNextElf($currentElf, $elves);

        // debugging:
        // showBoard($firstMarble);
        if ($marbleScore % 10000 === 0) {
            $mem = round(memory_get_usage() / (1024 * 1024), 1);
            print "Done $marbleScore marbles - $mem MB used\n";
        }
    }

    return max($scores);
}

function getNextElf(int $currentElf, int $numberOfElves): int
{
    $currentElf++;
    if ($currentElf === $numberOfElves) {
        $currentElf = 0;
    }

    return $currentElf;
}

/**
 * Adds a new marble with the specified score; returns the new marble
 * @param int $marbleScore
 * @param Marble $currentMarble
 * @return Marble
 */
function doMove1(int $marbleScore, Marble $currentMarble): Marble
{
    $targetMarble = getMarbleOffset($currentMarble, 1);
    $next = $targetMarble->nextMarble;
    $newMarble = new Marble($marbleScore);

    // Slot in the new marble after the target marble
    $newMarble->previousMarble = $targetMarble;
    $newMarble->nextMarble = $next;
    $targetMarble->nextMarble = $newMarble;
    $next->previousMarble = $newMarble;

    return $newMarble;
}

/**
 * Remove the marble 7 left of the current marble
 * @param int $marbleScore
 * @param Marble $currentMarble
 * @return array
 *   score => score
 *   currentMarble = new current marble
 */
function doMove2(int $marbleScore, Marble $currentMarble): array
{
    $targetMarble = getMarbleOffset($currentMarble, -7);

    // Remove the target marble from the list
    $targetMarble->nextMarble->previousMarble = $targetMarble->previousMarble;
    $targetMarble->previousMarble->nextMarble = $targetMarble->nextMarble;

    return [
        'score' => $targetMarble->score + $marbleScore,
        'currentMarble' => $targetMarble->nextMarble,
    ];
}

/**
 * Get the marble a specified offset from the current marble
 * @param Marble $currentMarble
 * @param int $offset
 * @return Marble
 */
function getMarbleOffset(Marble $currentMarble, int $offset): Marble
{
    if ($offset > 0) {
        for ($i = 0; $i < $offset; $i++) {
            $currentMarble = $currentMarble->nextMarble;
        }
    } else {
        for ($i = 0; $i < abs($offset); $i++) {
            $currentMarble = $currentMarble->previousMarble;
        }
    }

    return $currentMarble;
}

function showBoard(Marble $firstMarble)
{
    $out = '';
    $currentMarble = $firstMarble;
    while ($currentMarble->nextMarble !== $firstMarble) {
        $out .= substr('   ' . $currentMarble->score, -3) . ' ';
        $currentMarble = $currentMarble->nextMarble;
    }

    // Add the last marble
    $out .= substr('   ' . $currentMarble->score, -3) . ' ';

    print $out . "\n";
}

class Marble
{
    public $score;
    /** @var  Marble */
    public $nextMarble;
    /** @var  Marble */
    public $previousMarble;

    public function __construct(int $score)
    {
        $this->score = $score;
    }
}