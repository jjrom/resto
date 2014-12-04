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
        selectOverlay: null,
        
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
             * Initialize menu
             */
            self.mapMenu = new self.Menu();
            
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
            self.selectOverlay = new ol.FeatureOverlay({
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
             * Initialize feature overlay for selected feature
             */
            self.hiliteOverlay = new ol.FeatureOverlay({
                map: self.map,
                style: [new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: 'rgba(225, 225, 225, 0.2)'
                        }),
                        stroke: new ol.style.Stroke({
                            color: '#fff',
                            width: 1
                        })
                })]
            });
            
            /*
             * Map event - mousemove
             * Hilite hovered feature on mousemove
             */
            $(self.map.getViewport()).on('mousemove', function (evt) {
                var feature = self.map.forEachFeatureAtPixel(self.map.getEventPixel(evt.originalEvent), function (feature, layer) {
                    return feature;
                });
                if (feature !== self.hilited) {
                    if (self.hilited) {
                        self.hiliteOverlay.removeFeature(self.hilited);
                    }
                    if (feature) {
                        self.hiliteOverlay.addFeature(feature);
                    }
                    self.hilited = feature;
                }
            });

            /*
             * Map event - click
             * Display menu
             */
            self.map.on('click', function (evt) {
                var pixel = self.map.getEventPixel(evt.originalEvent);
                var test = self.map.forEachFeatureAtPixel(pixel, function (feature, layer) {
                    if (feature) {
                        self.select(feature.getId());
                        self.mapMenu.show(pixel, feature);
                        return true;
                    }
                    return false;
                });
                if (!test) {
                    self.unSelect();
                }
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
            if (!options.append) {
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
         * Select feature
         * 
         * @param {string} fid
         * @param {boolean} zoomOn
         */
        select: function (fid, zoomOn) {
            
            if (!this.isLoaded) {
                return false;
            }
                        
            var f = this.layer.getSource().getFeatureById(fid);
            if (f) {
                var extent = f.getGeometry().getExtent();
                this.unSelect();
                if (zoomOn) {
                    this.map.getView().fitExtent(extent, this.map.getSize());
                    this.mapMenu.show([$('#map').width() / 2, $('#map').height() / 2], f);
                }
                if (f !== this.selected) {
                    this.selectOverlay.addFeature(f);
                    this.selected = f;
                }
            }
        },
        
        /**
         * Unselect all feature
         * 
         * @param {string} fid
         */
        unSelect: function() {
            this.mapMenu.hide();
            if (this.selected) {
                this.selectOverlay.removeFeature(this.selected);
                this.selected = null;
            }
        }

    };
    
})(window);

/**
 * resto contextual menu
 * 
 * Code mainly from mapshup https://github.com/jjrom/mapshup/blob/master/client/js/mapshup/lib/core/Menu.js
 */
(function (window) {

    window.Resto.Map.Menu = function () {

        /*
         * Only one Map Menu object is created
         */
        if (window.Resto.Map.Menu._o) {
            return window.Resto.Map.Menu._o;
        }
        
        /**
         * Last mouse click is stored to display menu
         */
        this.coordinate = [0,0];

        /**
         * Check is menu is loaded
         */
        this.isLoaded = false;
        
        /**
         * Menu items array
         */
        this.items = [];
        
        /**
         * Menu is not displayed within the inner border of the map
         * of a size of "limit" pixels (default is 0 pixels)
         */
        this.limit = 0;
        
        /**
         * Menu initialisation
         *
         * <div id="menu">
         * </div>
         */
        this.init = function() {
            
            /*
             * Variables initialisation
             */
            var self = this;

            /*
             * menu is already initialized ? => do nothing
             */
            if (self.isLoaded) {
                return self;
            }

            /*
             * Create the menu div
             */
            $('#map').append('<div id="mapmenu"></div>');
            
            /*
             * Set jquery reference
             */
            self.$m = $('#mapmenu');
            
            /*
             * Menu is successfully loaded
             */
            self.isLoaded = true;
            
            return self;

        };
        
        /*
         * Add an external item to the menu
         * This function should be called by plugins
         * that require additionnal item in the menu
         * 
         * @param items : array of menu items
         * 
         * Menu item structure :
         * {
         *      id: // identifier
         *      text: // Displayed title
         *      title: // Title
         *      callback: // function to execute on click
         * }
         */
        this.add = function (items) {

            /*
             * Add new item
             */
            if ($.isArray(items)) {
                for (var i = 0, l = items.length; i < l; i++) {
                    this.items.push(items[i]);
                }

                /*
                 * Recompute items position within the menu
                 */
                this.refresh();
            }

            return true;
            
        };
        
        /**
         * Force menu to init
         */
        this.refresh = function() {
            
            /*
             * Items are displayed on a circle :
             *  - first item position is 180 degrees
             *  - trigonometric direction
             */
            var i,ii,x,y,rad,
                scope = this,
                offsetX = 0,
                angle = 180,
                step = 45,
                a = 75,
                b = a;
            
            /*
             * Clean menu
             */
            $('.item', scope.$m).remove();
            
            for (i = 0, ii = scope.items.length; i < ii; i++) {
                (function(item, $m) {
                
                    /*
                     * Convert angle in radians
                     */
                    rad = (angle * Math.PI) / 180;

                    $m.append('<div class="item right" id="'+item.id+'" title="'+item.title+'">'+item.text+'</div>');
                    x = Math.cos(rad) * a + offsetX;
                    y = Math.sin(rad) * b - 10;
                    $('#'+item.id).click(function(){
                        item.callback(scope);
                        return false;
                    }).css({
                        'left': Math.round(x),
                        'top': Math.round(y)
                    });

                    /*
                     * Increment angle
                     */ 
                    angle = angle + step;
                })(scope.items[i], scope.$m);
            }

        };
        
        /*
         * Remove an item from the menu
         * 
         * @param id : id of item to remove
         * 
         */
        this.remove = function(id) {
            
            /*
             * Roll over items
             */
            for (var i = 0, l = this.items.length;i<l;i++) {
                
                /*
                 * Remove item with corresponding id
                 */
                if (this.items[i].id === id) {
                    
                    this.items.splice(i,1);
                    
                    /*
                     * Recompute items position within the menu
                     */
                    this.refresh();
                    
                    return true;
                }
            }
            
            return false;

        };
        
        /*
         * Remove all item from menu
         */
        this.clean = function() {
            this.items = [];
            this.refresh();
            return true;
        };

        /**
         * Feature menu is displayed at "pixel" position
         * If pixel is not given as input, it is inferred
         * from this.coordinate position (i.e. last click on #map div)
         * 
         * @param {array} pixel : [x,y] position to display menu
         * @param {Object} feature
         */
        this.show = function(pixel, feature) {
            
            /**
             * menu is not loaded ? => initialize it
             */
            if (!this.isLoaded) {
                this.init();
            }
            
            /*
             * Add contextual menu item
             */
            this.clean();
            this.add([
                {
                    id:window.Resto.Util.getId(),
                    text:'<span class="fa fa-3x fa-close"></span>',
                    title:window.Resto.Util.translate('_close'),
                    callback:function(scope) {
                        window.Resto.Map.unSelect();
                    }
                }
            ]);
            if (feature) {
                var properties = feature.getProperties();
                this.add([
                    {
                        id:window.Resto.Util.getId(),
                        text:'<span class="fa fa-3x fa-info"></span>',
                        title:window.Resto.Util.translate('_viewMetadata'),
                        callback:function(scope) {
                            if (feature) {
                                window.location = window.Resto.restoUrl + 'collections/' + properties['collection'] + '/' + feature.getId() + '.html?lang=' + window.Resto.language;
                            }
                        }
                    }
                ]);
                
                if (properties['services'] && properties['services']['download'] && properties['services']['download']['url']) {
                    this.add([
                        {
                            id:window.Resto.Util.getId(),
                            text:'<span class="fa fa-3x fa-cloud-download"></span>',
                            title:window.Resto.Util.translate('_download'),
                            callback:function(scope) {
                                window.Resto.download(properties['services']['download']['url'] + '?lang=' + window.Resto.language);
                            }
                        }
                    ]);
                    if (window.Resto.Header.userProfile['userid'] !== -1) {
                        this.add([
                            {
                                id:window.Resto.Util.getId(),
                                text:'<span class="fa fa-3x fa-shopping-cart"></span>',
                                title:window.Resto.Util.translate('_addToCart'),
                                callback:function(scope) {
                                    window.Resto.addToCart({
                                        'id':feature.getId(),
                                        'properties':properties
                                    });
                                }
                            }
                        ]);
                    }
                }
            }
            
            pixel = pixel || [0, 0];
            
            this.coordinate = window.Resto.Map.map.getCoordinateFromPixel(pixel);
            
            /**
             * Show '#menu' at the right position
             * within #map div
             */
            this.$m.css({
                'left': pixel[0],
                'top': pixel[1] + $('.resto-search-panel').outerHeight() - $('.resto-search-panel').position().top
            }).show();

            return true;
        };
        
        /*
         * Update menu position
         */
        this.updatePosition = function() {
            var xy = window.Resto.Map.map.getPixelFromCoordinate(this.coordinate);
            this.$m.css({
                'left': xy[0],
                'top': xy[1]
            });
            return true;
        };
        

        /**
         * Hide menu
         */
        this.hide = function() {
            this.$m.hide();
            return true;
        };
        
        /*
         * Initialize object
         */
        this.init();
        
        /*
         * Set unique instance
         */
        window.Resto.Map.Menu._o = this;
        
        return this;
    };
    
})(window);
