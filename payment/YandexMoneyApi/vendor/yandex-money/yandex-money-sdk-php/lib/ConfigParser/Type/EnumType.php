<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\EnumDefinition;
use YaMoney\ConfigParser\PropertyOptions;

/**
 * Класс описания типа, принимающего одно из значений enum'a
 *
 * @package YaMoney\ConfigParser\Type
 */
class EnumType extends AbstractType
{
    /**
     * @var EnumDefinition Описание enum'a привязанного к типу
     */
    private $enum;

    /**
     * Конструктор
     * @param EnumDefinition $definition Описание enum'a привязанного к типу
     */
    public function __construct(EnumDefinition $definition)
    {
        $this->enum = $definition;
    }

    /**
     * Проверяет может ли объект текущего типа кастится нативно
     * @return bool True если объект можно катить, false если нет
     */
    public function hasCastDefinition()
    {
        return true;
    }

    /**
     * Возвращает код для каста в нужный тип
     * @return string PHP код для каста данных в нужный тип
     */
    public function getCastDefinition()
    {
        return 'string';
    }

    /**
     * Проверяет является ли текущий тип скалярым
     * @return bool True если тип скалярный, false если нет
     */
    public function isScalar()
    {
        return true;
    }

    /**
     * Добавляет в массив строк кода класса код для вылидации параметра текущего типа
     * @param PropertyOptions $options Настройки ссвойства класса
     * @param array $lines Массив строк кода класса
     * @return string[] Массив строк для валидации свойства
     */
    public function getValidationLines(PropertyOptions $options, &$lines)
    {
        $lines[] = '    if (!'.$this->enum->getClassName().'::valueExists($value)) {';
        $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
        $lines[] = '    }';
    }

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return 'string';
    }
}