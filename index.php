<?php

use Kirby\Cms\App as Kirby;

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('jan-herman/simple-taxonomies', [
    'options' => [
        'paramValue' => 'slug',
        'paramValueSeparator' => '+',
        'fieldValueSeparator' => ',',
    ],
    'pagesMethods' => require __DIR__ . '/config/pages-methods.php',
    'pageMethods' => require __DIR__ . '/config/page-methods.php',
    'fieldMethods' => require __DIR__ . '/config/field-methods.php',
    'validators' => require __DIR__ . '/config/validators.php',
    'fields' => [
        'synced-structure' => require_once __DIR__ . '/config/fields/synced-structure.php',
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
