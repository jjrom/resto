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
         * And modifiers
         */
        'andModifiers' => array(
            'and' => array('et', 'a')
        ),
        /*
         * Location modifiers
         */
        'locationModifiers' => array(
            'in' => array('en', 'au', 'a', 'sur'),
        ),
        /*
         * Quantity modifiers
         */
        'quantityModifiers' => array(
            'equal' => array('egal', 'egale', 'egaux'),
            'between' => array('entre', 'de'),
            'greater' => array('plus', 'superieur', 'superieure', '>'),
            'lesser' => array('moins', '<', 'inferieur', 'inferieure'),
            'with' => array('avec', 'contenant'),
            'without' => array('sans', 'pas')
        ),
        /*
         * Time modifiers
         */
        'timeModifiers' => array(
            'after' => array('apres'),
            'before' => array('avant'),
            'between' => array('entre', 'de'),
            'in' => array('en' , 'a'),
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
        'stopWords' => array('le', 'la', 'de', 'et', 'ou', 'l'),
        /*
         * List of words in the query that are
         * considered as 'noise' for the query analysis
         * and thus excluded from the analysis
         */
        'noise' => array('acquis', 'comprise'),
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
            'urban' => array('urbain', 'urbaine', 'artificiel', 'ville'),
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
            'autumn' => array('automne'),
            'spring' => array('printemps'),
            'summer' => array('ete'),
            'winter' => array('hiver')
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
