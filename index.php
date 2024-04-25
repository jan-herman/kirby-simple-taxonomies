<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Pages;
use Kirby\Content\Field;
use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Query\Query;
use JanHerman\SimpleTaxonomies\Terms;
use JanHerman\SimpleTaxonomies\Term;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('jan-herman/simple-taxonomies', [
    'options' => [
        'paramValue' => 'slug',
        'paramValueSeparator' => '+',
        'fieldValueSeparator' => ',',
    ],
    'pagesMethods' => [
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
        'filterByTerms' => function (string $taxonomy = 'categories', Terms $terms = null): Pages {
            $terms = $terms ?: $this->parent()->openTerms($taxonomy);

            if ($terms->isEmpty()) {
                return $this;
            }

            $separator = option('jan-herman.simple-taxonomies.fieldValueSeparator');
            $term_uuids = $terms->values(function ($term) {
                return $term->uuid()->toString();
            });

            return $this->filterBy($taxonomy, 'in', $term_uuids, $separator);
        },
        // deprecated
        'taxonomyTerms' => function (string $taxonomy = 'categories'): Terms {
            return $this->taxonomy($taxonomy);
        },
    ],
    'pageMethods' => [
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
        // deprecated
        'taxonomyTerms' => function (string $taxonomy = 'categories'): Terms {
            return $this->taxonomy($taxonomy);
        },
        'taxonomyArchiveSlug' => function (string $taxonomy = 'categories'): string {
            return $this->taxonomyParam($taxonomy);
        },
    ],
    'fieldMethods' => [
        'toTaxonomy' => function (Field $field): Terms {
            try {
                return Terms::factory(
                    Data::decode($field->value, 'yaml'),
                    ['parent' => $field->parent(), 'field' => $field]
                );
            } catch (Exception) {
                $message = 'Invalid structure data for "' . $field->key() . '" field';

                if ($parent = $field->parent()) {
                    $message .= ' on parent "' . $parent->id() . '"';
                }

                throw new InvalidArgumentException($message);
            }
        },
        'toTerms' => function (Field $field): Terms {
            $taxonomy_query = $field->parent()->blueprint()->field($field->key())['options']['query'];
            $query = new Query($taxonomy_query);
            $taxonomy = $query->resolve([
                'page' => $field->parent()
            ]);
            $uuids = $field->split();
            $separator = option('jan-herman.simple-taxonomies.fieldValueSeparator');

            if ($taxonomy->isEmpty() || !$uuids) {
                return Terms::factory();
            }

            return $taxonomy->filterBy('uuid', 'in', $uuids, $separator);
        }
    ],
    'validators' => [
        'unique' => function ($value, $field) {
            $values = array_column(Yaml::decode($value), $field);
            $is_valid = count($values) === count(array_flip($values));

            if (!$is_valid) {
                throw new Exception(tt('jan-herman.simple-taxonomies.validators.unique.exception', ['field' => $field]));
            }

            return $is_valid;
        }
    ],
    'fields' => [
        'synced-structure' => require_once 'synced-structure-field/synced-structure.php',
    ],
    'blueprints' => [
        'fields/taxonomy'       => __DIR__ . '/blueprints/fields/taxonomy.yml',
        'fields/taxonomy-slug'  => __DIR__ . '/blueprints/fields/taxonomy-slug.yml',
        'fields/taxonomy-terms' => __DIR__ . '/blueprints/fields/taxonomy-terms.yml',
    ],
    'translations' => [
        'en' => [
            'jan-herman.simple-taxonomies.validators.unique.exception' => 'Error: Field \'{ field }\' must be unique.',
            'jan-herman.simple-taxonomies.fields.taxonomy-terms.label' => 'Categories',
            'jan-herman.simple-taxonomies.fields.taxonomy-slug.label' => 'Category Archive URL',
            'jan-herman.simple-taxonomies.fields.taxonomy-slug.after' => ':category-slug',
            'jan-herman.simple-taxonomies.fields.taxonomy.label' => 'Categories',
            'jan-herman.simple-taxonomies.fields.taxonomy.empty' => 'You haven\'t created any categories yet.',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.title.label' => 'Title',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.slug.label' => 'Slug',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.singular_title.label' => 'Singular Title',
        ],
        'cs' => [
            'jan-herman.simple-taxonomies.validators.unique.exception' => 'Chyba: Pole \'{ field }\' musí být unikátní.',
            'jan-herman.simple-taxonomies.fields.taxonomy-terms.label' => 'Kategorie',
            'jan-herman.simple-taxonomies.fields.taxonomy-slug.label' => 'URL archivu kategorie',
            'jan-herman.simple-taxonomies.fields.taxonomy-slug.after' => ':nazev-kategorie',
            'jan-herman.simple-taxonomies.fields.taxonomy.label' => 'Kategorie',
            'jan-herman.simple-taxonomies.fields.taxonomy.empty' => 'Zatím jste nevytvořili žádné kategorie.',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.title.label' => 'Název',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.slug.label' => 'Název v URL',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.singular_title.label' => 'Název v jednotném čísle',
        ]
    ]
]);
