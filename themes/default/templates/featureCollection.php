<!DOCTYPE html>
<?php $_searchBar = true; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include '_head.php' ?>
    <body>
        
        <!-- Header -->
        <?php include '_header.php' ?>
        
        <div class="off-canvas-wrap" data-offcanvas>
            
            <div class="inner-wrap">
                    
                <!-- Metadata view -->
                <div class="resto-panel" id="panel-metadata"></div>
                
                <!-- Map view -->
                <div class="resto-panel" id="panel-map">
                    <div id="map" class="map"></div>
                </div>

                <!-- Result view -->
                <div class="resto-panel active" id="panel-list">

                    <!-- Search result -->
                    <div class="row fullWidth" style="min-height:800px;">
                        <div class="large-12 columns">
                            <ul class="resto-features-container small-block-grid-1 medium-block-grid-2 large-block-grid-3"></ul>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Left menu -->
                <div class="left-off-canvas-menu lightfield text-dark">
                    <ul class="small-block-grid-1 medium-block-grid-1 large-block-grid-1 facets padded">
                        <li>
                            <h4><?php echo $self->context->dictionary->translate('_facets_collections') ?></h4>
                            <?php if (isset($self->collection)) { ?>
                            <span class="facets_collections"><a class="resto-collection-info-trigger" href="#"><?php echo $self->collection->name ?></a></span>
                            <?php } ?>
                        </li>
                        <li>
                            <h4><?php echo $self->context->dictionary->translate('_facets_where') ?></h4>
                            <span class="facets_where"></span>
                        </li>
                        <li>
                            <h4><?php echo $self->context->dictionary->translate('_facets_when') ?></h4>
                            <span class="facets_when"></span>
                        </li>
                        <li>
                            <h4><?php echo $self->context->dictionary->translate('_facets_what') ?></h4>
                            <span class="facets_what"></span>
                        </li>
                    </ul>
                </div>
                
                <!--<span style="position:absolute;left:-5px;top:0px;">
                    <a id="off-canvas-toggle" href="#" class="fa fa-chevron-right text-dark lightfield" style="padding:0px 15px 0px 15px;"></a>
                </span>-->
                
                <!-- Footer -->
                <?php include '_footer.php' ?>
                    
            </div>
                
        </div>
        
        <!-- Scripts -->
        <?php
            $_issuer = 'getCollection';
            $_data = $self->toJSON();
        ?>
        <?php include '_scripts.php' ?>
    </body>
</html>
