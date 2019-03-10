<?php

//const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day24-input.txt';

// print "Part 1: " . part1() . "\n";
part2();

function part1(): int
{
    $groups = getGroups();
    $immuneGroups = $groups['immune'];
    $infectionGroups = $groups['infection'];
    doFight($immuneGroups, $infectionGroups);
    return getWinningScore($immuneGroups, $infectionGroups);
}

function part2()
{
    $groups = getGroups();
    $immuneGroups = $groups['immune'];
    $infectionGroups = $groups['infection'];
    applyBoost($immuneGroups, 34);
    doFight($immuneGroups, $infectionGroups);
    printWinner($immuneGroups, $infectionGroups);
}

function getGroups(): array
{
    $groups = [];
    $groupType = 'none';

    foreach(explode("\n", file_get_contents(INPUT_FILE)) as $line) {
        if ($line === 'Immune System:') {
            $groupType = 'immune';
        }

        if ($line === 'Infection:') {
            $groupType = 'infection';
        }

        if ($group = Group::create($line)) {
            $groups[$groupType][] = $group;
        }
    }

    return $groups;
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 */
function doFight(array &$immuneGroups, array &$infectionGroups)
{
    chooseTargets($immuneGroups, $infectionGroups);

    while (!isFightOver($immuneGroups, $infectionGroups)) {
        printStatus($immuneGroups, $infectionGroups);
        chooseTargets($immuneGroups, $infectionGroups);
        attackTargets($immuneGroups, $infectionGroups);

        if (count($immuneGroups) === 1 && count($infectionGroups) === 1) {
            $x = 1;
        }
    }
}

/**
 * @param array|Group[] $groups
 * @param int $boost
 */
function applyBoost(array &$groups, int $boost)
{
    foreach ($groups as $group) {
        $group->attackDamage += $boost;
    }
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 */
function printStatus(array $immuneGroups, array $infectionGroups)
{
    static $count = 0;
    $count++;

    if ($count > 2000) {
        $x = 1;
    }

    $message = 'Immune units: ';
    foreach ($immuneGroups as $immuneGroup) {
        $message .= $immuneGroup->units . ', ';
    }

    $message .= 'Infection units: ';
    foreach ($infectionGroups as $infectionGroup) {
        $message .= $infectionGroup->units . ', ';
    }

    print "$count - $message\n";
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 */
function printWinner(array $immuneGroups, array $infectionGroups)
{
    $unitsLeft = 0;
    $winner = '';

    if (count($immuneGroups) > 0 && count($infectionGroups) > 0) {
        print "It's a draw\n";
        return;
    }

    if (count($immuneGroups) > 0) {
        $winner = 'immune';
        foreach ($immuneGroups as $group) {
            $unitsLeft += $group->units;
        }
    }

    if (count($infectionGroups) > 0) {
        $winner = 'infection';
        foreach ($infectionGroups as $group) {
            $unitsLeft += $group->units;
        }
    }

    print "Winner: $winner, units left = $unitsLeft\n";
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 * @return bool
 */
function isFightOver(array &$immuneGroups, array &$infectionGroups): bool
{
    if (count($immuneGroups) <= 0) {
        return true;
    }

    if (count($infectionGroups) <= 0) {
        return true;
    }

    // Check for a draw - no groups have any potential targets
    $targetFound = false;
    foreach ($immuneGroups as $group) {
        if ($group->target) {
            $targetFound = true;
        }
    }

    foreach ($infectionGroups as $group) {
        if ($group->target) {
            $targetFound = true;
        }
    }

    // If there were no targets at all, then the fight is over
    if (!$targetFound) {
        return true;
    }

    return false;
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 */
function chooseTargets(array &$immuneGroups, array &$infectionGroups)
{
    resetTargets($immuneGroups);
    resetTargets($infectionGroups);

    sortGroupsByEffectivePower($immuneGroups);
    foreach ($immuneGroups as $group) {
        chooseTarget($group, $infectionGroups);
    }

    sortGroupsByEffectivePower($infectionGroups);
    foreach ($infectionGroups as $group) {
        chooseTarget($group, $immuneGroups);
    }
}

/**
 * @param array|Group[] $groups
 */
function resetTargets(array $groups)
{
    foreach ($groups as $group) {
        $group->target = null;
        $group->isTargetted = false;
    }
}

/**
 * @param Group $group
 * @param array|Group[] $potentialTargets
 */
function chooseTarget(Group $group, array $potentialTargets)
{
    $maxDamage = 0;
    /** @var Group|null $targettedGroup */
    $targettedGroup = null;

    foreach ($potentialTargets as $potentialTarget) {
        // Don't target a group which has already been targetted
        if ($potentialTarget->isTargetted) {
            continue;
        }

        $damage = $group->calculateDamage($potentialTarget);
        if ($damage === 0) {
            continue;
        }

        if ($damage === $maxDamage) {
            // we have a tie for max damage - see if this target exceeds the current target
            if ($potentialTarget->getEffectivePower() > $targettedGroup->getEffectivePower()) {
                $targettedGroup = $potentialTarget;
            } elseif ($potentialTarget->getEffectivePower() === $targettedGroup->getEffectivePower()) {
                if ($potentialTarget->initiative > $targettedGroup->initiative) {
                    $targettedGroup = $potentialTarget;
                }
            }
        } elseif ($damage > $maxDamage) {
            $maxDamage = $damage;
            $targettedGroup = $potentialTarget;
        }
    }

    if ($targettedGroup) {
        $targettedGroup->isTargetted = true;
        $group->target = $targettedGroup;
    }
}

/**
 * @param array|Group[] $groups
 */
function sortGroupsByEffectivePower(array &$groups)
{
    usort($groups, function (Group $a, Group $b) {
        // Use initiative to resolve ties in effective power
        if ($a->getEffectivePower() === $b->getEffectivePower()) {
            if ($a->initiative === $b->initiative) {
                return 0;
            }

            return ($a->initiative > $b->initiative) ? -1 : 1;
        }

        if ($a->getEffectivePower() === $b->getEffectivePower()) {
            return 0;
        }

        return ($a->getEffectivePower() > $b->getEffectivePower()) ? -1 : 1;
    });
}

/**
 * @param array|Group[] $groups
 */
function sortGroupsByInitiative(array &$groups)
{
    usort($groups, function (Group $a, Group $b) {
        if ($a->initiative === $b->initiative) {
            return 0;
        }

        return ($a->initiative > $b->initiative) ? -1 : 1;
    });
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 */
function attackTargets(array &$immuneGroups, array &$infectionGroups)
{
    /** @var Group[] $allGroups */
    $allGroups = array_merge($immuneGroups, $infectionGroups);
    sortGroupsByInitiative($allGroups);
    foreach ($allGroups as $group) {
        // Groups which have no units left cannot attack
        if ($group->units > 0) {
            $group->doAttack();
        }
    }

    removeEmptyGroups($immuneGroups, $infectionGroups);
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 */
function removeEmptyGroups(array &$immuneGroups, array &$infectionGroups)
{
    foreach ($immuneGroups as $index => $immuneGroup) {
        if ($immuneGroup->units <= 0) {
            unset($immuneGroups[$index]);
        }
    }

    foreach ($infectionGroups as $index => $infectionGroup) {
        if ($infectionGroup->units <= 0) {
            unset($infectionGroups[$index]);
        }
    }
}

/**
 * @param array|Group[] $immuneGroups
 * @param array|Group[] $infectionGroups
 * @return int
 */
function getWinningScore(array $immuneGroups, array $infectionGroups): int
{
    $score = 0;
    foreach ($immuneGroups as $group) {
        $score += $group->units;
    }

    foreach ($infectionGroups as $group) {
        $score += $group->units;
    }

    return $score;
}

class Group
{
    const ATTACK_FIRE = 1;
    const ATTACK_COLD = 2;
    const ATTACK_SLASHING = 3;
    const ATTACK_BLUDGEONING = 4;
    const ATTACK_RADIATION = 5;

    const ATTACKS = [
        'fire' => self::ATTACK_FIRE,
        'cold' => self::ATTACK_COLD,
        'slashing' => self::ATTACK_SLASHING,
        'bludgeoning' => self::ATTACK_BLUDGEONING,
        'radiation' => self::ATTACK_RADIATION,
    ];

    public $units;
    public $hitPoints;
    public $attackDamage;
    public $attackType;
    public $initiative;
    public $weaknesses = [];
    public $immunities = [];
    /** @var  Group|null */
    public $target;
    /** @var  bool */
    public $isTargetted;

    /**
     * @param string $definition
     * @return Group|null
     */
    public static function create(string $definition)
    {
        preg_match('/(\d+) units each with (\d+) hit points(.*)with an attack that does (\d+) ([a-z]+) damage at initiative (\d+)/', $definition, $matches);
        if (count($matches) !== 7) {
            return null;
        }

        $group = new Group();
        $group->units = (int) $matches[1];
        $group->hitPoints = (int) $matches[2];
        $group->attackDamage = (int) $matches[4];
        $attackTypeString = $matches[5];
        $group->attackType = self::ATTACKS[$attackTypeString];
        $group->initiative =  (int) $matches[6];
        self::addSpecials($matches[3], $group);
        return $group;
    }

    private static function addSpecials(string $specials, Group $group)
    {
        $specials = str_replace(['(', ')'], '', trim($specials));
        if (!$specials) {
            return;
        }

        foreach (explode(';', $specials) as $special) {
            $special = trim($special);
            if (substr($special, 0, 4) === 'weak') {
                self::addWeaknesses($special, $group);
            } elseif (substr($special, 0, 6) === 'immune') {
                self::addImmunities($special, $group);
            }
        }
    }

    private static function addWeaknesses(string $weaknesses, Group $group) {
        $weaknesses = str_replace('weak to ', '', $weaknesses);
        $weaknesses = explode(',', $weaknesses);
        foreach ($weaknesses as $weakness) {
            $type = self::ATTACKS[trim($weakness)];
            $group->weaknesses[] = $type;
        }
    }

    private static function addImmunities(string $immunities, Group $group) {
        $immunities = str_replace('immune to ', '', $immunities);
        $immunities = explode(',', $immunities);
        foreach ($immunities as $immunity) {
            $type = self::ATTACKS[trim($immunity)];
            $group->immunities[] = $type;
        }
    }

    public function getEffectivePower(): int
    {
        return $this->units * $this->attackDamage;
    }

    /**
     * How much damage will this group do to a target?
     * @param Group $target
     * @return int
     */
    public function calculateDamage(Group $target): int
    {
        $defaultDamage = $this->getEffectivePower();
        if ($this->isTargetWeak($target)) {
            return $defaultDamage * 2;
        }

        if ($this->isTargetImmune($target)) {
            return 0;
        }

        return $defaultDamage;
    }

    /**
     * Is a target weak to this group's attack type?
     * @param Group $target
     * @return bool
     */
    private function isTargetWeak(Group $target): bool
    {
        foreach ($target->weaknesses as $weakness) {
            if ($weakness === $this->attackType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is a target immune to this group's attack type?
     * @param Group $target
     * @return bool
     */
    private function isTargetImmune(Group $target): bool
    {
        foreach ($target->immunities as $immunity) {
            if ($this->attackType === $immunity) {
                return true;
            }
        }

        return false;
    }

    public function doAttack()
    {
        if ($this->target) {
            $damage = $this->calculateDamage($this->target);
            $this->target->takeDamage($damage);
        }
    }

    public function takeDamage(int $damage)
    {
        $unitsLost = floor($damage / $this->hitPoints);
        $this->units = $this->units - $unitsLost;
        if ($this->units < 0) {
            $this->units = 0;
        }
    }
}