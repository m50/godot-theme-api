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
        $this->sortResources();
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
            foreach (\get_object_vars($subResource) as $key => $value) {
                $key = Strings::toSnakeCase($key);
                $this->renderValue($txt, $value, $key, $subResource);
            }
            $txt[] = "";
        }

        return $txt;
    }

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
        } elseif (\is_subclass_of($value, TRes::class)) {
            /** @var TRes $value */
            $txt[] = "{$key} = " . $value->toTResString();
        } elseif (\is_array($value)) {
            foreach ($value as $k => $value) {
                $k = $key . '/' . Strings::toSnakeCase(Strings::toCamelCase($k));
                $txt[] = match (true) {
                    \is_a($value, ExternalResource::class) =>
                        "{$k} = ExtResource( {$this->externalResources[$keyPrefix . $k][0]} )",
                    \is_a($value, SubResource::class) =>
                        "{$k} = SubResource( {$this->subResources[$keyPrefix . $k][0]} )",
                    \is_a($value, TRes::class) =>
                        /** @var TRes $value */
                        $txt[] = "{$k} = " . $value->toTResString(),
                    \is_null($value) => "{$k} = null",
                    \is_string($value) => "{$k} = \"{$value}\"",
                    \is_bool($value) => "{$k} = " . ($value ? 'true' : 'false'),
                    \is_float($value) => $this->formatFloat($value),
                    \is_int($value) => sprintf('%d', $value),
                    default => "{$k} = {$value}",
                };
            }
        } else {
            $value = match (true) {
                default => $value,
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
            $type = $prop->getType()->getName();
            $key = $refClass->getShortName() . '/' . Strings::toSnakeCase(Strings::toCamelCase($prop->getName()));
            if (\is_a($type, ExternalResource::class, true)) {
                $this->externalResources[$key] = [count($this->externalResources) + 1, $prop->getValue($font)];
            } elseif (\is_a($type, SubResource::class, true)) {
                $this->subResources[$key] = [count($this->subResources) + 1, $prop->getValue($font)];
            } elseif ($type === 'array') {
                foreach ($prop->getValue($font) as $k => $value) {
                    $k = $key . '/' . Strings::toSnakeCase(Strings::toCamelCase($k));
                    if (\is_a($value, ExternalResource::class, true)) {
                        $this->externalResources[$k] = [count($this->externalResources) + 1, $value];
                    } elseif (\is_a($value, SubResource::class, true)) {
                        $this->subResources[$k] = [count($this->subResources) + 1, $value];
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
                $type = $prop->getType()->getName();
                $key = $refClass->getShortName() . '/' . Strings::toSnakeCase(Strings::toCamelCase($prop->getName()));
                if (\is_a($type, ExternalResource::class, true)) {
                    $this->externalResources[$key] = [count($this->externalResources) + 1, $prop->getValue($node)];
                } elseif (\is_a($type, SubResource::class, true)) {
                    $this->subResources[$key] = [count($this->subResources) + 1, $prop->getValue($node)];
                } elseif ($type === 'array') {
                    $values = $prop->getValue($node);
                    foreach ($values as $k => $value) {
                        $k = $key . '/' . Strings::toSnakeCase(Strings::toCamelCase($k));
                        if (\is_a($value, ExternalResource::class, true)) {
                            $this->externalResources[$k] = [count($this->externalResources) + 1, $value];
                        } elseif (\is_a($value, SubResource::class, true)) {
                            $this->subResources[$k] = [count($this->subResources) + 1, $value];
                        }
                    }
                }
            }
        }
    }

    private function sortResources(): void
    {
        $this->externalResources = $this->sortResourceArray($this->externalResources);
        $this->subResources = $this->sortResourceArray($this->subResources);
    }

    private function sortResourceArray(array $resources): array
    {
        $resources = \array_map(function (string $key, array $value) {
            return [$key, $value[1]];
        }, \array_keys($resources), $resources);

        \usort($resources, function ($a, $b): int {
            if ($a[1] instanceof ExternalResource && $b[1] instanceof ExternalResource) {
                // return \strlen($a[1]->getPath()) <=> \strlen($b[1]->getPath());
                return \strcmp($a[1]->getPath(), $b[1]->getPath());
            }

            return 0;
        });

        $newResources = [];

        foreach ($resources as $id => $resource) {
            $newResources[$resource[0]] = [$id+1, $resource[1]];
        }

        return $newResources;
    }
}
