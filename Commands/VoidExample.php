<?php
/**
 * Starting point command
 *
 * @authors: Miguel Delli Carpini
 *
 */

namespace Voragine\Commands;



//Namespace necessari per accedere agli oggetti Symfony
//------------------------------------------------------
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

//Namespace necessari per avviare l'applicazione secondo il caso
use Voragine\Kernel;

class VoidExample extends Command {

    protected function configure()
    {
        $environment = 'devel';
        $test = null;
        $production = null;

        $helpText =<<<EOT
Da scrivere la descrizione della guida all'utilizzo

Utilizzo:

<info>php app/console.php begin --env="devel"|"test"|"prod"</info>

Oppure, nel modo breve

<info>php app/console.php begin -env devel|test|prod</info>

EOT;

        $this->setName("begin")
            ->setDescription("Executes the example command\r\n          write begin -h for a briefing of the accepted commands.\r\n")
            ->setDefinition(new InputDefinition(array(
                new InputOption('env', 'e', InputOption::VALUE_REQUIRED, 'Specifies the environment of execution', $environment)
            )))
            ->setHelp($helpText);

    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        //Formattiamo l'uscita del testo
        $header_style = new OutputFormatterStyle('white', 'green', array('bold'));
        $output->getFormatter()->setStyle('header', $header_style);

        //Leggiamo gli argomenti da riga di comando
        //-----------------------------------------------------------
        $environment = $input->getOption('env');
        //Controlliamo se l'ambiente è stato impostato correttamente
        switch ($environment) {
            case 'devel':
            case 'test':
            case 'prod':
                break;
            default:
                throw new \InvalidArgumentException('Parametro --env | -e invalido');
        }


        //Lanciamo il wrapper di questo Command

        //Il grande Executor Tassadar ancora vive!
        $tassadar = new VoidExample($environment);


        //Cominciamo a processare
        $tassadar->mainLoop();


    }
}