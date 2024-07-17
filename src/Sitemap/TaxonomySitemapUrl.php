<?php

namespace Aerni\AdvancedSeo\Sitemap;

use Aerni\AdvancedSeo\Models\Defaults;
use Aerni\AdvancedSeo\Support\Helpers;
use Illuminate\Support\Collection;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Site;

class TaxonomySitemapUrl extends BaseSitemapUrl
{
    protected string $initialSite;

    public function __construct(protected Taxonomy $taxonomy, protected string $site, protected TaxonomySitemap $sitemap)
    {
        $this->initialSite = Site::current()->handle();

        // We need to set the site so that we can get to correct URL of the taxonomy.
        Site::setCurrent($site);
    }

    public function __destruct()
    {
        Site::setCurrent($this->initialSite);
    }

    public function loc(): string
    {
        return $this->absoluteUrl($this->taxonomy);
    }

    public function alternates(): ?array
    {
        if (! Site::multiEnabled()) {
            return null;
        }

        $sites = $this->taxonomies()->keys();

        if ($sites->count() < 2) {
            return null;
        }

        $hreflang = $sites->map(function ($site) {
            // Set the site so we can get the localized absolute URLs of the taxonomy.
            Site::setCurrent($site);

            return [
                'href' => $this->absoluteUrl($this->taxonomy),
                'hreflang' => Helpers::parseLocale(Site::current()->locale()),
            ];
        });

        $originSite = $this->taxonomy->sites()->first();

        $xDefaultSite = $sites->contains($originSite) ? $originSite : $this->site;

        // Set the site so we can get the localized absolute URL for the x-default.
        Site::setCurrent($xDefaultSite);

        return $hreflang->push([
            'href' => $this->absoluteUrl($this->taxonomy),
            'hreflang' => 'x-default',
        ])->values()->all();
    }

    public function lastmod(): string
    {
        if ($terms = $this->lastModifiedTaxonomyTerm()) {
            return $terms->lastModified()->format('Y-m-d\TH:i:sP');
        }

        return now()->format('Y-m-d\TH:i:sP');
    }

    public function changefreq(): string
    {
        return Defaults::data('taxonomies')->get('seo_sitemap_change_frequency');
    }

    public function priority(): string
    {
        return Defaults::data('taxonomies')->get('seo_sitemap_priority');
    }

    public function site(): string
    {
        return $this->site;
    }

    protected function lastModifiedTaxonomyTerm(): ?Term
    {
        return $this->taxonomy->queryTerms()
            ->where('site', $this->site)
            ->get()
            ->sortByDesc(fn ($term) => $term->lastModified())
            ->first();
    }

    protected function taxonomies(): Collection
    {
        return $this->sitemap->taxonomies();
    }
}
