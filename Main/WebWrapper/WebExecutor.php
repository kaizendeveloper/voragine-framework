<?php
/**
 * Punto di avvio principale dell'applicazione
 *
 * @authors: Miguel Delli Carpini
 */
namespace Voragine\Main\WebWrapper;

//Dependencies
//---------------------------

use Symfony\Component\Console\Output\ConsoleOutput;


class WebExecutor extends ExecutorDefinition
{


    public function mainLoop()
    {


        //--------------------------------------------------------
        //                      DISPATCHER
        //--------------------------------------------------------


        //A questo punto dovremmo avere il nostro router e controller resolver operativi funzionanti
        //dunque:

        //A partire da un URL controlla se tale URL matcha con qualche route impostato da YAML
        //deve restituire:
        //  array('_controller' => 'ControllerClass::Method', 'parametroImpostato1' => 'valore', '_route' => 'nomeDelRouteMatchato')
        //  Nel caso non combaci nessuno:
        //  restituirà un oggetto di tipo "ResourceNotFoundException" segnalando gli errori riscontrati


        //Carichiamo il servizio per routing
        $router = $this->services->get('router');

        //Attiviamo il servizio dei Controllers
        $controllerHandler = $this->services->get('controller_handler');

        //Facciamo in modo che ogni Controller possa avere accesso ai servizi
        $controllerHandler->makeControllersUseTheseServices($this->services);

        //Leggiamo la richiesta

        $request = new Request();
        $request = $request->createFromGlobals();




        //Proviamo a matchare
        //$routeMatch = $router->match('/blog/ilPrimo/ilSecondo');
        $routeMatch = $router->match($request->getPathInfo());


        $matchNotFound = $this->services->objectIsOfType('ResourceNotFoundException',$routeMatch);
        if($matchNotFound) {
            $response = $routeMatch;
        } else {

            //Prendiamo la collezione di routes estratti dallo YAML
            $routeCollection = $router->getRouteCollection();

            //Spacciamo! XD (Dobbiamo catturare la risposta)
            $response = $controllerHandler->dispatchController($routeCollection, $routeMatch);

        }


        //Analizziamo la risposta da parte del Controller
        if($this->isAValidResponse($response))
        {
            //Se si tratta di una risposta di tipo Response, la inviamo
            $response->send();

        } else {

            //Altrimenti processiamo noi quello che vogliamo
            $logger = $this->services->get('logger');
            $logger->error('Il controller non ha restituito un oggetto Response come output. Terminiamo l\'esecuzione. File: ' . __FILE__ . ' linea: ' . __LINE__ );

            $twig = $this->services->get('template_engine');

            //Sicuramente Response sarà di tipo Exception quindi il metodo getMessage() darà il messagio di errore
            $output = $twig->render('@httpErrors/404.twig', array('errors' => $response->getMessage()));

            $response = new Response($output, Response::HTTP_NOT_FOUND);

            $response->send();

        }


    }

}