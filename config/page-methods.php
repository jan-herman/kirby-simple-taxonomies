<?php

use JanHerman\SimpleTaxonomies\Terms;
use JanHerman\SimpleTaxonomies\Term;

return [
    'taxonomySlug' => function (string $taxonomy = 'categories'): string {
        return (string) $this->{$taxonomy . '_slug'}()->toString() ?: 'category';
    },
    'taxonomyParam' => function (string $taxonomy = 'categories'): string {
        return (string) param($this->taxonomySlug($taxonomy));
    },
    'taxonomyParamValues' => function (string $taxonomy = 'categories'): array {
        $param = $this->taxonomyParam($taxonomy);
        $separator = option('jan-herman.simple-taxonomies.paramValueSeparator');

        return explode($separator, $param);
    },
    'isTaxonomyArchive' => function (string $taxonomy = 'categories'): bool {
        return (bool) $this->taxonomyParam($taxonomy);
    },
    'taxonomy' => function (string $taxonomy = 'categories'): Terms {
        $terms = $this?->content()->{$taxonomy}()?->toTaxonomy();

        if (!$terms) {
            return Terms::factory();
        }

        return $terms;
    },
    'taxonomyTerm' => function (string $term_uuid, string $taxonomy = 'categories'): Term {
        return $this->taxonomy($taxonomy)->findBy('uuid', $term_uuid);
    },
    'terms' => function (string $taxonomy = 'categories'): Terms {
        $field = $this->content()->{$taxonomy}();
        return $field->toTerms($taxonomy);
    },
    'hasTerm' => function (Term|string $term, string $taxonomy = 'categories'): bool {
        if ($term instanceof Term) {
            return $this->terms($taxonomy)->has($term);
        } else {
            $terms = $this->content()->{$taxonomy}()?->split();
            return in_array($term, $terms);
        }
    },
    'openTerms' => function (string $taxonomy = 'categories'): Terms {
        if (!$this->isTaxonomyArchive($taxonomy)) {
            return Terms::factory();
        }

        $param_value = option('jan-herman.simple-taxonomies.paramValue');
        $terms = $this->taxonomy($taxonomy);
        $param_values = $this->taxonomyParamValues($taxonomy);

        return $terms->filterBy($param_value, 'in', $param_values);
    },
    'openTerm' => function (string $taxonomy = 'categories'): ?Term {
        return $this->openTerms($taxonomy)->first();
    },
];
