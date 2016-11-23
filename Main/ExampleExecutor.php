<?php
/**
 * Punto di avvio principale dell'applicazione
 *
 * @authors: Miguel Delli Carpini
 */
namespace Voragine\Kernel;

//Dependencies
//---------------------------

use Symfony\Component\Console\Output\ConsoleOutput;


class ExampleExecutor extends ExecutorDefinition
{


    public function mainLoop()
    {

        //Abilitiamo l'output per CLI
        $this->output = new ConsoleOutput();


        //CLI OUTPUT
        $this->output->writeln("  <info>Main application point</info>\r\n");

        //Console output
        $this->output->writeln("\r\n\r\n<info>Exiting now</info>\r\n");

        
    }

}