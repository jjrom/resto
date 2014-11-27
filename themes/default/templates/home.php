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

        <!-- Search bar -->
        <div class="row fullWidth" style="padding:100px 0px 50px 0px;width:100%;text-align:center;">
            <div class="large-12 columns">
                <h1 class="text-light"><?php echo $self->context->dictionary->translate('_homeSearchTitle')?></h1>
                <form id="resto-searchform" changeLocation="true" action="<?php echo $self->context->baseUrl . 'api/collections/search.html' ?>">
                    <span class="resto-search"><input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>" style="font-size:1.5em;height:50px;max-width:800px;width:80%;"/></span>
                    <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" /> 
                </form>
                <p class="text-light"><?php echo $self->context->dictionary->translate('_eg')?> "<a href="<?php echo $self->context->baseUrl . 'api/collections/search.html?q=' . $self->context->dictionary->translate('_homeSearchExample') . '&lang=' . $self->context->dictionary->language ?>"><?php echo $self->context->dictionary->translate('_homeSearchExample')?></a>"</p>
            </div>
        </div>

        <!-- Statistics -->
        <div class="center padded">
            <?php if ($nbOfProducts > 0) { ?>
            <ul class="small-block-grid-1 large-block-grid-3" style="padding-top:100px">
                <li>
                    <div class="resto-box"> 
                        <a href="<?php echo $self->context->baseUrl . 'collections'; ?>">
                            <h1 class="text-light"><?php echo $self->context->dictionary->translate('_numberOfCollections'); ?></h1>
                            <h1 class="text-light"><?php echo $nbOfCollections;?></h1>
                        </a>
                    </div>
                </li>
                <li>
                    <div class="resto-box"> 
                        <a href="<?php echo $self->context->baseUrl . 'api/collections/search.html'; ?>">
                            <h1 class="text-light"><?php echo $self->context->dictionary->translate('_numberOfProducts'); ?></h1>
                            <h1 class="text-light"><?php echo $nbOfProducts;?></h1>
                        </a>
                    </div>
                </li>
                <li>
                    <div class="resto-box">
                        <a href="<?php echo $self->context->baseUrl . 'TODO'; ?>">
                            <h1 class="text-light"><?php echo $self->context->dictionary->translate('_statistics'); ?></h1>
                            <h1 class="text-light fa fa-3x fa-bar-chart"></h1>
                        </a>
                    </div>
                </li>
            </ul>
            <?php } else { ?>
                Hey! there is nothing in the database yet
            <?php } ?>
        </div>

        <!-- Footer -->
        <?php include 'footer.php' ?>
           
        <script type="text/javascript">
            Resto.init({
                "translation":<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                "language":'<?php echo $self->context->dictionary->language; ?>',
                "restoUrl":'<?php echo $self->context->baseUrl ?>',
                "ssoServices":<?php echo json_encode($self->context->config['ssoServices']) ?>,
                "userProfile":<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()),  array('cart' => isset($_SESSION['cart']) ? $_SESSION['cart'] : array()))) ?>
            });
            Resto.Util.alignHeight($('.resto-box'));
        </script>
    </body>
</html>
