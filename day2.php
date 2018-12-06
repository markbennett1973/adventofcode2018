<?php

print "Part 1 = " . part1() . "\n";
print "Part 2 = " . part2() . "\n";

function part1()
{
	$lines = explode("\n", file_get_contents('day2-input.txt'));
	
	$twos = getExactCounts($lines, 2);
	$threes = getExactCounts($lines, 3);
	print "Twos = $twos, threes = $threes\n";
	return $twos * $threes;
}

function getExactCounts($lines, $target)
{
	$matches = 0;
	foreach ($lines as $line) {
		if (doesLineHaveCount($line, $target)) {
			$matches++;
		}		
	}
	
	return $matches;
}

function doesLineHaveCount($line, $target)
{
	$chars = [];
	
	foreach (str_split($line) as $char) {
		$chars[$char]++;
	}
	
	foreach ($chars as $count) {
		if ($count === $target) {
			return true;
		}
	}
	
	return false;
}

function part2()
{
	$lines = explode("\n", file_get_contents('day2-input.txt'));
	$lineCount = count($lines);

	for ($first = 0; $first < $lineCount - 1; $first++) {
		for ($second = $first + 1; $second < $lineCount; $second++) {
			$diff = diffChars($lines[$first], $lines[$second]);
			if ($diff === 1) {
				return findCommon($lines[$first], $lines[$second]);
			}
		}
	}
}

function diffChars($line1, $line2)
{	
	$diffs = 0;
	for ($i = 0; $i < strlen($line1); $i++) {
		if ($line1[$i] !== $line2[$i]) {
			$diffs++;
		}
	}
	
	return $diffs;
}

function findCommon($line1, $line2)
{
	$result = '';
	for ($i = 0; $i < strlen($line1); $i++) {
		if ($line1[$i] === $line2[$i]) {
			$result .= $line1[$i];
		}
	}
	
	return $result;
}