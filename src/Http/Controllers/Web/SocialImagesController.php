<?php

namespace Aerni\AdvancedSeo\Http\Controllers\Web;

use Statamic\View\View;
use Statamic\Facades\Data;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Facades\Statamic\CP\LivePreview;
use Statamic\Contracts\Entries\Entry;
use Statamic\Taxonomies\LocalizedTerm;
use Aerni\AdvancedSeo\Facades\SocialImage;
use Statamic\Exceptions\NotFoundHttpException;

class SocialImagesController extends Controller
{
    public function show(string $type, string $id, Request $request): View
    {
        // Throw if the social images generator is disabled.
        throw_unless(config('advanced-seo.social_images.generator.enabled', false), new NotFoundHttpException);

        // Throw if no data was found.
        throw_unless($data = $this->getData($id, $request), new NotFoundHttpException);

        // Throw if the data is not an entry or term.
        throw_unless($data instanceof Entry || $data instanceof LocalizedTerm, new NotFoundHttpException());

        // Throw if the social image type is not supported.
        throw_unless($specs = SocialImage::specs($type, $data), new NotFoundHttpException);

        $template = $specs['templates']->get($request->get('theme')) // Get the template based on the theme in the request.
            ?? $specs['templates']->get('default') // If no theme is set, use the default theme.
            ?? $specs['templates']->first(); // If the default doesn't exist either, fall back to the first theme.

        return (new View)
            ->template($template)
            ->layout($specs['layout'])
            ->with($data->merge($specs)->toAugmentedArray());
    }

    protected function getData(string $id, Request $request): ?Entry
    {
        if ($request->statamicToken()) {
            return LivePreview::item($request->statamicToken());
        }

        return Data::find($id);
    }
}
