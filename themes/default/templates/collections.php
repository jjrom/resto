<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body>
        
        <!-- Header -->
        <?php include 'header.php' ?>
        
        <div class="row" style="height:35px;">
            <div class="large-12 columns"></div>
        </div>
        <div class="row fullWidth resto-title">
            <div class="large-12 columns">
                <h1><a href="http://jjrom.github.io/resto/"><?php echo $self->context->dictionary->translate('_headerTitle'); ?></a></h1>
                <p><?php echo $self->context->dictionary->translate('_headerDescription'); ?></p>
            </div>
        </div>
        <div class="collections">
            <?php
            $left = false;
            foreach ($this->getCollections() as $key => $collection) {
                $left = !$left;
                ?>
                <div class="row fullWidth resto-collection" id="_<?php echo $key;?>"> 
                    <div class="large-12 columns <?php echo $left ? 'left' : 'right' ?>">
                        <h1>
                            <a class="fa fa-search" href="<?php echo $self->context->baseUrl . 'search/collections/' . $key . '.html?lang=' . $self->context->dictionary->language; ?>">  <?php echo $this->osDescription[$self->context->dictionary->language]['ShortName']; ?></a><br/>
                            <?php if ($self->context->user->canPut($key)) { ?><a class="fa fa-edit button bggreen updateCollection admin" href="#" collection="<?php echo $key; ?>" title="<?php echo $self->context->dictionary->translate('_update'); ?>"></a><?php } ?>
                            <?php if ($self->context->user->canDelete($key)) { ?><a class="fa fa-moon-o button bgorange deactivateCollection admin" href="#" collection="<?php echo $key; ?>" title="<?php echo $self->context->dictionary->translate('_deactivate'); ?>"></a><?php } ?>
                            <?php if ($self->context->user->canDelete($key)) { ?><a class="fa fa-trash-o button bgred removeCollection admin" href="#" collection="<?php echo $key; ?>" title="<?php echo $self->context->dictionary->translate('_remove'); ?>"></a><?php } ?>
                        </h1>
                        <p><?php echo $this->osDescription[$self->context->dictionary->language]['Description']; ?></p>
                    </div>
                </div>
            <?php } ?>
            <?php if ($self->context->user->canPost()) { ?>
            <div class="row fullWidth resto-admin">
                <div class="large-12 columns center">
                    <div id="dropZone" class="_dropCollection"><h1><?php echo $self->context->dictionary->translate('_addCollection'); ?></h1><span class="fa fa-arrow-down"></span> <?php echo $self->context->dictionary->translate('_dropCollection'); ?> <span class="fa fa-arrow-down"></span></div>
                </div>
            </div>
            <?php } ?>       
        </div>
        
        <!-- Footer -->
        <?php include 'footer.php' ?>
        
    </body>
</html>
