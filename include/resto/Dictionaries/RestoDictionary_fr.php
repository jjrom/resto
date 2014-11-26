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
         * For each entry 
         *   - the key (left side) is the modifier key
         *   - the value (right side) is an array of modifier homonyms
         *     in the given language. The first value is the prefered one
         *   
         */
        'modifiers' => array(
            'after' => array('apres'),
            'and' => array('et', 'a'),
            'before' => array('avant'),
            'between' => array('entre', 'de'),
            'equal' => array('egal', 'egale', 'egaux'),
            'greater' => array('plus', 'superieur', 'superieure', '>'),
            'in' => array('en', 'au'),
            'last' => array('dernier', 'derniers', 'derniere', 'dernieres'),
            'lesser' => array('moins', '<', 'inferieur', 'inferieure'),
            'since' => array('depuis'),
            'today' => array('aujourd'),
            'with' => array('avec', 'contenant'),
            'without' => array('sans', 'pas'),
            'yesterday' => array('hier')
        ),
        /*
         * Units
         * 
         * For each entry 
         *   - the key (left side) is the unit key
         *   - the value (right side) is an array of unit homonyms
         *     in the given language. The first value is the prefered one
         * 
         */
        'units' => array(
            'm' => array('m', 'metre', 'metres'),
            'km' => array('km', 'kilometre', 'kilometres'),
            '%' => array('%', 'pourcent', 'pourcents', 'pourcentage'),
            'days' => array('jours', 'jour'),
            'months' => array('mois'),
            'years' => array('annee', 'annees', 'an', 'ans')
        ),
        /*
         * Numbers
         * 
         * For each entry 
         *   - the key (left side) is the number key
         *   - the value (right side) is an array of number homonyms
         *     in the given language. The first value is the prefered one
         * 
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
         * Months
         * 
         * For each entry 
         *   - the key (left side) is the month key
         *   - the value (right side) is an array of month homonyms
         *     in the given language. The first value is the prefered one
         * 
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
         * Seasons
         */
        'seasons' => array(
            'automn' => array('automne'),
            'spring' => array('printemps'),
            'summer' => array('ete'),
            'winter' => array('hiver')
        ),
        /*
         * Quantities
         * 
         * For each entry 
         *   - the key (left side) is the quantity key
         *   - the value (right side) is an array of quantity homonyms
         *     in the given language. The first value is the prefered one
         * 
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
        )
    );
    
    protected $translations = array(
        '_headerTitle' => 'resto',
        '_headerDescription' => '<b>resto</b> - <b>RE</b>stful <b>S</b>emantic search <b>T</b>ool for ge<b>O</b>spatial<br/>RESTo est un service de recherche s&eacute;mantique de donn&eacute;es d\'observation de la Terreervice. Il suit le standard OGC 13-026 - OpenSearch Extension for Earth Observation.',
        '_administration' => 'Administration',
        '_homeSearchTitle' => 'Recherche d\'images satellites',
        '_eg' => 'ex.',
        '_homeSearchExample' => 'Images acquises en été sur des zones urbaines en France',
        '_numberOfCollections' => 'Collections',
        '_numberOfProducts' => 'Produits',
        '_statistics' => 'Statistiques',
        '_noSearchResultsTitle' => 'Pas de résultats',
        '_noSearchResultsFor' => 'Il n\'y a pas de résultats pour <b>"{a:1}"</b>. Veuillez essayer une autre recherche !',
        '_pageNotFoundTitle' => 'La page que vous recherchez n\'existe pas',
        '_pageNotFoundDescription' => 'Avez-vous essayer de chercher autre chose ?',
        '_myCart' => 'Mon panier',
        '_addToCart' => 'Ajouter au panier',
        '_cartIsEmpty' => 'Votre panier est vide',
        '_itemAddedToCart' => 'Produit ajouté au panier',
        '_itemAlreadyInCart' => 'Le produit est déjà présent dans le panier',
        '_downloadCart' => 'Télécharger',
        '_emailSent' => 'Un code d\'activation vient d\'être envoyé par mail',
        '_registrationFailed' => 'Enregistrement annulé',
        '_connectionFailed' => 'La connexion a échoué',
        '_disconnectFailed' => 'La deconnexion a échoué',
        '_nonExistentResource' => 'La resource n\'existe pas',
        '_unsufficientPrivileges' => 'Vous n\'avez pas les droits nécessaires pour accéder à cette resource',
        '_wrongPassword' => 'Mot de passe incorrect',
        '_cannotSignIn' => 'Impossible de se connecter',
        '_invalidEmail' => 'L\'adresse mail est invalide',
        '_usernameIsMandatory' => 'Veuillez renseigner un nom d\'utilisateur',
        '_passwordIsMandatory' => 'Veuillez renseigner un mot de passe',
        '_error' => 'Erreur',
        '_info' => 'Note',
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
        '_thisResourceContainsLanduse' => '{a:2} : {a:1}%',
        '_thisResourceIsLocated' => 'Image situ&eacute;e en {a:1}',
        '_thisResourceContainsCity' => 'Image autour de {a:1}',
        '_thisResourceWasAcquiredBy' => 'Image acquise par le satellite {a:1}',
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
        '_firstName' => 'Prénom',
        '_lastName' => 'Nom',
        '_userName' => 'Surnom',
        '_back' => 'Retour',
        '_addResource' => 'Ajouter une resource',
        '_dropResource' => 'Déposer un fichier de métadonnées',
        '_resultFor' => 'R&eacute;sultats correspondants &agrave; &#34;{a:1}&#34;',
        '_resourceSummary' => 'Image {a:1} acquise le {a:2}',
        '_poi' => 'Point d\'int&eacute;r&ecirc;ts',
        // Menu
        '_menu_shareOn' => 'Partager sur {a:1}',
        '_menu_cart' => 'Panier',
        '_menu_connexion' => 'Se connecter',
        '_menu_search' => 'Chercher...',
        '_menu_signup' => 'Inscription',
        '_menu_signup_explain' => 'Enregistrez-vous pour chercher, visualiser et télécharger des tonnes de produits',
        '_menu_signin' => 'Connection',
        '_menu_signinwith' => 'Se connecter avec',
        '_menu_signout' => 'Déconnection',
        '_menu_collections' => 'Collections',
        '_menu_map' => 'carte',
        '_menu_list' => 'liste',
        '_month01' => 'Janvier',
        '_month02' => 'Février',
        '_month03' => 'Mars',
        '_month04' => 'Avril',
        '_month05' => 'Mai',
        '_month06' => 'Juin',
        '_month07' => 'Juillet',
        '_month08' => 'Août',
        '_month09' => 'Septembre',
        '_month10' => 'Octobre',
        '_month11' => 'Novembre',
        '_month12' => 'Décembre',
        '_niceDate' => '{a:3} {a:2} {a:1}',
        '_facets_collections' => 'Collections',
        '_facets_where' => 'Où ? ',
        '_facets_when' => 'Quand ?',
        '_facets_what' => 'Quoi ?'
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
