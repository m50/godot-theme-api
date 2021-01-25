<?php

declare(strict_types=1);

namespace GCSS\Godot\Resources;

use GCSS\Godot\Color;

class StyleBoxFlat extends StyleBox
{
    public float $contentMarginLeft = 0;
    public float $contentMarginRight = 0;
    public float $contentMarginTop = 0;
    public float $contentMarginBottom = 0;
    public Color $bgColor;
    public int $borderWidthLeft = 0;
    public int $borderWidthTop = 0;
    public int $borderWidthRight = 0;
    public int $borderWidthBottom = 0;
    public Color $borderColor;
    public bool  $borderBlend = false;
    public int $cornerRadiusTopLeft = 0;
    public int $cornerRadiusTopRight = 0;
    public int $cornerRadiusBottomRight = 0;
    public int $cornerRadiusBottomLeft = 0;

    /**
     * @param array<string,int|float|bool|Color|string|null> $options
     */
    public function __construct(array $options = [])
    {
        $this->bgColor = new Color();
        $this->borderColor = new Color();
        parent::__construct($options);
    }

    public function setContentMarginLeft(float $value): static
    {
        $this->contentMarginLeft = $value;

        return $this;
    }

    public function setContentMarginRight(float $value): static
    {
        $this->contentMarginRight = $value;

        return $this;
    }

    public function setContentMarginTop(float $value): static
    {
        $this->contentMarginTop = $value;

        return $this;
    }

    public function setContentMarginBottom(float $value): static
    {
        $this->contentMarginBottom = $value;

        return $this;
    }

    public function setBgColor(Color|string $value): static
    {
        if (\is_string($value)) {
            $value = new Color($value);
        }
        $this->bgColor = $value;

        return $this;
    }

    public function setBorderWidthLeft(float $value): static
    {
        $this->borderWidthLeft = (int)$value;

        return $this;
    }

    public function setBorderWidthTop(float $value): static
    {
        $this->borderWidthTop = (int)$value;

        return $this;
    }

    public function setBorderWidthRight(float $value): static
    {
        $this->borderWidthRight = (int)$value;

        return $this;
    }

    public function setBorderWidthBottom(float $value): static
    {
        $this->borderWidthBottom = (int)$value;

        return $this;
    }

    public function setBorderColor(Color|string $value): static
    {
        if (\is_string($value)) {
            $value = new Color($value);
        }
        $this->borderColor = $value;

        return $this;
    }

    public function setBorderBlend(bool $value): static
    {
        $this->borderBlend = $value;

        return $this;
    }

    public function setCornerRadiusTopLeft(float $value): static
    {
        $this->cornerRadiusTopLeft = (int)$value;

        return $this;
    }

    public function setCornerRadiusTopRight(float $value): static
    {
        $this->cornerRadiusTopRight = (int)$value;

        return $this;
    }

    public function setCornerRadiusBottomRight(float $value): static
    {
        $this->cornerRadiusBottomRight = (int)$value;

        return $this;
    }

    public function setCornerRadiusBottomLeft(float $value): static
    {
        $this->cornerRadiusBottomLeft = (int)$value;

        return $this;
    }
}
