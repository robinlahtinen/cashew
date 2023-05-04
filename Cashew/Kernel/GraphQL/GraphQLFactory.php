<?php


namespace Cashew\Kernel\GraphQL;

use Cashew\GraphQL\Schema\Schema;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;

class GraphQLFactory {

    /**
     * @var string[] Schemas
     */
    private array $schemas = [
        "schema.graphql"
    ];

    public function getKernelGraphQL(): GraphQL {
        $graphQL = new GraphQL();

        $graphQL->setSchema(new Schema());
        $graphQL->setConfig($this->createConfig($graphQL));
        $graphQL->setServer(new StandardServer($graphQL->getConfig()));

        return $graphQL;
    }

    /**
     * @param GraphQL $graphQL
     * @return ServerConfig
     */
    protected function createConfig(GraphQL $graphQL): ServerConfig {
        return ServerConfig::create()->setSchema($graphQL->getSchema())->setQueryBatching(true);
    }

    /**
     * @return string[]
     */
    protected function getSchemas(): array {
        return $this->schemas;
    }

    /**
     * @param string[] $schemas
     */
    protected function setSchemas(array $schemas): void {
        $this->schemas = $schemas;
    }

}
