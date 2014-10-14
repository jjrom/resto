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
        $thumbnail = self.restoUrl + '/css/default/img/noimage.png';
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
      
        <div class="row" style="height:50px;">
            <div class="large-12 columns"></div>
        </div>
        
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
                    if ($product['properties']['keywords']) {
                        foreach ($product['properties']['keywords'] as $keyword => $value) {
                            if (strtolower($value['type']) === 'continent') {
            ?>
                <h2><a title="<?php echo $self->context->dictionary->translate('_thisResourceIsLocated', $keyword) ?>" href="<?php echo RestoUtil::updateUrlFormat($value['href'], 'html') ?>"><?php echo $keyword; ?></a></h2>
            <?php }}} ?>
            <?php
                    if ($product['properties']['keywords']) {
                        foreach ($product['properties']['keywords'] as $keyword => $value) {
                            if (strtolower($value['type']) === 'country') {
            ?>
                <h2><a title="<?php echo $self->context->dictionary->translate('_thisResourceIsLocated', $keyword) ?>" href="<?php echo RestoUtil::updateUrlFormat($value['href'], 'html') ?>"><?php echo $keyword; ?></a></h2>
            <?php }}} ?>
            <?php
                    if ($product['properties']['keywords']) {
                        foreach ($product['properties']['keywords'] as $keyword => $value) {
                            if (strtolower($value['type']) === 'region') {
            ?>
                <h2><a title="<?php echo $self->context->dictionary->translate('_thisResourceIsLocated', $keyword) ?>" href="<?php echo RestoUtil::updateUrlFormat($value['href'], 'html') ?>"><?php echo $keyword; ?></a></h2>
            <?php }}} ?>
            <?php
                    if ($product['properties']['keywords']) {
                        foreach ($product['properties']['keywords'] as $keyword => $value) {
                            if (strtolower($value['type']) === 'state') {
            ?>
                <h2><a title="<?php echo $self->context->dictionary->translate('_thisResourceIsLocated', $keyword) ?>" href="<?php echo RestoUtil::updateUrlFormat($value['href'], 'html') ?>"><?php echo $keyword; ?></a></h2>
            <?php }}} ?>
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
                        foreach ($product['properties']['keywords'] as $keyword => $value) {
                            if (strtolower($value['type']) === 'landuse') {
            ?>
                <h2><?php echo round($value['value']); ?> % <a title="<?php echo $self->context->dictionary->translate('_thisResourceContainsLanduse', $value['value'], $keyword) ?>" href="<?php echo RestoUtil::updateUrlFormat($value['href'], 'html') ?>"><?php echo $keyword; ?></a></h2>
            <?php }}} ?>
            </div>
        </div>
        
        <!-- Wikipedia -->
        <div class="row resto-resource fullWidth dark">
            <div class="large-6 columns">
                <h1 class="right"><?php echo $self->context->dictionary->translate('_poi'); ?></h1>
            </div>
            <div class="large-6 columns">
                <?php
                if (is_array($wikipediaEntries) && count($wikipediaEntries) > 0) {
                    foreach ($wikipediaEntries as $wikipediaEntry) {
                ?>
                <h2><a href="<?php echo $wikipediaEntry['url']; ?>"><?php echo $wikipediaEntry['title']; ?></a></h2>
                <p><?php echo $wikipediaEntry['summary']; ?></p>
                <?php }} ?>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include 'footer.php' ?>
        
        <script type="text/javascript">
            $(document).ready(function() {
               
                /*
                 * Initialize mapshup
                 */
                if (M) {
                    M.load();
                }

                /*
                 * Initialize RESTo
                 */
                R.init({
                    issuer:'getResource',
                    language: '<?php echo $self->context->dictionary->language; ?>',
                    data:  <?php echo '{"type":"FeatureCollection","features":[' . $self->toJSON() . ']}' ?>,
                    translation:<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                    restoUrl: '<?php echo $self->context->baseUrl ?>',
                    collection: '<?php echo $self->collection->name ?>',
                    ssoServices:<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    userProfile:<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()))) ?> 
                });
            });
        </script>
    </body>
</html>
