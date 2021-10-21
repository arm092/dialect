<?php

namespace Src;

class MockJsonDialectModel extends \Illuminate\Database\Eloquent\Model
{
    use \Eloquent\Dialect\Json;

    protected array $jsonColumns;

//    public function __construct(array $attributes = array())
//    {
//        static::$booted[get_class($this)] = true;
//        parent::__construct($attributes);
//    }

    public function setJsonColumns(Array $columns)
    {
        $this->jsonColumns = $columns;
    }

    public function getCustomGetAttribute(): string
    {
        return "custom getter result";
    }

    public function setCustomSetAttribute( $value )
    {
        $this->setJsonAttribute($this->jsonAttributes['custom_set'], 'custom_set', "custom {$value}");
    }
}
