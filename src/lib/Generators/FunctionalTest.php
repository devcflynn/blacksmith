<?php namespace Generators;


use DateTime;
use Illuminate\Support\Str;

class FunctionalTest extends Generator implements GeneratorInterface
{

    /**
     * Function to get the minimum template variables
     * 
     * @return array
     */
    public function getTemplateVars()
    {
        $entity    = $this->getEntityName();
        $fieldData = $this->getFieldData();

        $attributes = [];

        //get fake data for each property
        foreach ($fieldData as $property => $meta) {
            $attributes[$property] = $this->getAttributeMockValue($meta['type']);
        }

        //add override for specific cases like id, created_at etc
        $attributes['id'] = 1;
        $attributes['created_at'] = "'". date('Y-m-d H:00:00') ."'";
        $attributes['updated_at'] = "'". date('Y-m-d H:00:00') ."'";

        $mock_attribute_rows = [];

        //create a "row" for each entry
        foreach ($attributes as $key => $value) {
            $mock_attribute_rows[] = "'{$key}' => {$value},";
        }

        return [
            'Entity'          => Str::studly($entity),
            'Entities'        => Str::plural(Str::studly($entity)),
            'collection'      => Str::plural(Str::snake($entity)),
            'instance'        => Str::singular(Str::snake($entity)),
            'fields'          => $fieldData,
            'mock_attributes' => $mock_attribute_rows,
            'year'       => (new DateTime())->format('Y')
        ];
    }


    /**
     * Function to return the form element type
     * that should be used, given the datatype input
     * 
     * @param  string $dataType Field data type
     * @return string           Form element type to use
     */
    public function getAttributeMockValue($dataType)
    {
        $lookup = [
            'integer'    => 1,
            'biginteger' => PHP_INT_MAX,
            'string'     => "'dreamcatcher'",
            'decimal'    => 12345.12,
            'float'      => 152853.5047,
            'timestamp'  => time(),
            'date'       => "'". date('Y-m-d') ."'",
            'datetime'   => "'". date('Y-m-d H:i:s') ."'",
            'text'       => "'Tonx art party PBR&B, Blue Bottle sriracha Bushwick iPhone wolf kale chips Godard typewriter selfies shabby chic church-key 3 wolf moon'",
            'boolean'    => true
        ];

        $key = strtolower($dataType);
        return array_key_exists($key, $lookup) ? $lookup[$key] : "'text'";
    }
}
