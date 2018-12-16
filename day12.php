<?php

const INPUT_FILE = 'day12-input.txt';
const GENERATIONS = 20;
const BIG_GENERATIONS = 50000000000;

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $pots = getInitialState();
    $rules = getRules();
    printPots($pots);

    for ($i = 0; $i < GENERATIONS; $i++) {
        $pots = getNextGeneration($pots, $rules);
        printPots($pots);
    }

    return getSumOfPots($pots);
}

function part2(): int
{
    $pots = getInitialState();
    $rules = getRules();

    for ($i = 0; $i < BIG_GENERATIONS; $i++) {
        $pots = getNextGeneration($pots, $rules);

        if ($i % 100 === 0) {
            print "Done $i = " . round($i * 100/ BIG_GENERATIONS, 2) . "%\n";
        }
    }

    return getSumOfPots($pots);
}

function getInitialState(): array
{
    $lines = explode("\n", file_get_contents(INPUT_FILE));
    $state = str_replace('initial state: ', '', $lines[0]);

    $pots = [];
    $numPots = strlen($state);
    for ($i = 0; $i < $numPots; $i++) {
        $pots[$i] = $state[$i] === '#';
    }

    return $pots;
}

function getRules(): array
{
    $rules = [];

    $lines = explode("\n", file_get_contents(INPUT_FILE));
    $lines = array_slice($lines, 2);
    foreach ($lines as $line) {
        if (trim($line)) {
            $rules[] = new Rule($line);
        }
    }

    return $rules;
}

/**
 * @param array $pots
 * @param array|Rule[] $rules
 * @return array
 */
function getNextGeneration(array $pots, array $rules): array
{
    $pots = extendPots($pots);

    $nextGenPots = [];
    foreach ($pots as $index => $pot) {
        $nextGenPots[$index] = false;
        foreach ($rules as $rule) {
            if ($rule->doesRuleMatch($pots, $index)) {
                $nextGenPots[$index] = $rule->outcome;
                continue;
            }
        }
    }

    return $nextGenPots;
}

function extendPots(array $pots): array
{
    $minKey = min(array_keys($pots));
    $maxKey = max(array_keys($pots));

    $pots[$minKey - 1] = false;
    $pots[$minKey - 2] = false;
    $pots[$maxKey + 1] = false;
    $pots[$maxKey + 2] = false;

    return $pots;
}

function printPots(array $pots)
{
    $out = '';
    for ($i = -3; $i <= 35; $i++) {
        $out .= array_key_exists($i, $pots) ? ($pots[$i] ? '#' : '.') : '.';
    }

    print $out . "\n";
}

function getSumOfPots(array $pots): int
{
    $sum = 0;
    foreach ($pots as $index => $value) {
        if ($value) {
            $sum += $index;
        }
    }

    return $sum;
}

class Rule
{
    private $left1;
    private $left2;
    private $current;
    private $right1;
    private $right2;
    public $outcome;

    public function __construct(string $line)
    {
        $this->left2 = $line[0] === '#';
        $this->left1 = $line[1] === '#';
        $this->current = $line[2] ==='#';
        $this->right1 = $line[3] === '#';
        $this->right2 = $line[4] === '#';
        $this->outcome = $line[9] === '#';
    }

    public function doesRuleMatch(array $pots, int $targetPot): bool
    {
        if ($this->getPotValue($pots, $targetPot - 2) !== $this->left2) {
            return false;
        }

        if ($this->getPotValue($pots, $targetPot - 1) !== $this->left1) {
            return false;
        }

        if ($this->getPotValue($pots, $targetPot) !== $this->current) {
            return false;
        }

        if ($this->getPotValue($pots, $targetPot + 1) !== $this->right1) {
            return false;
        }

        if ($this->getPotValue($pots, $targetPot + 2) !== $this->right2) {
            return false;
        }

        return true;
    }

    private function getPotValue(array $pots, int $position): bool
    {
        if (array_key_exists($position, $pots)) {
            return $pots[$position];
        }

        return false;
    }
}