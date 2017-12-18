<?php

namespace YaMoney\CodeGeneratorBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use YaMoney\CodeGenerator\CodeGenerator;
use YaMoney\ConfigParser\Parser;

class GenerateCodeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('code:generate')
            ->setDescription('Generate SDK model classes.')
            ->setHelp('This command allows you to generate models for PHP SDK...')
        ;

        $this
            ->addOption('source', 's', InputOption::VALUE_REQUIRED, 'source file name')
            ->addOption('destination', 'd', InputOption::VALUE_REQUIRED, 'destination directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFileName = $input->getOption('source');
        if (empty($inputFileName)) {
            throw new \RuntimeException('Input file name required');
        }

        $outputDirectoryName = $input->getOption('destination');
        if (empty($outputDirectoryName)) {
            $outputDirectoryName = realpath(__DIR__ . '/../../../lib');
        }
        if (file_exists($outputDirectoryName) && !is_dir($outputDirectoryName)) {
            throw new \RuntimeException('Invalid output directory "' . $outputDirectoryName . '"');
        } elseif (!file_exists($outputDirectoryName)) {
            if (!mkdir($outputDirectoryName)) {
                throw new \RuntimeException('Failed to create output directory "' . $outputDirectoryName . '"');
            }
        }

        $output->write('Read source file "' . $inputFileName . '" ...');
        $config = $this->readConfig($inputFileName);
        $output->writeln(' complete');

        $output->write('Parse SDK structure ...');
        $parser = new Parser();
        $classList = $parser->parse($config, 'Model');
        $output->writeln(' complete');

        $output->write('Generate SDK classes ...');
        $codeGenerator = new CodeGenerator();
        $codeGenerator->setBaseNamespace('YaMoney');
        $phpCode = $codeGenerator->generateCode($classList);
        $output->writeln(' complete');

        $output->writeln('Save classes to "'.$outputDirectoryName.'" ... ');
        foreach ($phpCode as $className => $code) {
            $outFileName = $outputDirectoryName . '/' . $className . '.php';
            $output->writeln('    Save class "'.$className.'" to "'.$outFileName.'"');
            if (file_put_contents($outFileName, '<?php'.PHP_EOL.PHP_EOL.$code) === false) {
                throw new \RuntimeException('Failed to save class "'.$className.'"');
            }
        }
        $output->writeln(' complete');
    }

    private function readConfig($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \RuntimeException('File "'.$fileName.'" not exists');
        }
        if (!is_readable($fileName)) {
            throw new \RuntimeException('File "'.$fileName.'" not readable');
        }
        $content = file_get_contents($fileName);
        if (empty($content)) {
            throw new \RuntimeException('Filed to read data from file "'.$fileName.'"');
        }
        $value = Yaml::parse($content);
        if (empty($value)) {
            throw new \RuntimeException('Filed to parse data from file "'.$fileName.'"');
        }
        return $value;
    }
}