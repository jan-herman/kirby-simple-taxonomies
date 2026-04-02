<?php

use Kirby\Data\Yaml;
use Kirby\Exception\Exception;

return [
    'unique' => function ($value, $field) {
        $values = array_column(Yaml::decode($value), $field);
        $is_valid = count($values) === count(array_flip($values));

        if (!$is_valid) {
            throw new Exception(tt('jan-herman.simple-taxonomies.validators.unique.exception', ['field' => $field]));
        }

        return $is_valid;
    }
];
