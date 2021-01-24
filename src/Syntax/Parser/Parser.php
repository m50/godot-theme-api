<?php

declare(strict_types=1);

namespace GCSS\Syntax\Parser;

use Generator;
use Iterator;

class Parser
{
    public function process(Iterator $input): Generator
    {
        foreach ($input as $token) {
            yield $token;
        }
    }
}
