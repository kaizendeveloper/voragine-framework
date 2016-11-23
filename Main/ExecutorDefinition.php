<?php
/**
 * Modello per il punto di avvio principale dell'applicazione
 *
 * @authors: Miguel Delli Carpini
 *
 */
namespace Voragine\Main;

//Accediamo al namespace dei configuratori del kernel
use Voragine\Kernel\Services;
use Voragine\Kernel\Services\Base;
use Voragine\Kernel\Services\Base\ServiceLoader;
use Voragine\Utilities;


abstract class ExecutorDefinition
{


    //Qui ci saranno le collezioni che riguardano tutte le configurazioni
    protected $services;


    /**
     * All'istanza dell'oggetto configuriamo soltanto doctrine perché può darsi che si stia lanciando
     * doctrine da CLI e quindi questo pezzo qua inizializza soltanto i siteaccess così da avere le impostazioni
     * del DB che servono a doctrine
     *
     * MainExecutor constructor.
     * @param string $environment
     */
    public function __construct($environment = 'devel')
    {

        //Prendere tutti i servizi leggendo le rispettive configurazioni secondo il siteaccess
        $this->services  = new ServiceLoader($environment);

        //------------------------------------------------------------------------------------------
        //    ATTIVIAMO I SERVIZI DEL SISTEMA CHE SONO NECESSARI / OBBLIGATORI / IMPRESCINDIBILI
        //------------------------------------------------------------------------------------------

        //Facciamo il caricamento automatico dei servizi obbligatori
        $this->services->initAllMandatoryServices();

        //------------------------------------------------------------------------------------------


    }

    /**
     * Lo usiamo per l'avvio di doctrine da CLI
     * @return \Exception
     *
     * @throws \Exception
     */
    public function getEntityManagerForCLI(){

        $db = $this->services->get('db_connection');

        return $db->getEntityManager();

    }

    /**
     * Lo usiamo per l'avvio di doctrine da CLI
     * @return \Exception
     *
     * @throws \Exception
     */
    public function getEntityManagerForCommunityCLI(){

        $db = $this->services->get('database_community');

        return $db->getEntityManager();

    }

    /**
     * Lo usiamo per l'avvio di doctrine da CLI
     * @return \Exception
     *
     * @throws \Exception
     */
    public function getEntityManagerForRegistrazioneCLI(){

        $db = $this->services->get('database_registrazione');

        return $db->getEntityManager();

    }

    /**
     * Funzione astratta per l'esecuzione del loop principale
     *
     * @return mixed
     */
    abstract public function mainLoop();

    /**
     * Controlla se la variabile è una HttpFoundation\Response
     *
     * @param $response
     * @return bool
     */
    public function isAValidResponse($response)
    {
        //Il nome della classe contiene "Response"?
        return $this->services->objectIsOfType('Response', $response);

    }



}