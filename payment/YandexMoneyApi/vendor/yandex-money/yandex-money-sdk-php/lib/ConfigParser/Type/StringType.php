<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\PropertyOptions;

/**
 * Класс описания строкового типа
 *
 * @package YaMoney\ConfigParser\Type
 */
class StringType extends AbstractStringType
{
    /**
     * Возвращает код для определения длины строки
     * @param string $variableName Имя параметра в коде
     * @return string PHP код в виде строки для определения длины строки
     */
    protected function getLengthFunction($variableName)
    {
        return 'mb_strlen($'.$variableName.', \'utf-8\')';
    }

    /**
     * Добавляет в массив строк кода класса код для вылидации параметра текущего типа
     * @param PropertyOptions $options Настройки ссвойства класса
     * @param array $lines Массив строк кода класса
     * @return string[] Массив строк для валидации свойства
     */
    public function getValidationLines(PropertyOptions $options, &$lines)
    {
        parent::getValidationLines($options, $lines);
        $this->getPatternValidationLines($options, $lines);
    }

    /**
     * Добавляет строки валидации по шаблону, если в настройках это указано
     * @param PropertyOptions $options Настройки свойства
     * @param array $lines Массив строк кода класса
     */
    private function getPatternValidationLines(PropertyOptions $options, &$lines)
    {
        if ($options->hasOption(PropertyOptions::PATTERN)) {
            $lines[] = '    if (!preg_match(\'/'.$options->getOption(PropertyOptions::PATTERN).'/\', $value)) {';
            $lines[] = '        throw new \InvalidArgumentException(\'Invalid value\');';
            $lines[] = '    }';
        }
    }
}