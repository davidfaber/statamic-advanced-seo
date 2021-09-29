<?php

namespace Aerni\AdvancedSeo\Blueprints;

use Aerni\AdvancedSeo\Contracts\Blueprint as Contract;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Blueprint as BlueprintFields;
use Statamic\Support\Str;

abstract class BaseBlueprint implements Contract
{
    protected $data;

    public static function make(): self
    {
        return new static();
    }

    public function data($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function get(): BlueprintFields
    {
        return Blueprint::make()
            ->setHandle($this->handle())
            ->setContents(['sections' => $this->processSections()]);
    }

    public function items(): array
    {
        return $this->get()->fields()->all()->mapWithKeys(function ($field, $handle) {
            return [$handle => $field->config()];
        })->toArray();
    }

    protected function processSections(): array
    {
        return collect($this->sections())->map(function ($section, $handle) {
            return [
                'display' => Str::slugToTitle($handle),
                'fields' => $section::make()->data($this->data ?? null)->get(),
            ];
        })->filter(function ($section) {
            return ! empty($section['fields']);
        })->all();
    }

    abstract protected function sections(): array;

    abstract protected function handle(): string;
}
