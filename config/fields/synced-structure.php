<?php

// Source: https://gist.github.com/tobimori/eb855fe425edb44f5e8a62acbd866ead

use Kirby\Cms\ModelWithContent;
use Kirby\Form\Form;
use Kirby\Form\Field as FormField;

return [
    'extends' => 'structure',
    'methods' => [
        'form' => function () {
            $this->syncedStructureFields ??= SyncedStructure::normalizeFields($this->attrs['fields'] ?? []);

            $this->form ??= new Form(
                fields: $this->syncedStructureFields,
                model: $this->model(),
                language: 'current'
            );

            return $this->form->reset();
        },
    ],
    'save' => function ($value) {
        $form     = $this->form();
        $defaults = $form->defaults();
        $data     = [];

        foreach ($value as $row) {
            $row = $form
                ->reset()
                ->fill(
                    input: $defaults
                )
                ->submit(
                    input: $row,
                    passthrough: true
                )
                ->toStoredValues();

            // remove frontend helper id
            unset($row['_id']);

            $row['uuid'] ??= uuid();

            $data[] = $row;
        }

        if (kirby()->multilang()) {
            return SyncedStructure::sync($this, $data, $form);
        }

        return $data;
    },
];

class SyncedStructure
{
	public static function normalizeFields(array $fields): array
	{
		foreach ($fields as $name => $field) {
			$field['sync'] ??= false;

			if (array_key_exists('translate', $field)) {
				$field['sync'] = $field['sync'] || $field['translate'] === false;
				unset($field['translate']);
			}

			$fields[$name] = $field;
		}

		return $fields;
	}
	public static function generateUuids(array $data): array
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

	public static function sync(FormField $field, array $data, Form|null $form = null): array
	{
		$kirby = kirby();

		$currentLocale = $kirby->languageCode();

		$form ??= $field->form();
		$fields = $field->syncedStructureFields ?? [];
		$model  = $field->model();
		$name   = $field->name;
		$update = [];

		$data = static::generateUuids($data);

		foreach ($kirby->languages()->codes() as $locale) {
			if ($locale === $currentLocale) {
				$update[$locale] = $data;
				continue;
			}

			if (!$model?->translation($locale)->exists()) {
				continue;
			}

			if ($model->content($locale)->{$name}()->isEmpty()) {
				continue;
			}

			$values = $model->content($locale)->{$name}()->yaml();
			$values = static::generateUuids($values);

			foreach (array_keys($data) as $uuid) {
				$update[$locale][$uuid] = $values[$uuid] ?? $data[$uuid];
			}
		}

		foreach ($update as $locale => $values) {
			$update[$locale] = static::syncFields($fields, $values, $data);

			if ($locale !== $currentLocale) {
				static::updateLocale($model, $name, $update[$locale], $locale);
			}
		}

		return array_values($update[$currentLocale]);
	}

	protected static function updateLocale(ModelWithContent $model, string $name, array $values, string $locale): void
	{
		$payload = [$name => array_values($values)];

		foreach (['changes', 'latest'] as $versionId) {
			$version = $model->version($versionId);

			if ($version->exists($locale) === false) {
				continue;
			}

			$version->update($payload, $locale);
		}
	}

	public static function syncFields(array $fields, array $values, array $data): array
	{
		foreach ($fields as $name => $field) {
			$sync = $field['sync'] ?? false;

			if ($sync === false) {
				continue;
			}

			foreach (array_keys($values) as $uuid) {
				if (isset($data[$uuid][$name])) {
					$values[$uuid][$name] = $data[$uuid][$name];
				}
			}
		}

		return $values;
	}
}
