<?php

declare(strict_types=1);

namespace GCSS\Godot;

use GCSS\Contracts\TRes;
use Webmozart\Assert\Assert;

class Color implements TRes
{
    private const HEX_PARSE_REGEX = '/#(?:' .
            '([0-9a-f]{2})' . // r
            '([0-9a-f]{2})' . // g
            '([0-9a-f]{2})' . // b
            '([0-9a-f]{2})?' . // a (optional)
        '|' . // Or we can have single-character colors, ex. #f23f
            '([0-9a-f])' . //r
            '([0-9a-f])' . //g
            '([0-9a-f])' . //b
            '([0-9a-f])?' . //a (optional)
        ')/i';

    public float $r = 1;
    public float $g = 1;
    public float $b = 1;
    public float $a = 1;

    public function __construct(string $hex = '#ffff')
    {
        Assert::regex($hex, self::HEX_PARSE_REGEX, "Color {$hex} must be a valid hex value.");
        if (preg_match(self::HEX_PARSE_REGEX, $hex, $matches)) {
            $matches = array_values(array_filter($matches, fn ($m) => strlen($m) > 0 && strlen($m) <= 2));
            foreach ($matches as $idx => $match) {
                if (strlen($match) === 1) {
                    $match .= $match;
                }
                $value = hexdec($match) / 255;
                if ($idx === 0) {
                    $this->r = $value;
                } elseif ($idx === 1) {
                    $this->g = $value;
                } elseif ($idx === 2) {
                    $this->b = $value;
                } elseif ($idx === 3) {
                    $this->a = $value;
                }
            }
        }
    }

    public function toTresString(): string
    {
        return "Color( {$this->r}, {$this->g}, {$this->b}, {$this->a} )";
    }
}
