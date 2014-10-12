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
 * RESTo model for ALOS satellites
 */
class RestoModel_alos extends RestoModel {
    /*
     * Properties mapping between RESTo model and input
     * GeoJSON Feature file
     */
    public $inputPropertiesMapping = array(
        'productId' => 'productIdentifier'
    );
    
    public $extendedProperties = array(
        'title' => null,
        'description' => null,
        'orbitNumber' => null,
        'sensorMode' => null,
        'meshCode' => array(
            'name' => 'meshcode',
            'type' => 'INTEGER'
        ),
        'ellipsoid' => array(
            'name' => 'ellipsoid',
            'type' => 'VARCHAR(10)'
        ),
        'projection' => array(
            'name' => 'projection',
            'type' => 'VARCHAR(10)'
        ),
        'utmZoneNum' => array(
            'name' => 'utmzonenum',
            'type' => 'VARCHAR(10)'
        ),
        'resamplingMethod' => array(
            'name' => 'resamplingmethod',
            'type' => 'VARCHAR(10)'
        ),
        'imageOrientation1' => array(
            'name' => 'imageorientation1',
            'type' => 'VARCHAR(20)'
        ),
        'imageOrientation2' => array(
            'name' => 'imageorientation2',
            'type' => 'VARCHAR(20)'
        ),
        'noOfBands' => array(
            'name' => 'noofbands',
            'type' => 'INTEGER'
        ),
        'dataFormat' => array(
            'name' => 'dataformat',
            'type' => 'VARCHAR(50)'
        ),
        'orbitDirection' => array(
            'name' => 'orbitdirection',
            'type' => 'VARCHAR(50)'
        )
    );

    /**
     * Constructor
     * 
     * @param RestoContext $context : Resto context
     */
    public function __construct($context) {
        parent::__construct($context);
    }

}
