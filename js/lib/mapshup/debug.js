/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/*
 * Includes parts of code under the following licenses:
 *
 *
 * --- json2.js by Douglas Crockford
 * --- https://github.com/douglascrockford/JSON-js/blob/master/json2.js
 * --- Release under Public Domain
 *
 *
 * --- Checksum function
 * --- (c) 2006 Andrea Ercolino
 * --- http://noteslog.com/post/crc32-for-javascript/
 * --- Released under the MIT License
 * --- http://www.opensource.org/licenses/mit-license.php
 *
 */

/*
 * Uses icons under the following licenses :
 *
 * --- GeoSilk icon set by Rolando Peate
 * --- http://projects.opengeo.org/geosilk
 * --- Released under Creative Commons Attribution 3.0 License.
 * --- http://creativecommons.org/licenses/by/3.0/)
 *
 *
 * --- Iconic icon set
 * --- http://somerandomdude.com/projects/iconic/
 * --- Release under Creative Commons Attribution-Share Alike 3.0 license
 * --- http://creativecommons.org/licenses/by-sa/3.0/us/
 * 
 */

/*
 * Uses libraries under the following licences
 *
 * --- OpenLayers.js -- OpenLayers Map Viewer Library
 * --- Copyright 2005-2010 OpenLayers Contributors
 * --- Released under the Clear BSD license
 * --- http://svn.openlayers.org/trunk/openlayers/license.txt
 *
 *
 * --- jQuery JavaScript Library v1.4.4
 * --- http://jquery.com/
 * ---
 * --- Copyright 2010, John Resig
 * --- Released under the MIT license.
 * --- http://jquery.org/license
 *
 *
 * --- jqPlot
 * --- Copyright (c) 2009 - 2010 Chris Leonello
 * --- Released under the MIT license
 * --- http://www.opensource.org/licenses/mit-license.php
 *
 */

/**
 * mapshup.js : mapshup core library
 *
 * @requires js/mapshup/lang/en.js
 * @requires js/mapshup/config/default.js
 *
 *
 * Tips and tricks :
 * 
 *  - z-indexes :
 *      map                         :   1 (or something like that :)
 *      OpenLayers objects          :   2 to something << 10000
 *      coords                      :   10000
 *      hiliteFeature               :   10000
 *      Mlogo                     :   10000
 *      menu                        :   10000
 *
 *      whereami                    :   10100   (plugins/Geonames.js)
 *      featureinfo                 :   10150
 *      distance                    :   10150   (plugins/Distance.js)
 *      map3d                       :   10200   (plugins/Toolbar_GoogleEarth.js)
 *      drawinginfo                 :   10300   (plugins/Drawing.js)
 *      jUserManagementPanel        :   10700   (plugins/UserManagement.js)
 *      jUserManagementPanelTab     :   11000   (plugins/UserManagement.js)
 *      jCatalogConfiguration       :   11000   (plugins/Catalog.js)
 *      welcome                     :   12000   (plugins/Welcome.js)
 *      Toolbar                     :   19500   (core/Toolbar.js)
 *      .pn                         :   20000   (core/SouthPanel.js)
 *      ddzone                      :   34000   (plugins/AddLayer.js)
 *      wheader                     :   34500
 *      mask                        :   35000
 *      tooltip                     :   36000
 *      popup                       :   38000 
 *      message                     :   38000 
 *      drawingAsk                  :   38000   (plugins/Drawing.js)
 *      drawingDesc                 :   38000   (plugins/Drawing.js)
 *      activity                    :   38500
 *      
 */

/**
 * Create the util object for mapshup
 * 
 * @param {Object} window 
 * @param {Object} navigator
 * 
 */
(function(window,navigator) {
    
    /**
     * Create mapshup M object with properties and functions
     */
    window.M = {
        
        VERSION_NUMBER:'mapshup 2.5',
        
        /**
         * Plugin objects are defined within M.Plugins
         */
        Plugins:{},
        
        /**
         * This is the main entry point to mapshup
         * This function must be called after document has been fully loaded
         * through the jquery .ready() i.e.
         * 
         *      $(document).ready(function() {
         *          M.load();
         *      });
         * 
         * Note : default language files can be superseeded through options parameters
         * e.g.
         *        options = {
         *          i18n: {
         *              fr:{
         *                  'text 1':'traduction 1',
         *                  'text 2':'traduction 2',
         *                  ...etc...
         *              },
         *              en:{
         *                  'text 1':'translation 1',
         *                  'text 2':'translation 2',
         *                  ...etc...
         *              }
         *          }
         *        }
         * @param {object} options
         * 
         */
        load:function(options) {
            
            var ctx = null,
            self = this,
            kvp = (function () {
                return self.Util.extractKVP(window.location.href);
            })();    
            
            options = options || {};
            
            /*
             * If M.Config is not defined, everything stops !
             */
            if (self.Config === undefined) {
                alert("GRAVE : no configuration file defined. Load aborted");
                exit();
            }
            
            /*
             * Set panels configuration
             */
            self.Config.panels = self.Config.panels || {};
            $.extend(self.Config.panels,{
                south:self.Config.panels.south || {},
                side:self.Config.panels.side || {}
            });
        
            /*
             * Decode geohash from url
             * Structure is '#<geohash>:<zoomLevel>'
             * 
             * If zoomLevel is not set, it is forced at the value of 9
             * 
             * Note that geohash superseeds existing location
             */
            if (window.location.hash) {
                var lonlat, zoom, a = window.location.hash.split(':');
                try {
                    lonlat = self.Map.Util.Geohash.decode(a[0]);
                    kvp.lon = lonlat.lon;
                    kvp.lat = lonlat.lat;
                    zoom = parseInt(a[1]);
                    kvp.zoom = !isNaN(zoom) ? zoom : 9;
                }
                catch(e){}
            }
            
            /*
             * If #masphup div does not exist do not load mapshup
             */
            if ($('#mapshup').length === 0) {
                alert('GRAVE : no <div id="mapshup"> defined. Load aborted');
                exit();
            }
            
            /*
             * Create mapshup container structure
             * 
             *      <div id="mapshup">
             *          <div id="wcontainer">
             *              <div id="mapcontainer">
             *                  <div id="map"></div>
             *              </div>
             *          </div>
             *      </div>
             */
            $('#mapshup').append('<div id="wcontainer"><div id="mapcontainer"><div id="map"></div></div></div>');
            
            /**
             * Create header structure
             * 
             * <div id="theBar" class="shadow">
             *      <div class="container">
             *          <div class="logo hover"></div>
             *          <div class="searchBar"></div>
             *          <div class="leftBar"></div>
             *          <div class="userBar"></div>
             *      </div>
             * </div>
             */
            self.$header = self.Util.$$('#theBar', $('#mapshup')).addClass('shadow').html('<div class="container"><div class="logo hover"><a href="http://www.mapshup.info" target="_blank">mapshup</a></div><div class="searchBar"></div><div class="leftBar"></div><div class="userBar"></div></div>');
            
            /**
             * Initialize div elements reference
             */
            self.$map = $('#map');
            
            /**
             * Initialize map container reference
             */
            self.$mcontainer = $('#mapcontainer');
            
            /**
             * Initialize #wcontainer reference
             */
            self.$container = $('#wcontainer');
            
            /**
             * Initialize events
             */
            self.events = new self.Events();

            /**
             * Initialize tooltip
             */
            self.tooltip = new self.Tooltip();

            /**
             * Initialize activity
             */
            self.activity = new self.Activity();

            /**
             * Initialize mask
             */
            self.mask = new self.Mask();
            
            /**
             * Initialize timeLine
             */
            if (self.TimeLine) {
                self.timeLine = new self.TimeLine(self.Config["general"].timeLine);
            }
            
            /*
             * Initialize Side panel
             */
            self.sidePanel = (new self.SidePanel({
                over:self.Config.panels.side.over,
                position:self.Config.panels.side.position,
                w:self.Config.panels.side.w
            }));
            
            /*
             * Initialize South panel
             */
            self.southPanel = (new self.SouthPanel({
                over:self.Config.panels.south.over,
                h:self.Config.panels.south.h
            }));
            
            /*
             * If kvp got a "uid" key, then the corresponding context
             * is retrieved from the server
             */
            if (kvp["uid"]) {
                
                self.Util.ajax({
                    url:self.Util.proxify(self.Util.getAbsoluteUrl(self.Config["general"].getContextServiceUrl)+kvp["uid"]),
                    async:true,
                    dataType:"json",
                    success: function(data) {

                        /*
                         * Parse result
                         */
                        if (data && data.contexts) {

                            /*
                             * Retrieve the first context
                             * contexts[
                             *      {
                             *          context:
                             *          location:
                             *          utc:
                             *      },
                             *      ...
                             * ]
                             */
                            if (data.contexts[0]) {
                                ctx = JSON.parse(data.contexts[0].context);
                            }
                        }
                        
                        /*
                         * Continue initialization - set lang
                         */
                        self.setLang(kvp, {context:ctx, i18n:options['i18n']});
                        
                    },
                    error: function(data) {
                        self.Util.message("Error : context is not loaded");
                        
                        /*
                         * Continue initialization - set lang
                         */
                        self.setLang(kvp, {i18n:options['i18n']});
                        
                    }
                }, {
                    title:self.Util._("Load context"),
                    cancel:true
                });
                
            }
            /*
             * Add Layer
             */
            else if (kvp["layers"]) {
                try {
                    var ls = JSON.parse(decodeURIComponent(kvp["layers"].replace(window.location.hash, '')));
                    if (!$.isArray(ls)) {
                        ls = [ls];
                    }
                    for (i = 0, l = ls.length; i < l; i++) {
                        self.Config.add("layers", ls[i]);
                    }
                    self.setLang(kvp, {i18n:options['i18n']});
                }catch(e){
                    M.Util.message('Error : cannot read input layer');
                    self.setLang(kvp, {i18n:options['i18n']});
                }
            }
            /*
             * If there is no kvp["uid"] defined, then go the next initialization step,
             * i.e. set mapshup lang
             */
            else {
                self.setLang(kvp, {i18n:options['i18n']});
            }
            
        },
        
        /**
         * Retrieve mapshup default lang file
         * 
         * @param {Object} kvp : Key value pair object.
         *             If kvp["lang"] is defined, it superseed the default lang configuration
         * 
         * @param {Object} options
         */
        setLang:function(kvp, options) {
            
            /*
             * Read KVP from URL if any
             */
            var i,
            check = -1,
            check2 = -1,
            self = this,
            c = self.Config["i18n"];
            
            options = options || {};
            
            /*
             * Set lang from kvp
             */
            if (kvp.lang) {
                c.lang = kvp.lang;
            }
            
            /*
             * Internationalisation (i18n)
             * lang is defined as follow :
             *  - M.defaultLang
             *  - superseed by M.Config.lang (if defined)
             *  - superseed by kvp.lang (if defined)
             */
            c.langs = c.langs || ['en', 'fr'];
            
            /*
             * Set the i18n array
             */
            self.i18n = [];
            
            if (!c.lang || c.lang === 'auto') {
                try{
                    c.lang = navigator.language;
                }catch(e){
                    c.lang = navigator.browserLanguage; //IE
                }
            }

            /**
             * Determine browser language.
             * Since indexOf method on Arrays is not supported by
             * all browsers (e.g. Internet Explorer) this is a bit
             * tricky
             */
            for (i = c.langs.length;i--;) {
                if (c.langs[i] === c.lang) {
                    check = 0;
                    break;
                }
            }
            if (check === -1){
                check2 = -1;
                // Avoid country indicator
                if (c.lang !== undefined) {
                    c.lang = c.lang.substring(0,2);
                }
                for (i = c.langs.length;i--;) {
                    if (c.langs[i] === c.lang) {
                        check2 = 0;
                        break;
                    }
                }
                if (check2 === -1){
                    c.lang = c.langs[0];
                }
            }

            /**
             * Asynchronous call : load the lang file
             */
            $.ajax({
                url:self.Config["general"].rootUrl + c.path+"/"+c.lang+".js",
                async:true,
                dataType:"script",
                success:function() {
                    
                    /*
                     * Superseed lang file
                     */
                    if (options['i18n'] && options['i18n'][c.lang]) {                       
                        for(var key in options['i18n'][c.lang]) {
                            M.i18n[key] = options['i18n'][c.lang][key];
                        }
                    }
                    
                    self.init(kvp, options['context']);
                },
                /* Lang does not exist - load english */
                error:function() {
                    $.ajax({
                        url:self.Config["general"].rootUrl + c.path+"/en.js",
                        async:true,
                        dataType:"script",
                        success:function() {
                            c.lang = "en";
                            self.init(kvp, options['context']);
                        }
                    });
                }
            });
            
        },
        
        /*
         * mapshup initialisation
         * 
         * @param kvp : Key Value pair object
         *              If kvp["lat"] && kvp["lon"] is defined, it superseed the default location configuration
         * 
         * @param ctx: Context
         */
        init:function(kvp, ctx) {
            
            var bg,fn,i,l,name,options,plugin,
            self = this,
            c = self.Config;
            
            /*
             * Update location from context
             */
            if (ctx && ctx.location) {
                c["general"].location = ctx.location;
            }
            
            /*
             * Superseed location from input kvp
             */
            if (kvp) {
                c["general"].location = {
                    bg:self.Util.getPropertyValue(kvp, "bg", c["general"].location.bg),
                    lon:self.Util.getPropertyValue(kvp, "lon", c["general"].location.lon),
                    lat:self.Util.getPropertyValue(kvp, "lat", c["general"].location.lat),
                    zoom:self.Util.getPropertyValue(kvp, "zoom", c["general"].location.zoom)
                };
            }
            
            /**
             * Initialize menu
             */
            self.menu = new self.Menu();
            
            /**
             * Map initialization
             */
            self.Map.init(c);
            
            /**
             * Update configuration
             */
            if (ctx && ctx.layers) {
                c.update(ctx.layers);
            }
            
            /*
             * Plugins initialization
             * Roll over M.plugins hash table
             * and remove all entries that are not defined
             * within the M.Config.plugins object
             */
            self.plugins = [];
            for (i = 0, l = c.plugins.length; i < l; i++) {
                name = c.plugins[i].name;
                options = c.plugins[i].options || {};
                plugin = (new Function('return M.Plugins.'+name+' ? new M.Plugins.'+name+'() : null;'))();
                
                /*
                 * Plugin exists and is successfully initialized
                 * => add it to the self.plugins array
                 */
                if (plugin && plugin.init(options)) {
                    self.plugins[name] = plugin;
                }
            }
            
            /*
             *
             * Add layers read from config
             *
             * The code evaluate the OpenLayers class name and the corresponding options both defined
             * within the "layers" array in the configuration file.
             */
            for (i = 0, l = c.layers.length; i < l; i++) {
                
                if (c.layers[i].type && self.Map.layerTypes[c.layers[i].type]) {
                
                    /*
                     * Add layer to the map
                     */
                    self.Map.addLayer(c.layers[i], {
                        noDeletionCheck:true,
                        initial:true
                    });
                   
                }
            }

            /*
             * Set background
             */
            if (c["general"].location.bg) {
                bg = self.Map.Util.getLayerByMID(c["general"].location.bg);
                if (bg && bg.isBaseLayer) {
                    self.Map.setBaseLayer(bg);
                }
            }
            
            /* Store current window size */
            self._wd = {
                w:window.innerWidth,
                h:window.innerHeight
            };
            
            /* Force M.$mcontainer dimensions to pixels values (avoid computation problem with %) */
            self.$mcontainer.css({
                'width':self.$mcontainer.width(),
                'height':self.$mcontainer.height()
            });
            
            /*
             * Detect window resize
             * 
             *   On window resizing, div position and dimension
             *   are modified to reflect map new size
             *   
             *   Plugins must register a "resizeend" event to
             *   resize
             */
            $(window).bind('resize', function(){
                
                /*
                 * Trick to avoid too many resize events
                 * that could alter performance
                 */
                clearTimeout(fn);
                fn = setTimeout(function(){
                    
                    /*
                     * Resize map container width following window resize
                     */
                    if (!$('#mapshup').hasClass('noResizeWidth')) {
                        self.$mcontainer.css({'width':self.$mcontainer.width() + (window.innerWidth - self._wd.w)});
                    }
                    
                    /*
                     * Resize map container height for non embeded context
                     */
                    if (!$('#mapshup').hasClass('noResizeHeight')) {
                        self.$mcontainer.css({'height':self.$mcontainer.height() + (window.innerHeight - self._wd.h)});
                    }
                    
                    /*
                     * Set  M._wd to reference to the new window size
                     */
                    self._wd = {
                        w:window.innerWidth,
                        h:window.innerHeight
                    };

                    /*
                     * Propagate resizeend event
                     */
                    self.events.trigger('resizeend');
                }, 100);

            });
            
            /*
             * Force mapshup resize
             */
            setTimeout(function(){
                self.events.trigger('resizeend');
            }, 1000);
            
            /*
             * Prevent Drag&drop over everything
             */
            $(window).bind('drop dragover',function(e){
                e.preventDefault();
                return false;
            });
            
        },
        
        /**
         * Remove object from the mapshup context
         * 
         * @param {Object} obj
         */
        remove: function(obj) {

            if (!obj) {
                return false;
            }
            
            /*
             * Check for jquery $d main object
             * and remove it from the DOM
             */
            if (obj.$d) {
                obj.$d.remove();
            }
            
            /*
             * Remove attached events
             */
            this.events.unRegister(obj);
            this.Map.events.unRegister(obj);
            
            /*
             * Remove all object properties
             */
            for (var p in obj) {
                if (obj.hasOwnProperty(p)) {
                    delete obj[p];
                }
            }
            
            /*
             * Nullify object - TODO remove it from
             * the global hash table
             */
            obj = null;
            
            return true;
            
        }
        
    };

})(window, navigator);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * The Config object
 * This is an (almost) empty shell
 * Each property is described within
 * the Config/default.js file
 */
(function(M) {
    
    /*
     * Initialize M
     */
    M = M || {};
   
    /*
     * Initilaze M.Config
     */
    M.Config = {

        /**
         * General Configuration
         * Empty : see Config/default.js
         */
        general:{
            location:{
                lon:0,
                lat:0,
                zoom:0
            }
        },

        /**
         * List of predefined groups
         */
        groups:[],

        /**
         * Internationalization
         * Empty : see Config/default.js
         */
        i18n:{},
        
        /**
         * Layers list
         * Empty : see Config/default.js
         */
        layers:[],

        /**
         * Map Configuration
         * By default map is in EPSG:3857 projection (official EPSG projection for
         * Spherical Mercator projection (see EPSG:900913 Google projection)
         */
        mapOptions: {
            projection:new OpenLayers.Projection("EPSG:3857"),
            displayProjection:new OpenLayers.Projection("EPSG:4326"),
            numZoomLevels:22
        },

        /**
         * List of available plugins
         */
        plugins:[],

        /**
         * Upload service information
         * Empty : see Config/default.js
         */
        upload:{},

        /**
         * Add content to properties
         *
         *  @param {string} propertyName : name of the property
         *  @param {object} content : content to be added
         */
        add: function(propertyName, content) {

            var p = this[propertyName];
            
            /*
             * propertyName should be an array and one of the following
             *  - layers
             *  - groups
             *  - plugins
             */
            if ($.isArray(p)) {
                
                /*
                 * If plugin already exist, remove it first !
                 */
                if (content && this.get(propertyName, "name", content.name)) {
                    this.remove(propertyName, content.name);
                }
                p.push(content);
            }
        },
        
        /*
         * Extend plugin property
         */
        extend: function(pluginName, options) {
            
            var i,
                l,
                p;
            
            /*
             * Roll over Config plugins list
             */
            for (i = 0, l = this.plugins.length; i < l; i++) {

                /*
                 * Plugin is found => return plugin options object or an empty array
                 * if options is not defined
                 */
                if (this.plugins[i].name === pluginName) {
                    p = this.plugins[i];
                    p.options = p.options || {};
                    $.extend(p.options,options);
                }
            }
            
            
        },
            
        /**
         * Return object from propertyName array with
         * a key equals to value
         * 
         * @param {String} propertyName
         * @param {String} key
         * @param {String} value
         */
        get: function(propertyName, key, value) {
            
            var p = this[propertyName];
            
            /*
             * propertyName should be an array and one of the following
             *  - layers
             *  - groups
             *  - plugins
             */
            if ($.isArray(p)) {
                for (var i = 0, l = p.length; i < l; i++) {
                    if (p[i][key] === value) {
                        return p[i];
                    }
                }
            }
        
            return null;
            
        },
            
        /**
         * Remove content from one of the following property
         *  - layers
         *  - plugins
         *  - groups
         *
         *  !! Warning : if within one property there are more than
         *  one object with the same "name", only the first one is removed
         */
        remove: function(propertyName, name) {

            var i,
                l,
                checkName,
                property = this[propertyName];
            
            /*
             * property is an array => thus it's a valid property
             */
            if (property instanceof Array && name) {
                    
                /*
                 * Objects from properties "groups" and "plugins" have a unique "name" property
                 * It's not the case for "layer" property which have a non-unique "title" property
                 */
                checkName = propertyName === "layers" ? "title" : "name";
                for (i = 0, l = property.length ; i < l; i++) {
                    if (property[i][checkName] === name) {
                        property.splice(i,1);
                        break;
                    }
                }
            }
        },
        
        /**
         * 
         * Update Config object layers with input layer description list
         * 
         * @param lds : layer description
         */
        update: function(lds) {
            
            var i, l, j, k, b,
            self = this;
            
            /*
             * If the Map is not initialized, do nothing
             */
            if (!M.Map) {
                return false;
            }
            
            /*
             * Paranoid mode
             */
            lds = lds || [];
            
            /*
             * Update Config layers with context layers
             */
            for (i = 0; i < self.layers.length; i++) {

                /*
                 * By default, remove the layer
                 */
                b = true;

                /*
                 * Roll over input layer descriptions
                 */
                for (j = 0, k = lds.length; j < k; j++) {

                    /*
                     * The layer is present in the context layer list. No need to remove it
                     */
                    if ((new M.Map.LayerDescription(self.layers[i], M.Map)).getMID() === (new M.Map.LayerDescription(lds[j], M.Map)).getMID()) {
                        b = false;
                        break;
                    }

                }

                /*
                 * Remove the layer
                 * 
                 * !! Since we use splice we need to recompute j index with the new
                 * array size !!
                 */
                if (b) {
                    self.layers.splice(i,1);
                    i--;
                }

            }

            /*
             * Add or update layers
             */
            for (i = 0, l = lds.length; i < l; i++) {

                /*
                 * By default, add the layer
                 */
                b = true;

                /*
                 * Roll over existing layers
                 */
                for (j = 0, k = self.layers.length; j < k; j++) {

                    /*
                     * The layer already exist - update it
                     */
                    if ((new M.Map.LayerDescription(lds[i], M.Map)).getMID() === (new M.Map.LayerDescription(self.layers[j], M.Map)).getMID()) {
                        b = false;
                        break;
                    }

                }

                /*
                 * Add layer
                 */
                if (b) {
                    self.layers.push(lds[i]);
                }
                /*
                 * Update existing layer properties
                 */
                else {
                    
                    /*
                     * Update hidden status
                     */
                    self.layers[j].hidden = lds[i].hidden;
                    
                    /*
                     * Update search filters for catalog layers
                     */
                    if (lds[i].search) {
                        self.layers[j].search = lds[i].search;
                    }
                }
            }
            
            return true;

        }
        
    };
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
(function(window, M, document) {
    
    /*
     * Initialize M
     */
    M = M || {};
    
    /*
     * Initilaze M.Util
     */
    M.Util = {
        
        /**
         * Associative array of message
         */
        messages:[],
        
        /**
         * abc is added to proxyURL KVP (see scripts/proxy.php)
         */
        abc: (function() {
            var a = Math.round(Math.random()*865),
            b = Math.round(Math.random()*757);
            return '&a='+a+'&b='+b+'&c='+((a+17) - (3*(b-2)));
        })(),
        
        /*
         * Cookie object
         */
        Cookie: {
            
            /**
             * Get cookie
             * 
             * @param {String} name
             */
            get: function(name) {
                var nameEQ = name + "=",
                ca = document.cookie.split(';'),
                i,
                l,
                c;
                for(i = 0, l = ca.length; i < l; i++) {
                    c = ca[i];
                    while (c.charAt(0)===' ') c = c.substring(1,c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length,c.length);
                }
                return null;
            },
            
            /**
             * Delete cookie "name"
             * 
             * @param {String} name
             */
            remove:function(name) {
                this.set(name,"",-1);
            },
            
            /**
             * Set cookie "name=value" valid for days
             * 
             * @param {String} name
             * @param {String} value
             * @param {String} days
             */
            set: function(name,value,days) {

                var expires, domain, slash, stripped, path = "",
                date = new Date();
                
                if (days) {
                    date.setTime(date.getTime()+(days*24*60*60*1000));
                    expires = "; expires="+date.toGMTString();
                }
                else {
                    expires = "";
                }
                
                /*
                 * Compute domain and path from configuration properties
                 * rootUrl and indexPath
                 * It is assumes that rootUrl always start with "http//"
                 */
                stripped = M.Config["general"].rootUrl.substr(7, M.Config["general"].rootUrl.length);
                slash = stripped.indexOf('/');
                if (slash === -1) {
                    domain = stripped;
                    path = "/";
                }
                else {
                    domain = stripped.substr(0,slash);
                    path = stripped.substr(slash, stripped.length);
                }
                document.cookie = name+"="+value+expires+"; domain="+domain+"; path="+path;
            }
        },

        device:(function() {

            /*
             * Initialize user agent string to lower case
             */
            var device,
            touch,
            uagent = navigator.userAgent.toLowerCase();

            /*
             * android ?
             */
            if (uagent.indexOf("android") !== -1) {
                device = "android";
                touch = true;
            }
            /*
             * iphone ?
             */
            else if (uagent.indexOf("iphone") !== -1) {
                device = "iphone";
                touch = true;
            }
            /*
             * ipod ?
             */
            else if (uagent.indexOf("ipod") !== -1) {
                device = "ipod";
                touch = true;
            }
            /*
             * ipad ?
             */
            else if (uagent.indexOf("ipad") !== -1) {
                device = "ipad";
                touch = true;
            }
            /*
             * Normal device
             */
            else {
                device = "normal";
                touch = false;
            }

            return {
                type:device,
                touch:touch
            };
        })(),

        /*
         * Escapable and meta character protection
         * See https://github.com/douglascrockford/JSON-js/blob/master/json2.js
         *
         */
        escapable: /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
          
        meta: {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        
        /**
         * Sequence number to guarantee unique IDs
         */
        sequence:0,
        
        /**
         * Internationalisation i18n function.
         * Each displayed message are translated through
         * this function
         * 
         * @param {String} s
         */
        _: function(s) {
            if (M.i18n === undefined) {
                return s;
            }
            /*
             * Warning : if s is oneof the name of Array function ("shift", "splice", "push", etc)
             * it should be returned without translation to avoid function error
             */
            var i18nized = M.i18n[s];
            return typeof i18nized === "string" ? i18nized : s;
        },

        /**
         * Create the div "divname" under "context" and return it
         * If this div already exist, jsut return it
         *
         * Nota : if "context" is not specified, "divName" is created under "body"
         * 
         * @param {String} divName
         * @param {String} context
         */
        $$: function(divName, context) {
            context = context || 'body';
            var div = $(divName, context);
            if (div.length === 0) {
                var type = divName.substring(0,1) === "." ? "class" : "id";
                $(context).append('<div '+type+'="'+divName.substring(1,divName.length)+'"></div>');
                div = $(divName);
            }
            return div;
        },
        
        /**
         * Append a close button to input div
         * 
         * @param $d : jquery object reference
         * @param callback : callback function to call on click on close
         */
        addClose: function($d, callback) {
            
            var id = M.Util.getId();
            
            $d.append('<span id="'+id+'" class="close" title="'+M.Util._('close')+'"></span>');
            
            $('#'+id).click(function(e){
                $.isFunction(callback) ? callback(e) : $d.hide();
            });
        },
        
        /**
         * Clone object
         * 
         * Code from Keith Devens
         * (see http://keithdevens.com/weblog/archive/2007/Jun/07/javascript.clone)
         * 
         * @param srcInstance
         */
        clone: function(srcInstance) {
            if(typeof(srcInstance) !== 'object' || srcInstance === null) {
                return srcInstance;
            }
            var i, newInstance = srcInstance.constructor();
            for(i in srcInstance) {
                newInstance[i] = this.clone(srcInstance[i]);
            }
            return newInstance;
        },
        
        /**
         * Return GetCapabilities from an OGC service
         * 
         * @param XMLHttpRequestObj : the XMLHttpRequest object
         * @param format : format of the GetCapabilities file.
         *                 can be one of :
         *                      new OpenLayers.Format.WFSCapabilities()
         *                      new OpenLayers.Format.WMSCapabilities()
         *                      new OpenLayers.Format.WMTSCapabilities()
         */
        getCapabilities: function(XMLHttpRequestObj, format) {

            var capability = null;

            if (XMLHttpRequestObj.status !== 200 || !format) {
                return null;
            }
            try {
                capability = format.read(M.Util.textToXML(XMLHttpRequestObj.responseText));
            }
            catch(e) {
                M.Util.message(M.Util._("Error reading Capabilities file"));
            }

            return capability;
        },
        
        /**
         * Return the length of an associative array
         * 
         * @param h : associative array
         */
        getHashSize: function (h) {
            var v,r = 0;
            
            for (v in h) {
                r++;
            }
            return r;
        },
        
        /**
         * Return a unique title from layerDescription
         * 
         * @param layerDescription : input layerDescription object
         */
        getTitle: function(layerDescription) {
            layerDescription = layerDescription || {};
            return layerDescription.title ? layerDescription.title : (layerDescription.type || "Unknown") + "#" + this.sequence++;
        },
        
        /*
         * Returns UserInfo from LayersManager plugin
         * If not specified return an object with userid set to -1
         */
        getUserInfo: function() {
            
            var lm = M.Plugins["LayersManager"];
            
            if (lm && lm._o && lm._o.userInfo) {
                return lm._o.userInfo;
            }
            
            return {
                userid:-1
            };
            
        },
        
        /**
        * Extend URL parameters with newParams object
        *
        * @param {String} url : url
        * @param {Object} newParams : 
        *
        * @return {String} new URL
        */
        extendUrl: function(url, newParams) {
            
            var key, value, i, l, sourceParamsList, sourceParams = {}, newParamsString = "", sourceBase = url.split("?")[0];
            
            try {
                sourceParamsList = url.split("?")[1].split("&");
            }
            catch (e) {
                sourceParamsList = [];
            }
            for (i = 0, l = sourceParamsList.length; i < l; i++) {
                key = sourceParamsList[i].split('=')[0];
                value = sourceParamsList[i].split('=')[1];
                if (key && value) {
                    sourceParams[key] = value;
                }
            }
            
            newParams = $.extend(sourceParams, newParams);

            for (key in newParams) {
                if (newParams[key] !== null) {
                    newParamsString += key+"="+newParams[key]+"&";
                }
            }
            return sourceBase+"?"+newParamsString;
            
        },
        
        /**
         * Add a "display image" action to the given jquery 'a'
         * A click on 'a' will open the image within a fullscreen popup
         * 
         * @param {String} href
         * @param {String} title
         */
        showPopupImage:function(href, title) {

            /*
             * Popup reference
             */
            var popup = new M.Popup({
                modal:true,
                resize:false,
                autoSize:true,
                noHeader:true
            }),
            image = new Image();

            /*
             * Show Activity
             */
            M.activity.show();

            /*
             * Clear popup
             */
            popup.hide();

            /*
             * Show parent Mask
             */
            popup.$m.show();

            /*
             * Create image object
             */
            image.src = href;

            /*
             * Compute image after load
             */
            $(image).load(function() {

                /*
                 * Array of size :
                 *      0 => Image width
                 *      1 => Image height
                 *      2 => 80% of Window width
                 *      3 => 80% of Window height
                 */
                var sizes = [image.width, image.height, window.innerWidth * 0.8, window.innerHeight * 0.8],
                width = sizes[0],
                height = sizes[1];

                /*
                 * Image height is bigger than window height
                 * => reduce width/height to the window height preserving ratio
                 */
                if (height > sizes[3]) {
                    height = sizes[3];
                    width = (width * height) / sizes[1];
                }

                /*
                 * Image width is bigger than window width
                 * => reduce width/height to the window width preserving ratio
                 */
                if (width > sizes[2]) {
                    width = sizes[2];
                    height = (height * width) / sizes[0];
                }

                popup.$b.html('<div class="imageContent"><div class="padded"><img src="'+href+'" height="'+height+'" width="'+width+'"/><div class="innerTitle" style="width:'+width+'px;">'+title+'</div></div></div>');
                popup.$d.css({
                    'left':(window.innerWidth - popup.$d.width()) / 2,
                    'top':0
                });

                /*
                 * Hide Activity
                 */
                M.activity.hide();

                /*
                 * Show popup image
                 */
                popup.show();
            }).error(function () {
                
                /*
                 * Hide activity/popup
                 */
                M.activity.hide();
                popup.hide();
                
                M.Util.message("Error loading image");
                
            });

        },
        
        /**
         * Add a "display video" action to the given jquery 'a'
         * A click on 'a' will open the within within a fullscreen popup
         * 
         * @param video : object describing video i.e. 
         *                  {
         *                      title://Video title
         *                      url://url to the video
         *                      type://video type - one of mp4, ogg or webm
         *                      img://url to an image
         *                      w://width of the player (default 640)
         *                      h: height of the player (default 264)
         *                  }
         *                     
         */
        showPopupVideo: function(video) {
            
            /*
             * Popup reference
             */
            var type,w,h,img,codec,content,
            popup = new M.Popup({
                modal:true,
                resize:false,
                autoSize:true,
                noHeader:true
            });

            /*
             * Paranoid mode
             */
            video = video || {};
            if (!video.url) {
                this.message(this._("Error : url is not defined"));
                return false;
            }
            
            /*
             * Initialize default values
             */
            w = video.w || 640;
            h = video.h || 264;
            img = video.img || "";
            type = video.type;
            
            /*
             * Try to guess the video type from url if not specified
             */
            if (!type) {
                type = video.url.substring(video.url.length - 3,video.url.length).toLowerCase();
            }
            
            /*
             * Get codec from type
             */
            switch (type) {
                case "ogg":
                    codec = "video/ogg";
                    break;
                case "webm":
                    codec = "video/webm";
                    break;
                default:
                    codec = "video/mp4";
                    break;
            }
  
            /*
             * Show parent Mask
             */
            popup.$m.show();

            /*
             * Create videocontent
             * See http://camendesign.com/code/video_for_everybody for more information
             */
            content = '<video width="'+w+'" height="'+h+'" controls>'
            +'<source src="'+video.url+'" type="'+codec+'" />'
            +'<object width="'+w+'" height="'+h+'" type="application/x-shockwave-flash" data="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf">'
            +'<param name="movie" value="http://releases.flowplayer.org/swf/flowplayer-3.2.1.swf" />'
            +'<param name="allowfullscreen" value="true" />'
            +'<param name="flashvars" value="controlbar=over&amp;image='+img+'&amp;file='+video.url+'" />'
            +'<img src="'+img+'" width="'+w+'" height="'+h+'" alt="'+video.title+'" title="'+video.title+'"/>'
            +'</object>'
            +'</video>'
            +'<p class="vjs-no-video"><a href="'+video.url+'" target="_blank">'+this._("Download Video")+'</a></p>'
            +'</div>';
            popup.$b.html('<div class="imageContent"><div class="padded">'+content+'</div></div>');
            popup.$d.css({
                'left':(window.innerWidth - popup.$d.width()) / 2,
                'top':(window.innerHeight - popup.$d.height()) / 2
            });

            /*
             * Show popup image
             */
            popup.show();

            return true;
        },
        
        /*
         * Convert an input string into the right type
         * (for example "1" will be converted to an integer "true" to a boolean...etc)
         * 
         * @param {String} string : string to convert
         */
        stringToRealType:function(string) {
            
            if (!string) {
                return string;
            }
            
            if ($.isNumeric(string)) {
                return parseFloat(string);
            }
            
            if (string.toLowerCase() === 'true') {
                return true;
            }
            
            if (string.toLowerCase() === 'false') {
                return false;
            }
            
            return string;
        },
        
        /*
         * Return all node attributes without namespaces
         * 
         * @param obj : a jquery element
         */
        getAttributes:function(obj) {
            
            var a, i, l, attributes = {};
            
            if (obj && obj.length) {
                a = obj[0].attributes;
                for (i = 0, l = a.length; i < l; i++) {
                    attributes[M.Util.stripNS(a[i].nodeName)] = M.Util.stringToRealType(a[i].nodeValue);
                }
            }
            
            return attributes;
        },
        
        /*
         * Return nodeName without namespace
         * 
         * @param nodeName : a nodeName (e.g. "toto", "ns:toto", etc.)
         */
        stripNS:function(nodeName) {
            if (!nodeName) {
                return null;
            }
            var s = nodeName.split(':');
            return s.length === 2 ? s[1] : s[0];
        },
        
        /**
         * Strip HTML tags from input string
         *
         * @param {String} html : an html input string
         */
        stripTags: function(html) {
            var tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText;
        },
        
        /**
         * Launch an ajax call
         * This function relies on jquery $.ajax function
         * 
         * @param {Object} obj
         * @param {Object} options
         */
        ajax: function(obj, options) {

            var ajax;
            
            /*
             * Paranoid mode
             */
            if (typeof obj !== "object") {
                return null;
            }

            /*
             * Ask for a Mask
             */
            if (options) {
                
                var id = this.getId();
                
                obj['complete'] = function(c) {
                    M.mask.abort(id);
                };
                
                ajax = $.ajax(obj);
                
                /**
                 * Add information about loading
                 */
                M.mask.add({
                    title:options.title || this._("Processing"),
                    cancel:options.cancel === true ? options.cancel : false,
                    id:id,
                    request:ajax
                });
            }
            else {
                ajax = $.ajax(obj);
            }

            return ajax;
        },
        
        /**
         * Display a modal popup to ask user for
         * a particular value
         * 
         * @param {Object} options : constructor options
         * 
         * 
         * Warning : mandatory options depend on dataType - see comments in the function
         * 
         *   option structure {
         *      title: // popup title
         *      content: // html content appended within Popup <div class='description'></div> DOM element
         *      hint: // hint to displayed within input box
         *      dataType: // dataType
         *      size: // Input box size
         *      value: // default value to be displayed (or enumeration in case of dataType="list")
         *      callback: // callback function on value change
         */
        askFor:function(options) {
            
            options = options || {};
            
            var id,
            data,
            self = this,
            input = [],
            /* Create popup */
            popup = new M.Popup({
                modal:true,
                autoSize:true,
                centered:true,
                header:options.title,
                body:options.content ? options.content : ''
                
            });
            
            /*
             * dataType='list' special case
             * 
             * options.value should be an array of object
             *      {
             *          title: Display item title
             *          value: Value returned on click
             *          icon: // optional
             *      }
             */
            if (options.dataType === "list") {
                
                var el,icon,count = 0;
                
                /*
                 * Roll over items
                 */
                for (var i in options.value) {
                    id = this.getId();
                    el = options.value[i];
                    icon = el.icon ? '<img class="middle" src="'+el.icon+'"/>&nbsp;' : '';
                    popup.append('<a href="#" class="button marged" id="'+id+'">'+icon+el.title+'</a>', 'body');
                    
                    /*
                     * Return item value to callback on click
                     */
                    (function(d, a, c, v){
                        a.click(function(e){
                            if ($.isFunction(c)){
                                c(v);
                            }
                            d.remove();
                            return false;
                        });
                    })(popup, $('#'+id), options.callback, el.value);
                
                    count++;
                }
                
            }
            /*
             * dataType='complexData' special case
             * 
             * Dedicated options are 
             *      
             *      supportedFormat:  // optional
             *      maximumMegaBytes: // optional
             *      file: // File object - optional 
             *      fileUrl: // Url to file - optional
             *      upload: // Set to true to upload selected file on server
             *                 before sending back result to callback function
             *                 In this case, callback function will always get
             *                 a fileUrl back 
             *     
             * Structure of supportedFormat is
             * 
             *      [
             *          {
             *              mimeType://
             *              encoding://
             *              schema://
             *          },
             *          ...
             *      ]
             * 
             * A Drag&Drop zone is set with an hidden OK button
             * When user Drop a valid file, the OK button is shown
             * 
             * 
             */
            else if (options.dataType === "complexData") {
                
                /*
                 * Set validate button
                 */
                id = this.getId();
                
                /*
                 * Set drop zone
                 */
                if (M.DDZone) {
                    new M.DDZone({
                        parent:popup.$b,
                        maximumMegaBytes:options.maximumMegaBytes,
                        supportedFormats:options.supportedFormats,
                        file:options.file,
                        fileUrl:options.fileUrl,
                        success:function(_data) {
                            popup.center();
                            data = _data;
                            $('#'+id).show();
                        }
                    });
                }
                popup.append('<p class="big center padded"><br/><a href="#" class="button inline validate" id="'+id+'">'+M.Util._("Set")+'</a></p>', 'body');
                $('#'+id).click(function(){
                    
                    /*
                     * If options.upload is set to true, then
                     * the dropped file is uploaded to the server
                     * This is equivalent to transform a local 'file'
                     * to a 'fileUrl' - callback function is then
                     * call with a 'fileUrl'
                     * This 
                     *  
                     */
                    if (options.upload && data.file) {
                        self.upload(data.file,{
                            formats:options.supportedFormats,
                            maximumMegabytes:options.maximumMegaBytes,
                            callback:function(items) {
                                
                                /*
                                 * Only one file has been dropped, but the result
                                 * can contains more than one item (it is the case for
                                 * jpeg dropped files for example, where mapshup automatically
                                 * associate a "Photography" layer referenced by a second item
                                 * 
                                 * In this case, we pick up the first item
                                 */
                                if ($.isArray(items)) {
                                    if ($.isFunction(options.callback)) {
                                        options.callback({
                                            fileUrl:items[0].url
                                        });
                                    }
                                    popup.remove();
                                }
                                else {
                                    M.Util.message("Error : cannot upload file on server");
                                }
                            }
                        });
                    }
                    else {
                        if ($.isFunction(options.callback)) {
                            options.callback(data);
                        }
                        popup.remove();
                    }
                });
                
                /*
                 * Hide "set" button
                 */
                if(!options.file && !options.fileUrl) {
                    $('#'+id).hide();
                }
                
            }
            else {
                
                /*
                 * Get unique ids
                 */
                id = this.getId();
                
                /*
                 * Append input text box to body
                 */
                popup.append('<input id="'+id+'" type="text" size="'+(options.size || 10)+'"/>', 'body');
                
                /*
                 * Set default value if defined
                 * Input value is encoded to avoid javascript code injection
                 */
                input = $('#'+id);
                if (options.value) {
                    input.val(this.stripTags(options.value));
                }
                /*
                 * Or set input text box placeholder
                 */
                else if (options.hint) {
                    input.attr('placeholder', options.hint);
                }
                
                /*
                 * Add action on input text box (see fct above)
                 */
                input.focus(function(){
                    this.select();
                }).keypress(function(e){
                    
                    /*
                     * Input value is encoded to avoid javascript code injection
                     */   
                    var isValid = false, v = self.stripTags($(this).val());
                    
                    /*
                     * Close on ESC key
                     */
                    if (e.keyCode === 27) {
                        popup.remove(); 
                    }
                    
                    /*
                     * Return or tab keys
                     */
                    if (e.keyCode === 13 || e.keyCode === 9) {
                        
                        switch(options.dataType.toLowerCase()) {
                            case"date":
                                self.isDateOrInterval(v) || self.isISO8601(v) ? isValid = true : self.message(self._("Expected format is YYYY-MM-DD for a single date or YYYY-MM-DD/YYYY-MM-DD for a date interval"));
                                break;
                            case "bbox":
                                self.isBBOX(v) ? isValid = true : self.message(self._("Expected format is lonmin,latmin,lonmax,latmax"));
                                break;
                            case "integer":
                                self.isInt(v) ?  isValid = true : self.message(self._("Error : not a valid Integer"));
                                break;
                            case "float":
                                self.isFloat(v) ?  isValid = true : self.message(self._("Error : not a valid Float"));
                                break;     
                            case "double":
                                self.isFloat(v) ?  isValid = true : self.message(self._("Error : not a valid Double"));
                                break;
                            case "boolean":
                                self.isBoolean(v) ?  isValid = true : self.message(self._("Error : not a valid Boolean"));
                                break;
                            default:
                                isValid = true;
                        }
                        
                        /*
                         * Send back value to callback function and close popup
                         */
                        if (isValid) {
                            if ($.isFunction(options.callback)) {
                                options.callback(v);
                            }
                            popup.remove(); 
                        }
                        
                        return false;
                        
                    }
                    
                });
                
            }
            
            /*
             * Show the modal window
             */
            popup.show();
            
            /*
             * Set focus on input box if defined
             */
            if (input.length > 0) {
                input.focus();
            }
            
            return popup;
            
        },
        
        /**
         * Modified checksum function used to generate unique MID based on layer description
         * 
         * Based on http://noteslog.com/post/crc32-for-javascript/
         * (c) 2006 Andrea Ercolino http://www.opensource.org/licenses/mit-license.php
         * 
         * @param {String} str : string to coompute checksum on
         * @param {Integer} crc
         */
        crc32: function(str, crc) {
            
            var n = 0, x = 0; // number between 0 and 255
            
            if (crc === window.undefined) {
                crc = 0;
            }
            
            // hex number
            crc = crc ^ (-1);
            for( var i = 0, iTop = str.length; i < iTop; i++ ) {
                n = ( crc ^ str.charCodeAt( i ) ) & 0xFF;
                x = "0x" + "00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D".substr( n * 9, 8 );
                crc = ( crc >>> 8 ) ^ x;
            }
            return "mid" + Math.abs(crc ^ (-1));
        },
        
        /**
         * Update html content of #message div and display it during "duration" ms
         * css('left') is computed each time to reflect map resize
         * 
         * @param {html} content
         * @param {Integer} duration (in milliseconds)
         */
        message: function(content, duration) {
            
            var $d,$content,fct,
            self = this,
            id = self.getId();
            
            /*
             * Create a message container and associate it
             * to a new entry within the messages array
             */
            M.$container.append('<div id="' + id + '" class="message"><div class="content"></div></div>');
            $d = $('#' + id);
            self.messages[id] = $d;
            
            /*
             * Set update position function
             */
            fct = function() {
                
                var $message, message, top = 30;
                
                for (message in self.messages) {
                    $message = self.messages[message];
                    $message.css({
                        'top':top
                    });
                    top = $message.position().top + $message.height() + 5;
                }
                
            };
            
            /*
             * Set content
             */
            $content = $('.content',$d).html(content);
            self.addClose($content,function(e){
                delete self.messages[$d.attr('id')];
                $d.remove();
                fct();
            });
                
            /*
             * duration is set to -1
             * In this case, message is not automatically closed
             */
            if (duration && duration === -1) {
                $d.show();
                fct();
            }
            else {
                $d.fadeIn('slow').delay(duration || 2000).fadeOut('slow', function(){
                    delete self.messages[$d.attr('id')];
                    $d.remove();
                    fct();
                });
            }
            
            $d.css({
                'left': (M.$container.width() - $d.width()) / 2,
                'top' : 30
            });

            return $d;
        },
        
        /**
         * Replace all space,',",.,: and # characters from "str" by "_"
         * 
         * @param {String} str
         */
        encode: function(str) {
            if (!str) {
                return str;
            }
            return str.replace(/[',", ,\.,#,\:,\ ]/g,"_");
        },
    
        /**
         * Return base url (i.e. url without parameters) from an input url
         * E.g. extractBaseUrl("http://myserver.com/test?foo=bar") will return "http://myserver.com/test?"
         * 
         * @param {String} url
         * @param {Array} arr : if arr is not specified remove all url parameters
         *                      otherwiser only remove parameters set in arr
         */
        extractBaseUrl: function(url, arr) {
            
            var baseUrl, u = this.repareUrl(url);
            
            if (!u) {
                return null;
            }
            
            /*
             * Extract base url i.e. everything befor '?'
             */
            baseUrl = u.match(/.+\?/)[0];
            
            if (!arr || arr.length === 0) {
                return baseUrl;
            }
        
            var addToBaseUrl, key, i, l, kvps = this.extractKVP(url, true);
            
            for (key in kvps) {
                addToBaseUrl = true;
                for (i = 0, l = arr.length;i<l;i++) {
                    if (key === arr[i]) {
                        addToBaseUrl = false;
                        break;
                    }
                }
                if (addToBaseUrl) {
                    baseUrl += encodeURIComponent(key) + "=" + encodeURIComponent(kvps[key]) + "&";
                }
            }
        
            return baseUrl;
            
        },
        
        /**
         * Extract Key/Value pair from an url like string
         * (e.g. &lon=123.5&lat=2.3&zoom=5)
         * 
         * @param {String} str
         * @param {boolean} lowerCasedKey
         */
        extractKVP: function(str, lowerCasedKey) {
            var c = {};
            str = str || "";
            str.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                c[decodeURIComponent(lowerCasedKey ? key.toLowerCase() : key )] = (value === undefined) ? true : decodeURIComponent(value);
            });
            return c;
        },
            
        /**
         * Return an absolute URL.
         * If input url starts with '/', it is assumed that input url
         * is relative to the Config.general.serverRootUrl
         * 
         *  @param {String} url
         */
        getAbsoluteUrl: function(url) {

            /*
             * url is absolute => return url
             * else return absolute url
             */
            if (url && M.Config["general"].serverRootUrl) {

                /*
                 * Be carefull of array of url !
                 */
                if (typeof url === "object") {
                    return url;
                }
                
                /*
                 * If url does not start with '/' then returns it without modification
                 */
                return (this.isUrl(url) || url.substr(0,1) !== '/') ? url : M.Config["general"].serverRootUrl + url;
                
            }
            
            return url;
        },

        /**
         * Return a unique id
         */
        getId: function() {
            return "mid"+this.sequence++;
        },
        
        /**
         * Return a unique id
         * 
         * @param {String} f : fileName
         */
        getImgUrl: function(f) {
            
            /*
             * fileName is returned as is if it is :
             *  - null
             *  - a fully qualified url (i.e. starts with http:)
             *  - a base64 encoded image stream (i.e. starts with data:)
             */
            return (!f || f.substr(0,1) === '/' || f.substr(0,2) === './' || f.substr(0,3) === '../' || f.substr(0,5) ==='http:' || f.substr(0,6) ==='https:' || f.substr(0,5) ==='data:') ? f : M.Config["general"].rootUrl + M.Config["general"].themePath+"/img/"+f;
        },
        
        /**
         * Return the obj.property value if defined or value if not
         * 
         * @param {Object} obj
         * @param {Object} property
         * @param {String} value
         */
        getPropertyValue: function(obj, property, value) {
            
            /*
             * This should never happened...
             */
            if (obj === undefined || property === undefined) {
                return undefined;
            }
            
            /*
             * Property is set, returns its value
             */
            if (obj.hasOwnProperty(property)) {
                return obj[property];
            }
            
            /*
             * Returns the input value
             */
            return value;
        },
        
        /**
         * 
         * A valid BBOX object should be defined as
         *      {
         *          bounds:"xmin,ymin,xmax,ymax"
         *          srs: // optional if srs is specified
         *          crs: // optional is crs is specified
         *      }
         *      
         *  If input bbox is valid, then this function returns
         *  the unmodified bbox
         *  
         *  Else if input bbox is a string as "xmin,ymin,xmax,ymax" then
         *  this function returns the following object
         *      {
         *          bounds: input bbox,
         *          srs:"EPSG:4326"
         *      }
         *  
         *  Otherwise, this function returns the default value
         *  
         *  @param {Object} bbox
         *  @param {Object} value
         *  
         */
        getValidBBOX: function(bbox, value) {
            
            /*
             * Paranoid mode
             */
            if (bbox === undefined) {
                return value;
            }
            
           /*
            * If input bbox is a String then it is supposed that the input string corresponds
            * to an EPSG:4326 string (i.e. lonMin,latMin,lonMax,latMax)
            */
            if (typeof bbox === "string") {
                bbox = {
                    bounds: bbox,
                    srs: 'EPSG:4326'
                };
            }
            
            /*
             * Property is set, returns its value
             */
            if (bbox.hasOwnProperty("bounds")) {
                return bbox;
            }
            
            /*
             * Returns the input value
             */
            return value;
        },
            
        /**
         * Return true if input value is a boolean
         * i.e. an integer number with value
         * between -9007199254740990 to 9007199254740990
         * 
         *  @param {String} n
         */
        isBoolean: function(n) {
            if (typeof n === "boolean") {
                return true;
            }
            return n === "true" || n === "false" ? true : false;
        },
        
        
        /**
         * Return true if input value is an integer
         * i.e. an integer number with value
         * between -9007199254740990 to 9007199254740990
         * 
         *  @param {String} n
         */
        isInt: function(n) {
            if (!$.isNumeric(n)) {
                return false;
            }
            return !(parseFloat(n) % 1);
        },
         
        /**
         * Return true if input value is a float
         * i.e. a real number including Infinity and -Infinity but not NaN
         * 
         *  @param {String} n
         */
        isFloat: function(n) {
            return $.isNumeric(n);
        },
         
        /**
         * Check if a string is a valid BBOX
         * A valid BBOX is :
         *   - 4 decimal values comma separated A,B,C,D
         *   - A < C
         *   - B < D
         *   
         * @param {String} str
         */
        isBBOX: function (str) {
            if (!str || str.length === 0) {
                return false;
            }
            var arr = str.split(',');
            if (arr.length !== 4) {
                return false;
            }
            for (var i=0;i<3;i++) {
                if (!$.isNumeric(arr[i])) {
                    return false;
                }
            }
            if (parseFloat(arr[0]) > parseFloat(arr[2]) || parseFloat(arr[1]) > parseFloat(arr[3])) {
                return false;
            }
            return true;
        },

        /**
         * Check if a string is a valid date i.e. YYYY-MM-DD
         * 
         * @param {String} str
         */
        isDate: function(str) {

            /*
             * Paranoid mode
             */
            if (!str || str.length === 0) {
                return false;
            }

            /*
             * Days in month
             */
            var daysInMonth = [31,29,31,30,31,30,31,31,30,31,30,31],
            elements = str.split('-');
            
            /*
             * Format is YYYY-MM-DD
             */
            if (elements.length !== 3) {
                return false;
            }

            var y = parseInt(elements[0], 10),
            m = parseInt(elements[1], 10),
            d = parseInt(elements[2], 10);

            /*
             * February has 29 days in any year evenly divisible by four,
             * EXCEPT for centurial years which are not also divisible by 400.
             */
            var daysInFebruary = ((y % 4 === 0) && ( (!(y % 100 === 0)) || (y % 400 === 0))) ? 29 : 28;

            if (elements[0].length !==4 || y < 1900 || y > 2100) {
                return false;
            }
            if (elements[1].length !==2 || m < 1 || m > 12) {
                return false;
            }
            if (elements[2].length !==2 || d < 1 || d > 31 || (m === 2 && d > daysInFebruary) || d > daysInMonth[m]) {
                return false;
            }

            return true;
        },
        
        /**
         * Check if a string is a valid date or date interval
         * i.e. YYYY-MM-DD or YYYY-MM-DD/YYYY-MM-DD
         * 
         * @param {String} str
         */
        isDateOrInterval:function(str) {
            
            /*
             * Paranoid mode
             */
            if (str === undefined || str.length === 0) {
                return false;
            }
            
            /*
             * Input strDate can be a date or an interval
             * YYYY-MM-DD or YYYY-MM-DD/YYYY-MM-DD
             */
            var dates = str.split('/'),
            empty = 0,
            i,
            l;
                
            if (dates.length > 2) {
                return false;
            }
            
            /*
             * Roll over dates
             */
            for (i = 0, l = dates.length; i < l; i++) {
                
                /*
                 * Date is not valid return false
                 * unless it is empty (see below)
                 */
                if (!this.isDate(dates[i])) {
                    if (dates[i] === "") {
                        empty++;
                    }
                    else {
                        return false;
                    }
                }
            }
            
            /*
             * We want to allow unclosed interval
             * i.e. YYYY-MM-DD/ and /YYYY-MM-DD
             * so if only one empty is present the 
             * interval is still valid
             */
            if (dates.length - empty < 1) {
                return false;
            } 
            
            return true;

        },
        
        /**
         * Check if a string is a valid email adress
         * 
         * @param {String} str
         */
        isEmailAdress: function (str) {
            if (!str || str.length === 0) {
                return false;
            }
            var pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/;
            return pattern.test(str);
            
        },
        
        /**
         * Check if input mimeType is supported
         * 
         * @param {Array} formats : // Array of {mimeType://}
         * @param {String} mimeType
         */
        isSupportedMimeType:function(formats, mimeType) {
            
            formats = formats || [];
            
            var i, l = formats.length;
            
            /*
             * If no supportedFormats are defined, then
             * it supposes that every format is supported
             */
            if (l === 0) {
                return true;
            }

            for (i = 0; i < l; i++) {
                if (formats[i].mimeType.toLowerCase() === mimeType.toLowerCase()) {
                    return true;
                }
            }
            
            return false;
            
        },
        
        /**
         * Check if a string is a valid ISO8601 date or interval
         * i.e. YYYY-MM-DDTHH:mm:ss or YYYY-MM-DDTHH:mm:ss/YYYY-MM-DDTHH:mm:ss
         * 
         * @param {String} str
         */
        isISO8601: function (str) {

            /*
             * Paranoid mode
             */
            if (str === undefined || str.length === 0) {
                return false;
            }
            
            /*
             * Input strDate can be a date or an interval
             * YYYY-MM-DDTHH:mm:ss or YYYY-MM-DDTHH:mm:ss/YYYY-MM-DDTHH:mm:ss
             */
            var isos = str.split('/');
            if (isos.length > 2) {
                return false;
            }

            var arr;

            /*
             * Roll over iso8601s
             */
            for (var i = 0, l = isos.length; i < l; i++) {
                arr = isos[i].split('T');
                if (arr.length !== 2) {
                    return false;
                }
                if (!this.isDate(arr[0])) {
                    return false;
                }
                if (!this.isTime(arr[1])) {
                    return false;
                }
            }

            return true;

        },
        
        /**
         * Check if a string is a valid time i.e. HH:mm:ss or HH:mm:ssZ
         * 
         * @param {String} str
         */
        isTime: function(str) {

            /*
             * Paranoid mode
             */
            if (!str || str.length === 0) {
                return false;
            }

            /*
             * Remove last character if it's a Z
             */
            if (str.indexOf('Z') === str.length - 1) {
                str = str.substring(0,str.length - 1);
            }

            /*
             * Format is HH:mm:ss.mmm
             */
            var elements = str.split(':');
            if (elements.length !== 3) {
                return false;
            }

            var h = parseInt(elements[0], 10),
            m = parseInt(elements[1], 10),
            s = parseInt(elements[2], 10);
            
            if (elements[0].length !==2 || h < 0 || h > 23) {
                return false;
            }
            if (elements[1].length !==2 || m < 0 || m > 59) {
                return false;
            }
            if (elements[2].length !==2 || s < 0 || s > 59) {
                return false;
            }

            return true;
        },
        
        /*
         * Return true if input string is a valid url
         */
        isUrl: function(str) {
            
            if (str && typeof str === "string") {
                
                var s = str.substr(0,7);
                
                if (s === 'http://' || s === 'https:/' || s === 'ftp://') {
                    return true;
                }
                
                if (str.substr(0,2) === '//') {
                    return true;
                }
            }
                
            return false;
            
        },
        
        /**
         * Add pagination info to an input url
         *
         * @param {String} url : url to paginate
         * @param {Object} p : pagination info
         *                  {
         *                      nextRecord:{
         *                          name: // name of the nextRecord key
         *                          value: // value of nextRecord
         *                      },
         *                      numRecordsPerPage:{
         *                          name: // name of the numRecordsPerPage key
         *                          value: // value of numRecordsPerPage
         *                      }
         *                  }
         *                  
         */
        paginate: function(url, p) {
            if (p) {
                if (p.nextRecord) {
                    url = this.repareUrl(url) + p.nextRecord.name + "=" + p.nextRecord.value;
                }
                if (p.numRecordsPerPage) {
                    url = this.repareUrl(url) + p.numRecordsPerPage.name + "=" + p.numRecordsPerPage.value;
                }
            }
            return url;
        },
        
        
        /**
         * Return a "proxified" version of input url
         *
         * @param {String} url : url to proxify
         * @param {String} returntype : force HTTP header to the return type //optional
         */
        proxify: function(url, returntype) {
            
            /*
             * If proxyUrl is set then proxify input url
             */
            if (M.Config["general"].proxyUrl && M.Config["general"].proxyUrl !== "") {
                return this.getAbsoluteUrl(M.Config["general"].proxyUrl)+this.abc+(returntype ? "&returntype="+returntype : "")+"&url="+encodeURIComponent(url);
            }
            
            /*
             * otherwise, do nothing i.e. just return unmodified url
             */
            return url;
        },
        
        /**
         * Return a random color hex value
         */
        randomColor: function(){
            var colors = ["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"],
            d1 = "",
            d2 = "",
            d3 = "",
            i;
            for (i=0;i<2;i++) {
                d1=d1+colors[Math.round(Math.random()*15)];
                d2=d2+colors[Math.round(Math.random()*15)];
                d3=d3+colors[Math.round(Math.random()*15)];
            }
            return '#'+d1+d2+d3;
        },

        /**
         * Parse a string containing keys between dollars $$ and replace these
         * keys with obj properties.
         * Example :
         *      str = "Hello my name is $name$ $surname$"
         *      keys = {name:"Jerome", surname:"Gasperi"}
         *      modifiers = {name:{transform:function(v){...}}
         *
         *      will return "Hello my name is Jerome Gasperi"
         *
         * @param {String} template : template with keys to process
         * @param {Object} keys : object containing the property keys/values
         * @param {Object} modifiers : object containing the property keys
         */
        parseTemplate: function (template, keys, modifiers) {

            /*
             * Paranoid mode
             */
            keys = keys || {};
            modifiers = modifiers || {};
            
            /*
             * Be sure that str is a string
             */
            if (typeof template === "string") {

                /*
                 * Replace all $key$ within string by obj[key] value
                 */
                return template.replace(/\$+([^\$])+\$/g, function(m) {
                    
                    var k,
                    key = m.replace(/[\$\$]/g, ''),
                    value = keys[key];
                        
                    /*
                    * Roll over the modifiers associative array.
                    * 
                    * Associative array entry is the key
                    * 
                    * This array contains a list of objects
                    * {
                    *      transform: // function to apply to value before replace it
                    *            this function should returns a string
                    * }
                    */
                    for (k in modifiers) {

                        /*
                        * If key is found in array, get the corresponding value and exist the loop
                        */
                        if (key === k) {

                            /*
                            * Transform value if specified
                            */
                            if ($.isFunction(modifiers[k].transform)) {
                                return modifiers[k].transform(value);
                            }
                            break;
                        }
                    }

                    /*
                    * Return value or unmodified key if value is null
                    */
                    return value != null ? value : "$"+key+"$";
                    
                });
                
            }

            return template;

        },
        
        /**
         * Repare a wrong URL regarding the following principles :
         *
         *  - If no "?" character is found, returns url+"?"
         *  - else if last character is "?" or "&", returns url
         *  - else if a "?" character is found but the last character is not "&", returns url+"&"
         *
         * @param {String} url
         */
        repareUrl: function(url) {
            if (!url) {
                return null;
            }
            var questionMark = url.indexOf("?");
            if (questionMark === -1) {
                return url+"?";
            }
            else if (questionMark === url.length - 1 || url.indexOf("&",url.length - 1) !== -1) {
                return url;
            }
            else {
                return url+"&";
            }
        },

        /**
         * Reduce the length of a string "str" to "sizemax"
         *
         * @param {String} str : String to reduce
         * @param {Integer} sizemax : maximum length of the returned string
         * @param {boolean} end : 'true'  the end of str is shrinked
         *                        'false' the middle of str is shrinked
         */
        shorten: function(str,sizemax,end) {
            
            if (!str) {
                return null;
            }
            
            var ta = document.createElement("textarea");
            ta.innerHTML = str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
            str = ta.value;
            ta = null;
            end = end || false;
            
            if (str) {
                
                var size = str.length;
                
                /*
                 * Input string length is lower than sizemax.
                 * Return input string
                 */
                if (size <= sizemax) {
                    return str;
                }

                /*
                 * Shrink the end of the input string
                 */
                if (end) {
                    return str.substring(0,sizemax - 3)+"...";
                }

                /*
                 * Default : shrink the middle of input string
                 */
                var halfsize = Math.round(sizemax/2);
                return str.substring(0,halfsize-1)+"..."+str.substring(size-halfsize+3);
            }
            return str;
        },
        
        /**
         * Find "shorten_sizemax" class within a $div and reduce the length of
         * the class content text() to sizemax (30 by default)
         *
         * @param {jQuery} $div
         * @param {boolean} end : 'true'  the end of str is shrinked
         *                        'false' the middle of str is shrinked
         */
        findAndShorten:function($div, end) {
            
            /*
             * Find shorten_*
             */
            $('[class^=shorten]', $div).each(function(){
                
                // Find shorten classname
                var i, l, sizemax = 30, names = $(this).attr("class").split(" ");
                for (i = 0, l = names.length; i < l; i++) {
                    if (names[i].indexOf("shorten") === 0) {
                        sizemax = parseInt(names[i].split("_")[1]);
                    }
                }
                $(this).html(M.Util.shorten($(this).text(), sizemax, end));
            });
        
        },
            
        /**
         * Sort input array in alphabetical order
         * using key property
         * 
         * @param {Array} arr
         * @param {String} key
         */ 
        sortArray:function(arr, key) {
            if (typeof arr === "object" && arr.length && key) {
                var scope = this;
                arr.sort(function(a,b){
                    var nameA = scope._(a[key]).toLowerCase();
                    var nameB = scope._(b[key]).toLowerCase();
                    if (nameA < nameB) {
                        return -1;
                    }
                    if (nameA > nameB) {
                        return 1;
                    }
                    return 0;
                });
            }   
        },
        
        /*
         * Convert an XML text to XML object
         */
        textToXML: function(text) {
            try {
                var parser, found, xml = null;

                if ( window.DOMParser ) {

                    parser = new DOMParser();
                    xml = parser.parseFromString( text, "text/xml" );

                    found = xml.getElementsByTagName( "parsererror" );

                    if ( !found || !found.length || !found[ 0 ].childNodes.length ) {
                        return xml;
                    }

                    return null;
                }
                else {

                    xml = new ActiveXObject( "Microsoft.XMLDOM" );

                    xml.async = false;
                    xml.loadXML( text );

                    return xml;
                }
            } catch (e) {
                return null;
            }
        },
        
        lowerFirstLetter:function(string) {
            return string.charAt(0).toLowerCase() + string.slice(1);
        },
        
        /**
         * Upload files to server
         *
         * @param {Array} files : array of files to upload
         * @param {Object} options : upload options
         *                          {
         *                              formats: // Array of {mimeType:}
         *                              maximumMegabytes: // max size for uploaded file // MANDATORY
         *                              callback: callback function on successfull upload
         *                          }
         * 
         */
        upload:function(files, options) {

            var i, l, form, validFiles = [], self = this;
            
            options = options || {};
            
            /*
             * input files should be an array
             */
            if (!$.isArray(files)) {
                files = [files];
            }
            
            /*
             * Tell user that we are processing things !
             */
            M.mask.add({
                title:this._("Upload files")+"...",
                cancel:false
            });

            /*
             * Roll over files and check validity
             */
            $(files).each(function(key, file) {

                /*
                 * mimeType checking
                 */
                if (!self.isSupportedMimeType(options.formats, file.type)) {
                    self.message(M.Util._("Error : mimeType is not supported")+ ' ['+file.type+']');
                    return false;
                }
                /*
                 * size checking
                 */
                if (file.size/1048576 > options.maximumMegabytes) {
                    self.message(M.Util._("Error : file is to big")+ ' ['+file.name+']');
                    return false;
                }
                
                /*
                 * Add a valid file
                 */
                validFiles.push(file);
                
                return true;
            });
            
            /*
             * Upload validFiles
             */
            if (validFiles.length > 0) {

                /*
                 * Work-around for Safari occasionally hanging when doing a
                 * file upload.  For some reason, an additional HTTP request for a blank
                 * page prior to sending the form will force Safari to work correctly.
                 *
                 * See : http://www.smilingsouls.net/Blog/20110413023355.html
                 */
                $.get('./blank.html');

                var http = new XMLHttpRequest();

                /*
                 * Listen the end of the process
                 */
                http.onreadystatechange = function() {

                    var result;
                    
                    /*
                     * End of the process is readyState 4
                     * Successfull status is 200 or 0 (for localhost)
                     */
                    if (http.readyState === 4 && (http.status === 200 || http.status === 0)) {

                        M.mask.hide();

                        result = JSON.parse(http.responseText);

                        /*
                         * In case of success, roll over processed items
                         */
                        if (result.error) {
                            M.Util.message(result.error["message"]);
                        }
                        else {
                            if (result.items) {
                                if ($.isFunction(options.callback)) {
                                    options.callback(result.items);
                                }
                            }
                        }
                    }
                };

                if (typeof(FormData) !== 'undefined') {

                    form = new FormData();
                    form.append('path', '/');

                    for (i = 0, l = files.length; i < l; i++) {
                        form.append('file[]', files[i]);
                    }
                    
                    http.open('POST', M.Util.getAbsoluteUrl(M.Config["upload"].serviceUrl)+M.Util.abc+"&magic=true");
                    http.send(form);
                } else {
                    M.Util.message('Error : your browser does not support HTML5 Drag and Drop');
                }
            }
            else {
                M.Util.message('Error : this file type is not allowed');
                M.mask.hide();
            }
        }
        
    };

})(window, window.M, window.document);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Define mapshup events
 * 
 * @param {MapshupObject} M
 */
(function (M) {
   
    M.Events = function() {

        /*
         * Only one Events object instance is created
         */
        if (M.Events._o) {
            return M.Events._o;
        }
        
        /*
         * Set events hashtable
         */
        this.events = {
            
            /*
             * Array containing handlers to be call after
             * a map resize
             */
            resizeend:[],
            
            /*
             * Array containing handlers to be call after
             * a successfull signIn - See UserManagement plugin
             */
            signin: [],
                    
            /*
             * Array containing handlers to be call after
             * a successfull signOut - See UserManagement plugin
             */
            signout: []
        };
        
        

        /*
         * Register an event to jMap
         *
         * @param <String> eventname : Event name => 'resizeend'
         * @param <function> handler : handler attached to this event
         */
        this.register = function(eventname , scope, handler) {
            
            if (this.events[eventname]) {
                this.events[eventname].push({
                    scope:scope,
                    handler:handler
                });
            }
            
        };

        /*
         * Unregister event
         */
        this.unRegister = function(scope) {
            
            var a,i,key,l;
                
            for (key in this.events) {
                a = this.events[key];
                for (i = 0, l = a.length; i < l; i++) {
                    if (a[i].scope === scope) {
                        a.splice(i,1);
                        break;
                    }
                }
            }
        
        };
        
        /*
         * Trigger handlers related to an event
         *
         * @param <String> eventname : Event name => 'resizeend'
         * @param <Object> obj : extra object passed to the handler
         */
        this.trigger = function(eventname, obj) {

            var i, a = this.events[eventname];

            /*
             * Trigger event to each handlers
             */
            if (a) {
                for (i = a.length; i--; ) {
                    a[i].handler(a[i].scope, obj);
                }
            }
        };

        /*
         * Create unique object instance
         */
        M.Events._o = this;
        
        return this;

    };

})(window.M);
/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * mapshup Activity object
 */
(function(M) {
    
    
    M.Activity = function() {
        
        /*
         * Only one Activity object instance is created
         */
        if (M.Activity._o) {
            return M.Activity._o;
        }
        
        /**
         * Timer identifier
         */
        this.timer = 1;

        /**
         * Activity.png icon is made of 12 frames
         * from 1 to 12. Each frame is 40px height
         */
        this.frame = 1;
        
        /*
         * Hide activity div
         */
        this.hide = function() {
            this.$a.hide();
        };
         
        this.init = function() {
            
            var scope = this;
            
            /*
             * Set the container
             */
            this.$c = M.$container;
            
            /*
             * Create activity div
             */
            this.$a = M.Util.$$('#activity', this.$c).css(
            {
                'display':'none',
                'position':'fixed',
                'top':'50%',
                'left':'50%',
                'width':'40px',
                'height':'40px',
                'margin-top':'-20px',
                'margin-left':'0px',
                'overflow':'hidden',
                'z-index':'38500'
            }).html('<div></div>')
            
            /*
             * Set div content
             */
            this.$a.children().css({
                'position':'absolute',
                'top':'0',
                'left':'0',
                'width':'40px',
                'height':'480px',
                'background-image': "url('"+M.Util.getImgUrl('activity.png')+"')" 
            });
            
            /*
             * Add event on resize
             */
            M.events.register("resizeend", this, function(scope) {
                scope.$a.css({
                    'top': scope.$c.offset().top + (scope.$c.height() - scope.$a.height()) / 2,
                    'left': scope.$c.offset().left + (scope.$c.width() - scope.$a.width()) / 2
                });
            });
            
        };
    
        /*
         * Show activity div
         */
        this.show = function() {

            var self = this;
            
            /*
             * Remove timer
             */
            clearInterval(this.timer);

            /*
             * Show Activity icon
             */
            this.$a.show();
            
            /*
             * Respawn a timer of 66 ms
             */
            this.timer = setInterval(function() {

                /*
                 * If #activity is hidden, remove the timer
                 */
                if (!self.$a.is(':visible')){
                    clearInterval(self.timer);
                    return;
                }

                /*
                 * Iterate through Activity.png frames.
                 * Activity.png is made of 12 frames with a height of 40px
                 */
                $('div', self.$a).css('top', (self.frame * -40) + 'px');
                self.frame = (self.frame + 1) % 12;
            }, 66);
        };
        
        /*
         * Initialize object
         */
        this.init();
        
        /*
         * Create unique instance
         */
        M.Activity._o = this;
        
        return this;
        
    }
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * Toolbar
 *
 * Add a toolbar to the map
 * Toolbar is populated by items
 *
 * @param {MapshupObject} M
 */
(function(M) {

    M.Toolbar = function(options) {

        /*
         * List of toolbar items
         */
        this.items = [];

        /*
         * CSS classes name to add to the created toolbar
         */
        this.classes = options.classes;

        /*
         * Toolbar orientation can be
         *  - h (horizontal)
         *  - v (vertical)
         *  
         *  Default is no orientation
         */
        this.orientation = M.Util.getPropertyValue(options, "orientation", null);

        /*
         * Toolbar div is created within its parent in the DOM
         */
        this.parent = M.Util.getPropertyValue(options, "parent", M.$map.parent());

        /*
         * Toolbar pre-defined position can be
         * 
         *  - ne (north east)
         *  - nw (north west)
         *  - se (south east)
         *  - sw (south west)
         *  
         *  (Default is no position)
         */
        this.position = M.Util.getPropertyValue(options, "position", null);

        /**
         * Initialize toolbar
         * 
         * @param {Object} options
         */
        this.init = function(options) {

            var classes = 'tb', self = this,
                    uid = '_o' + (this.position ? self.position : M.Util.getId()) + 'tb';

            /*
             * mapshup can have one and only one toolbar
             * for each position (i.e. nw, ne, nn, sw, se, ss) which
             * are stored respectively under M.Toolbar._onwtb, M.Toolbar._onetb,
             * M.Toolbar._onntb,M.Toolbar._oswtb, M.Toolbar._osetb and M.Toolbar._osstb,.
             * 
             * If an already initialized toolbar is requested then
             * it is returned instead of creating an new toolbar
             * 
             * IMPORTANT: note that the orientation is never changed
             * i.e. if for example M.Toolbar._onwtb has been initialized as
             * a horizontal toolbar, any new nwtb toolbar created will
             * in fact returned this toolbar and thus the orientation parameter
             * will be ignored.
             */
            if (M.Toolbar[uid]) {
                return M.Toolbar[uid];
            }
            /*
             * Create unique toolbar reference
             */
            else {
                M.Toolbar[uid] = self;
            }

            /*
             * If position is set then create a toolbar div within #mapcontainer i.e. M.$map.parent()
             * Otherwise, just create the div without position constraint
             * 
             * Toolbar is a div container of <div class="item"> divs
             * 
             * Structure :
             *  <div class="tb">
             *      <div class="item"></div>
             *      <div class="item"></div>
             *      ...
             *  </div>
             *  
             */
            self.$d = M.Util.$$('#' + M.Util.getId(), self.parent);

            /*
             * Pre-defined toolbar are absolutely positionned
             */
            if (self.position) {
                self.$d.css({
                    'position': 'absolute',
                    'z-index': '19500'
                });
            }

            /*
             * Add classes
             */
            if (self.orientation) {
                classes += ' tb' + self.orientation;
            }
            if (self.position) {
                classes += ' tb' + self.position + (self.orientation ? self.orientation : 'h');
            }

            self.$d.addClass(classes + (self.classes ? ' ' + self.classes : ''));

            /*
             * Initialize items
             */
            if ($.isArray(options.items)) {
                for (var i = 0, l = options.items.length; i < l; i++) {
                    self.add(options.items[i]);
                }
            }

            return self;
        };

        /**
         * Add an item to the toolbar
         * (i.e. a <div class="item"> in this.$d
         *
         * @param {Object} item : item
         * 
         * (see Toolbar.Item for item structure)
         * 
         */
        this.add = function(item) {

            var tbItem, self = this;

            /*
             * Create a Toolbar Item
             */
            tbItem = new M.Toolbar.Item(self, item);

            /*
             * Add a new item
             */
            self.items.push(tbItem);

            /*
             * Return the newly created action div
             */
            return tbItem;

        };

        /*
         * Activate Toolbar Item identified by id
         * 
         * @param id : item to activate/deactivate
         * @param activate: true to activate, false to deactivate
         */
        this.activate = function(id, activate) {

            var self = this, tbItem = self.get(id);

            if (tbItem) {
                tbItem.activate(activate);
            }

        };

        /*
         * Return Toolbar Item identified by id
         */
        this.get = function(id) {
            for (var i = 0, l = this.items.length; i < l; i++) {
                if (this.items[i].id === id) {
                    return this.items[i];
                }
            }
            return null;
        };

        /*
         * Remove Toolbar Item
         */
        this.remove = function(id) {

            var i, l, self = this;

            for (i = 0, l = self.items.length; i < l; i++) {
                if (self.items[i].id === id) {
                    self.items[i].$d.remove();
                    self.items.splice(i, 1);
                    break;
                }
            }

        };
        
        /*
         * Remove all Toolbar items
         */
        this.clear = function() {
            
            for (var i = 0, l = this.items.length; i < l; i++) {
                this.items[i].$d.remove();
            }
            
            this.items = [];
        
        };
    
        /*
         * Initialize object
         */
        return this.init(options);

    };

    /**
     * Toolbar Item options
     * 
     * @param {Toolbar} tb
     * @param {Object} options
     * 
     * options structure
     *  {
     *      onoff: // boolean  - if true, click on item trigger 'activate' or 'unactivate'
     *                           When item is activated, other toolbar items are unactivated
     *                              
     *                           if false, click on item always trigger 'activate'
     *                           (default true)
     *                              
     *      onactivate: // function to call on activate
     *      ondeactivate: // function to call on deactivate
     *      e: // Extras properties - Properties under this property can be anything
     *      first:// boolean - if true item is added as the first element of the toolbar
     *                       - if false item is added at the end of the toolbar
     *                       (default false)
     *      icon: // Url to the icon image (if no text)
     *      id: // Unique identifier for the <li> element. Automatically created if not given
     *      nohover: // if true, item is not sensitive to onmouseover event
     *      title: // Text displayed within the item display (if no icon specifified)
     *      tt: // Text displayed on mouse over
     *      scope: // reference to the calling plugin
     *  }
     *
     */
    M.Toolbar.Item = function(tb, options) {

        /*
         * Paranoid mode
         */
        options = options || {};

        /*
         * If true, click on button alternate active/unactive
         */
        this.onoff = options.hasOwnProperty("onoff") ? options.onoff : true;

        /*
         * Callback function called on activate
         */
        this.onactivate = options.onactivate;

        /*
         * Callback function called on deactivate
         */
        this.ondeactivate = options.ondeactivate;
        
        /*
         * Extra properties container
         */
        this.e = options.e || {};

        /*
         * Extra properties container
         */
        this.first = M.Util.getPropertyValue(options, "first", false);

        /*
         * Url to the button icon image 
         */
        this.icon = options.icon;

        /*
         * Unique identifier for this element. Automatically created if not given
         * !! THIS ID IS DIFFERENT FROM THE ID OF THE CREATED jquery $d ELEMENT !!
         */
        this.id = options.id || M.Util.getId();

        /*
         * Boolean. If true, button is not sensitive to onmouseover event
         */
        this.nohover = options.nohover || false;

        /*
         * Toolbar reference 
         */
        this.tb = tb;

        /*
         * Textual content of the button
         */
        this.title = options.title;

        /*
         * Title to display on tooltip 
         */
        this.tt = options.tt || "";

        /*
         * Plugin scope reference
         */
        this.scope = options.scope;

        /*
         * Initialize Item
         */
        this.init = function() {

            var orientation, uid, content, p, self = this;

            /*
             * No toolbar - no Toolbar Item
             */
            if (!self.tb) {
                return null;
            }

            /*
             * Set extras properties
             */
            for (p in self.e) {
                if (self.e.hasOwnProperty(p)) {
                    self[p] = self.e[p];
                }
            }

            /*
             * Delete self.e
             */
            delete self.e;

            /*
             * Add a <li> element to toolbar
             * If button 'first' property is set to true,
             * the button is added at the beginning of the toolbar
             * otherwise it is added at the end of the toolbar
             */
            uid = M.Util.getId();
            content = '<div class="' + (self.nohover ? "" : "hover ") + 'item" jtitle="' + (M.Util._(self.tt) || "") + '" id="' + uid + '">' + (self.title ? self.title : '<img class="middle" alt="" src="' + M.Util.getImgUrl(self.icon || "empty.png") + '"/>') + '</div>';
            
            self.first ? self.tb.$d.prepend(content) : self.tb.$d.append(content);

            /*
             * Get newly created div reference
             */
            self.$d = $('#' + uid);

            /*
             * Activate/Deactive item on click on <li> element
             */
            self.$d.click(function() {
                self.onoff ? self.activate(!self.$d.hasClass('active')) : self.activate(true);
                return false;
            });

            /*
             * Add a tooltip depending on orientation
             */
            if (self.tt) {
                orientation = self.tb.orientation || 'h';
                if (self.tb.position) {
                    M.tooltip.add(self.$d, orientation === 'h' ? self.tb.position.substr(0, 1) : self.tb.position.substr(1, 2));
                }
                else {
                    M.tooltip.add(self.$d, orientation === 'h' ? 'n' : 'e');
                }
            }

            return this;
        };

        /*
         * Trigger the button
         */
        this.trigger = function() {
            this.$d.trigger('click');
        };

        /*
         * Activate or deactivate item
         * 
         * @param b : boolean - true to activate, false to deactivate
         */
        this.activate = function(b) {

            var i, l, item, self = this;

            /*
             * Activate item
             */
            if (b) {

                /*
                 * Remove all 'active' class from toolbar items
                 */
                for (i = 0, l = self.tb.items.length; i < l; i++) {
                    item = self.tb.items[i];
                    if (item.id !== self.id) {

                        /*
                         * Callback is defined on deactivate
                         */
                        if ($.isFunction(item.ondeactivate)) {
                            item.ondeactivate(item.scope, item);
                        }

                        item.$d.removeClass('active');

                    }
                }
                
                self.$d.addClass("active");
                
                /*
                 * Callback is defined on activate
                 */
                if ($.isFunction(self.onactivate)) {
                    self.onactivate(self.scope, self);
                }

            }
            else {

                /*
                 * Remove 'active' class from item
                 */
                self.$d.removeClass("active");
            
                /*
                 * Callback is defined on deactivate
                 */
                if ($.isFunction(self.ondeactivate)) {
                    self.ondeactivate(self.scope, self);
                }

            }

            return true;

        };

        /*
         * Initialize object
         */
        return this.init();

    };

})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * mapshup Mask
 */
(function (M) {

    M.Mask = function () {

        /*
         * Only one Mask object instance is created
         */
        if (M.Mask._o) {
            return M.Mask._o;
        }
  
        /**
         * Generaly only one request should be active at a time
         * but in some cases you can have multiple active requests
         * (e.g. a searchAll() on more than one "Catalog" layers
         */
        this.activeRequests = [];

        /*
         * Initialize Mask object
         */
        this.init = function() {
            
            /*
             * Create #jMask
             */
            this.$m = M.Util.$$('#'+M.Util.getId(),M.$container).addClass("mask").css(
            {
                'position':'absolute',
                'display':'none',
                'left':'0',
                'top':'0',
                'width':'100%',
                'height':'100%',
                'z-index':'35000'
            }).html('<div class="content"><div class="loading"></div></div>');
    
            /*
             * Reference to .mask .loading class
             */
            this.$l = $('.loading', this.$m);
            
        };
        
        /**
         * Abort request id
         *
         * @param <string> id : request identifier - if not provided, all requests are aborted
         */
        this.abort = function(id) {

            /**
             * Roll over activeRequests
             */
            for (var key in this.activeRequests) {


                /**
                 * id is not defined => abort all requests
                 */
                if (!id) {
                    if (this.activeRequests[key]) {
                        this.activeRequests[key].abort();
                    }
                    delete this.activeRequests[key];
                    $('#'+key).remove();
                }
                /**
                 * id is defined => only abort the corresponding request
                 */
                else {
                    if (key === id) {
                        if (this.activeRequests[key]) {
                            this.activeRequests[key].abort();
                        }
                        delete this.activeRequests[key];
                        $('#'+key).remove();
                        break;
                    }
                }
            }

            /*
             * This case occurs for ids without active requests
             */
            $('#'+id).remove();

            /**
             * No more active requests ?
             * => hide the Mask
             */
            if (this.$l.children().length === 0) {
                this.hide();
            }

        };

        /**
         * Add an item to the Mask
         *
         * @param <object> obj : structure
         * {
         *      title: (Line of text to be displayed on the Mask)
         *      cancel: (true => user can cancel the request / false => not possible) OPTIONAL
         *      layer: (if set close the mask on layerend trigger)
         *      id: (unique identifier for this request) OPTIONAL
         *      request: (reference of the ajax request) OPTIONAL
         * }
         */
        this.add = function(obj) {

            var self = this;
            
            /**
             * Object intialization
             */
            $.extend(obj,
            {
                title:obj.title || "",
                cancel:obj.cancel || false,
                layer:obj.layer || null,
                id:obj.id || M.Util.getId(),
                request:obj.request || null
            });

            /**
             * A reference to the ajax request is defined
             * => store it within the activeRequests hashtable
             */
            if (obj.request) {
                this.activeRequests[obj.id] = obj.request;
            }

            /**
             * If cancel is to true, user can close the Mask
             */
            if (obj.cancel) {
                this.$l.append('<div id="'+obj.id+'">'+obj.title+'&nbsp;(<a href="#" id="aj'+obj.id+'">'+M.Util._("Cancel")+'</a>)</div>');

                /**
                 * The class name of the link is the id which uniquely
                 * identifies the request in the activeRequests hashtable
                 * 
                 * I really love jquery :)
                 */
                $('#aj'+obj.id).click(function(){
                    self.abort(obj.id);
                });
            }
            else {
                this.$l.append('<div id="'+obj.id+'">'+obj.title+'</div>');
            }

            if (obj.layer) {
                M.Map.events.register("layersend", obj, function(action, layer, scope) {
                    
                    /*
                     * Only process current layer
                     */
                    if ((action != "remove") && (layer.id === scope.layer.id)) {
                       
                       /*
                        * Unregister event
                        */
                       M.Map.events.unRegister(scope);
                        
                       /*
                        * Show item on layer manager
                        */
                        var lm = M.Plugins.LayersManager;
                        if (!layer._tobedestroyed) {
                           if (lm && lm._o) {
                                lm._o.show(lm._o.get(layer['_M'].MID));
                            }
                        }
                        
                        self.abort(scope.id);
                    }
                });
            }
            
            /**
             * Show the Mask
             */
            this.show();

            return obj.id;

        };

        /**
         * Show the Mask
         * 
         * @param a : boolean - if true do not show the activity indicator
         */
        this.show = function(a) {
            this.$m.show();
            if (!a) {
                M.activity.show();
            }
        };

        /**
         * Hide the Mask
         */
        this.hide = function() {
            this.$l.empty();
            this.$m.hide();
            M.activity.hide();
        };
        
        /*
         * Initialize object
         */
        this.init();
        
        /*
         * Create unique instance
         */
        M.Mask._o = this;
        
        return this;
    }
    
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/*
 * Tooltip object
 */
(function (M) {

    /**
     * Create Tooltip object
     */
    M.Tooltip = function () {
        
        /*
         * Only one Tooltip object instance is created
         */
        if (M.Tooltip._o) {
            return M.Tooltip._o;
        }
        
        /*
         * Initialization
         */
        this.init = function() {
            
            /*
             * jTooltip div reference
             */
            this.$t = M.Util.$$('#mapshup-tooltip');

            /*
             * Reference to the current element
             */
            this.c = null;
            
        };
       
        /**
         * Add a tooltip
         * 
         * @param {jquery DOM Element} domelement : jquery DOM Element containing a jtitle attribute
         * @param {String} direction : direction of tooltip ('n','s','e','w')
         * @param {integer} offset : offset in pixel to add
         */
        this.add = function(domelement, direction, offset) {
        
            var self = this;
            
            offset = offset || 0;
            
            /*
             * Add events on domelement for non touch device
             */
            if (!M.Util.device.touch) {

                domelement.hover(function(){

                    var domelement = $(this);

                    /*
                     * Paranoid mode
                     */
                    if (!domelement) {
                        return;
                    }

                    /*
                     * Set __currentdomelement
                     */
                    self.c = self.c || [""];

                    /*
                     * Optimization rules !
                     */
                    if (domelement[0] === self.c[0]) {
                        return;
                    }

                    /*
                     * Store the domelement
                     */
                    self.c = domelement;

                    /*
                     * Set tooltip content
                     */
                    self.$t.html('<div class="inner">'+domelement.attr("jtitle")+'</div>');

                    /*
                    * Compute tooltip position
                    */
                    var actualWidth = self.$t.width(),
                    actualHeight = self.$t.height(),
                    pos = {
                        top:domelement.offset().top,
                        left: domelement.offset().left,
                        width: domelement.width(),
                        height: domelement.height()
                    };

                    switch (direction) {
                        case 'n':
                            self.$t.css({
                                top: pos.top + pos.height + 5 + offset,
                                left: pos.left + pos.width / 2 - actualWidth / 2
                            });
                            break;
                        case 's':
                            self.$t.css({
                                top: pos.top - actualHeight - 20 - offset,
                                left: pos.left + pos.width / 2 - actualWidth / 2
                            });
                            break;
                        case 'e':
                            self.$t.css({
                                top: pos.top + pos.height / 2 - actualHeight / 2,
                                left: pos.left - actualWidth - 10 - offset
                            });
                            break;
                        case 'w':
                            self.$t.css({
                                top: pos.top + pos.height / 2 - actualHeight / 2,
                                left: pos.left + pos.width + 10 + offset
                            });
                            break;
                        case 'nw':
                            self.$t.css({
                                top: pos.top + pos.height + 5 + offset,
                                left: pos.left
                            });
                            break;
                    }

                    self.$t.show();

                }, function(){
                    self.remove();
                });
            }
           
        };
        
        /*
         * Remove tooltip
         */
        this.remove = function() {
            this.$t.hide();
            this.c = null;
        };
        
        /*
         * Initialize object
         */
        this.init();
        
        /*
         * Create unique instance
         */
        M.Tooltip._o = this;
        
        return this;
        
    };
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * mapshup contextual menu
 */
(function (M) {

    M.Menu = function (limit) {

        /*
         * Only one Activity object instance is created
         */
        if (M.Menu._o) {
            return M.Menu._o;
        }
        
        /**
         * Last mouse click is stored to display menu
         */
        this.lonLat = new OpenLayers.LonLat(0,0);

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
        this.limit = limit || 0;
        
        /**
         * Set to true to disable menu
         */
        this.isNull = false;
        
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
             * M config says no menu
             * Menu is considered to be loaded (isLoaded) but jMenu
             * is not created. Thus hide() and show() function will do
             * nothing
             */
            if (!M.Config["general"].displayContextualMenu) {
                self.isLoaded = true;
                self.isNull = true;
                self.$m = $();
                return self;
            }

            /*
             * Create the menu div
             */
            M.$map.append('<div id="menu"><div class="cross"><img src="'+M.Util.getImgUrl("x.png")+'"</div></div>');
            
            /*
             * Set jquery #menu reference
             */
            self.$m = $('#menu');
            
            /*
             * Add "close" menu item
             */
            self.add([
                /* Add "close" menu item */
                {
                    id:M.Util.getId(),
                    ic:"x.png",
                    ti:"Close menu",
                    cb:function(scope) {}
                }
                /* Add "zoom in" menu item
                {
                    id:M.Util.getId(),
                    ic:"plus.png",
                    ti:"Zoom here",
                    cb:function(scope) {
                        M.Map.map.setCenter(scope.lonLat, M.Map.map.getZoom() + 1);
                    }
                },
                */
                /* Add "zoom out" menu item
                {
                    id:M.Util.getId(),
                    ic:"minus.png",
                    ti:"Zoom out",
                    cb:function(scope) {
                        M.Map.map.setCenter(scope.lonLat, Math.max(M.Map.map.getZoom() - 1, M.Map.lowestZoomLevel));
                    }
                }
                */
            ]);

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
         *      ic: // icon url,
         *      ti: // Displayed title
         *      cb: // function to execute on click
         * }
         */
        this.add = function(items) {
            
            if (this.isNull) {
                return false;
            }
            
            if ($.isArray(items)) {
                /*
                 * Add new item
                 */
                for (var i = 0, l = items.length;i<l;i++) {
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
             * Items are displayed on a circle.
             * Position from above and below 60 degrees are forbidden
             */
            var i,x,y,rad,
                scope = this,
                start = 45,
                offsetX = 80,
                angle = 180 - start,
                left = true,
                l = scope.items.length,
                step = (4 * start) / l,
                a = Math.sqrt(l) * 40,
                b = 1.3 * a;
            
            /*
             * Clean menu
             */
            $('.item', scope.$m).remove();
           
            for (i = 0; i < l; i++) {
                (function(item, $m) {
                
                    /*
                     * First item position is located at "2 * start" on the circle
                     * Position between (start and 2*start) and (3*start and 4*start) are forbidden 
                     */
                    if (angle > start && angle < (180 - start)) {
                        angle = 180 - start + angle;
                        left = true;
                    }
                    else if (angle > (180 + start) && angle < (360 - start)) {
                        angle = 180 - angle + step;
                        left = false;
                    }

                    /*
                     * Convert angle in radians
                     */
                    rad = (angle * Math.PI) / 180;

                    if (left) {
                        $m.append('<div class="item right" id="'+item.id+'">'+M.Util._(item.ti)+'&nbsp;<img class="middle" src="'+M.Util.getImgUrl(item.ic)+'"/></div>');
                        x = Math.cos(rad) * a - 200 + offsetX;
                    }
                    else {
                        $m.append('<div class="item left" id="'+item.id+'"><img class="middle" src="'+M.Util.getImgUrl(item.ic)+'"/>&nbsp;'+M.Util._(item.ti)+'</div>');
                        x = Math.cos(rad) * a - offsetX;
                    }

                    y = Math.sin(rad) * b - 10;
                    $('#'+item.id).click(function(){
                        scope.hide();
                        item.cb(scope);
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

        /**
         * menu display function
         * menu is entirely included inside the "#map" div
         */
        this.show = function() {

            var x,y;
            
            if (this.isNull) {
                return false;
            }

            /**
             * menu is displayed at "pixel" position
             * If pixel is not given as input, it is inferred
             * from this.lonLat position (i.e. last click on #map div)
             */
            if (M.Map.mouseClick) {

                x = M.Map.mouseClick.x;
                y = M.Map.mouseClick.y;

                /**
                 * Click on the border of the map are
                 * note taken into account
                 */
                if (y < this.limit || y > (M.$map.height() - this.limit) || x < this.limit || x > (M.$map.width() - this.limit)) {
                    this.$m.hide();
                    return false;
                }
                this.lonLat = M.Map.map.getLonLatFromPixel(M.Map.mouseClick);
            }
            else {
                return false;
            }

            /**
             * menu is not loaded ? => initialize it
             */
            if (!this.isLoaded) {
                this.init();
            }

            /**
             * Show '#menu' at the right position
             * within #map div
             */
            this.$m.css({
                'left': x,
                'top': y
            }).show();

            return true;
        };
        
        /*
         * Update menu position
         */
        this.updatePosition = function() {
            
            if (this.isNull) {
                return false;
            }
            
            var xy = M.Map.map.getPixelFromLonLat(this.lonLat);
            this.$m.css({
                'left': xy.x,
                'top': xy.y
            });
            
            return true;
        };
        

        /**
         * Hide menu
         */
        this.hide = function() {
            
            if (this.isNull) {
                return false;
            }
            
            this.$m.hide();
            
            return true;
        }
        
        /*
         * Initialize object
         */
        this.init();
        
        /*
         * Set unique instance
         */
        M.Menu._o = this;
        
        return this;
    };
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Mapshup TabPaginator
 * 
 * @param {Object} M : mapshup main object
 * 
 */
(function(M) {
    
    /*
     * TabPaginator constructor
     * 
     * @param {Object} options : should contain
     *                  {
     *                      target: // reference to the paginator target
     *                              (i.e. LayersManager plugin or SouthPanel) - MANDATORY
     *                      $container: // jQuery DOM element used to compute the
     *                                  nbOfTabsPerPage - OPTIONAL (set to M.$container if not specified)
     *                  }
     */
    M.TabPaginator = function(options) {
        
        /**
         * Paginator target
         * Target should be one of the following
         *   - LayersManager plugin
         *   - SouthPanel
         */
        this.target = options.target;
        
        /**
         * jQuery DOM element which the width is
         * used to compute nbrOfTabsPerPage
         */
        this.$container = options.$container || M.$container;
        
        /**
         * CSS left position in pixel relative to the map container
         */
        this.left = options.left || 0;
        
        /**
         * Current tabs page number
         * Pages go from 0 to Math.ceil((items.length - 1) / perPage)
         * 
         * (Note : perPage value is computed from the panel width)
         */
        this.page = 0;
        
        /**
         * Tab paginator initializer
         */
        this.init = function() {
            
            var idp = M.Util.getId(), idn = M.Util.getId(), self = this;
            
            /*
             * Paginator cannot be set if target is null
             */
            if (!self.target) {
                return null;
            }
        
            /*
             * Add tab paginator
             */
            self.target.$d.append('<a id="'+idp+'" class="tab ptab">&laquo;</a><a id="'+idn+'" class="tab ptab">&raquo;</a>');
            self.$prev = $('#'+idp).click(function(e){
                e.preventDefault();
                e.stopPropagation();
                self.goTo(self.page - 1);
            }).css({
                left:self.left
            });
            self.$next = $('#'+idn).click(function(e){
                e.preventDefault();
                e.stopPropagation();
                self.goTo(self.page + 1);
            }).css({
                left:self.$prev.offset().left + self.$prev.outerWidth()
            });
            
            /*
             * Panel width follow the width of the map
             */
            M.events.register("resizeend", self, function(scope){
                
                var i, l, item;
                
                scope.refresh();
                
               /*
                * Reinitialize ul positions and update pagination
                */
                if (scope.target && scope.target.items) {
                    for (i = 0, l = scope.target.items.length; i < l; i++) {
                        item = scope.target.items[i];
                        $('ul',item.$d).css('left', 0);
                        scope.updatePaginate(item);
                    }
                }
            });
            
            return this;
        };
        
        /**
         * Return number of tab page
         */
        this.nbOfPages = function() {
            return Math.ceil((this.target.items.length) / this.nbOfTabsPerPage()) - 1;
        };
        
        /**
         * Return number of tabs per page
         */
        this.nbOfTabsPerPage = function() {
            return Math.round((2 * this.$container.width() / 3) / 200);
        };
        
        /**
         * Update tabs position and ul left position
         */
        this.refresh = function() {
            
            var first, last, perPage, nbPage, i, $t, self = this, $d;
            
            /*
             * Paranoid mode
             */
            if (!self.target) {
                return;
            }
            
            $d = self.target.$d;
        
            /*
             * Hide all tabs
             */
            $('.tab', $d).hide();
            
            /*
             * Maximum number of tabs per page
             */
            perPage = self.nbOfTabsPerPage();
            
            /*
             * Check that page is not greater that number of page
             * (cf. needed if resizing window when not on page 0)
             */
            nbPage = self.nbOfPages();
            if (self.page > nbPage) {
                self.page = nbPage;
            }
            
            /*
             * A negative page means no more items
             */
            if (self.page < 0) {
                self.page = 0;
                $('.ptab', $d).hide();
                return;
            }
            
            /*
             * hide paginator if not needed
             */
            if (nbPage === 0) {
                $('.ptab', $d).hide();
            }
            else {
                $('.ptab', $d).show();
            }
            
            /*
             * Get the first tab in the current page
             */
            first = perPage * self.page;
            
            /*
             * Get the last tab in the current page
             */
            last = Math.min(first + perPage, self.target.items.length);
            
            /*
             * Set first item position right to the paginator
             */
            if (self.target.items[first]) {
                self.target.items[first].$tab.css({
                    left:self.$next.is(':visible') ? self.$next.position().left + self.$next.outerWidth() : self.left
                }).show();
            }
            
            /*
             * Tab position is computed from the first to the last index in the page
             */
            for (i = first + 1; i < last; i++) {
                $t = self.target.items[i-1].$tab;
                self.target.items[i].$tab.css({
                    left:$t.position().left + $t.outerWidth() + 10
                }).show();
            }
           
            return;
        };
        
        /*
         * Return the page number where an item is
         */
        this.getPageIdx = function(item) {
           for (var i = 0, l = this.target.items.length; i < l; i++) {
                if (this.target.items[i].id === item.id) {
                    /* Bitwise operator is faster than Map.floor */
                    return (i / this.nbOfTabsPerPage())|0;
                }
                
            }
            return -1;
        };
        
        /**
         * Display the tabs page with a cycling strategy
         * 
         * If page is greater than the maximum of page, then the first page is displayed
         * If page is lower than 0, then the last page is displayed
         * 
         * @param {Integer} page
         * 
         */
        this.goTo = function(page) {
          
            var self = this, nbPage = self.nbOfPages();
            
            if (page < 0) {
                self.page = nbPage;
            }
            else if (page > nbPage) {
                self.page = 0;
            }
            else {
                self.page = page;
            }
            
            self.refresh();
          
        };
    
        /*
         * Update pagination visibility
         */
        this.updatePaginate = function(item) {
            
            var $ul = $('ul', item.$d),
            $p = $('#'+item.id+'p'),
            $n = $('#'+item.id+'n');
            
            if ($('li', $ul).size() > 0) {
            
                /*
                 * Display previous 
                 */
                $('li', $ul).first().offset().left < 0 ? $p.show() : $p.hide();

                /*
                 * Display next
                 */
                $('li', $ul).last().offset().left > $('.thumbsWrapper',item.$d).width() ? $n.show() : $n.hide();
            
            }
            
        };
        
        return this.init();
        
    };

})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Mapshup South panel
 */
(function(M) {

    M.SouthPanel = function(options) {

        /*
         * Paranoid mode
         */
        options = options || {};

        /*
         * Reference of the active item
         */
        this.active = null;

        /*
         * Panel height
         */
        this.h = M.Util.getPropertyValue(options, "h", 300);

        /*
         * Panel width
         */
        this.w = M.Util.getPropertyValue(options, "w", '70%');

        /*
         * List of panel items
         * Structure 
         * {
         *      id: // unique id to identify item
         *      $d: // jquery object of the created item
         *      $content: // jquery content object reference
         *      $tab: // jquery tab object reference
         * }
         */
        this.items = [];

        /*
         * If true, the panel is display over the map
         * If false, the panel "push" the map
         */
        this.over = M.Util.getPropertyValue(options, "over", true);

        /*
         * Item container padding
         */
        this.padding = {
            top: 0,
            bottom: 0,
            right: 0
        };

        /**
         * Panel initialisation
         */
        this.init = function() {

            var self = this;

            /*
             * mapshup can have one and only one SouthPanel
             * 
             * If an already initialized panel is requested then
             * it is returned instead of creating a new one
             *
             */
            if (M.SouthPanel._o) {
                return M.SouthPanel._o;
            }

            /*
             * If height is set to -1 then it is computed from window.height
             * with a minimum value of 300px
             */
            if (self.h === -1) {
                self.h = Math.max(Math.round(2 * M.$map.height() / 5), 300);
            }
            
            /*
             * Store the $container top offset
             */
            self._offset = M.$container.offset().top;
            
            /*
             * Create a Panel div within M.$mcontainer
             * 
             * <div id="..." class="pns"></div>
             */
            self.$d = M.Util.$$('#' + M.Util.getId(), self.over ? M.$mcontainer : M.$container).addClass('pns').css({
                'bottom': -self.h,
                'height': self.h,
                /* Force width to 100% if panel "push" the map (i.e. over is false) */
                'width': self.over ? self.w : '100%'
            });

            /*
             * Panel width follow the width of the map container except for over
             */
            M.events.register("resizeend", self, function(scope) {

                if (!scope.over) {
                    scope.$d.width(M.$container.width());
                }

            });
           
            /*
             * Add tab paginator
             * IMPORTANT ! Must be set AFTER the SouthPanel resizeend event
             */
            self.paginator = new M.TabPaginator({target: this, $mcontainer: this.$d});

            /*
             * Set a SouthPanel reference
             */
            M.SouthPanel._o = self;

            return self;

        };

        /**
         * Add an item to the panel
         * 
         * @param content : content structure :
         *        {
         *          id: // Unique identifier for this item - MANDATORY
         *          title: // Text to display within title tab - OPTIONAL
         *          icon: // Icon to display within the title tab - OPTIONAL
         *          unremovable: // If true panel item cannot be removed (default true) - OPTIONAL
         *          html: // Html content to display within panel - OPTIONAL
         *          classes:  // class name(s) to add to main item div - OPTIONAL
         *          onclose: // function called on panel closing - OPTIONAL
         *          onshow: // function called on panel show,
         *          mask: // true to add a mask on top of panel
         *        }
         *        
         */
        this.add = function(content) {

            /*
             * If content is empty why bother to create a new item ?
             */
            if (!content || !content.id) {
                return false;
            }

            var item, tid, id, self = this;

            /*
             * By default, panel is not removable
             */
            content.unremovable = M.Util.getPropertyValue(content, "unremovable", true);

            /*
             * If an item with identifier 'id' already exists,
             * then replace it with new item
             * Else create a new item
             */
            item = self.get(content.id);

            /*
             * Item does not exist - create it
             * 
             * Important : $d object is created with a 'pnsi' or 'pnist' class (see setActive(item) function)
             */
            if (!item) {

                item = {
                    id: content.id,
                    pn: self,
                    $d: M.Util.$$('#' + content.id, self.$d).addClass('pnsi' + (self.over ? 't' : '')).css({
                        'margin': self.padding.top + 'px ' + (self.over ? self.padding.right : 0) + 'px ' + self.padding.bottom + 'px 0px'
                    })
                }

                /*
                 * Append tab to panel
                 */
                tid = M.Util.getId();
                self.$d.append('<a id="' + tid + 't" class="tab vtab">' + (content.icon ? '<img src="' + content.icon + '">&nbsp;' : '') + M.Util._(content.title) + '</a>');

                /*
                 * Set a trigger on tab
                 */
                item.$tab = $('#' + tid + 't');
                item.$tab.click(function(e) {
                    e.preventDefault();
                    (!self.active || self.active.id !== item.id) ? self.show(item) : self.hide(item);
                });

                /*
                 * Add close button
                 */
                if (!content.unremovable) {
                    M.Util.addClose(item.$tab, function(e) {
                        e.stopPropagation();
                        self.remove(item);
                    });
                }

                /*
                 * Add new item to the items array
                 * The item is added first !
                 */
                self.items.unshift(item);

            }

            /*
             * Update item content
             */
            id = M.Util.getId();

            item.$d.html((content.mask ? '<div id="' + id + 'mask" class="mask" style="display:none;width:100%;height:' + self.h + 'px;"></div>' : '') + '<div id="' + id + '" style="height:' + (self.h - self.padding.top - self.padding.bottom) + 'px"' + (content.classes ? ' class="' + content.classes + '"' : '') + '>' + (content.html || "") + '</div>');

            /*
             * Set jquery content object reference
             * item.$content === item.$d.children().first()
             * 
             * Set callback functions
             */
            $.extend(item, {
                $content: $('#' + id),
                $mask: $('#' + id + 'mask'),
                onclose: content.onclose,
                onshow: content.onshow
            });

            /*
             * Update tabs position
             */
            self.paginator.refresh();

            /*
             * Return the newly created item
             */
            return item;

        };


        /**
         * Remove an item from the panel
         */
        this.remove = function(item) {

            var i, l, self = this;

            /*
             * Paranoid mode
             */
            if (!item) {
                return false;
            }

            /*
             * Roll over items to find the item to remove based on unique id
             */
            for (i = 0, l = this.items.length; i < l; i++) {

                if (this.items[i].id === item.id) {

                    /*
                     * Hide panel
                     */
                    self.hide(item);

                    /*
                     * Remove item content
                     */
                    self.items[i].$content.remove();
                    self.items[i].$d.remove();
                    self.items[i].$tab.remove();

                    /*
                     * Remove item from the list of items
                     */
                    self.items.splice(i, 1);

                    /*
                     * Update tabs position
                     */
                    self.paginator.refresh();

                    return true;
                }
            }

            return false;
        };

        /*
         * Show the panel
         * 
         * @param item : jquery object id to display within this panel
         */
        this.show = function(item) {

            var self = this;

            /*
             * Paranoid mode
             */
            if (!item) {
                return false;
            }

            /*
             * Set item the new active item
             */
            self.setActive(item);

            /*
             * Set panel visibility
             */
            if (self.isVisible) {

                /*
                 * Call onshow function
                 */
                if ($.isFunction(item.onshow)) {
                    item.onshow();
                }

                /*
                 * Panel is already shown to the right div
                 */
                return false;

            }

            self.$d.stop().animate({
                'bottom': '0px'
            },
            {
                duration: 200,
                queue: true,
                step: function(now, fx) {
                    if (!self.over) {
                        M.$container.css('top', - now - self.h + self._offset);
                    }
                },
                complete: function() {
            
                    M.Map.map.updateSize();
                    
                    /*
                     * Call onshow function
                     */
                    if ($.isFunction(item.onshow)) {
                        item.onshow();
                    }
                }
            });

            /*
             * Set the visible status to true
             */
            self.isVisible = true;

            return true;

        };

        /*
         * Return Panel item identified by id
         */
        this.get = function(id) {

            var i, l, self = this, item = null;

            /*
             * Roll over panel items
             */
            for (i = 0, l = self.items.length; i < l; i++) {
                if (self.items[i].id === id) {
                    item = self.items[i];
                    break;
                }
            }

            return item;
        };

        /*
         * Hide the panel
         * 
         * @param item : item to hide
         */
        this.hide = function(item) {

            var self = this;

            /*
             * If item is not active, do nothing
             */
            if (!self.active || self.active.id !== item.id) {
                return false;
            }

            /*
             * Remove active reference
             */
            self.active = null;
            item.$tab.removeClass('active');

            /*
             * Set visible status to false
             */
            self.isVisible = false;

            self.$d.stop().animate({
                'bottom': -self.h
            },
            {
                duration: 200,
                queue: true,
                step: function(now, fx) {

                    /*
                     * Push the map
                     */
                    if (!self.over) {
                        M.$container.css('top', - now - self.h + self._offset);
                    }

                },
                complete: function() {

                    M.Map.map.updateSize();
                    
                    /*
                     * Call onclose function
                     */
                    if ($.isFunction(item.onclose)) {
                        item.onclose();
                    }
                }
            });

            return true;

        };

        /*
         * Set item the new active item
         */
        this.setActive = function(item) {

            var self = this;

            /*
             * Hide all 'pnsi' divs
             */
            $('.pnsi,.pnsit', self.$d).each(function(index) {

                /*
                 * This is bit tricky.
                 * If panel item has a 'nodisplaynone' class, then the
                 * item is not hidden using jquery .hide() function, but
                 * instead it's position is set to somewhere outside the
                 * window display.
                 * This avoid the 'display:none' bug when hiding GoogleEarth plugin
                 * iframe for example
                 */
                var $t = $(this);
                $t.hasClass("nodisplaynone") ? $t.css({
                    'position': 'absolute',
                    'top': '-1000px',
                    'left': '-1000px'
                }) : $t.hide();

            });

            /*
             * Remove active class from all tabs
             */
            $('.tab', self.$d).removeClass('active');

            /*
             * Show the input div
             * 
             * If panel item has a 'nodisplaynone' class, then the
             * item is not shown using jquery .show() function, but
             * instead it's absolute position is set to top:0px,left:0px
             * This avoid the 'display:none' bug when hiding GoogleEarth plugin
             * iframe for example
             * 
             */
            item.$d.hasClass("nodisplaynone") ? item.$d.css({
                'position': 'static',
                'top': '0px',
                'left': '0px'
            }) : item.$d.show();


            /*
             * Set item tab active
             */
            item.$tab.addClass('active');

            /* 
             * Set the input $id as the new this.active item
             */
            self.active = item;

        };

        /*
         * Initialize object
         */
        return this.init();

    };
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Mapshup Side Panel
 * 
 * A side panel is a panel displayed at the left or right border of the Map
 * It pushes the map to the right or the left depending on its position
 * 
 * @param {MapshupObject} M
 * 
 */
(function(M) {

    M.SidePanel = function(options) {

        /*
         * Paranoid mode
         */
        options = options || {};
        
        /*
         * Reference of the active item
         */
        this.active = null;

        /*
         * List of panel items
         * Structure 
         * {
         *      id: // unique id to identify item
         *      $d: // jquery object of the created item
         *      $content: // jquery content object reference
         *      $tab: // jquery tab object reference
         * }
         */
        this.items = [];
        
        /*
         * Panel width
         */
        this.w = M.Util.getPropertyValue(options, "w", 400);
        
        /*
         * If true, the side panel is display over the map
         * If false, the panel "push" the map
         */
        this.over = M.Util.getPropertyValue(options, "over", true);

        /*
         * Item container padding
         */
        this.padding = {
            top: 0,
            bottom: 0,
            right: 0
        };
    
        /**
         * Panel initialisation
         */
        this.init = function() {

            var self = this;

            /*
             * mapshup can have one and only one SidePanel
             * 
             * If an already initialized panel is requested then
             * it is returned instead of creating a new one
             *
             */
            if (M.SidePanel._o) {
                return M.SidePanel._o;
            }
            
            /*
             * Create a Panel div within M.$container
             * 
             * <div id="..." class="spn"></div>
             */
            self.$d = M.Util.$$('#' + M.Util.getId(), self.over ? M.$mcontainer : M.$container).addClass('spn').css({
                'right': -self.w,
                'height': '100%',
                'width': self.w
            });
        
            /*
             * Set a SidePanel reference
             */
            M.SidePanel._o = self;

            return self;

        };

        /**
         * Show the panel
         * 
         * @param {Object} item // optional item to show
         */
        this.show = function(item) {

            var self = this;
            
            /*
             * Set the input item active
             */
            self.setActive(item);
            
            /*
             * Set panel visibility
             */
            if (self.isVisible) {

                /*
                 * Panel is already shown
                 */
                return false;

            }
            
            /*
             * Show panel
             */
            if (self.over) {
                
                /*
                 * Move vertical toolbar
                 */
                var verticalToolbar = (new M.Toolbar({
                    position: 'ne',
                    orientation: 'v'
                }));
                
                self.$d.stop().animate({
                    'right': '0'
                },
                {
                    duration: 200,
                    queue: true,
                    step: function(now, fx) {
                        verticalToolbar.$d.css('right', self.w + now);
                    }
                });
            }
            else {
                self.$d.stop().animate({
                    'right': 0
                },
                {
                    duration: 200,
                    queue: true,
                    step: function(now, fx) {
                        M.$mcontainer.css('left', - now - self.w);
                    },
                    complete: function() {
                        M.Map.map.updateSize();
                    }
                });
            }
            
            /*
             * Set the visible status to true
             */
            self.isVisible = true;

            return true;

        };

        /**
         * Hide the panel
         * 
         * @param {Object} item
         */
        this.hide = function(item) {

            var self = this;

            /*
             * If Panel is not visible do nothing
             */
            if (!self.isVisible) {
                return false;
            }
            
            /*
             * If an item is specified, only hide the panel
             * if the active item is the input item
             */
            if (item) {
                if (self.active && self.active.id !== item.id) {
                    return false;
                }
            }
        
            /*
             * Set visible status to false
             */
            self.isVisible = false;

            if (self.over) {
                
                /*
                 * Move vertical toolbar
                 */
                var verticalToolbar = (new M.Toolbar({
                    position: 'ne',
                    orientation: 'v'
                }));
                
                self.$d.stop().animate({
                    'right': -self.w
                },
                {
                    duration: 200,
                    queue: true,
                    step: function(now, fx) {
                        verticalToolbar.$d.css('right', self.w + now);
                    }
                });
            }
            else {
                self.$d.stop().animate({
                    'right': -self.w
                },
                {
                    duration: 200,
                    queue: true,
                    step: function(now, fx) {
                        M.$mcontainer.css('left', - now - self.w);
                    },
                    complete: function() {
                        M.Map.map.updateSize();
                    }
                });
            }
            return true;

        };
        
        /**
         * Add an item to the panel
         * 
         * @param content : content structure :
         *        {
         *          id: // Unique identifier for this item - MANDATORY
         *          html: // Html content to display within panel - OPTIONAL
         *          classes:  // class name(s) to add to main item div - OPTIONAL
         *          onclose: // function called on panel closing - OPTIONAL
         *          onshow: // function called on panel show,
         *        }
         *        
         */
        this.add = function(content) {

            /*
             * If content is empty why bother to create a new item ?
             */
            if (!content || !content.id) {
                return false;
            }

            var item, id = M.Util.getId(), self = this;

            /*
             * If an item with identifier 'id' already exists,
             * then replace it with new item
             * Else create a new item
             */
            item = self.get(content.id);

            /*
             * Item does not exist - create it
             */
            if (!item) {

                item = {
                    id: content.id,
                    pn: self,
                    $d: M.Util.$$('#' + content.id, self.$d).css({
                        'margin': self.padding.top + 'px ' + (self.over ? self.padding.right : 0) + 'px ' + self.padding.bottom + 'px 0px'
                    })
                };

                /*
                 * Add new item to the items array
                 * The item is added first !
                 */
                self.items.unshift(item);

            }
            
            item.$d.html('<div id="' + id + '"'  + (content.classes ? ' class="' + content.classes + '"' : '') + '>' + (content.html || "") + '</div>');

            /*
             * Set jquery content object reference
             * item.$content === item.$d.children().first()
             * 
             * Set callback functions
             */
            $.extend(item, {
                $content: $('#' + id),
                onclose: content.onclose,
                onshow: content.onshow
            });

            /*
             * Return the newly created item
             */
            return item;

        };


        /**
         * Remove an item from the panel
         * 
         * @param {Object} item
         */
        this.remove = function(item) {

            var i, l, self = this;

            /*
             * Paranoid mode
             */
            if (!item) {
                return false;
            }

            /*
             * Roll over items to find the item to remove based on unique id
             */
            for (i = 0, l = this.items.length; i < l; i++) {

                if (this.items[i].id === item.id) {

                    /*
                     * Hide panel
                     */
                    self.hide(item);

                    /*
                     * Remove item content
                     */
                    self.items[i].$content.remove();
                    self.items[i].$d.remove();
                    self.items[i].$tab.remove();

                    /*
                     * Remove item from the list of items
                     */
                    self.items.splice(i, 1);

                    return true;
                }
            }

            return false;
        };
    
        /**
         * Return Panel item identified by id
         * 
         * @param {String} id
         */
        this.get = function(id) {

            var i, l, item = null;

            /*
             * Roll over panel items
             */
            for (i = 0, l = this.items.length; i < l; i++) {
                if (this.items[i].id === id) {
                    item = this.items[i];
                    break;
                }
            }

            return item;
        };
        
        /*
         * Set item the new active item
         */
        this.setActive = function(item) {

            var i, l;
            
            if (!item) {
                return false;
            }
        
            /*
             * Hide all items
             */
            for (i = 0, l = this.items.length; i < l; i++) {
                this.items[i].$d.hide();
            }
            
            item.$d.show();
            
            /* 
             * Set the input $id as the new this.active item
             */
            this.active = item;
            
            return true;

        };
    
        /*
         * Initialize object
         */
        return this.init();

    };
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * mapshup Popup
 */
(function (M) {

    M.Popup = function (options) {

        /*
         * Paranoid mode
         */
        options = options || {};
        
        /*
         * True to adapt popup size to its content 
         */
        this.autoSize = M.Util.getPropertyValue(options, "autoSize", false);
        
        /*
         * Class names to add to this popup
         * !! WARNING !! Adding class names overide the "autoSize" parameter 
         */
        this.classes = options.classes;
        
        /*
         * True to have popup horizontally centered on the map
         */
        this.centered = M.Util.getPropertyValue(options, "centered", true);
        
        /*
         * True to add a close button to popup
         */
        this.addCloseButton = M.Util.getPropertyValue(options, "addCloseButton", true);
        
        /*
         * Function callback called after popup is removed
         */
        this.onClose = options.onClose;
        
        /*
         * Where to display popup
         */
        this.parent = options.parent || M.$mcontainer;
        
        /*
         * True to display a generic popup (see mapshup.css .tools .generic definition)
         */
        this.generic = M.Util.getPropertyValue(options, "generic", true);
        
        /*
         * True to hide popup when closing it instead of remove it
         */
        this.hideOnClose = M.Util.getPropertyValue(options, "hideOnClose", false);
        
        /*
         * True to set this popup modal.
         * 
         * "In user interface design, a modal window is a child window
         * that requires users to interact with it before they can return
         * to operating the parent application"
         * http://en.wikipedia.org/wiki/Modal_window
         * 
         */
        this.modal = M.Util.getPropertyValue(options, "modal", false);
        
        /*
         * True to not set a popup header
         * !! WARNING !! If set to true, then 'header' parameter is discarded
         */
        this.noHeader = M.Util.getPropertyValue(options, "noHeader", false);
        
        /*
         * True to not set a popup footer
         */
        this.noFooter = M.Util.getPropertyValue(options, "noFooter", true);
        
        /*
         * True to automatically resize popup on window size change
         */
        this.resize = M.Util.getPropertyValue(options, "resize", true);
        
        /*
         * Parent scope for callback
         */
        this.scope = options.scope || this;
        
        /*
         * If true popup cannot be displayed outside of the map view
         */
        this.unbounded = M.Util.getPropertyValue(options, "unbounded", false);
        
        /*
         * If true popup position is fixed relatively to the map
         * (i.e. it moves with the map). 
         * !! WARNING !! Set the parameter to true overides the "unbounded" parameter
         * since the popup is unbounded in this case
         */
        this.followMap = M.Util.getPropertyValue(options, "followMap", false);
        
        /*
         * Display popup to the specified {OpenLayers.LonLat} map coordinates
         * If 'followMap' is set to true, then popup is anchored to this coordinates
         * when map moves
         */
        this.mapXY = options.mapXY;
        
        /*
         * Popup z-index
         */
        this.zIndex = M.Util.getPropertyValue(options, "zIndex", 35900);
        
        /*
         * Initialize Popup object
         */
        this.init = function(options) {
            
            var self = this;
            
            options = options || {};
            
            /*
             * Set an empty modal mask
             */
            self.$m = $();
            
            /*
             * Popup structure
             * 
             * <div id="..." class="po">
             *      <div class="header"> // optional
             *      <div class="body generic"> // generic class is not added if this.generic = false
             *      <div class="footer">
             *  </div>
             */
            self.$d = M.Util.$$('#'+M.Util.getId(), self.parent).addClass('po').html((self.noHeader ? ''  : '<div class="header"></div>')+'<div class="body'+(self.generic ? ' generic' : '')+'"></div>' + (!self.noFooter ? '<div class="footer"></div>' : ''));

            /*
             * If popup is modal, set a semi transparent mask
             * under the popup
             */
            if (self.modal) {
                
                /*
                 * Set popup over the mask and over activity
                 */
                self.$d.css({
                    'z-index':'38600'
                });
                
                self.$m = M.Util.$$('#modmask', self.parent)
                .addClass("mask")
                .css(
                {
                    'position':'absolute',
                    'display':'none',
                    'left':'0',
                    'top':'0',
                    'width':'100%',
                    'height':'100%',
                    'z-index':'36000'
                });
            }
            else {
                /*
                 * Set popup under the mask
                 * If popup followMap, then it is displayed behind Toolbars and LayersManager
                 */
                self.$d.css({
                    'z-index':self.followMap ? '19000' : self.zIndex
                });
            }
            
            /*
             * Set classes or automatic popup size
             */
            self.$d.addClass(self.classes ? self.classes : (self.autoSize ? 'poa' : 'pona'));
            
            /*
             * Set body, header and footer reference
             */
            self.$b = $('.body', self.$d);
            self.$h = $('.header', self.$d);
            self.$f = $('.footer', self.$d);
            
            /*
             * Set header content
             */
            if (options.header) {
                self.$h.html(options.header);
            }
            
            /*
             * Set body content
             */
            if (options.body) {
                self.$b.html(options.body);
            }
            
            /*
             * Set footer content
             */
            if (options.footer) {
                self.$f.html(options.footer);
            }
            /*
             * Add a close button
             */
            if (self.addCloseButton) {
                M.Util.addClose(self.$d, function(e){
                    self.hideOnClose ? self.hide() : self.remove();
                });
            }
        
            /*
             * Compute popup position on window resize
             */
            M.events.register("resizeend", self, function(scope) {
                scope.updatePosition(scope);
            });
            
            /*
             * Move popup on map move
             */
            if (options.followMap) {
                M.Map.map.events.register('move', M.Map.map, function(){
                    if (self.$d.is(':visible')) {
                        self.updatePosition(self);
                    }
                });
            }
            
            /*
             * Compute position on init
             */
            if (self.mapXY) {
                this.setMapXY(self.mapXY);
            }
            
            self.updatePosition(self);
            
            return self;
            
        };
        
        /**
         * Append content to popup header or body
         * 
         * @param {String} html : HTML string to append
         * @param {String} target : 'body' or 'header'
         */
        this.append = function(html, target) {
            
            var $div = target === 'header' ? this.$h : this.$b;
            
            $div.append(html);
            this.updatePosition(this);
            
        };
        
        /*
         * Update position and size of div
         */
        this.updatePosition = function(scope) {
            
            scope = scope || this;
            
            /*
             * Popup body max height is equal to 50% of its container
             */
            if (scope.resize) {
                scope.$b.css({
                    'max-height':Math.round( (1 * (M.$container.height() - scope.$h.height())) / 2)
                });
            }
            
            /*
             * Center popup if needed
             */
            if (scope.centered) {
                scope.center(scope);
            }
            
            /*
             * Follow map
             */
            if (scope.followMap && scope.mapXY) {
                scope.setMapXY(scope.mapXY);
            }
            
        };
        
        /**
         * Recenter popup
         * 
         * @param {Object} scope
         */
        this.center = function(scope) {
            
            scope = scope || this;
            
            /*
             * Center the popup over its container 
             */
            scope.$d.css({
                'left': ((M.$container.width() - scope.$d.width()) / 2 )
            });
            
        };
        
        /**
         * Hide popup
         * 
         * @param {boolean} noClose // true = do not launch onClose callback
         */
        this.hide = function(noClose) {
            
            var self = this;
            
            self.$d.hide();
            self.$m.hide();
            
            if (!noClose && $.isFunction(self.onClose)) {
                self.onClose(self.scope);
            }
            
        };
        
        /**
         * 
         * Attach popup to the specified map coordinates
         * when the popup 'followMap'
         * 
         * @param {OpenLayers.LonLat} mapXY
         * 
         */
        this.setMapXY = function(mapXY) {
            
            this.mapXY = mapXY;
            
            var xy = M.Map.map.getPixelFromLonLat(this.mapXY);
                    
            /*
             * Set action info menu position
             * 
             * If popup has an 'apo' class, then it is display
             * so the popup anchor is under mapXY
             * 
             * Otherwise the popup is centered on mapXY
             */
            if (this.$d.hasClass('apo')) {
                this.$d.css({
                    'left': xy.x - 31, //'left': xy.x - self.$d.outerWidth() + 31,
                    'top': xy.y - this.$d.outerHeight() - 12 // 'top': xy.y + 12
                });
            }
            else {
                this.$d.css({
                    'left': xy.x - (this.$d.outerWidth() / 2),
                    'top': xy.y - (this.$d.outerHeight() / 2)
                });
            }
           
        };
        
        /**
         * 
         * Move popup to be centered on pixel
         * 
         * @param {Object} MapPixel : pixel in {x,y} relative to the map
         * 
         */
        this.moveTo = function(MapPixel) {

            var x,y,pixel,
            $d = this.$d,
            parent = M.$map,
            offset = parent.offset();

            /*
             * If popup is not resizable it cannot be moved
             */
            if (!this.resize) {
                return false;
            }
            
            /*
             * (0,0) origin of MapPixel is M.$map
             * (0,0) origin of pixel is window
             */
            pixel = {
                x:MapPixel.x + offset.left,
                y:MapPixel.y + offset.top
            };

            /*
             * If xy is not (or uncorrectly) defined,
             * div is centered on $map div
             */
            if (!pixel || !pixel.x || !pixel.y) {
                x = offset.left + ((parent.width() - $d.width()) / 2);
                y = offset.top + ((parent.height() - $d.height()) / 2);
            }
            /*
             * Non unbounded popup are enterely contained within the map view
             */
            else if (!this.unbounded) {
                
                /*
                 * div left is far too left
                 */
                if ((pixel.x - ($d.width()/2) < offset.left)) {
                    x = offset.left;
                }
                /**
                 * div left is far too right
                 */
                else if ((pixel.x + ($d.width()/2) > (offset.left + parent.width()))) {
                    x = offset.left + parent.width() - $d.width();
                }
                /**
                 * div left is ok
                 */
                else {
                    x = pixel.x - ($d.width() / 2);
                }

                /**
                 * div top is far too top
                 */
                if ((pixel.y - ($d.height()/2) < offset.top)) {
                    y = offset.top;
                }
                /**
                 * div top is far too bottom
                 */
                else if ((pixel.y + ($d.height()/2) > (offset.top + parent.height()))) {
                    y = offset.top + parent.height() - $d.height();
                }
                /**
                 * div top is ok
                 */
                else {
                    y = pixel.y - ($d.height() / 2);
                }
            }
            else {
                x = pixel.x - ($d.width() / 2);
                y = pixel.y - ($d.height() / 2);
            }

            /*
             * Apply div css top/left modifications
             */
            $d.css({
                'top':y,
                'left':x
            });

            return true;
        };
        
        /**
         * Remove popup
         */
        this.remove = function() {
            this.hide();
            M.remove(this);
        };
        
        /**
         * Show popup
         */
        this.show = function() {
            this.$d.show();
            this.$m.show();
            
            if (this.followMap) {
                
                /*
                * Move the map to ensure that feature info panel is completely
                * visible
                */
                var lmo = $('.lm').offset(), // Check if LayersManager is visible
                    dy = this.$d.offset().top - M.$map.offset().top - (lmo ? lmo.top : 0),
                    dx = M.$map.offset().left + M.$map.width() - this.$d.offset().left - this.$d.outerWidth(),
                    c;

                if (dx > 0) {
                    dx = 0;
                }
                if (dy > 0) {
                    dy = 0;
                }
                
                /*
                 * Transform pixels to meters
                 */
                if (dx < 0 || dy < 0) {
                    c = M.Map.map.getPixelFromLonLat(M.Map.map.getCenter());
                    M.Map.map.setCenter(M.Map.map.getLonLatFromPixel(new OpenLayers.Pixel(c.x - dx, c.y + dy)));
                }
            }
        };

        /*
         * Initialize object
         */
        this.init(options);
        
        return this;
    };
    
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
(function(M) {

    /*
     * Initialize M.Map
     */
    M = M || {};

    /*
     * Initialize M.Map
     * 
     * This is the main mapshup object
     */
    M.Map = {
        /**
         * Reference to featureHilite jquery object
         */
        $featureHilite: $(),
        /**
         * Hack to avoid Logger plugin to re-log history
         * extent when they are requested through UserManagement
         * plugin
         */
        doNotLog: false,
        /**
         * Stpre current navigation state
         */
        currentState: null,
        /*
         * Predefined cluster strategy options
         */
        clusterOpts: {
            distance: 30,
            threshold: 3
        },
        /**
         * Plate Carrée projection object
         */
        pc: new OpenLayers.Projection("EPSG:4326"),
        /**
         * Spherical Mercator projection object
         */
        sm: new OpenLayers.Projection("EPSG:900913"),
        /**
         * An array of LayersGroup
         */
        layersGroups: [],
        /**
         * Registered layer types
         */
        layerTypes: [],
        /**
         * Map object reference
         */
        map: null,
        /**
         * Last mouse click within the map object
         * (from top,left)
         */
        mouseClick: new OpenLayers.Pixel(0, 0),
        /**
         * Current mouse position within the map object
         * (from top,left)
         */
        mousePosition: new OpenLayers.Pixel(0, 0),
        /**
         * Predefined layerDescriptions object
         * Used by AddLayer plugin
         */
        predefined: {
            /**
             * Hash of array containing layerDescription
             * The hash keys are the layerDescription types
             */
            items: [],
            /**
             * Add a new layer description to the item list
             * 
             * @param {Object} p
             */
            add: function(p) {

                var i, l, t,
                        add = true,
                        self = this;

                /*
                 * Paranoid mode
                 */
                if (!p || !p.hasOwnProperty("type")) {
                    return false;
                }

                /*
                 * Roll over t to check if layer Description already exists
                 */
                self.items[p["type"]] = self.items[p["type"]] || [];
                t = self.items[p["type"]];
                for (i = 0, l = t.length; i < l; i++) {
                    if ((new M.Map.LayerDescription(t[i], M.Map)).getMID() === (new M.Map.LayerDescription(p, M.Map)).getMID()) {
                        add = false;
                        break;
                    }
                }

                /*
                 * Add new layer description
                 */
                if (add) {
                    t.push(p);
                }

                return true;
            }

        },
        /**
         * Number of call to the window.setInterval function
         */
        refreshCycle: 0,
        /**
         * selectableLayers object contains all the layers that can be selected with
         * the "__CONTROL_SELECT__" control tool
         */
        selectableLayers: {
            /**
             * Array containing all selectable layers (i.e. not background layers, only vectors)
             */
            items: [],
            /**
             * Add a new layer to the selectable list
             * 
             * @param {OpenLayers.Layer} layer
             */
            add: function(layer) {
                layer.events.on({
                    "featureselected": function(e) {
                        M.Map.featureInfo.select(e["feature"]);
                    },
                    "featureunselected": function(e) {
                        M.Map.featureInfo.unselect(e["feature"]);
                    }
                });
                this.items.push(layer);

                /*
                 * By default, selectable layer is hilitable
                 * unless specified that it is not
                 */
                if (layer['_M'].hilitable) {
                    M.Map.hilitableLayers.add(layer);
                }

                M.Map.resetControl();
            },
            /**
             * Remove a layer from the selectable list
             * 
             * @param {OpenLayers.Layer} layer
             */
            remove: function(layer) {

                for (var i = 0, l = this.items.length; i < l; i++) {
                    if (this.items[i].id === layer.id) {
                        this.items.splice(i, 1);
                        break;
                    }
                }

                /*
                 * By default, selectable layer is hilitable
                 * unless specified that it is not
                 */
                M.Map.hilitableLayers.remove(layer);

            }
        },
        /**
         * hilitableLayers object contains all the layers that can be hilited with
         * the "__CONTROL_HIGHLITE__" control tool
         */
        hilitableLayers: {
            /**
             * Array containing all selectable layers (i.e. not background layers, only vectors)
             */
            items: [],
            /**
             * Add a new layer to the selectable list
             * 
             * @param {OpenLayers.Layer} layer
             */
            add: function(layer) {
                this.items.push(layer);
            },
            /**
             * Remove a layer from the selectable list
             * 
             * @param {OpenLayers.Layer} layer
             */
            remove: function(layer) {
                for (var i = 0, l = this.items.length; i < l; i++) {
                    if (this.items[i].id === layer.id) {
                        this.items.splice(i, 1);
                        break;
                    }
                }
            }
        },
        /*
         * Add GeoJSON feature to mapshup stuff layer
         * The stuff layer is a vector layer that contains
         * temporary result from various plugins (i.e. WPS processing
         * results for instance)
         * 
         * @param {Object} data : a valid FeatureCollection GeoJSON object
         * @param {Object} options :
         *                      {
         *                          zoomOn: true to zoom on added features (default false)
         *                      }
         * 
         */
        addToStuffLayer: function(data, options) {

            if (typeof data !== "object") {
                return null;
            }

            options = options || {};

            if (!this._stuffLayer) {
                this._stuffLayer = this.addLayer({
                    type: "GeoJSON",
                    title: "Stuff",
                    clusterized: false, // Very important !
                    editable: true,
                    unremovable: true,
                    ol: {
                        styleMap: new OpenLayers.StyleMap({
                            'default': {
                                strokeColor: 'white',
                                strokeWidth: 1,
                                fillColor: 'red',
                                fillOpacity: 0.2,
                                pointRadius: 5
                            }
                        })
                    }
                });
            }

            /*
             * Add new feature(s) and center on it
             */
            return this.layerTypes["GeoJSON"].load({
                data: data,
                layer: this._stuffLayer,
                zoomOnNew: options.zoomOn || false
            });

        },
        /*
         *
         * This fonction is the entry point to add any kind of layers
         * The add*Layer functions should not be called directly
         *
         *  @param {object} _layerDescription : layer description object (see layerTypes js files)
         *  @param {Object} _options : options can be
         *                  {
         *                      noDeletionCheck : if 'true', user is not request if the added layer replace an existing one
         *                      forceInitialized : if true, the layer["_M"].initialized is set to true and thus the map
         *                                         is not zoom on layer after load
         *                  }
         */
        addLayer: function(_layerDescription, _options) {

            /**
             * Paranoid mode
             */
            _options = _options || {};


            /**
             * By default, check for deletion
             */
            var noDeletionCheck = M.Util.getPropertyValue(_options, "noDeletionCheck", false);

            /*
             * Create a layerDescriptionObj from current layerDescription
             */
            var ldo = new this.LayerDescription(_layerDescription, this);

            /**
             * Check if layerDescription object is valid i.e. its layerType is registered
             * Only layers registered within this.Config.layerTypes array
             * can be added to Map
             */
            if (!ldo.isValid()) {
                return null;
            }

            var layerType = ldo.getLayerType();
            if (!layerType) {
                return null;
            }

            /**
             * Each layer must include a _M object wich contains 
             * specific M properties
             * 
             * One of this property, MID, is a unique and mandatory identifier
             * based on a checksum (see M.crc32 method)
             */
            var MID = ldo.getMID();

            /**
             * Check if newLayer already exist. Based on MID uniqueness
             */
            var newLayer = this.Util.getLayerByMID(MID);

            /**
             * If layer is already defined, replace it unless M.Config.general.confirmDeletion
             * is set to true.
             * In this case, ask for deletion  - dialog box (div : #jDialog)
             */
            if (newLayer) {

                /**
                 * Ask for deletion if :
                 *  - it is requested in the query
                 *  - M.Config.general.confirmDeletion is set to true
                 */
                if (!noDeletionCheck && M.Config["general"].confirmDeletion) {

                    M.Util.askFor({
                        title: M.Util._("Delete layer"),
                        content: M.Util._("Do you really want to remove layer") + " " + newLayer.name,
                        dataType: "list",
                        value: [{
                                title: M.Util._("Yes"),
                                value: "y"
                            },
                            {
                                title: M.Util._("No"),
                                value: "n"
                            }
                        ],
                        callback: function(v) {
                            if (v === "y") {
                                M.Map.removeLayer(newLayer);
                                M.Map.addLayer(_layerDescription);
                            }
                        }
                    });
                    return null;
                }
                else {
                    M.Map.removeLayer(newLayer);
                }
            }

            /*
             * Get a reference to the layerDescription object
             */
            var layerDescription = ldo.obj;

            /**
             * Ensure that layerDescription.url is an absolute url
             */
            if (layerDescription.url) {
                layerDescription.url = M.Util.getAbsoluteUrl(layerDescription.url);
            }

            /**
             * Default options for all newLayer
             * These can be superseed depending on layerTypes
             */
            var options = {};

            /**
             * Specific OpenLayers properties can be described
             * within the layerDescription.ol properties.
             * Usefull for styleMap description for example
             */
            for (var key in layerDescription.ol) {
                options[key] = layerDescription.ol[key];
            }

            /*
             * M specific properties.
             */
            options["_M"] = {
                /** True : add opacity buttons in LayersManager panel */
                allowChangeOpacity: M.Util.getPropertyValue(layerDescription, "allowChangeOpacity", false),
                /** True : layer is clusterized if it is supported in the layerTypes */
                clusterized: M.Util.getPropertyValue(layerDescription, "clusterized", M.Util.getPropertyValue(layerType, "clusterized", false)),
                /** Total number of feature within layer (see "layersend" event) */
                count: 0,
                /** True : layer is editable which means that feature can be modified and/or deleted individually */
                editable: M.Util.getPropertyValue(layerDescription, "editable", false),
                /** By default, a layer is not in a group */
                group: null,
                /** True : layer is selectable through click in the map (i.e. add to __CONTROL_SELECT__) */
                hilitable: M.Util.getPropertyValue(layerDescription, "hilitable", M.Util.getPropertyValue(layerType, "hilitable", true)),
                /** Icon */
                icon: M.Util.getPropertyValue(layerDescription, "icon", M.Util.getImgUrl(M.Util.getPropertyValue(layerType, "icon", null))),
                /** True : the layer is load during startup */
                initial: M.Util.getPropertyValue(layerDescription, "initial", false),
                /** True : the layer content is initialized */
                initialized: M.Util.getPropertyValue(_options, "forceInitialized", false),
                /** True : the layer content is loaded */
                isLoaded: true,
                /** True : the layer is a raster layer */
                isRaster: layerType.isRaster ? true : false,
                /** Unique M identifier for this layer */
                MID: MID,
                /** LayerDescription for this layer : use for context saving */
                layerDescription: layerDescription,
                /** True : the layer is a mapshup layer - mapshup layers are not part of a saved context */
                MLayer: M.Util.getPropertyValue(layerDescription, "MLayer", false),
                /** True : avoid zoomon on layer name click in LayersManager panel */
                noZoomOn: M.Util.getPropertyValue(layerDescription, "noZoomOn", false),
                /** Pagination should be an object - see below */
                pagination: M.Util.getPropertyValue(layerDescription, "pagination", null),
                /** True : for catalog layers, quicklook attached to feature results can be added as an image layer on the map  */
                qlToMap: M.Util.getPropertyValue(layerDescription, "qlToMap", false),
                /** True : add refresh button in LayersManager panel */
                refreshable: M.Util.getPropertyValue(layerDescription, "refreshable", false),
                /** Refresh time interval for this layer is multicated by refreshFactor */
                refreshFactor: M.Util.getPropertyValue(layerDescription, "refreshFactor", 1),
                /** True : layer is selectable through click in the map (i.e. add to __CONTROL_SELECT__) */
                selectable: M.Util.getPropertyValue(layerDescription, "selectable", M.Util.getPropertyValue(layerType, "selectable", false)),
                /** True : no remove button in LayersManager panel */
                unremovable: M.Util.getPropertyValue(layerDescription, "unremovable", false),
                /** True : use Micro Info popup instead of Feature Info popup (see FeatureInfo.js) */
                microInfoTemplate: M.Util.getPropertyValue(layerDescription, "microInfoTemplate", {})

            };

            /*
             * Micro Info Template is automatically enabled for touch device
             */
            if (M.Util.device.touch) {
                options["_M"].microInfoTemplate.enable = true;
            }

            /*
             * Some OpenLayers properties are linked to layerType :
             * 
             *  - the projection object
             *  - the styleMap object
             *  - the cluster strategy
             */
            if (layerType.hasOwnProperty("projection")) {
                options.projection = layerType.projection;
            }

            /*
             * styleMap definition is retrieve from layerType
             * only if it is not already defined in option object
             * (i.e. previously declared within layerDescription.ol object)
             */
            if (layerType.hasStyleMap && !options.hasOwnProperty("styleMap")) {
                options.styleMap = ldo.getStyleMap();
            }

            /*
             * Add newLayer
             * 
             * TODO : layerDescription ou ldo ???
             */
            newLayer = layerType.add(layerDescription, options);

            /*
             * Add newLayer to map
             */
            if (newLayer) {

                /*
                 * Zoom on layer
                 */
                if (newLayer['_M'].layerDescription && newLayer['_M'].layerDescription.zoomOnNew) {
                    this.zoomTo(newLayer.getDataExtent() || newLayer["_M"].bounds, newLayer['_M'].layerDescription.zoomOnNew === 'always' ? false : true);
                }

                /*
                 * Tell user a non mapshup layer has been added (only if it has been loaded)
                 */
                /* TODO : remove
                 if (!newLayer["_M"].MLayer && newLayer["_M"].isLoaded) {
                 M.Util.message(M.Util._("Added")+ " : " + M.Util._(newLayer.name));
                 }
                 */

                /*
                 * If no "loadstart" has been defined
                 * => add a default "loadstart" event, i.e. show the loading indicator for newLayer
                 */
                if (!newLayer.events.listeners.hasOwnProperty('loadstart') || newLayer.events.listeners['loadstart'].length === 0) {
                    newLayer.events.register("loadstart", newLayer, function() {
                        M.Map.events.trigger("loadstart", newLayer);
                    });
                }

                /*
                 * If no "loadend" has been defined
                 * => add a default "loadend" event, i.e. hide the loading indicator for newLayer
                 */
                if (!newLayer.events.listeners.hasOwnProperty('loadend') || newLayer.events.listeners['loadend'].length === 0) {
                    newLayer.events.register("loadend", newLayer, function() {

                        /* OpenLayers bug with select control ? */
                        M.Map.resetControl();

                        /*
                         * Remove the load indicator
                         */
                        M.Map.events.trigger("loadend", newLayer);

                        /*
                         * If the layer is empty, it is automatically removed
                         * if its type 'removeOnEmpty' property is set to true
                         */
                        var layerType = M.Map.layerTypes[this["_M"].layerDescription.type];
                        if (layerType && layerType.removeOnEmpty) {

                            /*
                             * Layer is empty => remove it
                             */
                            if (M.Map.Util.layerIsEmpty(this)) {
                                M.Util.message(M.Util._("No result"));
                                return M.Map.removeLayer(this);
                            }
                        }

                        /*
                         * Set a flag to indicate that this layer has been initialized
                         */
                        this._M.initialized = true;

                        return true;

                    });
                }


                /*
                 * Set the visibility of the layer depending on the "hidden" property
                 * If hidden is set to true, the layer is not displayed
                 */
                if (layerDescription && layerDescription.hidden === true) {
                    this.Util.setVisibility(newLayer, false);
                }

                /*
                 * Add newLayer to the map
                 */
                this.map.addLayer(newLayer);

                /*
                 * Add newLayer to the TimeLine
                 */
                if (M.timeLine) {
                    M.timeLine.add(newLayer);
                }
                
                /*
                 * First baseLayer is set as the new baseLayer
                 * (i.e. replace the EmptyBaseLayer layer)
                 */
                if (newLayer.isBaseLayer && !this.hasNonEmptyBaseLayer) {
                    this.setBaseLayer(newLayer);
                    this.removeLayer(this.Util.getLayerByMID("EmptyBaseLayer"), false);
                    this.hasNonEmptyBaseLayer = true;
                }

                /*
                 * Force layer redraw (e.g. refreshable WFS)
                 */
                if (layerType && layerType.forceReload) {
                    newLayer.redraw();
                }

                /*
                 * Add to selectable list
                 */
                if (newLayer["_M"] && newLayer["_M"].selectable) {
                    this.selectableLayers.add(newLayer);
                }

                /*
                 * Add to layersGroups if a groupName is defined
                 */
                if (newLayer["_M"].layerDescription && newLayer["_M"].layerDescription.groupName) {

                    var layerGroup = this.layersGroups[newLayer["_M"].layerDescription.groupName];

                    /*
                     * layerGroup does not exist => create it
                     */
                    if (!layerGroup) {
                        layerGroup = new this.LayersGroup(newLayer["_M"].layerDescription.groupName, null);
                        this.layersGroups[newLayer["_M"].layerDescription.groupName] = layerGroup;
                    }

                    /*
                     * Add the newLayer
                     */
                    newLayer["_M"].group = layerGroup;
                    layerGroup.add(newLayer);
                }

                /* 
                 * Trigger events layersend
                 */
                this.events.trigger("layersend", {
                    action: "add",
                    layer: newLayer
                });

                /*
                 * Set opacity
                 */
                if (layerDescription.opacity) {
                    newLayer.setOpacity(layerDescription.opacity);
                }

            }

            return newLayer;
        },
        /**
         * Method: getState
         * Get the current map state and return it.
         *
         * Returns:
         * {Object} An object representing the current state.
         */
        getState: function() {
            return {
                center: this.map.getCenter(),
                resolution: this.map.getResolution(),
                projection: this.map.getProjectionObject(),
                units: this.map.getUnits() || this.map.units || this.map.baseLayer.units
            };
        },
        /**
         * Load a context
         * 
         * Note : every property in a context is optional
         * 
         * Context structure :
         * {
         *      location:{
         *          bg:// Active background layer identifier
         *          lon:// Longitude of map center
         *          lat:// Latitude of map center
         *          zoom:// zoom level of map
         *      },
         *      layers:[
         *          // Layerdescription
         *      ]
         * }
         * 
         * @param {Object} context
         */
        loadContext: function(context) {

            var id, b, layer, i, j, k, l, s,
                    self = this;

            /*
             * Paranoid mode
             */
            context = context || {};

            /*
             * Set location
             */
            if (context.location) {
                self.map.setCenter(self.Util.d2p(new OpenLayers.LonLat(context.location.lon, context.location.lat)), Math.max(context.location.zoom, self.lowestZoomLevel));
            }

            /*
             * Set layers
             */
            context.layers = context.layers || [];

            /*
             * Parse context layer descriptions and compare
             * it with existing layers.
             * 
             * Three case are possibles :
             * 
             *   - context layers that are not in the map are added
             *   - map layers that are not in the context are removed
             *   - context layers that are already in the map are updated
             */

            /*
             * Remove layers
             */
            for (i = 0, l = self.map.layers.length; i < l; i++) {

                /*
                 * By default, remove the layer
                 */
                b = true;

                layer = self.map.layers[i];

                /*
                 * mapshup layers are excluded from the processing
                 */
                if (layer && layer["_M"] && !layer["_M"].MLayer) {

                    id = layer["_M"].MID;

                    /*
                     * Roll over context layers
                     */
                    for (j = 0, k = context.layers.length; j < k; j++) {

                        /*
                         * The layer is present in the context layer list. No need to remove it
                         */
                        if (id === (new self.LayerDescription(context.layers[j], self)).getMID()) {
                            b = false;
                            break;

                        }

                    }

                    /*
                     * Remove the layer
                     */
                    if (b) {
                        self.removeLayer(self.Util.getLayerByMID(id), false);
                    }

                }


            }

            /*
             * Add or update layers
             */
            for (i = 0, l = context.layers.length; i < l; i++) {

                id = (new self.LayerDescription(context.layers[i], self)).getMID();

                /*
                 * By default, add the layer
                 */
                b = true;

                /*
                 * Roll over existing layers
                 */
                for (j = 0, k = self.map.layers.length; j < k; j++) {

                    layer = self.map.layers[j];

                    /*
                     * mapshup layers are excluded from the processing
                     */
                    if (layer["_M"] && !layer["_M"].MLayer) {

                        /*
                         * The layer already exist - update it
                         */
                        if (id === layer["_M"].MID) {
                            b = false;
                            break;
                        }

                    }

                }

                /*
                 * Add layer
                 */
                if (b) {
                    self.addLayer(context.layers[i], {
                        noDeletionCheck: true,
                        forceInitialized: true
                    });
                }
                /*
                 * Update layer
                 */
                else {

                    /*
                     * Set visibility
                     */
                    if (!layer.isBaseLayer) {
                        M.Map.Util.setVisibility(layer, !context.layers[i].hidden);
                    }

                    /*
                     * Launch search on catalogs
                     */
                    s = context.layers[i].search;

                    if (s) {

                        //
                        // Update the search items
                        //
                        layer["_M"].searchContext.items = s.items;

                        //
                        // Launch unitary search
                        //
                        layer["_M"].searchContext.search({nextRecord: s.nextRecord});

                    }

                }
            }

            /*
             * Set default background
             */
            if (context.location.bg) {
                layer = self.Util.getLayerByMID(context.location.bg);
                if (layer && layer.isBaseLayer) {
                    self.setBaseLayer(layer);
                }
            }

        },
        /*
         * Get current map context represented by
         * 
         *  - map extent
         *  - layers list
         *  - MMI status (TODO)
         *  
         */
        getContext: function() {

            var i, l, layer, c, key, center, ld, self = this;

            /*
             * Get map center in Lat/Lon (epsg:4326)
             */
            center = self.Util.p2d(self.map.getCenter());

            /*
             * Initialize context
             */
            c = {
                location: {
                    bg: self.map.baseLayer["_M"].MID,
                    lat: center.lat,
                    lon: center.lon,
                    zoom: self.map.getZoom()
                },
                layers: []
            };

            /*
             * Roll over each layer
             */
            for (i = 0, l = self.map.layers.length; i < l; i++) {

                /*
                 * Retrieve current layer
                 */
                layer = self.map.layers[i];

                /*
                 * mapshup layers (i.e. MLayer) are not stored in the context
                 */
                if (layer["_M"] && layer["_M"].layerDescription && !layer["_M"].MLayer) {

                    /*
                     * Initialize object with an initial property set to true to indicate that
                     * layer has been added through context
                     */
                    ld = {
                        initial: true
                    };

                    /*
                     * Clone layerDescription omitting layer and ol properties
                     * to avoid serialization cycling during JSON.stringify processing
                     */
                    for (key in layer["_M"].layerDescription) {
                        if (key !== "layer" && key !== "ol") {
                            ld[key] = layer["_M"].layerDescription[key];
                        }
                    }

                    /*
                     * Layer is hidden
                     */
                    ld.hidden = !layer.getVisibility() && !layer.isBaseLayer ? true : false;

                    /*
                     * Layer got a non empty searchContext
                     */
                    if (layer["_M"].searchContext && layer["_M"].searchContext.items.length > 0) {
                        ld.search = {
                            MID: layer["_M"].MID,
                            items: layer["_M"].searchContext.items,
                            nextRecord: layer["_M"].searchContext.nextRecord
                        };
                    }

                    /*
                     * Add a layer description
                     */
                    c.layers.push(ld);

                }

            }

            /*
             * Return context
             */
            return c;
        },
        /**
         * Map initialization
         *
         * config : M.config object
         * urlParameters: window.location.href key/value pair if any
         * 
         * @param {Object} _config
         */
        init: function(_config) {

            /*
             * Reference to Map object
             */
            var self = this;

            /**
             * Set OpenLayers config option
             */
            OpenLayers.IMAGE_RELOAD_ATTEMPTS = 2;

            /**
             * Set the ProxyHost URL to bypass cross-scripting javascript
             */
            OpenLayers.ProxyHost = M.Util.proxify("");

            /**
             * Disable select feature on map pan
             */
            OpenLayers.Handler.Feature = OpenLayers.Class(OpenLayers.Handler.Feature, {
                stopDown: false
            });

            /*
             * OpenLayers 2.12 support SVG2 by default
             */
            OpenLayers.Layer.Vector.prototype.renderers = ["SVG", "VML"];

            /**
             * Force M CSS to overide default OpenLayers CSS
             */
            _config.mapOptions.theme = null;

            /*
             * Set initialLocation
             */
            self.initialLocation = _config["general"].location;

            /*
             * Hack : if Map height is set to auto, it is assumed that the Map div
             * height cover 100% of the navigator window minus a fixed sized header.
             * So the Map height is set to window height minus Map.css('top') value
             * The 'processHeightOnResize' class is also added to M.$map in order to
             * reprocess the width when window is resized (see M.resize() method)
             */
            if (M.$map.css('height') === 'auto' || M.$map.css('height') === '0px') {
                M.$map.css('height', window.innerHeight - M.$map.offset().top);
                M.$map.addClass('processHeightOnResize');
            }

            /**
             * Prepare the navigation control
             *
             * If device is a touch device, enable TouchNavigation
             * instead of Navigation
             */
            var opt = {
                id: "__CONTROL_NAVIGATION__",
                documentDrag: true,
                /* Disable oncontextmenu on right clicks */
                handleRightClicks: true,
                zoomWheelEnabled: true,
                mouseWheelOptions: {
                    interval: 50,
                    cumulative: false
                },
                dragPanOptions: {
                    enableKinetic: true,
                    /*
                     * When drag starts, store the clicked point and the time of click in milliseconds
                     */
                    panMapStart: function(e) {

                        // Begin reproduce OpenLayers DragPan.js panMapStart function
                        if (this.kinetic) {
                            this.kinetic.begin();
                        }
                        // End reproduce OpenLayers DragPan.js panMapStart function
                        self._clk = {
                            x: e.x,
                            y: e.y,
                            d: (new Date()).getTime()
                        };

                        return true;
                    },
                    /*
                     * When mouse up, if mouse have not moved and if the time between up and down is large enough,
                     * then display the contextual menu
                     */
                    panMapUp: function(e) {
                        if (self._clk) {

                            /*
                             * No drag occured
                             */
                            if (e.x === self._clk.x && e.y === self._clk.y) {

                                /*
                                 * User clicks was larger enough to display the contextual menu
                                 */
                                if ((new Date()).getTime() - self._clk.d > 200) {
                                    self.mouseClick = {
                                        x: e.x,
                                        y: e.y
                                    };
                                    M.menu.show();
                                }
                                /*
                                 * Short click -> trigger a mapclicked event
                                 */
                                else {
                                    M.Map.events.trigger('mapclicked', e);
                                }
                            }
                        }
                        return true;
                    }
                }
            };
            _config.mapOptions.controls = M.Util.device.touch ? [new OpenLayers.Control.TouchNavigation(opt)] : [new OpenLayers.Control.Navigation(opt)];

            /**
             * Create the mapfile
             */
            self.map = new OpenLayers.Map(M.$map.attr('id'), _config.mapOptions);

            /**
             * Add a very first empty baseLayer to the map
             * Usefull to avoid crash if no baseLayer are specified
             */
            self.map.addLayer(new OpenLayers.Layer("EmptyBaseLayer", {
                _M: {
                    MID: "EmptyBaseLayer"
                },
                isBaseLayer: true,
                displayInLayerSwitcher: false
            }));

            /*
             * Create an events object
             */
            self.events = new self.Events(self);

            /*
             * Initialize featureInfo
             */
            self.featureInfo = new self.FeatureInfo();

            /*
             * Update menu position on map move
             */
            self.map.events.register('move', self.map, function() {
                if (M.menu) {
                    M.menu.updatePosition();
                }
            });

            /*
             * Call Map 'moveend' events on map 'moveend'
             */
            self.map.events.register('moveend', self, function() {

                /*
                 * Propagate moveend to registered plugin
                 */
                M.Map.events.trigger('moveend');

                /*
                 * Store the new lastExtent
                 */
                self.currentState = M.Map.getState();

                /*
                 * Set Geohash
                 * 
                 * Note : if _bof is set to true, then it means that the map moved after
                 * a user click on back or forward button. Thus the map is not center again
                 * to avoid infinite loop
                 */
                if (!self._bof) {
                    if (M.Config["general"].enableHistory) {
                        self.hash = M.Map.Util.Geohash.encode(self.Util.p2d(self.map.getCenter().clone())) + ":" + self.map.getZoom();
                        window.location.hash = self.hash;
                    }
                    self._bof = false;
                }
                else {
                    self._bof = false;
                }

            });

            /*
             * Detect back/forward click
             */
            setInterval(function() {

                /*
                 * Note : set _bof to true to indicates not to recenter map on moveend
                 * (avoid infinite loop)
                 */
                if (self.hash && (window.location.hash !== self.hash)) {

                    self.hash = window.location.hash;
                    self._bof = true;

                    /* hash structure is '#<geohash>:<zoomLevel>' */
                    var a = self.hash.split(':');
                    self.map.setCenter(self.Util.d2p(self.Util.Geohash.decode(a[0])), parseInt(a[1]));

                }

            }, 100);

            /**
             * onmouseover event definition is only
             * valid if the current device is not a touch device
             */
            if (!M.Util.device.touch) {

                /*
                 * Create "coords" div to display mouse position info
                 */
                if (_config["general"].displayCoordinates) {

                    /*
                     * "coords" is created under Map only if it's not already defined within the html page
                     * (Note : this allow to display coordinates outside the map)
                     */
                    if ($('#coords').length === 0) {
                        M.Util.$$('#coords', M.$map);
                    }

                    self.$coords = $('#coords');
                }

                /*
                 * Create jHiliteFeature div to display hilited feature info
                 */
                if (_config["general"].featureHilite) {

                    /*
                     * featureHilite is created under Map only if it's not already defined within the html page
                     * (Note : this allow to display hilited feature info outside the map)
                     */
                    if (self.$featureHilite.length === 0) {
                        self.$featureHilite = M.Util.$$('#' + M.Util.getId(), M.$map).addClass("featureHilite").hide();
                    }

                }

                /*
                 * Define action on mousemove
                 */
                self.map.events.register('mousemove', self.map, function(e) {

                    /*
                     * Set the mousePosition object
                     */
                    var offset = M.$map.offset();

                    M.Map.mousePosition = new OpenLayers.Pixel(e.pageX - offset.left, e.pageY - offset.top);

                    /*
                     * Display the mouse position if Config.general.displayCoordinates is set to true
                     */
                    if (_config["general"].displayCoordinates) {
                        M.Map.$coords.html(M.Map.Util.getFormattedLonLat(self.Util.p2d(M.Map.map.getLonLatFromPixel(M.Map.mousePosition)), M.Config["general"].coordinatesFormat)).css({
                            'top': M.Map.mousePosition.y - 20,
                            'left': M.Map.mousePosition.x
                        }).show();

                    }

                    /*
                     * Display hilited feature
                     */
                    self.$featureHilite.css({
                        'top': M.Map.mousePosition.y + 30,
                        'left': M.Map.mousePosition.x + 15
                    });

                    return true;
                });

                /*
                 * Hide divs when mouse is outside of M.$map
                 */
                self.map.events.register('mouseout', self.map, function(e) {
                    if (M.Map.$coords) {
                        M.Map.$coords.hide();
                    }
                    self.$featureHilite.hide();
                    return true;
                });

            }

            /*******************************************
             *
             * Groups
             *
             *******************************************/

            /**
             * Initialize groups
             */
            if (_config.groups) {
                for (var i = 0, l = _config.groups.length; i < l; i++) {
                    self.layersGroup[_config.groups.name] = new self.LayersGroup(self, _config.groups.name, _config.groups.icon);
                }
            }

            /**************************************************
             *
             * Controls
             *
             *  Controls ids should be defined as follow :
             *  __CONTROL_NAME_OF_CONTROL__
             *
             **************************************************/

            var controls = [];

            /*
             * ScaleLine control
             */
            if (_config.general.displayScale && $.isFunction(OpenLayers.Control.ScaleLine)) {
                controls.push(new OpenLayers.Control.ScaleLine({
                    id: "__CONTROL_SCALELINE__",
                    /* Geodetic measurement is activated for non plate carree measurements */
                    geodetic: self.map.getProjectionObject().projCode === "EPSG:4326" ? false : true
                }));
            }

            /*
             * Select feature Control :
             *  This control is always active except during drawing
             */
            controls.push(new OpenLayers.Control.SelectFeature(self.selectableLayers.items, {
                id: "__CONTROL_SELECT__",
                clickout: false,
                toggle: true,
                multiple: false,
                hover: false
            }));

            /*
             * Hilite feature Control
             */
            if (_config.general.featureHilite) {
                controls.push(new OpenLayers.Control.SelectFeature(self.hilitableLayers.items, {
                    id: "__CONTROL_HIGHLITE__",
                    hover: true,
                    highlightOnly: true,
                    eventListeners: {
                        beforefeaturehighlighted: function(e) {

                            /*
                             * If menu is visible do not hilite feature
                             * to avoid 'post modern art flickering' effect
                             */
                            if (M.menu && M.menu.$m.is(':visible')) {
                                return false;
                            }

                            /*
                             * Paranoid mode
                             */
                            if (e.feature) {

                                /*
                                 * Change mouse cursor to pointer hover feature
                                 */
                                if (e.feature.geometry) {
                                    $('#' + e.feature.geometry.id).css('cursor', 'pointer');
                                }

                                /*
                                 * Never hilite an already selected or hilited feature
                                 */
                                if (M.Map.featureInfo.hilited) {
                                    self.$featureHilite.empty().hide();
                                    return true;
                                }
                                if (M.Map.featureInfo.selected) {
                                    if (M.Map.featureInfo.selected.id === e.feature.id) {
                                        self.$featureHilite.empty().hide();
                                        return false;
                                    }
                                }

                                /*
                                 * Title is first 'name' or 'title' or 'identifier' or 'id'
                                 */
                                self.$featureHilite.html(M.Util.stripTags(M.Map.Util.Feature.getTitle(e.feature))).attr("hilited", "hilited").show();

                            }

                            return true;
                        },
                        featureunhighlighted: function(e) {
                            self.$featureHilite.empty().attr("hilited", "").hide();
                        }
                    }
                }));
            }

            /*
             * Attribution control (see OpenLayers documentation)
             */
            controls.push(new OpenLayers.Control.Attribution({
                id: "__CONTROL_ATTRIBUTION__"
            }));

            /*
             * Add controls to the map
             */
            self.map.addControls(controls);

            /*
             * Overview map control
             */
            if (_config.general.overviewMap !== "none") {
                var overviewMapExtent = new OpenLayers.Bounds(-180, -90, 180, 90);
                var overviewMapControl = new OpenLayers.Control.OverviewMap({
                    mapOptions: {
                        theme: false,
                        projection: this.map.displayProjection,
                        maxExtent: overviewMapExtent,
                        numZoomLevels: 1,
                        autoPan: false,
                        restrictedExtent: overviewMapExtent
                    },
                    /* Overviewmap is visibility */
                    maximized: _config.general.overviewMap === "opened" ? true : false,
                    size: new OpenLayers.Size('250', '125'),
                    layers: [new OpenLayers.Layer.Image('ImageLayer', M.Util.getImgUrl('overviewmap.png'),
                                overviewMapExtent,
                                new OpenLayers.Size('250', '125')
                                )]
                });
                self.map.addControl(overviewMapControl);
            }

            /**
             * Set map center.
             * Map is centered at initialLocation unless a map.restrictedExtent
             * is defined. In this case, the map is centered to this restrictedExtent
             */
            self.map.restrictedExtent ? self.map.zoomToExtent(self.map.restrictedExtent) : self.setCenter(self.Util.d2p(new OpenLayers.LonLat(self.initialLocation.lon, self.initialLocation.lat)), self.initialLocation.zoom, true);

            /**
             * Set lowest zoom level
             * The map cannot be zoomout to a lower value than lowestZoomLevel
             */
            //self.lowestZoomLevel = self.map.getZoom();
            self.lowestZoomLevel = 0;

            /**
             * Set timer for layers with automatic refresh
             * We do not use a setInterval function but a
             * synchronized setTimeout to guarantee that actions
             * are executed before a new setTimeout is launched
             */
            (function loopsiloopsi() {
                /**
                 * Update the refreshCycle counter
                 */
                M.Map.refreshCycle++;
                var i,
                        layer;
                for (i = M.Map.map.layers.length; i--; ) {
                    layer = M.Map.map.layers[i];

                    /**
                     * Switch over non-backgrounds layer
                     * (i.e. isBaseLayer = false)
                     */
                    if (!layer.isBaseLayer && layer["_M"]) {
                        if (layer["_M"].refresh && ((M.Map.refreshCycle % layer["_M"].refreshFactor) === 0)) {
                            layer.refresh({
                                force: true
                            });
                        }

                    }
                }

                /**
                 * Respawn a timeout AFTER previous code has been executed
                 */
                window.setTimeout(loopsiloopsi, M.Config["general"].refreshInterval || 1000);

            })();

            /*
             * Set __CONTROL_NAVIGATION__ the default map control
             */
            self.resetControl(self.Util.getControlById("__CONTROL_NAVIGATION__"));

            /*
             * Add M event : update map size when window size change
             */
            M.events.register("resizeend", self, function(self) {

                /*
                 * Update map size
                 */
                self.map.updateSize();

                /*
                 * Trigger 'resizeend' event for each registered plugins
                 */
                self.events.trigger("resizeend");

            });

        },
        /**
         * Remove layer
         * 
         * @param {OpenLayers.Layer} layer
         * @param {boolean} confirm : if true, user is asked to confirm deletion
         */
        removeLayer: function(layer, confirm) {

            /*
             * Paranoid mode
             */
            if (!layer) {
                return false;
            }

            /*
             * Ask for deletion :
             *  - if it is requested in the query
             *  - and if M.Config.general.confirmDeletion is set to true
             */
            if (confirm && M.Config["general"].confirmDeletion) {


                M.Util.askFor({
                    title: M.Util._("Delete layer"),
                    content: M.Util._("Do you really want to remove layer") + " " + layer.name,
                    dataType: "list",
                    value: [{
                            title: M.Util._("Yes"),
                            value: "y"
                        },
                        {
                            title: M.Util._("No"),
                            value: "n"
                        }
                    ],
                    callback: function(v) {
                        if (v === "y") {
                            M.Map.removeLayer(layer);
                        }
                    }
                });

                return false;
            }

            /*
             * !Important! set a layer._tobedestroyed property to true
             * to indicate to M processing that this layer will be 
             * removed at the end of this function
             * (e.g. see LayersManager plugin)
             */
            layer._tobedestroyed = true;

            /*
             * Trigger "layersend" to registered handlers
             */
            this.events.trigger("layersend", {
                action: "remove",
                layer: layer
            });

            /*
             * Remove layer from selectableLayers list
             * !!! important !!!
             */
            this.selectableLayers.remove(layer);

            /*
             * Remove layer from group if any
             */
            if (layer["_M"] && layer["_M"].group) {
                layer["_M"].group.remove(layer);
            }

            /*
             * Remove SearchContext within layer
             */
            if (layer["_M"] && layer["_M"].SearchContext) {
                M.remove(layer["_M"].SearchContext);
            }

            /*
             * Remove layer from TimeLine
             */
            if (M.timeLine) {
                M.timeLine.remove(layer);
            }
            
            /*
             * If layer is an initial layer then its MID is stored
             * to be sure that it will be indicated as removed in the
             * getContext method
             */
            if (layer["_M"] && layer["_M"].MLayer) {
                this.removedLayers.push({
                    MID: layer["_M"].MID,
                    layerDescription: layer["_M"].layerDescription
                });
            }

            /*
             * Finally remove the layer from M.Map.map.layers
             */
            layer.destroy();

            return true;

        },
        /**
         * Deactivate control and activate SelectFeature control
         * 
         * @param {OpenLayers.Control} control
         */
        resetControl: function(control) {

            if (control) {
                control.deactivate();
                this.Util.getControlById("__CONTROL_NAVIGATION__").activate();
            }

            /*
             * Is this an OpenLayers bug ?
             * We need to reactivate the hfControl and AFTER the sfControl
             * to ensure that highlite/select controls are actives
             */
            var sfControl = this.Util.getControlById("__CONTROL_SELECT__"),
                    hfControl = this.Util.getControlById("__CONTROL_HIGHLITE__");

            if (sfControl) {

                /*
                 * Deactivate Select Feature control
                 */
                sfControl.deactivate();

                /*
                 * Deactivate Highlite Feature control if exists
                 */
                if (hfControl) {
                    hfControl.deactivate();
                }

                /*
                 * Reactivate Select/Highlite feature controls if
                 * selectable layers are defined
                 */
                if (this.selectableLayers.items.length > 0) {

                    /*
                     * First highlite...
                     */
                    if (hfControl) {
                        hfControl.activate();
                    }

                    /*
                     * ...and AFTER select
                     */
                    sfControl.activate();
                }
            }
        },
        /*
         * Setcenter
         */
        setCenter: function(lonlat, zoom, doNotLog) {

            /*
             * Tell Map not to log this map move
             */
            this.doNotLog = doNotLog || false;

            if (zoom !== null) {
                this.map.setCenter(lonlat, zoom);
            }
            else {
                if (M.Util.device.touch || M.Config["general"].teleport) {
                    this.map.setCenter(lonlat);
                }
                else {
                    this.map.panTo(lonlat);
                }
            }
        },
        /**
         * Zoom to the input bounds + a half of the bounds
         * If bounds is too small (point), then the map is centered on the
         * bounds with a zoom level of 14
         * 
         * @param {OpenLayers.Bounds} bounds
         * @param {boolean} partial // if true then only zoom if bounds does not intersect map extent
         */
        zoomTo: function(bounds, partial) {
            
            var self = this;

            /*
             * Paranoid mode
             */
            if (!bounds) {
                return;
            }
            
            /*
             * Do not zoom if input bounds intersect map bounds
             */
            if (partial && self.map.getExtent().containsBounds(bounds, true)) {
                return;
            }
            
            /*
             * Get the bounds + a half of the bounds 
             */
            var w = bounds.getWidth(),
                    h = bounds.getHeight(),
                    c = bounds.getCenterLonLat(),
                    e = M.Util._("Cannot zoom : this feature is outside authorized extent");

            /**
             * Bounds is too small => center to bounds
             */
            if (w < 1 && h < 1) {
                if (self.map.restrictedExtent && !self.map.restrictedExtent.containsBounds(bounds, true)) {
                    M.Util.message(e);
                }
                else {
                    self.map.setCenter(c, Math.max(9, self.map.getZoom()));
                }
            }
            /**
             * Bounds is ok => zoom to bounds + quarter of bounds
             */
            else {
                if (self.map.restrictedExtent && !self.map.restrictedExtent.containsBounds(bounds, true)) {
                    M.Util.message(e);
                }
                else {
                    self.map.zoomToExtent(bounds);
                }
            }

        },
        /*
         * Change base layer and update projection
         * if needed
         * 
         * @param {OpenLayers.Layer} baseLayer
         */
        setBaseLayer: function(baseLayer) {
            
            var i, j, mapProj, baseProj;
            
            /*
             * Set new base baseLayer
             */
            this.map.setBaseLayer(baseLayer);
            
            /*
             * This is mandatory to force unzoom to level lower than 3
             * Note : OpenLayers Bug ???
             */
            this.map.baseLayer.wrapDateLine = false;
            
            /*
             * The current map projection
             */
            mapProj = (this.map.projection && this.map.projection instanceof OpenLayers.Projection) ? this.map.projection : new OpenLayers.Projection(this.map.projection);
            
            /*
             * The projection of the new base layer
             */
            baseProj = baseLayer.projection;
            
            /*
             * If new base layer projection is different from the map projection
             * we need to change the map projection to the new base layer projection
             * and reproject every non base layers
             */
            if (!(baseProj.equals(mapProj))) {
                
                /*
                 * Set map projection to new base layer projection
                 */
                this.map.projection = baseProj;
                this.map.maxExtent = baseLayer.maxExtent;
                
                this.map.setCenter(this.map.getCenter().transform(mapProj, baseProj), this.map.getZoom(), false, true);
                
                /*
                 * Reproject all map layers that are not base layers
                 */
                for (i = 0; i < this.map.layers.length; i++) {
                    
                    if (this.map.layers[i].isBaseLayer === false) {
                 
                        this.map.layers[i].addOptions({projection: baseProj}, true);
                        
                        /*
                         * Reproject vector features
                         */ 
                        if ($.isArray(this.map.layers[i].features)) {
                            for (j = 0; j < this.map.layers[i].features.length; j++) {
                                this.map.layers[i].features[j].geometry.transform(mapProj, baseProj);
                            }
                        }
                        
                        this.map.layers[i].redraw();
                        
                    }
                }
                
                /*
                 * Force map size update to ensure Google layers to be redrawn
                 * correctly
                 */
                this.map.updateSize();
            }
            
        }

    };

})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Define M.Map util functions
 */
(function(M, Map) {

    /*
     * Initialize M.Map.Util
     */
    Map.Util = {};

    /**
     * Geohash library for Javascript
     * https://github.com/davetroy/geohash-js
     * 
     * (c) 2008 David Troy
     * Distributed under the MIT License
     * 
     */
    Map.Util.Geohash = {
        BITS: [16, 8, 4, 2, 1],
        BASE32: "0123456789bcdefghjkmnpqrstuvwxyz",
        refineInterval: function(interval, cd, mask) {
            if (cd & mask) {
                interval[0] = (interval[0] + interval[1]) / 2;
            }
            else {
                interval[1] = (interval[0] + interval[1]) / 2;
            }
        },
        decode: function(geohash) {
            var c, cd, mask, i, j, is_even = 1, lat = [], lon = [], lat_err = 90.0, lon_err = 180.0;

            lat[0] = -90.0;
            lat[1] = 90.0;
            lon[0] = -180.0;
            lon[1] = 180.0;

            /*
             * Remove trailing '#'
             */
            geohash = geohash.replace('#', '');

            for (i = 0; i < geohash.length; i++) {
                c = geohash.charAt(i);
                cd = this.BASE32.indexOf(c);
                for (j = 0; j < 5; j++) {
                    mask = this.BITS[j];
                    if (is_even) {
                        lon_err /= 2;
                        this.refineInterval(lon, cd, mask);
                    }
                    else {
                        lat_err /= 2;
                        this.refineInterval(lat, cd, mask);
                    }
                    is_even = !is_even;
                }
            }
            lat[2] = (lat[0] + lat[1]) / 2;
            lon[2] = (lon[0] + lon[1]) / 2;

            return new OpenLayers.LonLat(lon[1], lat[1]);
        },
        encode: function(point) {

            var mid, is_even = 1, i = 0, lat = [], lon = [], bit = 0, ch = 0, precision = 12, geohash = "";

            lat[0] = -90.0;
            lat[1] = 90.0;
            lon[0] = -180.0;
            lon[1] = 180.0;

            while (geohash.length < precision) {
                if (is_even) {
                    mid = (lon[0] + lon[1]) / 2;
                    if (point.lon > mid) {
                        ch |= this.BITS[bit];
                        lon[0] = mid;
                    } else
                        lon[1] = mid;
                } else {
                    mid = (lat[0] + lat[1]) / 2;
                    if (point.lat > mid) {
                        ch |= this.BITS[bit];
                        lat[0] = mid;
                    } else
                        lat[1] = mid;
                }

                is_even = !is_even;
                if (bit < 4)
                    bit++;
                else {
                    geohash += this.BASE32.charAt(ch);
                    bit = 0;
                    ch = 0;
                }
            }
            return '#' + geohash;
        }

    };

    /**
     * Feature functions
     */
    Map.Util.Feature = {
        /**
         *
         * Return feature icon url
         * 
         * Icon is assumed to be a square image of 75x75 px displayed within NorthPanel
         *
         * @param {OpenLayers.Feature} feature : input feature
         *
         */
        getIcon: function(feature) {

            var style, defaultStyle, icon;

            /*
             * Paranoid mode
             */
            if (!feature) {
                return icon;
            }

            /*
             * Guess icon with the following preference order :
             * 
             *    - attributes icon
             *    - attributes thumbnail
             *    - attributes quicklook
             *    - attributes imageUrl
             *    - feature style externalGraphic
             *    - icon based on type (i.e. point, line or polygon)
             *    - generic image 
             * 
             */
            if (feature.attributes.icon) {
                return feature.attributes.icon;
            }
            if (feature.attributes.thumbnail) {
                return feature.attributes.thumbnail;
            }
            if (feature.attributes.quicklook) {
                return feature.attributes.quicklook;
            }
            if (feature.attributes.imageUrl) {
                return feature.attributes.imageUrl;
            }

            /*
             * This is quite experimental :)
             */
            if (feature.layer) {

                /*
                 * Get the default style object from styleMap
                 */
                style = feature.layer.styleMap.styles["default"];

                /*
                 * The defaultStyle descriptor should be defined directly
                 * under feature.style property. If not, there is always
                 * a valid defaultStyle descriptor under feature.layer.styleMap.styles["default"]
                 */
                defaultStyle = feature.style || style.defaultStyle;
                if (defaultStyle.externalGraphic) {
                    return Map.Util.KML.resolveStyleAttribute(feature, style, defaultStyle.externalGraphic);
                }
            }

            /*
             * Icon based on type (point, linestring, polygon)
             */
            if (feature.geometry) {
                switch (feature.geometry.CLASS_NAME.replace("OpenLayers.Geometry.", "")) {
                    case "MultiPolygon":
                    case "Polygon":
                        return M.Util.getImgUrl("polygon.png");
                        break;
                    case "MultiLineString":
                    case "LineString":
                        return M.Util.getImgUrl("line.png");
                        break;
                    case "MultiPoint":
                    case "Point":
                        return M.Util.getImgUrl("point.png");
                        break;
                }
            }

            return icon;
        },
        /**
         * Return feature title if it's defined within layerDescription.featureInfo.title property
         *
         * @param {OpenLayers.Feature} feature : input feature
         */
        getTitle: function(feature) {

            var k;

            /*
             * Paranoid mode
             */
            if (!feature) {
                return null;
            }

            /*
             * First check if feature is a cluster
             */
            if (feature.cluster && feature.cluster.length > 0) {
                return M.Util._(feature.layer.name) + ": " + feature.cluster.length + " " + M.Util._("entities");
            }

            /*
             * User can define is own title with layerDescription.featureInfo.title property
             */
            if (feature.layer && feature.layer["_M"].layerDescription.featureInfo && feature.layer["_M"].layerDescription.featureInfo.title) {

                /*
                 * The tricky part :
                 * 
                 * Parse title and replace keys between dollars $$ with the corresponding value
                 * eventually transformed with the getValue() function
                 *
                 * Example :
                 *      title = "Hello my name is $name$ $surname$"
                 *      feature.attributes = {name:"Jerome", surname:"Gasperi"}
                 *
                 *      will return "Hello my name is Jerome Gasperi"
                 * 
                 */
                return feature.layer["_M"].layerDescription.featureInfo.title.replace(/\$+([^\$])+\$/g, function(m, key, value) {
                    var k = m.replace(/[\$\$]/g, '');
                    return Map.Util.Feature.getValue(feature, k, feature.attributes[k]);
                });

            }

            /*
             * Otherwise returns in the following order (first found = exit)
             *  name, title, identifier, fid or id or ""
             */
            for (k in {
                name: 1,
                title: 1,
                identifier: 1
            }) {
                if (feature.attributes[k]) {
                    return Map.Util.Feature.getValue(feature, k, feature.attributes[k]);
                }
            }
            if (feature.fid) {
                return Map.Util.Feature.getValue(feature, 'identifier', feature.fid);
            }
            return feature.id || "";

        },
        /*
         * Get feature attribute value
         * 
         * If layerDescription.featureInfo.keys array is set and if a value attribute is set for "key"
         * then input value is transformed according to the "value" definition
         *
         * @param {OpenLayers.Feature} feature : feature reference
         * @param {String} key : key attribute name
         * @param {String} value : value of the attribute
         */
        getValue: function(feature, key, value) {

            var k, keys;

            /*
             * Paranoid mode
             */
            if (!feature || !key) {
                return value;
            }

            /*
             * Check if keys array is defined
             */
            if (feature.layer && feature.layer["_M"].layerDescription.hasOwnProperty("featureInfo")) {

                keys = feature.layer["_M"].layerDescription.featureInfo.keys || [];

                /*
                 * Roll over the featureInfo.keys associative array.
                 * Associative array entry is the attribute name (i.e. key)
                 * 
                 * This array contains a list of objects
                 * {
                 *      v: // Value to display instead of key
                 *      transform: // function to apply to value before instead of directly displayed it
                 *            this function should returns a string
                 * }
                 */
                for (k in keys) {

                    /*
                     * If key is found in array, get the corresponding value and exist the loop
                     */
                    if (key === k) {

                        /*
                         * Transform value if specified
                         */
                        if ($.isFunction(keys[k].transform)) {
                            return keys[k].transform(value);
                        }
                        break;
                    }
                }

            }

            /*
             * In any case returns input value
             */
            return value;
        },
        /*
         * Replace input key into its "human readable" equivalent defined in layerDescription.featureInfo.keys associative array
         *
         * @param {String} key : key to replace
         * @param {OpenLayers.Feature} feature : feature reference
         */
        translate: function(key, feature) {

            var c, k, keys;

            /*
             * Paranoid mode
             */
            if (!feature || !key) {
                return M.Util._(key);
            }

            /*
             * Check if keys array is defined
             * This array has preseance to everything else
             */
            if (feature.layer["_M"].layerDescription.hasOwnProperty("featureInfo")) {

                keys = feature.layer["_M"].layerDescription.featureInfo.keys || [];

                /*
                 * Roll over the featureInfo.keys associative array.
                 * Associative array entry is the attribute name (i.e. key)
                 * 
                 * This array contains a list of objects
                 * {
                 *      v: // Value to display instead of key
                 *      transform: // function to apply to value before instead of directly displayed it
                 *            this function should returns a string
                 * }
                 */
                for (k in keys) {

                    /*
                     * If key is found in array, get the corresponding value and exist the loop
                     */
                    if (key === k) {

                        /*
                         * Key value is now "v" value if specified
                         */
                        if (keys[k].hasOwnProperty("v")) {
                            return M.Util._(keys[k].v);
                        }

                        break;

                    }
                }

            }

            /*
             * If feature layer got a searchContext then use the connector
             * metadataTranslator array to replace the key
             */
            c = feature.layer["_M"].searchContext;
            if (c && c.connector) {
                return M.Util._(typeof c.connector.metadataTranslator[key] === "string" ? c.connector.metadataTranslator[key] : key);
            }

            /*
             * In any case returns a i18n translated string
             */
            return M.Util._(key);
        },
        /*
         * Return a feature to given mimeType representation
         * 
         * @param {OpenLayers.Feature} feature 
         * @param {Object} format
         *                  {
         *                      mimeType:
         *                      encoding:
         *                      schema:
         *                  }
         */
        toGeo: function(feature, format) {

            format = format || {};

            switch (Map.Util.getGeoType(format["mimeType"])) {
                case 'GML':
                    return Map.Util.GML.featureToGML(feature, format["schema"]);
                    break;
                case 'WKT':
                    return this.toWKT(feature);
                    break;
                default:
                    return null;
            }
        },
        /*
         * Return a WKT representation of feature
         */
        toWKT: function(feature) {
            if (feature && feature.geometry) {
                return M.Map.Util.p2d(feature.geometry.clone()).toString();
            }
            return "";
        },
        /**
         * Zoom on features
         * 
         * @param {Array} features : array of OpenLayers Features
         * @param {boolean} partial : true to only zoom if features are not in the map view
         */
        zoomOn: function(features, partial) {

            if (!$.isArray(features)) {
                features = [features];
            }
            
            /*
             * If partial is not set, assume it is true 
             */
            if ($.type(partial) !== "boolean") {
                partial = true;
            }
            
            var i, l, bounds = new OpenLayers.Bounds();

            for (i = 0, l = features.length; i < l; i++) {
                bounds.extend(features[i].geometry.getBounds());
            }

            M.Map.zoomTo(bounds, partial);

        }

    };

    /**
     * 
     * Return an OpenLayers.Bounds in EPSG 4326 projection
     *
     * Returned values are strictly between [-180,180] for longitudes
     * and [-90,90] for latitudes
     * 
     * @param {Object/String} obj : a bbox structure
     *                  {
     *                      bounds: array (i.e. [minx, miny, maxx, maxy]) or string (i.e. "minx, miny, maxx, maxy")
     *                      crs: "EPSG:4326" or "EPSG:3857" (optional)
     *                      srs: "EPSG:4326" or "EPSG:3857" (optional)
     *                  } 
     *                  If input bbox is a String then it is supposed that the input string corresponds
     *                  to an EPSG:4326 string (i.e. lonMin,latMin,lonMax,latMax)
     */
    Map.Util.getGeoBounds = function(obj) {

        /*
         * Paranoid mode
         */
        if (!obj) {
            return null;
        }

        /*
         * If input bbox is a String then it is supposed that the input string corresponds
         * to an EPSG:4326 string (i.e. lonMin,latMin,lonMax,latMax)
         */
        if (typeof obj === "string") {
            obj = {
                bounds: obj,
                srs: 'EPSG:4326'
            };
        }

        if (!obj.bounds) {
            return null;
        }

        var bounds, coords, coords2 = [], srs = obj.srs, crs = obj.crs || "EPSG:4326";

        /*
         * Bounds is an array or a string ?
         */
        if (!$.isArray(obj.bounds)) {
            var coords = obj.bounds.split(',');
            coords[0] = parseFloat(coords[0]);
            coords[1] = parseFloat(coords[1]);
            coords[2] = parseFloat(coords[2]);
            coords[3] = parseFloat(coords[3]);
        }
        else {
            coords = obj.bounds;
        }

        /*
         * If srs is specified and srs === EPSG:4326 then
         * order coordinates is lon,lat
         * Otherwise it is lat,lon
         * 
         * Be sure to not be outside -180,-90,180,90
         */
        if (srs === "EPSG:4326") {
            coords2[0] = Math.max(-180, coords[0]);
            coords2[1] = Math.max(-90, coords[1]);
            coords2[2] = Math.min(180, coords[2]);
            coords2[3] = Math.min(90, coords[3]);
            coords = coords2;
        }
        else if (crs === "EPSG:4326") {
            coords2[0] = Math.max(-180, coords[1]);
            coords2[1] = Math.max(-90, coords[0]);
            coords2[2] = Math.min(180, coords[3]);
            coords2[3] = Math.min(90, coords[2]);
            coords = coords2;
        }

        bounds = new OpenLayers.Bounds(coords[0], coords[1], coords[2], coords[3]);

        /*
         * Returns geo bounds
         */
        return srs === "EPSG:4326" || crs === "EPSG:4326" ? bounds : M.Map.Util.p2d(bounds);

    };

    /**
     * 
     * Return an OpenLayers.Bounds in EPSG:3857 projection
     * Add an error at the pole to deal with infinite at the pole in Spherical Mercator
     * 
     * @param {Object/String} obj : a bbox structure
     *                  {
     *                      bounds: array (i.e. [minx, miny, maxx, maxy]) or string (i.e. "minx, miny, maxx, maxy")
     *                      crs: "EPSG:4326" or "EPSG:3857" (optional)
     *                      srs: "EPSG:4326" or "EPSG:3857" (optional)
     *                  } 
     *                  If input bbox is a String then it is supposed that the input string corresponds
     *                  to an EPSG:4326 string (i.e. lonMin,latMin,lonMax,latMax)
     */
    Map.Util.getProjectedBounds = function(obj) {

        /*
         * Paranoid mode
         */
        if (!obj) {
            return null;
        }

        /*
         * If input bbox is a String then it is supposed that the input string corresponds
         * to an EPSG:4326 string (i.e. lonMin,latMin,lonMax,latMax)
         */
        if (typeof obj === "string") {
            obj = {
                bounds: obj,
                srs: 'EPSG:4326'
            };
        }

        if (!obj.bounds) {
            return null;
        }

        var avoidBoundError = 0, bounds, coords, srs = obj.srs, crs = obj.crs;

        /*
         * Bounds is an array or a string ?
         */
        if (!$.isArray(obj.bounds)) {
            var coords = obj.bounds.split(',');
            coords[0] = parseFloat(coords[0]);
            coords[1] = parseFloat(coords[1]);
            coords[2] = parseFloat(coords[2]);
            coords[3] = parseFloat(coords[3]);
        }
        else {
            coords = obj.bounds;
        }

        /*
         * Avoid reprojection error at the pole
         */
        if (srs === "EPSG:4326") {

            if (coords[0] === -180 || coords[1] === -90 || coords[2] === 180 || coords[3] === 90) {
                avoidBoundError = 1;
            }

            bounds = Map.Util.d2p(new OpenLayers.Bounds(coords[0] + avoidBoundError, coords[1] + avoidBoundError, coords[2] - avoidBoundError, coords[3] - avoidBoundError));

        }
        else if (crs === "EPSG:4326") {

            if (coords[0] === -180 || coords[1] === -90 || coords[2] === 180 || coords[3] === 90) {
                avoidBoundError = 1;
            }

            bounds = Map.Util.d2p(new OpenLayers.Bounds(coords[1] + avoidBoundError, coords[0] + avoidBoundError, coords[3] - avoidBoundError, coords[2] - avoidBoundError));

        }
        else {
            bounds = new OpenLayers.Bounds(coords[0], coords[1], coords[2], coords[3]);
        }

        /*
         * Returns projected bounds
         */
        return bounds;

    };

    /**
     * Return geoType from mimeType
     * 
     * @param {String} mimeType
     */
    Map.Util.getGeoType = function(mimeType) {

        if (!mimeType) {
            return null;
        }

        var gmt = [];

        /*
         * List of geometrical mimeTypes
         */
        gmt["text/xml; subtype=gml/3.1.1"] = "GML";
        gmt["application/gml+xml"] = "GML";
        gmt["text/gml"] = "GML";
        gmt["application/geo+json"] = "JSON";
        gmt["application/geojson"] = "JSON";
        gmt["application/wkt"] = "WKT";
        gmt["application/x-ogc-wms"] = "WMS";

        return gmt[mimeType.toLowerCase()];

    };

    /**
     * Convert "input" to "format" using "precision"
     *  "input" can be one of the following :
     *    - OpenLayers.Bounds
     *
     *  "format" can be one of the following :
     *    - WKT
     *    - EXTENT
     *    
     *  @param {Object} obj
     */
    Map.Util.convert = function(obj) {
        if (obj && obj.input instanceof OpenLayers.Bounds) {
            var left, bottom, right, top,
                    precision = obj.precision || -1,
                    limit = obj.hasOwnProperty("limit") ? obj.limit : false;

            if (precision !== -1) {
                left = obj.input.left.toFixed(precision);
                right = obj.input.right.toFixed(precision);
                bottom = obj.input.bottom.toFixed(precision);
                top = obj.input.top.toFixed(precision);
            }
            else {
                left = obj.input.left;
                right = obj.input.right;
                bottom = obj.input.bottom;
                top = obj.input.top;
            }

            /*
             * If limit is set, assume that input obj coordinates
             * are in deegrees and that output coordinates cannot
             * be outside of the whole earth i.e. -180,-90,180,90
             */
            if (limit) {
                left = Math.max(left, -180);
                right = Math.min(right, 180);
                bottom = Math.max(bottom, -90);
                top = Math.min(top, 90);
            }

            if (obj.format === "WKT") {
                return "POLYGON((" + left + " " + bottom + "," + left + " " + top + "," + right + " " + top + "," + right + " " + bottom + "," + left + " " + bottom + "))";
            }
            else if (obj.format === "EXTENT") {
                return left + "," + bottom + "," + right + "," + top;
            }
        }
        return "";
    };

    /**
     * Return control identified by id
     * 
     * @param {String} id
     */
    Map.Util.getControlById = function(id) {
        return Map.map.getControlsBy("id", id)[0];
    };

    
    /**
     * Return an array of unclusterized features for Point cluster 
     * 
     * @param {OpenLayers.Layer} layer : layer containing clusterizes or unclusterized features
     * @param {Object} options : options for sorting or reprojecting in display projection
     *                 {
     *                      attribute: // name of the attribute to sort
     *                      order: // order of sorting - 'a' (default) for ascending and 'd' for descending
     *                      type: // attribute type - 'd' for date, 'n' for number, 't' for text (default)
     *                      toDisplayProjection: // true to reproject features to display projection
     *                 }
     * @param {boolean} noUncluster : true to not uncluster features array (default is false - i.e. features
     *                                are returned unclusterized)
     */
    Map.Util.getFeatures = function(layer, options, noUncluster) {

        var feature, i, j, l, m, features = [];

        /*
         * Paranoid mode
         */
        options = options || {};

        /*
         * Roll over layer features
         */
        if (layer && layer.features) {

            for (i = 0, l = layer.features.length; i < l; i++) {

                /*
                 * Get feature
                 */
                feature = layer.features[i];

                /*
                 * If feature is a cluster, roll over features
                 * within this cluster
                 */
                if (feature.cluster && !noUncluster) {

                    /*
                     * Roll over cluster features
                     */
                    for (j = 0, m = feature.cluster.length; j < m; j++) {

                        /*
                         * Set layer to feature
                         */
                        feature.cluster[j].layer = feature.cluster[j].layer || layer;

                        /*
                         * Add a new entry to features array
                         */
                        features.push(options.toDisplayProjection ? feature.cluster[j].clone() : feature.cluster[j]);

                    }
                }
                else {
                    features.push(options.toDisplayProjection ? feature.clone() : feature);
                }
            }
        }

        /*
         * Reproject ?
         */
        if (options.toDisplayProjection) {
            for (i = 0, l = features.length; i < l; i++) {
                if (features.components) {
                    for (j = 0, m = features.components.length; j < m; j++) {
                        M.Map.Util.p2d(features.components[j].geometry);
                    }
                }
                else {
                    M.Map.Util.p2d(features[i].geometry);
                }
            }
        }

        /*
         * Sorting ?
         */
        if (options.attribute) {

            features.sort(function(a, b) {

                var one, two;

                /*
                 * Paranoid mode
                 */
                if (!a.hasOwnProperty("attributes") || !b.hasOwnProperty("attributes")) {
                    return 0;
                }

                /*
                 * Ascending or descending
                 */
                if (options.order === 'd') {
                    one = b.attributes[options.attribute];
                    two = a.attributes[options.attribute];
                }
                else {
                    one = a.attributes[options.attribute];
                    two = b.attributes[options.attribute];
                }

                /*
                 * Number case
                 */
                if (options.type === 'n') {
                    one = parseFloat(one);
                    two = parseFloat(two);
                }
                /*
                 * Text case
                 */
                else if (options.type === 't') {
                    one = one.toLowerCase();
                    two = two.toLowerCase();
                }

                /*
                 * Order
                 */
                if (one < two) {
                    return -1;
                }
                if (one > two) {
                    return 1;
                }
                return 0;
            });
        }

        return features;

    };

    /**
     * Return feature base on its fid
     * 
     * @param {OpenLayer.Layer} layer
     * @param {string} identifier
     */
    Map.Util.getFeature = function(layer, identifier) {
        
        if (!layer || !identifier || !layer.features) {
            return null;
        }
        
        var features = Map.Util.getFeatures(layer);
        
        for (var i = 0, l = features.length; i < l; i++) {
            if (features[i].fid === identifier) {
                return features[i];
            }
        }

        return null;
    };
    
    /*
     * This function will return a formated LonLat
     * 
     * Parameters:
     *      lonlat - {OpenLayers.LonLat} the lonlat object to be formatted MUST BE IN Longitude/Latitude
     *      format - {String} specify the precision of the output can be one of:
     *           'dms' show degrees minutes and seconds (default)
     *           'hms' show hour minutes second
     */
    Map.Util.getFormattedLonLat = function(lonlat, format) {

        /*
         * Check format - By default returns degree, minutes, seconds
         */
        if (!format) {
            format = 'dms';
        }

        /*
         * Format 'hms' first display Right Ascension then Declinaison
         */
        if (format.indexOf('h') !== -1) {
            return Map.Util.getFormattedCoordinate(lonlat.lon, "lon", format) + "&nbsp;::&nbsp;" + Map.Util.getFormattedCoordinate(lonlat.lat, "lat", format);
        }
        /*
         * Classical 'dms' first display Latitude then Longitude
         */
        else {
            return Map.Util.getFormattedCoordinate(lonlat.lat, "lat", format) + "&nbsp;::&nbsp;" + Map.Util.getFormattedCoordinate(lonlat.lon, "lon", format);
        }

    };

    /**
     *
     * This function will return latitude or longitude value formatted
     * It is inspired by the OpenLayers.Util.getFormattedLonLat function
     *
     * @param {Float} coordinate - the coordinate value to be formatted
     * @param {String} axis - value of either 'lat' or 'lon' to indicate which axis is to
     *                        to be formatted (default = lat)
     * @param {String} format - specify the precision of the output can be one of:
     *                          'dms' show degrees minutes and seconds (default)
     *                          'hms' show hour minutes second
     *                          'dm' show only degrees and minutes
     *                          'd' show only degrees
     * 
     * Returns:
     *      {String} the coordinate value formatted as a string
     */
    Map.Util.getFormattedCoordinate = function(coordinate, axis, format) {

        var result, degreesOrHours, minutes, seconds, tmp, nsew,
                sign = "",
                degreesOrHoursUnit = "\u00B0";

        /*
         * Check format - By default returns degree, minutes, seconds
         */
        if (!format) {
            format = 'dms';
        }

        /*
         * Normalize coordinate for longitude values between [-180,180] degrees for longitude and [-90,90] for latitudes
         */
        if (axis === "lon") {
            coordinate = (coordinate + 540) % 360 - 180;
            nsew = coordinate < 0 ? "W" : "E";
        }
        else {
            coordinate = (coordinate + 270) % 180 - 90;
            nsew = coordinate < 0 ? "S" : "N";
        }

        /*
         * Computation for longitude coordinate depends on the display format
         */
        if (format.indexOf('h') !== -1) {

            /*
             * For longitude, coordinate is in hours not in degrees
             */
            if (axis === 'lon') {

                /*
                 * Transform degrees -> hours
                 * Warning : 0 degrees = 0 hours
                 */
                coordinate = 24 * ((360 - coordinate) % 360) / 360.0;
                degreesOrHoursUnit = "h";
            }

            /*
             * nsew has no sense in 'hms'
             */
            nsew = "";

            /*
             * For latitude (i.e. declinaison) the sign is stored 
             */
            sign = coordinate < 0 ? '-' : '+';

        }

        /*
         * Get degreesOrHour, minutes and seconds
         */
        coordinate = Math.abs(coordinate);
        /* Bitwise operator is faster than Map.floor */
        degreesOrHours = coordinate | 0;
        minutes = (coordinate - degreesOrHours) / (1 / 60);
        tmp = minutes;
        /* Bitwise operator is faster than Map.floor */
        minutes = minutes | 0;
        seconds = Math.round((tmp - minutes) / (1 / 60) * 10) / 10;
        if (seconds >= 60) {
            seconds -= 60;
            minutes += 1;
            if (minutes >= 60) {
                minutes -= 60;
                degreesOrHours += 1;
            }
        }

        /*
         * Format result
         */
        result = (axis === 'lat' ? sign : "") + (degreesOrHours < 10 ? "0" : "") + degreesOrHours + degreesOrHoursUnit;
        if (format.indexOf('m') >= 1) {
            result += (minutes < 10 ? "0" : "") + minutes + "'";
            if (format.indexOf('ms') >= 1) {
                result += (seconds < 10 ? "0" : "") + seconds + '"';
            }
        }

        return result + nsew;

    };

    /**
     * MID is an unique identifier used to identify
     * unambiguisly a specific layer
     * 
     * @param {String} MID
     */
    Map.Util.getLayerByMID = function(MID) {
        if (!MID || MID === "") {
            return null;
        }
        for (var j = 0, l = Map.map.layers.length; j < l; j++) {
            var layer = Map.map.layers[j];
            /*
             * We use '==' instead of '===' in case that input MID is a string
             * and not a numeric
             */
            if (layer["_M"] && (layer["_M"].MID === MID)) {
                return layer;
            }
        }
        return null;
    };


    /**
     * Return true if the layer is a raster layer
     * 
     *  @param {OpenLayers.Layer} layer
     */
    Map.Util.isRaster = function(layer) {

        if (!layer || !layer['_M']) {
            return false;
        }

        return layer['_M'].isRaster;
    };


    /**
     * Return true if the layer is empty
     * 
     * @param {OpenLayers.Layer} layer
     */
    Map.Util.layerIsEmpty = function(layer) {

        /*
         * No layer
         */
        if (!layer || !layer.features) {
            return true;
        }

        /*
         * Layer is defined but no features inside
         */
        if (layer.features.length === 0) {
            return true;
        }

        var isEmpty = true,
                length = layer.features.length,
                i;
        for (i = length; i--; ) {
            if (layer.features[i].geometry) {
                isEmpty = false;
                break;
            }
        }

        return isEmpty;
    };


    /*
     * Transform input object from display projection (epsg4326) to map projection 
     */
    Map.Util.d2p = function(obj) {
        return obj.transform(Map.pc, Map.map.getProjectionObject());
    };

    /*
     * Transform input object from map projection to display projection (epsg4326)
     */
    Map.Util.p2d = function(obj) {
        return obj.transform(Map.map.getProjectionObject(), Map.pc);
    };

    /**
     * Set "layer" on top of other layers
     * (see LayerIndex in OpenLayers)
     * 
     * @param {OpenLayers.Layer} layer
     */
    Map.Util.setLayerOnTop = function(layer) {
        if (layer) {
            Map.map.setLayerIndex(layer, Map.map.layers.length);
        }
    };

    /*
     * Set the layer visibility
     */
    Map.Util.setVisibility = function(layer, v) {

        /*
         * Set the layer visibility to v
         */
        layer.setVisibility(v);

        /*
         * Trigger the visibilitychanged trigger
         */
        Map.events.trigger("visibilitychanged", layer);

    };

    /**
     * Switch the layer visibility
     * 
     * @param {String} MID
     */
    Map.Util.switchVisibility = function(MID) {
        var l = Map.Util.getLayerByMID(MID);
        if (l) {
            Map.Util.setVisibility(l, !l.getVisibility());
        }
    };

    /**
     * Reindex layer to ensure that :
     *  - vectors layers are always on top of raster layers
     *  - Point and/or Line vector layers are always
     *    on top Polygonal vector layers
     *    
     * @param {OpenLayers.Layer} layer
     */
    Map.Util.updateIndex = function(layer) {

        var i, tmpLayer,
                index = Map.map.getLayerIndex(layer), //Set index to the layer index
                l = Map.map.layers.length;

        /*
         * Do not process raster layers
         */
        if (!layer || !layer.features) {
            return false;
        }

        /*
         * Roll over layers list from the higher element
         * and retrieve it
         */
        for (i = l; i--; ) {

            tmpLayer = Map.map.layers[i];

            /*
             * Do not process input layer
             */
            if (layer.id === tmpLayer.id) {
                continue;
            }

            /*
             * layer is a vector and have at least one feature with a non null geometry
             */
            if (layer.features[0] && layer.features[0].geometry) {

                /*
                 * We already reached a raster layer => break
                 */
                if (!tmpLayer.features) {
                    index = Map.map.getLayerIndex(tmpLayer);
                    break;
                }

                /*
                 * layer is a Point => directly break
                 */
                if (layer.features[0].geometry.CLASS_NAME === "OpenLayers.Geometry.Point") {
                    break;
                }

                if (tmpLayer.features[0] && tmpLayer.features[0].geometry) {

                    /*
                     * We reached a Polygon => break
                     */
                    if (tmpLayer.features[0].geometry.CLASS_NAME === "OpenLayers.Geometry.Polygon") {
                        index = Map.map.getLayerIndex(tmpLayer);
                        break;
                    }

                    /*
                     * We reached a Line => break
                     */
                    if (layer.features[0].geometry.CLASS_NAME === "OpenLayers.Geometry.Line" && tmpLayer.features[0].geometry.CLASS_NAME === "OpenLayers.Geometry.Line") {
                        index = Map.map.getLayerIndex(tmpLayer);
                        break;
                    }
                }

            }
        }

        /*
         * Change layer index
         */
        if (Map.map.getLayerIndex(layer) !== index) {

            /*
             * Change layer index
             */
            Map.map.setLayerIndex(layer, index + 1);

        }

        return true;

    };

    /*
     * Center the map on the layer extent.
     * 
     * This centering is only done if the layer, or part of the added layer,
     * is not already visible in the map view
     * 
     * If force is set to true, the layer in centered even if part of the entire layer
     * is visible
     *
     * Note : if the layer has already been loaded then the _M.initialized attribute
     * is set to true and the map is not centered any more on this layer even if
     * its content changes
     * 
     * @param {OpenLayer.Layer} layer
     * @param {boolean} force
     */
    Map.Util.zoomOn = function(layer, force) {

        var extent;

        /*
         * Paranoid mode
         */
        if (!layer || !layer["_M"]) {
            return false;
        }

        /*
         * Vector layers have a getDataExtent() function that returns bounds
         * Raster layer such as WMS or Image should have a ["_M"].bounds property
         * set during initialization
         */
        extent = layer.getDataExtent() || layer["_M"].bounds;
        
        /*
         * Force zoom
         */
        if (force && extent) {
            M.Map.zoomTo(extent);
            return true;
        }

        /*
         * Only zoom on layer that are initialized
         */
        if (layer["_M"].initialized) {

            if (extent) {

                /*
                 * Centering is done only if the entire layer or part of the layer
                 * is not visible within the map view
                 */
                if (!M.Map.map.getExtent().intersectsBounds(extent, true)) {
                    M.Map.zoomTo(extent);
                    return true;
                }
                
            }
        }

        return false;

    };

    /**
     * Return a GeoJSON geometry string from a GML posList
     * 
     * @param {String} posList : a GML posList (or a GML pos) i.e. a string
     *                           containing x y coordinqtes separated by white spaces
     *                           (i.e. x1 y1 x2 y2 x3 y3 ..., x* y* being double)
     */
    Map.Util.posListToGeoJsonGeometry = function(posList) {

        var pairs = [], i, l, coordinates, latlon = false;

        /*
         * Paranoid mode
         */
        if (posList) {

            coordinates = posList.split(" ");

            /*
             * Parse each coordinates
             */
            for (i = 0, l = coordinates.length; i < l; i = i + 2) {

                /*
                 * Case 1 : coordinates order is latitude then longitude
                 */
                if (latlon) {
                    pairs.push('[' + coordinates[i + 1] + ',' + coordinates[i] + ']');
                }
                /*
                 * Case 2 : coordinates order is longitude then latitude
                 */
                else {
                    pairs.push('[' + coordinates[i] + ',' + coordinates[i + 1] + ']');
                }
            }

        }

        return pairs.join(',');

    };

    /**
     * Return a GeoJSON geometry string from an ElasticSearch result
     * 
     *  Elastic Search result example :
     *  
     *      {
     *          "took" : 138,
     *          "timed_out" : false,
     *          "_shards" : {
     *              "total" : 5,
     *              "successful" : 5,
     *              "failed" : 0
     *          },
     *          "hits" : {
     *              "total" : 19882872,
     *              "max_score" : 1.0,
     *              "hits" : [
     *                  {
     *                      "_index" : "osm",
     *                      "_type" : "way",
     *                      "_id" : "42165222",
     *                      "_score" : 1.0,
     *                      "_source" :{
     *                          "centroid":[1.9309686748050385,44.192819178853966],
     *                          "lengthKm":6.719306622689737,
     *                          "areaKm2":1.1417793121178532,
     *                          "shape":{
     *                              "type":"polygon",
     *                              "coordinates":[[[1.9304132,44.1974077],[1.9305396000000001,44.195908800000005],[1.931243,44.1946627],[1.9327492000000002,44.1944188],[1.9347191000000001,44.1940934],[1.9348400000000001,44.193344100000004],[1.9360017,44.1927651],[1.9364714,44.191850800000005],[1.9368212,44.191436200000005],[1.9384401000000002,44.1916917],[1.9397132000000001,44.191696300000004],[1.9416863000000002,44.190787400000005],[1.9418085,44.189955000000005],[1.9407707,44.1893691],[1.9391409000000002,44.190778200000004],[1.9387963000000001,44.190361100000004],[1.9391491,44.189612700000005],[1.940776,44.1886194],[1.9395094000000002,44.1876988],[1.9377793,44.186942800000004],[1.9352315000000002,44.1871841],[1.9358037000000001,44.1881022],[1.9341724,44.189594400000004],[1.9324328000000002,44.1901713],[1.929998,44.1907444],[1.9277904000000001,44.1919849],[1.9257061000000002,44.192144500000005],[1.9230418,44.1924671],[1.9210665,44.1936252],[1.9203623,44.194954300000006],[1.9216322000000001,44.195293],[1.9235919000000001,44.1963828],[1.9252103,44.196721600000004],[1.9264845000000002,44.1964769],[1.9272893000000002,44.197312800000006],[1.9275084,44.198978200000006],[1.928201,44.1992315],[1.9297109000000001,44.198487400000005],[1.9304132,44.1974077]]]
     *                          },
     *                          "tags":{"wood":"deciduous","source":"Union européenne - SOeS, CORINE Land Cover, 2006.","CLC:code":"311","CLC:id":"FR-211193","CLC:year":"2006","landuse":"forest"}
     *                      }
     *                  }
     *                  ...etc...
     *              ]
     *          }
     *      }
     * 
     * 
     * 
     * @param {String} elasticResult : a geocoded elasticSearch result
     * @return {Object} : a GeoJSON object
     * 
     */
    Map.Util.elasticResultToGeoJSON = function(elasticResult) {

        var i, id, type, source, l, hit, properties = {}, features = [], mapping = {point: "Point", linestring: "LineString", polygon: "Polygon"};

        for (i = 0, l = elasticResult.hits.hits.length; i < l; i++) {
            hit = elasticResult.hits.hits[i];
            id = hit._id;
            type = hit._type;
            source = hit._source;
            
            /*
             * ElasticSearch shape types are point, linestring and polygon
             * GeoJSON equivalent are Point, LineString and Polygon
             */
            source.shape.type = mapping[source.shape.type] || source.shape.type;
            properties = {
                "id": id,
                "type": type
            };
            $.extend(properties, source.tags);
            features.push({
                "type": "Feature",
                "geometry": source.shape,
                "properties": properties
            });
        }

        return {
            "type": "FeatureCollection",
            "features": features
        };
    };

})(window.M, window.M.Map);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Define GML Map util functions
 */
(function(M, Map) {

    Map.Util = Map.Util || {};

    /*
     * Initialize Map.Util.GML
     */
    Map.Util.GML = {
        namespaces: 'xmlns:gml="http://www.opengis.net/gml"',
        /*
         * Return a GML representation of feature
         * 
         * Structure :
         *
         *  feature.geometry // geometry object
         *  feature.geometry.CLASS_NAME // geometry type
         *      - OpenLayers.Geometry.Point
         *      - OpenLayers.Geometry.LineString
         *      - OpenLayers.Geometry.Polygon
         *  feature.geometry.components[] // array of points
         *      feature.geometry.components[].x
         *      feature.geometry.components[].y
         *
         * @param {OpenLayers.Feature} feature
         * @param {String} schema : GML schema
         *                 if schema = "http://schemas.opengis.net/gml/3.1.1/base/feature.xsd"
         *                 then a FeatureCollection is returned
         *                 otherwise a GeometryProperty is returned
         * 
         */
        featureToGML: function(feature, schema) {

            var i, l, gml = '';

            /*
             * Roll over each component. A component contain
             * a point in (x,y) map coordinates.
             * Each point is transformed in lat/lon for GML
             */
            if (feature && feature.geometry) {

                var gt = feature.geometry.CLASS_NAME;

                /*
                 * Initialize kml string based on the feature
                 * geometry class
                 */
                if (gt === "OpenLayers.Geometry.Point") {
                    gml = this.geometryPointToGML(feature.geometry);
                }
                else if (gt === "OpenLayers.Geometry.MultiPoint") {
                    // TODO
                }
                else if (gt === "OpenLayers.Geometry.LineString") {
                    gml = this.geometryLineStringToGML(feature.geometry);
                }
                else if (gt === "OpenLayers.Geometry.MultiLineString") {
                    if (feature.geometry.components) {
                        for (i = 0, l = feature.geometry.components.length; i < l; i++) {
                            gml += this.geometryLineStringToGML(feature.geometry.components[i]);
                        }
                    }
                }
                else if (gt === "OpenLayers.Geometry.Polygon") {
                    return this.geometryPolygonToGML(feature.geometry);
                }
                else if (gt === "OpenLayers.Geometry.MultiPolygon") {
                    if (feature.geometry.components) {
                        for (i = 0, l = feature.geometry.components.length; i < l; i++) {
                            gml += this.geometryPolygonToGML(feature.geometry.components[i]);
                        }
                    }
                }
            }

            /*
             * Return a FeatureCollection
             */
            if (schema === "http://schemas.opengis.net/gml/3.1.1/base/feature.xsd") {
                return '<gml:FeatureCollection ' + Map.Util.GML.namespaces + ' ><gml:featureMember><gml:GeometryPropertyType>' + gml + '</gml:GeometryPropertyType></gml:featureMember></gml:FeatureCollection>';
            }

            /*
             * Return a GeometryType
             */
            return gml;

        },
        /*
         * @param {OpenLayers.Geometry.Point} geometry
         */
        geometryPointToGML: function(geometry) {
            var point;
            point = Map.Util.p2d(new OpenLayers.LonLat(geometry.x, geometry.y));
            return '<gml:Point ' + Map.Util.GML.namespaces + ' srsName="' + Map.map.displayProjection.projCode + '"><gml:pos>' + point.lon + ' ' + point.lat + '</gml:pos></gml:Point>';
        },
        /*
         * @param {OpenLayers.Geometry.LineString} geometry
         */
        geometryLineStringToGML: function(geometry) {

            var point, gml = '';

            /*
             * LineString geometry get a "components" array of points
             */
            if (geometry.components) {
                for (var i = 0, l = geometry.components.length; i < l; i++) {
                    point = geometry.components[i];
                    point = M.Map.Util.p2d(new OpenLayers.LonLat(point.x, point.y));
                    gml += point.lon + ' ' + point.lat + ' ';
                }
            }

            /*
             * Remove trailing white space
             */
            return '<gml:LineString ' + Map.Util.GML.namespaces + ' srsName="' + Map.map.displayProjection.projCode + '"><gml:posList>' + gml.substring(0, gml.length - 1) + '</gml:posList></gml:LineString>';
        },
        /*
         * @param {OpenLayers.Geometry.Polygon} geometry
         */
        geometryPolygonToGML: function(geometry) {

            var point, component, gml = '';

            /*
             * Polygon geometry get a "components" array of "components"
             */
            if (geometry.components) {
                for (var i = 0, l = geometry.components.length; i < l; i++) {
                    component = geometry.components[i];
                    for (var j = 0, k = component.components.length; j < k; j++) {
                        point = component.components[j];
                        point = M.Map.Util.p2d(new OpenLayers.LonLat(point.x, point.y));
                        gml += point.lon + ' ' + point.lat + ' ';
                    }
                }
            }

            /*
             * Remove trailing white space
             */
            return '<gml:Polygon ' + Map.Util.GML.namespaces + ' srsName="' + Map.map.displayProjection.projCode + '"><gml:exterior><gml:LinearRing><gml:posList>' + gml.substring(0, gml.length - 1) + '</gml:posList></gml:LinearRing></gml:exterior></gml:Polygon>';

        },
        /**
         * 
         * Take a GML object in entry and return a GeoJSON FeatureCollection string
         * 
         * GeoJSON example (from http://www.geojson.org/geojson-spec.html)
         * 
         *      { "type": "FeatureCollection",
         *          "features": [
         *              { 
         *                  "type": "Feature",
         *                  "geometry": {"type": "Point", "coordinates": [102.0, 0.5]},
         *                  "properties": {"prop0": "value0"}
         *              },
         *              {
         *                  "type": "Feature",
         *                  "geometry": {
         *                      "type": "LineString",
         *                      "coordinates": [
         *                          [102.0, 0.0], 
         *                          [103.0, 1.0], 
         *                          [104.0, 0.0], 
         *                          [105.0, 1.0]
         *                       ]
         *                  },
         *                  "properties": {"prop0": "value0", "prop1": 0.0}
         *              },
         *              { 
         *                  "type": "Feature",
         *                  "geometry": {
         *                      "type": "Polygon",
         *                      "coordinates": [
         *                           [
         *                              [100.0, 0.0],
         *                              [101.0, 0.0],
         *                              [101.0, 1.0],
         *                              [100.0, 1.0],
         *                              [100.0, 0.0]
         *                           ]
         *                      ]
         *                  },
         *                  "properties": {"prop0": "value0","prop1": {"this": "that"} }
         *              }
         *          ]
         *      }
         *      
         *      
         * @param {jQueryObject} gml : gml in javascript XML object
         * @param {Object} properties : properties to set
         * 
         */
        toGeoJSON: function(gml, properties) {

            var geoJSON = {};

            /*
             * Input gml description must be a jQuery object to be parsed
             */
            if (gml instanceof jQuery) {

                /*
                 * Detect GML type
                 */
                switch (M.Util.stripNS(gml[0].nodeName)) {
                    case 'Point':
                        geoJSON = this.pointToGeoJSON(gml, properties);
                        break;
                    case 'LineString':
                        geoJSON = this.lineStringToGeoJSON(gml, properties);
                        break;
                    case 'Polygon':
                        geoJSON = this.polygonToGeoJSON(gml, properties);
                        break;
                    case 'MultiPolygon':
                        geoJSON = this.multiPolygonToGeoJSON(gml, properties);
                        break;
                }

            }

            return geoJSON;
        },
        /*
         * Return a GeoJSON geometry from a GML Point
         * 
         *  GML Point structure 
         *  
         *          <gml:Point srsName="urn:ogc:def:crs:epsg:7.9:4326">
         *              <gml:pos>77.0223274997802 52.58523464466345</gml:pos>
         *          </gml:Point>
         *  
         * @param {jQuery Object} gml : gml in javascript XML object
         * @param {Object} properties : properties to set
         * 
         */
        pointToGeoJSON: function(gml, properties) {

            properties = properties || {identifier: M.Util.getId()};

            /*
             * First children is gml:pos
             */
            return JSON.parse(M.Util.parseTemplate(this.geoJSONTemplate, {
                geometry: '{"type":"Point","coordinates":' + Map.Util.posListToGeoJsonGeometry(gml.children().text()) + '}',
                properties: JSON.stringify(properties)
            }));

        },
        /*
         * Return a GeoJSON geometry from a GML Polygon
         * 
         *  GML LineString structure 
         *  
         *          <gml:LineString srsName="urn:ogc:def:crs:epsg:7.9:4326">
         *              <gml:posList>77.0223274997802 52.58523464466345 86.63758854839588 41.09044727093532 86.34797437056751 40.97981843953097 77.0223274997802 52.58523464466345</gml:posList>
         *          </gml:LineString>
         *  
         * @param {jQuery Object} gml : gml in javascript XML object
         * @param {Object} properties : properties to set
         * 
         */
        lineStringToGeoJSON: function(gml, properties) {

            properties = properties || {identifier: M.Util.getId()};

            /*
             * First children is gml:posList
             */
            return JSON.parse(M.Util.parseTemplate(this.geoJSONTemplate, {
                geometry: '{"type":"LineString","coordinates":[' + Map.Util.posListToGeoJsonGeometry(gml.children().text()) + ']}',
                properties: JSON.stringify(properties)
            }));

        },
        /*
         * Return a GeoJSON geometry from a GML Polygon
         * 
         *  GML Polygon structure 
         *  
         *          <gml:Polygon srsName="urn:ogc:def:crs:epsg:7.9:4326">
         *               <gml:exterior>
         *                   <gml:LinearRing srsName="urn:ogc:def:crs:epsg:7.9:4326">
         *                      <gml:posList>77.0223274997802 52.58523464466345 86.63758854839588 41.09044727093532 86.34797437056751 40.97981843953097 77.0223274997802 52.58523464466345</gml:posList>
         *                   </gml:LinearRing>
         *               </gml:exterior>
         *           </gml:Polygon>
         *  
         * @param {jQuery Object} gml : gml in javascript XML object
         * @param {Object} properties : properties to set
         * 
         */
        polygonToGeoJSON: function(gml, properties) {

            var geometries = [];

            properties = properties || {identifier: M.Util.getId()};

            /*
             * Roll over exterior and interiors
             */
            gml.children().each(function() {

                /*
                 * Parse interior and interiors
                 */
                $(this).children().each(function() {
                    geometries.push('[' + Map.Util.posListToGeoJsonGeometry($(this).children().text()) + ']');
                });

            });

            return JSON.parse(M.Util.parseTemplate(this.geoJSONTemplate, {
                geometry: '{"type":"Polygon","coordinates":[' + geometries.join(',') + ']}',
                properties: JSON.stringify(properties)
            }));

        },
        /*
         * Return a GeoJSON geometry from a GML MultiPolygon
         * 
         *  GML MultiPolygon structure 
         *  
         *          <gml:MultiPolygon srsDimension="2" xmlns:sch="http://www.ascc.net/xml/schematron" xmlns:gml="http://www.opengis.net/gml" xmlns:xlink="http://www.w3.org/1999/xlink">
         *              <gml:polygonMember>
         *                  <gml:Polygon srsName="urn:ogc:def:crs:epsg:7.9:4326">
         *                      <gml:exterior>
         *                          <gml:LinearRing srsName="urn:ogc:def:crs:epsg:7.9:4326">
         *                              <gml:posList>77.0223274997802 52.58523464466345 86.63758854839588 41.09044727093532 86.34797437056751 40.97981843953097 77.0223274997802 52.58523464466345</gml:posList>
         *                          </gml:LinearRing>
         *                      </gml:exterior>
         *                  </gml:Polygon>
         *              </gml:polygonMember>
         *              ...
         *          </gml:MultiPolygon>
         *  
         * @param {jQuery Object} gml : gml in javascript XML object
         * @param {Object} properties : properties to set
         * 
         */
        multiPolygonToGeoJSON: function(gml, properties) {

            var members = [],
                polygons = [];

            properties = properties || {identifier: M.Util.getId()};

            /*
             * Roll over MultiPolygon/polygonMember
             */
            gml.children().each(function() {

                /*
                 * Parse Polygon
                 */
                $(this).children().each(function() {
                    
                    polygons = [];
                    
                    /*
                     * Parse interior and interiors
                     */
                    $(this).children().each(function() {
                        polygons.push('[' + Map.Util.posListToGeoJsonGeometry($(this).children().text()) + ']');
                    });
                    
                    members.push('[' + polygons.join(',') + ']');
                    
                });

            });
        
            return JSON.parse(M.Util.parseTemplate(this.geoJSONTemplate, {
                geometry: '{"type":"MultiPolygon","coordinates":[' + members.join(',') + ']}',
                properties: JSON.stringify(properties)
            }));

        },
        geoJSONTemplate: '{"type":"FeatureCollection","features":[{"type":"Feature","geometry":$geometry$,"properties":$properties$}]}'

    };

})(window.M, window.M.Map);

/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Define Map util functions
 */
(function(M, Map) {
    
    Map.Util = Map.Util || {};
    
    /*
     * Initialize Map.Util
     */
    Map.Util.KML = {
        
        /*
         * Return a KML string from an OpenLayers layer
         *
         * @param {OpenLayers.Layer} layer : OpenLayers layer
         * @param {Object} options : options for color/opacity
         *
         * @return {String} a kml representation of the input layer
         */
        layerToKML: function(layer, options) {

            /*
             * Paranoid mode
             */
            if (!layer) {
                return false;
            }

            /*
             * KML layers are already in kml !
             */
            if (layer["_M"] && layer["_M"].kml) {
                return layer["_M"].kml;
            }

            options = options || {};

            /*
             * Open kmlString
             */
            var kmlString = '',
                i,
                l;

            /*
             * Case 1 : Image layers
             */
            if (layer.CLASS_NAME === "OpenLayers.Layer.Image") {
                kmlString += this.imageToKML(layer);
            }

            /*
             * Case 2 : WMS layers
             */
            else if (layer.CLASS_NAME === "OpenLayers.Layer.WMS" && options.synchronizeWMS) {
                kmlString += this.wmsToKML(layer);
            }
            /*
             * Case 3 : Vector layers
             */
            else if (layer.CLASS_NAME === "OpenLayers.Layer.Vector") {

                var feature,
                    features = [];

                /*
                 * Roll over layer features
                 */
                for (i = 0, l = layer.features.length; i < l; i++) {

                    /*
                     * Get feature
                     */
                    feature = layer.features[i];

                    /*
                     * If feature is a cluster, roll over features
                     * within this cluster
                     */
                    if (feature.cluster) {

                        /*
                         * Roll over cluster features
                         */
                        for (var j = 0, m = feature.cluster.length; j < m; j++) {

                            /*
                             * Add feature to features array
                             */
                            if (feature.cluster[j].geometry) {
                                features.push(feature.cluster[j]);
                            }

                        }
                    }
                    else {

                        /*
                         * Add feature to features array
                         */
                        if (feature.geometry) {
                            features.push(feature);
                        }

                    }
                }

                /*
                 * Generate KML only for non empty layer
                 */
                if (features.length > 0) {

                    /*
                     * Set style if 'options.styleUrl' is specified
                     */
                    if (options.color) {

                        kmlString += ''
                        + '<Style id="normalState">'
                        + '<IconStyle>'
                        + '<scale>1.1</scale>'
                        + '<Icon><href>'+M.Util.getImgUrl('ylw-pushpin.png')+'</href></Icon>'
                        + '<hotSpot x="20" y="2" xunits="pixels" yunits="pixels"/>'
                        + '</IconStyle>'
                        + '<PolyStyle>'
                        + '<color>' + this.color2KML(options.color, options.opacity) + '</color>'
                        + '</PolyStyle>'
                        + '</Style>';
                    }

                    /*
                     * Get KML representation for each feature
                     */
                    for (i = 0, l = features.length; i < l; i++) {
                        kmlString += this.featureToKML(features[i], options);
                    }
                }
            }

            /*
             * Parse kmlString
             */
            if (kmlString !== ''){
                kmlString = '<?xml version="1.0" encoding="UTF-8"?>'
                + '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:kml="http://www.opengis.net/kml/2.2">'
                + '<Document>'
                + '<name>'+this.encode(M.Util._(layer.name))+'</name>'
                + '<description></description>'
                + kmlString
                + '</Document></kml>';
            }

            return kmlString;

        },

        /**
         * Return a KML string from feature
         * Structure :
         *
         *  feature.geometry // geometry object
         *  feature.geometry.CLASS_NAME // geometry type
         *      - OpenLayers.Geometry.Point
         *      - OpenLayers.Geometry.LineString
         *      - OpenLayers.Geometry.Polygon
         *  feature.geometry.components[] // array of points
         *      feature.geometry.components[].x
         *      feature.geometry.components[].y
         *
         *  Note : if feature has an "ele" attribute, this attribute
         *  is assumed to be an elevation value in meters. This value is added
         *  to the geometry (see GPX format)
         *
         * @param {OpenLayers.Feature} feature : a feature in map coordinates
         * @param {Object} options : options for color/opacity
         * 
         * @return {String} a KML Placemark
         */
        featureToKML: function(feature, options) {

            var attribute,
                name,
                point,
                value,
                kml = '',
                description = '',
                i,
                l;

            options = options || {};

            /*
             * Roll over each component. A component contain
             * a point in (x,y) map coordinates.
             * Each point is transformed in lat/lon for KML
             */
            if (feature && feature.geometry) {

                /*
                 * Initialize altitude to null
                 */
                var elevation = null,
                    gt = feature.geometry.CLASS_NAME;

                /*
                 * Get name and description
                 */
                for(attribute in feature.attributes) {
                    
                    /*
                     * Name is easy to find :)
                     */
                    if (attribute === "name") {
                        name = feature.attributes[attribute];
                    }
                    /*
                     * Description too :)
                     */
                    else if (attribute === "description") {
                        description += feature.attributes[attribute] + '<br/>';
                    }
                    /*
                     * Add each attribute to the description
                     */
                    else {

                        value = feature.attributes[attribute];

                        /*
                         * The tricky part :
                         * If value begins by http, then it's a link
                         */
                        if (typeof value === "string" && M.Util.isUrl(value)) {
                            if (attribute === "thumbnail") {
                                description += '<img src="'+value+'" class="center" width="250"/><br/>';
                            }
                            else {
                                description += '<a target="_blank" href="'+value+'">'+attribute+'</a><br/>';
                            }
                        }
                        else {
                            description += attribute + ' : ' + value + '<br/>';
                        }
                    }

                    /*
                     * Get elevation in meters (but be sure that this is at least a number!)
                     */
                    if (attribute === "ele") {
                        elevation = feature.attributes[attribute];
                        if (!$.isNumeric(elevation)) {
                            elevation = null;
                        }
                    }
                }

                /*
                 * If name is not defined in attributes,
                 * set it to the feature id
                 */
                if (name === undefined || name === ''){
                    name = feature.id;
                }

                /*
                 * Set attribute string
                 *
                 * Note : description is encapsulated into a <table></table> tag to
                 * force the balloon width
                 *
                 */
                attribute = '<name><![CDATA['+name+']]></name><description><![CDATA[<table border="0" cellpadding="0" cellspacing="0" width="300" align="left"><tr><td>'+description+'</td></tr></table>]]></description>';
                
                /*
                 * Initialize kml string based on the feature
                 * geometry class
                 */
                if (gt === "OpenLayers.Geometry.Point") {
                    point = M.Map.Util.p2d(new OpenLayers.LonLat(feature.geometry.x,feature.geometry.y));
                    kml = '<Point><coordinates>'+point.lon + ',' + point.lat + (elevation ? ',' + elevation : '') + '</coordinates></Point>';
                }
                else if (gt === "OpenLayers.Geometry.MultiPoint") {

                    /*
                     * MultiPoint geometry get a "components" array of points
                     */
                    if (feature.geometry.components) {
                        for (i = 0, l = feature.geometry.components.length; i < l; i++) {
                            point = feature.geometry.components[i];
                            point = M.Map.Util.p2d(new OpenLayers.LonLat(point.x,point.y));
                            kml += '<Point><coordinates>'+point.lon + ',' + point.lat + (elevation ? ',' + elevation : '') + '</coordinates></Point>';
                        }
                    }

                }
                else if (gt === "OpenLayers.Geometry.LineString") {

                    /*
                     * LineString geometry get a "components" array of points
                     */
                    if (feature.geometry.components) {
                        for (i = 0, l = feature.geometry.components.length; i < l; i++) {
                            point = feature.geometry.components[i];
                            point = M.Map.Util.p2d(new OpenLayers.LonLat(point.x,point.y));
                            kml += point.lon + ',' + point.lat + (elevation ? ',' + elevation : '') + ' ';
                        }
                    }

                    /*
                     * Remove trailing white space
                     */
                    kml = '<LineString><coordinates>'+kml.substring(0, kml.length-1)+'</coordinates></LineString>';

                }
                else if (gt === "OpenLayers.Geometry.Polygon") {

                    var j, k, component;

                    /*
                     * Polygon geometry get a "components" array of "components"
                     */
                    if (feature.geometry.components) {
                        for (i = 0, l = feature.geometry.components.length; i < l; i++) {
                            component = feature.geometry.components[i];
                            for (j = 0, k = component.components.length; j < k; j++){
                                point = component.components[j];
                                point = M.Map.Util.p2d(new OpenLayers.LonLat(point.x,point.y));
                                kml += point.lon + ',' + point.lat + (elevation ? ',' + elevation : '') + ' ';
                            }
                        }
                    }

                    /*
                     * Remove trailing white space
                     */
                    kml = '<Polygon><outerBoundaryIs><LinearRing><coordinates>'+kml.substring(0, kml.length-1)+'</coordinates></LinearRing></outerBoundaryIs></Polygon>';
                }

            }

            /*
             * Return kml Placemark
             * Note that the last character (space) of the kml string is removed
             *
             * ?ote : if "options.styleUrl" is specified, no style is computed
             */
            if (kml !== '') {
                return '<Placemark>'
                + attribute
                + (options.color ? '<styleUrl>#normalState</styleUrl>' : this.featureToKMLStyle(feature))
                + kml
                + '</Placemark>';
            }

            return kml;
        },

        /**
         * Return a KML <Style>...</Style> string from feature
         *
         * @param {OpenLayers.Feature} feature : a feature in map coordinates
         * 
         * @return {String} a KML <Style>...</Style> string
         */
        featureToKMLStyle: function(feature) {

            /*
             * Empty style is the default
             */
            var kmlStyle = '',
                style,
                defaultStyle,
                gt;

            /*
             * Paranoid mode
             */
            if (feature && feature.layer) {

                /*
                 * feature.geometry.CLASS_NAME
                 */
                gt = feature.geometry.CLASS_NAME;

                /*
                 * Get the default style object from styleMap
                 */
                style = feature.layer.styleMap.styles["default"];

                /*
                 * The defaultStyle descriptor should be defined directly
                 * under feature.style property. If not, there is always
                 * a valid defaultStyle descriptor under feature.layer.styleMap.styles["default"]
                 */
                defaultStyle = feature.style || style.defaultStyle;

                /*
                 * Style for OpenLayers.Geometry.Point
                 */
                if (gt === "OpenLayers.Geometry.Point") {

                    /*
                     * Case one => external graphic
                     */
                    if (defaultStyle.externalGraphic) {
                        kmlStyle = this.resolveStyleAttribute(feature, style, defaultStyle.externalGraphic);
                    }
                    /*
                     * Default google pushpin
                     */
                    else {
                        kmlStyle = M.Util.getImgUrl("ylw-pushpin.png");
                    }

                    /*
                     * Style for OpenLayers.Geometry.Point
                     */
                    if (kmlStyle.substr(0,5) !== 'http:' && kmlStyle.substr(0,1) !== '/') {
                        kmlStyle = M.Config.general.rootUrl + '/' + kmlStyle;
                    }
                    kmlStyle = '<Style><IconStyle><Icon><href>'+this.encode(M.Util.getAbsoluteUrl(kmlStyle))+'</href></Icon></IconStyle></Style>';
                }

                /*
                 * Style for OpenLayers.Geometry.LineString
                 */
                else if (gt === "OpenLayers.Geometry.LineString") {

                    /*
                     * Color should be express as a #RGB hexadecimal value
                     */
                    kmlStyle = '<LineStyle><color>'+this.color2KML(this.resolveStyleAttribute(feature, style, defaultStyle.strokeColor), 1)+'</color><width>'+(defaultStyle.strokeWidth || 1)+'</width></LineStyle>';

                }

                /*
                 * Style for OpenLayers.Geometry.Polygon
                 */
                else if (gt === "OpenLayers.Geometry.Polygon") {

                    /*
                     * Color should be express as a #RGB hexadecimal value
                     */
                    kmlStyle = '<Style><PolyStyle><color>'+this.color2KML(this.resolveStyleAttribute(feature, style, defaultStyle.fillColor),  defaultStyle.fillOpacity)+'</color></PolyStyle></Style>';

                }
            }

            return kmlStyle;
        },

        /**
         * Return the resolved style value
         */
        resolveStyleAttribute: function(feature, style, value) {

            var str = value,
                pointer;

            /*
             * "value" can be an url to an imageor an OpenLayers pointer.
             * In the second case, the pointer ${...} can be
             * an attribute of the feature or a function in the
             * context object
             */
            if (value.indexOf("${") !== -1) {

                /*
                 * Get pointer
                 */
                pointer = value.substring(2, value.length-1);

                /*
                 * Pointer is a function
                 */
                if (style.context && $.isFunction(style.context[pointer])) {
                    str = style.context[pointer](feature);
                }
                /*
                 * Pointer is an attribute
                 */
                else {
                    str = feature.attributes[pointer];
                }
            }

            return str;

        },

        /**
         * Take an hexadecimal HTML color (i.e. #RRGGBB)
         * and convert it into Hexadecimal KML color scheme i.e. AABBGGRR
         *
         * @param {String} color : an HTML color #RRGGBB
         * @param {float} opacity : opacity (0 to 1)
         */
        color2KML: function(color, opacity) {

            /*
             * Default opacity is 40%
             */
            opacity = opacity || 0.4;
            opacity = Math.round(opacity * 16).toString(16);

            /*
             * First remove the # character
             */
            color = color ? color.replace(/#/, "").toLowerCase() : "ee9900";

            /*
             * Split color RRGGBB into an array [RR,GG,BB]
             * and recompose it into BBGGRR
             */
            return opacity+opacity+color.substring(4,6)+color.substring(2,4)+color.substring(0,2);
        },

        /**
         * Return a KML string  ("GroundOverlay") from an OpenLayers Image
         * Structure :
         *
         *  feature.geometry // geometry object
         *  feature.geometry.CLASS_NAME // geometry type
         *      - OpenLayers.Geometry.Point
         *      - OpenLayers.Geometry.LineString
         *      - OpenLayers.Geometry.Polygon
         *  feature.geometry.components[] // array of points
         *      feature.geometry.components[].x
         *      feature.geometry.components[].y
         *
         * @param {OpenLayers.Feature} feature : a feature in map coordinates
         * @return {String} a KML Placemark
         */
        imageToKML: function(layer) {

            /*
             * Compute epsg:4326 extent
             */
            var geoBounds = layer["_M"] && layer["_M"].bounds ? M.Map.Util.p2d(layer["_M"].bounds.clone()) : M.Map.Util.p2d(layer.bounds.clone());

            if (layer.url) {
                return '<GroundOverlay>'
                + '<name>'+this.encode(M.Util._(layer.name))+'</name>'
                + '<description></description>'
                + '<drawOrder>0</drawOrder>'
                + '<Icon>'
                + '<href>'+this.encode(layer.url)+'</href>'
                + '</Icon>'
                + '<LatLonBox>'
                + '<north>'+geoBounds.top+'</north>'
                + '<south>'+geoBounds.bottom+'</south>'
                + '<east>'+geoBounds.left+'</east>'
                + '<west>'+geoBounds.right+'</west>'
                + '<rotation>0</rotation>'
                + '</LatLonBox>'
                + '</GroundOverlay>';
            }

            return '';
        },

        /**
         * Return a KML string  ("GroundOverlay") from an OpenLayers WMS layer
         * Structure :
         *
         *  feature.geometry // geometry object
         *  feature.geometry.CLASS_NAME // geometry type
         *      - OpenLayers.Geometry.Point
         *      - OpenLayers.Geometry.LineString
         *      - OpenLayers.Geometry.Polygon
         *  feature.geometry.components[] // array of points
         *      feature.geometry.components[].x
         *      feature.geometry.components[].y
         *
         * @param {OpenLayers.Feature} feature : a feature in map coordinates
         * @return {String} a KML Placemark
         */
        wmsToKML: function(layer) {

            /*
             * Compute epsg:4326 extent of the map
             */
            var geoBounds = Map.Util.p2d(Map.map.getExtent()),
                version = layer["_M"].layerDescription.version || "1.1.0",
                projstr = version === "1.3.0" ? "&CRS=EPSG:4326&BBOX="+geoBounds.bottom+","+geoBounds.left+","+geoBounds.top+","+geoBounds.right : "&SRS=EPSG:4326&BBOX="+geoBounds.left+","+geoBounds.bottom+","+geoBounds.right+","+geoBounds.top,
                WMSUrl = layer.url
                +"WIDTH=512&HEIGHT=256&FORMAT=image/png&TRANSPARENT=true&SERVICE=WMS&REQUEST=GetMap&LAYERS="
                +layer["_M"].layerDescription.layers
                +"&VERSION="+version
                +projstr;

            if (layer.url) {
                return '<GroundOverlay>'
                + '<name>'+this.encode(M.Util._(layer.name))+'</name>'
                + '<description></description>'
                + '<drawOrder>0</drawOrder>'
                + '<Icon>'
                + '<href>'+this.encode(WMSUrl)+'</href>'
                + '</Icon>'
                + '<LatLonBox>'
                + '<north>'+geoBounds.top+'</north>'
                + '<south>'+geoBounds.bottom+'</south>'
                + '<east>'+geoBounds.left+'</east>'
                + '<west>'+geoBounds.right+'</west>'
                + '<rotation>0</rotation>'
                + '</LatLonBox>'
                + '</GroundOverlay>';
            }

            return '';
        },
        
        /*
         * Replace & by &amp; from input string
         */
        encode:function(s) {
            return s.replace(/\&/g,'&amp;');
        }
        
    }
    
})(window.M, window.M.Map);

/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Define M.Map events
 * 
 * @param {MapshupObject} M
 *   
 */
(function (M) {
      
    M.Map.Events = function(Map) {

        /*
         * Only one Events object instance is created
         */
        if (Map.Events._o) {
            return Map.Events._o;
        }
        
        /*
         * Reference to M.Map
         */
        this.map = Map.map;
        
        /*
         * Set events hashtable
         */
        this.events = {
            
            /*
             * Array containing handlers to be call after
             * a feature is selected
             */
            featureselected:[],
            
            /*
             * Array containing handlers to be call after
             * a change in map.layers list i.e. after :
             *  - add
             *  - remove
             *  - update
             *  - features
             */
            layersend:[],
            
            /*
             * Array containing handlers to be call after
             * a map move
             */
            moveend:[],
            
            /*
             * Array containing handlers to be call after
             * a map resize
             */
            resizeend:[],
            
            /*
             * Array containing handlers to be call after
             * a change in visibility layer
             */
            visibilitychanged:[],
                    
            /*
             * Array containing handlers to be call after
             * a short click on the map (i.e. long click and pan do not trigger event)
             */
             mapclicked:[]
        };
       
        /*
         * Register an event to M.Map
         *
         * @param <String> eventname : Event name => 'resizeend', 'layersend', 'moveend'
         * @param <function> scope : scope related to this event
         * @param <function> handler : handler attached to this event
         */
        this.register = function(eventname , scope, handler) {

            var e = eventname;
            
            /*
             * Special case :
             *  - loadend and loadstart are registered within layersend
             */
            if (eventname === "loadstart" || eventname === "loadend") {
                e = "layersend";
            }

            if (this.events[e]) {
                this.events[e].push({
                    scope:scope,
                    handler:handler
                });
            }

        };

        /*
         * Unregister event
         */
        this.unRegister = function(scope) {
            var arr,
                i,
                key,
                l;
            for (key in this.events) {
                arr = this.events[key];
                for (i = 0, l = arr.length; i < l; i++) {
                    if (arr[i].scope === scope) {
                        arr.splice(i,1);
                        break;
                    }
                }
            }
        };
        
        /*
         * Trigger handlers related to an event
         *
         * @param <String> eventname : Event name => 'resizeend', 'layersend', 'moveend'
         * @param <Object> extra : options object or layer object (optional)
         */
        this.trigger = function(eventname, extra) {

            var obj,i,l,self = this;

            /*
             * Trigger layersend to each handlers
             */
            if (eventname === 'layersend') {
                
                if (extra) {
                    
                    /*
                     * Compute the true number of features layer i.e.
                     * including features hidden within clusters 
                     */
                    if (extra.layer && extra.layer.features) {
                        var count = 0,
                            f = extra.layer.features;
                        for (i = 0, l = f.length; i < l; i++) {
                            count += f[i].cluster ? f[i].cluster.length : 1;
                        }
                        extra.layer["_M"].count = count;
                    }
                    
                    for (i = 0, l = self.events["layersend"].length; i < l; i++) {
                        
                        obj = self.events["layersend"][i];
                        
                        /*
                         * Update layer index if needed
                         */
                        if (extra.layer && extra.action === "features") {
                            M.Map.Util.updateIndex(extra.layer);
                        }
                        
                        if (obj) {
                            obj.handler(extra.action, extra.layer, obj.scope);
                        }
                        
                    }
                    
                    /*
                     * Set mapshup load status
                     */
                    if (!M.isLoaded) {
                        
                        var layer, loading = false;
                        
                        for (i = 0, l = self.map.layers.length; i < l; i++) {

                            layer = self.map.layers[i];
                            
                            /* Don't care of mapshup layers */
                            if (!layer.hasOwnProperty('_M') || layer["_M"].MLayer) {
                                continue;
                            }
                            
                            /* If the layer is due to be destroyed */
                            if (layer._tobedestroyed) {
                                continue;
                            }
                            
                            if (!layer._M.isLoaded) {
                                loading = true;
                                break;
                            }
                        }
                        if (!loading) {
                            M.isLoaded = true;
                            self.map.restrictedExtent ? self.map.zoomToExtent(self.map.restrictedExtent) : Map.setCenter(Map.Util.d2p(new OpenLayers.LonLat(Map.initialLocation.lon,Map.initialLocation.lat)), Map.initialLocation.zoom, true);
                        }
                    }
                }
            }
            else if (eventname === 'loadstart') {

                /*
                 * Paranoid mode
                 */
                if (extra) {

                    /*
                     * Clear layer loaded status
                     */
                    extra["_M"].isLoaded = false;

                    /*
                     * The layer has been updated (for example Wikipedia layer)
                     * Propagate this update to plugins
                     */
                    self.trigger("layersend", {
                        action:"update",
                        layer:extra
                    });
                }
            }
            else if (eventname === 'loadend') {

                /*
                 * Paranoid mode
                 */
                if (extra) {

                    /*
                     * Clear layer loaded status
                     */
                    extra["_M"].isLoaded = true;

                    /*
                     * Update plugins
                     */
                    self.trigger("layersend", {
                        action:"update",
                        layer:extra
                    });
                }
            }
            /*
             * Trigger moveend to each handlers
             */
            else if (eventname === 'moveend') {
                for (i = 0, l = self.events["moveend"].length; i < l; i++) {
                    obj = self.events["moveend"][i];
                    obj.handler(self.map, obj.scope);
                }
            }
            /*
             * Trigger resizeend to each handlers
             */
            else if (eventname === 'resizeend') {
                for (i = 0, l = self.events["resizeend"].length; i < l; i++) {
                    obj = self.events["resizeend"][i];
                    obj.handler(obj.scope);
                }
            }
            /*
             * Trigger other handlers
             */
            else {
                /*
                 * Depending on event, "extra" can be :
                 *   - a layer object
                 *   - a feature object
                 *   - a mouse click event (for mapclicked event)
                 */
                for (i = 0, l = self.events[eventname].length; i < l; i++) {
                    obj = self.events[eventname][i];
                    obj.handler(extra, obj.scope);
                } 
            }
        };
        
        /*
         * Create unique object instance
         */
        Map.Events._o = this;
        
        return this;

    };
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * A LayersGroup is a layers container
 * It must be created like this :
 *  var newGroup = new M.jMap.LayersGroup(map, "name")
 *  (with name a unique name for this group)
 */
(function (M) {
  
    M.Map.LayersGroup = function(map, name, icon) {

        /**
         * Identify this object as a LayerGroup
         */
        this.CLASS_NAME = "M.Map.LayersGroup";
        
        /**
         * Reference to M.Map object
         */
        this.map = map;
        
        /**
         * Group name (must be unique)
         */
        this.name = name;

        /**
         * Unique group id
         */
        this.id = M.Util.getId();
        
        /**
         * Group icon
         */
        this.icon = icon || M.Util.getImgUrl('group.png');

        /**
         * Returns true if all layers within this group are loaded
         * Returns false in the other case
         */
        this.isLoaded = function() {
            var length = this.layers.length;
            for (var i=length;i--;) {
                if (!this.layers[i]["_M"].isLoaded) {
                    return false;
                }
            }
            return true;
        };

        /**
         * Layers belonging to the group
         */
        this.layers = [];

        /**
         * Group menu items
         * (See LayersManager plugin)
         *
         * Exemple of valid menu item :
         * {
         *      id:"search", // Replace with your code
         *      icon:M.getImgUrl("search.png"), // Replace with your code
         *      title:"Search", // Replace with your code
         *      javascript:function() {
         *          M.plugins["Catalog"].searchAll();
         *          return false;
         *      }
         *  }
         */
        this.layersManagerMenuItems = [];

        /**
         * Group visibility
         */
        this.visibility = true;
         
        /**
         * Add a layer to the group
         */
        this.add = function(layer) {

            /**
             * Roll over group layers
             */
            var length = this.layers.length;
            for (var i=length;i--;) {

                /**
                 * Layers is already in the group => do nothing
                 */
                if (this.layers[i]["_M"].MID === layer["_M"].MID) {
                    return false;
                }
            }
            /**
             * Add the input layer to the layers
             */
            this.layers.push(layer);

            return true;
        };

        /**
         * Remove layer from the group
         */
        this.remove = function(layer) {

            /**
             * Roll over group layers
             */
            var length = this.layers.length;
            for (var i=0;i<length;i++) {

                /**
                 * Layer was found => remove it from layers
                 */
                if (this.layers[i]["_M"].MID === layer["_M"].MID) {
                    this.layers.splice(i,1);
                    break;
                }
            }
            
            return true;

        };

        /**
         * Zoom to the group extent
         */
        this.zoomOn = function() {

            /**
             * Compute the global bounds = the bounds of all layers bounds
             */
            var layerBounds = null;
            var bounds = null;
            var length = this.layers.length;
            for (var i=length;i--;) {

                /**
                 * layers[i] has bounds => add it to the global bounds
                 * Note : raster layers (e.g. WMS, Image) should have a layer["_M"].bounds
                 */
                if ((layerBounds = this.layers[i].getDataExtent()) || (layerBounds = this.layers[i]["_M"].bounds)) {
                    bounds ? bounds.extend(layerBounds) : bounds = layerBounds.clone();
                }
            }

            /**
             * Zoom to the group bounds
             */
            if (bounds) {
                this.map.zoomTo(bounds);
            }
        };

        /**
         * Set group visibility => hide or show all layers group
         */
        this.setVisibility = function(visible) {
            var length = this.layers.length;
            for (var i=length;i--;) {
                this.map.setVisibility(this.layers[i], visible);
            }
            this.visibility = visible;
        };

        /**
         * Get group visibility
         * => true if at least one layer is visible
         * => false in other case
         */
        this.getVisibility = function() {
            return this.visibility;
        };

        /**
         * Return menu items
         * (See LayersManager plugin)
         */
        this.getLayersManagerMenuItems = function() {
            return this.layersManagerMenuItems;
        }
    }
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Define FeatureInfo object
 * 
 * @param {MapshupObject} M
 * 
 */
(function(M) {

    M.Map.FeatureInfo = function(options) {

        /*
         * Only one FeatureInfo object instance is created
         */
        if (M.Map.FeatureInfo._o) {
            return M.Map.FeatureInfo._o;
        }

        /**
         * Current selected feature
         */
        this.selected = null;

        /**
         * Current hilited feature
         */
        this.hilited = null;

        /**
         * Feature info popup dimension
         */
        this.dimension = {
            w: 100,
            h: 50
        };

        /**
         * Bypass normal select feature
         * 
         * WARNING : this value SHOULD not be modified
         * It is used by the WPS plugin to bypass the 
         * normal feature selection execution
         */
        this.bypassCallback = null;

        /**
         * Initialization
         * 
         * @param {Object} options
         */
        this.init = function(options) {

            var self = this;

            /*
             * Init options
             */
            options = options || {};

            /*
             * Initialize popups :
             *      - '_p' is the feature info popup
             *      - '_mip' is the mirco info popup
             *  
             *  When one is visible, the other is hidden !
             */
            self._p = new M.Popup({
                modal: false,
                generic: false,
                hideOnClose: true,
                noHeader: true,
                autoSize: true,
                classes: 'fipopup apo',
                followMap: true,
                centered: false,
                onClose: function() {
                    self.clear();
                }
            });

            self._mip = new M.Popup({
                modal: false,
                generic: false,
                hideOnClose: true,
                noHeader: true,
                noFooter: false,
                scope: self,
                classes: 'mip',
                centered: false,
                autoSize: true,
                zIndex: 30000,
                footer: '<div class="tools"></div>',
                onClose: function() {
                    self.clear();
                }
            });
            self._mip.$b.addClass('padded');

            /*
             * Initialize FeatureInfo sidePanel item
             */
            self.sidePanelItem = M.sidePanel.add({
                id: M.Util.getId()
            });

            /*
             * Hide FeatureInfo panel when layer is removed
             */
            M.Map.events.register("layersend", self, function(action, layer, scope) {

                /*
                 * If a feature is selected and the corresponding layer is removed,
                 * then we unselect the feature
                 */
                if (action === "remove") {
                    if (scope.selected && scope.selected.layer && scope.selected.layer.id === layer.id) {
                        scope.unselect(scope.selected);
                    }
                }

                return true;

            });

            /*
             * Event on a change in layer visibility
             */
            M.Map.events.register("visibilitychanged", self, function(layer, scope) {

                /*
                 * Show/Hide featureinfo menu depending on layer visibility
                 */
                if (scope.selected && scope.selected.layer === layer) {

                    if (layer.getVisibility()) {

                        /*
                         * Show feature info panel
                         */
                        var fi = layer["_M"].layerDescription.featureInfo;
                        if (!fi || !fi.noMenu) {
                            scope.getPopup(layer).show();
                        }

                    }
                    else {

                        /*
                         * Hide feature info panel
                         */
                        scope.hide();

                    }
                }
            });

            return self;

        };

        /**
         * Return the right popup depending
         * on the layer configuration
         * 
         * @param {OpenLayers.Layer} layer
         */
        this.getPopup = function(layer) {

            /*
             * Default - return popup
             */
            if (!layer) {
                return this._p;
            }

            return layer['_M'].microInfoTemplate.enable ? this._mip : this._p;

        };

        /**
         * Unselect all feature
         */
        this.clear = function() {

            var c = M.Map.Util.getControlById("__CONTROL_SELECT__");

            /*
             * The cluster nightmare
             */
            if (this.selected) {
                try {
                    c.unselect(this.selected);
                }
                catch (e) {
                    c.unselectAll();
                }
            }
            else {
                c.unselectAll();
            }

            this.unselect(null);

        };

        /**
         * Set popups content
         * 
         *  @param {OpenLayers.Feature}  feature
         */
        this.setContent = function(feature) {

            var template, title, $target, self = this;

            /*
             * Paranoid mode
             */
            if (!feature) {
                return false;
            }
            
            template = feature.layer._M.microInfoTemplate;

            /*
             * CASE 1 : Micro Info Template
             * 
             */
            if (template.enable) {

                title = M.Map.Util.Feature.getTitle(feature);

                /*
                 * Set popup body content
                 */
                self._mip.$b.html(template.body ? M.Util.parseTemplate(template.body, feature.attributes) : '<h2 class="shorten_30" title="' + title + '">' + title + '</h2>');

                /*
                 * Shorten text
                 */
                M.Util.findAndShorten(self._mip.$d, true);

                /*
                 * Set tools target
                 */
                $target = $('.tools', self._mip.$f);

            }

            /*
             * CASE 2 : Feature Info popup
             *   ______________________
             *   |                      |
             *   |.title                |
             *   |.tools                |    
             *   |______________________|
             *      \/
             * 
             */
            else {

                /*
                 * Set popup body content
                 */
                self._p.$b.html('<span class="title" style="white-space:nowrap;">' + M.Map.Util.Feature.getTitle(feature) + '</span><br/><span class="tools"></span>');

                /*
                 * Set tools target
                 */
                $target = $('.tools', self._p.$b);

            }

            /*
             * Set tools
             */
            self.setTools(feature, $target);

            /*
             * Hide featureHilite menu
             */
            M.Map.$featureHilite.empty().hide();

            return true;
        };

        /**
         * Set tools within $target
         * 
         * @param {OpenLayers.Feature} feature
         * @param {jQueryElement} $target
         */
        this.setTools = function(feature, $target) {

            var a, d, i, l, connector, key, plugin, menutools, _a,
                    self = this,
                    tools = [],
                    fi = feature.layer["_M"].layerDescription.featureInfo;

            // Clean target
            $target.empty();

            /*
             * Add "Show info" action
             */
            tools.push({
                id: M.Util.getId(),
                icon: "info.png",
                title: "Info",
                tt: "More info",
                callback: function(a, f) {
                    self.showInfo(f);
                    return false;
                }
            });

            /*
             * Add "Center on feature" action
             */
            tools.push({
                id: M.Util.getId(),
                icon: "center.png",
                title: "Zoom",
                tt: "Zoom on feature",
                callback: function(a, f) {
                    self.zoomOn();
                    return false;
                }
            });

            /*
             * Add "switch layer visibility"
             * Only if the layer is displayed within LayersManager !
             */
            if (feature.layer.displayInLayerSwitcher) {
                tools.push({
                    id: M.Util.getId(),
                    icon: "hide.png",
                    title: "Hide the parent layer",
                    callback: function(a, f) {
                        M.Map.Util.setVisibility(f.layer, false);
                    }
                });
            }
            
            /*
             * services defines specific tools and should contains optional properties
             *      - download : to add a download action
             *      - browse : to add a layer
             * These tools are displayed within the tools list
             *
             */
            
            /*
            * Download feature
            */
            var addDownload = false;
            
            if (feature.attributes.hasOwnProperty("services")) {
                _a = feature.attributes["services"]["browse"];
                addDownload = feature.attributes["services"]["download"] ? true : false;
            }
            else if (feature.attributes['quicklook'] && feature.attributes['quicklook'].toLowerCase().indexOf('service=wms') !== -1) {
                _a = {};
            }

            /*
             * ATOM case
             */
            if (feature.attributes.hasOwnProperty("atom")) {
                if ($.isArray(feature.attributes.atom.links)) {
                    for (var i = 0, l = feature.attributes.atom.links.length; i < l; i++) {
                        if (feature.attributes.atom.links[i].rel === 'enclosure') {
                            addDownload = true;
                            break;
                        }
                    }
                }
            }

            if (addDownload) {
                tools.push({
                    id: M.Util.getId(),
                    icon: "download.png",
                    title: "Download",
                    tt: "Download feature",
                    sla: function(a, f) {
                        if (f && f["attributes"]) {

                            var d = {};

                            /*
                             * GeoJSON case
                             */
                            if (f.attributes.hasOwnProperty("services")) {
                                d = f.attributes["services"]["download"];
                            }
                            /*
                             * ATOM case
                             */
                            else if (f.attributes.hasOwnProperty("atom")) {
                                if ($.isArray(f.attributes.atom.links)) {
                                    for (var i = 0, l = f.attributes.atom.links.length; i < l; i++) {
                                        if (f.attributes.atom.links[i].rel === 'enclosure') {
                                            d.url = feature.attributes.atom.links[i].href;
                                            break;
                                        }
                                    }
                                }
                            }
                            /*
                             * Structure of d is :
                             * {
                             *      url: // url to download
                             *      mimeType: // if "text/html" open a new window. Otherwise set url
                             * }
                             */
                            a.attr("href", d.url);

                            if (d.mimeType && d.mimeType.toLowerCase() === "text/html") {
                                a.attr("target", "_blank");
                            }

                        }
                    },
                    callback: function(a, f) {
                        return true;
                    }
                });
            }

            /*
             * Add layer action
             */
            if (_a) {
                tools.push({
                    id: M.Util.getId(),
                    icon: "add.png",
                    tt: _a["title"] || "Add to map",
                    title: "Add",
                    callback: function(a, f) {

                        /*
                         * Add layer obj
                         */
                        var l;
                        
                        if (f.attributes.hasOwnProperty('services') && f.attributes["services"]["browse"]) {
                            l = M.Map.addLayer(f.attributes["services"]["browse"]["layer"]);
                        }
                        else if (f.attributes.hasOwnProperty('quicklook') && f.attributes['quicklook'].toLowerCase().indexOf('service=wms') !== -1) {
                            if (M.Map.layerTypes["WMS"]) {
                                var layerDescription = M.Map.layerTypes["WMS"].getLayerDescriptionFromUrl(f.attributes['quicklook']);
                                layerDescription.type = 'WMS';
                                layerDescription.srs = 'EPSG:3857';
                                l = M.Map.addLayer(layerDescription);
                            }
                        }
                        
                        /*
                         * Force zoom on added layer
                         */
                        if (l) {
                            M.Map.zoomTo(l.getDataExtent() || l["_M"].bounds);
                        }

                        return false;
                    }
                });

            }

            /**
             * Add item from other plugins
             */
            for (key in M.plugins) {
                plugin = M.plugins[key];
                if (plugin) {
                    if ($.isFunction(plugin.getFeatureActions)) {
                        menutools = plugin.getFeatureActions(feature);
                        if (menutools) {
                            if (menutools instanceof Array) {
                                for (i = 0, l = menutools.length; i < l; i++) {
                                    tools.push(menutools[i]);
                                }
                            }
                            else {
                                tools.push(menutools);
                            }
                        }
                    }
                }
            }

            /*
             * If a layerDescription.featureInfo.action, add an action button
             */
            if (fi && fi.action) {

                /*
                 * The action is added only if javascript property is a valid function
                 */
                if ($.isFunction(fi.action.callback)) {

                    /*
                     * Add feature action
                     */
                    tools.push({
                        id: M.Util.getId(),
                        icon: fi.action["icon"],
                        title: fi.action["title"],
                        tt: fi.action["tt"],
                        callback: function(a, f) {
                            fi.action.callback(a, f);
                            return false;
                        }
                    });

                }

            }

            /*
             * If feature layer got a searchContext, set actions defined within its connector
             */
            if (feature.layer["_M"].searchContext) {

                connector = feature.layer["_M"].searchContext.connector;

                if (connector && connector.action) {

                    /*
                     * Add feature action
                     */
                    tools.push({
                        id: M.Util.getId(),
                        icon: connector.action["icon"],
                        title: connector.action["title"],
                        tt: connector.action["tt"],
                        sla: $.isFunction(connector.action.sla) ? connector.action.sla : null,
                        callback: function(a, f) {

                            /*
                             * If an href was set with sla function, resolve href
                             * Otherwise trigger callback
                             */
                            if ($.isFunction(connector.action.callback)) {
                                if (a.attr("href") === "#") {
                                    connector.action.callback(a, f);
                                    return false;
                                }
                            }
                            return true;
                        }
                    });

                }

            }

            /*
             * Set actions
             */
            for (i = 0, l = tools.length; i < l; i++) {
                a = tools[i];
                $target.append('<a class="item image" jtitle="' + M.Util._(a.tt || a.title) + '" id="' + a.id + '"><img class="middle" src="' + M.Util.getImgUrl(a.icon) + '"/></a>');
                d = $('#' + a.id);

                /* Add tooltip */
                M.tooltip.add(d, 'n', 10);

                (function(d, a, f) {
                    d.click(function(e) {
                        return a.callback(a, f);
                    });
                })(d, a, feature);

                /*
                 * The "sla" function can be used to set href
                 */
                if (a.sla) {
                    a.sla(d, feature);
                }

            }
        };

        /**
         * Select feature and get its information
         * Called by "onfeatureselect" events
         * 
         * @param feature : 
         * @param _triggered : if true the feature selection has been triggered
         *                     automatically and not by a user click
         *                     This attribute is set to true by Catalog plugins
         *                     when feature is selected by clicking on the search result panel
         */
        this.select = function(feature, _triggered) {

            var c, i, bounds, length, ran, self = this;

            /*
             * Set select time (see unselect function)
             */
            self._tse = (new Date()).getTime();

            /*
             * Two types of clusters :
             * 
             * 1. Points clusters
             *    
             *    The map is zoomed on the cluster extent upon click
             *    
             * 2. Polygons clusters
             * 
             *    Each polygons inside the cluster are shown within the SidePanel
             *    upon click             * 
             */
            if (feature.cluster) {

                length = feature.cluster.length;

                if (length > 0) {
                    
                    /*
                     * OpenLayers issue ?
                     * In some cases cluster does have a null layer...
                     */
                    if (!feature.layer) {
                        feature.layer = feature.cluster[0].layer;
                    }
                
                    if (feature.layer && feature.layer['_M'].clusterType === "Polygon") {
                        self.selected = feature;
                        self.showCluster(feature.cluster);
                    }
                    else {

                        /*
                         * Initialize cluster bounds with first item bounds
                         */
                        bounds = feature.cluster[0].geometry.getBounds().clone();

                        /*
                         * Add each cluster item bounds to the cluster bounds
                         */
                        for (i = 1; i < length; i++) {
                            bounds.extend(feature.cluster[i].geometry.getBounds());
                        }

                        /*
                         * Zoom on the cluster bounds
                         */
                        M.Map.map.zoomToExtent(bounds);

                    }

                    /*
                     * Hide feature info panel
                     */
                    self.hide();

                    return false;

                }
            }

            /*
             * If global _triggered is set to true then previous select was triggered by a process
             * and not by a user click.
             * 
             * In this case the Lon/Lat click position is set on the middle of the map
             * Otherwise it is set on the middle of the clicked object if it is a Point and on the clicked xy
             * if it is a LineString or a Polygon
             */
            self._p.setMapXY(self._triggered ? M.Map.map.getCenter() : (feature.geometry.CLASS_NAME === "OpenLayers.Geometry.Point" ? feature.geometry.getBounds().getCenterLonLat() : M.Map.map.getLonLatFromPixel(M.Map.mousePosition)));

            /*
             * This is a bit tricky...
             * If _triggered is set to true, then set the global _triggered to true
             */
            if (_triggered) {
                self._triggered = true;
                c = M.Map.Util.getControlById("__CONTROL_SELECT__");
                if (self.selected) {
                    try {
                        c.unselect(self.selected);
                    }
                    catch (e) {
                        self.selected = null;
                    }
                }
                return c.select(feature);
            }

            /*
             * Call mapshup featureselect event unless the selected was triggered
             */
            if (!self._triggered) {
                M.Map.events.trigger("featureselected", feature);
            }

            /*
             * Set _triggered to false (see above)
             */
            self._triggered = false;

            /*
             * Hide menu
             */
            M.menu.hide();

            /*
             * Set the current selected object
             */
            self.selected = feature;

            /*
             * Experimental : bypass mechanism (used by Plugins/WPSClient)
             */
            if ($.isFunction(self.bypassCallback)) {
                self.bypassCallback(feature);
                try {
                    c.unselect(self.selected);
                }
                catch (e) {
                    self.selected = null;
                }
                return true;
            }

            /*
             * If layerType.resolvedUrlAttributeName is set,
             * display feature info within an iframe
             */
            ran = M.Map.layerTypes[feature.layer["_M"].layerDescription["type"]].resolvedUrlAttributeName;
            if (ran) {

                /*
                 * Add a new item to South Panel
                 * 
                 * Note : unique id is based on the feature layer type
                 * and feature layer name. Ensure that two identical
                 * feature leads to only one panel item 
                 */
                var t = M.Map.Util.Feature.getTitle(feature),
                        panelItem = M.southPanel.add({
                        id:M.Util.crc32(t + feature.layer["_M"].layerDescription["type"]),
                        tt:t,
                        title:t,
                        unremovable:false,
                        html:'<iframe class="frame" src="' + feature.attributes[ran] + '" width="100%" height="100%"></iframe>',
                        onclose:function() {

                    /*
                     * Unselect feature
                     */
                    if (feature && feature.layer) {
                        M.Map.Util.getControlById("__CONTROL_SELECT__").unselect(feature);
                    }

                    /*
                     * Hide activity
                     */
                    M.activity.hide();

                }
                });

                M.southPanel.show(panelItem);

                M.activity.show();
                $('.frame', panelItem.$d).load(function() {
                    M.activity.hide();
                });

            }
            else {
                
                var fi = feature.layer["_M"].layerDescription.featureInfo;
                
                /*
                 * Show feature information
                 */
                self.hide();
                
                /*
                 * Set popup content
                 */
                if (!fi || !fi.noMenu) {
                    self.setContent(feature);
                    self.getPopup(feature.layer).show();
                }
            
                /*
                 * Call back function on select
                 */
                if (fi && $.isFunction(fi.onSelect)){
                    fi.onSelect(feature);
                }

            }

            return true;

        };

        /**
         * Unselect feature and clear information
         * Called by "onfeatureunselect" events
         * 
         * @param {OpenLayers.Feature} feature 
         */
        this.unselect = function(feature) {

            var self = this;

            /*
             * Set unselect time
             */
            self._tun = (new Date()).getTime();

            if (feature && feature.layer['_M'].clusterType === "Polygon") {
                M.sidePanel.hide(self.sidePanelItem);
            }
            
            /*
             * Call back function on unselect
             */
            if (feature && feature.layer["_M"].layerDescription.featureInfo && $.isFunction(feature.layer["_M"].layerDescription.featureInfo.onUnselect)){
                feature.layer["_M"].layerDescription.featureInfo.onUnselect(feature);
            }

            M.Map.featureInfo.selected = null;

            /*
             * Call mapshup featureselect event
             */
            M.Map.events.trigger("featureselected", null);

            /*
             * This is really and awfully tricky...
             * If user select another feature, the current feature is unselected
             * before the new one is selected.
             * Thus, we should close the panel if and only if the unselect is a 
             * true unselect and not an unselect due to a new select.
             * This is done by delaying the panel closing to a time superior to
             * the delay between an unselect/select sequence
             */
            setTimeout(function() {

                if (self._tun - self._tse > 0) {

                    /*
                     * Hide menu
                     */
                    M.menu.hide();

                    /*
                     * Hide feature info panel
                     */
                    self.hide();

                }

            }, 10);
            
        };

        /**
         * Zoom map on selected feature
         */
        this.zoomOn = function() {
            if (M.Map.featureInfo.selected && M.Map.featureInfo.selected.geometry) {
                M.Map.zoomTo(M.Map.featureInfo.selected.geometry.getBounds());
            }
        };

        /*
         * Hilite feature
         */
        this.hilite = function(f) {

            var self = this,
                    c = M.Map.Util.getControlById("__CONTROL_HIGHLITE__");

            if (c && f) {
                try {

                    /*
                     * First unhighlight all feature
                     */
                    var i, l, fs = M.Map.Util.getFeatures(f.layer);

                    for (i = 0, l = fs.length; i < l; i++) {
                        c.unhighlight(fs[i]);
                    }

                    /*
                     * Highlite input feature
                     */
                    self.hilited = f;
                    c.highlight(f);

                }
                catch (e) {
                }
            }

        };

        /*
         * Hilite feature
         */
        this.unhilite = function(f) {

            var self = this,
                    c = M.Map.Util.getControlById("__CONTROL_HIGHLITE__");

            if (c && f) {
                try {

                    /*
                     * Unhighlite input feature
                     */
                    self.hilited = null;
                    c.unhighlight(f);

                }
                catch (e) {
                }
            }

        };

        /**
         * Set info popup html content
         * 
         * 1. feature got a quicklook property
         *  ___________________________
         * |          .title           | .header
         * |___________________________| 
         * |  ________                 |
         * | |        | |              |
         * | | .ql    | |    .info     |
         * | |________| |              |
         *  –––––––––––––––––––––––––––
         *  
         * 2. Otherwise
         *  ___________________________
         * |          .title           | .header
         * |___________________________| 
         * |                           | 
         * |          .info            | .body
         * |___________________________|
         * 
         * @param feature : the feature to display
         *                 
         */
        this.showInfo = function(feature) {

            /*
             * Paranoid mode
             */
            if (!feature) {
                return false;
            }

            var $target, id, v, t, i, l, k, kk, ts,
                    $info,
                    layerType,
                    typeIsUnknown = true,
                    title = M.Util.stripTags(M.Map.Util.Feature.getTitle(feature)),
                    ql = feature.attributes['quicklook'] || feature.attributes['imageUrl']; // Thumbnail of quicklook attributes

            /*
             * Create the info container over everything else
             * 
             * <div class="fi">
             *      <div class="header>
             *          <div class="title"></div>
             *      </div>
             *      <div class="body">
             *      
             *      </div>
             * </div>
             *      
             * 
             */
            $target = M.Util.$$('#' + M.Util.getId(), $('#mapshup'))
                    .addClass("overall")
                    .append('<div class="fi"><div class="header"><div class="title"></div></div><div class="body"></div></div>');

            /*
             * Add a close button to the info panel
             */
            M.Util.addClose($target, function() {
                M.activity.hide();
                $target.remove();
            });

            /*
             * Add a quicklook div (or not)
             * Set quicklook width and height to respectively
             * 40% and 90% of the main wrapper 
             */
            if (ql) {
                
                $('.body', $target).append('<div class="ql" style="float:left;width:49%;"><img src="' + ql + '"/></div><div class="info"></div>');
                $('.ql img', $target).css({
                    'max-width': Math.round($('#mapshup').width() * 4 / 10),
                    'max-height': Math.round($('#mapshup').height() * 9 / 10)
                });
               
               /*
                * Show activity indicator during image loading
                */
                var image = new Image();
                image.src = ql;
                M.activity.show();
                $(image).load(function() {
                    M.activity.hide();
                }).error(function() {
                    M.activity.hide();
                });
            }
            else {
                $('.body', $target).append('<div class="info"></div>');
            }

            /*
             * Set header
             */
            $('.title', $target).attr('title', feature.layer.name + ' | ' + title).html(title);

            /*
             * Roll over layer types to detect layer features that should be
             * displayed using a dedicated setFeatureInfoBody function
             */
            layerType = M.Map.layerTypes[feature.layer["_M"].layerDescription["type"]];
            if (layerType) {
                if ($.isFunction(layerType.setFeatureInfoBody)) {
                    layerType.setFeatureInfoBody(feature, $('.info', $target));
                    typeIsUnknown = false;
                }
            }

            /*
             * If feature type is unknown, use default display
             *  
             * In both case, key/value are displayed within a <table>
             * 
             *      <div class="thumb"></div>
             *      <div class="info"></div>
             * 
             */
            if (typeIsUnknown) {

                /*
                 * Default feature info are set within an html table
                 */
                $('.info', $target).html('<table style="width:' + (ql ? '45' : '95') + '%"></table>');
                $info = $('.info table', $target);

                /*
                 * Roll over attributes  
                 */
                for (k in feature.attributes) {

                    /*
                     * Special keywords
                     */
                    if (k === 'self' || k === 'identifier' || k === 'icon' || k === 'thumbnail' || k === 'quicklook' || k === 'imageUrl' || k === 'modified' || k === 'color') {
                        continue;
                    }

                    /*
                     * Get key value
                     */
                    v = feature.attributes[k];
                    if (v) {

                        /*
                         * Check type
                         */
                        t = typeof v;

                        /*
                         * Simple case : string
                         */
                        if (t === "string" && M.Util.isUrl(v)) {
                            $info.append('<tr><td>' + M.Map.Util.Feature.translate(k, feature) + '</td><td>&nbsp;</td><td><a target="_blank" title="' + v + '" href="' + v + '">' + M.Util._("Download") + '</a></td></tr>');
                        }
                        /*
                         * Object case
                         */
                        else if (t === "object") {

                            /*
                             * Special case for services property
                             * services defines specific actions and should contains optional properties
                             *      - download : to add a download action
                             *      - browse : to add a layer
                             * These actions are displayed within the actions list - see this.setFooter(feature) function
                             *
                             */
                            if (k === "services") {
                                continue;
                            }
                            
                            /*
                             * ATOM special case
                             */
                            if (k === "atom") {
                                if (v['id']) {
                                    $info.append('<tr><td title="' + M.Util._('identifier') + '">' + M.Util._('identifier') + '</td><td>&nbsp;</td><td>' + v['id'] + '<td></tr>');
                                }
                                if (v['updated']) {
                                    $info.append('<tr><td title="' + M.Util._('updated') + '">' + M.Util._('updated') + '</td><td>&nbsp;</td><td>' + v['updated'] + '<td></tr>');
                                }
                                continue;
                            }
                            
                            /*
                             * Roll over properties name
                             */
                            for (kk in v) {

                                /*
                                 * Check type : if object => create a new tab
                                 */
                                if (typeof v[kk] === "object") {

                                    /*
                                     * Special case for photos array
                                     * No tab is created but instead a photo gallery
                                     * is displayed as thumbs
                                     */
                                    if (kk === 'photos') {
                                        $info.append('<tr><td></td><td>&nbsp;</td><td class="thumbs"><td></tr>');
                                        for (i = 0, l = v[kk].length; i < l; i++) {
                                            id = M.Util.getId();
                                            $('.thumbs', $info).append('<img href="' + v[kk][i]["url"] + '" title="' + v[kk][i]["name"] + '" id="' + id + '" src="' + v[kk][i]["url"] + '"/>');
                                            /*
                                             * Popup image
                                             */
                                            (function($id) {
                                                $id.click(function() {
                                                    M.Util.showPopupImage($id.attr('href'), $id.attr('title'));
                                                    return false;
                                                });
                                            })($('#' + id));

                                        }
                                    }
                                }
                                else {
                                    ts = M.Map.Util.Feature.translate(k, feature);
                                    $info.append('<tr><td title="' + ts + '">' + ts, 20 + ' &rarr; ' + M.Map.Util.Feature.translate(kk, feature) + '</td><td>&nbsp;</td><td>' + v[kk] + '</td></tr>');
                                }
                            }

                        }
                        else {
                            ts = M.Map.Util.Feature.translate(k, feature);
                            $info.append('<tr><td title="' + ts + '">' + ts + '</td><td>&nbsp;</td><td>' + M.Map.Util.Feature.getValue(feature, k, v) + '</td></tr>');
                        }
                    }
                }
            }

            $target.show();

            return true;

        };

        /**
         * Show cluster features within SidePanel
         * 
         * @param {Array} cluster
         * 
         */
        this.showCluster = function(cluster) {
            
            var i, l, f, id, icon, title, $t, self = this;
            
            $t = self.sidePanelItem.$d.html('<div class="marged"></div>').children().first();
            
            /*
             * Roll over features
             */
            for (i = 0, l = cluster.length; i < l; i++) {

                f = cluster[i];

                /*
                 * This is very important to ensure that feature are correctly synchronized
                 */
                if (!f.layer) {
                    continue;
                }

                /*
                 * The id is based on feature unique id
                 * 
                 * !! Warning !! 'f.id' is reserved by LayersManager plugin
                 */
                id = M.Util.encode(f.id) + 'c';

                /*
                 * Some tricky part here :
                 * 
                 *   - use of jquery .text() to strip out html elements
                 *     from the M.Map.Util.Feature.getTitle() function return
                 *     
                 *   - If icon or thumbnail is not defined in the feature attributes,
                 *     then force text span display
                 */
                icon = M.Map.Util.Feature.getIcon(f);
                title = M.Util.stripTags(M.Map.Util.Feature.getTitle(f));
                $t.append('<span class="thumbs" jtitle="' + title + '" id="' + id + '">' + (icon ? '' : '<span class="title">' + title + '</span>') + '<img src="' + (icon ? icon : M.Util.getImgUrl('nodata.png')) + '"></span>');
                (function(f, $div) {
                    $div.click(function(e) {

                        e.preventDefault();
                        e.stopPropagation();

                        /*
                         * Zoom on feature and select it
                         */
                        //console.log(f);
                        /*M.Map.zoomTo(f.geometry.getBounds());
                        M.Map.featureInfo.select(f, true);
                        self.hilite(f);*/

                        return false;
                    });
                    M.tooltip.add($div, 'e', 10);
                })(f, $('#' + id));
            }
        
            /*
             * Display the SidePanel
             */
            M.sidePanel.show(this.sidePanelItem);

        };

        /**
         * Hide popups
         */
        this.hide = function() {
            var self = this;
            if (self._p.$d.is(':visible')) {
                self._p.hide(true);
            }
            if (self._mip.$d.is(':visible')) {
                self._mip.hide(true);
            }
        };

        /*
         * Create unique object instance
         */
        M.Map.FeatureInfo._o = this;

        return this.init(options);
    };

})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Define M.Map events
 */
(function (M) {
    
    /*
     * LayerDescription
     * 
     * @param obj : layer description object
     * @param jmap : reference to M.Map object
     */
    M.Map.LayerDescription = function(obj, map) {
        
        /**
         * Layer description
         */
        this.obj = obj;
        
        /**
         * M.Map reference
         */
        this.map = map;
        
        /**
         * Check if a layerDescription is valid,
         * i.e. if mandatory properties based on type
         * are present
         */
        this.isValid = function() {
            
            var layerType,i,l,m;
            
            /*
             * Paranoid mode
             */
            if (!this.obj || typeof this.obj !== "object") {
                return false;
            }
        
            /*
             * Check layerType - not valid then return false
             */
            layerType = this.getLayerType();
            if (!layerType) {
                return false;
            }
        
            /*
             * Mandatory properties array
             */
            m = layerType.mandatories || [];
        
            /*
             * Roll over properties
             */
            for (i=0, l = m.length; i < l; i++) {
                if (!this.obj.hasOwnProperty([m[i]])) {
                    return false;
                }
            }

            return true;
    
        };
        
        /*
         * Return the layerType for this layer description
         */
        this.getLayerType = function() {
            if (!this.map || !this.obj) {
                return null;
            }
            return this.map.layerTypes[this.obj.type];
        };
        
        /*
         * Return the layer description object
         */
        this.get = function() {
            return this.obj;
        };
        
        /**
         * Compute a MID from its type
         */
        this.getMID = function() {

            var o = this.obj, layerType = this.map.layerTypes[o.type];

            /*
             * Layerdescrition.MID has preseance over everything else
             * This is used to force MID on specific layers (drawing for example)
             */
            if (o.MID) {
                return o.MID;
            }
            
            /*
             * Layer type does not exists => return null
             */
            if (!layerType) {
                return null;
            }

            /*
             * Default MID is M.crc32(layerDescription.type + (layerDescription.url || layerDescription.title || ""))
             * unless specified in the layerType object
             */
            if ($.isFunction(layerType.getMID)) {
                return layerType.getMID(o);
            }
            else {
                
                /*
                 * 
                 * Special case of "replace" property - use "url" instead of "title" for unique identifier
                 */
                if (o.replace && o.hasOwnProperty("url")) {
                    
                    /*
                     * Important : split url to remove additionnal parameters (i.e. after "?" char)
                     */
                    return M.Util.crc32(o.type + o.url.split('?')[0]);
                    
                }
            }
            
            return M.Util.crc32(o.type + (o.title || ""));
        };
        
        /**
         * Return a human readable array of layer properties
         * 
         * @return Array of Key/Value arrays
         */
        this.getInfo = function() {
        
            var arr = [];

            if (this.obj) {

                var layerType = this.map.layerTypes[this.obj["type"]];

                /*
                 * Only valid layerType are processed
                 */
                if (layerType) {

                    /*
                     * Common description
                     */
                    arr = [
                    ["Title", M.Util._(this.obj["title"])],
                    ["Description", M.Util._(this.obj["description"] || "No description available")],
                    ["Type", M.Util._(this.obj["type"])]
                    ];

                    /*
                     * Check for an eventual preview url
                     */
                    if (this.obj["preview"]) {
                        arr.push(["Preview", this.obj["preview"]]);
                    }

                    if ($.isFunction(layerType.getInfo)) {
                        arr = arr.concat(layerType.getInfo(this.obj));
                    }
                }
            }
        
            return arr;
        
        };
        
        /**
         * Return a default StyleMap from layerDescription
         */
        this.getStyleMap =  function() {

            var icon = null, // Determines if point must be represented by an icon
                obj = this.obj, // Object reference
                opacity = obj.hasOwnProperty("opacity") ? obj.opacity : 0.2; // Set opacity

            /*
             * Features got a 'icon' attribute => use it as symbol
             * for point representation
             */
            if (obj.hasIconAttribute) {
                icon = "${icon}";
            }
            
            /*
             * Set the default style
             */
            var styleDefault = new OpenLayers.Style(OpenLayers.Util.applyDefaults({
                pointRadius: icon ? 10 : "${scaledSize}",
                externalGraphic:icon,
                fillOpacity: opacity,
                graphicOpacity: 1,
                strokeColor:"#000",
                strokeWidth:1,
                label:"${label}",
                fontSize:"${fontSize}",
                fontColor:"${fontColor}",
                fontStrokeColor:"${fontColor}",
                fontStrokeWidth:0,
                fillColor:"${color}"
            }, OpenLayers.Feature.Vector.style["default"]), {

                /*
                 * Context depends on filterOn attribute
                 */
                context: {

                    /*
                     * If icon is specified
                     */
                    icon: function(feature) {
                        if(feature.cluster) {
                            return M.Util.getImgUrl('imgcluster.png');
                        }
                        else {
                            return feature["attributes"].icon;
                        }
                    },
                    
                    /*
                     * Label for clusters
                     */
                    label: function(feature) {
                        var fi = feature.layer["_M"].layerDescription.featureInfo;
                        if(feature.cluster) {
                            return feature.cluster.length;
                        }
                        else if (fi && fi.label && fi.label["value"]) {
                            return fi.label["value"].replace(/\$+([^\$])+\$/g, function(m, key, value) {
                                var k = m.replace(/[\$\$]/g, '');
                                return M.Map.Util.Feature.getValue(feature, k, feature.attributes[k]);
                            });
                        }
                        else {
                            return '';
                        }
                    },
                    
                    /*
                     * Fontsize for labels
                     */
                    fontSize: function(feature) {
                        var fi = feature.layer["_M"].layerDescription.featureInfo;
                        if (fi && fi.label) {
                            return fi.label["size"] || 10;
                        }
                        else {
                            return 20;
                        }
                    },
                    
                    /*
                     * Font color for labels
                     */
                    fontColor: function(feature) {
                        var fi = feature.layer["_M"].layerDescription.featureInfo;
                        if (fi && fi.label) {
                            return fi.label["color"] || "#000";
                        }
                        else {
                            return "#fff";
                        }
                    },
                        
                    /*
                     * Clusters have a different color than normal objects
                     */
                    color: function(feature) {

                        /*
                         * Cluster case
                         */
                        if(feature.cluster) {
                            return "#666";
                        }
                        else {
                            return feature["attributes"].color || feature.layer["_M"].layerDescription.color || "darkgray";
                        }
                    
                    },
                    scaledSize: function(feature) {

                        /*
                         * Cluster case
                         */
                        if(feature.cluster) {

                            /*
                             * filterOn is defined => return the size element with the larger filterOn value
                             * within the cluster
                             */
                            if (feature.attributes[obj.filterOn]) {
                                var i,max,main;
                                for(var c = 0, l = feature.cluster.length; c < l; c++) {
                                    i = feature.cluster[c].attributes[obj.filterOn];
                                    if(i > max) {
                                        max = i;
                                        main = c;
                                    }
                                }
                                return feature.cluster[main].attributes[obj.filterOn] * 5;
                            }
                            /*
                             * filterOn is not defined => return proportional to cluster size
                             */
                            else {
                                return 15 + Math.min(feature.cluster.length * 2, 40);
                            }
                        }
                        /*
                         * Normal case => no cluster
                         */
                        else {

                            /*
                             * filterOn is defined => return a size proportional to the filterOn value
                             */
                            if (feature.attributes[obj.filterOn]) {
                                return feature.attributes[obj.filterOn] * 5;
                            }
                            /*
                             * filterOn is not defined => return a normal scale
                             */
                            else {
                                return 15;
                            }
                        }
                    }
                }
            });

            /*
             * Return styleMap
             */
            return new OpenLayers.StyleMap({
                "default": styleDefault,
                "select": {
                    strokeColor:"#ffff00",
                    fillOpacity:opacity <= 0.9 ? opacity + 0.1 : opacity - 0.1
                }
            });
        };
       
    };
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * Search context function
 * One searchContext should be initialized for each catalog layer within the
 * _M.searchContext property
 * (i.e. layer["_M"].searchContext = new M.plugins["Catalog"].SearchContext(layer,connector,options);)
 *
 * @param layer : catalog layer
 * @param connector : catalog connector
 * @param options : possible options are
 * {
 *      autosearch : true to set an auto search mode
 *      nextRecord : next record value
 *      nextRecordAlias : alias name for "nextRecord" property - see OpenSearch catalog connector
 *      numRecordsPerPage : Maximum number of records per page
 *      numRecordsPerPageAlias : alias name for "numRecordsPerPage" property - see OpenSearch catalog connector
 *      callback : function to call after a successfull search
 *      scope : scope of the callback function
 * }
 * 
 */
(function(M) {
    
    M.Map.SearchContext = function(layer, connector, options) {

        /*
         * Paranoid mode
         */
        options = options || {};
        
        /**
         * If autoSearch is set to true, search() function is triggered
         * each time an item is added/removed/updated from the items list
         */
        this.autoSearch = M.Util.getPropertyValue(options, "autoSearch", false);

        /**
         * Connector reference
         */
        this.connector = connector;
        
        /**
         * Layer reference
         */
        this.layer = layer;

        /**
         * Next record value
         */
        this.nextRecord = M.Util.getPropertyValue(options, "nextRecord", 1);

        /**
         * Next record alias value
         */
        this.nextRecordAlias = options.nextRecordAlias || "nextRecord";
        
        /**
         * Maximum number of records per page
         */
        this.numRecordsPerPage = M.Util.getPropertyValue(options, "numRecordsPerPage", 20);
        
        /**
         * Maximum number of records per page
         */
        this.numRecordsPerPageAlias = options.numRecordsPerPageAlias || "numRecordsPerPage";
        
        /**
         * Maximum number of results for this search context
         */
        this.totalResults = 0;

        /**
         * Callback function to be called when search is successfully performed
         */
        this.callback = options.callback;

        /**
         * Scope for the Callback function
         */
        this.scope = options.scope;
        
        /**
         * When useGeo is set to true, search is restricted to the map view extent
         */
        this.useGeo = true;

        /**
         * VERY IMPORTANT !
         *
         * items array contains "filter item"
         *
         * An item is defined like this :
         *  {
         *      id: unique item identifier
         *      title: item title
         *      value: value sent to the server (only mandatory if son is defined)
         *      operator: operator linking item to its son,
         *      son:[{
         *          id: son identifier (unique within this item),
         *          title: son title
         *          value: value sent to the server,
         *          javascript: action (optional)
         *      }]
         *  }
         *  
         */
        this.items = [];

        /**
         * Check if input item is already
         * defined
         */
        this.isDefined = function(_item) {

            /*
             * Roll over items list (i.e. roll over items already added)
             */
            for (var i = 0, l = this.items.length; i < l; i++){

                var item = this.items[i];

                /*
                 * newItem is already stored in items array
                 */
                if (_item.id === item.id) {

                    /*
                     * newItem has value at the father level
                     * => return true
                     */
                    if (_item.value !== undefined) {
                        return true;
                    }

                    /*
                     * newItem has no value at the father level
                     * => check if son is defined
                     */
                    else {

                        if (item.son && _item.son && _item.son.length > 0) {
                            /*
                             * Roll over newItem sons
                             */
                            for (var j = 0, m = item.son.length; j < m; j++) {

                                /*
                                 * newItem son is already defined
                                 * => return true
                                 */
                                if (_item.son[0].id === item.son[j].id) {
                                    return true;
                                }
                            }
                        }

                    }

                }
            }

            /*
             * If you get there it means that the item
             * is not already defined
             */
            return false;

        };

        /**
         * Add an item to the items array
         *
         * @param newItem
         */
        this.add = function(newItem) {

            /*
             * Roll over items list (i.e. roll over items already added)
             */
            for (var i = 0, l = this.items.length; i < l; i++){

                var item = this.items[i];

                /*
                 * newItem is already stored in items array
                 * do not added again but instead update the content
                 */
                if (newItem.id === item.id) {

                    /*
                     * newItem has value at the father level
                     * => update this value
                     */
                    if (newItem.value !== undefined) {
                        item.value = newItem.value;
                        item.title = newItem.title;
                    }

                    /*
                     * newItem has no value at the father level
                     * => update the son values
                     */
                    else {

                        /*
                         * Roll over newItem sons
                         */
                        for (var j = 0, m = item.son.length; j < m; j++) {

                            /*
                             * newItem son is already defined
                             */
                            if (newItem.son[0].id === item.son[j].id) {
                                
                                item.son[j].value = newItem.son[0].value;
                                item.son[j].title = newItem.son[0].title;
                                
                                /* Automatically trigger search() function if requested */
                                if (this.autoSearch && newItem.id !== "bbox") {
                                    this.search();
                                }
                                
                                return true;
                            }
                        }

                        /*
                         * newItem son was not defined => add it
                         */
                        item.son.push(newItem.son[0]);
                    }

                    /* Automatically trigger search() function if requested */
                    if (this.autoSearch && newItem.id !== "bbox") {
                        this.search();
                    }
                                
                    return true;
                }
            }

            /*
             * newItem was not already defined (i.e. not in the items array)
             * => add it to items array
             */
            this.items.push(newItem);

            /* Automatically trigger search() function if requested */
            if (this.autoSearch && newItem.id !== "bbox") {
                this.search();
            }
            
            return true;
        };

        /**
         * Clear the search context
         * 
         * @param {boolean} notTime : if set to true, time filter is not cleared
         */
        this.clear = function(notTime) {
            this.items = [];
            this.setGeo(this.useGeo);
            if (M.timeLine && notTime) {
                this.setTime(M.timeLine.getInterval());
            }
        };
        
        /**
         * Return the items array as an HTTP GET key/value pairs string
         */
        this.getSerializedParams = function() {

            /*
             * Initialize serializedParams string
             */
            var serializedParams = this.numRecordsPerPageAlias + "=" + this.numRecordsPerPage;

            /*
             * Roll over items
             */
            for (var i = 0, l = this.items.length; i < l; i++) {

                /*
                 * New key/value pair for item[i]
                 */
                serializedParams += "&" + encodeURIComponent(this.items[i].id) + "=";

                /*
                 * Value is defined at item level
                 */
                if (this.items[i].value !== undefined) {
                    serializedParams += encodeURIComponent(this.items[i].value);
                }
                /*
                 * No value defined at item level => get the son values
                 */
                else {
                    
                    /*
                     * Roll over item's sons
                     */
                    for (var j = 0, m = this.items[i].son.length; j < m; j++) {
                        if (j>0) {
                            serializedParams += "|";
                        }
                        serializedParams += encodeURIComponent(this.items[i].son[j].value);
                    }
                }
            }

            return serializedParams;
        };

        /**
         * Get the value of an item
         */
        this.getValue = function(_id) {

            for (var i = 0, l = this.items.length; i < l; i++) {
                if (this.items[i].id === _id) {
                    return this.items[i].value;
                }
            }

            return null;

        };

        /**
         * Make a search on the next page of records
         */
        this.next = function() {

            /*
             * Set the new nextRecord value
             */
            var p = this.nextRecord + this.numRecordsPerPage;

            /*
             * We are already at the last page
             * Do nothing and returns false
             */
            if (p > this.totalResults) {
                return false;
            }

            /*
             * Launch a search
             */
            return this.search({nextRecord:p});
        };

        /**
         * Make a search on the previous page of records
         */
        this.previous = function() {

            /*
             * Set the new next record
             */
            var p = this.nextRecord - this.numRecordsPerPage;

            /*
             * We are already at the first page
             * Do nothing and returns false
             */
            if (p < 0) {
                return false;
            }

            /*
             * Launch a search
             */
            return this.search({nextRecord:p});

        };

        /**
         * Remove item 'id' or 'fatherId->id' from items list
         *
         * If fatherId is null then it is assumes that 'id' is already
         * at father level
         * 
         * @param <boolean> noauto : if true, autosearch is deactivated
         */
        this.remove = function(id, fatherId, noauto) {
            
            var i, j, l, m, self = this;
             
            /*
             * Roll over items
             */
            for (i = 0, l = self.items.length; i < l; i++){

                /*
                 * fatherId is null means that 'id' is a father
                 */
                if (fatherId === null) {

                    /*
                     * id is found
                     */
                    if (id === self.items[i].id) {

                        /*
                         * Remove item id from items list
                         */
                        self.items.splice(i,1);

                        /* Automatically trigger search() function if requested */
                        if (!noauto) {
                            if (self.autoSearch && fatherId !== "bbox") {
                                self.search();
                            }
                        }
                        
                        return true;
                    }
                }

                /*
                 * fatherId is defined means that 'id' is at son level
                 */
                else  {

                    /*
                     * father "fatherId" is found
                     */
                    if (fatherId === self.items[i].id) {

                        /*
                         * Roll over each father's son(s)
                         */
                        for (j = 0, m = self.items[i].son.length; j < m; j++) {

                            /*
                             * son "id" is found
                             */
                            if (id === self.items[i].son[j].id) {

                                /*
                                 * Remove the son from sons
                                 */
                                self.items[i].son.splice(j,1);

                                /*
                                 * If there is no more son, remove the son array
                                 */
                                if (self.items[i].son.length === 0) {
                                    self.items.splice(i,1);
                                }

                                /* Automatically trigger search() function if requested */
                                if (!noauto) {
                                    if (self.autoSearch  && fatherId !== "bbox") {
                                        self.search();
                                    }
                                }
                                return true;
                            }
                        }
                    }
                }

            }
            return false;
        };

        /**
         * Launch a search request on this SearchContext
         *
         * @param {Object} options : options structure
         *                          {
         *                              nextRecord // (optional)
         *                              callback // function to call after search (optional)
         *                          }
         */
        this.search = function(options) {

            var key, nextRecord, _callback, extras = "", layer = this.layer, self = this;
            
            options = options || {};
            
            /*
             * Set nextRecord
             */
            nextRecord = options.nextRecord || 1;
            
            /*
             * Set local _callback
             */
            _callback = $.isFunction(options.callback) ? options.callback : null; 

            /*
             * Set extras parameters
             */
            if (layer["_M"] && layer["_M"].layerDescription.extras) {
                for (key in layer["_M"].layerDescription.extras) {
                    extras += "&"+key+"="+layer["_M"].layerDescription.extras[key];
                }
            }

            /**
             * Launch an asynchronous search
             * The result is a GeoJSON object
             */
            M.Util.ajax({
                url:M.Util.proxify(this.connector.searchUrl + this.getSerializedParams() + "&" + self.nextRecordAlias + "=" + nextRecord + extras),
                async:true,
                dataType:"json",
                success: function(data) {

                    var onSearch, l, lm = M.Plugins.LayersManager;
                    
                    /*
                     * First check if there is no error
                     * Otherwise, display results
                     */
                    if (!data) {
                        
                        /*
                         * Endless search
                         * If nextRecord is greater than 1 then it is assumes that the search
                         * is paginate. In this case the existing features are not removed
                         * Otherwise it is assumes that this is a new search and the existing
                         * features are removed
                         */
                        if (nextRecord === 1) {
                            layer.destroyFeatures();
                        }
                        
                        /*
                         * Tells mapshup that features changed
                         */
                        M.Map.events.trigger("layersend", {
                            action:"features",
                            layer:layer
                        });
                        
                        /*
                         * Be kind with user
                         */
                        M.Util.message(layer.name + " : " + M.Util._("No resut"));
                        
                        
                    }
                    else if (data.error) {
                        M.Util.message(layer.name + " : " + data.error["message"], -1);
                    }
                    else {

                        /*
                         * Endless search
                         * If nextRecord is greater than 1 then it is assumes that the search
                         * is paginate. In this case the existing features are not removed
                         * Otherwise it is assumes that this is a new search and the existing
                         * features are removed
                         */
                        if (nextRecord === 1) {
                            layer.destroyFeatures();
                        }
                        
                        /*
                         * Process the GeoJSON result
                         *
                         * Note: result is in EPSG:4326
                         */
                        var features = new OpenLayers.Format.GeoJSON({
                            internalProjection:M.Map.map.getProjectionObject(),
                            externalProjection:M.Map.pc
                        }).read(data);
                        
                        /*
                         * Empty result
                         */
                        l = features.length;
                        
                        if (!features || l === 0) {
                            
                            /*
                             * Be kind with users !
                             */
                            M.Util.message(M.Util._(layer.name) + " : " + M.Util._("No result"));
                            
                            /*
                             * Add features to the layer
                             * 
                             * WARNING! If the layer is clustered, we need to specifically
                             * recluster the layer
                             */
                            if (layer["_M"].clusterized) {
                                
                                /*
                                 * First strategy is obviously the (Polygon)Cluster strategy 
                                 * If this strategy supports reclustering do it !
                                 */ 
                                if ($.isFunction(layer.strategies[0].recluster)) {
                                    layer.strategies[0].clearCache();
                                    layer.strategies[0].recluster();
                                }
                            
                            }  
                            
                        }
                        else {
                            
                            /*
                             * Add features to the layer
                             * 
                             * WARNING! If the layer is clustered - Since the cluster
                             * strategy will clear the layer features before adding
                             * the new feature, we first need to cache every feature including
                             * previous layer features 
                             */
                            if (layer["_M"].clusterized) {
                                
                                /*
                                 * Add the new features to layer previous features
                                 */
                                var ll = M.Map.Util.getFeatures(layer).concat(features);
                                layer.addFeatures(ll);
                                
                                /*
                                 * First strategy is obviously the (Polygon)Cluster strategy 
                                 * If this strategy supports reclustering do it !
                                 */ 
                                if ($.isFunction(layer.strategies[0].recluster)) {
                                    layer.strategies[0].recluster();
                                }
                            
                            }
                            else {
                                layer.addFeatures(features);
                            }
                            
                            /*
                             * Zoom on layer extent
                             */
                            onSearch = layer["_M"].layerDescription.onSearch || {};
                            
                            if (onSearch.zoom) {
                                M.Map.zoomTo(layer.getDataExtent(), false);
                            }
                            else {
                                M.Map.Util.zoomOn(layer);
                            }
                            
                            /*
                             * Callback function after succesfull search
                             */
                            if ($.isFunction(onSearch.callback)) {
                                onSearch.callback(layer);
                            }
                            
                            /*
                             * Show result in LayersManager
                             */
                            if (lm && lm._o) {
                                lm._o.show(lm._o.get(layer['_M'].MID));
                            }
                        
                            /*
                             * Launch local _callback if defined
                             */
                            if (_callback) {
                                _callback(self.scope,layer);
                            }
                            
                        }
                        
                        /*
                         * Avoid case where server don't take care of numRecordsPerPage value
                         */
                        if (l > self.numRecordsPerPage) {
                            self.numRecordsPerPage = l;
                        }
                        
                        /*
                         * Set nextRecord new value
                         */
                        layer["_M"].searchContext.nextRecord = nextRecord;

                        /*
                         * Update the totalResults value
                         * If data.totalResults is not set then set totalResults to the number of features
                         */
                        var src = data.hasOwnProperty('properties') ? data.properties : data;
                        layer["_M"].searchContext.totalResults = src.hasOwnProperty("totalResults") ? src.totalResults : l;
                        
                        /*
                         * Endless search - Tricky part
                         * 
                         * If this is the first search (i.e. nextRecord === 1) then
                         * tell LayersManager to compute entirely features thumbs (send "features" action)
                         * Otherwise tells LayersManager to refresh features thumbs without removing
                         * previous features (send "featureskeep" action)
                         */
                        M.Map.events.trigger("layersend", {
                            action:nextRecord === 1 ? "features" : "featureskeep",
                            layer:layer
                        });
                        
                        /*
                         * Finally tells callback function that the search was
                         * successfully performed
                         */
                        if (self.callback) {
                            self.callback(self.scope,layer);
                        }

                    }

                }

            },{
                title:M.Util._("Searching") + " : " + M.Util._(this.layer.name),
                cancel:true
            });

            return true;

        };
                                
        /*
         * Set use of search bbox 
         * 
         * @param <boolean> b: true to use search bbox. false otherwise
         * 
         */
        this.setGeo = function(b) {
            
            var self = this;
            
            self.useGeo = b;
            self.setBBOX(self.useGeo ? M.Map.map.getExtent() : null);
            if (self.autoSearch) {
                self.search();
            }
        };
        
        /**
         * Set the bbox to the given bounds
         *
         * @param <OpenLayers.Bounds> bounds : bounds in map projection
         */
        this.setBBOX = function(bounds){

            /**
             * bounds is null => remove geometry item from the SearchContext
             */
            if (!bounds) {
                this.remove('geometry', 'bbox');
            }
            else {

                /**
                 * Create the geographical equivalent to the given bounds
                 */
                var geoBounds = M.Map.Util.p2d(bounds.clone()),
                item = {
                    id:"bbox",
                    title:M.Util._("Search Area"),
                    son: [{
                        id:"geometry",
                        title:"geometry",
                        value:M.Map.Util.convert({
                            input:geoBounds,
                            format:"EXTENT",
                            precision:5,
                            limit:true
                        })
                    }
                    ]
                };
                this.add(item);
            }
        };

        /**
         * Set filter to the given value
         *
         * @param <OpenLayers.Bounds> bounds : bounds in map projection
         */
        this.setFilter = function(id, value) {

            /*
             * Search for item corresponding to the filterId in the connector
             */
            for (var i = 0, l = this.connector.filters.length; i < l; i++) {

                /*
                 * Filter matches
                 */
                if (this.connector.filters[i].id === id) {

                    /*
                     * Only "text" type is supported at the moment
                     */
                    if (this.connector.filters[i].type === "text") {

                        /*
                         * Add a newItem
                         */
                        var newItem = {
                            id:this.connector.filters[i].id,
                            title:this.connector.filters[i].title,
                            operator:this.connector.filters[i].operator,
                            value:value
                        };

                        this.add(newItem);
                    }

                    break;
                }
            }

        };
        
        /**
         * Set time interval
         * 
         * @param interval : array of 2 ISO 8601 date
         */
        this.setTime = function(interval) {
            
            var startDate, completionDate, self = this;
            
            /*
             * Set startDate and completionDate
             */
            startDate = self.connector.startDateAlias;
            completionDate = self.connector.completionDateAlias;
            
            /**
             * startDate is null => remove startDate item from the SearchContext
             */
            if (!interval) {
                self.remove(startDate, null);
                self.remove(completionDate, null);
            }
            else {
                if (startDate) {
                    this.add({
                        id:startDate,
                        title:M.Util._("Date"),
                        value:interval[0]
                    });
                }
                if (completionDate) {
                    this.add({
                        id:completionDate,
                        title:M.Util._("Date"),
                        value:interval[1]
                    });
                }
            }
        };
        
        /**
         * Set searchTerms for full text search
         * 
         * @param {String} searchTerms 
         */
        this.setSearchTerms = function(searchTerms) {
            
            /*
             * Set startDate and completionDate
             */
            var searchKey = this.connector.searchKeyAlias ? this.connector.searchKeyAlias : 'q';
            
            /**
             * startDate is null => remove startDate item from the SearchContext
             */
            if (!searchTerms) {
                this.remove(searchKey, null);
            }
            else {
                this.add({
                    id:searchKey,
                    title:M.Util._("searchTerms"),
                    value:searchTerms
                });
            }
        };
        
        /*
         * Return this object
         */
        return this;
    };
    
})(window.M);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * GeoRSS layer type
 */
(function (M, Map){
    
    Map.layerTypes["Atom"] = {

        /**
         * Layer clusterization is set by default
         */
        clusterized:true,

        /**
         * MANDATORY
         */
        icon:"vector.png",

        /**
         * MANDATORY
         */
        isFile:true,

        /**
         * Set default styleMap
         */
        hasStyleMap:true,

        /**
         * MANDATORY
         */
        selectable:true,

        /**
         * MANDATORY
         * {
         *      type:"Atom",
         *      title:"Toulouse (Points)",
         *      url:"/server/plugins/tripsntracks/getFeatures.php?type=point",
         *      data: // A valid Atom data structure
         *      hidden:false,
         *      clusterized:true,
         *      hasIconAttribute:false // if true assume that features got a 'icon' attribute
         * }
         *
         */
        add: function(layerDescription, options) {

            var newLayer,self = this;
            
            /*
             * Set title
             */
            layerDescription.title = M.Util.getTitle(layerDescription);
            
            /*
             * Cluster strategy
             */
            if (options["_M"].clusterized && !options.hasOwnProperty("strategies")) {
                options.strategies = [new OpenLayers.Strategy.Cluster(new OpenLayers.Strategy.Cluster(Map.clusterOpts))];
            }

            /*
             * Layer creation
             */
            newLayer = new OpenLayers.Layer.Vector(layerDescription.title, options);

            /*
             * If layerDescription.data is set, the Atom stream is directly
             * read from data description
             */
            if (layerDescription.hasOwnProperty("data")) {
                if (!self.load(layerDescription.data, layerDescription, newLayer)) {
                    M.Map.removeLayer(newLayer, false);
                }
            }
            /*
             * Otherwise, read data asynchronously from url
             */
            else {
                
                /*
                 * First set the isLoaded status to false to avoid
                 * annoying popup telling that the layer is added before
                 * the data has been effectively retrieve from server
                 */
                newLayer['_M'].isLoaded = false;
                
                /*
                 * Add a featuresadded event
                 */
                newLayer.events.register("featuresadded", newLayer, function() {
                    
                   /*
                    * Tell mapshup that features were added
                    */
                    Map.events.trigger("layersend", {
                        action:"features",
                        layer:newLayer
                    });
                    
                });
                
                /**
                 * Retrieve FeatureCollection from server
                 */
                $.ajax({
                    url:M.Util.proxify(M.Util.getAbsoluteUrl(layerDescription.url)),
                    layer:newLayer,
                    async:true,
                    success:function(data) {
                        if (!self.load(data, layerDescription, this.layer)) {
                            M.Map.removeLayer(this.layer, false);
                        }
                    }
                });
                
            }
            
            return newLayer;

        },
        
        /*
         * Load Atom data from a stream
         */
        load: function(data, layerDescription, layer) {
            
            var features;
            
            /*
             * Atom feed is an XML feed
             */
            try {
                /*
                 * By default, Atom stream is assume to be in EPSG:4326 projection
                 * unless srs is specified in EPSG:3857 or EPSG:900913
                 */
                if (layerDescription.srs === "EPSG:3857" || layerDescription.srs === "EPSG:900913") {
                    features = new OpenLayers.Format.Atom().read(data);
                }
                else {
                    features = new OpenLayers.Format.Atom({
                        internalProjection:Map.map.getProjectionObject(),
                        externalProjection:Map.pc
                    }).read(data);
                }
                
            }
            catch(e) {
                M.Util.message(layer.name + " : " + M.Util._("Error"), -1);
                return false;
            }
            
            if (features) {
                
                /*
                 * No features then remove layer
                 */
                if (features.length === 0) {
                    M.Util.message(M.Util._(layer.name)+ " : " + M.Util._("No result"));
                    return false;
                }
                else {
                    
                    /*
                     * Tell user that layer is added
                     */
                    M.Util.message(M.Util._("Added")+ " : " + M.Util._(layer.name));

                    /*
                     * Set layer isLoaded status to true
                     */
                    layer['_M'].isLoaded = true;

                    /*
                     * Add features to layer
                     */
                    layer.addFeatures(features);
                    
                    /*
                     * Zoom on layer
                     */
                    Map.Util.zoomOn(layer);

                    /*
                     * Reindex layer
                     */
                    //Map.Util.updateIndex(layer);
                    
                }
            
            }
            
            return true;

        }
        
    }

})(window.M, window.M.Map);
/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * Bing layer type
 */
(function (M, Map){
    
    Map.layerTypes["Bing"] = {
        
        /*
         * This is a raster layer
         */
        isRaster: true,
        
        /**
         * MANDATORY
         *
         * layerDescription = {
         *  type:"Bing",
         *  title:"Bing Aerial",
         *  bingType:"Aerial",
         *  key:your Bing API key
         *
         *  bingType possible values are :
         *   "Road"
         *   "Aerial"
         *   "AerialWithLabels"
         */
        add: function(layerDescription, options) {

            /*
             * Extend options object with Bing properties
             */
            $.extend(options,
            {
                isBaseLayer:true,
                name:M.Util.getTitle(layerDescription),
                key:layerDescription.key,
                type:layerDescription.bingType,
                transitionEffect:'resize'
            }
            );

            var newLayer = new OpenLayers.Layer.Bing(options);
            newLayer.projection = new OpenLayers.Projection("EPSG:3857");
            
            return newLayer;

        },

        /**
         * MANDATORY
         * Compute an unique MID based on layerDescription
         */
        getMID:function(layerDescription) {
            return layerDescription.MID || M.Util.crc32(layerDescription.type + (layerDescription.bingType || ""));
        }
    }
    
})(window.M, window.M.Map);
/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * Generic layer type
 */
(function (M, Map){
    
    Map.layerTypes["Generic"] = {

        /**
         * MANDATORY
         */
        icon:"map.png",

        /**
         * MANDATORY
         *
         * layerDescription = {
         *  type:"Generic",
         *  title:"",
         *  layer:,
         *  icon:
         */
        add: function(layerDescription, options) {
            
            /*
             * Set title
             */
            layerDescription.title = M.Util.getTitle(layerDescription);
            
            if (!layerDescription.layer) {
                return new OpenLayers.Layer.Vector(layerDescription.title, options);
            }
            layerDescription.layer["_M"] = options._M;
            return layerDescription.layer;
        }
    };

})(window.M, window.M.Map);
/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * GeoRSS layer type
 */
(function (M, Map){
    
    Map.layerTypes["GeoJSON"] = {

        /**
         * Layer clusterization is set by default
         */
        clusterized:true,

        /**
         * MANDATORY
         */
        icon:"vector.png",

        /**
         * MANDATORY
         */
        isFile:true,

        /**
         * Set default styleMap
         */
        hasStyleMap:true,

        /**
         * MANDATORY
         */
        selectable:true,

        /**
         * MANDATORY
         * {
         *      type:"GeoJSON",
         *      title:"Toulouse (Points)",
         *      url:"/server/plugins/tripsntracks/getFeatures.php?type=point",
         *      data: // A valid GeoJSON data structure
         *      hidden:false,
         *      clusterized:true,
         *      hasIconAttribute:false // if true assume that features got a 'icon' attribute
         * }
         *
         */
        add: function(layerDescription, options, urlModifier) {

            var newLayer,self = this;
            
            /*
             * Set title
             */
            layerDescription.title = M.Util.getTitle(layerDescription);
            
            /*
             * Cluster strategy
             */
            if (options["_M"].clusterized && !options.hasOwnProperty("strategies")) {
                options.strategies = [new OpenLayers.Strategy.Cluster(Map.clusterOpts)];
            }
            
            /*
             * Layer creation
             */
            newLayer = new OpenLayers.Layer.Vector(layerDescription.title, options);

            /*
             * Add a featuresadded event
             */
            newLayer.events.register("featuresadded", newLayer, function() {

               /*
                * Tell mapshup that features were added
                */
                Map.events.trigger("layersend", {
                    action:"features",
                    layer:newLayer
                });        

            });
            
            /*
             * If layerDescription.data is set, the GeoJSON stream is directly
             * read from data description
             */
            if (layerDescription.hasOwnProperty("data")) {
                newLayer.destroyFeatures();
                if (!self.load({
                    data:layerDescription.data,
                    layerDescription:layerDescription, 
                    layer:newLayer,
                    zoomOnNew:layerDescription.zoomOnNew
                })) {
                   /*
                    * Tell mapshup that layer is loaded
                    */
                    newLayer["_M"].isLoaded = true;

                    /*
                    * Tell mapshup that no features were added
                    */
                    Map.events.trigger("layersend", {
                        action:"features",
                        layer:newLayer
                    });
                    //M.Map.removeLayer(newLayer, false);
                }
            }
            /*
             * Otherwise, read data asynchronously from url
             */
            else if (layerDescription.url) {
                
                /*
                 * First set the isLoaded status to false to avoid
                 * annoying popup telling that the layer is added before
                 * the data has been effectively retrieve from server
                 */
                newLayer['_M'].isLoaded = false;
                
                self.refresh(newLayer, urlModifier);
                
            }
            
            return newLayer;

        },
        
        /*
         * Load GeoJSON data from a stream
         */
        load: function(options) {
            
            var l,p,features;
            
            options = options || {};
            
            /*
             * First check if there is no error
             * Otherwise, display results
             */
            if (!options.data || !options.data.features || options.data.error) {
                M.Util.message(options.layer.name + " : " + (options.data ? options.data.error["message"] : "Error"), -1);
                return null;
            }
            else {
                
                l = options.data.features.length;
                p = options.layer['_M'].pagination || {};
                
                /*
                 * No features
                 */
                if (l === 0) {
                    M.Util.message(M.Util._(options.layer.name)+ " : " + M.Util._("No result"));
                    return null;
                }
                else {
                    
                    /*
                     * Set layer isLoaded status to true
                     */
                    options.layer['_M'].isLoaded = true;
                    
                    /*
                     * Pagination
                     */
                    if (p.numRecordsPerPage && p.nextRecord) {
                        
                       /*
                        * Avoid case where server don't take care of numRecordsPerPage value
                        */
                        if (l > p.numRecordsPerPage.value) {
                            p.numRecordsPerPage.value = l;
                        }
                        
                        /*
                        * Set nextRecord new value
                        */
                        p.nextRecord.value = p.nextRecord.value + l;

                        /*
                         * Update the totalResults value
                         * If data.totalResults is not set then set totalResults is set to null
                         */
                        var src = options.data.hasOwnProperty('properties') ? options.data.properties : options.data;
                        p.totalResults = src.hasOwnProperty("totalResults") &&  src.totalResults ? src.totalResults : null;
                        
                    }
                    
                    /*
                     * By default, GeoJSON stream is assume to be in EPSG:4326 projection
                     * unless srs is specified in EPSG:3857 or EPSG:900913
                     */
                    if (options.layerDescription && (options.layerDescription.srs === "EPSG:3857" || options.layerDescription.srs === "EPSG:900913")) {
                        features = new OpenLayers.Format.GeoJSON().read(options.data);
                    }
                    else {
                        features = new OpenLayers.Format.GeoJSON({
                            internalProjection:Map.map.getProjectionObject(),
                            externalProjection:Map.pc
                        }).read(options.data);
                    }
                    
                    /*
                     * Cluster is a bit special...needs to remove every feature
                     * and then add it again !
                     */
                    if (options.layer['_M'].clusterized) {
                        var allfeatures = Map.Util.getFeatures(options.layer),
                            afl = allfeatures.length;
                        for (var i = 0, l = features.length; i < l; i++) {
                            allfeatures[afl + i] = features[i];
                        }
                        options.layer.destroyFeatures();
                        options.layer.addFeatures(allfeatures);
                    }
                    else {
                        options.layer.addFeatures(features);
                    }
                    
                    /*
                     * Zoom on new added features otherwise zoom on layer
                     */
                    if (options.zoomOnNew) {
                        Map.Util.Feature.zoomOn(features, options.zoomOnNew === 'always' ? false : true);
                    }
                    else {
                        Map.Util.zoomOn(options.layer);
                    }
                    
                }
                
            }
            
            return features;

        },
        
        /*
         * Load next page of features
         */
        next: function(layer) {
            
            var self = this,
            p = layer["_M"].pagination,
            ld = layer["_M"].layerDescription;
            
            /*
             * Paranoid mode
             */
            if (!p) {
                return false;
            }
            
            /*
             * We are already at the last page
             * Do nothing and returns false
             */
            if (p.totalResults && (p.nextRecord.value > p.totalResults)) {
                return false;
            }
            
            /*
            * Retrieve FeatureCollection from server
            */
            M.Util.ajax({
                url:M.Util.proxify(M.Util.paginate(ld.url, p)),
                layer:layer,
                async:true,
                dataType:"json",
                success:function(data) {
                    self.load({
                        data:data,
                        layerDescription:ld, 
                        layer:this.layer
                    });
                }
            },{
                title:M.Util._("Retrieve features"),
                cancel:true 
            });

            return true;
           
        },
     
        /*
         * Refresh layer
         */
        refresh: function(layer, urlModifier) {
            
            var p, layerDescription, url, self = this;
            
            /*
             * Paranoid mode
             */
            if (!layer || !layer["_M"]) {
                return false;
            }
            
            layerDescription = layer["_M"].layerDescription;
            
            /*
             * Refresh pagination
             */
            p = layer['_M'].pagination || {};
            if (p.nextRecord) {
                p.nextRecord.value = 1;
            }
            
            /*
             * If urlModifier is set, add it before layerDescription.url
             * (See Pleiades.js layerType to understand why)
             */
            url = urlModifier ? M.Util.getAbsoluteUrl(urlModifier + encodeURIComponent(layerDescription.url + M.Util.abc)) : layerDescription.url;

            $.ajax({
                url:M.Util.proxify(M.Util.paginate(url, layer["_M"].pagination)),
                layer:layer,
                async:true,
                dataType:"json",
                success:function(data) {
                    
                    /*
                     * First remove features
                     */
                    this.layer.destroyFeatures();
                    
                    if (!self.load({
                        data:data, 
                        layerDescription:layerDescription, 
                        layer:this.layer,
                        zoomOnNew:layerDescription.zoomOnNew
                    })) {
                            
                        /*
                        * Tell mapshup that layer is loaded
                        */
                        this.layer["_M"].isLoaded = true;
                            
                        /*
                        * Tell mapshup that no features were added
                        */
                        Map.events.trigger("layersend", {
                            action:"features",
                            layer:this.layer
                        });
                    }
                }
            });
            
            return true;
        }
        
    };
    
})(window.M, window.M.Map);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * Google layer type
 */
(function (M,Map){
    
    Map.layerTypes["Google"] = {
        
        /*
         * This is a raster layer
         */
        isRaster: true,
        
        /**
         * MANDATORY
         *
         * layerDescription = {
         *  type:"Google",
         *  title:"",
         *  googleType:"satellite",
         *  numZoomLevels:""
         *
         *  googleType possible values :
         *    "satellite"
         *    "terrain"
         *    "hybrid"
         *    "roadmap"
         *
         */
        add: function(layerDescription, options) {

            /**
             * Check if google.maps library is loaded
             * If not, plugin is discarded
             */
            if (typeof google !== "object" || google.maps === undefined) {
                return null;
            }

            /**
             * TODO : Why ????
             * 
             * Apparently there is a bug with Safari < 6 and Google maps
             * Google layer are not added in this case
             * 
             * Update : Chrome browser on Mac OS X tells also that it is Safari...
             */
            if (/Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent)) {
                
                var idx;
                
                /* Safari < 6 is broken */
                idx = navigator.userAgent.indexOf("Version");
                if (idx !== -1) {
                    if (parseFloat(navigator.userAgent.substring(idx + 8).split(' ')[0]) < 6) {
                        return null;
                    }
                }
            }
            
            /**
             * Set layerDescription googleType to ROADMAP if not
             * already set
             */
            layerDescription.googleType = M.Util.getPropertyValue(layerDescription, "googleType", "roadmap");

            /*
             * Set title
             */
            layerDescription.title = M.Util.getTitle(layerDescription);
            
            /*
             * Extend options object with Google specific properties
             */
            $.extend(options,
            {
                isBaseLayer:true,
                numZoomLevels:M.Util.getPropertyValue(layerDescription, "numZoomLevels", M.Map.map.getNumZoomLevels()),
                transitionEffect:'resize',
                type:layerDescription.googleType
            });
            
            var newLayer = new OpenLayers.Layer.Google(layerDescription.title,options);
            newLayer.projection = new OpenLayers.Projection("EPSG:3857");
            
            return newLayer;
        },

        /**
         * MANDATORY
         * Compute an unique MID based on layerDescription
         */
        getMID:function(layerDescription) {
            return layerDescription.MID || M.Util.crc32(layerDescription.type + (layerDescription.googleType || "roadmap"));
        }
    };
    
})(window.M, window.M.Map);
/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * WMS layer type
 * 
 * @param {Mapshup} M
 * @param {Mapshup.Map} Map
 */
(function(M, Map) {

    Map.layerTypes["WMS"] = {
        
        /*
         * This is a raster layer
         */
        isRaster: true,
        
        /*
         * MANDATORY
         */
        icon: "wms.png",
        /*
         * Layers must always be specified
         */
        mandatories:[
            'layers'
        ],
        /**
         * MANDATORY
         *
         * layerDescription = {
         *       type:'WMS',
         *       title:,
         *       url:,
         *       layers:,
         *       displayType:,
         *       srs:,
         *       version: // optional - set to 1.1.1 if not specified,
         *       time : // optional - only used for WMS Time layer
         *  };
         *
         *  Note: "time" property is an object with two parameters
         *  time: {
         *      default: // mandatory default value
         *      values:[] // optional, array of possible values
         *                  e.g. ["1995-01-01/2011-12-31/PT5M"]
         *                  (see WMS specification OGC 06-042)
         *  }
         *
         * @param {Object} layerDescription
         * @param {Object} options
         */
        add: function(layerDescription, options) {

            var version, projection, srs, bbox;

            /**
             * If url is a GetMap then base url is extracted
             */
            $.extend(layerDescription, this.getLayerDescriptionFromUrl(layerDescription.url));

            /**
             * Repare URL if it is not well formed
             */
            layerDescription.url = M.Util.repareUrl(layerDescription.url);

            /**
             * Set layerDescription.srs if not set
             */
            layerDescription.srs = M.Util.getPropertyValue(layerDescription, "srs", Map.pc.projCode);
            
            /*
             * Set version default to 1.1.1 if not specified
             */
            version = layerDescription.version || "1.1.1";

            /*
             * Check mandatory properties
             * 
             * Note : with the addition of getLayerDescriptionFromUrl(url) mechanism, the "layers" property
             * must also be checked since the mandatories array is empty
             */
            if (!(new Map.LayerDescription(layerDescription, Map)).isValid() || !layerDescription.hasOwnProperty("layers")) {

                /*
                 * Important : non valid layers loaded during
                 * startup are discarded without asking user
                 */
                if (!layerDescription.initial) {
                    this.update(layerDescription);
                }
                return null;
            }

            /**
             * If layer is not a baseLayer with projection object set...
             * 
             * Check the srs of layer : if different from get map srs, then Mapshup creates
             * a mapfile on server side to allow on the fly reprojection of the WMS tiles
             * 
             */
            if (!options.projection || !layerDescription.isBaseLayer) {
                projection = M.Util.getPropertyValue(Map.map, "projection", Map.pc);
                if (layerDescription.srs !== projection.projCode) {
                    OpenLayers.Request.GET({
                        url: M.Util.getAbsoluteUrl(M.Config["general"].reprojectionServiceUrl) + M.Util.abc + "&url=" + encodeURIComponent(layerDescription.url) + "&layers=" + encodeURIComponent(layerDescription.layers) + "&srs=" + layerDescription.srs,
                        callback: function(request) {
                            var json = (new OpenLayers.Format.JSON()).read(request.responseText);

                            // Add a new property "projectedUrl" that should be used in place of original url
                            if (json.success) {
                                layerDescription.projectedUrl = json.url;
                                layerDescription.srs = Map.map.getProjectionObject().projCode;
                                Map.addLayer(layerDescription);
                            }
                            else {
                                M.Util.message(M.Util._("Error : cannot reproject this layer"));
                            }
                        }
                    });
                    return null;
                }
            }
            
            /**
             * Input "options" modification
             * If no BBOX is given, default is set to -170,-80,170,80
             */
            bbox = layerDescription.bbox || {
                bounds: "-170,-80,170,80",
                srs: "EPSG:4326"
            };

            $.extend(options["_M"],
                    {
                        /* A WMS cannot be "selectable" */
                        selectable: false,
                        bounds: Map.Util.getProjectedBounds(bbox),
                        allowChangeOpacity: true,
                        /* A WMS should have a GetLegendGraphic function to retrieve a Legend */
                        legend: layerDescription.url + "service=WMS&version=" + version + "&format=image/png&request=GetLegendGraphic&layer=" + layerDescription.layers,
                        icon: this.getPreview(layerDescription)
                    });

            /*
             * Extend options object with WMS specific properties
             */
            $.extend(options,
                    {
                        buffer: 0,
                        wrapDateLine: true,
                        /*transitionEffect:'resize',*/
                        /* WMS can be set as background (isBaseLayer:true) or as overlay */
                        isBaseLayer: M.Util.getPropertyValue(layerDescription, "isBaseLayer", false),
                        attribution: layerDescription.attribution || null
                    }
            );

            /*
             * Time component
             */
            if (layerDescription.time && layerDescription.time.hasOwnProperty("default")) {
                options.time = layerDescription.time["default"];
            }

            /*
             * Set title
             */
            layerDescription.title = M.Util.getTitle(layerDescription);

            /*
             * Layer creation
             * !! If "projectedUrl" is defined, then use it instead
             * of original url
             */
            var newLayer = new OpenLayers.Layer.WMS(layerDescription.title, M.Util.getPropertyValue(layerDescription, "projectedUrl", layerDescription.url), {
                layers: layerDescription.layers,
                format: "image/png",
                transitionEffect: "resize",
                transparent: 'true',
                SLD: layerDescription.SLD,
                version: version
            }, options);

            /*
             * Add a setTime function
             */
            if (layerDescription.hasOwnProperty("time")) {

                newLayer["_M"].setTime = function(interval) {

                    /*
                     * Currently only the first value of the interval is 
                     * sent to the WMS server
                     */
                    var time, self = this;

                    if ($.isArray(interval)) {
                        time = interval[0] + (interval[1] !== '' ? '/' + interval[1] : '');
                    }
                    else {
                        time = '';
                    }
                    if (self.layerDescription.time) {
                        self.layerDescription.time = self.layerDescription.time || {};
                        self.layerDescription.time["value"] = time;
                        newLayer.mergeNewParams({
                            'time': time
                        });
                    }

                };

            }

            return newLayer;

        },
        /*
         * Return a layerDescription from a WMS GetMap url i.e. 
         *  {
         *      url: base WMS url endpoint (i.e. without request=GetMap and without GetMap parameters)
         *      version: WMS version extracted from WMS GetMap url
         *      srs: WMS srs extracted from WMS GetMap url
         *      bbox : WMS bbox extracted from WMS GetMap url
         *      layers: WMS layers extracted from WMS GetMap url
         *      preview: GetMap url
         *  }
         *  
         *  If input url is not a GetMap url, then url is returned as is :
         *  {
         *      url: input url
         *  }
         *  
         * @param {String} url
         */
        getLayerDescriptionFromUrl: function(url) {

            /*
             * Default - returns url within an object
             */
            var o = {
                url: url
            };

            if (!url) {
                return o;
            }

            var kvps = M.Util.extractKVP(url, true);

            /*
             * If url is not a GetMap request then returns input url
             * within object
             */
            if (!kvps["request"] || kvps["request"].toLowerCase() !== "getmap") {
                return o;
            }

            /*
             * Extract interesting parts from WMS GetMap url i.e.
             * LAYERS, VERSION, SRS and BBOX
             * 
             * Complete baseUrl with non GetMap parameters i.e.
             * constructs baseUrl from baseUrl plus all kvp url parameters
             * minus the specific parameters
             * 
             *      LAYERS=
             *      FORMAT=
             *      TRANSITIONEFFECT=
             *      TRANSPARENT=
             *      VERSION=
             *      REQUEST=
             *      STYLES=
             *      SRS=
             *      BBOX=
             *      WIDTH=
             *      HEIGHT=
             */
            $.extend(o, {
                url: M.Util.extractBaseUrl(url, ['layers', 'format', 'transparent', 'transitioneffect', 'styles', 'version', 'request', 'styles', 'srs', 'crs', 'bbox', 'width', 'height']),
                preview: M.Util.extractBaseUrl(url, ['width', 'height']) + 'width=125&height=125',
                layers: kvps["layers"],
                version: kvps["version"],
                bbox: {
                    bounds: kvps["bbox"],
                    srs: kvps["srs"],
                    crs: kvps["crs"]
                },
                srs: kvps["srs"] || kvps["crs"]
            });

            return o;

        },
        /*
         * Launch an ajax call to WMS getCapabilities service
         * based on input layerDescription
         * On success, "callback" function is called with an array
         * of layerDescription object as input parameter
         * 
         * @param layerDescription: layerDescription object of a WMS server
         * @param callback : function to be called on success with an array of layerDescription
         *                   as input parameter (e.g. plugins["AddLayer"].displayLayersInfo(a))
         */
        update: function(layerDescription, callback) {

            var i, l, availableLayer, predefined,
                    self = Map.layerTypes["WMS"],
                    doCall = !callback || !$.isFunction(callback) ? false : true;

            /*
             * First check if one of the predefined layers with the same url
             * already got a capabilities
             */
            Map.predefined.items["WMS"] = Map.predefined.items["WMS"] || [];
            predefined = Map.predefined.items["WMS"];
            for (i = 0, l = predefined.length; i < l; i++) {

                availableLayer = predefined[i];

                /*
                 * This layer is one of the available layers
                 */
                if (availableLayer.url === layerDescription.url) {

                    /*
                     * The capabilities is already defined
                     */
                    if (availableLayer.capabilities !== undefined) {
                        layerDescription.capabilities = availableLayer.capabilities;
                        if (doCall) {
                            callback(self.getLayerDescriptions(layerDescription));
                        }
                        return true;
                    }
                }
            }
            /*
             * By default call WMS with version set to 1.1.1
             */
            M.Util.ajax({
                url: M.Util.proxify(M.Util.repareUrl(layerDescription.url + "request=GetCapabilities&service=WMS&version=1.1.1"), "XML"),
                async: true,
                success: function(data, textStatus, XMLHttpRequest) {

                    /*
                     * Append capabilities to layerDescription
                     */
                    layerDescription.capabilities = M.Util.getCapabilities(XMLHttpRequest, new OpenLayers.Format.WMSCapabilities());

                    /*
                     * Set the layerDescription title if not already set
                     */
                    if (!layerDescription.title) {
                        layerDescription.title = layerDescription.capabilities.service ? layerDescription.capabilities.service["title"] : M.Util.getTitle(layerDescription);
                    }

                    /*
                     * Add this layerDescription to the list of available layers,
                     * or update this list if it is already defined
                     */
                    var i, l, update = false;
                    for (i = 0, l = predefined.length; i < l; i++) {

                        availableLayer = predefined[i];

                        /*
                         * This layer is one of the available layers
                         */
                        if (availableLayer.url === layerDescription.url) {

                            /*
                             * => update capabilities
                             */
                            if (availableLayer.layers === undefined) {
                                availableLayer.capabilities = layerDescription.capabilities;
                                update = true;
                                break;
                            }

                        }
                    }

                    /*
                     * No update => insert
                     */
                    if (!update) {
                        Map.predefined.add({
                            type: "WMS",
                            title: layerDescription.title,
                            url: layerDescription.url,
                            capabilities: layerDescription.capabilities
                        });
                    }

                    if (doCall) {
                        callback(self.getLayerDescriptions(layerDescription));
                    }
                },
                error: function(e) {
                    M.Util.message(M.Util._("Error reading Capabilities file"));
                }
            }, {
                title: M.Util._("WMS") + " : " + M.Util._("Get capabilities"),
                cancel: true
            });


            return true;
        },
        /**
         * Return an array of layerDescription derived from capabilities information
         * 
         * @param {Object} layerDescription
         * 
         */
        getLayerDescriptions: function(layerDescription) {

            /*
             * Default is an empty array
             */
            var a = [],
                    capabilities;

            /*
             * Paranoid mode
             */
            if (!layerDescription || typeof layerDescription !== "object") {
                return a;
            }

            /*
             * Empty capability => return empty array
             */
            capabilities = layerDescription.capabilities;

            /*
             * Error
             */
            if (!capabilities || !capabilities.capability) {
                a = {
                    type: "error",
                    error: {
                        message: "Error performing GetCapabilities operation"
                    }
                };
                return a;
            }
            else {

                /*
                 * Get the getmap url
                 */
                var url = M.Util.repareUrl(layerDescription.url),
                        d,
                        layer,
                        ptitle = (capabilities.capability.nestedLayers && capabilities.capability.nestedLayers[0]) ? capabilities.capability.nestedLayers[0]["title"] : null;

                /*
                 * Parse layers list
                 */
                for (var i = 0, l = capabilities.capability.layers.length; i < l; i++) {

                    layer = capabilities.capability.layers[i];

                    /*
                     * Initialize new object
                     */
                    d = {
                        type: "WMS",
                        title: layer["title"],
                        ptitle: ptitle,
                        url: url,
                        layers: layer["name"],
                        preview: this.getPreview({
                            url: url,
                            version: capabilities.version,
                            bbox: {
                                bounds: layer.llbbox,
                                srs: "EPSG:4326"
                            },
                            layers: layer["name"]
                        }),
                        version: capabilities.version
                    };

                    /*
                     * In OpenLayers bbox in EPSG:4326 is lonmin,latmin,lonmax,latmax
                     *
                     * The bbox of this layer if retrieved from the capabilities
                     * or set to the whole earth if not found
                     */
                    d.bbox = {
                        bounds: "-170,-80,170,80",
                        srs: "EPSG:4326"
                    };
                    if (layer.llbbox && layer.llbbox.length === 4) {
                        d.bbox.bounds = layer.llbbox[0] + ',' + layer.llbbox[1] + ',' + layer.llbbox[2] + ',' + layer.llbbox[3];
                    }

                    /*
                     * Is layer queryable ?
                     */
                    d.queryable = layer["queryable"] ? true : false;

                    /*
                     * Is layer a WMS Time layer
                     */
                    if (layer.dimensions && layer.dimensions.time) {
                        d.time = {
                            "default": layer.dimensions.time["default"],
                            values: layer.dimensions.time["values"] || []
                        };
                    }

                    /*
                     * Get the "best" srs, i.e. Map.map.getProjection()
                     * If this srs does not exists, put a EPSG:4326 srs instead
                     * M server will reproject the layer on the fly
                     */
                    for (var srs in layer.srs) {
                        if (srs === Map.map.getProjectionObject().projCode) {
                            break;
                        }
                    }
                    if (srs !== Map.map.getProjectionObject().projCode) {
                        srs = Map.pc;
                    }
                    d.srs = srs;

                    /*
                     * Add layerDescription to array
                     */
                    a.push(d);

                }
            }

            return a;
        },
        /**
         * Return a wms preview from layer
         * 
         * @param {Object} layerDescription
         */
        getPreview: function(layerDescription) {

            var url, version, bounds;

            layerDescription = layerDescription || {};

            /*
             * The easy part !
             */
            if (layerDescription.preview) {
                return layerDescription.preview;
            }

            /*
             * Default version is 1.1.1
             */
            version = layerDescription.version || "1.1.1";

            /*
             * Set default BBOX to the whole world
             */
            bounds = M.Map.Util.getGeoBounds(layerDescription.bbox);
            if (!bounds) {
                bounds = new OpenLayers.Bounds(-180, -90, 180, 90);
            }

            /*
             * Set default url to a 150x75 pixels thumbnail
             */
            url = M.Util.repareUrl(layerDescription.url) + "WIDTH=150&HEIGHT=75&STYLES=&FORMAT=image/png&TRANSPARENT=false&SERVICE=WMS&REQUEST=GetMap&VERSION=" + version;

            /*
             * WMS 1.3.0 => srs is now crs and axis order is switched for
             * EPSG:4326
             *
             */
            if (version === "1.3.0") {
                url += "&CRS=EPSG:4326&BBOX=" + bounds.bottom + ',' + bounds.left + ',' + bounds.top + ',' + bounds.right;
            }
            else {
                url += "&SRS=EPSG:4326&BBOX=" + bounds.left + ',' + bounds.bottom + ',' + bounds.right + ',' + bounds.top;
            }

            return url + '&LAYERS=' + layerDescription.layers;
        },
        /**
         * Launch a getFeatureInfo on all queryables WMS layers
         *
         * @param {OpenLayers.LonLat} lonLat : clicked point in map coordinates
         * @param {Object} options :
         *                  {
         *                      responseFormat : // format to get the data back - default is 'text/plain'
         *                      callback : // callback function to send result back
         *                  }
         */
        getFeatureInfo: function(lonLat, options) {

            if (!lonLat) {
                return [];
            }
            
            options = options || {};
            
            var j, layer, layerDescription, url, result, results = [], xy = Map.map.getPixelFromLonLat(lonLat);

            /*
             * Roll over WMS layers
             */
            for (j = Map.map.layers.length; j--; ) {
                
                layer = Map.map.layers[j];
                
                if (layer["_M"]) {
                    layerDescription = layer["_M"].layerDescription;
                    if (layerDescription && layerDescription.type === "WMS" && layerDescription.queryable) {

                        /*
                         * Set default version if not specified
                         */
                        layerDescription.version = layerDescription.version || "1.1.1";

                        /**
                         * Prepare the getFeatureInfo request
                         */
                        url = M.Util.repareUrl(layerDescription.url);
                        url += "SERVICE=WMS";
                        url += "&VERSION=" + layerDescription.version;
                        url += "&REQUEST=GetFeatureInfo";
                        url += "&EXCEPTIONS=application/vnd.ogc.se_xml";
                        url += "&X=" + xy.x;
                        url += "&Y=" + xy.y;
                        url += "&INFO_FORMAT=" + (options.responseFormat ? options.responseFormat : "text/plain"),
                        url += "&QUERY_LAYERS=" + layerDescription.layers;
                        url += "&LAYERS=" + layerDescription.layers;
                        url += "&WIDTH=" + Map.map.size.w;
                        url += "&HEIGHT=" + Map.map.size.h;
                        //url += "&FEATURE_COUNT=1";

                        /*
                         * If projectedUrl is defined, then it means that the original WMS server
                         * is not in map.getProjectionObject() and the WMS server should be called
                         * with epsg:4326 projection
                         */
                        if (layerDescription.projectedUrl) {
                            var extent = Map.map.p2d(map.getExtent().clone());
                            url += "&BBOX=" + extent.toBBOX();
                            url += "&SRS=" + Map.pc.projCode;
                        }
                        else {
                            url += "&BBOX=" + Map.map.getExtent().toBBOX();
                            url += "&SRS=" + layerDescription.srs;
                        }

                        /**
                         * Initialize result container
                         */
                        result = {
                            identifier:layerDescription.layers,
                            name:layer.name
                        };
                        results.push(result);
                        
                        (function(result, url) {
                            $.ajax({
                                url: M.Util.proxify(url),
                                async: true,
                                dataType: "text",
                                success: function(data) {
                                    if (typeof options.callback === 'function') {
                                        options.callback(result.identifier, data);
                                    }
                                },
                                error: function(e) {
                                    if (typeof options.callback === 'function') {
                                        options.callback(result.identifier, null);
                                    }
                                }
                            });
                        })(result, url);
                    }
                }
            }

            return results;

        },
        /**
         * MANDATORY
         * Compute an unique MID based on layerDescription
         * 
         * @param {Object} layerDescription
         */
        getMID: function(layerDescription) {
            return layerDescription.MID || M.Util.crc32(layerDescription.type + (M.Util.repareUrl(layerDescription.url) || "") + (layerDescription.layers || ""));
        }
    };
})(window.M, window.M.Map);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * WMTS layer type
 */
(function (M,Map){
    
    Map.layerTypes["WMTS"] = {
        
        /*
         * This is a raster layer
         */
        isRaster: true,
        
        /**
         * MANDATORY
         */
        icon:"wms.png",

        /**
         * Mandatory properties for
         * valid layerDescription
         */
        mandatories:[
            "layer",
            "matrixSet"
        ],

        /**
         * MANDATORY
         *
         * layerDescription = {
         *       type:'WMTS',
         *       title:,
         *       url:,
         *       layer:,
         *       matrixSet:,
         *       format: // default is image/png
         *       matrixIds: // [level of TileMatrix in GetCapabilities]
         *       prefixMatrix : // prefix to generate matrixIds array (i.e. "EPSG:4326:" will generate
         *                         ["EPSG:4326:0", "EPSG:4326:1", etc.]
         *       matrixLength: // size of the matrix (22 by default)
         *       time : // optional - only used for WMS Time layer
         *  };
         *  
         *  Note: "time" property is an object with two parameters
         *  time: {
         *      default: // mandatory default value
         *      values:[] // optional, array of possible values
         *                  e.g. ["1995-01-01/2011-12-31/PT5M"]
         *                  (see WMS specification OGC 06-042)
         *  }
         *
         */
        add: function(layerDescription, options) {

            var i, l = layerDescription.matrixLength || 22, matrixIds = new Array(l);
            
            /*
             * Repare URL if it is not well formed
             */
            layerDescription.url = M.Util.repareUrl(layerDescription.url);
            
            /*
             * Generate tileMatrix
             */
            if (!layerDescription.matrixIds) {
                for (i = 0; i <= l; ++i) {
                    matrixIds[i] = (layerDescription.prefixMatrix ? layerDescription.prefixMatrix : "") + i;
                }
            }
            
            $.extend(options, {
                name:layerDescription.title,
                url:layerDescription.url,
                layer:layerDescription.layer,
                matrixSet:layerDescription.matrixSet,
                matrixIds:layerDescription.matrixIds ? layerDescription.matrixIds : matrixIds,
                maxZoomLevel:l,
                format:layerDescription.format || "image/png",
                style: layerDescription.style || "normal",
                /*transitionEffect: "resize",*/
                version:"1.0.0",
                wrapDateLine:true,
                /* WMTS can be set as background (isBaseLayer:true) or as overlay */
                isBaseLayer:M.Util.getPropertyValue(layerDescription, "isBaseLayer", false),
                attribution:layerDescription.attribution || null
            });
            
            /*
             * Time component
             */
            if (layerDescription.time && layerDescription.time.hasOwnProperty("default")) {
                options.time = layerDescription.time["default"];
            }
            
            var newLayer =  new OpenLayers.Layer.WMTS(options);
            
            /*
             * Add a setTime function
             */
            if (layerDescription.hasOwnProperty("time")) {

                newLayer["_M"].setTime = function(interval) {

                    /*
                     * Currently only the first value of the interval is 
                     * sent to the WMS server
                     */
                    var time, self = this;

                    if ($.isArray(interval)) {
                        time = interval[0];
                    }
                    else {
                        time = '';
                    }
                    
                    /*
                     * Remove hours
                     */
                    var arr = time.split('T');
                    time = arr[0];
                    
                    if (self.layerDescription.time) {
                        self.layerDescription.time = self.layerDescription.time || {};
                        self.layerDescription.time["value"] = time;
                        newLayer.mergeNewParams({
                            'TIME': time
                        });
                    }

                };

            }
            
            return newLayer;
            
            /*
            $.ajax({
                url:M.Util.proxify(layerDescription.url),
                async:true,
                dataType:"xml",
                success:function(data, textStatus, XMLHttpRequest) {
                console.log(M.Util.getCapabilities(XMLHttpRequest, new OpenLayers.Format.WMTSCapabilities()));
                }
            });
            */

        },
        
        /**
         * MANDATORY
         * Compute an unique MID based on layerDescription
         */
        getMID:function(layerDescription) {
            return layerDescription.MID || M.Util.crc32(layerDescription.type + (M.Util.repareUrl(layerDescription.url) || "") + (layerDescription.layer || "") + (layerDescription.matrixSet || ""));
        }
        
    }
    
})(window.M, window.M.Map);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/**
 * XYZ layer type
 */
(function (M,Map){
    
    Map.layerTypes["XYZ"] = {
        
        /*
         * This is a raster layer
         */
        isRaster: true,
        
        /**
         * MANDATORY
         *
         * layerDescription = {
         *       type:'XYZ',
         *       title:,
         *       url:
         *  };
         */
        add: function(layerDescription, options) {

            /*
             * Extend options object
             */
            $.extend(options,
            {
                numZoomLevels:M.Util.getPropertyValue(layerDescription, "numZoomLevels", M.Map.map.getNumZoomLevels()),
                sphericalMercator: true,
                wrapDateLine:true,
                transitionEffect:'resize',
                buffer:0,
                /* XYZ can be set as background (isBaseLayer:true) or as overlay */
                isBaseLayer:M.Util.getPropertyValue(layerDescription, "isBaseLayer", true)
            }
            );
                
            /*
             * selectable cannot be overriden
             */
            options["_M"].selectable = false;
            
            /*
             * Transparency selection cannot be overriden
             */
            options["_M"].allowChangeOpacity = true;

            /*
             * Layer creation
             */
            var newLayer = new OpenLayers.Layer.XYZ(layerDescription.title, layerDescription.url, options);
            newLayer.projection = new OpenLayers.Projection("EPSG:3857");
            
            return newLayer;
        },

        /**
         * MANDATORY
         * Compute an unique MID based on layerDescription
         */
        getMID:function(layerDescription) {
            if (layerDescription.MID) {
                return layerDescription.MID;
            } 
            var str = layerDescription.url;
            if (typeof layerDescription.url === "object") {
                str = layerDescription.url.toString();
            }
            return M.Util.crc32(layerDescription.type + (str || ""));
        }
    }
})(window.M, window.M.Map);/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */
/*********************************************
 *
 * PLUGIN: Distance
 *
 * A valid plugin is an fn object containing
 * at least the init() mandatory function.
 *
 *********************************************/
(function(M) {
    
    M.Plugins.Distance = function() {
        
        /*
         * Only one Distance object instance is created
         */
        if (M.Plugins.Distance._o) {
            return M.Plugins.Distance._o;
        }
        
        /*
         * Result object containing title and elevation samples
         * {
         *      id:
         *      lat:
         *      lng:
         *      elevation:
         * }
         */
        this.result = {
            title:"",
            elevations:[],
            plots:null
        };

        /**
         * Initialize plugin
         */
        this.init = function(options) {

            var mc, self = this;
            
            /**
             * Init options
             */
            self.options = options || {};
            $.extend(self.options,{
                elevationServiceUrl:M.Util.getPropertyValue(options, "elevationServiceUrl", "http://maps.google.com/maps/api/elevation/json?sensor=false&"),
                samples:M.Util.getPropertyValue(options, "samples", 30)
            });
        
            /*
             * Generate a unique id for elevation panel
             */
            self.uid = M.Util.getId();
            
            /*
             * Measure distance control
             */
            mc = new OpenLayers.Control.Measure(
                OpenLayers.Handler.Path, {
                    id:"__CONTROL_MEASURE__",
                    persist: true,
                    handlerOptions: {
                        layerOptions: {
                            styleMap:new OpenLayers.StyleMap({
                                "default": new OpenLayers.Style("default",{
                                    rules:[new OpenLayers.Rule({
                                        symbolizer: {
                                            "Point": {
                                                pointRadius:6,
                                                graphicName:"circle",
                                                fillColor:"gray",
                                                fillOpacity:1,
                                                strokeWidth:1,
                                                strokeOpacity:1,
                                                strokeColor:"white"
                                            },
                                            "Line": {
                                                pointRadius:6,
                                                strokeWidth:3,
                                                strokeOpacity:1,
                                                strokeColor:"#FFFFFF",
                                                strokeDashstyle:"dash"
                                            }
                                        }
                                    })]
                                })
                            })
                        }
                    },
                    eventListeners: {
                        measure: function(e) {
                            var units = e.units,
                            order = e.order,
                            measure = e.measure,
                            out = "";
                            if(order === 1) {
                                out += M.Util._("Distance")+" : " + measure.toFixed(3) + " " + units;
                            } else {
                                out += M.Util._("Area")+" : " + measure.toFixed(3) + " " + units + "<sup>2</sup>";
                            }
                            
                            /*
                             * Store result
                             */
                            self.result.title = out;
                            self.result.elevations = [];
                            self.result.plots = null;
                            
                            /*
                             * Display results
                             */
                            self.display(e.geometry.getVertices());
                            M.Map.resetControl(this);
                        }
                    }
                });

            self.layer = new OpenLayers.Layer.Vector("__LAYER_DISTANCE__",{
                projection:M.Map.pc,
                displayInLayerSwitcher:false,
                styleMap:new OpenLayers.StyleMap({
                    "default": new OpenLayers.Style("default",{
                        rules:[new OpenLayers.Rule({
                            symbolizer: {
                                "Point": {
                                    pointRadius:"${size}",
                                    graphicName:"circle",
                                    fillColor:"${color}",
                                    fillOpacity:1,
                                    strokeWidth:1,
                                    strokeOpacity:1,
                                    strokeColor:"white",
                                    label:"${label}",
                                    fontColor:"white",
                                    fontSize:"12px",
                                    labelXOffset:"10",
                                    labelYOffset:"10",
                                    labelAlign:"rm"
                                }
                            }
                        })]
                    })
                })
            });

            /**
             * Add a distance layer
             */
            M.Map.addLayer({
                type:"Generic",
                title:self.layer.name,
                unremovable:true,
                MLayer:true,
                layer:self.layer
            });

            /*
             * Add control to map
             */
            M.Map.map.addControl(mc);

            /*
             * Add "Measure" item in menu
             */
            if (M.menu) {
                M.menu.add([
                {
                    id:M.Util.getId(),
                    ic:"distance.png",
                    ti:"Measure distance",
                    cb:function() {
                        M.Map.Util.getControlById("__CONTROL_MEASURE__").activate();
                    }
                }
                ]);
            }
            
            /*
             * Distance layer is always on top of other layers
             */
            M.Map.events.register("layersend", self, function(action,layer,scope){
                M.Map.Util.setLayerOnTop(scope.layer);
            });
            
            /*
             * Elevation plot should be redrawn on map size change
             */
            M.Map.events.register("resizeend", self, function(scope){
                scope.refreshElevation();
            });
            
            return this;
        };

        /** Plugin specific */

        /**
         * Display distance information.
         * If options.elevationServiceUrl, distance
         * is displayed along an elevation profile
         */
        this.display = function(vertices) {
            
            /*
             * Elevation is computed only if a service is defined and
             * jQplot is part of mapshup build
             */
            if (this.options.elevationServiceUrl && $.isFunction($.jqplot)) {

                /*
                 * First transform array of vertices into a google elevation path
                 */
                var i,
                l,
                isFirst = true,
                path = "",
                latlonVertice = null,
                lonMin = Number.MAX_VALUE,
                lonMax = Number.MIN_VALUE,
                latMin = Number.MAX_VALUE,
                latMax = Number.MIN_VALUE,
                self = this;

                for(i = 0, l = vertices.length; i < l; i++) {
                    latlonVertice = M.Map.Util.p2d(vertices[i]);
                    if (!isFirst) {
                        path += "|";
                    }
                    isFirst = false;
                    if (latlonVertice.x < lonMin) {
                        lonMin = latlonVertice.x;
                    }
                    if (latlonVertice.x > lonMax) {
                        lonMax = latlonVertice.x;
                    }
                    if (latlonVertice.y < latMin) {
                        latMin = latlonVertice.y;
                    }
                    if (latlonVertice.y > latMax) {
                        latMax = latlonVertice.y;
                    }
                    path += latlonVertice.y + "," + latlonVertice.x;
                }

                /**
                 * Get elevation object
                 */
                M.Util.ajax({
                    url:M.Util.proxify(M.Util.repareUrl(M.Util.getAbsoluteUrl(this.options.elevationServiceUrl))+"path="+path+"&samples="+self.options.samples),
                    async:true,
                    dataType:"json",
                    success:function(data){
                        var i,
                        l,
                        xy,
                        feature,
                        result,
                        layer = self.layer,
                        plots = [];
                        M.Map.Util.setVisibility(layer, true);
                        layer.destroyFeatures();

                        /**
                         * data is valid
                         */
                        if (data &&  data.results.length > 0) {
                            
                            l = data.results.length;
                        
                            for (i = 1; i <= l; i++) {
                                result = data.results[i-1];
                                xy = M.Map.Util.d2p(new OpenLayers.LonLat(parseFloat(result.location.lng),parseFloat(result.location.lat)));
                                
                                /**
                                 * Add feature to "__LAYER_DISTANCE__" layer
                                 *  - first point is green and bigger
                                 *  - last point is red and bigger
                                 *  - each 5th point are alt color
                                 *  - other points are gray
                                 */
                                feature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(xy.lon, xy.lat), {
                                    label:i === 1 || i % 5 === 0 ? i : "",
                                    size:i === 1 || i === self.options.samples ? 6 : 4,
                                    color:(function(point) {
                                        if (point === 1) {
                                            return "green";
                                        }
                                        else if (point === self.options.samples) {
                                            return "red";
                                        }
                                        else if (point % 5 === 0) {
                                            return "alt";
                                        }
                                        return "gray";
                                    })(i)
                                });
                                layer.addFeatures(feature);
                                plots[i-1]=[i,parseFloat(result.elevation)];

                                /*
                                 * Update plugin "elevations" array
                                 */
                                self.result.elevations.push({
                                    id:i,
                                    lat:result.location.lat,
                                    lng:result.location.lng,
                                    elevation:result.elevation
                                });
                               
                            }
                            
                            /*
                             * Set container item within SouthPanel
                             */
                            if (!self.panelItem) {

                                /*
                                 * Add Streetview to South Panel
                                 */
                                self.panelItem = M.southPanel.add({
                                    id:self.uid,
                                    title:"Elevation",
                                    onclose:function() {
                                        
                                        /*
                                         * Clear result
                                         */
                                        self.result.plots = [];

                                        /*
                                         * Hide layer
                                         */
                                        M.Map.Util.setVisibility(self.layer, false);

                                        /*
                                         * Nullify panelItem
                                         */
                                        self.panelItem = null;
                                        
                                    },
                                    onshow:function() {
                                        M.Map.Util.setVisibility(self.layer, true);
                                    }
                                });

                                self.$e = self.panelItem.$content;
                                
                            }
                            
                            self.result.plots = [plots];
                            
                            /*
                             * Indicate that the plot is a fresh one
                             */
                            self.fresh = true;
                          
                            /*
                             * Display result
                             */
                            self.showElevation();
                            
                        }
                        /**
                         * No data...display simple distance
                         */
                        else {
                            M.Util.message(self.result.title);
                        }

                    },
                    /**
                     * Error...display simple distance
                     */
                    error:function(e) {
                        M.Util.message(self.result.title);
                    }
                },{
                    title:M.Util._("Retrieve elevation data..."),
                    cancel:true
                });

            }
            /**
             * Simple distance display
             */
            else {
                M.Util.message(this.result.title);
            }
        };

        /**
         * Show elevation
         */
        this.showElevation = function() {
           
            var self = this;
            
            if (self.fresh) {
                
                /*
                 * Activate panel item
                 */
                M.southPanel.show(self.panelItem);
                
            }
            
            /*
             * This is no more a new plot
             */
            self.fresh = false;
            
            /*
             * Refresh plot
             */
            self.refreshElevation();
            
        };
        
        /*
         * Refresh elevation plots
         */
        this.refreshElevation = function() {
            
            if (!this.result.plots || this.result.plots.length === 0) {
                return;
            }
            
            /*
             * Empty elevation div
             */
            this.$e.empty();
            
            /*
             * Display elevation through jqplot
             */
            $.jqplot(this.$e.attr('id'), this.result.plots, {
                axes:{
                    xaxis:{
                        min:1,
                        max:this.samples,
                        label:this.result.title,
                        tickOptions:{
                            formatString:'%d'
                        }
                    },
                    yaxis:{
                        label:M.Util._("Elevation (m)"),
                        labelRenderer: $.jqplot.CanvasAxisLabelRenderer
                    }
                },
                cursor:{
                    show:true,
                    showTooltip:true,
                    tooltipFormatString:'%d: %.1 m',
                    tooltipLocation:'nw'
                },
                seriesDefaults: {
                    fill:true,
                    fillToZero:true,
                    shadow:false,
                    color:'#000',
                    fillColor:'#000'
                },
                negativeSeriesColors:['#555']
            });
            
        };
        
        /*
         * Set unique instance
         */
        M.Plugins.Distance._o = this;
        
        return this;
        
    };
})(window.M);

/*
 * mapshup - Webmapping made easy
 * http://mapshup.info
 *
 * Copyright Jérôme Gasperi, 2011.12.08
 *
 * jerome[dot]gasperi[at]gmail[dot]com
 *
 * This software is a computer program whose purpose is a webmapping application
 * to display and manipulate geographical data.
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
 */

/**
 * PLUGIN: Navigation
 *
 * Add navigation tools
 * 
 * @param {MapshupObject} M
 */
(function(M) {

    M.Plugins.Navigation = function() {

        /*
         * Only one Navigation object instance is created
         */
        if (M.Plugins.Navigation._o) {
            return M.Plugins.Navigation._o;
        }

        /**
         * Initialize plugin
         * 
         * @param {Object} options
         */
        this.init = function(options) {

            var self = this;

            self.options = options || {};

            /*
             * Set options
             * Default toolbar is North East Vertical
             */
            $.extend(self.options, {
                home: M.Util.getPropertyValue(self.options, "home", {
                    lon: 0,
                    lat: 40,
                    zoom: 2
                }),
                zoomin: M.Util.getPropertyValue(self.options, "zoomin", true),
                zoomout: M.Util.getPropertyValue(self.options, "zoomout", true),
                position: M.Util.getPropertyValue(self.options, "position", 'nw'),
                orientation: M.Util.getPropertyValue(self.options, "orientation", 'v')
            });

            /*
             * Set the toolbar container
             */
            tb = new M.Toolbar({
                position: self.options.position,
                orientation: self.options.orientation
            });

            /*
             * Zoom in button
             */
            if (self.options.zoomin) {
                tb.add({
                    title: "+",
                    tt: "Zoom",
                    onoff: false,
                    onactivate: function(scope, item) {
                        item.activate(false);
                        M.Map.map.setCenter(M.Map.map.getCenter(), M.Map.map.getZoom() + 1);
                    }
                });
            }

            /*
             * Zoom out button
             */
            if (self.options.zoomout) {
                tb.add({
                    title: "-",
                    tt: "Zoom out",
                    onoff: false,
                    onactivate: function(scope, item) {
                        item.activate(false);
                        M.Map.map.setCenter(M.Map.map.getCenter(), Math.max(M.Map.map.getZoom() - 1, M.Map.lowestZoomLevel));
                    }
                });
            }

            /*
             * Home button
             */
            if (self.options.home) {
                tb.add({
                    title: "&#8226;",
                    tt: "Global view",
                    onoff: false,
                    onactivate: function(scope, item) {
                        item.activate(false);
                        M.Map.map.restrictedExtent ? M.Map.map.zoomToExtent(M.Map.map.restrictedExtent) : M.Map.setCenter(M.Map.Util.d2p(new OpenLayers.LonLat(self.options.home.lon, self.options.home.lat)), self.options.home.zoom, true);
                    }
                });
            }

            return this;

        };

        /*
         * Set unique instance
         */
        M.Plugins.Navigation._o = this;

        return this;
    };

})(window.M);