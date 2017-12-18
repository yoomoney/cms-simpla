<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\PropertyOptions;

/**
 * Базовый класс численного типа
 *
 * @package YaMoney\ConfigParser\Type
 */
abstract class AbstractNumericType extends AbstractType
{
    /**
     * Проверяет может ли объект текущего типа кастится нативно
     * @return bool True если объект можно катить, false если нет
     */
    public function hasCastDefinition()
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
        $this->getRangeValidationLines($options, $lines);
        $this->getRangeExclusiveValidationLines($options, $lines);
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
     * Добавляет строки для валидации минимального и максимального значения включительно
     * @param PropertyOptions $options Настройки свойства
     * @param array $lines Массив строк кода класса
     */
    private function getRangeValidationLines(PropertyOptions $options, &$lines)
    {
        if ($options->hasOption(PropertyOptions::MAXIMUM) && $options->hasOption(PropertyOptions::MINIMUM)) {
            $max = $options->getOption(PropertyOptions::MAXIMUM);
            $min = $options->getOption(PropertyOptions::MINIMUM);

            if ($max == $min) {
                $lines[] = '    if ($value != ' . $min . ') {';
            } else {
                $lines[] = '    if ($value < ' . $min . ' || $value > ' . $max . ') {';
            }
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        } elseif ($options->hasOption(PropertyOptions::MAXIMUM)) {
            $lines[] = '    if ($value > ' . $options->getOption(PropertyOptions::MAXIMUM) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        } elseif ($options->hasOption(PropertyOptions::MINIMUM)) {
            $lines[] = '    if ($value < ' . $options->getOption(PropertyOptions::MINIMUM) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        }
    }

    /**
     * Добавляет строки для валидации минимального и максимального значения
     * @param PropertyOptions $options Настройки свойства
     * @param array $lines Массив строк кода класса
     */
    private function getRangeExclusiveValidationLines(PropertyOptions $options, &$lines)
    {
        if ($options->hasOption(PropertyOptions::MAXIMUM_EXCLUSIVE)) {
            $lines[] = '    if ($value >= ' . $options->getOption(PropertyOptions::MAXIMUM_EXCLUSIVE) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        }
        if ($options->hasOption(PropertyOptions::MINIMUM_EXCLUSIVE)) {
            $lines[] = '    if ($value <= ' . $options->getOption(PropertyOptions::MINIMUM_EXCLUSIVE) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        }
    }
}