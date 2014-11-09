<?php
    $_noMap = true;
    $statistics = $self->context->dbDriver->getStatistics();
    $nbOfProducts = 0;
    $nbOfCollections = 0;
    if (isset($statistics['collection'])) {
        foreach (array_values($statistics['collection']) as $count) {
            $nbOfProducts += $count;
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
          
        <div class="row fullWidth" style="padding:100px 0px 50px 0px;width:100%;text-align:center;">
            <div class="large-12 columns">
                <form id="resto-searchform" changeLocation="true" action="<?php echo $self->context->baseUrl . 'api/collections/search.html' ?>">
                    <span class="resto-search"><input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>" style="font-size:1.5em;height:50px;max-width:500px;width:80%;"/></span>
                    <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" /> 
                </form>
            </div>
        </div>
        
        <div class="row fullWidth" style="min-height:600px;width:100%;text-align:center;color:#fff;font-size:2em;">
            <div class="large-12 columns">
                <?php if ($nbOfProducts > 0) { ?>
                Currently <?php echo $nbOfProducts;?> images available in <?php echo $nbOfCollections;?> <a href="<?php echo $self->context->baseUrl . 'collections'; ?>">collections</a>
                <?php } else { ?>
                Hey! there is nothing in the database yet
                <?php } ?>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include 'footer.php' ?>
        
        <script type="text/javascript">
            $(document).ready(function() {
                Resto.init({
                    "translation":<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                    "language":'<?php echo $self->context->dictionary->language; ?>',
                    "restoUrl":'<?php echo $self->context->baseUrl ?>',
                    "ssoServices":<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    "userProfile":<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()))) ?>
                });
            });
        </script>
    </body>
</html>
