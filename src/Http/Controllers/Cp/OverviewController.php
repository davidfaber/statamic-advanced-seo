<?php

namespace Aerni\AdvancedSeo\Http\Controllers\Cp;

use Illuminate\View\View;
use Illuminate\Http\Response;
use Aerni\AdvancedSeo\Facades\Defaults;
use Aerni\AdvancedSeo\Data\SeoVariables;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Http\Controllers\CP\CpController;

class OverviewController extends CpController
{
    public function index(): View
    {
        $this->authorize('index', SeoVariables::class);

        return view('advanced-seo::cp.index');
    }

    public function show(string $group): View|Response
    {
        $validGroup = Defaults::groups()->contains($group);

        throw_unless($validGroup, new NotFoundHttpException);

        $this->authorize($group . 'DefaultsIndex', SeoVariables::class);

        return view("advanced-seo::cp.{$group}_index");
    }
}
