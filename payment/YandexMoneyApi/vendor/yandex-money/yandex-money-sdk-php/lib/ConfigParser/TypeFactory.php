<?php

namespace YaMoney\ConfigParser;

/**
 * Класс фабрики типов свойств и скалярных типов
 *
 * @package YaMoney\ConfigParser
 */
class TypeFactory
{
    /**
     * @var array Мапа для получения из алиасов нужного тмени типа
     */
    private $typeMap = array(
        'int'      => 'integer',
        'integer'  => 'integer',
        'int32'    => 'integer',
        'int64'    => 'integer',

        'float'    => 'float',
        'double'   => 'float',

        'string'   => 'string',

        'byte'     => 'byteArray',
        'binary'   => 'byteArray',

        'date'     => 'dateTime',
        'dateTime' => 'dateTime',

        'bool'     => 'boolean',
        'boolean'  => 'boolean',
    );

    /**
     * @var array Массив зарегестрированных и уникальных типов
     */
    private $scalarTypes = array();

    /**
     * Конструктор, инициализирует скалярные типы
     */
    public function __construct()
    {
        $this->scalarTypes = array(
            'integer'   => new Type\IntegerType(),
            'float'     => new Type\FloatType(),
            'string'    => new Type\StringType(),
            'byteArray' => new Type\ByteArrayType(),
            'boolean'   => new Type\BooleanType(),
            'dateTime'  => new Type\DateTimeType(),
        );
    }

    /**
     * Регестрирует класс как тип
     * @param ClassDefinition $definition Описание класса
     */
    public function registerClass(ClassDefinition $definition)
    {
        $this->scalarTypes[$definition->getClassName()] = new Type\ClassType($definition);
    }

    /**
     * Регестрирует описание скалярного типа как тип
     * @param ScalarParameterDefinition $definition Опсиание скалярного типа
     */
    public function registerScalar(ScalarParameterDefinition $definition)
    {
        $this->scalarTypes[$definition->getName()] = new Type\ScalarDefinitionType($definition);
    }

    /**
     * Регестрирует описание enum'a как тип
     * @param EnumDefinition $definition Опсиание enum'a
     */
    public function registerEnum(EnumDefinition $definition)
    {
        $this->scalarTypes[$definition->getClassName()] = new Type\EnumType($definition);
    }

    /**
     * Фабрика типов
     * @param string $type Имя типа, полученное из конфига
     * @param array $options Настройки свойства класса или типа
     * @return AbstractType Инстанс типа, подходыщего по смыслу
     */
    public function factoryType($type, $options)
    {
        if (array_key_exists($type, $this->typeMap)) {
            $type = $this->typeMap[$type];
        }
        if ($type === 'array') {
            $type = new Type\ArrayType();
            if (!empty($options['items'])) {
                if (isset($options['items']['type'])) {
                    $type->setItemsType($this->factoryType($options['items']['type'], $options['items']));
                } elseif (!empty($options['items']['$ref'])) {
                    $parts = explode('/', $options['items']['$ref']);
                    $className = end($parts);
                    $type->setItemsType($this->factoryType($className, $options['items']));
                }
                $type->setItemsOptions($options['items']);
            }
            return $type;
        }
        if ($type === 'string') {
            if (isset($options['format']) && strncmp($options['format'], 'date', 4) === 0) {
                $type = 'dateTime';
            }
        }
        if (array_key_exists($type, $this->scalarTypes)) {
            return $this->scalarTypes[$type];
        }
        if ($type === 'numeric' || $type === 'number') {
            $type = 'float';
            if (isset($options['format']) && strncmp($options['format'], 'int', 3) === 0) {
                $type = 'integer';
            }
            return $this->scalarTypes[$type];
        }
        return $this->scalarTypes['string'];
    }
}
