<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\PropertyOptions;

/**
 * Класс описания типа массива
 *
 * @package YaMoney\ConfigParser\Type
 */
class ArrayType extends AbstractType
{
    /**
     * @var AbstractType Тип элементов внутри массива
     */
    private $itemsType;

    /**
     * @var PropertyOptions Настроки типа элементов внутри массива
     */
    private $itemsOptions;

    /**
     * Возвращает тип элементов внутри массива или null если тип не задан
     * @return AbstractType Тип элементов внутри массива или null если тип не задан
     */
    public function getItemsType()
    {
        return $this->itemsType;
    }

    /**
     * Устанавливает тип элментов внутри массива
     * @param AbstractType $type Тип элементов внутри массива
     * @return ArrayType Инстанс текущего объекта
     */
    public function setItemsType(AbstractType $type)
    {
        $this->itemsType = $type;
        return $this;
    }

    /**
     * Возвращает настройки типа элементов внутри массива
     * @return PropertyOptions Настройки типа элементов внутри массива
     */
    public function getItemsOptions()
    {
        return $this->itemsOptions === null ? new PropertyOptions(array()) : $this->itemsOptions;
    }

    /**
     * Устанавливает настройки типа элементов внутри массива
     * @param array $options Настройки типа элементов внутри массива
     * @return ArrayType Инстанс текущего объекта
     */
    public function setItemsOptions($options)
    {
        if (is_array($options)) {
            $options = new PropertyOptions($options);
        }
        $this->itemsOptions = $options;
        return $this;
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
        $this->getRequiredValidationLines($options, $lines);
        $this->getArrayValidationLines($options, $lines);
        $this->getRangeValidationLines($options, $lines);
    }

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return 'array';
    }

    /**
     * Добавляет в список строк кода код валидации массива
     * @param PropertyOptions $options Настройки текущего свойства
     * @param array $lines Массив строк кода класа
     */
    private function getArrayValidationLines(PropertyOptions $options, &$lines)
    {
        $lines[] = '    if (!is_array($value) && !($value instanceof \Traversable)) {';
        $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
        $lines[] = '    }';
    }

    /**
     * Добавляет в массив строк сеттера валидацию обызательного параметра, если нужно
     * @param PropertyOptions $options Настройки свойства
     * @param array $lines Массив строк кода класса
     */
    private function getRequiredValidationLines(PropertyOptions $options, &$lines)
    {
        if ($options->hasOption(PropertyOptions::REQUIRED)) {
            $value = $options->getOption(PropertyOptions::REQUIRED);
            if ($value) {
                $lines[] = '    if ($value === null || $value === \'\') {';
                $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
                $lines[] = '    }';
            }
        }
    }

    /**
     * Добавляет валидацю размеров массива, если эти настройки заданы
     * @param PropertyOptions $options Настройки текущего свойства
     * @param array $lines Массив строк кода класа
     */
    private function getRangeValidationLines(PropertyOptions $options, &$lines)
    {
        if ($options->hasOption(PropertyOptions::MAX_ITEMS) && $options->hasOption(PropertyOptions::MIN_ITEMS)) {
            $max = $options->getOption(PropertyOptions::MAX_ITEMS);
            $min = $options->getOption(PropertyOptions::MIN_ITEMS);

            if ($max == $min) {
                $lines[] = '    if (count($value) != ' . $min . ') {';
            } else {
                $lines[] = '    if (count($value) < ' . $min . ' || count($value) > ' . $max . ') {';
            }
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        } elseif ($options->hasOption(PropertyOptions::MAX_ITEMS)) {
            $lines[] = '    if (count($value) > ' . $options->getOption(PropertyOptions::MAX_ITEMS) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        } elseif ($options->hasOption(PropertyOptions::MIN_ITEMS)) {
            $lines[] = '    if (count($value) < ' . $options->getOption(PropertyOptions::MIN_ITEMS) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        }
    }
}