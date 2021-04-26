<?php
/*
 * Copyright 2018 Jérôme Gasperi
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

/**
 * resto OpenSearch Document Description class
 *
 * <OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:geo="http://a9.com/-/opensearch/extensions/geo/1.0/" xmlns:time="http://a9.com/-/opensearch/extensions/time/1.0/">
 *      <ShortName>OpenSearch search</ShortName>
 *      <Description>My OpenSearch search interface</Description>
 *      <Url type="application/atom+xml" template="http://myserver.org/Controller_name/?q={searchTerms}&bbox={geo:box?}&format=atom&start={time:start?}&end={time:end?}&modified={time:start?}&platform={take5:platform?}&instrument={take5:instrument?}&product={take5:product?}&limit={count?}&index={startIndex?}"/>
 *      <Contact>admin@myserver.org</Contact>
 *      <Tags>opensearch</Tags>
 *      <LongName>My OpenSearch search interface</LongName>
 *      <Query role="example" searchTerms="observatory"/>
 *      <Developer>Jérôme Gasperi</Developer>
 *      <Attribution>mapshup.com</Attribution>
 *      <Language>fr</Language>
 * </OpenSearchDescription>
 *
 */
class OSDD extends RestoXML
{

    /*
     * Reference to context
     */
    private $context;

    /*
     * Reference to collection object (null if no collection)
     */
    private $collection;

    /*
     * Client Id (CEOS Opensearch Best Practice document)
     */
    private $clientId;

    /*
     * OpenSearch description
     */
    private $osDescription;

    /*
     * Collection statistics
     */
    private $statistics = array();

    /*
     * Output contentTypes
     */
    private $contentTypes = array('html', 'atom', 'json');

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
     * @param RestoContext $context
     * @param RestoModel $model
     * @param Array $statistics
     * @param RestoCollection $collection
     */
    public function __construct($context, $model, $statistics, $collection)
    {
        parent::__construct();
        $this->context = $context;
        $this->model = $model;
        $this->statistics = $statistics;
        $this->collection = $collection;
        $this->clientId = isset($this->context->query['clientId']) ? 'clientId=' . rawurlencode($this->context->query['clientId']) . '&' : '';
        $this->osDescription = isset($this->collection) ? $this->collection->osDescription[$this->context->lang] ?? $this->collection->osDescription['en'] : $this->context->osDescription;
        $this->setOSDD();
    }

    /**
     * Set OpenSearch Description Document
     */
    private function setOSDD()
    {
        
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
    private function startOSDD()
    {
        $this->startElement('OpenSearchDescription');
        $this->writeAttributes(array(
            'xml:lang' => $this->context->lang,
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
    private function setStartingElements()
    {
        $this->writeElements(array(
            'ShortName' => $this->osDescription['ShortName'],
            'Description' => $this->osDescription['Description']
        ));
    }

    /**
     * Set OSDD ending elements
     */
    private function setEndingElements()
    {
        $this->writeElements(array(
            'Contact' => $this->osDescription['Contact'],
            'Tags' => implode(' ', array('CEOS-OS-BP-V1.0', $this->osDescription['Tags'])),
            'LongName' => $this->osDescription['LongName']
        ));
        $this->startElement('Query');
        $this->writeAttributes(array(
            'role' => 'example',
            'searchTerms' => $this->osDescription['Query']
        ));
        $this->endElement('Query');
        $this->writeElements(array(
            'Developer' => $this->osDescription['Developer'],
            'Attribution' => $this->osDescription['Attribution'],
            'SyndicationRight' => 'open',
            'AdultContent' => 'false'
        ));
        for ($i = 0, $l = count($this->context->core['languages']); $i < $l; $i++) {
            $this->writeElement('Language', $this->context->core['languages'][$i]);
        }
        $this->writeElements(array(
            'InputEncoding' => 'UTF-8',
            'OutputEncoding' => 'UTF-8'
        ));
    }

    /**
     * Generate OSDD <Url> elements
     */
    private function setUrls()
    {
        foreach (array_values($this->contentTypes) as $format) {

            /*
             * Special case for HTML output
             */
            if ($format === 'html' && empty($this->context->core['htmlSearchEndpoint'])) {
                continue;
            }

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
            $this->setParameters($format);

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
    private function getUrlTemplate($format)
    {

        /*
         * HTML output is based on htmlSearchEndpoint
         */
        if ($format === 'html') {
            return $this->context->core['htmlSearchEndpoint'] . '?' . $this->model->searchFilters['searchTerms']['osKey'] .'={searchTerms?}';
        }
       
        $url = RestoUtil::restoUrl($this->context->core['baseUrl'], (isset($this->collection) ? '/collections/' . $this->collection->id . '/items' : '/search'), $format) . '?' . $this->clientId;
        
        $count = 0;
        foreach ($this->model->searchFilters as $filterName => $filter) {
            if (isset($filter)  && !isset($filter['hidden'])) {
                $optional = isset($filter['minimum']) && $filter['minimum'] === 1 ? '' : '?';
                $url .= ($count > 0 ? '&' : '') . $filter['osKey'] . '={' . $filterName . $optional . '}';
                $count++;
            }
        }
        return $url;
    }

    /**
     * Set <parameters:Parameter> elements
     *
     * @param string $format
     */
    private function setParameters($format)
    {
        foreach ($this->model->searchFilters as $filterName => $filter) {
            if (isset($filter) && $filterName != 'searchTerms' && !isset($filter['hidden'])) {
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
                            $this->startElement('parameters:Option');
                            $this->writeAttribute('value', $filter['options'][$i]['value']);
                            if (isset($filter['options'][$i]['label'])) {
                                $this->writeAttribute('label', $filter['options'][$i]['label']);
                            }
                            $this->endElement();
                        }
                    } elseif ($filter['options'] === 'auto') {
                        if (isset($filter['osKey']) && isset($this->statistics[$filter['osKey']])) {

                            /*
                             * [STAC] Summaries format is "oneOf" if several options
                             */
                            if (isset($this->statistics[$filter['osKey']]['const'])) {
                                $this->startElement('parameters:Option');
                                $this->writeAttribute('value', $this->statistics[$filter['osKey']]['const']);
                                $this->endElement();
                            }
                            else {
                                for ($i = count($this->statistics[$filter['osKey']]['oneOf']); $i--;) {
                                    $this->startElement('parameters:Option');
                                    $this->writeAttribute('value', $this->statistics[$filter['osKey']]['oneOf'][$i]['const']);
                                    $this->endElement();
                                }
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
