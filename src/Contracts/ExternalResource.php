<?php

declare(strict_types=1);

namespace GCSS\Contracts;

interface ExternalResource
{
    public function getPath(): string;
}
