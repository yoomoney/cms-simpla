<?php

namespace YaMoney\ConfigParser;

/**
 * Класс настроек свойств классов и скалярных типов
 *
 * @package YaMoney\ConfigParser
 */
class PropertyOptions
{
    const MULTIPLE_OF = 'multipleOf';
    const MAXIMUM = 'maximum';
    const MAXIMUM_EXCLUSIVE = 'exclusiveMaximum';
    const MINIMUM = 'minimum';
    const MINIMUM_EXCLUSIVE = 'exclusiveMinimum';
    const MAX_LENGTH = 'maxLength';
    const MIN_LENGTH = 'minLength';
    const PATTERN = 'pattern';
    const MAX_ITEMS = 'maxItems';
    const MIN_ITEMS = 'minItems';
    const UNIQUE_ITEMS = 'uniqueItems';
    const MAX_PROPERTIES = 'maxProperties';
    const MIN_PROPERTIES = 'minProperties';
    const REQUIRED = 'required';
    const ENUM = 'enum';

    /**
     * @var array Массив свойств, которые можно устанавливать
     */
    static private $validOptions = array(
        self::MULTIPLE_OF       => false,
        self::MAXIMUM           => true,
        self::MAXIMUM_EXCLUSIVE => true,
        self::MINIMUM           => true,
        self::MINIMUM_EXCLUSIVE => true,
        self::MAX_LENGTH        => true,
        self::MIN_LENGTH        => true,
        self::PATTERN           => true,
        self::MAX_ITEMS         => true,
        self::MIN_ITEMS         => true,
        self::UNIQUE_ITEMS      => false,
        self::MAX_PROPERTIES    => false,
        self::MIN_PROPERTIES    => false,
        self::REQUIRED          => true,
        self::ENUM              => true,
    );

    /**
     * @var array Массив установленных свойств свойства или скалярного типа
     */
    private $options;

    /**
     * @param array $options Массив настроек свойства или типа
     */
    public function __construct($options)
    {
        $this->options = array();
        foreach ($options as $optionName => $value) {
            if (self::isOptionExists($optionName)) {
                $this->options[$optionName] = $value;
            }
        }
    }

    /**
     * Проверяет существует ли свойство
     * @param string $option Имя проверяемого свойства
     * @return bool True если свойство имеется, false если нет
     */
    static public function isOptionExists($option)
    {
        return array_key_exists($option, self::$validOptions) && self::$validOptions[$option];
    }

    /**
     * Возвращает значение свойства по его имени
     * @param string $optionName Имя свойства
     * @return mixed Значение свойства
     */
    public function getOption($optionName)
    {
        if ($this->hasOption($optionName)) {
            return $this->options[$optionName];
        }
        return null;
    }

    /**
     * Проверяет, задано ли переданное свойство
     * @param string $optionName Имя свойства
     * @return bool True если свойство задано, false если нет
     */
    public function hasOption($optionName)
    {
        return array_key_exists($optionName, $this->options);
    }
}