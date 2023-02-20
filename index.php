<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Page;
use Kirby\Cms\Structure;
use Kirby\Cms\StructureObject;

Kirby::plugin('jan-herman/simple-taxonomies', [
    'pagesMethods' => [
        'taxonomyTerms' => function (string $taxonomy = 'categories'): Structure
        {
            $terms = new Structure();

            foreach ($this as $page) {
                $page_terms = $page->taxonomyTerms($taxonomy);

                if ($page_terms) {
                    foreach ($page_terms as $key => $value) {
                        $terms->append($key, $value);
                    }
                }
            }

            return $terms;
        }
    ],
    'pageMethods' => [
        'taxonomyArchiveSlug' => function (string $taxonomy = 'categories'): string
        {
            return (string) param($this->{$taxonomy . '_slug'}()->toString() ?: 'category');
        },
        'isTaxonomyArchive' => function (string $taxonomy = 'categories'): bool
        {
            return (bool) $this->taxonomyArchiveSlug($taxonomy);
        },
        'taxonomyTerms' => function (string $taxonomy = 'categories'): Structure
        {
            $terms = $this?->content()->{$taxonomy}()?->toStructure();

            if (!$terms) {
                return new Structure();
            }

            $taxonomy_slug = $this->content()?->{$taxonomy . '_slug'}()->toString() ?: 'category';

            foreach ($terms as $term) {
                $term->content()->update([
                    'url' => url($this->url(), ['params' => [$taxonomy_slug => $term->slug()]])
                ]);
            }

            return $terms;
        },
        'taxonomyTerm' => function (string $term_slug, string $taxonomy = 'categories'): StructureObject
        {
            return $this->taxonomyTerms($taxonomy)->findBy('slug', $term_slug);
        },
        'terms' => function (string $taxonomy = 'categories', Page $taxonomy_page = null): Structure
        {
            $taxonomy_page = $taxonomy_page ?: $this->parent();
            $taxonomy_terms = $taxonomy_page->taxonomyTerms($taxonomy);
            $slugs = $this->content()->{$taxonomy}()?->split();

            if (!$taxonomy_terms || !$slugs) {
                return new Structure();
            }

            return $taxonomy_terms->filterBy('slug', 'in', $slugs);
        }
    ],
    'fieldMethods' => [
        'toTerms' => function ($field, Page $taxonomy_page = null): Structure
        {
            $taxonomy_page = $taxonomy_page ?: $field->parent()->parent();
            $taxonomy_terms = $taxonomy_page->taxonomyTerms($field->key());
            $slugs = $field->split();

            if (!$taxonomy_terms || !$slugs) {
                return new Structure();
            }

            return $taxonomy_terms->filterBy('slug', 'in', $slugs);
        }
    ],
    'blueprints' => [
        'fields/taxonomy'       => __DIR__ . '/blueprints/fields/taxonomy.yml',
        'fields/taxonomy-slug'  => __DIR__ . '/blueprints/fields/taxonomy-slug.yml',
        'fields/taxonomy-terms' => __DIR__ . '/blueprints/fields/taxonomy-terms.yml',
    ],
    'translations' => [
        'en' => [
            'jan-herman.simple-taxonomies.fields.taxonomy-terms.label' => 'Categories',
            'jan-herman.simple-taxonomies.fields.taxonomy-slug.label' => 'Category Archive URL',
            'jan-herman.simple-taxonomies.fields.taxonomy.label' => 'Categories',
            'jan-herman.simple-taxonomies.fields.taxonomy.empty' => 'You haven\'t created any categories yet.',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.title.label' => 'Title',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.slug.label' => 'Slug',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.singular_title.label' => 'Singular Title',
        ],
        'cs' => [
            'jan-herman.simple-taxonomies.fields.taxonomy-terms.label' => 'Kategorie',
            'jan-herman.simple-taxonomies.fields.taxonomy-slug.label' => 'URL archivu kategorie',
            'jan-herman.simple-taxonomies.fields.taxonomy.label' => 'Kategorie',
            'jan-herman.simple-taxonomies.fields.taxonomy.empty' => 'Zatím jste nevytvořili žádné kategorie.',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.title.label' => 'Název',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.slug.label' => 'Název v URL',
            'jan-herman.simple-taxonomies.fields.taxonomy.fields.singular_title.label' => 'Název v jednotném čísle',
        ]
    ]
]);
