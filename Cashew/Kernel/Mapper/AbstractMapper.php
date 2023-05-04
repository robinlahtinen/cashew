<?php


namespace Cashew\Kernel\Mapper;

use Cashew\Kernel\Log\Log;
use DateTime;
use Exception;

/**
 * Class AbstractMapper
 * @package Cashew\Kernel\Mapper
 */
abstract class AbstractMapper {

    public const DATATYPE_INT = 0;

    public const DATATYPE_VARCHAR = 1;

    public const DATATYPE_BOOL = 2;

    public const DATATYPE_ARRAY = 3;

    public const DATATYPE_TEXT = 4;

    public const DATATYPE_FLOAT = 5;

    public const DATATYPE_DATETIME = 6;

    protected array $columns = [
        "id" => self::DATATYPE_INT
    ];

    protected array $fields = [];

    protected string $primaryKey = "id";

    protected bool $primaryKeyAutoIncrement = true;

    /**
     * @var string Table name.
     */
    protected string $table = "";

    /**
     * @var bool Is there an entry on database.
     */
    protected bool $isNew = true;

    /**
     * @var string[] Selected fields to populate.
     */
    protected array $selectedFields = [];

    /**
     * AbstractMapper constructor.
     * @param mixed $primaryValue Primary value.
     * @throws Exception
     */
    public function __construct($primaryValue = null) {
        if (isset($primaryValue)) {
            $factory = $this->getFactory();

            $this->setField($this->getPrimaryKey(), $primaryValue);
            $factory->resetMapper($this);
        }
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey(string $primaryKey): void {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @param int $id
     * @return $this
     */
    public static function get(int $id): AbstractMapper {
        return self::getMapperFactory()->getMapperByPrimaryKey($id);
    }

    /**
     * @param string $field
     * @param $value
     * @return MapperFactory
     */
    public static function getMapperFactory(): MapperFactory {
        return self::getMapper()->getFactory();
    }

    /**
     * @return MapperFactory
     */
    public function getFactory(): MapperFactory {
        $factory = new MapperFactory();
        $factory->setBaseMapper(clone $this);

        return $factory;
    }

    /**
     * @return $this
     */
    protected static function getMapper(): AbstractMapper {
        $class = static::class;

        /**
         * @var $this $mapper Mapper instance.
         */
        $mapper = new $class;

        return $mapper;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public static function getByField(string $field, $value): AbstractMapper {
        return self::getMapperFactory()->getMapperByField($field, $value);
    }

    /**
     * @return $this[] Mapper instances.
     * @throws Exception
     */
    public static function getAll(): array {
        return self::getMapperFactory()->getMappers();
    }

    /**
     * @param string $field
     * @param $value
     * @return $this[] Mapper instances.
     * @throws Exception
     */
    public static function getAllByField(string $field, $value): array {
        $factory = self::getMapperFactory();
        $factory->setField($field, $value);

        return $factory->getMappers();
    }

    /**
     * @param string $field
     * @param mixed $value
     * @throws Exception
     */
    public function setField(string $field, $value): void {
        $fields = $this->getFields();

        $fields[$field] = $value;

        $this->setFields($fields);
    }

    /**
     * @return array
     */
    public function getFields(array $fields = []): array {
        $values = $this->fields;

        if (!empty($fields)) {
            $selected = [];

            foreach ($values as $field => $value) {
                if (in_array($field, $fields)) {
                    $selected[$field] = $value;
                }
            }

            return $selected;
        }

        return $values;
    }

    /**
     * @param array $fields
     * @throws Exception Exception on non-valid field.
     */
    public function setFields(array $fields): void {
        $columns = $this->getColumns();

        foreach ($fields as $field => $fieldName) {
            if (!array_key_exists($field, $columns)) {
                throw new Exception("Trying to set field that doesn't exist. (Field: {$field}) (Name: {$fieldName}) (" . get_class($this) . ")");
            }
        }

        $this->fields = $fields;
    }

    public function save(): void {
        $this->getFactory()->saveMapper($this);
    }

    public function reset(): void {
        $this->getFactory()->resetMapper($this);
    }

    public function getColumnsForDb(): array {
        $columns = [];

        $prefix = "";

        if (!empty($this->getTable())) {
            $prefix = $this->getTable() . ".";
        }

        foreach ($this->getColumns() as $field => $type) {
            $columns[] = $prefix . $field;
        }

        return $columns;
    }

    /**
     * @return string
     */
    public function getTable(): string {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): void {
        $this->table = $table;
    }

    /**
     * @return int[]
     */
    public function getColumns() {
        $selectedFields = $this->getSelectedFields();

        if (!empty($selectedFields)) {
            $columns = [];

            foreach ($selectedFields as $selectedField) {
                if (in_array($selectedField, array_keys($this->columns))) {
                    $columns[$selectedField] = $this->columns[$selectedField];
                }
            }

            return $columns;
        }

        return $this->columns;
    }

    /**
     * @param array|int[] $columns
     */
    public function setColumns($columns): void {
        $this->columns = $columns;
    }

    /**
     * @return string[]
     */
    public function getSelectedFields(): array {
        return $this->selectedFields;
    }

    /**
     * @param string[] $selectedFields
     */
    public function setSelectedFields(array $selectedFields): void {
        $this->selectedFields = $selectedFields;
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function getField(string $field) {
        $value = null;

        if (isset($this->getFields()[$field])) {
            $value = $this->getFields()[$field];
        } else {
            Log::debug("Field is not set. (Field: {$field})");

            if (isset($this->getColumns()[$field])) {
                $fieldType = $this->getColumns()[$field];

                if ($fieldType === self::DATATYPE_INT) {
                    $value = 0;
                }

                if (in_array($fieldType, [self::DATATYPE_VARCHAR, self::DATATYPE_TEXT])) {
                    $value = "";
                }

                if ($fieldType === self::DATATYPE_ARRAY) {
                    $value = [];
                }

                if ($fieldType === self::DATATYPE_FLOAT) {
                    $value = 0.0;
                }

                if ($fieldType === self::DATATYPE_DATETIME) {
                    try {
                        $value = new DateTime("now");
                    } catch (Exception $exception) {
                        Log::error($exception->getMessage(), $exception->getTrace());
                    }
                }

                if ($fieldType === self::DATATYPE_BOOL) {
                    $value = false;
                }
            }
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function isNew(): bool {
        return $this->isNew;
    }

    /**
     * @param bool $isNew
     */
    public function setIsNew(bool $isNew): void {
        $this->isNew = $isNew;
    }

    /**
     * @return bool
     */
    public function isPrimaryKeyAutoIncrement(): bool {
        return $this->primaryKeyAutoIncrement;
    }

    /**
     * @param bool $primaryKeyAutoIncrement
     */
    public function setPrimaryKeyAutoIncrement(bool $primaryKeyAutoIncrement): void {
        $this->primaryKeyAutoIncrement = $primaryKeyAutoIncrement;
    }

}
