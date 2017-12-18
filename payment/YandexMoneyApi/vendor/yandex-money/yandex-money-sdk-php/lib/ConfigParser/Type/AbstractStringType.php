<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\PropertyOptions;

/**
 * Базовый класс строкового типа
 *
 * @package YaMoney\ConfigParser\Type
 */
abstract class AbstractStringType extends AbstractType
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
     * Возвращает код для каста в нужный тип
     * @return string PHP код для каста данных в нужный тип
     */
    public function getCastDefinition()
    {
        return 'string';
    }

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return 'string';
    }

    /**
     * Возвращает код для определения длины строки
     * @param string $variableName Имя параметра в коде
     * @return string PHP код в виде строки для определения длины строки
     */
    abstract protected function getLengthFunction($variableName);

    /**
     * Добавляет в массив строк кода класса код для вылидации параметра текущего типа
     * @param PropertyOptions $options Настройки ссвойства класса
     * @param array $lines Массив строк кода класса
     * @return string[] Массив строк для валидации свойства
     */
    public function getValidationLines(PropertyOptions $options, &$lines)
    {
        $this->getRequiredValidationLines($options, $lines);
        $this->getLengthValidationLines($options, $lines);
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
     * Добавляет строки валидации длины строки, если это требуется
     * @param PropertyOptions $options Настройки свойства
     * @param array $lines Массив строк кода класса
     */
    private function getLengthValidationLines(PropertyOptions $options, &$lines)
    {
        if ($options->hasOption(PropertyOptions::MAX_LENGTH) && $options->hasOption(PropertyOptions::MIN_LENGTH)) {
            $max = $options->getOption(PropertyOptions::MAX_LENGTH);
            $min = $options->getOption(PropertyOptions::MIN_LENGTH);

            $lines[] = '    $length = ' . $this->getLengthFunction('value') . ';';
            if ($max == $min) {
                $lines[] = '    if ($length != ' . $min . ') {';
            } else {
                $lines[] = '    if ($length < ' . $min . ' || $length > ' . $max . ') {';
            }
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        } elseif ($options->hasOption(PropertyOptions::MAX_LENGTH)) {
            $lines[] = '    $length = ' . $this->getLengthFunction('value') . ';';
            $lines[] = '    if ($length > ' . $options->getOption(PropertyOptions::MAX_LENGTH) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        } elseif ($options->hasOption(PropertyOptions::MIN_LENGTH)) {
            $lines[] = '    $length = ' . $this->getLengthFunction('value') . ';';
            $lines[] = '    if ($length < ' . $options->getOption(PropertyOptions::MIN_LENGTH) . ') {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        }
    }
}
