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
 */(function(a){a.general.rootUrl="http://localhost/resto2";a.general.serverRootUrl=a.general.rootUrl+"/s";a.general.indexPath="/index.html";a.general.confirmDeletion=!0;a.general.coordinatesFormat="dms";a.general.displayContextualMenu=!0;a.general.displayCoordinates=!0;a.general.displayScale=!0;a.general.enableHistory=!0;a.general.featureHilite=!0;a.general.location={lon:0,lat:40,zoom:2};a.general.overviewMap="closed";a.general.proxyUrl="/proxy.php?";a.general.getContextServiceUrl="/plugins/logger/getContext.php?uid=";
a.general.refreshInterval=1E3;a.general.reprojectionServiceUrl="/mapserver/getReprojectedWMS.php?";a.general.rssToGeoRSSServiceUrl="/utilities/rss2georss.php?url=";a.general.saveStreamServiceUrl="/utilities/saveStream.php?";a.general.shpToWMSServiceUrl="/mapserver/shp2wms.php?";a.general.mbtilesServiceUrl="/utilities/mbtsrv.php?zxy=${z}/${x}/${y}&t=";a.general.numRecordsPerPage=20;a.general.themePath="/js/mapshup/theme/default";a.general.teleport=!1;a.general.timeLine={enable:!0};a.i18n.langs=["en",
"fr","de","jp"];a.i18n.lang="auto";a.i18n.path="/js/lib/mapshup/i18n";a.panels={south:{over:!0,h:250},side:{w:400}};a.upload.serviceUrl="/utilities/upload.php?";a.upload.allowedMaxSize=1E6;a.upload.allowedExtensions="gml gpx kml xml rss jpeg jpg gif png shp shx dbf json".split(" ");a.add("layers",{type:"Google",title:"Satellite",MID:"GoogleSatellite",googleType:"satellite",numZoomLevels:22,unremovable:!0});a.add("layers",{type:"Google",title:"Streets",MID:"GoogleStreets",numZoomLevels:22,unremovable:!0});
a.add("layers",{type:"Google",title:"Relief",MID:"GoogleRelief",googleType:"terrain",numZoomLevels:22,unremovable:!0});a.add("layers",{type:"XYZ",title:"MapQuest OSM",MID:"MapQuestOSM",url:["http://otile1.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png","http://otile2.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png","http://otile3.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png","http://otile4.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png"],ol:{attribution:'<p>Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png"></p>'}});
a.add("layers",{type:"XYZ",title:"OpenStreetMap",MID:"OpenStreetMap",url:["http://a.tile.openstreetmap.org/${z}/${x}/${y}.png","http://b.tile.openstreetmap.org/${z}/${x}/${y}.png","http://c.tile.openstreetmap.org/${z}/${x}/${y}.png"],ol:{attribution:'Tiles from <a href="http://www.openstreetmap.org" target="_blank">OpenStreetMap</a>'}});a.add("plugins",{name:"UserManagement"});a.add("plugins",{name:"Export"});a.add("plugins",{name:"Navigation"});a.add("plugins",{name:"BackgroundsManager"});a.add("plugins",
{name:"RastersManager"});a.add("plugins",{name:"LayersManager"});a.add("plugins",{name:"LayerInfo"});a.add("plugins",{name:"AddLayer",options:{allowedLayerTypes:[{name:"Catalog"},{name:"Atom"},{name:"GeoJSON"},{name:"GeoRSS"},{name:"KML"},{name:"Pleiades"},{name:"WFS"},{name:"WMS"},{name:"WMTS"}]}});a.add("plugins",{name:"Search",options:{services:[{url:"/plugins/flickr/opensearch.xml",stype:"Flickr"},{url:"/plugins/youtube/opensearch.xml",stype:"Youtube"},{url:"/plugins/geonames/opensearch.xml"},
{url:"/plugins/wikipedia/opensearch.xml"}]}});a.add("plugins",{name:"GoogleEarth",options:{synchronizeWMS:!1,buildings:!1}});a.add("plugins",{name:"Distance"});a.add("plugins",{name:"GetFeatureInfo"});a.add("plugins",{name:"Geonames",options:{findNearByUrl:"http://ws.geonames.org/findNearbyJSON?"}});a.add("plugins",{name:"Wikipedia",options:{searchUrl:"http://ws.geonames.net/wikipediaBoundingBoxJSON?"}});a.add("plugins",{name:"Flickr"});a.add("plugins",{name:"Drawing"});a.add("plugins",{name:"Streetview"});
a.add("plugins",{name:"Routing",options:{method:"SPD",url:"/plugins/routing/getShortestPath2.php?"}});a.add("plugins",{name:"Catalog",options:{nextRecord:1,connectors:[{name:"CSWISO"},{name:"CSWEOCharter"},{name:"CSWEO"},{name:"CSWEOr5"},{name:"CSWEOr5ebRR"},{name:"SPOTRest"},{name:"OpenSearch"}]}});a.add("plugins",{name:"WorldGrid"});a.add("plugins",{name:"Share"});a.add("plugins",{name:"Help"});a.add("plugins",{name:"UTFGrid"});a.add("plugins",{name:"WPSClient"});a.add("plugins",{name:"FeatureEdition"});
a.add("plugins",{name:"API",options:{authorized:"*"}})})(window.M.Config);
