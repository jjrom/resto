<?php
/*
 * Copyright 2014 Jérôme Gasperi
 *
 * Licensed under the Apache License, version 2.0 (the "License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/*
 * English Dictionary class
 */
class RestoDictionary_fr extends RestoDictionary {

    /*
     * Multiwords
     */
    public $multiwords = array(
        'pres de',
        'plus grand que',
        'superieur a',
        'superieure a',
        'plus petit que',
        'inferieur a',
        'inferieure a',
        'ete austral',
        'hiver austral',
        'printemps austral',
        'automne austral'
    );
    
    protected $dictionary = array(
        
        /*
         * List of word prefixes that are removed 
         * before analyze
         */
        'prefixes' => array('l\'', 'd\''),
        
        /*
         * And modifiers
         */
        'andModifiers' => array(
            'and' => array('et', 'a')
        ),
        /*
         * Location modifiers
         */
        'locationModifiers' => array(
            'in' => array('en', 'au', 'aux', 'a', 'sur', 'pres de', 'vers' , 'de'),
        ),
        /*
         * Quantity modifiers
         * !! Order is important !!
         */
        'quantityModifiers' => array(
            'equal' => array('egal', 'egale', 'egaux'),
            'with' => array('avec', 'contenant'),
            'for' => array('pour', 'de', 'sur', 'dans'),
            'between' => array('entre', 'de'),
            'greater' => array('plus', 'plus grand que', 'superieur a', 'superieure a', '>'),
            'lesser' => array('moins', 'plus petit que', '<', 'inferieur a', 'inferieure a'),
            'without' => array('sans', 'pas')
        ),
        /*
         * Time modifiers
         */
        'timeModifiers' => array(
            'after' => array('apres'),
            'before' => array('avant'),
            'between' => array('entre', 'de'),
            'in' => array('en' , 'a' , 'durant'),
            'last' => array('dernier', 'derniers', 'derniere', 'dernieres'),
            'next' => array('prochain', 'prochaine', 'prochains', 'prochaines'),
            'since' => array('depuis'),
            'today' => array('aujourd'),
            'tomorrow' => array('demain'),
            'yesterday' => array('hier')
        ),
        /*
         * Stop words i.e. excluded words
         */
        'stopWords' => array('le', 'la', 'de', 'des', 'et', 'ou', 'un', 'une'),
        /*
         * List of words in the query that are
         * considered as 'noise' for the query analysis
         * and thus excluded from the analysis
         */
        'noise' => array('acquis%', 'compris%'),
        /*
         * Months
         */
        'months' => array(
            '01' => array('janvier'),
            '02' => array('fevrier'),
            '03' => array('mars'),
            '04' => array('avril'),
            '05' => array('mai'),
            '06' => array('juin'),
            '07' => array('juillet'),
            '08' => array('aout'),
            '09' => array('septembre'),
            '10' => array('octobre'),
            '11' => array('novembre'),
            '12' => array('decembre')
        ),
        /*
         * Numbers
         */
        'numbers' => array(
            '1' => array('un'),
            '2' => array('deux'),
            '3' => array('trois'),
            '4' => array('quatre'),
            '5' => array('cinq'),
            '6' => array('six'),
            '7' => array('sept'),
            '8' => array('huit'),
            '9' => array('neuf'),
            '10' => array('dix'),
            '100' => array('cent'),
            '1000' => array('mille')
        ),
        /*
         * Quantities
         */
        'quantities' => array(
            'resolution' => array('resolution'),
            'orbit' => array('orbite'),
            'cloud' => array('nuage', 'nuages', 'nuageuse'),
            'snow' => array('neige', 'neigeuse'),
            'ice' => array('glace', 'glacier'),
            'urban' => array('urbain', 'urbaine', 'artificiel', 'ville', 'villes'),
            'cultivated' => array('cultivee', 'zone cultivee', 'cultivees', 'cultive', 'champ', 'champs'),
            'forest' => array('foret', 'forets', 'forestier', 'forestiere'),
            'herbaceous' => array('herbace', 'zone herbacee', 'plaine', 'steppe'),
            'desert' => array('desert', 'erg'),
            'flooded' => array('zone inondable', 'zone humide', 'humide'),
            'water' => array('eau')
        ),
        /*
         * Seasons
         */
        'seasons' => array(
            'autumn' => array('printemps austral', 'automne'),
            'spring' => array('automne austral', 'printemps'),
            'summer' => array('hiver austral', 'ete'),
            'winter' => array('ete austral', 'hiver')
        ),
        /*
         * Time units
         */
        'timeUnits' => array(
            'days' => array('jours', 'jour'),
            'months' => array('mois'),
            'years' => array('annee', 'annees', 'an', 'ans'),
            'weeks' => array('semaine', 'semaines')
        ),
        /*
         * Units
         */
        'units' => array(
            'm' => array('m', 'metre', 'metres'),
            'km' => array('km', 'kilometre', 'kilometres'),
            '%' => array('%', 'pourcent', 'pourcents', 'pourcentage')
        )
    );
    
    protected $translations = array(
        'activationSubject' => '[{a:1}] Code d\'activation',
        'activationMessage' => "Bonjour,\r\n\r\nVous vous êtes enregistré sur l'application {a:1}\r\n\r\nPour valider votre compte, cliquer sur le lien {a:2}\r\n\r\nCordialement\r\n\r\nL'équipe {a:1}",
        'resetPasswordSubject' => '[{a:1}] Demande de réinitialisation de mot de passe',
        'resetPasswordMessage' => "Bonjour,\r\n\r\nVous avez demandé une réinitialisation de votre mot de passe pour l'application {a:1}\r\n\r\nPour réinitialiser ce mot de passe, veuillez vous rendre sur le lien suivante {a:2}\r\n\r\nCordialement\r\n\r\nL'équipe {a:1}",
        '_acquiredOn' => 'acquis le {a:1}',
        '_alternateCollectionLink' => 'alternate',
        '_atomLink' => 'Lien ATOM pour {a:1}',
        '_firstCollectionLink' => 'premier',
        '_firstPage' => '<<',
        '_htmlLink' => 'Lien HTML pour {a:1}',
        '_jsonLink' => 'Lien GeoJSON pour {a:1}',
        '_lastCollectionLink' => 'dernier',
        '_lastPage' => '>>',
        '_metadataLink' => 'Lien vers le fichier de métadonnés de {a:1}',
        '_multipleResult' => '{a:1} résultats',
        '_nextCollectionLink' => 'suivant',
        '_nextPage' => 'Suivante',
        '_oneResult' => '1 résultat',
        '_osddLink' => 'OpenSearch Description Document',
        '_previousCollectionLink' => 'précédent',
        '_previousPage' => 'Précédente',
        '_selfCollectionLink' => 'self',
        '_selfFeatureLink' => 'self'
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
