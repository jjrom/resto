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
   
     window.Resto = {  
        
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
         * Next page url for infiniteScroll
         */
        nextPageUrl: null,
        
        /*
         * Features array
         */
        features: {},
        
        /*
         * Initialize RESTo
         * 
         * @param {array} options
         * @param {Object} data
         */
        init: function(options, data) {

            var self = this;
            
            /*
             * Initialize variables
             */
            self.issuer = options.issuer || 'getCollection';
            self.language = options.language || 'en';
            self.restoUrl = options.restoUrl || '';
            self.Util.translation = options.translation || {};
            self.Header.ssoServices = options.ssoServices || {};
            self.Header.userProfile = options.userProfile || {};
            
            /*
             * Input data is a GeoJSON object
             */
            self.data = data;
            
            /*
             * Set header
             */
            self.Header.init();
            
            /*
             * Show active panel and hide others
             */
            $('.resto-panel').each(function() {
                $(this).hasClass('active') ? $(this).show() : $(this).hide();
            });
            
            /*
             * Set trigger for panels
             */
            $('.resto-panel-trigger').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                self.switchTo($(this));
            });
            
            /*
             * Side nav
             */
            $('#off-canvas-toggle').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $('.off-canvas-wrap').foundation('offcanvas', 'toggle', 'move-right');
            }).css({
                'line-height':$('.resto-search-panel').outerHeight() + 'px'
            });
            
            /*
             * Update searchForm input
             */
            $("#resto-searchform").submit(function(e) {
                
                e.preventDefault();
                e.stopPropagation();
                
                /*
                 * Avoid multiple simultaneous ajax calls
                 */
                if (!self.ajaxReady) {
                    return false;
                }
                
                /*
                 * Unselect all
                 */
                self.unselectAll();
                
                /*
                 * Reload page instead of update page
                 * (For home.php and collections.php pages) 
                 */
                if ($(this).attr('changeLocation')) {
                    self.ajaxReady = false;
                    window.Resto.Util.showMask();
                    this.submit();
                    return true;
                }
                
                /*
                 * Bound search to map extent in map view only !
                 */
                window.History.pushState({randomize: window.Math.random()}, null, '?' + $(this).serialize() + (window.Resto.Map.isVisible() ? '&box=' + window.Resto.Map.getBBOX() : ''));
            });
            
            /*
             * Force focus on search input form
             */
            $('#search').focus();
            
            /*
             * init(options) was called by getCollection
             */
            if (self.issuer === 'getCollection') {
                    
                if (self.data) {
                    self.updateFeaturesList(data, {
                        updateMap: false,
                        centerMap: data && data.query,
                        append:false
                    });
                }
                
                /*
                 * Bind history change with update collection action
                 */
                self.onHistoryChange(self.updateFeaturesList);
                
                /*
                 * Infinite scroll
                 */
                var lastScrollTop = 0;
                $(window).scroll(function() {
                    if (!self.nextPageUrl) {
                        return false;
                    }
                    var st = $(this).scrollTop();
                    if (st > lastScrollTop){
                        if($(window).scrollTop() + $(window).height() > $(document).height() - $('.footer').height() - 100 && self.ajaxReady) {
                            self.ajaxReady = false;
                            self.offset = self.offset + self.limit;
                            self.Util.showMask();
                            $.ajax({
                                type: "GET",
                                dataType: 'json',
                                url: self.nextPageUrl,
                                async: true,
                                success: function(data) {
                                    self.Util.hideMask();
                                    self.unselectAll();
                                    self.updateFeaturesList(data, {
                                        updateMap: true,
                                        centerMap: false,
                                        append:true
                                    });
                                    self.ajaxReady = true;
                                },
                                error: function(e) {
                                    self.Util.hideMask();
                                    self.offset = self.offset - self.limit;
                                    self.ajaxReady = true;
                                    alert('error : ' + e['responseJSON']['ErrorMessage']);
                                }
                            });
                        }
                    }
                    lastScrollTop = st;
                 });
                 
            }
            
            self.Util.hideMask();

        },
        
        
        /**
         * Bind history state change
         * 
         * @param {function} callback // callback function to call on state change
         * 
         */
        onHistoryChange: function(callback) {
            
            var self = this;
            
            /*
             * State change - Ajax call to RESTo backend server
             */
            window.History.Adapter.bind(window, 'statechange', function() {

                // Be sure that json is called !
                var state = window.History.getState(), url = self.Util.updateUrlFormat(state.cleanUrl, 'json');

                self.Util.showMask();
                self.ajaxReady = false;
                $.ajax({
                    url: url,
                    async: true,
                    dataType: 'json',
                    success: function(json) {
                        self.ajaxReady = true;
                        self.Util.hideMask();
                        if (typeof callback === 'function') {
                            callback(json, {
                                updateMap:true,
                                centerMap:true
                                //centerMap:(state.data && state.data.centerMap) || (json.query && json.query.hasLocation) ? true : false
                            });
                        }
                    },
                    error: function(e) {
                        self.ajaxReady = true;
                        self.Util.hideMask();
                        self.Util.alert("Connection error");
                    }
                });
            });
            
            /*
             * Anchor change ===> panel switch TODO
             *
            window.History.Adapter.bind(window, 'anchorchange', function() {
                //self.switchTo($('#' + window.History.getHash()));
            });
            */
        },
        
        /**
         * Switch view to input panel triggered by $trigger
         * 
         * @param {jQueryObject} $trigger
         */
        switchTo: function($trigger) {
            
            var $panel = $($trigger.attr('href'));
            
            $('.resto-panel').each(function() {
                $(this).removeClass('active').hide();
            });
            $('.resto-panel-trigger').each(function() {
                $(this).removeClass('active');
            });
            $trigger.addClass('active');
            $panel.addClass('active').show();
            //window.History.pushState({randomize: window.Math.random()}, null, window.History.getState().cleanUrl.split('#')[0] + '#' + $panel.attr('id'));
            
            /*
             * Map special case
             */
            if ($panel.attr('id') === 'panel-map') {
                this.Map.init(this.data);
            }
        },
        
        /**
         * Return textual resolution from value in meters
         * 
         * @param {integer} value
         */
        getResolution: function(value) {

            if (!$.isNumeric(value)) {
                return null;
            }

            if (value <= 2.5) {
                return 'THR';
            }

            if (value > 2.5 && value <= 30) {
                return 'HR';
            }

            if (value > 30 && value <= 500) {
                return 'MR';
            }

            return 'LR';

        },
        
        /**
         * Update facets list
         * 
         * @param {array} query
         * 
         * @returns {undefined}
         */
        updateFacets: function (query) {
            
            var i, typeAndId, key, where = [], when = [], what = [], self = this;
            query = query || {};
            
            /*
             * Update search input form
             */
            if ($('#search').length > 0) {
                $('#search').val(query ? self.Util.sanitizeValue(query.original.searchTerms) : '');
            }
            
            /*
             * Update query analysis result - TODO
             */
            if (query.analyzed) {
                for (key in query.analyzed) {
                    if (query.analyzed[key]) {
                        if (key === 'searchTerms') {
                            for (i = query.analyzed[key].length; i--;) {
                                var name = query.analyzed[key][i]['name'] || query.analyzed[key][i]['id'].split(':')[1];
                                if (query.analyzed[key][i]['type'] === 'continent' || query.analyzed[key][i]['type'] === 'country' || query.analyzed[key][i]['type'] === 'region' || query.analyzed[key][i]['type'] === 'state' || query.analyzed[key][i]['type'] === 'city') {
                                    where.push('<a href="#" class="resto-collection-info-trigger">' + name + '</a>');
                                }
                                else if (query.analyzed[key][i]['type'] === 'month') {
                                    when.push('<a href="#">' + name + '</a>');
                                }
                            }
                        }
                    }
                }
            }
            
            $('.facets_where').html(where.join('<br/>'));
            $('.facets_when').html(when.join('<br/>'));
            $('.facets_what').html(what.join('<br/>'));
           
        },
        
        /**
         * Update features list
         * 
         * @param {array} json
         * @param {boolean} options 
         *          {
         *              append: // true to append input features to existing features
         *              updateMap: // true to update map content
         *              centerMap: // true to center map on content
         *          }
         * 
         */
        updateFeaturesList: function(json, options) {

            var p, self = window.Resto;

            json = json || {};
            p = json.properties || {};
            options = options || {};

            /*
             * Update facets
             */
            self.updateFacets(p.query);
            
            /*
             * Update next page url (for infinite scroll)
             */
            self.nextPageUrl = null;
            if (p.links) {
                if ($.isArray(p.links)) {
                    for (var i = p.links.length; i--;) {
                        if (p.links[i]['rel'] === 'next') {
                            self.nextPageUrl = self.Util.updateUrlFormat(p.links[i]['href'], 'json');
                        }
                    }
                }
            } 
            /*
             * Update result
             */
            var $container = $('.resto-features-container');
            if (!options.append) {
                $container = $container.empty();
                self.features = {};
            }
            self.updateGetCollectionResultEntries(json, $container);

            /*
             * Update map view
             */
            if (options.updateMap) {
                window.Resto.Map.updateLayer(json, {
                    'centerMap':options.centerMap,
                    'append':options.append
                });
            }
            
            /*
             * Click on ajaxified element call href url through Ajax
             */
            $('.resto-ajaxified').each(function() {
                $(this).click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.History.pushState({
                        randomize: window.Math.random(),
                        centerMap: false
                    }, null, self.Util.updateUrlFormat($(this).attr('href'), 'html'));
                    $('html, body').scrollTop(0);
                    return false;
                });
            });
            
        },

        /**
         * Update GetCollection result entries after a search
         * 
         * @param {array} json
         */
        updateGetCollectionResultEntries: function(json, $container) {

            var i, ii, j, k, image, feature, $div, bottomInfos, topInfos, self = this;

            json = json || {};
            
            /*
             * Iterate on features and update result container
             */
            for (i = 0, ii = json.features.length; i < ii; i++) {

                bottomInfos = [];
                topInfos = [];
                feature = json.features[i];
                
                /*
                 * Update object features array 
                 */
                self.features[feature.id] = feature;
                
                /*
                 * Quicklook
                 */
                image = feature.properties['quicklook'] || feature.properties['thumbnail'] || self.restoUrl + '/css/default/img/noimage.png';

                /*
                 * Display structure
                 *  
                 *  <li>
                 *      <div id="...">
                 *          <div class="streched">
                 *              <div class="feature-info-top"></div>
                 *              <div class="feature-info-bottom"></div>
                 *          </div>
                 *      </div>
                 *  </li>
                 * 
                 */
                $container.append('<li style="position:relative;padding:0px;"><div id="' + feature.id + '" class="resto-feature"><div class="bg-alpha-dark-hover streched"><div class="padded pin-top feature-info-top"></div><div class="padded pin-bottom feature-info-bottom link-light"></div><div class="padded pin-top pin-right feature-info-right"></div></div></div></li>');
                $div = $('#' + feature.id)
                        .css({
                            'background': "url('" + image + "') no-repeat",
                            '-webkit-background-size': 'cover',
                            '-moz-background-size': 'cover',
                            '-o-background-size': 'cover',
                            'background-size': 'cover',
                            'height': '350px',
                            'box-sizing': 'border-box',
                            'padding': '0px',
                            'cursor': 'pointer'
                        }).click(function (e) {
                            $(this).hasClass('selected') ? self.unselectAll() : self.selectFeature($(this).attr('id'), false);
                        });
                
                /*
                 * Feature infos (bottom)
                 */
                var keyword, typeAndId;
                if (feature.properties.keywords) {
                    for (j = feature.properties.keywords.length; j--;) {
                        keyword = feature.properties.keywords[j];
                        typeAndId = keyword.id.split(':');
                        if (typeAndId[0] === 'landuse') {
                            bottomInfos.push('<a href="' + self.Util.updateUrlFormat(keyword['href'], 'html') + '" class="landuse resto-ajaxified resto-keyword' + (typeAndId[0] ? ' resto-keyword-' + typeAndId[0].replace(' ', '') : '') + '" title="' + self.Util.translate('_thisResourceContainsLanduse', [Math.round(keyword.value), keyword.name]) + '"><img src="' + self.restoUrl + 'themes/default/img/landuse/' + typeAndId[1] + '.png"/></a> ');
                        }
                    }
                }
                $('.feature-info-bottom', $div).html(bottomInfos.join(''));
                
                /*
                 * Feature infos (top)
                 */
                topInfos.push('<h3 class="small text-light">' + self.Util.niceDate(feature.properties.startDate) + '</h3>');
                
                if (feature.properties.keywords) {
                    var hash, typeAndValue, best = -1, state = -1, region = -1, country = -1, continent = -1;
                    for (j = feature.properties.keywords.length; j--;) {
                        typeAndValue = feature.properties.keywords[j].id.split(':');
                        switch (typeAndValue[0]) {
                            case 'state':
                                state = j;
                                break;
                            case 'region':
                                if (feature.properties.keywords[j].id !== 'region:_all') {
                                    region = j;
                                }
                                break;
                            case 'country':
                                country = j;
                                break;
                            case 'continent':
                                continent = j;
                                break;
                        }
                    }
                    if (state !== -1) {
                        best = state;
                    }
                    else if (region !== -1) {
                        best = region;
                    }
                    else if (country !== -1) {
                        best = country;
                    }
                    else if (continent !== -1) {
                        best = continent;
                    }
                    if (best !== -1) {
                        hash = feature.properties.keywords[best]['hash'];
                        topInfos.push('<h2 class="small upper"><a href="' + feature.properties.keywords[best]['href'] + '" class="resto-ajaxified">' + feature.properties.keywords[best]['name'] + '</a></h2>');
                        var newHash, parentHash = feature.properties.keywords[best]['parentHash'];
                        while (parentHash) {
                            newHash = null;
                            for (k = feature.properties.keywords.length; k--;) {
                                if (feature.properties.keywords[k].hasOwnProperty('hash') && feature.properties.keywords[k]['hash'] === parentHash) {
                                    if (feature.properties.keywords[k]['name'] !== 'region:_all') {
                                        topInfos.push('<h4 class="small"><a href="' + feature.properties.keywords[k]['href'] + '" class="resto-ajaxified text-light">' + feature.properties.keywords[k]['name'] + '</a></h4>');
                                    }
                                    newHash = feature.properties.keywords[k]['parentHash'];
                                    hash = feature.properties.keywords[k]['hash'];
                                    break;
                                }
                            }
                            parentHash = newHash;
                        }
                    }
                    $('.feature-info-right', $div).html('<img src="' + self.restoUrl + 'themes/default/img/world/' + hash + '.png"/>');
                }
                $('.feature-info-top', $div).html(topInfos.join(''));
                
            }

        },
        
        /**
         * Select feature id
         * 
         * @param {string} id
         */
        selectFeature: function(id, scroll) {
            
            var $id = $('#' + id);
            
            this.unselectAll();
            
            /*
             * Switch to list view
             */
            this.switchTo($('#resto-panel-trigger-list'));
            $('.resto-feature').each(function () {
                $(this).addClass('darker');
            });
            $id.addClass('selected').removeClass('darker').children().first().removeClass('bg-alpha-dark-hover');
            if (scroll) {
                $('html, body').scrollTop($id.offset().top);
            }
            
            this.showFeatureInfoDetails(id);
        },
        
        /**
         * Unselect all features
         */
        unselectAll: function() {
            $('.resto-feature').each(function () {
                $(this).removeClass('selected darker').children().first().addClass('bg-alpha-dark-hover');
            });
            $('#feature-info-details').hide();
        },
        
        /**
         * Display detailled feature info panel
         * 
         * @param {string} id : feature identifier
         */
        showFeatureInfoDetails:function(id) {
            
            var $id = $('#' + id), $div, feature = this.features[id], self = this;
            
            if (!feature) {
                return false;
            }
            
            /*
             * Compute position of feature info panel based on foundation grid system
             * 
             */
            var $offCanvas = $('.left-off-canvas-menu'), left = $id.offset().left - (Math.abs($offCanvas.offset().left + $offCanvas.outerWidth()) < 20 ? 0 : $offCanvas.outerWidth()),
                top = $id.offset().top - $('.inner-wrap').offset().top;
            if (left + (2 * $id.outerWidth()) > $(window).width()) {
                if (left - $id.outerWidth() < 0) {
                    top = top + $id.outerHeight();
                }
                else {
                    left = left - $id.outerWidth();
                }
            }
            else {
                left = left + $id.outerWidth();
            }
            
            $div = $('#feature-info-details').addClass('lightfield').empty().css({
                'position': 'absolute',
                'height': $id.outerHeight() + 'px',
                'top': top + 'px',
                'left': left + 'px',
                'width':$id.outerWidth() + 'px',
                'z-index':'100'
            }).show();
            
            /*
             * Refresh infos
             */
            var infos = [];
            
            infos.push('<a class="fa fa-3x fa-file-text viewMetadata" href="#" title="' + self.Util.translate('_viewMetadata') + '"></a>');
            infos.push('<a class="fa fa-3x fa-map-marker showOnMap" href="#" title="' + self.Util.translate('_showOnMap') + '"></a>');
            
            // Download feature
            //if (feature.properties['services'] && feature.properties['services']['download'] && feature.properties['services']['download']['url']) {
                infos.push('<a class="fa fa-3x fa-cloud-download downloadProduct" href="#" title="' + self.Util.translate('_download') + '"></a>');
            //}
            
            // Add to cart
            if (self.Header.userProfile.userid !== -1) {
                infos.push('<a class="fa fa-3x fa-shopping-cart addToCart" href="#" title="' + self.Util.translate('_addToCart') + '"></a>');
            }
            $div.append('<div class="feature-info-bottom">' + infos.join('') + '</div>');
            
            $('.viewMetadata', $div).click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.location = self.restoUrl + 'collections/' + feature.properties['collection'] + '/' + feature.id + '.html?lang=' + self.language;
                return false;
            });
            $('.viewOnMap', $div).click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.switchTo($('#resto-panel-trigger-map'));
                self.Map.hilite($div.attr('id'), true);
                return false;
            });
            $('.showOnMap', $div).click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.switchTo($('#resto-panel-trigger-map'));
                self.Map.hilite(id, true);
                return false;
            });
            $('.addToCart', $div).click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                self.Util.showMask();
                $.ajax({
                    url: self.restoUrl + 'users/' + self.Header.userProfile.userid + '/cart',
                    async: true,
                    type: 'POST',
                    dataType: "json",
                    data: JSON.stringify({'url':self.restoUrl + 'collections/' + feature.properties['collection'] + '/' + feature.id + '/download'}),
                    contentType: 'application/json',
                    success: function(obj, textStatus, XMLHttpRequest) {
                        self.Util.hideMask();                
                        if (XMLHttpRequest.status === 200) {
                            alert('added')
                        }
                    },
                    error: function(e) {
                        self.Util.hideMask();
                        alert('TODO - error');
                    }
                });
                return false;
            });
            $('.downloadProduct', $div).click(function(e){
                e.preventDefault();
                e.stopPropagation();
                alert('TODO');
                return false;
                //feature.properties['services']['download']['url'] + '"' + (feature.properties['services']['download']['mimeType'] === 'text/html' ? ' target="_blank"' : '') + 
            });
            
        },
        
        TODO: function() {
            
            /*
             * Keywords are splitted in different types 
             * 
             *  - type = landuse (forest, water, etc.)
             *  - type = country/continent/region/state/city
             *  - type = platform/instrument
             *  - type = date
             *  - type = null and keyword start with a '#' = tags
             *  
             */
            infos =  {};
            if (feature.properties.keywords) {
                var keywords = {
                    location: {
                        title: '_location',
                        keywords: []
                    },
                    landuse: {
                        title: '_landUse',
                        keywords: []
                    },
                    tag: {
                        title: '_tags',
                        keywords: []
                    },
                    resolution: {
                        title: '_resolution',
                        keywords: []
                    },
                    other: {
                        title: '_other',
                        keywords: []
                    }
                };
                var j, keyword, text, typeAndId, type, title, resolution;
                for (j = feature.properties.keywords.length; j--;) {
                    keyword = feature.properties.keywords[j];
                    text = keyword['name'];
                    typeAndId = keyword.id.split(':'),
                            title = "";
                    if (typeAndId[0] === 'landuse') {
                        type = 'landuse';
                        text = text + ' (' + Math.round(keyword.value) + '%)';
                        title = self.Util.translate('_thisResourceContainsLanduse', [keyword.value, key]);
                    }
                    else if (typeAndId[0] === 'country' || typeAndId[0] === 'continent' || typeAndId[0] === 'region' || typeAndId[0] === 'state') {
                        type = 'location';
                        title = self.Util.translate('_thisResourceIsLocated', [text]);
                    }
                    else if (typeAndId[0] === 'city') {
                        type = 'location';
                        title = self.Util.translate('_thisResourceContainsCity', [text]);
                    }
                    else if (keyword.name.indexOf("#") === 0) {
                        type = 'tag';
                    }
                    else if (typeAndId[0] === 'other') {
                        type = 'other';
                    }
                    else {
                        continue;
                    }
                    keywords[type]['keywords'].push('<a href="' + self.Util.updateUrlFormat(keyword['href'], 'html') + '" class="resto-ajaxified resto-keyword' + (typeAndId[0] ? ' resto-keyword-' + typeAndId[0].replace(' ', '') : '') + '" title="' + title + '">' + text + '</a> ');
                }

                /*
                 * Resolution
                 */
                if (feature.properties['resolution']) {
                    resolution = self.getResolution(feature.properties['resolution']);
                    keywords['resolution']['keywords'].push(feature.properties['resolution'] + 'm - <a href="' + self.Util.updateUrl(self.Util.updateUrlFormat(selfUrl, 'html'), {q: self.Util.translate(resolution)}) + '" class="resto-ajaxified resto-updatebbox resto-keyword resto-keyword-resolution" title="' + self.Util.translate(resolution) + '">' + resolution + '</a>');
                }

                for (var key in keywords) {
                    if (keywords[key]['keywords'].length > 0) {
                        infos.push('<p><span class="upper">' + self.Util.translate(keywords[key]['title']) + '</span>&nbsp;&nbsp;<span>' + keywords[key]['keywords'].join(', ') + '</span></p>');
                    }
                }
                $('#feature-info-details').append('<div class="feature-info-details">' + infos.join('') + '</div>');
            }
            
        }
    };
    
    window.Resto.Header = {
        
        ssoServices: {},
        
        init: function() {
            
            var self = this;
            
            /*
             * Set Oauth servers
             */
            self.setOAuthServers();
            
            /*
             * Share on facebook
             */
            $('.shareOnFacebook').click(function(e) {
                e.preventDefault();
                window.open('https://www.facebook.com/sharer.php?u=' + encodeURIComponent(window.History.getState().cleanUrl) + '&t=' + encodeURIComponent(self.Util.sanitizeValue($('#search'))));
                return false;
            });

            /*
             * Share to twitter
             */
            $('.shareOnTwitter').click(function(e) {
                e.preventDefault();
                window.open('http://twitter.com/intent/tweet?status=' + encodeURIComponent(self.Util.sanitizeValue($('#search')) + " - " + window.History.getState().cleanUrl));
                return false;
            });

            /*
             * Show gravatar if user is connected
             */
            $('.gravatar').css('background-image', 'url(' + window.Resto.Util.getGravatar(self.userProfile.userhash, 200) + ')');
            
            /*
             * Sign in locally
             */
            $('.signIn').click(function(e) {
                e.preventDefault();
                self.signIn();
                return false;
            });
            
            /*
             * Register
             */
            $('.register').click(function(e){
                e.preventDefault();
                self.signUp();
                return false;
            });
            
            /*
             * Collection info trigger
             */
            $('.resto-collection-info-trigger').click(function(e){
                e.preventDefault();
                if ($('.resto-collection-info').is(':visible')) {
                    $('.resto-collection-info').slideUp();
                    $(this).removeClass('active');
                }
                else {
                    $('.resto-collection-info').slideDown();
                    $(this).addClass('active');
                }
                return false;
            });
            
            /*
             * Events
             */
            $('#userPassword').keypress(function (e) {
                if (e.which === 13) {
                    $('.signIn').trigger('click');
                    return false;
                }
            });
            $('#userPassword1').keypress(function (e) {
                if (e.which === 13) {
                    $('.register').trigger('click');
                    return false;
                }
            });
            
            $(document).on('opened.fndtn.reveal', '[data-reveal]', function () {
                switch($(this).attr('id')) {
                    case 'displayRegister':
                        $('#userName').focus();
                        break;
                    case 'displayLogin':
                        $('#userEmail').focus();
                        break;
                    case 'displayProfile':
                        self.showProfile();
                        break;
                    default:
                        break;
                }
            });
            
            /*
             * Show small menu
             */
            $('.show-small-menu').click(function(e){
                e.preventDefault();
                if ($('#small-menu').is(':visible')){
                    $('#small-menu').hide();
                    $('.show-small-menu').removeClass('icon-close');
                    $('.show-small-menu').addClass('icon-menu');
                }else{
                    $('#small-menu').show();
                    $('.show-small-menu').addClass('icon-close');
                    $('.show-small-menu').removeClass('icon-menu');
                }
                return false;
            });
            
            $(window).resize(function(){
                $('#small-menu').hide();
            });
            
        },
     
        /**
         * Sign in
         */
        signIn: function() {
            
            Resto.Util.showMask();
            $.ajax({
                url: Resto.restoUrl + 'api/users/connect',
                headers: {
                    'Authorization': "Basic " + btoa(Resto.Util.sanitizeValue($('#userEmail')) + ":" + Resto.Util.sanitizeValue($('#userPassword')))
                },
                dataType: 'json',
                success: function (json) {
                    if (json && json.userid === -1) {
                        Resto.Util.hideMask();
                        Resto.Util.alert($('#displayLogin'), 'Error - unknown user or incorrect password');
                    }
                    else {
                        window.location.reload();
                    }
                },
                error: function (e) {
                    Resto.Util.hideMask();
                    Resto.Util.alert($('#displayLogin'), 'Error - cannot sign in');
                }
            });
        },
        
        /**
         * Register
         */
        signUp: function() {
            var username = Resto.Util.sanitizeValue($('#userName')), 
                password1 = Resto.Util.sanitizeValue($('#userPassword1')),
                email = Resto.Util.sanitizeValue($('#r_userEmail')),
                $div = $('#displayRegister');

            if (!email || !Resto.Util.isEmailAdress(email)) {
                Resto.Util.alert($div, 'Email is not valid');
            }
            else if (!username) {
                Resto.Util.alert($div, 'Username is mandatory');
            }
            else if (!password1) {
                Resto.Util.alert($div, 'Password is mandatory');
            }
            else {
                Resto.Util.showMask();
                $.ajax({
                    url: Resto.restoUrl + 'users',
                    async: true,
                    type: 'POST',
                    dataType: "json",
                    data: {
                        email: email,
                        password: password1,
                        username: username,
                        givenname: Resto.Util.sanitizeValue($('#firstName')),
                        lastname: Resto.Util.sanitizeValue($('#lastName'))
                    },
                    success: function (json) {
                        Resto.Util.hideMask();
                        if (json && json.status === 'success') {
                            Resto.Util.alert($div, Resto.Util.translate('_emailSent'));
                            $div.hide();
                        }
                        else {
                            Resto.Util.alert($div, json.ErrorMessage);
                        }
                    },
                    error: function (e) {
                        Resto.Util.hideMask();
                        if (e.responseJSON) {
                            Resto.Util.alert($div, e.responseJSON.ErrorMessage);
                        }
                        else {
                            Resto.Util.alert($div, 'Error : cannot register');
                        }
                    }
                });
            }
        },
        
        /**
         * Show user profile
         */
        showProfile: function() {
            var $div = $('#displayProfile');
            $div.html('<div class="padded large-12 columns center"><img class="gravatar-big" src="' + window.Resto.Util.getGravatar(this.userProfile.userhash, 200) + '"/><a class="button signOut">' + window.Resto.Util.translate('_logout') + '</a></div><a class="close-reveal-modal">&#215;</a>');
            $('.signOut').click(function() {
                Resto.Util.showMask();
                $.ajax({
                    url: window.Resto.restoUrl + 'api/users/disconnect',
                    dataType:'json',
                    success: function(json) {
                        Resto.Util.hideMask();
                        window.location.reload();
                    },
                    error: function(e) {
                        Resto.Util.hideMask();
                        Resto.Util.alert($div, 'Error : cannot disconnect');
                    }
                });
                return false;
            });
        },
        
        /**
         * OAuth (e.g. google)
         */
        setOAuthServers: function () {
            var self = this;
            for (var key in self.ssoServices) {
                (function (key) {
                    $('.signWithOauth').append('<span id="_oauth' + key + '">' + self.ssoServices[key].button + '</span>');
                    $('a', '#_oauth' + key).click(function (e) {
                        
                        e.preventDefault();
                        e.stopPropagation();
                        
                        /*
                         * Open SSO authentication window
                         */
                        Resto.Util.showMask();
                        var popup = Resto.Util.popupwindow(self.ssoServices[key].authorizeUrl, "oauth", 400, $(window).height());
                        
                        /*
                         * Load user profile after popup has been closed
                         */
                        var fct = setInterval(function () {
                            if (popup.closed) {
                                clearInterval(fct);
                                window.location.reload();
                            }
                        }, 200);
                        return false;
                    });
                })(key);
            }
        }
    };

})(window);
