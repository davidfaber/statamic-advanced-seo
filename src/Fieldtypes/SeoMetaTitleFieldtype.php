<?php

namespace Aerni\AdvancedSeo\Fieldtypes;

use Aerni\AdvancedSeo\Facades\Seo;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class SeoMetaTitleFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        // Load the localized site defaults.
        $defaults = Site::all()->map(function ($site) {
            return Seo::find('site', 'general')
                ->in($site->handle())
                ->values()
                ->only(['site_name', 'title_separator'])
                ->all();
        });

        // Load the localized content defaults if we're on an entry.
        if ($this->field->parent()) {
            $contentDefaults = Site::all()->map(function ($site) {
                return Seo::find('collections', $this->collectionHandle())
                    ->in($site->handle())
                    ->values()
                    ->only('seo_title')
                    ->all();
            });

            $defaults = $defaults->mergeRecursive($contentDefaults);
        }

        return $defaults;
    }

    protected function collectionHandle(): string
    {
        $parent = $this->field->parent();

        return $parent instanceof \Statamic\Entries\Collection
            ? $parent->handle()
            : $parent->collection()->handle();
    }
}
