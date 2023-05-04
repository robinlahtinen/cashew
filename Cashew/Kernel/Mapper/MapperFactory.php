<?php


namespace Cashew\Kernel\Mapper;


use Cashew\Kernel\Kernel;
use Cashew\Kernel\Log\Log;
use DateTime;
use PDO;
use PDOStatement;

class MapperFactory {

    /**
     * @var AbstractMapper New mapper instance.
     */
    protected AbstractMapper $baseMapper;

    /**
     * @var array
     */
    protected array $fields = [];

    /**
     * @var array
     */
    protected array $fieldsNot = [];

    protected array $ors = [];

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function whereField(string $field, $value): MapperFactory {
        $this->setField($field, $value);

        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    public function setField(string $field, $value): void {
        $fields = $this->getFields();

        $fields[$field] = $value;

        $this->setFields($fields);
    }

    /**
     * @return array
     */
    public function getFields(): array {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void {
        $this->fields = $fields;
    }

    /**
     * @param string $field
     * @param $value
     * @return $this
     */
    public function whereFieldNot(string $field, $value): MapperFactory {
        $this->setFieldNot($field, $value);

        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    public function setFieldNot(string $field, $value): void {
        $fields = $this->getFieldsNot();

        $fields[$field] = $value;

        $this->setFieldsNot($fields);
    }

    /**
     * @return array
     */
    public function getFieldsNot(): array {
        return $this->fieldsNot;
    }

    /**
     * @param array $fieldsNot
     */
    public function setFieldsNot(array $fieldsNot): void {
        $this->fieldsNot = $fieldsNot;
    }

    /**
     * @return $this
     */
    public function or(): MapperFactory {
        $ors = $this->getOrs();
        $ors[$this->countFields()] = true;

        $this->setOrs($ors);

        return $this;
    }

    /**
     * @return array
     */
    public function getOrs(): array {
        return $this->ors;
    }

    /**
     * @param array $ors
     */
    public function setOrs(array $ors): void {
        $this->ors = $ors;
    }

    /**
     * @return int
     */
    public function countFields(): int {
        return count($this->getFields()) + count($this->getFieldsNot());
    }

    /**
     * Reset mapper instance.
     *
     * @param AbstractMapper $mapper
     */
    public function resetMapper(AbstractMapper $mapper): void {
        $newMapper = $mapper->getFactory()->getMapperByPrimaryKey($mapper->getField($mapper->getPrimaryKey()));

        $mapper->setIsNew($newMapper->isNew());
        $mapper->setFields($newMapper->getFields());
        $mapper->setColumns($newMapper->getColumns());
        $mapper->setPrimaryKey($newMapper->getPrimaryKey());
        $mapper->setTable($newMapper->getTable());
    }

    /**
     * @param int $id
     * @return AbstractMapper
     */
    public function getMapperByPrimaryKey(int $id): AbstractMapper {
        return $this->getMapperByField($this->getBaseMapper()->getPrimaryKey(), $id);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return AbstractMapper
     */
    public function getMapperByField(string $field, $value): AbstractMapper {
        $this->setField($field, $value);

        return $this->getMapper();
    }

    /**
     * @return AbstractMapper Mapper instance.
     */
    public function getMapper(): AbstractMapper {
        $mapper = $this->getBaseMapper();

        $mappers = $this->getMappers();

        if (!empty($mappers[0])) {
            $mapper = $mappers[0];
        }

        return $mapper;
    }

    /**
     * @return AbstractMapper
     * @psalm-suppress UndefinedClass
     */
    public function getBaseMapper(): AbstractMapper {
        return clone $this->baseMapper;
    }

    /**
     * @param AbstractMapper $baseMapper
     */
    public function setBaseMapper(AbstractMapper $baseMapper): void {
        $this->baseMapper = $baseMapper;
    }

    /**
     * @return AbstractMapper[] Mapper instances.
     */
    public function getMappers(): array {
        return $this->populateMappers($this->getRowsFromDb());
    }

    /**
     * @param array $rows
     * @return array
     * @throws \Exception
     */
    protected function populateMappers(array $rows): array {
        $mappers = [];

        $rowsAmount = count($rows);
        $mapperName = (string)get_class($this->getBaseMapper());

        Log::debug("Populating mappers from rows. (Rows: {$rowsAmount}) (Mapper: {$mapperName})");

        $defaultColumns = $this->getBaseMapper()->getColumns();

        /**
         * @var array $row
         */
        foreach ($rows as $row) {
            $mapper = $this->getBaseMapper();

            $mapper->setIsNew(false);

            /**
             * @var string $column
             * @var string|int $value
             */
            foreach ($row as $column => $value) {
                $defaultColumn = $defaultColumns[$column];

                if (array_key_exists($column, $defaultColumns)) {
                    if ($defaultColumn === AbstractMapper::DATATYPE_INT) {
                        $value = (int)$value;
                    }

                    if ($defaultColumn === AbstractMapper::DATATYPE_BOOL) {
                        if ($value > 0) {
                            if ($value > 1) {
                                Log::notice("Boolean data type is more than 1. (Value: {$value}) (Mapper: {$mapperName})");
                            }

                            $value = true;
                        } else {
                            $value = false;
                        }
                    }

                    if ($defaultColumn === AbstractMapper::DATATYPE_ARRAY) {
                        $value = (array)json_decode($value, true);
                    }

                    if ($defaultColumn === AbstractMapper::DATATYPE_FLOAT) {
                        $value = (float)$value;
                    }

                    if ($defaultColumn === AbstractMapper::DATATYPE_DATETIME) {
                        $value = new DateTime($value);
                    }

                    $mapper->setField($column, $value);
                }
            }

            $mappers[] = $mapper;
        }

        return $mappers;
    }

    protected function getRowsFromDb(): array {
        $rows = [];

        $newMapper = $this->getBaseMapper();
        $mapperName = (string)get_class($newMapper);

        $table = $newMapper->getTable();
        $primaryKey = $newMapper->getPrimaryKey();

        Log::debug("Fetching rows from table: {$table} (Mapper: {$mapperName})");

        $conditions = "";
        $condition = [];

        foreach ($this->getFields() as $field => $fieldValue) {
            if (is_array($fieldValue)) {
                $fieldValue = implode(",", $fieldValue);

                $condition[] = "{$table}.{$field} IN ({$fieldValue})";
            } else {
                $condition[] = "{$table}.{$field} = :{$field}";
            }

            if (!empty($this->getOrs()[count($condition) + 1]) && $this->getOrs()[count($condition) + 1] === true) {
                $condition[] = "OR";
            }
        }

        foreach ($this->getFieldsNot() as $fieldNot => $fieldNotValue) {
            if (is_array($fieldNotValue)) {
                $fieldNotValue = implode(",", $fieldNotValue);

                $condition[] = "{$table}.{$fieldNot} NOT IN ({$fieldNotValue})";
            } else {
                $condition[] = "{$table}.{$fieldNot} != :{$fieldNot}";
            }
        }

        if (!empty($condition)) {
            foreach ($condition as $cond) {
                if ($cond === "OR") {
                    $conditions .= $cond;

                    continue;
                }

                if (empty($conditions)) {
                    $conditions .= "WHERE " . $cond;

                    continue;
                }

                $conditions .= " AND " . $cond;
            }
        }

        $selects = implode(",", $newMapper->getColumnsForDb());
        $order = "ORDER BY {$table}.{$primaryKey} DESC";

        $sql = "SELECT {$selects}
                FROM {$table}
                {$conditions}
                {$order}";

        $pdo = Kernel::getInstance()->getPdo();

        $statement = $pdo->prepare($sql);

        $this->bindValues($statement, $this->getFields(), false);
        $this->bindValues($statement, $this->getFieldsNot(), false);

        if ($statement->execute()) {
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $errorCode = $statement->errorCode();

            Log::error("Mapper factory fetch statement query execution failed. (Query: {$sql}) (Code: {$errorCode})", $statement->errorInfo());
        }

        return $rows;
    }

    /**
     * @param PDOStatement $statement
     * @param array $fields
     */
    protected function bindValues(PDOStatement $statement, array $fields, bool $bindArray = true): void {
        foreach ($fields as $field => $fieldValue) {
            $bindType = PDO::PARAM_STR;

            if (is_bool($fieldValue)) {
                $bindType = PDO::PARAM_BOOL;

                if ($fieldValue === true) {
                    $fieldValue = 1;
                } else {
                    $fieldValue = 0;
                }
            }

            if (is_array($fieldValue)) {
                if ($bindArray === false) {
                    continue;
                }

                $fieldValue = json_encode($fieldValue, JSON_UNESCAPED_UNICODE);
            }

            if (is_int($fieldValue)) {
                $bindType = PDO::PARAM_INT;
            }

            if (is_float($fieldValue)) {
                $bindType = PDO::PARAM_STR;
            }

            if ($fieldValue instanceof DateTime) {
                $fieldValue = $fieldValue->format("Y-m-d H:i:s");
            }

            $statement->bindValue($field, $fieldValue, $bindType);
        }
    }

    /**
     * Save mappers to database.
     *
     * @param AbstractMapper[] $mappers Mapper instances.
     */
    public function saveMappers(array $mappers): void {
        foreach ($mappers as $mapper) {
            $mapper->save();
        }
    }

    /**
     * Save mapper instance to database.
     *
     * @param AbstractMapper $mapper Mapper instance.
     */
    public function saveMapper(AbstractMapper $mapper): void {
        $table = $mapper->getTable();
        $mapperName = (string)get_class($mapper);

        if ($mapper->isNew()) {
            Log::debug("Saving a new mapper. (Mapper: {$mapperName})");

            $sqlField = [];
            $sqlValue = [];

            foreach ($mapper->getFields() as $field => $fieldValue) {
                $sqlField[] = $field;
                $sqlValue[] = ":" . $field;
            }

            $fields = implode(",", $sqlField);
            $values = implode(",", $sqlValue);

            $sql = "INSERT INTO {$table} ($fields)
                    VALUES ($values)";
        } else {
            $primaryKey = $mapper->getPrimaryKey();
            $primaryValue = $mapper->getField($primaryKey);

            Log::debug("Saving an existing mapper with {$primaryKey}: {$primaryValue} (Mapper: {$mapperName})");

            $set = [];

            foreach ($mapper->getFields() as $field => $fieldValue) {
                $set[] = $field . "=" . ":" . $field;
            }

            $sets = implode(",", $set);

            $sql = "UPDATE {$table}
                    SET {$sets}
                    WHERE {$primaryKey}={$primaryValue}";
        }

        $pdo = Kernel::getInstance()->getPdo();

        $pdo->beginTransaction();

        $statement = $pdo->prepare($sql);

        $this->bindValues($statement, $mapper->getFields());

        if ($statement->execute()) {
            if ($mapper->isNew() && $mapper->isPrimaryKeyAutoIncrement()) {
                $lastInsertId = $pdo->lastInsertId();

                $mapper->setIsNew(false);
                $mapper->setField($mapper->getPrimaryKey(), $lastInsertId);

                Log::debug("New mapper saved with id: {$lastInsertId} (Mapper: {$mapperName})");
            }

            $pdo->commit();
        } else {
            $errorCode = $statement->errorCode();

            Log::error("Mapper factory statement query execution failed. (Query: {$sql}) (Code: {$errorCode})", $statement->errorInfo());

            $pdo->rollBack();
        }
    }

}
