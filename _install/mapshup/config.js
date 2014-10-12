(function(c) {

    /*
     * !!! CHANGE THIS !!!
     */
    c["general"].rootUrl = '//localhost/resto/';
    
    /*
     * !! DO NOT EDIT UNDER THIS LINE !!
     */
    c["general"].serverRootUrl = null;
    c["general"].proxyUrl = null;
    c["general"].confirmDeletion = false;
    c["general"].themePath = "/js/css";
    c["i18n"].path = "/js/i18n";
    c["general"].displayContextualMenu = false;
    c["general"].displayCoordinates = true;
    c["general"].displayScale = false;
    c["general"].overviewMap = "none";
    c['general'].enableHistory = false;
    c["general"].timeLine = {
        enable: false
    };
    c.extend("Navigation", {
        position: 'nw',
        orientation: 'h',
        home: null
    });
    c.remove("layers", "Streets");
    c.remove("layers", "Satellite");
    c.remove("layers", "Relief");
    c.remove("layers", "MapQuest OSM");
    c.remove("layers", "OpenStreetMap");
    c.add("layers", {
        type: "Bing",
        title: "Satellite",
        key: "AmraZAAcRFVn6Vbxk_TVhhVZNt66x4_4SV_EvlfzvRC9qZ_2y6k1aNsuuoYS0UYy",
        bingType: "AerialWithLabels"
    });

})(window.M.Config);
