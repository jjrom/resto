<?php

/*
 * RESTo
 * 
 * REST OpenSearch - Very Lightweigt PHP REST Library for OpenSearch EO
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
 * RESTo model for Airbus Defence and Space satellites
 */
class RestoModel_ads extends RestoModel {
    
    public $extendedProperties = array(
        'title' => null,
        'description' => null,
        'incidenceAngle' => array(
            'name' => 'incidenceangle',
            'type' => 'NUMERIC'
        ),
        'sunAzimuth' => array(
            'name' => 'sunazimuth',
            'type' => 'NUMERIC'
        ),
        'orientationAngle' => array(
            'name' => 'orientationangle',
            'type' => 'NUMERIC'
        ),
        'acrossTrackIncidenceAngle' => array(
            'name' => 'acrosstrackincidenceangle',
            'type' => 'NUMERIC'
        ),
        'alongTrackIncidenceAngle' => array(
            'name' => 'alongtrackincidenceangle',
            'type' => 'NUMERIC'
        ),
        'archivingStation' => array(
            'name' => 'archivingstation',
            'type' => 'TEXT'
        ),
        'receivingStation' => array(
            'name' => 'receivingstation',
            'type' => 'TEXT'
        ),
        'pitch' => array(
            'name' => 'pitch',
            'type' => 'NUMERIC'
        ),
        'roll' => array(
            'name' => 'roll',
            'type' => 'NUMERIC'
        ),
        'qualityQuotes' => array(
            'name' => 'qualityquotes',
            'type' => 'TEXT'
        ),
        'sunElevation' => array(
            'name' => 'sunelevation',
            'type' => 'NUMERIC'
        )
    );

    /**
     * Constructor
     * 
     * @param RestoContext $context : Resto context
     * @param RestoContext $user : Resto user
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }

}
