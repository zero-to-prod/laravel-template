<?php

use App\Modules\Api\Api;
use App\Modules\Api\Support\Field;
use App\Routes\ApiRoutes;
use App\Routes\WebRoutes;
use Zerotoprod\DataModel\Describe;

if (! function_exists('api')) {
    function api(): ApiRoutes
    {
        return ApiRoutes::getInstance();
    }
}

if (! function_exists('web')) {
    function web(): WebRoutes
    {
        return WebRoutes::getInstance();
    }
}

if (! function_exists('api_response')) {
    function api_response(): Api
    {
        return app(Api::class);
    }
}

if (! function_exists('build_schema')) {
    function build_schema(string $class): array
    {
        $ReflectionClass = new ReflectionClass($class);
        $schema = [];

        foreach ($ReflectionClass->getProperties() as $Property) {
            if (! $Property->isPublic()) {
                continue;
            }

            $Type = $Property->getType();
            $field_attributes = $Property->getAttributes(Field::class);
            $Field = ! empty($field_attributes) ? $field_attributes[0]->newInstance() : null;
            $description = $Field?->description ?? '';
            $rules = $Field?->rules ?? '';
            $type_name = $Type?->getName() ?? 'mixed';

            $example = $Field?->example;

            $entry = [
                'type' => $type_name,
                'nullable' => $Type?->allowsNull() ?? true,
                ...($description !== '' ? ['description' => $description] : []),
                ...($rules !== '' ? ['rules' => $rules] : []),
                ...($example !== null ? ['example' => $example] : []),
            ];

            if ($type_name !== 'mixed' && ! in_array($type_name, ['string', 'int', 'float', 'bool', 'array', 'object'], true) && class_exists($type_name)) {
                $entry['type'] = class_basename($type_name);
                $entry['schema'] = build_schema($type_name);
            }

            $describe_attributes = $Property->getAttributes(Describe::class);
            if (! empty($describe_attributes)) {
                $args = $describe_attributes[0]->getArguments()[0] ?? [];
                $nested_type = $args['type'] ?? null;
                if ($nested_type !== null && class_exists($nested_type)) {
                    $entry['type'] = 'array';
                    $entry['items_type'] = class_basename($nested_type);
                    $entry['items'] = build_schema($nested_type);
                }
            }

            $schema[$Property->getName()] = $entry;
        }

        return $schema;
    }
}

if (! function_exists('render_url')) {
    function render_url(string $url, array $parameters): string
    {
        foreach ($parameters as $key => $parameter) {
            $url = str_replace("{{$key}}", $parameter, $url);
        }

        return $url;
    }
}
