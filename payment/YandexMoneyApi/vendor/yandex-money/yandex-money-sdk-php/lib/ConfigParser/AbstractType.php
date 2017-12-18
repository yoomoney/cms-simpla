<?php

namespace YaMoney\ConfigParser;

/**
 * Базовый класс описания типов свойств
 *
 * @package YaMoney\ConfigParser
 */
abstract class AbstractType
{
    /**
     * Проверяет может ли объект текущего типа кастится нативно
     * @return bool True если объект можно катить, false если нет
     */
    public function hasCastDefinition()
    {
        return false;
    }

    /**
     * Возвращает код для каста в нужный тип
     * @return string PHP код для каста данных в нужный тип
     */
    public function getCastDefinition()
    {
        throw new \BadMethodCallException();
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
     * Проверяет, является ли текущий тип массивом
     * @return bool True если тип является типом массива, false если нет
     */
    public function isArray()
    {
        return false;
    }

    /**
     * Добавляет в массив строк кода класса код для вылидации параметра текущего типа
     * @param PropertyOptions $options Настройки ссвойства класса
     * @param array $lines Массив строк кода класса
     * @return string[] Массив строк для валидации свойства
     */
    abstract public function getValidationLines(PropertyOptions $options, &$lines);

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    abstract public function getName();
}
