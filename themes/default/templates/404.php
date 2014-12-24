<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include '_head.php' ?>
    <body class="bg-404 glass">
        
        <!-- Header -->
        <?php include '_header.php' ?>

        <!-- Not found -->
        <div class="row fullWidth" style="padding:100px 0px 50px 0px;width:100%;text-align:center;">
            <div class="large-12 columns">
                <h1 class="text-light"><?php echo $self->context->dictionary->translate('_pageNotFoundTitle')?></h1>
                <p class="text-light"><?php echo $self->context->dictionary->translate('_pageNotFoundDescription')?></p>
                <form id="resto-searchform" changeLocation="true" action="<?php echo $self->context->baseUrl . 'api/collections/search.html' ?>">
                    <span class="resto-search"><input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>" style="font-size:1.5em;height:50px;max-width:500px;width:80%;"/></span>
                    <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" /> 
                </form>
            </div>
        </div>

        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- scripts -->
        <?php include '_scripts.php' ?>
    </body>
</html>