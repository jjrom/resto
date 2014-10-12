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
            <div class="large-6 columns">
                <h1 class="right"><?php echo $self->collection->osDescription[$self->context->dictionary->language]['ShortName']; ?></h1>
            </div>
            <div class="large-6 columns">
                <p>
                    <?php echo $self->collection->osDescription[$self->context->dictionary->language]['Description']; ?>
                </p>
            </div>
        </div>
        <!-- Search bar -->
        <div class="row padded">
            <div class="large-12 columns resto-search center">
                <form id="resto-searchform" action="<?php echo $self->context->baseUrl . 'collections/' . $self->collection->name. '/search.json' ?>">
                    <?php
                    if ($self->context->dictionary->language) {
                        echo '<input type="hidden" name="' . $self->model->searchFilters['language']['osKey'] . '" value="' . $self->context->dictionary->language . '" />';
                    }
                    ?>
                    <input type="search" id="search" name="<?php echo $self->model->searchFilters['searchTerms']['osKey'] ?>" value="<?php echo isset($self->context->query[$self->model->searchFilters['searchTerms']['osKey']]) ? $self->context->query[$self->model->searchFilters['searchTerms']['osKey']] : ''; ?>" placeholder="<?php echo $self->context->dictionary->translate('_placeHolder', $self->collection->osDescription[$self->context->dictionary->language]['Query']); ?>"/><span id="searchsubmit" class="button zoom fa fa-search" title="search"></span>
                </form>
            </div>
        </div>
        <!-- mapshup display -->
        <div id="mapshup" class="noResizeHeight"></div>
        <!-- Administration -->
        <?php if ($self->context->user->canPost($self->collection->name)) { ?>
            <div class="row fullWidth resto-admin">
                <div class="large-12 columns center">
                    <div id="dropZone"><h1><?php echo $self->context->dictionary->translate('_addResource'); ?></h1><span class="fa fa-arrow-down"></span> <?php echo $self->context->dictionary->translate('_dropResource'); ?> <span class="fa fa-arrow-down"></span></div>
                </div>
            </div>
        <?php } ?>
        <!-- Query analyze result -->
        <?php if (isset($self->context->query['_showQuery']) && $self->context->query['_showQuery']) { ?>
            <div class="resto-queryanalyze fixed"></div>
        <?php } ?>
        <!-- Result -->
        <div class="row padded">
            <div class="large-12 columns center">
                <h3 id="resultsummary"></h3>
            </div>
        </div>    
        <!-- Pagination -->
        <div class="row">
            <div class="large-12 columns">
                <ul class="small-block-grid-1 medium-block-grid-3 large-block-grid-4 resto-pagination center"></ul>
            </div>
        </div>
        <!-- Search result -->
        <div class="row">
            <div class="large-12 columns">
                <ul class="small-block-grid-1 medium-block-grid-3 large-block-grid-4 resto-content center"></ul>
            </div>
        </div>
        <!-- Pagination -->
        <div class="row">
            <div class="large-12 columns">
                <ul class="small-block-grid-1 medium-block-grid-3 large-block-grid-4 resto-pagination center"></ul>
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
                    issuer:'getCollection',
                    language: '<?php echo $self->context->dictionary->language; ?>',
                    data: <?php echo $self->toJSON() ?>,
                    translation:<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                    restoUrl: '<?php echo $self->context->baseUrl ?>',
                    collection: '<?php echo $self->collection->name ?>',
                    ssoServices:<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    userProfile:<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => $_SESSION['rights']))) ?> 
                });
                
            });
        </script>
    </body>
</html>
