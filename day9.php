<?php

print "Part 1: " . part1() . "\n";
// print "Part 2: " . part2() . "\n";

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
    $board = [];
    $scores = [];
    for ($elf = 0; $elf < $elves; $elf++) {
        $scores[$elf] = 0;
    }

    // Initialise the board with elf 0 playing marble 0
    $board[0] = 0;
    $currentMarble = 0;
    $currentElf = 1;

    // Play game with the rest of the marbles
    for ($marble = 1; $marble <= $lastMarble; $marble++) {
        if ($marble % 23 === 0) {
            $result = doMove2($board, $marble, $currentMarble);
            $scores[$currentElf] += $result['score'];
            $currentMarble = $result['currentMarble'];
        } else {
            $currentMarble = doMove1($board, $marble, $currentMarble);
        }

        $currentElf = getNextElf($currentElf, $elves);

        // debugging:
        // showBoard($board);
        if ($marble % 1000 === 0) {
            print "Done $marble marbles out of $lastMarble\n";
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

function doMove1(array &$board, int $marbleToPlay, int $currentMarble)
{
    $newPosition = getOffset($board, $currentMarble,1);
    insertMarble($board, $marbleToPlay, $newPosition);
    return $newPosition;
}

function doMove2(array &$board, int $marbleToPlay, $currentMarble): array
{
    $newPosition = getOffset($board, $currentMarble, -8);
    $score = $board[$newPosition] + $marbleToPlay;

    // Remove the marble from the new position
    $lastPosition = count($board) - 1;
    for ($i = $newPosition; $i < $lastPosition; $i++) {
        $board[$i] = $board[$i+1];
    }
    unset ($board[$lastPosition]);

    return [
        'currentMarble' => $newPosition,
        'score' => $score,
    ];
}

function getOffset(array $board, int $currentPosition, int $offset): int
{
    // Find offset position
    $newPosition = $currentPosition + $offset;

    // Wrap around if we've gone past either end of the board
    $maxPosition = count($board) - 1;
    if ($newPosition > $maxPosition) {
        $newPosition = $newPosition - count($board);
    }

    if ($newPosition < 0) {
        $newPosition = $newPosition + count($board);
    }

    return $newPosition + 1;
}

function insertMarble(array &$board, int $newMarble, int $newPosition)
{
    // shuffle up marbles after newPosition
    $pos = count($board);
    while ($pos > $newPosition) {
        $board[$pos] = $board[$pos - 1];
        $pos--;
    }

    $board[$newPosition] = $newMarble;
}

function showBoard(array $board)
{
    $out = '';
    foreach ($board as $item) {
        $out .= substr('   ' . $item, -3) . ' ';
    }

    print $out . "\n";
}