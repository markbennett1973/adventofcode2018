<?php

//const SAMPLES_FILE = 'small.txt';
const SAMPLES_FILE = 'day16-input-a.txt';
const OPS_FILE = 'day16-input-b.txt';

const OP_CODES = [
    'addr',
    'addi',
    'mulr',
    'muli',
    'banr',
    'bani',
    'borr',
    'bori',
    'setr',
    'seti',
    'gtir',
    'gtri',
    'gtrr',
    'eqir',
    'eqri',
    'eqrr',
];

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $samples = readSamples();

    $multiMatches = 0;
    foreach ($samples as $sample) {
        if (countOpCodeMatches($sample) >= 3) {
            $multiMatches++;
        }
    }

    return $multiMatches;
}

function part2(): int
{
    $opCodes = identifyOpCodes();
    $ops = readOps();
    $registers = [0, 0, 0, 0];

    foreach ($ops as $op) {
        if (!array_key_exists($op->opCode, $opCodes)) {
            $x = 1;
        }

        $opName = $opCodes[$op->opCode];
        $registers = call_user_func_array($opName, [$registers, $op->inputA, $op->inputB, $op->output]);
    }

    return $registers[0];
}

/**
 * @return array|Sample[]
 */
function readSamples(): array
{
    $samples = [];

    $lines = explode("\n", file_get_contents(SAMPLES_FILE));

    $sampleCount = ceil(count($lines) / 4);
    for ($i = 0; $i < $sampleCount; $i++) {
        $samples[] = new Sample(
            $lines[$i * 4],
            $lines[$i * 4 +1],
            $lines[$i * 4 +2]
        );
    }

    return $samples;
}

/**
 * @return array|Op[]
 */
function readOps(): array
{
    $ops = [];
    $lines = explode("\n", file_get_contents(OPS_FILE));
    foreach ($lines as $line) {
        if (trim($line)) {
            $ops[] = new Op($line);
        }
    }

    return $ops;
}

/**
 * @param Sample $sample
 * @param array $excludedOpCodes
 * @return int
 */
function countOpCodeMatches(Sample $sample, array $excludedOpCodes = []): int
{
    $matches = 0;

    foreach (OP_CODES as $opCode) {
        if (!in_array($opCode, $excludedOpCodes)) {
            $finalRegisters = call_user_func_array($opCode, [$sample->initialRegisters, $sample->inputA, $sample->inputB, $sample->output]);
            if ($sample->finalRegisters === $finalRegisters) {
                $matches++;
            }
        }
    }

    return $matches;
}

/**
 * @return array
 */
function identifyOpCodes(): array
{
    $samples = readSamples();

    $foundCodes = [];

    while (count($foundCodes) < count(OP_CODES)) {
        foreach ($samples as $sample) {
            if (countOpCodeMatches($sample, $foundCodes) == 1) {
                identifyOpCodeMatch($sample, $foundCodes);
            }
        }
    }

    return $foundCodes;
}

/**
 * @param Sample $sample
 * @param array $foundOpCodes
 */
function identifyOpCodeMatch(Sample $sample, array &$foundOpCodes)
{
    foreach (OP_CODES as $opCode) {
        if (!in_array($opCode, $foundOpCodes)) {
            $finalRegisters = call_user_func_array($opCode, [$sample->initialRegisters, $sample->inputA, $sample->inputB, $sample->output]);
            if ($sample->finalRegisters === $finalRegisters) {
                $foundOpCodes[$sample->opCode] = $opCode;
            }
        }
    }
}

function addr(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] + $registers[$inputB];
    return $registers;
}

function addi(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] + $inputB;
    return $registers;
}

function mulr(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] * $registers[$inputB];
    return $registers;
}

function muli(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] * $inputB;
    return $registers;
}

function banr(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] & $registers[$inputB];
    return $registers;
}

function bani(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] & $inputB;
    return $registers;
}

function borr(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] | $registers[$inputB];
    return $registers;
}

function bori(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA] | $inputB;
    return $registers;
}

function setr(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $registers[$inputA];
    return $registers;
}

function seti(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = $inputA;
    return $registers;
}

function gtir(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = ($inputA > $registers[$inputB]) ? 1 : 0;
    return $registers;
}

function gtri(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = ($registers[$inputA] > $inputB)  ? 1 : 0;
    return $registers;
}

function gtrr(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = ($registers[$inputA] > $registers[$inputB]) ? 1 : 0;
    return $registers;
}

function eqir(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = ($inputA == $registers[$inputB]) ? 1 : 0;
    return $registers;
}

function eqri(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = ($registers[$inputA] == $inputB) ? 1 : 0;
    return $registers;
}

function eqrr(array $registers, int $inputA, int $inputB, int $output): array
{
    $registers[$output] = ($registers[$inputA] == $registers[$inputB]) ? 1 : 0;
    return $registers;
}

class Sample
{
    public $initialRegisters = [];
    public $finalRegisters = [];
    public $op;
    public $opCode;
    public $inputA;
    public $inputB;
    public $output;

    public function __construct(string $before, string $operation, string $after)
    {
        $this->initialRegisters = $this->getRegisterValues($before);
        $this->finalRegisters = $this->getRegisterValues($after);

        $opValues = explode(' ', $operation);
        $this->opCode = $opValues[0];
        $this->inputA = $opValues[1];
        $this->inputB = $opValues[2];
        $this->output = $opValues[3];
    }

    private function getRegisterValues(string $valuesString): array
    {
        $valuesString = str_replace(['Before:', 'After:', '[', ']'], '', $valuesString);
        $values = explode(',', $valuesString);

        foreach ($values as $index => $value) {
            $values[$index] = (int) $value;
        }

        return $values;
    }
}

class Op
{
    public $opCode;
    public $inputA;
    public $inputB;
    public $output;

    public function __construct(string $operation)
    {
        $opValues = explode(' ', $operation);
        $this->opCode = (int) $opValues[0];
        $this->inputA = (int) $opValues[1];
        $this->inputB = (int) $opValues[2];
        $this->output = (int) $opValues[3];
    }
}