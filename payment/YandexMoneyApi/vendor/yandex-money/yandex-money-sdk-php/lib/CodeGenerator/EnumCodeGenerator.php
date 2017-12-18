<?php

namespace YaMoney\CodeGenerator;

use YaMoney\ConfigParser\AbstractClassDefinition;
use YaMoney\ConfigParser\EnumDefinition;

/**
 * Генератор PHP кода для классов перечислений
 *
 * @package YaMoney\CodeGenerator
 */
class EnumCodeGenerator extends AbstractClassCodeGenerator
{
    /**
     * Генерирует код класса enum'a
     * @param AbstractClassDefinition $enum Описание enum'a
     * @param int $depth Глубина вхождения класса (влияет на количество отступов)
     * @return string PHP код класса в виде строки
     */
    public function getClassCode(AbstractClassDefinition $enum, $depth = 0)
    {
        if (!($enum instanceof EnumDefinition)) {
            throw new \RuntimeException();
        }

        $lines = array();
        $padding = $this->getPadding($depth);

        $this->getNamespaceLines($enum, $lines);
        $this->getCommentLines($enum, $lines);
        $this->getFirstClassLines($enum, $lines);

        foreach ($enum->getValidValues() as $value) {
            $lines[] = '    const ' . strtoupper($value) . ' = \'' . $value . '\';';
        }

        $lines[] = '';
        $lines[] = '    protected static $validValues = array(';
        foreach ($enum->getValidValues() as $value) {
            $lines[] = '        \'' . $value . '\' => true,';
        }
        $lines[] = '    );';
        $lines[] = '}';

        return implode(PHP_EOL . $padding, $lines) . PHP_EOL;
    }

    /**
     * Возвращает имя базового класса, используемого в качестве родительского для всех типов объектов
     * @return string Имя базового класса
     */
    protected function getBaseClassName()
    {
        return 'YaMoney\Common\AbstractEnum';
    }
}