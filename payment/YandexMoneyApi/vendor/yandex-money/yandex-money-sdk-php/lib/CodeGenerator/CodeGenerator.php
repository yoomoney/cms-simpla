<?php

namespace YaMoney\CodeGenerator;

use YaMoney\ConfigParser\AbstractDefinition;

/**
 * Класс генератора кода
 * 
 * @package YaMoney\CodeGenerator
 */
class CodeGenerator
{
    /**
     * @var ClassCodeGenerator Генератор кода для классов
     */
    private $classCodeGenerator;

    /**
     * @var EnumCodeGenerator Генератор кода для enum'ов
     */
    private $enumCodeGenerator;

    /**
     * Конструктор, инициализаирует генераторы кода для классов
     */
    public function __construct()
    {
        $this->classCodeGenerator = new ClassCodeGenerator();
        $this->enumCodeGenerator = new EnumCodeGenerator();
    }

    /**
     * Устанавливает базовое пространство имён генерируемых классов
     * @param string $value Базовое пространство имён классов
     */
    public function setBaseNamespace($value)
    {
        $this->classCodeGenerator->setBaseNamespace($value);
        $this->enumCodeGenerator->setBaseNamespace($value);
    }

    /**
     * Генерирует код классов, возвращает массив строк с кодом
     * @param AbstractDefinition[] $classList Массив с описанием классов
     * @return string[] Массив с PHP кодом классов
     */
    public function generateCode($classList)
    {
        $list = array();
        foreach ($classList as $class) {
            $generator = null;
            if ($class->isEnum()) {
                $generator = $this->enumCodeGenerator;
            } elseif ($class->isClass()) {
                $generator = $this->classCodeGenerator;
            }
            if ($generator !== null) {
                $code = $generator->getClassCode($class);
                $list[$class->getName()] = $code;
            }
        }
        return $list;
    }
}
