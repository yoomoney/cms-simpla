<?php

namespace YaMoney\ConfigParser;

/**
 * Класс описания свойства класса
 * 
 * @package YaMoney\ConfigParser
 */
class PropertyDefinition extends AbstractDefinition
{
    /**
     * @var AbstractType Тип свойства
     */
    private $type;

    /**
     * @var PropertyOptions Настройки свойства класса
     */
    private $options;

    /**
     * Возвращает имя свойства, преобразует свойство в камэлкейс, если нужно
     * @return string Имя свойства
     */
    public function getPropertyName()
    {
        return preg_replace_callback('/\_(\w)/', function ($matches) {
            return strtoupper($matches[1]);
        }, $this->getName());
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
     * Устанавливает тип свойства
     * @param AbstractType $value Тип свойства
     */
    public function setType(AbstractType $value)
    {
        $this->type = $value;
    }

    /**
     * Возвращает тип свойства
     * @return AbstractType Тип свойства
     */
    public function getType()
    {
        return $this->type;
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
