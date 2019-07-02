<?php 
// src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Process\Process;

class DumpYamlCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'dump:yaml';

    protected function configure()
    {
        $this
            ->setDescription('It test issue 32251')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->yaml = [
            [
                'id' => 'user1', 
                'annotations' => [
                    'name' => 'Louisa Nicolas',
                    'email' => 'mail@domain.com',
                ],
            ],
            [
                'id' => 'user2',
                'annotations' => [
                    'name' => 'Telper Max',
                    'email' => 'mail@domain.com'
                ]
            ]
        ];

        $this->notTagged = $this->yaml;

        $f = function($item){return new TaggedValue('user', $item);};
        $this->tagged = array_map($f, $this->yaml);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Yaml::dump(content, 2)');
        dump(Yaml::dump($this->notTagged, 2));
        $io->title('Yaml::dump(new TaggedValue("user", content), 2)');
        dump(Yaml::dump($this->tagged, 2));

        $io->title('Yaml::dump(content, 3)');
        dump(Yaml::dump($this->notTagged, 3));
        $io->title('Yaml::dump(new TaggedValue("user", content), 3)');
        dump(Yaml::dump($this->tagged, 3));
        
        $io->title('File content verification');
        // file_put_contents('not-tagged-inline-default', Yaml::dump($this->notTagged));
        file_put_contents('not-tagged-inline-2', Yaml::dump($this->notTagged, 2));
        file_put_contents('not-tagged-inline-3', Yaml::dump($this->notTagged, 3));
        
        // file_put_contents('tagged-inline-default', Yaml::dump($this->tagged));
        file_put_contents('tagged-inline-2', Yaml::dump($this->tagged, 2));
        file_put_contents('tagged-inline-3', Yaml::dump($this->tagged, 3));
        

        // diff btw 2 x 3 (not tagged)        
        $io->title('Difference between not tagged inline=2 and inline=3');
        $diff = new Process(['diff', 'not-tagged-inline-2', 'not-tagged-inline-3']);
        $diff->run();
        $output->writeln($diff->getOutput() === "" ? 'no diff' : $diff->getOutput());

        // diff btw inline=2 and 3 (tagged)
        $io->title('Difference between not tagged inline=2 and inline=3');
        $diff = new Process(['diff', 'tagged-inline-2', 'tagged-inline-3']);
        $diff->run();
        $output->writeln($diff->getOutput() === "" ? 'no diff' : $diff->getOutput());

        $output->writeln(PHP_EOL);
    }
}
