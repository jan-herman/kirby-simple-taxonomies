<?php

use Kirby\Content\Field;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Query\Query;
use JanHerman\SimpleTaxonomies\Terms;

return [
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
    },
];
