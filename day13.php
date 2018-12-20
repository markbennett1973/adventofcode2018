<?php

// const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day13-input.txt';

// print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";

function part1(): string
{
    $map = $carts = [];
    getMapAndCarts($map, $carts);
    // drawMap($map, $carts);

    $loops = 0;
    while (true) {
        foreach ($carts as $cart) {
            $cart->moveCart($map);

            /** @var Cart $crashedCart */
            if ($crashedCart = getCrashedCart($carts)) {
                return $crashedCart->getCoordsAsString();
            }
        }

        // drawMap($map, $carts);

        $loops++;
        if ($loops % 100 === 0) {
            print "Done $loops loops\n";
        }
    }

    return '';
}


function part2(): string
{
    $map = $carts = [];
    getMapAndCarts($map, $carts);
    // drawMap($map, $carts);

    $loops = 0;
    while (true) {
        sortCarts($carts);

        foreach ($carts as $cart) {
            $cart->moveCart($map);

            /** @var Cart $crashedCart */
            if ($crashedCart = getCrashedCart($carts)) {
                removeCartsAtCoords($crashedCart, $carts);
            }
        }

        // drawMap($map, $carts);

        if (count($carts) === 1) {
            $cart = reset($carts);
            return $cart->getCoordsAsString();
        }

        $loops++;
        if ($loops % 100 === 0) {
            print "Done $loops loops\n";
        }
    }

    return '';
}

/**
 * @param array|Track[] $map
 * @param array|Cart[] $carts
 * @return array
 */
function getMapAndCarts(array &$map, array &$carts)
{
    $lines = explode("\n", file_get_contents(INPUT_FILE));
    for ($i = 0; $i < count($lines); $i++) {
        for ($j = 0; $j < strlen($lines[$i]); $j++) {
            if ($lines[$i][$j] !== ' ') {
                $map[$i][$j] = new Track($lines[$i][$j]);

                if ($map[$i][$j]->direction === Track::DIR_UNKNOWN) {
                    $carts[] = new Cart($i, $j, $lines[$i][$j]);
                }
            } else {
                $map[$i][$j] = null;
            }
        }
    }

    addMapPadding($map);
    fillUnknownMapMarkers($map);

    return $map;
}

function addMapPadding(array &$map)
{
    $maxWidth = 0;
    foreach ($map as $row) {
        if (count($row) > $maxWidth) {
            $maxWidth = count($row);
        }
    }

    for ($i = 0; $i < count($map); $i++) {
        if (count($map[$i]) < $maxWidth) {
            for ($j = count($map[$i]); $j < $maxWidth; $j++) {
                $map[$i][$j] = null;
            }
        }
    }
}

function fillUnknownMapMarkers(array &$map)
{
    $rows = count($map);
    $cols = count($map[0]);

    for ($i = 0; $i < $rows; $i++) {
        for ($j = 0; $j < $cols; $j++) {
            /** @var Track $track */
            $track = $map[$i][$j];
            if ($track && $track->direction === Track::DIR_UNKNOWN) {
                $track->deriveDirection(
                    $j > 0 ? $map[$i][$j - 1] : null,
                    $j < $cols ? $map[$i][$j + 1] : null,
                    $i > 0 ? $map[$i - 1][$j] : null,
                    $i < $rows ? $map[$i + 1][$j] : null
                );
            }
        }
    }
}

/**
 * @param array|Track[] $map
 * @param array|Cart[] $carts
 */
function drawMap(array $map, array $carts)
{
    $out = '';

    $cartCoords = [];
    foreach ($carts as $cart) {
        $cartCoords[$cart->getCoordsAsString()] = $cart->getOutChar();
    }

    $rows = count($map);
    $cols = count($map[0]);

    for ($i = 0; $i < $rows; $i++) {
        for ($j = 0; $j < $cols; $j++) {
            if ($map[$i][$j] === null) {
                $out .= ' ';
            } else {
                $coord = "$j,$i";
                if (array_key_exists($coord, $cartCoords)) {
                    $out .= $cartCoords[$coord];
                } else {
                    /** @var Track $track */
                    $track = $map[$i][$j];
                    $out .= $track->getOutChar();
                }
            }
        }

        $out .= "\n";
    }

    print $out;
}

/**
 * @param array|Cart[] $carts
 * @return Cart|null
 */
function getCrashedCart(array $carts)
{
    $cartCoords = [];
    foreach ($carts as $cart) {
        if (in_array($cart->getCoordsAsString(), $cartCoords)) {
            return $cart;
        }

        $cartCoords[] = $cart->getCoordsAsString();
    }

    return null;
}

/**
 * @param Cart $sourceCart
 * @param array|Cart[] $carts
 */
function removeCartsAtCoords(Cart $sourceCart, array &$carts)
{
    foreach ($carts as $index => $cart) {
        if ($cart->row === $sourceCart->row && $cart->column === $sourceCart->column) {
            unset($carts[$index]);
        }
    }
}

/**
 * @param array|Cart[] $carts
 */
function sortCarts(&$carts)
{
    usort($carts, function (Cart $a, Cart $b) {
        if ($a->row === $b->row) {
            return (int) $a->column <=> (int) $b->column;
        }

        return (int) $a->row <=> (int) $b->row;
    });
}

class Cart
{
    const DIR_LEFT = 0;
    const DIR_RIGHT = 1;
    const DIR_UP = 2;
    const DIR_DOWN = 3;
    const DIR_STRAIGHT = 4;

    const DIR_CHARS = [
        self::DIR_LEFT => '<',
        self::DIR_RIGHT => '>',
        self::DIR_UP => '^',
        self::DIR_DOWN => 'v',
    ];

    public $row;
    public $column;

    /** @var int left, right, up or down */
    public $direction;
    /** @var int left, right or straight */
    public $lastTurn;

    public function __construct(int $row, int $col, string $directionChar)
    {
        $this->row = $row;
        $this->column = $col;
        $this->lastTurn = self::DIR_RIGHT;

        switch ($directionChar) {
            case '<':
                $this->direction = self::DIR_LEFT;
                break;

            case '>':
                $this->direction = self::DIR_RIGHT;
                break;

            case '^':
                $this->direction = self::DIR_UP;
                break;

            case 'v':
                $this->direction = self::DIR_DOWN;
                break;

            default:
                throw new \Exception('Invalid direction: ' . $directionChar);
        }
    }

    public function getOutChar(): string
    {
        return self::DIR_CHARS[$this->direction];
    }

    public function getCoordsAsString(): string
    {
        return $this->column . ',' . $this->row;
    }

    public function moveCart(array $map)
    {
        $this->move();

        /** @var Track $newTrack */
        $newTrack = $map[$this->row][$this->column];

        // Now change direction
        if ($newTrack->direction === Track::DIR_INTERSECTION) {
            $this->changeDirectionIntersection();
        } elseif ($newTrack->direction === Track::DIR_CURVE) {
            $this->changeDirectionCurve($map);
        }
     }

     private function move()
     {
         switch ($this->direction) {
             case self::DIR_LEFT:
                 $this->column--;
                 break;

             case self::DIR_RIGHT:
                 $this->column++;
                 break;

             case self::DIR_UP:
                 $this->row--;
                 break;

             case self::DIR_DOWN:
                 $this->row++;
         }
     }

     private function changeDirectionIntersection()
     {
         switch ($this->direction) {
             case self::DIR_LEFT:
                 switch ($this->lastTurn) {
                     case self::DIR_LEFT:
                         // If we last turned left, we now go straight, so don't change direction
                         $this->lastTurn = self::DIR_STRAIGHT;
                         break;

                     case self::DIR_STRAIGHT:
                         // If we last went straight, next we turn right, which mean we end up going up
                         $this->direction = self::DIR_UP;
                         $this->lastTurn = self::DIR_RIGHT;
                         break;

                     case self::DIR_RIGHT:
                         // If we last went right, next we turn left, so we end up going down
                         $this->direction = self::DIR_DOWN;
                         $this->lastTurn = self::DIR_LEFT;
                         break;
                 }
                 break;

             case self::DIR_UP:
                 switch ($this->lastTurn) {
                     case self::DIR_LEFT:
                         // If we last turned left, we now go straight, so don't change direction
                         $this->lastTurn = self::DIR_STRAIGHT;
                         break;

                     case self::DIR_STRAIGHT:
                         // If we last went straight, next we turn right, which mean we end up going right
                         $this->direction = self::DIR_RIGHT;
                         $this->lastTurn = self::DIR_RIGHT;
                         break;

                     case self::DIR_RIGHT:
                         // If we last went right, next we turn left, so we end up going left
                         $this->direction = self::DIR_LEFT;
                         $this->lastTurn = self::DIR_LEFT;
                         break;
                 }
                 break;

             case self::DIR_RIGHT:
                 switch ($this->lastTurn) {
                     case self::DIR_LEFT:
                         // If we last turned left, we now go straight, so don't change direction
                         $this->lastTurn = self::DIR_STRAIGHT;
                         break;

                     case self::DIR_STRAIGHT:
                         // If we last went straight, next we turn right, which mean we end up going down
                         $this->direction = self::DIR_DOWN;
                         $this->lastTurn = self::DIR_RIGHT;
                         break;

                     case self::DIR_RIGHT:
                         // If we last went right, next we turn left, so we end up going up
                         $this->direction = self::DIR_UP;
                         $this->lastTurn = self::DIR_LEFT;
                         break;
                 }
                 break;

             case self::DIR_DOWN:
                 switch ($this->lastTurn) {
                     case self::DIR_LEFT:
                         // If we last turned left, we now go straight, so don't change direction
                         $this->lastTurn = self::DIR_STRAIGHT;
                         break;

                     case self::DIR_STRAIGHT:
                         // If we last went straight, next we turn right, which mean we end up going left
                         $this->direction = self::DIR_LEFT;
                         $this->lastTurn = self::DIR_RIGHT;
                         break;

                     case self::DIR_RIGHT:
                         // If we last went right, next we turn left, so we end up going right
                         $this->direction = self::DIR_RIGHT;
                         $this->lastTurn = self::DIR_LEFT;
                         break;
                 }
                 break;

         }
     }

     private function changeDirectionCurve(array $map)
     {
         $maxRow = count($map) - 1;
         $maxCol = count($map[0]) - 1;

         switch ($this->direction) {
             case self::DIR_LEFT:
             case self::DIR_RIGHT:
                 if ($this->row === 0) {
                     // there's nothing above, so we must change direction to down
                     $this->direction = self::DIR_DOWN;
                 } elseif ($this->row === $maxRow) {
                     // there's nothing below, so we must change direction to up
                     $this->direction = self::DIR_UP;
                 } else {
                     $above = $map[$this->row - 1][$this->column];
                     $below = $map[$this->row + 1][$this->column];

                     if ($above === null) {
                         // Nothing above, so must go down
                         $this->direction = self::DIR_DOWN;
                     } elseif ($below === null) {
                         // Nothing below, so must go up
                         $this->direction = self::DIR_UP;
                     } else {
                         // We've got track above and below. One must be horizontal, so we can't go that way
                         if ($above->direction === Track::DIR_HORIZONTAL) {
                             $this->direction = self::DIR_DOWN;
                         } else {
                             $this->direction = self::DIR_UP;
                         }
                     }
                 }
                 break;

             case self::DIR_UP:
             case self::DIR_DOWN:
                 if ($this->column === 0) {
                     // there's nothing left, so we must change direction to right
                     $this->direction = self::DIR_RIGHT;
                 } elseif ($this->column === $maxCol) {
                     // there's nothing right, so we must change direction to left
                     $this->direction = self::DIR_LEFT;
                 } else {
                     $left = $map[$this->row][$this->column - 1];
                     $right = $map[$this->row][$this->column + 1];

                     if ($left === null) {
                         // Nothing left, so must go right
                         $this->direction = self::DIR_RIGHT;
                     } elseif ($right === null) {
                         // Nothing right, so must go left
                         $this->direction = self::DIR_LEFT;
                     } else {
                         // We've got track left and right. One must be vertical, so we can't go that way
                         if ($left->direction === Track::DIR_VERTICAL) {
                             $this->direction = self::DIR_RIGHT;
                         } else {
                             $this->direction = self::DIR_LEFT;
                         }
                     }
                 }
                 break;
         }
     }
}

class Track
{
    const DIR_HORIZONTAL = 0;
    const DIR_VERTICAL = 1;
    const DIR_INTERSECTION = 2;
    const DIR_CURVE = 3;
    const DIR_UNKNOWN = 4;

    const DIR_CHARS = [
        self::DIR_HORIZONTAL => '-',
        self::DIR_VERTICAL => '|',
        self::DIR_INTERSECTION => '+',
        self::DIR_CURVE => '*',
        self::DIR_UNKNOWN => 'C',
    ];

    public $direction;
    private $initialCartChar;

    public function __construct(string $type)
    {
        switch ($type) {
            case '-':
                $this->direction = self::DIR_HORIZONTAL;
                break;

            case '|':
                $this->direction = self::DIR_VERTICAL;
                break;

            case '+':
                $this->direction = self::DIR_INTERSECTION;
                break;

            case '\\':
            case '/':
                $this->direction = self::DIR_CURVE;
                break;

            case '>':
            case '<':
            case 'v':
            case '^':
                $this->direction = self::DIR_UNKNOWN;
                $this->initialCartChar = $type;
                break;

            default:
                throw new \Exception('Invalid track char: ' . $type);
        }
    }

    public function getOutChar(): string
    {
        return self::DIR_CHARS[$this->direction];
    }

    /**
     * Work out the direction of an unknown piece of track based on the initial cart direction
     * and the neighbouring track pieces (which may be null if there is no neighbour in that direction)
     * @param $left
     * @param $right
     * @param $up
     * @param $down
     * @throws Exception
     */
    public function deriveDirection($left, $right, $up, $down)
    {
        switch ($this->initialCartChar) {
            case '>':
                $this->direction = $right ? self::DIR_HORIZONTAL : self::DIR_CURVE;
                break;

            case '<':
                $this->direction = $left ? self::DIR_HORIZONTAL : self::DIR_CURVE;
                break;

            case '^':
                $this->direction = $up ? self::DIR_VERTICAL : self::DIR_CURVE;
                break;

            case 'v':
                $this->direction = $down ? self::DIR_VERTICAL : self::DIR_CURVE;
                break;

            default:
                throw new \Exception('No initial cart direction');
        }

    }
}