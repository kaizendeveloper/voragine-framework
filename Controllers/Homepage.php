<?php
/**
 *
 *
 * @authors: Miguel Delli Carpini, Matteo Scirea, Javier Jara
 */

namespace Voragine\Controllers;



//Namespace necessari per accedere agli oggetti Symfony
//------------------------------------------------------


//Namespace necessari per avviare l'applicazione secondo il caso

use Symfony\Component\HttpFoundation\Response;
use Voragine\Kernel\Definitions\Controllers;

class Homepage extends ControllerSkeleton
{

    public function landingPage(){

        $twig = $this->services->get('template_engine');

        $output = $twig->render('homepage-landingpage.twig', array());

        $response = new Response($output, Response::HTTP_OK);

        return $response;

    }


}