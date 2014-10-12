<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
 */

/*
 * English Dictionary class
 */

class RestoDictionary_fr extends RestoDictionary {

    protected $dictionary = array(
        /*
         * List of words in the query that are
         * considered as 'noise' for the query analysis
         * and thus excluded from the analysis
         */
        'excluded' => array(
            'image',
            'images',
            'acquise',
            'acquises',
            'comprise',
            'comprises',
            'couverture',
            'aire',
            'zone',
            'ayant'
        ),
        /*
         * Modifiers
         * 
         * Valid modifiers values are
         *  - with
         *  - witout
         *  - less
         *  - greater
         *  - and
         * 
         * For each entry 
         *   - the key (left side) is what the user types 
         *   - the value (right side) is the equivalent modifier
         */
        'modifiers' => array(
            'avant' => 'before',
            'apres' => 'after',
            'entre' => 'between',
            'de' => 'between',
            'a' => 'and',
            'contenant' => 'with',
            'avec' => 'with',
            'sans' => 'without',
            'pas' => 'without',
            'moins' => 'lesser',
            'inferieur' => 'lesser',
            'inferieure' => 'lesser',
            'plus' => 'greater',
            'superieur' => 'greater',
            'superieure' => 'greater',
            'egal' => 'equal',
            'egale' => 'equal',
            'egaux' => 'equal',
            'et' => 'and',
            'depuis' => 'since',
            'dernier' => 'last',
            'derniers' => 'last',
            'derniere' => 'last',
            'dernieres' => 'last',
            'aujourd' => 'today',
            'hier' => 'yesterday'
        ),
        /*
         * Units
         * 
         * For each entry 
         *   - the key (left side) is what the user types
         *   - the value (right side) is the equivalent unit
         * 
         */
        'units' => array(
            'm' => 'm',
            'metre' => 'm',
            'metres' => 'm',
            'km' => 'km',
            'kilometre' => 'km',
            'kilometres' => 'km',
            'pourcent' => '%',
            'pourcents' => '%',
            'pourcentage' => '%',
            '%' => '%',
            'jour' => 'days',
            'jours' => 'days',
            'mois' => 'months',
            'an' => 'years',
            'ans' => 'years',
            'annee' => 'years',
            'annees' => 'years'
        ),
        /*
         * Numbers
         * 
         * For each entry 
         *   - the key (left side) is the textual number
         *   - the value (right side) is number
         * 
         */
        'numbers' => array(
            'un' => '1',
            'deux' => '2',
            'trois' => '3',
            'quatre' => '4',
            'cinq' => '5',
            'six' => '6',
            'sept' => '7',
            'huit' => '8',
            'neuf' => '9',
            'dix' => '10',
            'cent' => '100',
            'mille' => '1000'
        ),
        /*
         * Months
         * 
         * For each entry 
         *   - the key (left side) is the month
         *   - the value (right side) is the equivalent
         *     month number (from 01 to 12)
         * 
         */
        'months' => array(
            'janvier' => '01',
            'fevrier' => '02',
            'mars' => '03',
            'avril' => '04',
            'mai' => '05',
            'juin' => '06',
            'juillet' => '07',
            'aout' => '08',
            'septembre' => '09',
            'octobre' => '10',
            'novembre' => '11',
            'decembre' => '12',
        ),
        /*
         * Quantities
         * 
         * Quantity is the entity on which apply a comparaison modifier
         * 
         *  e.g.
         *      "resolution   lesser    than 10  meters"
         *       <quantity> <modifier>           <units>
         * 
         */
        'quantities' => array(
            'resolution' => 'resolution',
            'orbite' => 'orbit',
            'nuage' => 'cloud',
            'nuages' => 'cloud',
            'nuageuse' => 'cloud',
            'neige' => 'snow',
            'neigeuse' => 'snow',
            'glace' => 'ice',
            'urbain' => 'urban',
            'urbaine' => 'urban',
            'artificiel' => 'urban',
            'ville' => 'urban',
            'zone cultivee' => 'cultivated',
            'cultivee' => 'cultivated',
            'cultivees' => 'cultivated',
            'foret' => 'forest',
            'forets' => 'forest',
            'herbace' => 'herbaceous',
            'zone herbacee' => 'herbaceous',
            'desert' => 'desert',
            'zone inondable' => 'flooded',
            'inondable' => 'flooded',
            'eau' => 'water'
        )
    );
    
    protected $translations = array(
        '_headerTitle' => 'RESTo',
        '_headerDescription' => '<b>RESTo</b> - <b>RE</b>stful <b>S</b>emantic search <b>T</b>ool for ge<b>O</b>spatial<br/>RESTo est un service de recherche s&eacute;mantique de donn&eacute;es d\'observation de la Terreervice. Il suit le standard OGC 13-026 - OpenSearch Extension for Earth Observation.',
        '_selfCollectionLink' => 'self',
        '_alternateCollectionLink' => 'alternate',
        '_firstCollectionLink' => 'first',
        '_lastCollectionLink' => 'last',
        '_nextCollectionLink' => 'next',
        '_previousCollectionLink' => 'previous',
        '_selfFeatureLink' => 'self',
        '_about' => 'A propos',
        '_close' => 'Fermer',
        '_acquiredOn' => 'acquise en {a:1}',
        '_placeHolder' => 'Chercher - ex. {a:1}',
        '_query' => 'Filtres de recherche - {a:1}',
        '_notUnderstood' => 'Requête non comprise - aucun filtre de recherche n\'est appliqué',
        '_noResult' => 'Aucun résultat - essayez une autre recherche !',
        '_oneResult' => '1 résultat',
        '_multipleResult' => '{a:1} résultats',
        '_firstPage' => '<<',
        '_previousPage' => 'Précédent',
        '_nextPage' => 'Suivant',
        '_lastPage' => '>>',
        '_pageNumber' => 'Page {a:1}',
        '_identifier' => 'Identifiant',
        '_resolution' => 'Résolution',
        '_startDate' => 'Début d\'acquisition',
        '_completionDate' => 'Fin d\'acquisition',
        '_viewMetadata' => 'Voir la description compl&egrave;te du produit {a:1}',
        '_viewMapshup' => 'Afficher sur une carte',
        '_viewMapshupFullResolution' => 'Afficher sur la carte',
        '_download' => 'Télécharger',
        '_keywords' => 'Mots clés',
        '_atomLink' => 'Lien ATOM pour {a:1}',
        '_htmlLink' => 'Lien HTML pour {a:1}',
        '_jsonLink' => 'Lien GeoJSON pour {a:1}',
        '_thisResourceContainsLanduse' => 'Images contenant de {a:2}',
        '_thisResourceIsLocated' => 'Images situ&eacute;e en {a:1}',
        '_thisResourceContainsCity' => 'Images autour de {a:1}',
        '_thisResourceWasAcquiredBy' => 'Images acquises par le satellite {a:1}',
        '_landUse' => 'Occupation du sol',
        '_location' => 'Localisation',
        '_platform' => 'Satellite',
        '_tags' => 'Tags',
        '_other' => 'Autre',
        'THR' => 'Images de résolution comprise entre 0 et 2.5 m',
        'HR' => 'Images de résolution comprise entre 2.5 et 30 m',
        'MR' => 'Images de résolution comprise entre 30 et 500 m',
        'LR' => 'Images de résolution supérieure à 500 m',
        '_home' => 'Accueil',
        '_viewAtomFeed' => 'Voir le flux Atom pour le résultat de cette recherche',
        '_shareOn' => 'Partager sur {a:1}',
        '_zoom' => 'Zoomer',
        '_unZoom' => 'Dézoomer',
        '_centerOnLayer' => 'Centrer la vue sur le résultat de recherche',
        '_globalMapView' => 'Centrer la vue sur la Terre',
        '_showOnMap' => 'Voir sur la carte',
        '_addCollection' => 'Ajouter une collection',
        '_update' => 'Modifier',
        '_deactivate' => 'Désactiver',
        '_remove' => 'Supprimer',
        '_login' => 'Connexion',
        '_logout' => 'Deconnexion',
        '_dropCollection' => 'Déposer un fichier descriptif de collection',
        '_email' => 'Adresse mail',
        '_password' => 'Mot de passe',
        '_createAccount' => 'Créer un compte',
        '_givenName' => 'Prénom',
        '_lastName' => 'Nom',
        '_userName' => 'Surnom',
        '_retypePassword' => 'Retaper le mot de passe',
        '_back' => 'Retour',
        '_signWithOauth' => 'S\'identifier avec un compte {a:1}',
        '_addResource' => 'Ajouter une resource',
        '_dropResource' => 'Déposer un fichier de métadonnées',
        '_resultFor' => 'R&eacute;sultats correspondants &agrave; &#34;{a:1}&#34;',
        '_resourceSummary' => 'Image {a:1} ({a:2} m) acquise le {a:3}',
        '_poi' => 'Point d\'int&eacute;r&ecirc;ts'
    );
    
    /**
     * Constructor
     * 
     * @param RestoDatabaseDriver $dbDriver
     * @throws Exception
     */
    public function __construct($dbDriver) {
        parent::__construct($dbDriver);
    }

}
