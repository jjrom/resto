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
(function(window) {
    
    /**
     * map view
     */
    window.Resto.Map = {
        
        /*
         * True when map is loaded
         */
        isLoaded: false,
        
        /*
         * Layer
         */
        layer: null,
        
        /**
         * Initialize map with input GeoJSON FeatureCollection
         */
        init: function(data) {
            
            var timer, self = this;
            
            if (self.isLoaded) {
                return false;
            }
            
            /*
             * mapshup is defined
             */
            if (window.M) {
                
                self.isLoaded = true;
                $('#mapshup').height($(window).height() - $('.resto-search-panel').outerHeight() - $('.resto-search-panel').position().top);
                window.M.load();
                
                /*
                 * mapshup bug ?
                 * Force map size refresh when user scroll RESTo page
                 */
                $('#resto-container').bind('scroll', function() {
                    clearTimeout(timer);
                    timer = setTimeout(function() {
                        window.M.events.trigger('resizeend');
                    }, 150);
                });
                
                /*
                 * Update bbox parameter in href attributes of all element with 'resto-updatebbox' class
                 */
                var uFct = setInterval(function() {
                    if (window.M.Map.map && window.M.isLoaded) {
                        window.M.Map.events.register("moveend", self, function(map, scope) {
                            scope.updateBBOX();
                        });
                        self.updateBBOX();
                        clearInterval(uFct);
                    }
                }, 500);
                
            }
            
            /* 
             * Note : setInterval function is needed to ensure that mapshup map
             * is loaded before sending the GeoJSON feed
             */
            if (window.M && data) {
                var fct = setInterval(function () {
                    if (window.M.Map.map && window.M.isLoaded) {

                        self.initLayer(data, true);

                        /*
                         * Display full size WMS
                         */
                        if (window.Resto.issuer === 'getResource') {
                            if (self.layer) {
                                window.M.Map.zoomTo(self.layer.getDataExtent(), false);
                                if (self.userRights && self.userRights['visualize']) {
                                    if ($.isArray(data.features) && data.features[0]) {
                                        if (data.features[0].properties['services']['browse'] && data.features[0].properties['services']['browse']['layer']) {
                                            M.Map.addLayer({
                                                title: data.features[0].id,
                                                type: data.features[0].properties['services']['browse']['layer']['type'],
                                                layers: data.features[0].properties['services']['browse']['layer']['layers'],
                                                url: data.features[0].properties['services']['browse']['layer']['url'].replace('%5C', '')
                                            });
                                        }
                                    }
                                }
                            }
                        }

                        /*
                         * Add "Center on layer" action
                         */
                        (new window.M.Toolbar({
                            position: 'nw',
                            orientation: 'h'
                        })).add({
                            title: '<span class="fa fa-map-marker"></span>',
                            tt: "Center",
                            onoff: false,
                            onactivate: function (scope, item) {
                                item.activate(false);
                                if (self.layer && self.layer.features && self.layer.features.length > 0) {
                                    window.M.Map.zoomTo(self.layer.getDataExtent(), false);
                                }
                            }
                        });

                        clearInterval(fct);
                    }
                }, 500);
            } 
        },
        
        /**
         * Post to mapshup
         * 
         * @param {string/object} json
         */
        addLayer: function(json) {

            if (!this.isLoaded) {
                return null;
            }

            if (typeof json === 'string') {
                json = JSON.parse(decodeURI(json));
            }

            return window.M.Map.addLayer(json, {
                noDeletionCheck: true
            });

        },
        /**
         * Initialize search result layer
         * 
         * @param {object} json - GeoJSON FeatureCollection
         * @param {boolean} centerMap - if true, force map centering on FeatureCollection 
         */
        initLayer: function(json, centerMap) {
            if (!this.isLoaded) {
                return false;
            }
            this.layer = this.addLayer({
                type: 'GeoJSON',
                clusterized: false,
                data: json,
                zoomOnNew: centerMap ? 'always' : false,
                MID: '__resto__',
                color: '#FFF1FB',
                selectable:window.Resto.issuer === 'getCollection' ? true : false,
                featureInfo: {
                    noMenu: true,
                    onSelect: function(f) {
                        if (f && f.fid) {
                            window.M.Map.featureInfo.unhilite(window.M.Map.featureInfo.hilited);
                            window.Resto.selectFeature(f.fid, true);
                        }
                    },
                    onUnselect: function(f) {
                        $('.resto-feature').each(function() {
                            $(this).removeClass('selected');
                        });
                    }
                },
                ol:{
                    styleMap:new OpenLayers.StyleMap({
                        "default": new OpenLayers.Style(OpenLayers.Util.applyDefaults({
                            fillOpacity: window.Resto.issuer === 'getCollection' ? 0.2 : 0.001,
                            strokeColor: "#ffff00",
                            strokeWidth: 1,
                            fillColor: "#fff"
                        })),
                        "select": {
                            strokeColor:"#ffa500",
                            fillOpacity:window.Resto.issuer === 'getCollection' ? 0.7 : 0.001
                        }
                    })
                }
            });

        },
        
        /**
         * Update features layer
         * 
         * @param {Object} json - GeoJSON FeatureCollection
         * @param {boolean} centerMap : true to center the map
         * 
         */
        updateLayer: function(json, centerMap) {
            
            if (!this.isLoaded) {
                return false;
            }
            
            /*
             * Layer already exist => reload content
             * i.e. remove old features and insert new ones
             */
            if (this.layer) {
                this.layer.destroyFeatures();
                window.M.Map.layerTypes['GeoJSON'].load({
                    data: json,
                    layerDescription: this.layer['_M'].layerDescription,
                    layer: this.layer,
                    zoomOnNew: centerMap ? 'always' : false
                });
            }
            /*
             * Layer does not exist => create it
             */
            else {
                this.initLayer(json, centerMap);
            }
            
            this.updateBBOX();
        },
        
        /**
         * Add map bounding box in EPSG:4326 to all element with a 'resto-updatebbox' class
         */
        updateBBOX: function() {
            if (window.M && window.M.Map.map) {
                if ($('#mapshup').is(':visible')) {
                    var box = this.getBBOX();
                    $('.resto-updatebbox').each(function() {
                        $(this).attr('href', window.M.Util.extendUrl($(this).attr('href'), {
                            box: box
                        }));
                    });
                }
                else {
                    $('.resto-updatebbox').each(function() {
                        $(this).attr('href', window.M.Util.extendUrl($(this).attr('href'), {
                            box:null
                        }));
                    });
                }
            }
        },
        
        /**
         * Check that map panel is visible
         * 
         * @returns boolean
         */
        isVisible: function() {
            if (!this.isLoaded) {
                return false;
            }
            if (window.M && window.M.Map && window.M.Map.map && $('#mapshup').is(':visible')) {
                return true;
            }
            return false;
        },
        
        /**
         * Return current map bounding box
         */
        getBBOX: function() {
            if (!this.isLoaded) {
                return false;
            }
            return window.M.Map.Util.p2d(window.M.Map.map.getExtent()).toBBOX();
        },
        
        /**
         * Hilite feature
         * 
         * @param {string} fid
         * @param {boolean} zoomOn
         */
        hilite: function(fid, zoomOn) {
            if (!this.isLoaded) {
                return false;
            }
            var f = window.M.Map.Util.getFeature(window.M.Map.Util.getLayerByMID('__resto__'), fid);
            if (f) {
                if (zoomOn) {
                    window.M.Map.zoomTo(f.geometry.getBounds(), false);
                }
                window.M.Map.featureInfo.hilite(f);
            }
        }
        
    };

})(window);
