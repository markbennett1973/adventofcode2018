<?php

// const INPUT_FILE = 'small.txt';
const INPUT_FILE = 'day8-input.txt';
const ELEMENT_TYPE_CHILD_NODE_COUNT = 0;
const ELEMENT_TYPE_METADATA_COUNT = 1;
const ELEMENT_TYPE_METADATA = 2;

print "Part 1: " . part1() . "\n";
print "Part 2: " . part2() . "\n";


function part1(): int
{
    $tree = buildTree();
    return getMetadataSum($tree, 0);
}

function part2(): int
{
    $tree = buildTree();
    return $tree->getNodeValue();
}

function buildTree(): Node
{
    $currentElementType = null;
    $previousElementType = null;

    /** @var Node $currentNode */
    $currentNode = null;

    $elements = explode(' ', file_get_contents(INPUT_FILE));

    foreach ($elements as $elementValue) {
        $elementValue = (int) $elementValue;

        // Start by identifying the element type
        if ($currentElementType === null) {
            // No previous elements - the first one is a child node count
            $currentElementType = ELEMENT_TYPE_CHILD_NODE_COUNT;
        } elseif ($previousElementType === ELEMENT_TYPE_CHILD_NODE_COUNT) {
            // After a child node count, we always get a metadata count
            $currentElementType = ELEMENT_TYPE_METADATA_COUNT;
        } else {
            // If we've done all the children of this node, start doing the metadata
            if ($currentNode->areChildrenDone()) {
                if ($currentNode->isMetadataDone()) {
                    // we've finished the metadata for this node - go back up to the parent
                    while ($currentNode->isNodeComplete()) {
                        $currentNode = $currentNode->parentNode;
                    }

                    // Back at the parent level, we may need to add more children, or may
                    // need to move on to metadata
                    if ($currentNode->areChildrenDone()) {
                        $currentElementType = ELEMENT_TYPE_METADATA;
                    } else {
                        $currentElementType = ELEMENT_TYPE_CHILD_NODE_COUNT;
                    }
                } else {
                    // we're still doing metadata for the current node
                    $currentElementType = ELEMENT_TYPE_METADATA;
                }
            } else {
                // If we've not done all the children yet, we need to create a new child
                $currentElementType = ELEMENT_TYPE_CHILD_NODE_COUNT;
            }
        }

        // Now do what we need to do for this element type
        switch ($currentElementType) {
            case ELEMENT_TYPE_CHILD_NODE_COUNT:
                $currentNode = new Node($elementValue, $currentNode);
                break;

            case ELEMENT_TYPE_METADATA_COUNT:
                $currentNode->metadataCount = $elementValue;
                break;

            case ELEMENT_TYPE_METADATA:
                $currentNode->metadata[] = $elementValue;
                break;
        }

        $previousElementType = $currentElementType;
    }

    return $currentNode;
}

function getMetadataSum(Node $node, int $currentSum): int
{
    foreach ($node->metadata as $element) {
        $currentSum += $element;
    }

    foreach ($node->children as $child) {
        $currentSum = getMetadataSum($child, $currentSum);
    }

    return $currentSum;
}

class Node
{
    public $children = [];
    public $metadata = [];
    public $parentNode;
    public $metadataCount;
    public $childNodeCount;

    public function __construct(int $numberOfChildren, Node $parentNode = null)
    {
        $this->childNodeCount = $numberOfChildren;
        if ($parentNode) {
            $parentNode->children[] = $this;
            $this->parentNode = $parentNode;
        }
    }

    public function areChildrenDone(): bool
    {
        return count($this->children) === $this->childNodeCount;
    }

    public function isMetadataDone(): bool
    {
        return count($this->metadata) === $this->metadataCount;
    }

    public function isNodeComplete(): bool
    {
        return $this->areChildrenDone() && $this->isMetadataDone();
    }

    public function getNodeValue(): int
    {
        if (count($this->children) === 0) {
            return array_sum($this->metadata);
        }

        $value = 0;
        foreach ($this->metadata as $index) {
            // our child nodes are zero-indexed...
            $childIndex = $index - 1;

            if (array_key_exists($childIndex, $this->children)) {
                $value += $this->children[$childIndex]->getNodeValue();
            }
        }

        return $value;
    }
}