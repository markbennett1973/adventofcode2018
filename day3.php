<?php

print "Part 1: " . part1() . " duplicates found\n";
print "Part 2: ID" . part2() . " does not overlap\n";

function part1(): int
{
    $claims = getClaims();
    $fabric = [];

    foreach ($claims as $claim) {
        addClaimToFabric($claim, $fabric);
    }

    // Count duplicates
    $duplicates = 0;
    foreach ($fabric as $row => $columns) {
        foreach ($columns as $column => $count) {
            if ($count > 1) {
                $duplicates++;
            }
        }
    }

    return $duplicates;
}

function part2(): int
{
    $claims = getClaims();
    $fabric = $map = [];

    foreach ($claims as $claim) {
        addClaimToFabric($claim, $fabric);
    }

    // Now check if claim is unique
    foreach ($claims as $claim) {
        if (isClaimUnique($claim, $fabric)) {
            return $claim->id;
        }
    }
    return 0;
}

/**
 * @return array|Claim[]
 */
function getClaims(): array
{
    $claims = [];
    $claimDefinitions = explode("\n", file_get_contents('day3-input.txt'));
    foreach ($claimDefinitions as $claimDefinition) {
        if (trim($claimDefinition)) {
            $claims[] = new Claim($claimDefinition);
        }
    }

    return $claims;
}

function addClaimToFabric(Claim $claim, array &$fabric)
{
    $row = $claim->top;

    while ($row < $claim->bottom) {
        $column = $claim->left;
        while ($column < $claim->right) {
            $fabric[$row][$column]++;
            $column++;
        }
        $row++;
    }
}

function isClaimUnique(Claim $claim, array $fabric): bool
{
    $maxOverlap = 0;

    $row = $claim->top;

    while ($row < $claim->bottom) {
        $column = $claim->left;
        while ($column < $claim->right) {
            $overlap = $fabric[$row][$column];

            if ($overlap > $maxOverlap) {
                $maxOverlap = $overlap;
            }

            $column++;
        }
        $row++;
    }

    return $maxOverlap === 1;
}

class Claim
{
    public $id;
    public $left;
    public $top;
    public $height;
    public $width;
    public $right;
    public $bottom;

    /**
     * Claim constructor.
     * @param string $definition
     *   e.g. #1 @ 861,330: 20x10
     */
    public function __construct(string $definition)
    {
        $definition = str_replace([',', ':', 'x'], ' ', $definition);
        $parts = explode(' ', $definition);

        $this->id = str_replace('#', '', $parts[0]);
        $this->left = $parts[2];
        $this->top = $parts[3];
        $this->width = $parts[5];
        $this->height = $parts[6];
        $this->right = $this->left + $this->width;
        $this->bottom = $this->top + $this->height;
    }
}