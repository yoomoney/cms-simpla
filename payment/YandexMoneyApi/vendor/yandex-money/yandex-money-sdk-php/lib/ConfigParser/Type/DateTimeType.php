<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\PropertyOptions;

/**
 * Класс описания типа даты и времени
 *
 * @package YaMoney\ConfigParser\Type
 */
class DateTimeType extends AbstractType
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
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return '\DateTime';
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
        $lines[] = '    if (is_scalar($value)) {';
        $lines[] = '        if (is_int($value)) {';
        $lines[] = '            $date = new \DateTime();';
        $lines[] = '            $date->setTimestamp($value);';
        $lines[] = '            $value = $date;';
        $lines[] = '        } else {';
        $lines[] = '            $value = new \DateTime($value);';
        $lines[] = '        }';
        $lines[] = '    }';
    }
}