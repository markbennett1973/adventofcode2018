<?php

//const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day19-input.txt';

$instructionRegister = null;

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): int
{
    $ops = readOps();
    $registers = [0, 0, 0, 0, 0, 0];

    $nextOp = true;
    while ($nextOp) {
        $nextOp = doNextOp($ops, $registers);
    }

    return $registers[0];
}

function part2(): int
{
    global $instructionRegister;

    $ops = readOps();
    $registers = [1, 0, 0, 0, 0, 0];

    $nextOp = true;
    while ($nextOp) {
        $nextOp = doNextOp($ops, $registers);
        print "Next op = " . $registers[$instructionRegister] . "\n";
    }

    return $registers[0];
}

/**
 * @return array|Sample[]
 */
function readOps(): array
{
    global $instructionRegister;

    $ops = [];

    $lines = explode("\n", file_get_contents(INPUT_FILE));

    foreach ($lines as $line) {
        if (substr($line, 0, 3) === '#ip') {
            $instructionRegister = (int) str_replace('#ip ', '', $line);
        } elseif (trim($line)) {
            $ops[] = new Op($line);
        }
    }

    return $ops;
}

/**
 * @param array|Op[] $ops
 * @param array $registers
 * @return bool
 */
function doNextOp(array $ops, array &$registers): bool
{
    global $instructionRegister;

    $nextOpIndex = $registers[$instructionRegister];
    if (!array_key_exists($nextOpIndex, $ops)) {
        return false;
    }

    // Do the op
    $op = $ops[$nextOpIndex];
    $registers = call_user_func_array($op->opCode, [$registers, $op->inputA, $op->inputB, $op->output]);

    // Increment the instruction register
    $registers[$instructionRegister]++;
    return true;
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

class Op
{
    public $opCode;
    public $inputA;
    public $inputB;
    public $output;

    public function __construct(string $operation)
    {
        $opValues = explode(' ', $operation);
        $this->opCode = $opValues[0];
        $this->inputA = (int) $opValues[1];
        $this->inputB = (int) $opValues[2];
        $this->output = (int) $opValues[3];
    }
}