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
 * resto OpenSearch Document Description class
 */
class RestoOSDD extends RestoXML {
    
    /*
     * Reference to collection object
     */
    private $collection;
    
    /*
     * Client Id (CEOS Opensearch Best Practice document)
     */
    private $clientId;
    
    /*
     * Output contentTypes
     */
    private $contentTypes = array('atom', 'json');
    
    /*
     * Template extension parameters
     */
    private $extensionParams = array(
        'minimum',
        'maximum',
        'minExclusive',
        'maxExclusive',
        'minInclusive',
        'maxInclusive',
        'pattern',
        'title'
    );
    
    /**
     * Constructor
     * 
     * @param RestoCollection $collection
     */
    public function __construct($collection) {
        parent::__construct();
        $this->collection = $collection;
        $this->clientId = isset($this->collection->context->query['clientId']) ? 'clientId=' . urlencode($this->collection->context->query['clientId']) . '&' : '';
        $this->setOSDD();
    }
    
    /**
     * Set OpenSearch Description Document
     */
    private function setOSDD() {
         
        /*
         * Start OpsenSearchDescription
         */
        $this->startOSDD();
        
        /*
         * Start elements
         */
        $this->setStartingElements();
        
        /*
         * Generate <Url> elements
         */
        $this->setUrls();
        
        /*
         * Generate informations elements
         */
        $this->setEndingElements();
        
        /*
         * OpsenSearchDescription - end element
         */
        $this->endElement();
        
    }
    
    /**
     * Start XML OpenSearchDescription element
     */
    private function startOSDD() {
        $this->startElement('OpenSearchDescription');
        $this->writeAttributes(array(
            'xml:lang' => $this->collection->context->dictionary->language,
            'xmlns' => 'http://a9.com/-/spec/opensearch/1.1/',
            'xmlns:atom' => 'http://www.w3.org/2005/Atom',
            'xmlns:time' => 'http://a9.com/-/opensearch/extensions/time/1.0/',
            'xmlns:geo' => 'http://a9.com/-/opensearch/extensions/geo/1.0/',
            'xmlns:eo' => 'http://a9.com/-/opensearch/extensions/eo/1.0/',
            'xmlns:parameters' => 'http://a9.com/-/spec/opensearch/extensions/parameters/1.0/',
            'xmlns:dc' => 'http://purl.org/dc/elements/1.1/',
            'xmlns:rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            'xmlns:resto' => 'http://mapshup.info/-/resto/2.0/'
        ));
    }
    
    /**
     * Set OSDD starting elements
     */
    private function setStartingElements() {
        $this->writeElements(array(
            'ShortName' => $this->collection->osDescription[$this->collection->context->dictionary->language]['ShortName'],
            'Description' => $this->collection->osDescription[$this->collection->context->dictionary->language]['Description'],
            'Tags' => $this->collection->osDescription[$this->collection->context->dictionary->language]['Tags'],
            'Contact' => $this->collection->osDescription[$this->collection->context->dictionary->language]['Contact']
        ));
    }
    
    /**
     * Set OSDD ending elements
     */
    private function setEndingElements() {
        $this->writeElement('LongName', $this->collection->osDescription[$this->collection->context->dictionary->language]['LongName']);
        $this->startElement('Query');
        $this->writeAttributes(array(
            'role' => 'example',
            'searchTerms' => $this->collection->osDescription[$this->collection->context->dictionary->language]['Query']
        ));
        $this->endElement('Query');
        $this->writeElements(array(
            'Developper' => $this->collection->osDescription[$this->collection->context->dictionary->language]['Developper'],
            'Attribution' => $this->collection->osDescription[$this->collection->context->dictionary->language]['Attribution'],
            'SyndicationRight' => 'open',
            'AdultContent' => 'false'
        ));
        for ($i = 0, $l = count($this->collection->context->languages); $i < $l; $i++) {
            $this->writeElement('Language', $this->collection->context->languages[$i]);
        }
        $this->writeElements(array(
            'OutputEncoding' => 'UTF-8',
            'InputEncoding' => 'UTF-8'
        ));
    }
    
    /**
     * Generate OSDD <Url> elements
     */
    private function setUrls() {
        
        foreach (array_values($this->contentTypes) as $format) {
            
            /*
             * <Url> element
             */
            $this->startElement('Url');
            $this->writeAttributes(array(
                'type' => RestoUtil::$contentTypes[$format],
                'rel' => 'results',
                'template' => $this->getUrlTemplate($format)
            ));
            
            /*
             * Extension parameters
             */
            $this->setParameters();
            
            /*
             * End <Url> element
             */
            $this->endElement();
        }
        
    }
    
    /**
     * Return template url for format
     * 
     * @param string $format
     * @return string
     */
    private function getUrlTemplate($format) {
        $url = RestoUtil::restoUrl($this->collection->context->baseUrl, '/api/collections/' . $this->collection->name . '/search', $format) . '?' . $this->clientId;
        $count = 0;
        foreach ($this->collection->model->searchFilters as $filterName => $filter) {
            if (isset($filter)) {
                $optional = isset($filter['minimum']) && $filter['minimum'] === 1 ? '' : '?';
                $url .= ($count > 0 ? '&' : '') . $filter['osKey'] . '={' . $filterName . $optional . '}';
                $count++;
            }
        }
        return $url;
    }
    
    /**
     * Set <parameters:Parameter> elements
     */
    private function setParameters() {
       
        foreach ($this->collection->model->searchFilters as $filterName => $filter) {
            if (isset($filter)) {
                $this->startElement('parameters:Parameter');
                $this->writeAttributes(array(
                    'name' => $filter['osKey'],
                    'value' => '{' . $filterName . '}'
                ));
                for ($i = count($this->extensionParams); $i--;) {
                    if (isset($filter[$this->extensionParams[$i]])) {
                        $this->writeAttribute($this->extensionParams[$i], $filter[$this->extensionParams[$i]]);
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
                            $this->startElement('parameters:Options');
                            $this->writeAttribute('value', $filter['options'][$i]['value']);
                            if (isset($filter['options'][$i]['label'])) {
                                $this->writeAttribute('label', $filter['options'][$i]['label']);
                            }
                            $this->endElement();
                        }
                    }
                    else if ($filter['options'] === 'auto') {
                        if (isset($filter['key']) && isset($this->collection->statistics[$filter['key']])) {
                            foreach (array_keys($this->collection->statistics[$filter['key']]) as $key) {
                                $this->startElement('parameters:Options');
                                $this->writeAttribute('value', $key);
                                $this->endElement();
                            }
                        }
                    }
                }
                $this->endElement(); // parameters:Parameter
            }
        }

        /*
         * Parameter extension for clientId
         */
        if ($this->clientId !== '') {
            $this->startElement('parameters:Parameter');
            $this->writeAttributes(array(
                'name' => 'clientId',
                'minimum', '1'
            ));
            $this->endElement(); // parameters:Parameter
        }

    }
}
