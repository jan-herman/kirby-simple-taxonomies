<?php

use Kirby\Cms\Pages;
use JanHerman\SimpleTaxonomies\Terms;
use JanHerman\SimpleTaxonomies\Term;

return [
    'taxonomy' => function (string $taxonomy = 'categories'): Terms {
        $terms = Terms::factory();

        foreach ($this as $page) {
            $page_terms = $page->taxonomy($taxonomy);

            if ($page_terms) {
                foreach ($page_terms as $key => $value) {
                    $terms->append($key, $value);
                }
            }
        }

        return $terms;
    },
    'filterByTerms' => function (string $taxonomy = 'categories', Terms|Term|null $terms = null, bool|int $min_matches = true): Pages {
        if ($terms === null) {
            $terms = $this->parent()->openTerms($taxonomy);

            if ($terms->isEmpty()) {
                return $this;
            }
        }

        $term_uuids = [];
        $separator = option('jan-herman.simple-taxonomies.fieldValueSeparator');

        if ($terms instanceof Terms) {
            $term_uuids = $terms->values(function ($term) {
                return $term->uuid()->toString();
            });
        } elseif ($terms instanceof Term) {
            $term_uuids[] = $terms->uuid()->toString();
        }

        if ($min_matches === true) {
            return $this->filterBy($taxonomy, 'in', $term_uuids, $separator);
        }

        $min_matches = max(1, (int) $min_matches);

        return $this->filter(
            function ($page) use ($taxonomy, $term_uuids, $separator, $min_matches) {
                $page_uuids = $page->{$taxonomy}()->split($separator);
                return count(array_intersect($page_uuids, $term_uuids)) >= $min_matches;
            }
        );
    },
];
