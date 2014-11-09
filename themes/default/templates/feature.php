<?php
    
    /*
     * Set variables
     */
    $product = $self->toArray();          
    $thumbnail = $product['properties']['thumbnail'];
    $quicklook = $product['properties']['quicklook'];
    if (!isset($thumbnail) && isset($quicklook)) {
        $thumbnail = $quicklook;
    }
    else if (!isset($thumbnail) && !isset($quicklook)) {
        $thumbnail = $self->context->baseUrl . 'themes/' . $self->context->config['theme'] . '/img/noimage.png';
    }
    if (!isset($quicklook)) {
        $quicklook = $thumbnail;
    }
    
    if (isset($self->context->config['modules']['Wikipedia'])) {
        $self->wikipedia = new Wikipedia($self->context, $self->context->config['modules']['Wikipedia']);
        $wikipediaEntries = $self->wikipedia->search(array(
            'polygon' => RestoUtil::geoJSONGeometryToWKT($product['geometry']),
            'limit' => 10
        ));
    }
    
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body>
        
        <!-- Header -->
        <?php include 'header.php' ?>
      
        <!-- Collection title and description -->
        <div class="row">
            <div class="large-6 columns center">
                <h1>&nbsp;</h1>
                <h2><?php echo $self->context->dictionary->translate('_resourceSummary', $product['properties']['platform'], $product['properties']['resolution'], substr($product['properties']['startDate'],0, 10)); ?></h2>
                <h7 title="<?php echo $product['id']; ?>" style="overflow: hidden;"><?php echo $product['id']; ?></h7>
                <?php
                    if (isset($product['properties']['services']) && isset($product['properties']['services']['download']) && isset($product['properties']['services']['download']['url'])) {
                        if ($self->context->user->canDownload($self->collection->name, $product['id'])) {
                ?>
                <p class="center padded-top">
                    <a class="fa fa-4x fa-cloud-download" href="<?php echo $product['properties']['services']['download']['url']; ?>" <?php echo $product['properties']['services']['download']['mimeType'] === 'text/html' ? 'target="_blank"' : ''; ?> title="<?php echo $self->context->dictionary->translate('_download'); ?>"></a> 
                </p>
                <?php
                      } 
                    }
                ?>
                <h1>&nbsp;</h1>
            </div>
            <div class="large-6 columns grey">
                <h3><?php echo $self->collection->osDescription[$self->context->dictionary->language]['ShortName']; ?></h3>
                <p>
                    <?php echo $self->collection->osDescription[$self->context->dictionary->language]['Description']; ?>
                </p>
            </div>
        </div>
        
        <!-- mapshup display -->
        <div id="mapshup" class="noResizeHeight"></div>
        
        <!-- Quicklook and metadata -->
        <div class="row resto-resource">
            <div class="large-6 columns center">
                <img title="<?php echo $product['id'];?>" class="resto-image" src="<?php echo $quicklook;?>"/>
            </div>
            <div class="large-6 columns">
                <table style="width:100%;">
                    <?php
                    $excluded = array('quicklook', 'thumbnail', 'links', 'services', 'keywords', 'updated', 'productId', 'landUse');
                    foreach(array_keys($product['properties']) as $key) {
                        if (in_array($key, $excluded)) {
                            continue;
                        }
                        if (!is_array($product['properties'][$key])) {
                            echo '<tr><td>' . $self->context->dictionary->translate($key) . '</td><td>' . $product['properties'][$key] . '</td></tr>';
                        }   
                    }
                    ?>
                </table>
            </div>
        </div>
        
        <!-- Location content (Landcover) -->
        <div class="row resto-resource fullWidth dark">
            <div class="large-6 columns">
                <h1><span class="right"><?php echo $self->context->dictionary->translate('_location'); ?></span></h1>
            </div>
            <div class="large-6 columns">
            <?php
            foreach(array_values(array('continent', 'country', 'region', 'state')) as $key) {
                if ($product['properties']['keywords']) {
                        for ($i = 0, $l = count($product['properties']['keywords']); $i < $l; $i++) {
                            list($type, $id) = explode(':', $product['properties']['keywords'][$i]['id'], 2);
                            if (strtolower($type) === $key) { ?>
                <h2><a title="<?php echo $self->context->dictionary->translate('_thisResourceIsLocated', $product['properties']['keywords'][$i]['name']) ?>" href="<?php echo RestoUtil::updateUrlFormat($product['properties']['keywords'][$i]['href'], 'html') ?>"><?php echo $product['properties']['keywords'][$i]['name']; ?></a></h2>
            <?php }}}} ?>
            </div>
        </div>
        
        <!-- Thematic content (Landcover) -->
        <div class="row resto-resource fullWidth light">
            <div class="large-6 columns">
                <h1><span class="right"><?php echo $self->context->dictionary->translate('_landUse'); ?></span></h1>
            </div>
            <div class="large-6 columns">
            <?php
                    if ($product['properties']['keywords']) {
                        for ($i = 0, $l = count($product['properties']['keywords']); $i < $l; $i++) {
                            list($type, $id) = explode(':', $product['properties']['keywords'][$i]['id'], 2);
                            if (strtolower($type) === 'landuse') { ?>
                    <h2><?php echo round($product['properties']['keywords'][$i]['value']); ?> % <a title="<?php echo $self->context->dictionary->translate('_thisResourceContainsLanduse', $product['properties']['keywords'][$i]['value'], $keyword) ?>" href="<?php echo RestoUtil::updateUrlFormat($product['properties']['keywords'][$i]['href'], 'html') ?>"><?php echo $product['properties']['keywords'][$i]['name']; ?></a></h2>
            <?php }}} ?>
            </div>
        </div>
        
        <!-- Wikipedia -->
        <?php if (isset($wikipediaEntries) && is_array($wikipediaEntries) && count($wikipediaEntries) > 0) { ?>
        <div class="row resto-resource fullWidth dark">
            <div class="large-6 columns">
                <h1 class="right"><?php echo $self->context->dictionary->translate('_poi'); ?></h1>
            </div>
            <div class="large-6 columns">
                <?php foreach ($wikipediaEntries as $wikipediaEntry) { ?>
                <h2><a href="<?php echo $wikipediaEntry['url']; ?>"><?php echo $wikipediaEntry['title']; ?></a></h2>
                <p><?php echo $wikipediaEntry['summary']; ?></p>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        
        <!-- Footer -->
        <?php include 'footer.php' ?>
        
        <script type="text/javascript">
            $(document).ready(function() {
                Resto.init({
                    "issuer":'getResource',
                    "translation":<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                    "language":'<?php echo $self->context->dictionary->language; ?>',
                    "restoUrl":'<?php echo $self->context->baseUrl ?>',
                    "collection":'<?php echo isset($self->collection->name) ? $self->collection->name : null ?>',
                    "ssoServices":<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    "userProfile":<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()))) ?>
                    }, <?php echo '{"type":"FeatureCollection","features":[' . $self->toJSON() . ']}' ?>
                );
            });
        </script>
    </body>
</html>
