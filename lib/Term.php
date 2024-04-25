<?php

namespace JanHerman\SimpleTaxonomies;

use Kirby\Cms\Page;
use Kirby\Cms\StructureObject;

class Term extends StructureObject
{
    public function __toString()
    {
        return $this->title()->toString();
    }

    public function url(Page $archive_page = null): string
    {
        $archive_page = $archive_page ?: $this->parent();
        $taxonomy = $this->field()->key();
        $taxonomy_slug = $archive_page->taxonomySlug($taxonomy);
        $param_value = option('jan-herman.simple-taxonomies.paramValue');

        return url($archive_page->url(), ['params' => [$taxonomy_slug => $this->{$param_value}()->toString()]]);
    }

    public function isOpen(): bool
    {
        $taxonomy = $this->field()->key();
        $open_slugs = $this->parent()->taxonomyParamValues($taxonomy);
        $param_value = option('jan-herman.simple-taxonomies.paramValue');

        if (empty($open_slugs)) {
            return false;
        }

        return in_array($this->{$param_value}()->toString(), $open_slugs);
    }
}
