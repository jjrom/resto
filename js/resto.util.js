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

    window.R = window.R || {};

    /**
     * Util
     */
    window.R.util = {
        
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
            
            if (!window.R.translation || !window.R.translation[str]) {
                return str;
            }

            var i, l, out = window.R.translation[str];

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
         * Launch an ajax call
         * This function relies on jquery $.ajax function
         * 
         * @param {Object} obj
         * @param {boolean} showMask
         */
        ajax: function(obj, showMask) {

            var self = this;

            /*
             * Paranoid mode
             */
            if (typeof obj !== "object") {
                return null;
            }

            /*
             * Ask for a Mask
             */
            if (showMask) {
                obj['complete'] = function(c) {
                    self.hideMask();
                };
                self.showMask();
            }

            return $.ajax(obj);

        },
        
        /**
         * Display non intrusive message to user
         * 
         * @param {string} message
         * @param {integer} duration
         */
        message: function(message, duration) {
            var $container = $('body'), $d;
            $container.append('<div class="adminMessage"><div class="content">' + message + '</div></div>');
            $d = $('.adminMessage', $container);
            $d.fadeIn('slow').delay(duration || 2000).fadeOut('slow', function() {
                $d.remove();
            }).css({
                'left': ($container.width() - $d.width()) / 2,
                'top': 60
            });
            return $d;

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
         * Create an alert button with specific message.
         * 
         * @param {jQueryObject} parent
         * @param {String} text
         */
        alert: function(parent, text){
            if(!$('#_alert').length){
                parent.prepend('<a id="_alert" href="#" class="button expand alert hide"></a>');
                
                $("#_alert").on('click', function() {
                    $('#_alert').hide();
                });
            }
            $('#_alert').text(text);
            $('#_alert').show();
        },
        
        /**
         * Inifinte scrolling by calling loadMore
         * 
         * WARNING : ajaxReady has to be global
         * 
         * @param {Object} loadMore
         */
        infiniteScroll: function(loadMore){
            $(window).scroll(function() {
                if($(window).scrollTop() + $(window).height() > $(document).height() - 100 && ajaxReady) {
                    loadMore();
                }
             });
        }
    };
})(window);
