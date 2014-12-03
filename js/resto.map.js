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
(function (window) {

    /**
     * map view
     */
    window.Resto.Map = {
        
        /*
         * True if map is already initialized
         */
        isLoaded: false,
        
        /*
         * Result layer
         */
        layer: null,
        
        /*
         * Feature overlay
         */
        featureOverlay: null,
        
        /*
         * GeoJSON formatter
         */
        geoJSONFormatter: null,
            
        /**
         * Initialize map with input features array
         */
        init: function (features) {

            var self = this;
            
            if (!window.ol || self.isLoaded) {
                return false;
            }
            
            self.isLoaded = true;
            
            /*
             * Initialize OpenStreetMap background layer
             */
            var bgLayer = new ol.layer.Tile({
                source: new ol.source.OSM()
            });
            
            /*
             * Initialize GeoJSON formatter
             */
            self.geoJSONFormatter = new ol.format.GeoJSON({
                defaultDataProjection: "EPSG:4326"
            });
                    
            /*
             * Initialize result vector layer
             */
            self.layer = new ol.layer.Vector({
                source: new ol.source.Vector(),
                style: new ol.style.Style({
                    fill: new ol.style.Fill({
                        color: 'rgba(255, 128, 128, 0.2)'
                    }),
                    stroke: new ol.style.Stroke({
                        color: '#000',
                        width: 1
                    })
                })
            });
            
            /*
             * Initialize map
             */
            self.map = new ol.Map({
                controls: ol.control.defaults(),
                layers:[bgLayer, self.layer],
                renderer: 'canvas',
                target: 'map',
                view: new ol.View({
                    center: [0, 0],
                    zoom: 2
                })
            });
            
            /*
             * Initialize feature overlay for selected feature
             */
            self.featureOverlay = new ol.FeatureOverlay({
                map: self.map,
                style: function (feature, resolution) {
                    return [new ol.style.Style({
                            fill: new ol.style.Fill({
                                color: 'rgba(255, 128, 128, 0.4)'
                            }),
                            stroke: new ol.style.Stroke({
                                color: '#ff0',
                                width: 3
                            }),
                            text: new ol.style.Text({
                                font: '12px Roboto,sans-serif',
                                text: resolution < 1000 ? feature.getId() : '',
                                fill: new ol.style.Fill({
                                    color: '#000'
                                })
                            })
                        })];
                }
            });
            
            /*
             * Map event - mousemove
             * Hilite hovered feature on mousemove
             */
            $(self.map.getViewport()).on('mousemove', function (evt) {
                var feature = self.map.forEachFeatureAtPixel(self.map.getEventPixel(evt.originalEvent), function (feature, layer) {
                    return feature;
                });
                if (feature !== self.highlighted) {
                    if (self.highlighted) {
                        self.featureOverlay.removeFeature(self.highlighted);
                    }
                    if (feature) {
                        self.featureOverlay.addFeature(feature);
                    }
                    self.highlighted = feature;
                }
            });

            /*
             * Map event - click
             * Display menu
             */
            self.map.on('click', function (evt) {
                self.map.forEachFeatureAtPixel(self.map.getEventPixel(evt.originalEvent), function (feature, layer) {
                    if (feature) {
                        Resto.selectFeature(feature.getId(), true);
                    }
                });
            });

            /*
             * Detect window resize to resize map
             */
            $(window).bind('resize', function () {
                self.updateSize();
            });

            /*
             * Add input features to map
             */
            self.layer.getSource().addFeatures(self.geoJSONFormatter.readFeatures(JSON.stringify({
                'type': 'FeatureCollection',
                'features': features
            }), {
                featureProjection: 'EPSG:3857'
            }));
            
            /*
             * TODO
             * SELECT : window.Resto.selectFeature(f.fid, true);
             * UNSELECT : $('.resto-feature').each(function () {
                            $(this).removeClass('selected');
                        });
             */
            
            /*
             * Initialize map size and repaint
             */
            self.updateSize();
        },
        
        /**
         * Update map size
         */
        updateSize: function () {
            $('#map').height($(window).height() - $('.resto-search-panel').outerHeight() - $('.resto-search-panel').position().top - $('.left-off-canvas-menu').offset().top);
            if (this.isLoaded && this.map) {
                this.map.updateSize();
            }
        },
        
        /**
         * Add a WMS layer to map
         * 
         * @param {string} url : WMS GetMap url
         */
        addWMSLayer: function (url) {

            if (!this.isLoaded) {
                return null;
            }
            
            //Resto.Map.addWMSLayer('http://spirit.cnes.fr/cgi-bin/mapserv?map=/mount/landsat/wms/map.map&file=LANDSAT8_OLITIRS_XS_20141031_N2A_France-MetropoleD0003H0008&service=WMS&LAYERS=landsat&FORMAT=image%2Fpng&TRANSITIONEFFECT=resize&TRANSPARENT=true&VERSION=1.1.1&REQUEST=GetMap&STYLES=&SRS=EPSG%3A3857&BBOX=-278120.21901876,6153474.6465768,-101753.50345638,6330340.8077245&WIDTH=256&HEIGHT=256');
            var parsedWMS = Resto.Util.parseWMSGetMap(url);
            var wms = new ol.layer.Tile({
                source: new ol.source.TileWMS({
                    attributions: [new ol.Attribution({
                            html: 'Test'
                        })],
                    params: {
                        'LAYERS': parsedWMS.layers,
                        'FORMAT': parsedWMS.format
                    },
                    url: parsedWMS.url
                })
            });
            
            this.map.addLayer(wms);
            
            return wms;

        },
        
        /**
         * Update features layer
         * 
         * @param {Object} json - Feature array
         * @param {Object} options :
         *              
         *              {
         *                  centerMap : //true to center the map
         *                  append: // true to add features to existing features
         *              } 
         */
        updateLayer: function (features, options) {
            
            if (!this.isLoaded) {
                return null;
            }
            
            var centerMap = options.hasOwnProperty('centerMap') ? options.centerMap : false;

            /*
             * Erase previous features unless "append" is set to true
             */
            if (!options.hasOwnProperty('append') || options.append === false) {
                this.layer.getSource().clear();
            }
            
            /*
             * Add features to result layer
             */
            this.layer.getSource().addFeatures(this.geoJSONFormatter.readFeatures(JSON.stringify({
                'type': 'FeatureCollection',
                'features': features
            }), {
                featureProjection: 'EPSG:3857'
            }));
        
            this.updateBBOX();
        },
        
        /**
         * Add map bounding box in EPSG:4326 to all element with a 'resto-updatebbox' class
         */
        updateBBOX: function () {
            
            if (this.isVisible()) {
                var bbox = this.getExtent().join(',');
                $('.resto-updatebbox').each(function () {
                    $(this).attr('href', Resto.Util.updateUrl($(this).attr('href'), {
                        box: bbox
                    }));
                });
            }
            else {
                $('.resto-updatebbox').each(function () {
                    $(this).attr('href', Resto.Util.updateUrl($(this).attr('href'), {
                        box: null
                    }));
                });
            }
        },
        
        /**
         * Check that map panel is visible
         * 
         * @returns {boolean}
         */
        isVisible: function () {
            if (!this.isLoaded || !$('#map').is(':visible')) {
                return false;
            }
            return true;
        },
        
        /**
         * Return current map extent in EPSG:4326 projection
         * 
         * @returns {array}
         */
        getExtent: function () {
            var extent = [-180, -90, 180, 90];
            if (!this.isLoaded) {
                try {
                    extent = ol.extent.applyTransform(this.map.getView().calculateExtent(this.map.getSize()), ol.proj.getTransform('EPSG:3857', 'EPSG:4326'));
                } catch (e) {}
            }
            return extent;
        },
        
        /**
         * Hilite feature
         * 
         * @param {string} fid
         * @param {boolean} zoomOn
         */
        hilite: function (fid, zoomOn) {
            
            if (!this.isLoaded) {
                return false;
            }
            
            var f = this.layer.getSource().getFeatureById(fid);
            if (f) {
                if (zoomOn) {
                    this.map.getView().fitExtent(f.getGeometry().getExtent(), this.map.getSize());
                }
                //window.M.Map.featureInfo.hilite(f);
            }
        }

    };

})(window);
