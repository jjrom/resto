<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body class="glass">
        
        <!-- Header -->
        <form id="resto-searchform" changeLocation="true" action="<?php echo $self->context->baseUrl . 'api/collections/search.html' ?>">
        <?php include 'header.php' ?>
        </form>
        
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
                                <a class="fa" href="<?php echo $self->context->baseUrl . 'api/collections/' . $key . '/search.html?lang=' . $self->context->dictionary->language; ?>">  <?php echo $collection->osDescription[$self->context->dictionary->language]['ShortName']; ?></a>
                            </h1>
                            <p><?php echo $collection->osDescription[$self->context->dictionary->language]['Description']; ?></p>
                            <h1 class="center bottom">
                                <?php if ($self->context->user->canPut($key)) { ?><a class="fa fa-edit button bggreen updateCollection admin" href="#" collection="<?php echo $key; ?>" title="<?php echo $self->context->dictionary->translate('_update'); ?>"></a><?php } ?>
                                <?php if ($self->context->user->canDelete($key)) { ?><a class="fa fa-trash-o button bgred removeCollection admin" href="#" collection="<?php echo $key; ?>" title="<?php echo $self->context->dictionary->translate('_remove'); ?>"></a><?php } ?>
                            </h1>
                        </div>
                    </li>
                    <?php } ?>
                    <?php if ($self->context->user->canPost()) { ?>
                    <li>
                        <div id="dropZone" class="_dropCollection collectionItem">
                            <h1 class="center">
                                <?php echo $self->context->dictionary->translate('_addCollection'); ?>
                            </h1>
                            <p class="center">+</p>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>     
        </div>
        
        <!-- Footer -->
        <?php include 'footer.php' ?>
       
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
            $(document).scroll(function(){
                if ($(this).scrollTop() > 100) {
                    $('body').removeClass('glass');
                }
                else {
                    $('body').addClass('glass');
                }
            });
            
            /*
            $(window).bind('resize', function() {
                fixMinSize($('.collectionItem'));
            });
            */  
            
            fixMinSize($('.collectionItem'));
            function fixMinSize(groupOfItem){
                var tallest = 0;
                groupOfItem.each(function(){
                    var thisHeight = $(this).height() + 20;
                    if(thisHeight > tallest){
                        tallest = thisHeight;
                    }
                });
                groupOfItem.css('height', tallest);
            }
            
        </script>
        <div align="center" ><img src="<?php echo $self->context->baseUrl ?>themes/<?php echo $self->context->config['theme'] ?>/img/bg.jpg" border="0" class="bg" ></div>
    </body>
</html>
