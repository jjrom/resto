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
     * Util
     */
    window.Resto.Util = {
        
        /*
         * Is ajax ready to do another request ?
         */
        ajaxReady: true,
        
        /*
         * infinite scrolling offset
         */
        offset: 0,
        
        /*
         * infinite scrolling limit
         */
        limit: 0,
        
        /*
         * Sequence for unique id
         */
        sequence:1,
        
        /*
         * Translation array
         */
        translation: {},
        
        /**
         * Protect user input from XSS attacks by removing html tags
         * from user input
         * 
         * @param {Object/String} jqueryObj
         */
        sanitizeValue: function(jqueryObj) {
            if (!jqueryObj || !jqueryObj.length) {
                return '';
            }
            return ($.type(jqueryObj) === 'string' ? jqueryObj : jqueryObj.val()).replace( /<.*?>/g, '' );
        },
        
        /**
         * Show mask overlay (during loading)
         */
        showMask: function() {
            $('<div id="resto-mask-overlay"><span class="fa fa-3x fa-refresh fa-spin"></span></div>').appendTo($('body')).css({
                'position': 'fixed',
                'z-index': '100000',
                'top': '0px',
                'left': '0px',
                'background-color': 'rgba(128, 128, 128, 0.7)',
                'color': 'white',
                'text-align': 'center',
                'width': '100%',
                'height': '100%',
                'line-height': $(window).height() + 'px'
            }).show();
        },
        
        /**
         * Clear mask overlay
         */
        hideMask: function() {
            $('#resto-mask-overlay').remove();
        },
        
        /**
         * Replace {a:1}, {a:2}, etc within str by array values
         * 
         * @param {string} str (e.g. "My name is {a:1} {a:2}")
         * @param {array} values (e.g. ['Jérôme', 'Gasperi'])
         * 
         */
        translate: function(str, values) {
            
            if (!this.translation || !this.translation[str]) {
                return str;
            }

            var i, l, out = this.translation[str];

            /*
             * Replace additional arguments
             */
            if (values && out.indexOf('{a:') !== -1) {
                for (i = 0, l = values.length; i < l; i++) {
                    out = out.replace('{a:' + (i + 1) + '}', values[i]);
                }
            }

            return out;
        },
        
        /**
         * Update key/value parameters from url by values
         * 
         * @param {string} url (e.g. 'http://localhost/resto/?format=json)
         * @param {object} params (e.g. {format:'html'})
         * 
         */
        updateUrl: function(url, params) {

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
                if (key) {
                    sourceParams[key] = value ? value : '';
                }
            }

            for (key in params) {
                sourceParams[key] = params[key];
            }

            for (key in sourceParams) {
                newParamsString += key + "=" + sourceParams[key] + "&";
            }

            return sourceBase + "?" + newParamsString;
        },
        
        /**
         * Rewrite URL with new format
         * 
         * @param {string} url (e.g. 'http://localhost/resto/?format=json)
         * @param {string} format (e.g. 'html')
         * 
         */
        updateUrlFormat: function(url, format) {            
           var splitted = url.split("?"), path = splitted[0], params = splitted[1];
           var dotted = path.split(".");
           if (dotted.length > 1) {
               dotted.pop();
               path = dotted.join(".");
           }
           return path + "." + format + "?" + params;
        },
        
        /**
         * Get gravatar icon url
         * 
         * See http://en.gravatar.com
         * 
         * @param emailhash : md5 hash of an email adress
         * @param size : size of the returned icon in pixel
         */
        getGravatar: function(emailhash, size) {
            return 'http://www.gravatar.com/avatar/' + (emailhash ? emailhash : '') + '?d=mm' + (!size || !$.isNumeric(size) ? '' : '&s=' + size);
        },
        
        /**
         * Check if a string is a valid email adress
         * 
         * @param {String} str
         */
        isEmailAdress: function(str) {
            if (!str || str.length === 0) {
                return false;
            }
            var pattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,4}$/;
            return pattern.test(str);
            
        },
        
        /**
         * Align height of a group of elements to the highest element
         * 
         * @param {jQueryObject} groupOfItem
         */
        alignHeight: function(groupOfItem){
            var tallest = 0;
            groupOfItem.each(function() {
                var thisHeight = $(this).height() + 20;
                if(thisHeight > tallest){
                    tallest = thisHeight;
                }
            });
            groupOfItem.css('height', tallest);
        },
        
        /**
         * Return nice date from ISO 8601 
         * 
         * @param {string} iso8601
         */
        niceDate: function(iso8601) {
            if (!iso8601) {
                return '';
            }
            var ymd = iso8601.split('T')[0].split('-');
            return this.translate('_niceDate', [ymd[0], this.translate('_month' + ymd[1]), ymd[2]]);
        },
        
        /**
         * Display a dialog popup
         * (i.e. Foundation modal popup)
         * 
         * @param {String} title
         * @param {String} content
         * 
         */
        dialog: function(title, content) {
            $('#dialog').html('<div class="padded center"><h2>' + title + '</h2><p class="text-dark">' + content + '</p><a class="text-dark close-reveal-modal">&#215;</a></div>').foundation('reveal', 'open');
        },
        
        /**
         * Infinite scolling
         * 
         * @param {String or Object} url
         * @param {String} dataType
         * @param {Json} data
         * @param {method} callback
         * @param {int} limit
         */
        infiniteScroll: function(url, dataType, data, callback, limit){
            
            var self = this, lastScrollTop = 0;
            self.limit = limit;
            
            data = data || {};
            if (typeof(callback) !== "function") {
                return false;
            }
            $(window).scroll(function() {
                var st = $(this).scrollTop();
                if (st > lastScrollTop){
                    if($(window).scrollTop() + $(window).height() > $(document).height() - 100 && self.ajaxReady) {
                        self.ajaxReady = false;
                        self.offset = self.offset + self.limit;
                        data['startIndex'] = self.offset;
                        self.showMask();
                        $.ajax({
                            type: "GET",
                            dataType: dataType,
                            url: url,
                            async: true,
                            data: data
                        }).done(function(data) {
                            callback(data);
                        }).fail(function(jqXHR, textStatus) {
                            self.dialog('Error', textStatus);
                            self.offset = self.offset - self.limit;
                        }).always(function(){
                            self.ajaxReady = true;
                            self.hideMask();
                        });
                    }
                }
                lastScrollTop = st;
             });
             return false;
        },
        
        /**
         * Open a centered browser popup
         * 
         * @param {string} url
         * @param {string} title
         * @param {integer} w
         * @param {integer} h
         */
        popupwindow: function(url, title, w, h) {
            var left = (window.width/2)-(w/2);
            var top = (window.height/2)-(h/2);
            return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
        },
        
        /**
         * Convert associative array to array
         * 
         * @param {Array} arr
         */
        associativeToArray: function(associativeArray) {
            var normalArray = [];
            for (var item in associativeArray){
                normalArray.push(associativeArray[item]); 
            }
            return normalArray;
        },
        
        /**
         * Return true if client device is a touch device.
         * False otherwise
         * 
         * @returns {Boolean}
         */
        isMobile: function() {
            try {
                document.createEvent("TouchEvent");
                return true;     
            } catch (e) {
                return false;
            }
        },
        
        /**
         * (From https://github.com/jjrom/mapshup/blob/master/client/js/mapshup/lib/core/Util.js)
         * 
         * Return a layerDescription from a WMS GetMap url i.e. 
         *  {
         *      url: base WMS url endpoint (i.e. without request=GetMap and without GetMap parameters)
         *      version: WMS version extracted from WMS GetMap url
         *      format:WMS format extracted from WMS GetMap url
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
        parseWMSGetMap: function(url) {

            /*
             * Default - returns url within an object
             */
            var o = {
                url: url
            };

            if (!url) {
                return o;
            }

            var kvps = this.extractKVP(url, true);

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
                url: this.extractBaseUrl(url, ['layers', 'format', 'transparent', 'transitioneffect', 'styles', 'version', 'request', 'styles', 'srs', 'crs', 'bbox', 'width', 'height']),
                preview: this.extractBaseUrl(url, ['width', 'height']) + 'width=125&height=125',
                layers: kvps["layers"],
                version: kvps["version"],
                format: kvps["format"] || "image/jpeg",
                bbox: {
                    bounds: kvps["bbox"],
                    srs: kvps["srs"],
                    crs: kvps["crs"]
                },
                srs: kvps["srs"] || kvps["crs"]
            });

            return o;

        },
        
        /**
         * (From https://github.com/jjrom/mapshup/blob/master/client/js/mapshup/lib/core/Util.js)
         * 
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
         * (From https://github.com/jjrom/mapshup/blob/master/client/js/mapshup/lib/core/Util.js)
         * 
         * Return base url (i.e. url without parameters) from an input url
         * E.g. extractBaseUrl("http://myserver.com/test?foo=bar") will return "http://myserver.com/test?"
         * 
         * @param {String} url
         * @param {Array} arr : if arr is not specified remove all url parameters
         *                      otherwiser only remove parameters set in arr
         */
        extractBaseUrl: function(url, arr) {
            
            var baseUrl;
            
            if (!url) {
                return null;
            }
            
            /*
             * Extract base url i.e. everything befor '?'
             */
            baseUrl = url.match(/.+\?/)[0];
            
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
         * Return a unique id
         */
        getId: function() {
            return "rid"+this.sequence++;
        },
        
        /**
         * Return true if browser window is in fullscreen mode
         */
        isFullScreen: function() {
            if (!window.screenTop && !window.screenY) {
                return true;
            }
            return false;
        }
        
    };
})(window);
