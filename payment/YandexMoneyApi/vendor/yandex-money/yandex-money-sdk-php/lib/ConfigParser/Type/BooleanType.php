<?php

namespace YaMoney\ConfigParser\Type;

use YaMoney\ConfigParser\AbstractType;
use YaMoney\ConfigParser\PropertyOptions;

/**
 * Класс описания булева типа
 *
 * @package YaMoney\ConfigParser\Type
 */
class BooleanType extends AbstractType
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
        return 'bool';
    }

    /**
     * Возвращает имя текущего типа
     * @return string Имя текущего типа
     */
    public function getName()
    {
        return 'bool';
    }

    /**
     * Добавляет в массив строк кода класса код для вылидации параметра текущего типа
     * @param PropertyOptions $options Настройки ссвойства класса
     * @param array $lines Массив строк кода класса
     * @return string[] Массив строк для валидации свойства
     */
    public function getValidationLines(PropertyOptions $options, &$lines)
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
}
