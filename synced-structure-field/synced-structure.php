<?php

// Source: https://gist.github.com/lukaskleinschmidt/1c0b94ffab51d650b7c7605a4d25c213
// https://github.com/getkirby/kirby/blob/main/config/fields/structure.php

use Kirby\Form\Form;

@include_once __DIR__ . '/helpers.php';

return [
    'extends' => 'structure',
    'methods' => [
        'form' => function (array $values = []) {
            $uuid = uuid();

            $fields = $this->attrs['fields'] + [
                'uuid' => [
                    'type' => 'hidden',
                    'default' => $uuid,
                ],
            ];

            foreach ($fields as &$field) {
                if (isset($field['translate'])) {
                    $field['sync'] = !$field['translate'];
                    unset($field['translate']);
                }
            }

            $values['uuid'] ??= $uuid;

            return new Form([
                'fields' => $fields,
                'values' => $values,
                'model'  => $this->model,
            ]);
        },
    ],
    'save' => function ($value) {
        $data = [];

        foreach ($value as $row) {
            $row = $this->form($row)->content();

            // remove frontend helper id
            unset($row['_id']);

            $data[] = $row;
        }

        if (kirby()->multilang() === true) {
            return sync_structure($this, $data);
        }

        return $data;
    },
];
