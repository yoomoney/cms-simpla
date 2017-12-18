<?php

namespace YaMoney\ConfigParser;

/**
 * Класс описания enum свойств
 *
 * @package YaMoney\ConfigParser
 */
class EnumDefinition extends AbstractClassDefinition
{
    /**
     * @var array Массив валидных значений enum'a
     */
    private $validValues;

    /**
     * Добавляет в enum значение
     * @param mixed $value Добавляемое значение
     */
    public function addValue($value)
    {
        $this->validValues = $value;
    }

    /**
     * Устанавливает все значения enum'a
     * @param array $values Массив значений enum'a
     */
    public function setValues($values)
    {
        $this->validValues = $values;
    }

    /**
     * Возвращает все значения enum'a
     * @return array Массив значений enum'a
     */
    public function getValidValues()
    {
        return $this->validValues;
    }

    /**
     * Проверяет, является ли описание описанием enum'a
     * @return bool True если enum, false если нет
     */
    public function isEnum()
    {
        return true;
    }
}
