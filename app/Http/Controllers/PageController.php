<?php

namespace App\Http\Controllers;

use Exception;
use Spatie\YamlFrontMatter\Parser;
use Storage;
use App\Http\Navigation\Navigation;

class PageController extends Controller
{
    public function page()
    {
        $pageProperties = $this->getPageProperties();

        return view('page')->with($pageProperties);
    }

    public function edit()
    {
        $slug = substr(request()->path(), 0, -5);

        return redirect("https://github.com/nWidart/nwidart-docs/edit/master/resources/views/{$slug}.md");
    }

    public function getPageProperties() : array
    {
        try {
            $content = Storage::disk('content')->get(request()->path() . '.md');
        } catch (Exception $e) {
            abort(404);
        }

        $document = (new Parser())->parse($content);

        $pageProperties = $document->matter();
        $pageProperties['pagePath'] = request()->path();

        $pageProperties['content'] = markdown($document->body());
        $pageProperties['layout'] = $pageProperties['layout'] ?? request()->segment(1);

        $pageProperties['package'] = current_package();
        $pageProperties['version'] = current_version();

        $pageProperties['previousUrl'] = app(Navigation::class)->getPreviousPage();
        $pageProperties['nextUrl'] = app(Navigation::class)->getNextPage();

        return $pageProperties;
    }
}
