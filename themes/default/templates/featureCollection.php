<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body class="darkfield">
        
        <!-- Header -->
        <?php include 'header.php' ?>

        <div class="off-canvas-wrap" data-offcanvas>
            
            <div class="inner-wrap">
                    
                <!-- Search panel -->
                <div class="row fullWidth resto-search-panel center">
                    <div class="large-12 columns">
                        <form id="resto-searchform" action="<?php echo $self->context->baseUrl . 'api/collections/' . (isset($self->collection->name) ? $self->collection->name . '/' : '') . 'search.json' ?>" style="padding-top:5px;">
                            <span class="resto-search">
                                <input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>"/>
                                <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" />
                            </span>
                            <span class="panel-triggers" style="display:inline-block;">
                                <a href="#panel-list" class="resto-panel-trigger active" id="resto-panel-trigger-list"><span class="fa fa-th"></span>&nbsp;<?php echo $self->context->dictionary->translate('_menu_list'); ?></a>&nbsp;<a href="#panel-map" class="resto-panel-trigger" id="resto-panel-trigger-map"><span class="fa fa-map-marker"></span>&nbsp;<?php echo $self->context->dictionary->translate('_menu_map'); ?></a>
                            </span>
                        </form>
                    </div>
                </div>

                <!-- Map view -->
                <div class="resto-panel" id="panel-map">
                    <div id="mapshup"></div>
                </div>

                <!-- Result view -->
                <div class="resto-panel active" id="panel-list">

                    <!-- Query analyze result -->
                    <!--
                    <?php if (isset($self->context->query['_showQuery']) && $self->context->query['_showQuery']) { ?>
                        <div class="resto-queryanalyze fixed"></div>
                    <?php } ?>
                    -->

                    <!-- Search result -->
                    <div class="row fullWidth" style="min-height:800px;">
                        <div class="large-12 columns">
                            <ul class="resto-features-container small-block-grid-1 medium-block-grid-2 large-block-grid-3"></ul>
                        </div>
                    </div>

                    <!-- Detailed info -->
                    <div id="feature-info-details" style="display:none"></div>

                    <!-- Footer -->
                    <?php include 'footer.php' ?>
                </div>
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
                    <!--
                    <?php if (isset($self->collection)) { ?>
                    <div class="row fullWidth resto-collection-info">
                        <div class="large-6 columns">
                            <h1 class="right"><?php echo $self->collection->getOSProperty('ShortName'); ?></h1>
                        </div>
                        <div class="large-6 columns">
                            <p class="text-light">
                                <?php echo $self->collection->getOSProperty('Description'); ?>
                            </p>
                        </div>
                    </div>
                    <?php } ?>
                    -->
                </div>
                <span style="position:absolute;left:-5px;top:0px;">
                    <a id="off-canvas-toggle" href="#" class="fa fa-chevron-right text-dark lightfield" style="padding:0px 15px 0px 15px;"></a>
                </span>
            </div>
                
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                Resto.init({
                    "issuer":'getCollection',
                    "translation":<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                    "language":'<?php echo $self->context->dictionary->language; ?>',
                    "restoUrl":'<?php echo $self->context->baseUrl ?>',
                    "ssoServices":<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    "userProfile":<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()))) ?>
                    }, <?php echo $self->toJSON(); ?>
                );
            });
        </script>
    </body>
</html>
