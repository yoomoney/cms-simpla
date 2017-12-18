<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\PropertyOptions;
use YaMoney\ConfigParser\ScalarParameterDefinition;

/**
 * Класс описания типа, основанного на именованном скалярном типе
 *
 * @package YaMoney\ConfigParser\Type
 */
class ScalarDefinitionType extends AbstractType
{
    /**
     * @var ScalarParameterDefinition Объект описания именованного скалярного типа
     */
    private $def;

    /**
     * Конструктор
     * @param ScalarParameterDefinition $definition Объект описания именованного скалярного типа
     */
    public function __construct(ScalarParameterDefinition $definition)
    {
        $this->def = $definition;
    }

    /**
     * Проверяет может ли объект текущего типа кастится нативно
     * @return bool True если объект можно катить, false если нет
     */
    public function hasCastDefinition()
    {
        return $this->def->getType()->hasCastDefinition();
    }

    /**
     * Возвращает код для каста в нужный тип
     * @return string PHP код для каста данных в нужный тип
     */
    public function getCastDefinition()
    {
        return $this->def->getType()->getCastDefinition();
    }

    /**
     * Проверяет является ли текущий тип скалярым
     * @return bool True если тип скалярный, false если нет
     */
    public function isScalar()
    {
        return $this->def->getType()->isScalar();
    }

    /**
     * Добавляет в массив строк кода класса код для вылидации параметра текущего типа
     * @param PropertyOptions $options Настройки ссвойства класса
     * @param array $lines Массив строк кода класса
     * @return string[] Массив строк для валидации свойства
     */
    public function getValidationLines(PropertyOptions $options, &$lines)
    {
        return $this->def->getType()->getValidationLines($this->def->getOptions(), $lines);
    }

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return $this->def->getType()->getName();
    }
}