<?php

namespace Controllers;

use Twig\Extension\ExtensionInterface;

abstract class AbstractTwigExtension implements ExtensionInterface
{
    
    public function getTokenParsers()
    {
        return [];
    }

    public function getNodeVisitors()
    {
        return [];
    }

    public function getFilters()
    {
        return [];
    }

    public function getTests()
    {
        return [];
    }

    public function getOperators()
    {
        return [];
    }

    public function getFunctions():array
    {
        return [];
    }
}
