<?php

namespace YaMoney\ConfigParser;

/**
 * Класс описания класса
 *
 * @package YaMoney\ConfigParser
 */
class ClassDefinition extends AbstractClassDefinition
{
    /**
     * @var PropertyDefinition[] Массив с описанием полей класса
     */
    private $properties = array();

    /**
     * Добавляет новое своство классе
     * @param PropertyDefinition $property Свойство класса
     */
    public function addProperty(PropertyDefinition $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     * Проверяет, имеется ли у класса свойство с указанным именем
     * @param string $propertyName Имя свойства класса
     * @return bool True если свойство имеется, false если нет
     */
    public function hasProperty($propertyName)
    {
        return array_key_exists($propertyName, $this->properties);
    }

    /**
     * Возвращает описание свойств класса
     * @return PropertyDefinition[] Массив с описанием свойств класса
     */
    public function getProperties()
    {
        return $this->properties;
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
