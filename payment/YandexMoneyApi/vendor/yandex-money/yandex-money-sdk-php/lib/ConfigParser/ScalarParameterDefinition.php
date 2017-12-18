<?php

namespace YaMoney\ConfigParser;

/**
 * Класс описания именованного скалярного типа
 *
 * @package YaMoney\ConfigParser
 */
class ScalarParameterDefinition extends AbstractDefinition 
{
    /**
     * @var AbstractType Тип объекта
     */
    private $type;

    /**
     * @var PropertyOptions Свойства скалярного типа
     */
    private $options;

    /**
     * Устанавливает тип текущего скалярного типа
     * @param AbstractType $value Тип
     */
    public function setType(AbstractType $value)
    {
        $this->type = $value;
    }

    /**
     * Возвращает тип текущего скалярного типа
     * @return AbstractType Тип
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Устанавливает свойства свойства класса
     * @param array|PropertyOptions $options Настройки свойства класса
     * @return PropertyDefinition Инстанс текущего объекта
     */
    public function setOptions($options)
    {
        if (!($options instanceof PropertyOptions)) {
            $options = new PropertyOptions($options);
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Возвращает настройки текущего свойства класса
     * @return PropertyOptions Настройки свойства класса
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Проверяет, является ли текущий объект описанием класса
     * @return bool True если описание класса, false если нет
     */
    public function isClass()
    {
        return false;
    }

    /**
     * Проверяет, является ли описание описанием enum'a
     * @return bool True если enum, false если нет
     */
    public function isEnum()
    {
        return false;
    }
}
