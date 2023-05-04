<?php


namespace Cashew\Mapper;

use Cashew\Kernel\Mapper\AbstractMapper;

/**
 * Class ExampleMapper
 * @package Cashew\Mapper
 */
class ExampleMapper extends AbstractMapper {

    protected string $table = "example";

    protected array $columns = [
        "id" => self::DATATYPE_INT,
    ];

}
