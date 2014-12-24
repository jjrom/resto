<?php
    $_noSearchBar = true;
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
    <?php include '_head.php' ?>
    <body class="bg glass">
        
        <!-- Header -->
        <?php include '_header.php' ?>

        <div class="row" style="height:100px;">
            <div class="large-12 columns"></div>
        </div>
        <div class="collections">
            <div class="row resto-collection fullWidth"> 
                <ul class="small-block-grid-1 medium-block-grid-1 large-block-grid-3">
                    <?php foreach ($self->getCollections() as $key => $collection) { ?>
                    <li id="_<?php echo $key;?>">
                        <div class="collectionItem">
                            <h1 class="center">
                                <a class="fa" href="<?php echo $self->context->baseUrl . 'api/collections/' . $key . '/search.html?lang=' . $self->context->dictionary->language; ?>">  <?php echo $collection->getOSProperty('ShortName'); ?></a>
                            </h1>
                            <h5 class='text-light center'><?php echo $self->context->dictionary->translate('_nbOfProducts', isset($statistics['collection'][$collection->name]) ? $statistics['collection'][$collection->name] : 0); ?></h5>
                            <p><?php echo $collection->getOSProperty('Description'); ?></p>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>     
        </div>

        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- scripts -->
        <?php include '_scripts.php' ?>
        
        <script type="text/javascript">
            $(document).ready(function(){
                Resto.Util.alignHeight($('.collectionItem'));
            });
        </script>
    </body>
</html>
