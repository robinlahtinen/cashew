<?php

namespace Cashew\GraphQL\Schema;

use Cashew\GraphQL\Type\MutationType;
use Cashew\GraphQL\Type\QueryType;

class Schema extends \GraphQL\Type\Schema {

    public function __construct() {
        parent::__construct([
            "query" => QueryType::get(),
            "mutation" => MutationType::get()
        ]);
    }

}
