<?php
/*
 * Copyright 2024 Jérôme Gasperi
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

class STACUtil
{

    /*
     * Reserved catalog paths
     * These paths are reserved for internal use
     */
    const RESERVED_CATALOG_PATHS = array(
        'collections',
        'users',
        'projects'
    );

    public static $extensions = array(
        'acuracy' => array(
            'id' => ' https://stac-extensions.github.io/accuracy/v1.0.0-beta.1/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'aerial-photo' => array(
            'id' => 'https://stac.linz.govt.nz/_STAC_VERSION_/aerial-photo/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'alternate' => array(
            'id' => 'https://stac-extensions.github.io/alternate-assets/v1.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'anon' => array(
            'id' => 'https://stac-extensions.github.io/anonymized-location/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'archive' => array(
            'id' => 'https://stac-extensions.github.io/archive/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            ),
        ),
        'altm' => array(
            'id' => 'https://stac-extensions.github.io/altimetry/v0.1.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'auth' => array(
            'id' => 'https://stac-extensions.github.io/authentication/v1.1.0/schema.json',
            'scope' => array(
                'Item',
                'Catalog',
                'Collection',
                'Item',
                'Asset',
                'links'
            )
        ),
        'camera' => array(
            'id' => 'https://stac.linz.govt.nz/_STAC_VERSION_/camera/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'ceosard' => array(
            'id' => 'https://stac-extensions.github.io/ceos-ard/v0.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'cf' => array(
            'id' => 'https://stac-extensions.github.io/cf/v0.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'classification' => array(
            'id' => 'https://stac-extensions.github.io/classification/v2.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'composite' => array(
            'id' => 'https://stac-extensions.github.io/composite/v1.0.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'cmip6' => array(
            'id' => 'https://stac-extensions.github.io/cmip6/v1.0.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'cube' => array(
            'id' => 'https://stac-extensions.github.io/datacube/v2.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'disaster' => array(
            'id' => 'https://terradue.github.io/stac-extensions-disaster/v1.1.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'eq' => array(
            'id' => 'https://stac-extensions.github.io/earthquake/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),

        'eo' => array(
            'id' => 'https://stac-extensions.github.io/eo/v2.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'example' => array(
            'id' => 'https://stac-extensions.github.io/example-links/v0.0.1/schema.json',
            'scope' => array(
                'Item',
                'Catalog',
                'Collection'
            )
        ),
        'file' => array(
            'id' => 'https://stac-extensions.github.io/file/v2.1.0/schema.json',
            'scope' => array(
                'Item',
                'Catalog',
                'Collection'
            )
        ),
        'film' => array(
            'id' => 'https://stac.linz.govt.nz/_STAC_VERSION_/film/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'forecast' => array(
            'id' => 'https://stac-extensions.github.io/forecast/v0.1.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'fws_nwi' => array(
            'id' => 'https://stac-extensions.github.io/usfws-nwi/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'goes' => array(
            'id' => 'https://stac-extensions.github.io/goes/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'grid' => array(
            'id' => 'https://stac-extensions.github.io/grid/v1.1.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'hsi' => array(
            'id' => 'https://stac-extensions.github.io/hsi/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'insar' => array(
            'id' => 'https://stac-extensions.github.io/insar/v1.0.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'label' => array(
            'id' => 'https://stac-extensions.github.io/label/v1.0.1/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'landsat' => array(
            'id' => 'https://stac-extensions.github.io/landsat/v2.0.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'ml-aoi' => array(
            'id' => 'https://stac-extensions.github.io/ml-aoi/v0.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection',
                'Asset',
                'Links'
            )
        ),
        'ml-model' => array(
            'id' => 'https://stac-extensions.github.io/ml-model/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'mlm' => array(
            'id' => 'https://crim-ca.github.io/mlm-extension/v1.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection',
                'Asset',
                'Links'
            )
        ),
        'mgrs' => array(
            'id' => 'https://stac-extensions.github.io/mgrs/v1.0.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'noaa_mrms_qpe' => array(
            'id' => 'https://stac-extensions.github.io/noaa-mrms-qpe/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'order' => array(
            'id' => 'https://stac-extensions.github.io/order/v1.1.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'osc' => array(
            'id' => 'https://stac-extensions.github.io/osc/v1.0.0-rc.3/schema.json',
            'scope' => array(
                'Item',
                'Catalog',
                'Collection'
            )
        ),
        'pers' => array(
            'id' => 'https://stac-extensions.github.io/perspective-imagery/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'pc' => array(
            'id' => 'https://stac-extensions.github.io/pointcloud/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'processing' => array(
            'id' => 'https://stac-extensions.github.io/processing/v1.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'product' => array(
            'id' => 'https://stac-extensions.github.io/product/v0.1.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'proj' => array(
            'id' => 'https://stac-extensions.github.io/projection/v2.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'quality' => array(
            'id' => 'https://stac.linz.govt.nz/_STAC_VERSION_/quality/schema.json',
            'scope' => array(
                'Collection'
            )
        ),
        'raster' => array(
            'id' => 'https://stac-extensions.github.io/raster/v2.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'render' => array(
            'id' => 'https://stac-extensions.github.io/render/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'scan' => array(
            'id' => 'https://stac.linz.govt.nz/_STAC_VERSION_/scanning/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'sci' => array(
            'id' => 'https://stac-extensions.github.io/scientific/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'sar' => array(
            'id' => 'https://stac-extensions.github.io/sar/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'sat' => array(
            'id' => 'https://stac-extensions.github.io/sat/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        's1' => array(
            'id' => 'https://stac-extensions.github.io/sentinel-1/v0.2.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        's2' => array(
            'id' => 'https://stac-extensions.github.io/sentinel-2/v1.0.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        's5p' => array(
            'id' => 'https://stac-extensions.github.io/sentinel-5p/v0.2.0/schema.json',
            'scope' => array(
                'Item'
            )
        ),
        'ssys' => array(
            'id' => 'https://stac-extensions.github.io/ssys/v1.1.0/schema.json',
            'scope' => array(
                'Item',
                'Catalog',
                'Collection'
            )
        ),
        'stats' => array(
            'id' => 'https://stac-extensions.github.io/stats/v0.2.0/schema.json',
            'scope' => array(
                'Catalog',
                'Collection'
            )
        ),
        'stereo-imagery' => array(
            'id' => 'https://stac-extensions.github.io/stereo-imagery/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection',
                'Catalog'
            )
        ),
        'storage' => array(
            'id' => 'https://stac-extensions.github.io/storage/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'table' => array(
            'id' => 'https://stac-extensions.github.io/table/v1.2.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'tiles' => array(
            'id' => 'https://stac-extensions.github.io/tiled-assets/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Catalog',
                'Collection'
            )
        ),
        'tdml' => array(
            'id' => 'https://stac-extensions.github.io/trainingdml-ai/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'video' => array(
            'id' => 'https://stac-extensions.github.io/video/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'view' => array(
            'id' => 'https://stac-extensions.github.io/view/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'vrt' => array(
            'id' => 'https://stac-extensions.github.io/virtual-assets/v1.0.0/schema.json',
            'scope' => array(
                'Item',
                'Collection'
            )
        ),
        'xarray' => array(
            'id' => 'https://stac-extensions.github.io/xarray-assets/v1.0.0/schema.json',
            'scope' => array(
                'Asset'
            )
        )
    );

}