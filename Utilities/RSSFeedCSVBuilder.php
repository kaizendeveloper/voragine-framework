<?php
/**
 * L'engine per la costruzione dell'XML
 *
 * @author: Miguel Delli Carpini
 * @author: Matteo Scirea
 * @author: Javier Jara
 *
 * 
 *
 */

namespace Engine\Utilities;

class RSSFeedCSVBuilder
{

    //Qui salviamo l'instestazione del CSV
    protected $intestazione;

    //E qui ogni riga del CSV
    protected $csv_accumulator;

    //Separatore per default
    protected $separatorChar;



    /**
     * RSSFeedXMLBuilder constructor.
     */
    public function __construct()
    {

        $this->intestazione = '';
        $this->separatorChar = ',';
        $this->csv_accumulator = '';

    }

    /**
     * Inserisce sotto la document root i dati relativi a un utente in modalità "update"
     * <CMD V="U">
     *
     * @param array $userInfoArray
     */
    public function insertUser($userInfoArray = array()){

        if(count($userInfoArray) > 0) {

            //Solo la prima volta prendiamo l'array associativo per creare l'intestazione
            if($this->intestazione === '') {
                //Prendiamo le chiavi dall'array associativo
                $keysArray = array_keys($userInfoArray);
                $this->intestazione = implode($this->separatorChar , $keysArray);
            }

            //Creiamo riga del CSV
            $this->csv_accumulator .= implode($this->separatorChar, $userInfoArray) . "\r\n";

        }
    }

    /**
     * Restituisce il CSV finale
     * @param $withoutHeader boolean Stabilisce se si vuole stampare senza l'intestazione, utile quando si
     *                               devono concatenare più CSV
     * @return string
     */
    public function generateFinalDocument($withoutHeader = false){

        if($withoutHeader === true) {
            //Senza intestazione
            return $this->csv_accumulator;
        } else {
            //Con intestazione
            return $this->intestazione . "\r\n" . $this->csv_accumulator;
        }
    }

    public function setSeparatorCharacter($char)
    {
        $this->separatorChar = (string)$char;
    }

    public function resetBuffer()
    {
        $this->csv_accumulator = '';
        $this->intestazione = '';
        return $this;
    }

}