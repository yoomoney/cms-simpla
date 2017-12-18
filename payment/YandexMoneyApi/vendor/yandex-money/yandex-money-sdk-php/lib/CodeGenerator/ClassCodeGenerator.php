<?php

namespace YaMoney\CodeGenerator;

use YaMoney\ConfigParser\AbstractClassDefinition;
use YaMoney\ConfigParser\ClassDefinition;
use YaMoney\ConfigParser\Type\ArrayType;

/**
 * Класс генератора кода для классов
 *
 * @package YaMoney\CodeGenerator
 */
class ClassCodeGenerator extends AbstractClassCodeGenerator
{
    /**
     * @var PropertyCodeGenerator Генератор кода для свойств класса, их геттеров и сеттеров
     */
    private $propertyCodeGenerator;

    /**
     * Конструктор, инициализирует генератор кода для свойств класса
     */
    public function __construct()
    {
        $this->propertyCodeGenerator = new PropertyCodeGenerator();
    }

    /**
     * Генерирует код класса
     * @param AbstractClassDefinition $class Описание класса
     * @param int $depth Глубина вхождения класса (влияет на количество отступов)
     * @return string PHP код класса в виде строки
     */
    public function getClassCode(AbstractClassDefinition $class, $depth = 0)
    {
        if (!($class instanceof ClassDefinition)) {
            throw new \RuntimeException();
        }

        $lines = array();
        $padding = $this->getPadding($depth);

        $this->getNamespaceLines($class, $lines);
        $this->getCommentLines($class, $lines);
        $this->getFirstClassLines($class, $lines);

        foreach ($class->getProperties() as $property) {
            $lines[] = $this->propertyCodeGenerator->getPropertyCode($property);
            $lines[] = '';
        }

        foreach ($class->getProperties() as $property) {
            $lines[] = '';
            $lines[] = $this->propertyCodeGenerator->getGetterCode($property);
            $lines[] = '';
            $lines[] = $this->propertyCodeGenerator->getSetterCode($property);
        }

        $lines[] = '}';

        return implode(PHP_EOL . $padding, $lines) . PHP_EOL;
    }

    /**
     * Вставляет в список в комментарий класса список свойств класса в аннотациях
     * @param AbstractClassDefinition $class Описание класса или enum'a
     * @param array $lines Массив строк класса
     */
    protected function setAnnotationLines(AbstractClassDefinition $class, &$lines)
    {
        if (!($class instanceof ClassDefinition)) {
            throw new \RuntimeException();
        }
        foreach ($class->getProperties() as $property) {
            $type = $property->getType()->getName();
            if ($property->getType()->isArray()) {
                /** @var ArrayType $propertyType */
                $propertyType = $property->getType();
                if ($propertyType->getItemsType() !== null) {
                    $type = $propertyType->getItemsType()->getName() . '[]';
                }
            }
            $comment = '';
            if ($property->hasTitle()) {
                $comment .= ' ' . $property->getTitle();
            }
            $lines[] = ' * @property ' . $type . ' $' . $property->getPropertyName() . $comment;
        }
    }

    /**
     * Возвращает имя базового класса, используемого в качестве родительского для всех типов объектов
     * @return string Имя базового класса
     */
    protected function getBaseClassName()
    {
        return 'YaMoney\Common\AbstractObject';
    }
}