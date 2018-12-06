<?php

const ASLEEP = 1;
const AWAKE = 0;
const UNKNOWN = -1;

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $guardData = getGuardData();
    $sleepiestGuard = findSleepiestGuard($guardData);
    print "Sleepiest guard = $sleepiestGuard\n";

    $sleepiestMinute = findSleepiestMinute($guardData, $sleepiestGuard);
    print "Sleepiest minute: $sleepiestMinute\n";

    return $sleepiestGuard * $sleepiestMinute;
}

function part2(): int
{
    $guardData = getGuardData();
    $guardSleepingMinutes = getGuardSleepingMinutes($guardData);

    $sleepiestGuard = $mostAsleepMinutes = $sleepiestMinute = 0;
    foreach ($guardSleepingMinutes as $guardId => $minutes) {
        $guardSleepiestMinute = findMaxKey($minutes);
        $guardAsleepMinutes = $minutes[$guardSleepiestMinute];

        if ($guardAsleepMinutes > $mostAsleepMinutes) {
            $mostAsleepMinutes = $guardAsleepMinutes;
            $sleepiestGuard = $guardId;
            $sleepiestMinute = $guardSleepiestMinute;
        }
    }

    return $sleepiestGuard * $sleepiestMinute;
}

/**
 * @return array
 *   [guardID][date][minute] = 0 (awake) or 1 (asleep)
 */
function getGuardData(): array
{
    $guards = [];

    $currentDate = null;
    $currentGuard = null;
    $currentState = UNKNOWN;
    $currentMinute = null;

    $lines = explode("\n", file_get_contents('day4-input-sorted.txt'));

    // TEMP: $lines = array_slice($lines, 0, 9);

    foreach ($lines as $line) {
        if (strpos($line, 'begins shift') !== false) {
            // Fill in remaining minutes at current state
            if ($currentMinute !== null) {
                for ($i = $currentMinute; $i < 60; $i++) {
                    $guards[$currentGuard][$currentDate][$i] = $currentState;
                }
            }

            // Start a new date
            $currentDate = getDateFromLine($line);
            $currentMinute = 0;
            $currentGuard = getGuardFromLine($line);
            $currentState = AWAKE;
        } else {
            // Guard has either fallen asleep or woken up
            $stateChangeMinute = getMinuteFromLine($line);

            // Fill in the minutes up to the state change
            for ($i = $currentMinute; $i < $stateChangeMinute; $i++) {
                $guards[$currentGuard][$currentDate][$i] = $currentState;
            }

            $currentState = getStateFromLine($line);
            $currentMinute = $stateChangeMinute;
        }
    }

    // Fill in the remaining time for the last guard
    for ($i = $currentMinute; $i < 60; $i++) {
        $guards[$currentGuard][$currentDate][$i] = $currentState;
    }

    return $guards;
}

function getDateFromLine(string $line): string
{
    // TODO - watch for rounding up. Might get away without this...
    $date = substr($line, 1, 10);
    // $hour = substr($line, 12, 5);

    return $date;
}

function getGuardFromLine(string $line): int
{
    $match = substr($line, 26);
    $match = str_replace(' begins shift', '', $match);
    return $match;
}

function getMinuteFromLine(string $line): int
{
    return substr($line, 15, 2);
}

function getStateFromLine(string $line): int
{
    if (strpos($line, 'wakes up') !== false) {
        return AWAKE;
    }

    if (strpos($line, 'falls asleep') !== false) {
        return ASLEEP;
    }

    return UNKNOWN;
}

function findSleepiestGuard(array $guardData): int
{
    // Count how many minutes each guard was asleep
    $minutesAsleep = [];
    foreach ($guardData as $guardId => $dates) {
        foreach ($dates as $date => $minutes) {
            foreach ($minutes as $minute => $state) {
                if ($state === ASLEEP) {
                    $minutesAsleep[$guardId]++;
                }
            }
        }
    }

    return findMaxKey($minutesAsleep);
}

function findSleepiestMinute(array $guardData, int $sleepiestGuard): int
{
    $minutes = [];

    foreach ($guardData[$sleepiestGuard] as $dates) {
        foreach ($dates as $minute => $state) {
            if ($state === ASLEEP) {
                $minutes[$minute]++;
            }
        }

    }

    return findMaxKey($minutes);
}

function findMaxKey(array $array): int
{
    // Now find the max
    $maxValue = $maxKey = 0;
    foreach ($array as $key => $value) {
        if ($value > $maxValue) {
            $maxValue = $value;
            $maxKey = $key;
        }
    }

    return $maxKey;
}

function getGuardSleepingMinutes(array $guardData): array
{
    $sleepingMinutes = [];
    foreach ($guardData as $guardId => $dates) {
        foreach ($dates as $date => $minutes) {
            foreach ($minutes as $minute => $state) {
                if ($state === ASLEEP) {
                    $sleepingMinutes[$guardId][$minute]++;
                }
            }
        }
    }

    return $sleepingMinutes;
}