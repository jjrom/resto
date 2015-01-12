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

/**
 * RESTo Collection
 */
class RestoCollection {
    
    /*
     * Collection name must be unique
     */
    public $name =  null;
   
    /*
     * Data model for this collection
     */
    public $model = null;
    
    /*
     * Properties mapping
     */
    public $propertiesMapping = array();
    
    /*
     * Context reference
     */
    public $context = null;
    
    /*
     * User
     */
    public $user = null;
    
    /*
     * Array of OpenSearch Description parameters per lang
     */
    public $osDescription = null;
    
    /*
     * Collection licenses (i.e. conditions of use)
     * 
     * Structure
     *      array(
     *          'en' => //license url
     *          'fr' => //license url
     *          ...
     *      )
     */
    public $license;
    
    /*
     * Statistics
     */
    public $statistics = array();
    
    /*
     * Array of options
     */
    private $options = array();
    
    /*
     * Collection description is synchronized with database
     */
    private $synchronized = false;
    
    /**
     * Constructor
     * 
     * @param string $name : collection name
     * @param RestoContext $context : RESTo context
     * @param RestoUser $user : RESTo user
     * @param array $options : constructor options
     */
    public function __construct($name, $context, $user, $options = array()) {
        
        /*
         * Context is mandatory
         */
        if (!isset($context) || !is_a($context, 'RestoContext')) {
            throw new Exception('Context must be defined', 500);
        }
        
        /*
         * Collection name should be alphanumeric based only
         */
        if (!isset($name) || !ctype_alnum($name) || is_numeric(substr($name, 0, 1))) {
            throw new Exception(($context->debug ? __METHOD__ . ' - ' : '') . 'Collection name must be an alphanumeric string not starting with a digit', 500);
        }
        
        $this->name = $name;
        $this->context = $context;
        $this->user = $user;
        $this->options = $options;
        
        /*
         * Load collection description from database 
         */
        if (isset($options['autoload']) && $options['autoload']) {
            $this->loadFromStore();
        }
        
        return $this;
        
    }
    
    /**
     * Return collection url
     * 
     * @param string $format : output format for url
     */
    public function getUrl($format = '') {
        return RestoUtil::restoUrl($this->context->baseUrl, 'collections/' . $this->name, $format);
    }
    
    /**
     * Load collection parameters from input collection description 
     * Collection description is a JSON file with the following structure
     * 
     *      {
     *          "name": "Charter",
     *          "controller": "RestoCollection_Default",
     *          "status": "public",
     *          "osDescription": {
     *              "en": {
     *                  "ShortName": "International Charter Space and Major Disasters",
     *                  "LongName": "International Charter Space and Major Disasters catalog",
     *                  "Description": "The International Charter aims at providing a unified system of space data acquisition and delivery to those affected by natural or man-made disasters through Authorized Users. Each member agency has committed resources to support the provisions of the Charter and thus is helping to mitigate the effects of disasters on human life and property",
     *                  "Tags": "international charter space disasters",
     *                  "Developper": "J\u00e9r\u00f4me Gasperi",
     *                  "Contact": "jerome.gasperi@gmail.com",
     *                  "Query": "Cyclones in Asia in october 2013",
     *                  "Attribution": "RESTo framework. Copyright 2013, All Rights Reserved"
     *              },
     *              "fr": {
     *                  ...
     *              }
     *          },
     *          "propertiesMapping": {
     *              "identifier": "{a:1} will be replaced by identifier property value",
     *              "organisationName": "This is a constant"
     *              ...
     *          }
     *      }
     * 
     * @param array $object : collection description as json file
     * @param boolean $synchronize : true to store collection to database
     */
    public function loadFromJSON($object, $synchronize = false) {
        
        /*
         * Input $object should be JSON
         */
        if (!isset($object) || !is_array($object)) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Invalid input JSON', 500);
        }
        
        /*
         * Check that input file is for the current collection
         */
        if (!isset($object['name']) ||$this->name !== $object['name']) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Property "name" and collection name differ', 500);
        }
        
        /*
         * Model name must be set in JSON file
         */
        if (!isset($object['model'])) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Property "model" is mandatory', 500);
        }
      
        /*
         * Check that input file is for the current collection
         */
        if (isset($this->model)) {
            if ($this->model->name !== $object['model']) {
                throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'Property "model" and collection model differ', 500);
            }
        }
        /*
         * Set model
         */
        else {
            $this->model = RestoUtil::instantiate($object['model'], array($this->context, $this->user));
        }
        
        /*
         * Default collection status is 'public'
         */
        $this->status = isset($object['status']) && $object['status'] === 'private' ? 'private' : 'public';
        
        /*
         * At least an english OpenSearch Description object is mandatory
         */
        if (!is_array($object['osDescription']) || !is_array($object['osDescription']['en'])) {
            throw new Exception(($this->context->debug ? __METHOD__ . ' - ' : '') . 'English OpenSearch description is mandatory', 500);
        }
        $this->osDescription = $object['osDescription'];
        
        /*
         * Licence
         */
        if (isset($object['license'])) {
            $this->license = $object['license'];
        }
        
        /*
         * Template
         */
        if (isset($object['propertiesMapping'])) {
            $this->propertiesMapping = $object['propertiesMapping'];
        }
        
        $this->synchronized = false;
        
        /*
         * Save on database
         */
        if ($synchronize) {
            $this->saveToStore();
        }
        
        return $this;
        
    }
   
    /**
     * Return license in the current language
     */
    public function getLicense() {
        if (!isset($this->license)) {
            return null;
        }
        if (!isset($this->license[$this->context->dictionary->language])) {
            if (isset($this->license['en'])) {
                return $this->license['en'];
            }
            return null;
        }
        return $this->license[$this->context->dictionary->language];
    }
    
    /**
     * Return OpenSearch property in the current language
     * or in english otherwise
     * 
     * @param string $property
     */
    public function getOSProperty($property) {
        if (!isset($property)) {
            return '';
        }
        if (!isset($this->osDescription[$this->context->dictionary->language]) || !isset($this->osDescription[$this->context->dictionary->language][$property])) {
            return isset($this->osDescription['en'][$property]) ? $this->osDescription['en'][$property] : $property;
        }
        return $this->osDescription[$this->context->dictionary->language][$property];
    }
    
    /**
     * Load collection parameters from RESTo database
     */
    public function loadFromStore() {
        
        /*
         * Retrieve facets
         */
        $facets = array('collection', 'continent');
        $model = new RestoModel_default($this->context, $this->user);
        foreach (array_values($model->searchFilters) as $filter) {
            if (isset($filter['options']) && $filter['options'] === 'auto') {
                $facets[] = $filter['key'];
            }
        }
        
        $collectionDescription = $this->context->dbDriver->getCollectionDescription($this->name, $facets);
        $this->model = RestoUtil::instantiate($collectionDescription['model'], array($this->context, $this->user));
        $this->osDescription = $collectionDescription['osDescription'];
        $this->status = $collectionDescription['status'];
        $this->license = $collectionDescription['license'];
        $this->propertiesMapping = $collectionDescription['propertiesMapping'];
        $this->statistics = $collectionDescription['statistics'];
        $this->synchronized = true;
        return $this;
    }
    
    /**
     * Remove collection  from RESTo database
     */
    public function removeFromStore() {
        $this->context->dbDriver->removeCollection($this);
        $this->synchronized = false;
    }
    
    /**
     * Save collection to RESTo database
     */
    public function saveToStore() {
        $this->context->dbDriver->storeCollection($this);
        $this->synchronized = true;
        return $this;
    }
    
    /**
     * Return true if collection description is synchronized with database
     */
    public function isSynchronized() {
        return $this->synchronized;
    }
    
    
    /**
     * Search features within collection
     * 
     * @return array (FeatureCollection)
     */
    public function search() {
        return new RestoFeatureCollection($this->context, $this->user, $this);
    }
    
    /**
     * Get a feature identified by unique $identifier
     * 
     * @param string $identifier : feature unique $identifier (i.e. UUID)
     * @param array $rightsFilters : rights filters applied to this feature
     */
    public function getFeature($identifier, $rightsFilters = array()) {
       
    }
    
    /**
     * Add feature to the {collection}.features table
     * 
     * @param array $data : GeoJSON file or file splitted in array
     */
    public function addFeature($data) {
        return $this->model->addFeature($data, $this->name);
    }
    
    /**
     * Output collection description as an array
     */
    public function toArray() {
        return array(
            'name' => $this->name,
            'status' => $this->status,
            'model' => $this->model->name,
            'license' => isset($this->license) ? $this->license : null,
            'osDescription' => $this->osDescription,
            //'propertiesMapping' => $this->propertiesMapping,
            'statistics' => $this->statistics
        );
    }
    
    /**
     * Output collection description as a JSON stream
     * 
     * @param boolean $pretty : true to return pretty print
     */
    public function toJSON($pretty = false) {
        return RestoUtil::json_format($this->toArray(), $pretty);
    }
    
    /**
     * Output collection description as an XML OpenSearch document
     * 
     * <OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:geo="http://a9.com/-/opensearch/extensions/geo/1.0/" xmlns:time="http://a9.com/-/opensearch/extensions/time/1.0/">
     *      <ShortName>OpenSearch search</ShortName>
     *      <Description>My OpenSearch search interface</Description>
     *      <Tags>opensearch</Tags>
     *      <Contact>admin@myserver.org</Contact>
     *      <Url type="application/atom+xml" template="http://myserver.org/Controller_name/?q={searchTerms}&bbox={geo:box?}&format=atom&startDate={time:start?}&completionDate={time:end?}&modified={time:start?}&platform={take5:platform?}&instrument={take5:instrument?}&product={take5:product?}&maxRecords={count?}&index={startIndex?}"/>
     *      <LongName>My OpenSearch search interface</LongName>
     *      <Query role="example" searchTerms="observatory"/>
     *      <Attribution>mapshup.info</Attribution>
     *      <Language>fr</Language>
     * </OpenSearchDescription>
     */
    public function toXML() {
        
        $lang = $this->context->dictionary->language;
        
        /*
         * Client Id (CEOS Opensearch Best Practice document)
         */
        $clientId = isset($this->context->query['clientId']) ? 'clientId=' . urlencode($this->context->query['clientId']) . '&' : '';
                
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        /*
         * OpsenSearchDescription - Start element
         */
        $xml->startElement('OpenSearchDescription');
        $xml->writeAttribute('xmlns', 'http://a9.com/-/spec/opensearch/1.1/');
        $xml->writeAttribute('xmlns:os', 'http://a9.com/-/spec/opensearch/1.1/');
        $xml->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $xml->writeAttribute('xmlns:time', 'http://a9.com/-/opensearch/extensions/time/1.0/');
        $xml->writeAttribute('xmlns:geo', 'http://a9.com/-/opensearch/extensions/geo/1.0/');
        $xml->writeAttribute('xmlns:eo', 'http://a9.com/-/opensearch/extensions/eo/1.0/');
        $xml->writeAttribute('xmlns:parameters', 'http://a9.com/-/spec/opensearch/extensions/parameters/1.0/');
        $xml->writeAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $xml->writeAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $xml->writeAttribute('xmlns:resto', 'http://mapshup.info/-/resto/2.0/');
        
        /*
         * Read from config file
         */
        $xml->writeElement('ShortName', $this->osDescription[$lang]['ShortName']);
        $xml->writeElement('Description', $this->osDescription[$lang]['Description']);
        $xml->writeElement('Tags', $this->osDescription[$lang]['Tags']);
        $xml->writeElement('Contact', $this->osDescription[$lang]['Contact']);

        /*
         * Generate search urls
         */
        $parameters = array('minimum', 'maximum', 'minExclusive', 'maxExclusive', 'minInclusive', 'maxInclusive', 'pattern', 'title');
        foreach (RestoUtil::$contentTypes as $format => $mimeType) {

            $url = RestoUtil::restoUrl($this->context->baseUrl, 'api/collections/' . $this->name . '/search', $format) . '?' . $clientId;
            
            /*
             * Url templates
             */
            $xml->startElement('Url');
            $xml->writeAttribute('type', $mimeType);
            $xml->writeAttribute('rel', 'results');
            $count = 0;
            foreach ($this->model->searchFilters as $filterName => $filter) {
                if (isset($filter)) {
                    $optional = isset($filter['minimum']) && $filter['minimum'] === 1 ? '' : '?';
                    $url .= ($count > 0 ? '&' : '') . $filter['osKey'] . '={' . $filterName . $optional . '}';
                    $count++;
                }
            }
            $xml->writeAttribute('template', $url);
            
            /*
             * Parameter extension
             */
            foreach ($this->model->searchFilters as $filterName => $filter) {
                if (isset($filter)) {
                    $xml->startElement('parameters:Parameter');
                    $xml->writeAttribute('name', $filter['osKey']);
                    $xml->writeAttribute('value', '{' . $filterName . '}');
                    for ($i = count($parameters); $i--;) {
                        if (isset($filter[$parameters[$i]])) {
                            $xml->writeAttribute($parameters[$i], $filter[$parameters[$i]]);
                        }
                    }
                    
                    /*
                     * Options - two cases
                     * 1. predefined value/label
                     * 2. retrieve from database
                     */
                    if (isset($filter['options'])) {
                        if (is_array($filter['options'])) {
                            for ($i = count($filter['options']); $i--;) {
                                $xml->startElement('parameters:Options');
                                $xml->writeAttribute('value', $filter['options'][$i]['value']);
                                if (isset($filter['options'][$i]['label'])) {
                                    $xml->writeAttribute('label', $filter['options'][$i]['label']);
                                }
                                $xml->endElement();
                            }
                        }
                        else if ($filter['options'] === 'auto') {
                            if (isset($filter['key']) && isset($this->statistics[$filter['key']])) {
                                foreach (array_keys($this->statistics[$filter['key']]) as $key) {
                                    $xml->startElement('parameters:Options');
                                    $xml->writeAttribute('value', $key);
                                    $xml->endElement();
                                }
                            }
                        }
                    }
                    $xml->endElement(); // parameters:Parameter
                }
            }
            
            /*
             * Parameter extension for clientId
             */
            if ($clientId !== '') {
                $xml->startElement('parameters:Parameter');
                $xml->writeAttribute('name', 'clientId');
                $xml->writeAttribute('minimum', '1');
                $xml->endElement(); // parameters:Parameter
            }

            $xml->endElement(); // Url
        }
        // URLS
        $xml->writeElement('LongName', $this->osDescription[$lang]['LongName']);
        $xml->startElement('Query');
        $xml->writeAttribute('role', 'example');
        $xml->writeAttribute('searchTerms', $this->osDescription[$lang]['Query']);
        $xml->endElement(); // Query
        $xml->writeElement('Developper', $this->osDescription[$lang]['Developper']);
        $xml->writeElement('Attribution', $this->osDescription[$lang]['Attribution']);
        $xml->writeElement('SyndicationRight', 'open');
        $xml->writeElement('AdultContent', 'false');
        for ($i = 0, $l = count($this->context->config['languages']); $i < $l; $i++) {
            $xml->writeElement('Language', $this->context->config['languages'][$i]);
        }
        $xml->writeElement('OutputEncoding', 'UTF-8');
        $xml->writeElement('InputEncoding', 'UTF-8');

        /*
         * OpsenSearchDescription - end element
         */
        $xml->endElement();

        return $xml->outputMemory(true);
        
    }
    
}