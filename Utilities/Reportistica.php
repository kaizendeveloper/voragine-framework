<?php
/**
 * Class Reportistica
 *
 * Restituisce il report attuale, il quale verrà aggiornato mentre non sia scaduto
 *
 * @dependencies: MailerService, EntityManager, TemplateEngineService
 *
 */
namespace WPUserExtractor\Utilities;

use WPUserExtractor\Kernel\ExecutorDefinition;

use Entity\Report;
use Entity\ArticleFromFeed;

class Reportistica
{
    //Gli arretrati saranno quelli che hanno più di un giorno
    //Per capire il formato usato andate su
    // http://php.net/manual/en/class.dateinterval.php
    const SOGLIA_DI_TEMPO = 'PT45S';

    //Servizio Mailer
    protected $mailer;

    //Servizio Doctrine
    protected $em;

    //Servizio Template Engine
    protected $twig;

    //Periodo di tempo per l'invio delle mail
    protected $soglia_di_tempo;

    public function __construct(ExecutorDefinition $executorDefinition)
    {

        //Useremo la notazione per i servizi che abbiamo creato noi
        //----------------------------------------------------------
        //Wrappiamo il mailer
        $this->mailer = $executorDefinition->getService('mailer');
        //Wrappiamo l'entity manager
        $this->em = $executorDefinition->getService('db_connection')->getEntityManager();
        //Wrappiamo il servizio template
        $this->twig = $executorDefinition->getService('template_engine');

        //Prendiamo il periodo di invio delle mail
        $this->soglia_di_tempo = $this->mailer->getReportTimeConfig();

    }

    /**
     * Prende i report che hanno una data di invio più vecchia della soglia di tempo voluta
     *
     * @return mixed
     */
    private function prendiReportArretrati()
    {

        //Usiamo il query builder di Doctrine
        $qb = $this->em->createQueryBuilder();

        //Calcoliamo il tempo
        $sogliaDiTempo = new \DateTime();
        //$sogliaDiTempo = $sogliaDiTempo->sub(new \DateInterval($this->soglia_di_tempo));

        //La costruzione si può concatenare ma per semplicità di lettura ho fatto riga a riga
        $qb->select('e');
        $qb->from('Entity\Report','e');
        $qb->add('where', $qb->expr()->lt('e.ora_invio', ':from'));

        //Chi è pratico di PDO capirà il perché di questo qui
        $qb->setParameters(array('from' => $sogliaDiTempo));

        //Dovrà restituire le rispettive entità
        return $qb->getQuery()->getResult();
    }

    /**
     *  Invece per i report che hanno una data di invio al di sopra la soglia di tempo voluta
     *  rientreranno come report attuali
     *
     * @return mixed
     */

    private function prendiReportAttuale(){

        //Usiamo il query builder di Doctrine
        $qb = $this->em->createQueryBuilder();

        $adesso = new \DateTime();

        //La chiamata ai metodi si possono concatenare ma per semplicità di lettura
        //--------------------------------------------------------------------------
        $qb->select('e');
        $qb->from('Entity\Report','e');
        $qb->add('where', $qb->expr()->gt('e.ora_invio',':from'));
        //Chi è pratico di PDO capirà il perché di questo qui (PDO SQL Injection Protection)
        $qb->setParameters(array('from' => $adesso));

        //In italiano: Si prendono i report con ora di invio superiore a la soglia di tempo

        //Dovrà restituire le rispettive entità
        return $qb->getQuery()->getResult();
    }


    /**
     * Punto principale di questa classe
     *
     * - Genera report HTML
     * - Spedisce le mail
     * - Cancella i vecchi report dal DB
     *
     * @param $reportEntity
     */
    private function generaReport($reportEntity) {


        //Istanziamo la libreria per la conversione delle immagini
        $imageConverter = new ImageConverterPerMail();

        //Resoconto da dare in pasto al template Twig
        $reportDaSpedire = array();


        //--------------------------------------------------------------------------
        //OGGETTI AGGIUNTI
        //--------------------------------------------------------------------------

        //Visto che dobbiamo usare un GroupBy purtroppo ricorrere al query builder di Doctrine
        $qb = $this->em->createQueryBuilder();

        //La chiamata ai metodi si possono concatenare ma per semplicità di lettura
        //--------------------------------------------------------------------------
        $qb->select('e');
        $qb->from('Entity\ReportAddedArticle','e');
        $qb->add('where', $qb->expr()->eq('e.report',':id_report'));
        $qb->groupBy('e.article_from_feed');
        //Chi è pratico di PDO capirà il perché di questo qui (PDO SQL Injection Protection)
        $qb->setParameters(array('id_report' => $reportEntity->getId()));

        $oggettiAggiunti = $qb->getQuery()->getResult();

        //--------------------------------------------------------------------------
        //OGGETTI AGGIORNATI
        //--------------------------------------------------------------------------

        //Visto che dobbiamo usare un GroupBy purtroppo ricorrere al query builder di Doctrine
        $qb = $this->em->createQueryBuilder();

        //La chiamata ai metodi si possono concatenare ma per semplicità di lettura
        //--------------------------------------------------------------------------
        $qb->select('e');
        $qb->from('Entity\ReportUpdatedArticle','e');
        $qb->add('where', $qb->expr()->eq('e.report',':id_report'));
        $qb->groupBy('e.article_from_feed');
        //Chi è pratico di PDO capirà il perché di questo qui (PDO SQL Injection Protection)
        $qb->setParameters(array('id_report' => $reportEntity->getId()));

        $oggettiAggiornati = $qb->getQuery()->getResult();

        //----------------------------------------------------------------------------

        foreach($oggettiAggiunti as $oggettoAggiunto) {

            //$oggettoAggiunto sarà l'unione (vedere tabella nel DB) di quelle due entintà quindi
            //per reperire l'articolo aggiunto dobbiamo fare

            $article = $oggettoAggiunto->getArticleFromFeed();

            $reportDaSpedire['articoli_aggiunti'][] = array(
                'title'         => $article->getTitle(),
                'guid'          => $article->getRemoteId(),
                'description'   => $article->getDescription(),
                'link'          => $article->getLink(),
                'image_url'     => $article->getImageUrl(),
                'imagebase64'   => $imageConverter->convertImage($article->getImageUrl()));

        }

        foreach($oggettiAggiornati as $oggettoAggiornato) {

            //$oggettoAggiunto sarà l'unione (vedere tabella nel DB) di quelle due entintà quindi
            //per reperire l'articolo aggiunto dobbiamo fare

            $article = $oggettoAggiornato->getArticleFromFeed();

            $reportDaSpedire['articoli_aggiornati'][] = array(
                'title'         => $article->getTitle(),
                'guid'          => $article->getRemoteId(),
                'description'   => $article->getDescription(),
                'link'          => $article->getLink(),
                'image_url'     => $article->getImageUrl(),
                'imagebase64'   => $imageConverter->convertImage($article->getImageUrl()));

        }


        //Fatto questo elaboriamo il corpo della mail
        if(!is_null($this->twig)){
            $reportInHTML = $this->twig->render('base.twig', array('report' => $reportDaSpedire));
        } else {
            $reportInHTML = '<strong>NO TEMPLATE ENGINE CONFIGURED</strong>';
        }



        //E' una buona pratica controllare se esiste il servizio
        if(!is_null($this->mailer)){
            $this->mailer->sendMessage($reportInHTML);
        }

        //Una volta inviato questo report possiamo bruciarlo via
        $this->em->remove($reportEntity);

    }

    /**
     * Restituisce il report attuale, il quale verrà aggiornato mentre non sia scaduto
     * @return Report|mixed
     */
    public function processa(){
        //Controlliamo se ci sono report arretrati
        foreach($this->prendiReportArretrati() as $reportASpedire) {
            $this->generaReport($reportASpedire);
        }

        //Il report attuale lo creiamo in base alla soglia di tempo
        $reportAttuale = $this->prendiReportAttuale();

        //Se è ancora in vigore un report faremo tutte le operazioni proprio su quello
        if(count($reportAttuale) > 0) {
            $reportAttuale = $reportAttuale[0];
        } else {
            //Altrimenti ne creiamo uno nuovo
            $reportAttuale = new Report();
            //Dobbiamo approfittare qui per impostare la data di invio
            $adesso = new \DateTime('NOW');
            $reportAttuale->setOraInvio($adesso->add(new \DateInterval($this->soglia_di_tempo)));
        }

        return $reportAttuale;
    }

    /**
     * Controlla se un articolo che si vuole aggiornare risulta nella tabella degli oggetti aggiunti
     * per i report
     * @param ArticleFromFeed $articolo
     * @return bool
     */
    public function articoloSiDeveAggiornare(ArticleFromFeed $articolo) {

        //Fetch usando Repository
        //--------------------------------------------------------------------------

        $dcmRepo = $this->em->getRepository('Entity\ReportAddedArticle');
        $found = $dcmRepo->findOneBy(array('article_from_feed' => $articolo));

        if($found !== null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Controlla se un articolo che si vuole aggiungere risulta nella tabella degli oggetti aggiunti
     * o quelli aggiornati
     * per i report
     * @param ArticleFromFeed $articolo
     * @return bool
     */
    public function articoloDeveAndareInRapporto(ArticleFromFeed $articolo) {



        $esisteComeAggiornato = false;
        $esisteComeAggiunto = false;


        //Fetch usando Repository
        //--------------------------------------------------------------------------

        $dcmRepo = $this->em->getRepository('Entity\ReportUpdatedArticle');
        $found = $dcmRepo->findOneBy(array('article_from_feed' => $articolo));

        if($found !== null) {
            $esisteComeAggiornato = true;
        } else {
            $esisteComeAggiornato = false;
        }

        $dcmRepo = $this->em->getRepository('Entity\ReportAddedArticle');
        $found = $dcmRepo->findOneBy(array('article_from_feed' => $articolo));

        if($found !== null) {
            $esisteComeAggiunto = true;
        } else {
            $esisteComeAggiunto = false;
        }

        return !($esisteComeAggiornato || $esisteComeAggiunto );



    }


    public function articoliSonoUguali(ArticleFromFeed $articoloSX, ArticleFromFeed $articoloDX)
    {
        $remote_id_sx =  md5(serialize($articoloSX->getRemoteId()));
        $remote_id_dx =  md5(serialize($articoloDX->getRemoteId()));

        $remote_equal = ($remote_id_dx === $remote_id_sx);

        $title_sx =  md5(serialize($articoloSX->getTitle()));
        $title_dx =  md5(serialize($articoloDX->getTitle()));

        $title_equal = ($title_dx === $title_sx);

        $author_sx =  md5(serialize($articoloSX->getAuthor()));
        $author_dx =  md5(serialize($articoloDX->getAuthor()));

        $author_equal = ($author_dx === $author_sx);

        $image_url_sx =  md5(serialize($articoloSX->getImageUrl()));
        $image_url_dx =  md5(serialize($articoloDX->getImageUrl()));

        $image_equal = ($image_url_dx === $image_url_sx);

        $category_sx =  md5(serialize($articoloSX->getCategory()));
        $category_dx =  md5(serialize($articoloDX->getCategory()));

        $category_equal =  ($category_dx === $category_sx);

        $description_sx =  md5(serialize($articoloSX->getDescription()));
        $description_dx =  md5(serialize($articoloDX->getDescription()));

        $description_equal = ($description_dx === $description_sx);

        $link_sx =  md5(serialize($articoloSX->getLink()));
        $link_dx =  md5(serialize($articoloDX->getLink()));

        $link_equal = ($link_dx === $link_sx);

        $pubdate_sx =  md5(serialize($articoloSX->getPubdate()->date));
        $pubdate_dx =  md5(serialize($articoloDX->getPubdate()->date));

        $pubdate_equal = ($pubdate_dx === $pubdate_sx);


        
        return ($remote_equal && $title_equal && $author_equal
            && $image_equal && $category_equal && $description_equal
            && $link_equal && $pubdate_equal);
    }
}
