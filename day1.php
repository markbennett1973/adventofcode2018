<?php

print "Part 1 - final frequency = " . findFinalFrequency() . "\n";
print "Part 2 - first repeated frequency = " . findFirstDuplicatedFrequency() . "\n";

function findFinalFrequency() {
	$current = 0;
	$lines = file_get_contents('day1-input.txt');
	foreach (explode("\n", $lines) as $line) {
		$sign = substr($line, 0, 1);	
		$number = substr($line, 1);
		if ($sign === '+') {
			$current = $current + $number;
		} else {
			$current = $current - $number;
		}
	}

	return $current;
}

function findFirstDuplicatedFrequency()
{
	$seen = [];
	
	$current = 0;
	$lines = file_get_contents('day1-input.txt');
	while (true) {
		foreach (explode("\n", $lines) as $line) {
			if (trim($line) === '') {
				continue;
			}
			
			$sign = substr($line, 0, 1);	
			$number = substr($line, 1);
			if ($sign === '+') {
				$current = $current + $number;
			} else {
				$current = $current - $number;
			}
			
			// print "Current = $current, found numbers = " . count($seen) . "\n";
			if (in_array($current, $seen)) {
				return $current;
			}
			
			$seen[] = $current;
		}
	
		print "None found - seen " . count($seen) . " - looping again\n";
	}
}