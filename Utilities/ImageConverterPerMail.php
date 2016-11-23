<?php
namespace Engine\Utilities;


class ImageConverterPerMail
{
    //Immagine da mettere quando non ce n'è una
    const NO_IMAGE_GIF = "data:image/gif;base64,R0lGODlhSAA2AOeCALO5q7S5rLS6rLW6rbW7rba7rra8rre8r7i9sLi+sbm+sbm+srq/srq/s7vAs7vAtLzBtb3Btb3Ctr7Dt7/DuL/EuMDEucDFusHFusHGu8LGu8LHvMPHvMPHvcTIvcTIvsTJvsXJv8bKv8bKwMfLwcjMwsjMw8nNw8rNxMrOxMrOxcvOxcvPxszPxszPx8zQx83Qx83RyM7RyM7Ryc7Syc/SytDTytDTy9HUzNHUzdLVzdPWztPWz9TXz9TX0NXX0NXY0dbY0dbZ0tfZ09fa09ja09ja1Njb1Nnb1dnc1drc1trd1tvd19ve2Nze2N3f2d3f2t7g2t7g29/h29/h3ODi3eHi3eHj3uLj3+Lk3+Pk4OPl4OTl4eTm4eXm4uXn4+bn4+fo5Ofp5ejp5ejq5unq5+nr5+rr5+rr6Ovs6Ovs6ezt6u3u6+7v7O7v7e/w7fDw7vDx7/Hx7/Hy8PLy8PLz8fPz8fPz8vP08vT08vT08/T18/X19Pb29Pb29ff39ff39vj49////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH+EUNyZWF0ZWQgd2l0aCBHSU1QACwAAAAASAA2AAAI/gADCRxIsKDBgwgTKlzIsKHDhxAjSpxIsaLFixgzatzIsaPHjyBDihxJsqTJkyhTqlx5cYnLJk2WsKRoAYDNmzbTzIyIsyeAnQxtEvDZcygAAkANEl3aM+nAG0yj/nQaiGkfgWuWaqG6VKlPMFx9CsQ5tidYp2KrNlV700pYsmxvlr15Nmnauzi5vJUb1+Zcm3rR4vXbF0Bguz7XGBTh8zBQpgSXOt5pQipkqoUtE8aseS3mz6BD2x07V+fAn4SnVjVwmrBR1R/9op4Ke7ba1qRJAxBBUnbV05Grgk39u3jrzbEDBUAt0/jv2bQDBGCtGjXvkT8j0DYaHPWbn05oHv8NZGA57JJ1J6YXzb69+/fw48ufT7++/fv482sMCAA7";

    //Valori per default per la conversione
    public $max_width = 72;
    public $max_height = 72;

    /**
     * @param $imageURL
     * @return null|string
     */
    public function convertImage($imageURL) {
        //Abbiamo l'extension CURL attivata?
        if (in_array('curl', get_loaded_extensions())) {

            //CURL è abilitato nel nostro server, facciamone uso
            $curl = curl_init();
            
            //Per evitare il caricamento di tutta un'HP quando l'articolo XML non ha un'immagine facciamo:
            if(!preg_match('/jpg|jpeg|gif|bmp|png$/i',$imageURL)){
                return $this->getNoImage();
            }
            curl_setopt($curl, CURLOPT_URL, $imageURL);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120); //Timeout da 2 minuti
            //Verrà letto dopo in caso il server risponda con un 200
            $datiOttenuti = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            //Salviamo risorse liberandone la connessione
            curl_close($curl);

            //Se qualcosa andrà storto CURL darà false
            if ($httpCode !== 200) {
                return null;
            } else {

                //Se il server risponde, a partire dall'informazione restituita prendiamo le dimensioni
                list($width_orig, $height_orig) = getimagesizefromstring($datiOttenuti);

                //Calcoliamo aspect ratio
                $ratio_orig = $width_orig / $height_orig;

                if ($this->max_width / $this->max_height > $ratio_orig) {
                    $newWidth = $this->max_height * $ratio_orig;
                    $newHeight = $this->max_height;
                } else {
                    $newHeight = $this->max_width / $ratio_orig;
                    $newWidth = $this->max_width;
                }

                //Facciamo il resampling dell'immagine
                $image_p = imagecreatetruecolor($newWidth, $newHeight);

                //In base al tipo d'immagine creiamo la risorsa PHP
                if (preg_match('/jpg|jpeg/i',$imageURL)) {
                    $image = imagecreatefromjpeg($imageURL);
                } elseif (preg_match('/png/i',$imageURL)) {
                    $image = imagecreatefrompng($imageURL);
                } elseif (preg_match('/gif/i',$imageURL)) {
                    $image = imagecreatefromgif($imageURL);
                } elseif (preg_match('/bmp/i',$imageURL)) {
                    $image = imagecreatefrombmp($imageURL);
                } else {
                    //Altrimenti rispondiamo con questo
                    return $this->getNoImage();
                }

                //Ecco la funzione PHP che fa il resampling
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width_orig, $height_orig);

                //Per poter prendere il contenuto dobbiamo catturare l'output dal buffer (che menta)
                ob_start();
                    //Questo è l'unico modo per catturare il contenuto della conversione
                    imagejpeg($image_p);

                    //Prendiamo l'output
                    $final_image = ob_get_contents();

                //E puliamo il buffer
                ob_end_clean();

                //In base al tipo d'immagine creiamo la risorsa PHP
                if (preg_match('/jpg|jpeg/i',$imageURL)) {
                    $srcHeader = "data:image/jpeg;base64,";
                } elseif (preg_match('/png/i',$imageURL)) {
                    $srcHeader = "data:image/png;base64,";
                } elseif (preg_match('/gif/i',$imageURL)) {
                    $srcHeader = "data:image/gif;charset=utf-8;base64,";
                } elseif (preg_match('/bmp/i',$imageURL)) {
                    $srcHeader = "data:image/bmp;charset=utf-8;base64,";
                }

                $base64Image = $srcHeader . base64_encode($final_image);

                imagedestroy($image);
                imagedestroy($image_p);
            }
            return $base64Image;
        } else {

        }
    }

    private function getNoImage() {
        return self::NO_IMAGE_GIF;
    }
}
