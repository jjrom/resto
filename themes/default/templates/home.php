<?php
    $_noSearchBar = true;
    $_noMap = true;
    $facetsStatistics = $self->context->dbDriver->getFacetsStatistics(null);
    $nbOfProducts = 0;
    $nbOfCollections = 0;
    if (isset($facetsStatistics['facet_counts']['facet_fields']['collection'])) {
        $l = count($facetsStatistics['facet_counts']['facet_fields']['collection']);
        for ($i = 1; $i < $l; $i = $i + 2) {
            $nbOfProducts += $facetsStatistics['facet_counts']['facet_fields']['collection'][$i];
            $nbOfCollections++;
        } 
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body class="bg glass">
        
        <!-- Header -->
        <?php include 'header.php' ?>
        
        <div class="row" style="height:250px;">
            <div class="large-12 columns"></div>
        </div>
                    
        <form id="resto-searchform" changeLocation="true" action="<?php echo $self->context->baseUrl . 'api/collections/search.html' ?>">
            <div class="row fullWidth" style="width:100%;text-align:center;">
                <div class="large-12 columns">
                    <span class="resto-search"><input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>" style="font-size:1.5em;height:50px;width:500px;"></span>
                </div>
            </div>
        </form>
        
        <div class="row" style="height:50px">
            <div class="large-12 columns"></div>
        </div>
        
        <div class="row fullWidth" style="width:100%;text-align:center;color:#fff;font-size:2em;">
            <div class="large-12 columns">
                <?php if ($nbOfProducts > 0) { ?>
                Currently <?php echo $nbOfProducts;?> images available in <?php echo $nbOfCollections;?> <a href="<?php echo $self->context->baseUrl . 'collections'; ?>">collections</a>
                <?php } else { ?>
                Hey! there is nothing in the database yet
                <?php } ?>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="position:fixed;bottom:0px;text-align:center;width:100%;">
        <?php include 'footer.php' ?>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                R.init({
                    language: '<?php echo $self->context->dictionary->language; ?>',
                    translation:<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                    restoUrl: '<?php echo $self->context->baseUrl ?>',
                    ssoServices:<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    userProfile:<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()))) ?> 
                });
            });
        </script>
    </body>
</html>
