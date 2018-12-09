<?php

// const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day7-input.txt';
const ELVES = 5;
const RUN_TIME_OFFSET = 60;

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): string
{
    $stepsDone = [];

    $rules = getRules();
    $stepsRemaining = getDistinctSteps($rules);

    while (count($stepsRemaining) > 0) {
        $nextStep = getNextEligibleStep($rules, $stepsDone, $stepsRemaining);
        // add this rule to the list of applied rules
        $stepsDone[] = $nextStep;

        // and remove from remaining rules
        if (($key = array_search($nextStep, $stepsRemaining)) !== false) {
            unset($stepsRemaining[$key]);
        }
    }

    return implode('', $stepsDone);
}

function part2(): int
{
    $stepsDone = [];
    $schedule = [];
    $stepsRunning = [];
    $currentTime = 0;


    $rules = getRules();
    $stepsRemaining = getDistinctSteps($rules);

    while (count($stepsRemaining) > 0) {
        if (!array_key_exists($currentTime, $schedule)) {
            $schedule[$currentTime] = [];
        }

        // See if any steps have finished running
        foreach ($stepsRunning as $step => $endTime) {
            if ($endTime === $currentTime) {
                unset($stepsRunning[$step]);
                $stepsDone[] = $step;
            }
        }

        for ($elf = 0; $elf < ELVES; $elf++) {
            // Is this elf available at the current time?
            if (!array_key_exists($elf, $schedule[$currentTime])) {
                // See if there's a step that can be started
                if ($nextStep = getNextEligibleStep($rules, $stepsDone, $stepsRemaining)) {
                    $endTime = addStepToSchedule($schedule, $currentTime, $elf, $nextStep);

                    // add this step to the list of running steps
                    $stepsRunning[$nextStep] = $endTime;

                    // and remove from list of remaining steps
                    if (($key = array_search($nextStep, $stepsRemaining)) !== false) {
                        unset($stepsRemaining[$key]);
                    }
                }
            }
        }

        $currentTime++;
    }

//    // Debug: print schedule
//    $out = '';
//    foreach ($schedule as $time => $rows) {
//        $out .= $time . "\t";
//        for ($elf = 0; $elf < ELVES; $elf++) {
//            if (array_key_exists($elf, $schedule[$time])) {
//                $out .= $schedule[$time][$elf] . "\t";
//            } else {
//                $out .= ".\t";
//            }
//        }
//        $out .= "\n";
//    }
//
//    print $out;

    return count($schedule);
}

/**
 * @return array
 *   Array of steps with precedences
 *   e.g. A => [B, C]
 *        B => [C, D]
 */
function getRules(): array
{
    $rules = [];

    $lines = explode("\n", file_get_contents(INPUT_FILE));
    $lines = array_map(function (string $line): string {
        return str_replace(['Step ', ' must be finished before step ', ' can begin.'], '', $line);
    }, $lines);

    foreach ($lines as $line) {
        if (trim($line)) {
            $precedence = $line[0];
            $step = $line[1];

            $rules[$step][] = $precedence;
        }
    }

    return $rules;
}

/**
 * Get the distinct steps from the $rules array
 *
 * @param array $rules
 *   Array of letter pairs, e.g. AB, CD
 * @return array
 *   Array of distinct single letters
 */
function getDistinctSteps(array $rules): array
{
    $distinctSteps = [];
    foreach ($rules as $step => $precedents) {
        $distinctSteps[] = $step;
        foreach ($precedents as $precedent) {
            $distinctSteps[] = $precedent;
        }
    }

    $distinctSteps = array_unique($distinctSteps);
    sort($distinctSteps);

    return $distinctSteps;
}

/**
 * Get a sorted array of steps which can now be applied
 *
 * @param array $rules
 * @param array $stepsDone
 * @param array $stepsRemaining
 * @return string
 * @throws Exception
 */
function getNextEligibleStep(array $rules, array $stepsDone, array $stepsRemaining): string
{
    foreach ($stepsRemaining as $step) {
        // If we've already done this step, then it's not an eligible next step
        if (in_array($step, $stepsDone)) {
            continue;
        }

        // If there are no precedence rules for this step, then this is an eligible step
        if (!in_array($step, array_keys($rules))) {
            return $step;
        }

        // If all precedence steps have been done, then this is an eligible step
        $allPrecedentsDone = true;
        foreach ($rules[$step] as $precedents) {
            if (!in_array($precedents, $stepsDone)) {
                $allPrecedentsDone = false;
            }
        }

        if ($allPrecedentsDone) {
            return $step;
        }
    }

    // No step is eligible at the moment
    return '';
}

function addStepToSchedule(array &$schedule, int $currentTime, int $elf, string $step): int
{
    $stepRunTime = getStepRunTime($step);
    for ($time = $currentTime; $time < $currentTime + $stepRunTime; $time++) {
        $schedule[$time][$elf] = $step;
    }

    return $time;
}

function getStepRunTime(string $step): int
{
    $step = strtolower($step);
    $index = ord($step);
    $runTime = $index - 96;

    return RUN_TIME_OFFSET + $runTime;
}