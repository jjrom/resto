<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body>
        <!-- Header -->
        <?php include 'header.php' ?>
        
        <?php if (isset($self->collection)) { ?>
        <!-- Collection description -->
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

        <!-- Search panel -->
        <div class="row fullWidth resto-search-panel center">
            <div class="large-6 columns">
                <ul class="small-block-grid-1 medium-block-grid-2 large-block-grid-4 facets">
                    <li>
                        <h4><?php echo $self->context->dictionary->translate('_facets_collections') ?></h4>
                        <?php if (isset($self->collection)) { ?>
                        <span class="facets_collections"><a class="resto-collection-info-trigger" href="#"><?php echo $self->collection->name ?></a></span>
                        <?php } ?>
                    </li>
                    <li>
                        <h4><?php echo $self->context->dictionary->translate('_facets_when') ?></h4>
                        <span class="facets_when"></span>
                    </li>
                    <li>
                        <h4><?php echo $self->context->dictionary->translate('_facets_where') ?></h4>
                        <span class="facets_where"></span>
                    </li>
                    <li>
                        <h4><?php echo $self->context->dictionary->translate('_facets_what') ?></h4>
                        <span class="facets_what"></span>
                    </li>
                </ul>
            </div>
            <div class="large-5 columns">
                <form id="resto-searchform" action="<?php echo $self->context->baseUrl . 'collections/' . (isset($self->collection->name) ? $self->collection->name . '/' : '') . 'search.json' ?>" style="padding-top:5px;">
                    <span class="resto-search">
                        <input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>"/>
                        <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" />
                    </span>
                </form>
            </div>
            <div  class="large-1 columns">
                <span class="panel-triggers">
                    <a href="#panel-list" class="fa fa-2x fa-th resto-panel-trigger active" id="resto-panel-trigger-list" title="<?php echo $self->context->dictionary->translate('_menu_list'); ?>"></a>&nbsp;<a href="#panel-map" class="fa fa-2x fa-map-marker resto-panel-trigger" id="resto-panel-trigger-map" title="<?php echo $self->context->dictionary->translate('_menu_map'); ?>"></a>
                </span>
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
                    <ul class="resto-features-container small-block-grid-1 medium-block-grid-3 large-block-grid-4"></ul>
                </div>
            </div>
            
            <!-- Detailed info -->
            <div id="feature-info-details" style="display:none"></div>
            
            <!-- Footer -->
            <?php include 'footer.php' ?>
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
