Questa cartella è per l'usufruizione dei Proxy di Doctrine, quando 
stabiliamo relazioni tra tabelle tramite doctrine le risposte dove in una
query normale ti restituirebbe un id, doctrine ti restituisce la riga completa
della tabella relazionata

Per fare questo lui si appoggia a queste classi Proxy

Il fatto è che loro dovrebbero essere create lanciando il comando
**_sudo -u www-data php vendor/bin/doctrine orm:generate-proxies --env=devel_**

Ma alla creazione abbiamo settato a TRUE il parametro $isDevMode
 il quale ogni volta che viene eseguito Doctrine controllerà se esistono
 i proxies e li creerà a bisogno, lascio il pezzo di codice per capire meglio

    /**
     * Creates a configuration with a yaml metadata driver.
     *
     * @param array   $paths
     * @param boolean $isDevMode
     * @param string  $proxyDir
     * @param Cache   $cache
     *
     * @return Configuration
     */
    public static function createYAMLMetadataConfiguration(array $paths, $isDevMode = false, $proxyDir = null, Cache $cache = null)
    

E così è come l'abbiamo configurato

**_Setup::createYAMLMetadataConfiguration(array(self::DOCTRINE_ENTITIES_CFG_PATH), true, self::DOCTRINE_ENTITIES_PROXY_CACHE_PATH);_**