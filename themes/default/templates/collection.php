<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include '_head.php' ?>
    <body>
        
        <!-- Header -->
        <?php include '_header.php' ?>
        
        <div class="row fullWidth resto-title">
            <div class="large-12 columns">
                <h1><a href="http://jjrom.github.io/resto/"><?php echo $self->context->dictionary->translate('_headerTitle'); ?></a></h1>
                <p><?php echo $self->context->dictionary->translate('_headerDescription'); ?></p>
            </div>
        </div>
        <div class="collections">
            <div class="row fullWidth resto-collection" id="_<?php echo $self->name;?>"> 
                <div class="large-12 columns left">
                    <h1>
                        <a class="fa fa-search" href="<?php echo $self->context->baseUrl . 'api/collections/' . $self->name . '/search.html?lang=' . $self->context->dictionary->language; ?>">  <?php echo $self->getOSProperty('ShortName'); ?></a><br/>
                    </h1>
                    <p><?php echo $self->getOSProperty('Description'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- scripts -->
        <?php include '_scripts.php' ?>
    </body>
    
</html>
