<?php

namespace YaMoney\CodeGenerator;

use YaMoney\ConfigParser\AbstractClassDefinition;

/**
 * Базовый генератор кода для классов
 * 
 * @package YaMoney\CodeGenerator
 */
abstract class AbstractClassCodeGenerator extends AbstractCodeGenerator
{
    /**
     * @var string|null Базовое пространство имён класса
     */
    private $baseNamespace;
    
    /**
     * Генерирует код класса
     * @param AbstractClassDefinition $definition Описание класса или enum'a
     * @param int $depth Глубина вхождения класса (влияет на количество отступов)
     * @return string PHP код класса в виде строки
     */
    abstract public function getClassCode(AbstractClassDefinition $definition, $depth = 0);

    /**
     * Устанавливает базовое пространство имён генерируемых классов
     * @param string $value Базовое пространство имён классов
     */
    public function setBaseNamespace($value)
    {
        $this->baseNamespace = $value;
    }

    /**
     * Вставляет шапку файла класса в список строк
     * @param AbstractClassDefinition $definition Описание класса или enum'a
     * @param array $lines Массив строк файла
     */
    protected function getNamespaceLines(AbstractClassDefinition $definition, &$lines)
    {
        $ns = '';
        if (!empty($this->baseNamespace)) {
            $ns .= $this->baseNamespace . '\\';
        }
        $ns .= $definition->getNamespace();
        if (!empty($ns)) {
            $lines[] = 'namespace ' . $ns . ';';
            $lines[] = '';
        }
        if ($definition->getParent() === null) {
            $parent = $this->getBaseClassName();
            if (!empty($parent)) {
                $lines[] = 'use '.$parent.';';
                $lines[] = '';
            }
        }
    }

    /**
     * Вставляет в список строк класса комментарий к классу
     * @param AbstractClassDefinition $definition Описание класса или enum'a
     * @param array $lines Массив строк класса
     */
    protected function getCommentLines(AbstractClassDefinition $definition, &$lines)
    {
        $lines[] = '/**';
        if ($definition->hasTitle()) {
            foreach (explode("\n", $definition->getTitle()) as $line) {
                $lines[] = ' * ' . rtrim($line);
            }
        }
        if ($definition->hasDescription()) {
            foreach (explode("\n", $definition->getDescription()) as $line) {
                $lines[] = ' * ' . rtrim($line);
            }
        }
        $this->setAnnotationLines($definition, $lines);
        $lines[] = ' */';
    }

    /**
     * Вставляет в список строк класса строку с объявлением класса
     * @param AbstractClassDefinition $definition Описание класса или enum'a
     * @param array $lines Массив строк класса
     */
    protected function getFirstClassLines(AbstractClassDefinition $definition, &$lines)
    {
        $line = '';
        if ($definition->isAbstract()) {
            $line .= 'abstract ';
        }
        $line .= 'class ' . $definition->getClassName();
        if ($definition->getParent() !== null) {
            $line .= ' extends ' . $definition->getParent()->getClassName();
        } else {
            $parent = $this->getBaseClassName();
            if (!empty($parent)) {
                $pos = strrpos($parent, '\\');
                $className = substr($parent, $pos + 1);
                $line .= ' extends ' . $className;
            }
        }
        $lines[] = $line;
        $lines[] = '{';
    }

    /**
     * Вставляет в список в комментарий класса список свойств класса в аннотациях
     * @param AbstractClassDefinition $definition Описание класса или enum'a
     * @param array $lines Массив строк класса
     */
    protected function setAnnotationLines(AbstractClassDefinition $definition, &$lines)
    {}

    /**
     * Возвращает имя базового класса, используемого в качестве родительского для всех типов объектов
     * @return string Имя базового класса
     */
    abstract protected function getBaseClassName();
}
