<?php


namespace Cashew\Kernel\GraphQL;

use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Schema;

class GraphQL {

    /**
     * @var Schema
     */
    private Schema $schema;

    /**
     * @var StandardServer
     */
    private StandardServer $server;

    /**
     * @var ServerConfig
     */
    private ServerConfig $config;

    /**
     * @return Schema
     */
    public function getSchema(): Schema {
        return $this->schema;
    }

    /**
     * @param Schema $schema
     */
    public function setSchema(Schema $schema): void {
        $this->schema = $schema;
    }

    /**
     * @return StandardServer
     */
    public function getServer(): StandardServer {
        return $this->server;
    }

    /**
     * @param StandardServer $server
     */
    public function setServer(StandardServer $server): void {
        $this->server = $server;
    }

    /**
     * @return ServerConfig
     */
    public function getConfig(): ServerConfig {
        return $this->config;
    }

    /**
     * @param ServerConfig $config
     */
    public function setConfig(ServerConfig $config): void {
        $this->config = $config;
    }

}
