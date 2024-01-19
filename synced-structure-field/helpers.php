<?php

use Kirby\Form\Field;

function uuid_structure(array $data): array
{
    $result = [];

    foreach ($data as $value) {
        $uuid = $value['uuid'] ?? uuid();

        if (isset($result[$uuid])) {
            $uuid = $value['uuid'] = uuid();
        }

        $result[$uuid] = $value;
    }

    return $result;
}

function sync_structure(Field $field, array $data): array
{
    $kirby = kirby();

    $currentLocale = $kirby->languageCode();

    $fields = $field->fields;
    $model  = $field->model;
    $name   = $field->name;
    $update = [];

    $data = uuid_structure($data);

    foreach ($kirby->languages()->codes() as $locale) {
        if ($locale === $currentLocale) {
            $update[$locale] = $data;
            continue;
        }

        if (!$model->translation($locale)->exists()) {
            continue;
        }

        if ($model->content($locale)->{$name}()->isEmpty()) {
            continue;
        }

        $values = $model->content($locale)->{$name}()->yaml();
        $values = uuid_structure($values);

        foreach (array_keys($data) as $uuid) {
            $update[$locale][$uuid] = $values[$uuid] ?? $data[$uuid];
        }
    }

    foreach ($update as $locale => $values) {
        $update[$locale] = sync_structure_fields($fields, $values, $data);

        if ($locale !== $currentLocale) {
            $model->save([
                $name => array_values($update[$locale]),
            ], $locale);
        }
    }

    return array_values($update[$currentLocale]);
}

function sync_structure_fields(array $fields, array $values, array $data): array
{
    foreach ($fields as $name => $field) {
        $sync = $field['sync'] ?? false;

        if ($sync === false) {
            continue;
        }

        foreach (array_keys($values) as $uuid) {
            if (isset($data[$uuid][$name])) {
                $values[$uuid][$name] ??= $data[$uuid][$name];
            }
        }
    }

    return $values;
}
