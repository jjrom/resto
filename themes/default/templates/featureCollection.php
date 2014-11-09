<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body>
        <!-- Header -->
        <?php include 'header.php' ?>

        <!-- Search panel -->
        <div class="row fullWidth resto-search-panel center">
            <div class="large-12 columns">
                <form id="resto-searchform" action="<?php echo $self->context->baseUrl . 'collections/' . (isset($self->collection->name) ? $self->collection->name . '/' : '') . 'search.json' ?>">
                    <span class="resto-search">
                        <input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>" style="font-size:1em;height:30px;max-width:400px;width:90%;"/>
                        <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" />
                    </span>
                    <span class="panel-triggers">
                        <a href="#panel-list" class="fa fa-th resto-panel-trigger active" id="resto-panel-trigger-list"><font style="padding-left:5px"><?php echo $self->context->dictionary->translate('_menu_list'); ?></font></a> <a href="#panel-map" class="fa fa-map-marker resto-panel-trigger" id="resto-panel-trigger-map"><font style="padding-left:5px"><?php echo $self->context->dictionary->translate('_menu_map'); ?></font></a>
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
            <!-- Result -->
            <!--
            <div class="row padded">
                <div class="large-12 columns center">
                    <h3 id="resultsummary"></h3>
                </div>
            </div>    
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
                    "collection":'<?php echo isset($self->collection->name) ? $self->collection->name : null ?>',
                    "ssoServices":<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    "userProfile":<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()))) ?>
                    }, <?php echo $self->toJSON(); ?>
                );
            });
        </script>
    </body>
</html>
