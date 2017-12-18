<?php

namespace YaMoney\CodeGenerator;

use YaMoney\ConfigParser\PropertyDefinition;
use YaMoney\ConfigParser\Type\ArrayType;

/**
 * Класс генератора кода для полей класса, геттеров и сеттеров
 *
 * @package YaMoney\CodeGenerator
 */
class PropertyCodeGenerator extends AbstractCodeGenerator
{
    /**
     * Возвращает PHP код в виде строки с объявлением поля класса
     * @param PropertyDefinition $property Описание свойства класса
     * @param int $depth Количество отступов
     * @return string PHP код в виде строки с объявлением поля класса
     */
    public function getPropertyCode(PropertyDefinition $property, $depth = 1)
    {
        $lines = array();
        $padding = $this->getPadding($depth);

        $lines[] = $this->getPropertyComment($property, $padding);

        $line = 'private $' . $property->getPropertyName();
        if ($property->getType()->isArray()) {
            $line .= ' = array()';
        }
        $line .= ';';

        $lines[] = $line;

        return implode(PHP_EOL . $padding, $lines);
    }

    /**
     * Возвращает PHP код в виде строки с объявлением геттера для поля класса
     * @param PropertyDefinition $property Описание свойства класса
     * @param int $depth Количество отступов
     * @return string PHP код в виде строки с объявлением геттера поля класса
     */
    public function getGetterCode(PropertyDefinition $property, $depth = 1)
    {
        $lines = array();
        $padding = $this->getPadding($depth);

        $lines[] = $padding . '/**';
        
        $line = ' * @return ' . $property->getType()->getName();
        if ($property->hasTitle()) {
            $line .= ' ' . $property->getTitle();
        }

        $lines[] = $line;
        $lines[] = ' */';
        $lines[] = 'public function get' . ucfirst($property->getPropertyName()) . '()';
        $lines[] = '{';
        $lines[] = '    return $this->' . $property->getPropertyName() . ';';
        $lines[] = '}';

        return implode(PHP_EOL . $padding, $lines);
    }

    /**
     * Возвращает PHP код в виде строки с объявлением сеттера для поля класса
     * @param PropertyDefinition $property Описание свойства класса
     * @param int $depth Количество отступов
     * @return string PHP код в виде строки с объявлением сеттера поля класса
     */
    public function getSetterCode(PropertyDefinition $property, $depth = 1)
    {
        $lines = array();
        $padding = $this->getPadding($depth);

        $lines[] = $this->getSetterComment($property, $padding);

        $line = 'public function set' . ucfirst($property->getPropertyName()) . '(';
        if (!$property->getType()->isScalar()) {
            $line .= $property->getType()->getName() . ' ';
        }
        $line .= '$value)';

        $lines[] = $line;
        $lines[] = '{';
        $property->getType()->getValidationLines($property->getOptions(), $lines);

        if ($property->getType()->isArray()) {
            $lines[] = '    $this->' . $property->getPropertyName() . ' = array();';
            $lines[] = '    foreach ($value as $key => $val) {';
            $lines[] = '        $this->' . $property->getPropertyName() . '[$key] = $val;';
            $lines[] = '    }';
        } else {
            $line = '    $this->' . $property->getPropertyName() . ' = ';
            if ($property->getType()->hasCastDefinition()) {
                $line .= '(' . $property->getType()->getCastDefinition() . ')';
            }
            $line .= '$value;';
            $lines[] = $line;
        }
        $lines[] = '}';

        if ($property->getType()->isArray()) {
            $this->getArrayMethods($property, $lines);
        }

        return implode(PHP_EOL . $padding, $lines);
    }

    /**
     * Добавляет в список строк с кодом описание методов типа addXXX($value), для полей с типом array
     * @param PropertyDefinition $property Описание свойства класса
     * @param array $lines Массив строк с кодом класса
     */
    private function getArrayMethods(PropertyDefinition $property, &$lines)
    {
        $type = $property->getType();
        if ($type instanceof ArrayType) {
            $itemsType = $type->getItemsType();
            $options = $type->getItemsOptions();
        } else {
            $itemsType = null;
            $options = array();
        }

        $lines[] = '';

        $line = 'public function add' . ucfirst($property->getPropertyName()) . '(';
        if ($itemsType !== null && !$itemsType->isScalar()) {
            $line .= $itemsType->getName() . ' ';
        }
        $line .= '$value)';

        $lines[] = $line;
        $lines[] = '{';
        if ($itemsType !== null) {
            $itemsType->getValidationLines($options, $lines);
        }

        $line = '    $this->' . $property->getPropertyName() . '[] = ';
        if ($itemsType !== null) {
            if ($itemsType->hasCastDefinition()) {
                $line .= '(' . $itemsType->getCastDefinition() . ')';
            }
        }
        $line .= '$value;';

        $lines[] = $line;
        $lines[] = '}';
    }

    /**
     * Возвращает комментарий к свойству класса виде строки
     * @param PropertyDefinition $property Описание свойства класса
     * @param string $padding Смещение строк относительно начала строки
     * @return string Комментарий в виде строки
     */
    private function getPropertyComment(PropertyDefinition $property, $padding)
    {
        $lines = array();

        $lines[] = $padding . '/**';
        if ($property->hasDescription()) {
            foreach (explode("\n", $property->getDescription()) as $line) {
                $lines[] = $padding . ' * ' . rtrim($line);
            }
        }
        $lastLine = ' * @var ' . $property->getType()->getName();
        if ($property->hasTitle()) {
            $lastLine .= ' ' . $property->getTitle();
        }
        $lines[] = $padding . $lastLine;
        $lines[] = $padding . ' */';

        return implode(PHP_EOL, $lines);
    }

    /**
     * Возвращает комментарий к сеттеру свойства класса виде строки
     * @param PropertyDefinition $property Описание свойства класса
     * @param string $padding Смещение строк относительно начала строки
     * @return string Комментарий в виде строки
     */
    private function getSetterComment(PropertyDefinition $property, $padding)
    {
        $lines = array();

        $lines[] = $padding . '/**';
        $lines[] = $padding . ' * @param ' . $property->getType()->getName() . ' $value';
        if ($property->hasTitle()) {
            $lines[1] .= ' ' . $property->getTitle();
        }
        $lines[] = $padding . ' */';

        return implode(PHP_EOL, $lines);
    }
}
