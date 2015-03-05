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
    <?php include '_head.php' ?>
    <body class="bg glass">

        <!-- Header -->
        <?php include '_header.php' ?>

        <!-- Search bar -->
        <div class="row fullWidth" style="padding:100px 0px 50px 0px;width:100%;text-align:center;">
            <div class="large-12 columns">
                <h1 class="text-light"><?php echo $self->context->dictionary->translate('_homeSearchTitle')?></h1>
                <form id="resto-searchform" changeLocation="true" action="<?php echo $self->context->baseUrl . 'api/collections/S1/search.html' ?>">
                    <span class="resto-search"><input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>" style="font-size:1.5em;height:50px;max-width:800px;width:80%;"/></span>
                    <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" /> 
                </form>
                <p class="text-light"><?php echo $self->context->dictionary->translate('_eg')?> "<a href="<?php echo $self->context->baseUrl . 'api/collections/S1/search.html?q=' . $self->context->dictionary->translate('_homeSearchExample') . '&lang=' . $self->context->dictionary->language ?>"><?php echo $self->context->dictionary->translate('_homeSearchExample')?></a>"</p>
            </div>
        </div>
        <!--
        <div class="center">
            <?php if ($nbOfProducts > 0) { ?>
            <div class="row fullWidth" style="padding:1% 10%;"> 
                <ul class="small-block-grid-1 large-block-grid-2">
                    <li>
                        <div class="resto-box mainstats"> 
                            <a href="<?php echo $self->context->baseUrl . 'collections'; ?>">
                                <h1 class="text-light"><?php echo $self->context->dictionary->translate('_numberOfCollections'); ?></h1>
                                <h1 class="text-light"><?php echo $nbOfCollections;?></h1>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="resto-box mainstats"> 
                            <a href="<?php echo $self->context->baseUrl . 'api/collections/S1/search.html'; ?>">
                                <h1 class="text-light"><?php echo $self->context->dictionary->translate('_numberOfProducts'); ?></h1>
                                <h1 class="text-light"><?php echo $nbOfProducts;?></h1>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
            <?php } else { ?>
                Hey! there is nothing in the database yet
            <?php } ?>
        </div>
        -->
        <div class="collections">
            <div class="row fullWidth" style="padding:1% 10%;">
                <ul class="small-block-grid-1 medium-block-grid-1 large-block-grid-3">
                    <?php foreach ($statistics as $key => $stats) { ?>
                    <li>
                        <div class="resto-box detailedstats" style="display:block">
                            <h2 class="text-light"><?php echo $self->context->dictionary->translate($key === 'continent' ? 'location' : $key); ?></h2>
                            <?php if ($key === 'continent') { ?>
                            <h4 class='text-light'>
                                <?php foreach ($stats as $item => $count) { ?>
                                    <?php
                                        $value = $self->context->dictionary->getKeywordFromValue($item, $key);
                                        if (!empty($value)) {
                                        ?>
                                <a href="<?php echo $self->context->baseUrl . 'api/collections/S1/search.html?q=' . urlencode(RestoUtil::quoteIfNeeded($value)) . '&lang=' . $self->context->dictionary->language ?>"><span style="white-space:nowrap;"><img width="110px" height="110px" src="<?php echo $self->context->baseUrl . 'themes/default/img/world/' . str_replace(' ', '', $item) . '.png'; ?>"/><?php echo '(' . $count . ')'; ?></a></span>
                                <?php }} ?>
                            </h4>
                            <?php } else { ?>
                                <?php foreach ($stats as $item => $count) {
                                    if ($key === 'collection') { ?>
                            <h4 class='text-light'><a href="<?php echo $self->context->baseUrl . 'api/collections/' . $item . '/search.html?lang=' . $self->context->dictionary->language ?>"><?php echo $item . ' (' . $count . ')'; ?></a></h4>
                                    <?php } else if ($key === 'year' || $key === 'processingLevel' || $key === 'productType' || $key === 'platform') { ?>
                            <h4 class='text-light'><a href="<?php echo $self->context->baseUrl . 'api/collections/S1/search.html?q=' . urlencode(RestoUtil::quoteIfNeeded($item)) .'&lang=' . $self->context->dictionary->language ?>"><?php echo $item . ' (' . $count . ')'; ?></a></h4>
                                    <?php } else {
                                    $value = $self->context->dictionary->getKeywordFromValue($item, $key); ?>
                            <h4 class='text-light'><a href="<?php echo $self->context->baseUrl . 'api/collections/S1/search.html?q=' . urlencode(RestoUtil::quoteIfNeeded($value)) . '&lang=' . $self->context->dictionary->language ?>"><?php echo $value . ' (' . $count . ')'; ?></a></h4>
                                <?php }}} ?>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>     
        </div>

        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- Scripts -->
        <?php include '_scripts.php' ?>
        <script type="text/javascript">
            $(document).ready(function(){
                Resto.Util.alignHeight($('.detailedstats'));
                Resto.Util.alignHeight($('.mainstats'));
            });
        </script>
    </body>
</html>
