<?php

namespace Aerni\AdvancedSeo\Repositories;

use Aerni\AdvancedSeo\Content\SocialImage;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;

class SocialImageRepository
{
    public function all(Entry $entry): array
    {
        return $this->types()->flatMap(function ($item, $type) use ($entry) {
            return match ($type) {
                'twitter' => $this->twitter($entry),
                'og' => $this->openGraph($entry),
            };
        })->toArray();
    }

    public function openGraph(Entry $entry): array
    {
        return (new SocialImage($entry, $this->specs('og', $entry)))->generate();
    }

    public function twitter(Entry $entry): array
    {
        return (new SocialImage($entry, $this->specs('twitter', $entry)))->generate();
    }

    public function specs(string $type, Entry $entry): ?array
    {
        $specs = $this->types()->get($type);

        return match ($type) {
            'og' => $specs,
            'twitter' => collect($specs)->firstWhere('card', $entry->value('seo_twitter_card', 'summary')),
            default => null,
        };
    }

    public function types(): Collection
    {
        return collect([
            'og' => [
                'type' => 'og',
                'field' => 'seo_og_image',
                'layout' => config('advanced-seo.social_images.presets.open_graph.layout', 'social_images/layout'),
                'template' => config('advanced-seo.social_images.presets.open_graph.template', 'social_images/open_graph'),
                'width' => config('advanced-seo.social_images.presets.open_graph.width', 1200),
                'height' => config('advanced-seo.social_images.presets.open_graph.height', 628),
            ],
            'twitter' => [
                [
                    'type' => 'twitter',
                    'card' => 'summary',
                    'field' => 'seo_twitter_image',
                    'layout' => config('advanced-seo.social_images.presets.twitter.summary.layout', 'social_images/layout'),
                    'template' => config('advanced-seo.social_images.presets.twitter.summary.template', 'social_images/twitter_summary'),
                    'width' => config('advanced-seo.social_images.presets.twitter.summary.width', 240),
                    'height' => config('advanced-seo.social_images.presets.twitter.summary.height', 240),
                ],
                [
                    'type' => 'twitter',
                    'card' => 'summary_large_image',
                    'field' => 'seo_twitter_image',
                    'layout' => config('advanced-seo.social_images.presets.twitter.summary_large_image.layout', 'social_images/layout'),
                    'template' => config('advanced-seo.social_images.presets.twitter.summary_large_image.template', 'social_images/twitter_summary_large_image'),
                    'width' => config('advanced-seo.social_images.presets.twitter.summary_large_image.width', 1100),
                    'height' => config('advanced-seo.social_images.presets.twitter.summary_large_image.height', 628),
                ],
            ],
        ]);
    }
}
