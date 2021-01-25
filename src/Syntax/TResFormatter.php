<?php

declare(strict_types=1);

namespace GCSS\Syntax;

use ReflectionClass;
use GCSS\Contracts\TRes;
use Underscore\Types\Strings;
use GCSS\Contracts\SubResource;
use GCSS\Godot\Resources\Theme;
use GCSS\Exceptions\ValueException;
use GCSS\Contracts\ExternalResource;

/**
 * Formats the theme in a tres format, to be read by Godot.
 * @todo Clean this up, as there is not only a lot of redundancy, but it's also just massive...
 */
class TResFormatter
{
    /** @var array<string,array{int,ExternalResource}> */
    private array $externalResources = [];
    /** @var array<string,array{int,SubResource}> */
    private array $subResources = [];

    public function __construct(public Theme $theme)
    {
        $this->getNodeResources();
        $this->getDefaultFontResources();
    }

    public function render(): string
    {
        $txt = [];
        $loadSteps = count($this->subResources) + count($this->externalResources) + 1; // +1 because the Theme itself
        $txt[] = "[gd_resource type=\"Theme\" load_steps={$loadSteps} format=2]\n"; // What is format?

        $txt = array_merge(
            $txt,
            $this->renderExternalResources(),
            $this->renderSubResources(),
            $this->renderTheme(),
        );

        return implode("\n", $txt) . "\n";
    }

    private function renderTheme(): array
    {
        $txt = [];
        $txt[] = '[resource]';
        $txt[] = "default_font = SubResource( {$this->subResources['default_font'][0]} )";
        foreach ($this->theme->getNodes() as $node) {
            $refClass = new ReflectionClass($node);
            $properties = $refClass->getProperties();
            foreach ($properties as $prop) {
                $key = $refClass->getShortName() . '/' . Strings::toSnakeCase(Strings::toCamelCase($prop->getName()));
                /**
                 * @var (ExternalResource|SubResource|
                 *      array<string,ExternalResource|SubResource|scalar|null>|
                 *      float|bool|string|int) $value
                 */
                $value = $prop->getValue($node);
                $this->renderValue($txt, $value, $key);
            }
        }

        return $txt;
    }

    /** @return list<string> */
    private function renderExternalResources(): array
    {
        $txt = [];
        foreach ($this->externalResources as $tuple) {
            [$id, $externalResource] = $tuple;
            $type = (new ReflectionClass($externalResource))->getShortName();
            $txt[] = "[ext_resource path=\"{$externalResource->getPath()}\" type=\"{$type}\" id={$id}]";
        }
        $txt[] = "";

        return $txt;
    }

    /** @return list<string> */
    private function renderSubResources(): array
    {
        $txt = [];
        foreach ($this->subResources as $tuple) {
            [$id, $subResource] = $tuple;
            $type = (new ReflectionClass($subResource))->getShortName();
            $txt[] = "[sub_resource type=\"{$type}\" id={$id}]";
            /**
             * @var (ExternalResource|SubResource|
             *      array<string,ExternalResource|SubResource|scalar|null>|
             *      float|bool|string|int) $value
             */
            foreach (\get_object_vars($subResource) as $key => $value) {
                $key = Strings::toSnakeCase($key);
                $this->renderValue($txt, $value, $key, $subResource);
            }
            $txt[] = "";
        }

        /** @var list<string> */
        return $txt;
    }

    /**
     * @param array $txt
     * @param (ExternalResource|SubResource|
     *      array<string,ExternalResource|SubResource|scalar|null>|
     *      float|bool|string|int) $value
     * @param string $key
     * @param SubResource|null $owner
     * @return void
     */
    private function renderValue(array &$txt, mixed $value, string $key, ?SubResource $owner = null): void
    {
        $keyPrefix = '';
        if (! \is_null($owner)) {
            $keyPrefix = (new ReflectionClass($owner))->getShortName() . '/';
        }
        if (\is_a($value, ExternalResource::class)) {
            $txt[] = "{$key} = ExtResource( {$this->externalResources[$keyPrefix . $key][0]} )";
        } elseif (\is_a($value, SubResource::class)) {
            $txt[] = "{$key} = SubResource( {$this->subResources[$keyPrefix . $key][0]} )";
        } elseif ($value instanceof TRes) {
            /** @var TRes $value */
            $txt[] = "{$key} = " . $value->toTResString();
        } elseif (\is_array($value)) {
            foreach ($value as $k => $value) {
                $k = $key . '/' . Strings::toSnakeCase(Strings::toCamelCase($k));
                $txt[] = match (true) {
                    $value instanceof ExternalResource =>
                        "{$k} = ExtResource( {$this->externalResources[$keyPrefix . $k][0]} )",
                    $value instanceof SubResource =>
                        "{$k} = SubResource( {$this->subResources[$keyPrefix . $k][0]} )",
                    $value instanceof TRes =>
                        $txt[] = "{$k} = " . $value->toTResString(),
                    \is_null($value) => "{$k} = null",
                    \is_string($value) => "{$k} = \"{$value}\"",
                    \is_bool($value) => "{$k} = " . ($value ? 'true' : 'false'),
                    \is_float($value) => "{$k} = " . $this->formatFloat($value),
                    \is_int($value) => sprintf('%s = %d', $k, $value),
                };
            }
        } else {
            $value = match (true) {
                \is_bool($value) => $value ? 'true' : 'false',
                \is_string($value) => '"' . $value . '"',
                \is_float($value) => $this->formatFloat($value),
                \is_int($value) => sprintf('%d', $value),
            };
            $txt[] = "{$key} = {$value}";
        }
    }

    private function formatFloat(float $value): string
    {
        $output = sprintf('%0.06F', $value);
        $output = str_replace('.000000', '.0', $output);

        return $output;
    }

    private function getDefaultFontResources(): void
    {
        $font = $this->theme->getFont();
        if (\is_null($font)) {
            return;
        }

        $this->subResources['default_font'] = [count($this->subResources) + 1, $font];

        $refClass = new ReflectionClass($font);
        $properties = $refClass->getProperties();
        foreach ($properties as $prop) {
            if (! $prop->hasType()) {
                throw ValueException::untyped($font::class, $prop->getName());
            }
            /**
             * @var (ExternalResource|SubResource|
             *      array<string,ExternalResource|SubResource|scalar|null>|
             *      float|bool|string|int) $value
             */
            $value = $prop->getValue($font);
            $key = $refClass->getShortName() . '/' . Strings::toSnakeCase(Strings::toCamelCase($prop->getName()));
            if ($value instanceof ExternalResource) {
                $this->externalResources[$key] = [count($this->externalResources) + 1, $value];
            } elseif ($value instanceof SubResource) {
                $this->subResources[$key] = [count($this->subResources) + 1, $value];
            } elseif (\is_array($value)) {
                foreach ($value as $k => $v) {
                    $k = $key . '/' . Strings::toSnakeCase(Strings::toCamelCase($k));
                    if ($v instanceof ExternalResource) {
                        $this->externalResources[$k] = [count($this->externalResources) + 1, $v];
                    } elseif ($v instanceof SubResource) {
                        $this->subResources[$k] = [count($this->subResources) + 1, $v];
                    }
                }
            }
        }
    }

    private function getNodeResources(): void
    {
        $nodes = $this->theme->getNodes();

        $nodes = array_reverse($nodes);
        foreach ($nodes as $node) {
            $refClass = new ReflectionClass($node);
            $properties = $refClass->getProperties();
            foreach ($properties as $prop) {
                if (! $prop->hasType()) {
                    throw ValueException::untyped($node::class, $prop->getName());
                }
                $key = $refClass->getShortName() . '/' . Strings::toSnakeCase(Strings::toCamelCase($prop->getName()));
                /**
                 * @var (ExternalResource|SubResource|
                 *      array<string,ExternalResource|SubResource|scalar|null>|
                 *      float|bool|string|int) $value
                 */
                $value = $prop->getValue($node);
                if ($value instanceof ExternalResource) {
                    $this->externalResources[$key] = [count($this->externalResources) + 1, $value];
                } elseif ($value instanceof SubResource) {
                    $this->subResources[$key] = [count($this->subResources) + 1, $value];
                } elseif (\is_array($value)) {
                    foreach ($value as $k => $v) {
                        $k = $key . '/' . Strings::toSnakeCase(Strings::toCamelCase($k));
                        if ($v instanceof ExternalResource) {
                            $this->externalResources[$k] = [count($this->externalResources) + 1, $v];
                        } elseif ($v instanceof SubResource) {
                            $this->subResources[$k] = [count($this->subResources) + 1, $v];
                        }
                    }
                }
            }
        }
    }
}
