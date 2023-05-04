<?php


namespace Cashew\GraphQL\Type;

use Cashew\Kernel\GraphQL\ObjectTypeSingleton;

class QueryType extends ObjectTypeSingleton {

    public function __construct() {
        parent::__construct([
            "fields" => []
        ]);
    }

}
