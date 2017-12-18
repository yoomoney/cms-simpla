<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\ClassDefinition;
use YaMoney\ConfigParser\PropertyOptions;

/**
 * Класс описания типа класса, объявленного в конфиге API
 *
 * @package YaMoney\ConfigParser\Type
 */
class ClassType extends AbstractType
{
    /**
     * @var ClassDefinition Описание класса, привязанного к типу
     */
    private $class;

    /**
     * Конструктор
     * @param ClassDefinition $definition Описание класса, привязанного к типу
     */
    public function __construct(ClassDefinition $definition)
    {
        $this->class = $definition;
    }

    /**
     * Проверяет может ли объект текущего типа кастится нативно
     * @return bool True если объект можно катить, false если нет
     */
    public function hasCastDefinition()
    {
        return false;
    }

    /**
     * Проверяет является ли текущий тип скалярым
     * @return bool True если тип скалярный, false если нет
     */
    public function isScalar()
    {
        return false;
    }

    /**
     * Добавляет в массив строк кода класса код для вылидации параметра текущего типа
     * @param PropertyOptions $options Настройки ссвойства класса
     * @param array $lines Массив строк кода класса
     * @return string[] Массив строк для валидации свойства
     */
    public function getValidationLines(PropertyOptions $options, &$lines)
    {}

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return $this->class->getClassName();
    }
}