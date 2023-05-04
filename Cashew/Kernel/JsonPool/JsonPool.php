<?php


namespace Cashew\Kernel\JsonPool;

class JsonPool {

    /**
     * @var string[]
     */
    protected array $json = [];

    /**
     * @return string
     */
    public function getJsonAsString(): string {
        $string = "";

        $jsonArray = $this->getJson();

        foreach ($jsonArray as $json) {
            if (!empty($string)) {
                $string .= "," . PHP_EOL;
            }

            $string .= $json;
        }

        if (count($jsonArray) > 1) {
            $string = "[" . $string . "]";
        }

        if (empty($string)) {
            $string = "{}";
        }

        return $string;
    }

    /**
     * @return string[]
     */
    public function getJson(): array {
        return $this->json;
    }

    /**
     * @param string[] $json
     */
    public function setJson(array $json): void {
        $this->json = $json;
    }

    /**
     * @param string $json
     */
    public function addJson(string $json): void {
        $array = $this->getJson();

        array_push($array, $json);

        $this->setJson($array);
    }

}
