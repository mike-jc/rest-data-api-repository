<?php

namespace AppBundle\Helper;

use Tree\Node\Node as TreeNode;

trait TreeTrait {

    /**
     * Check if current branch of the tree contains this class already
     * @param TreeNode $node
     * @param string $className
     * @return bool
     */
    protected function isTracked(TreeNode $node, $className) {
        $currentNode = $node;
        do {
            if ($currentNode->getValue() == $className) {
                return true;
            }
            $currentNode = $currentNode->isRoot() ? null : $currentNode->getParent();
        } while ($currentNode);
        return false;
    }
}