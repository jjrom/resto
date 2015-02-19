<?php if (!isset($_noBreadcrumb) || !isset($_noSearchBar)){ ?>
<div style="width: 100%; height: 45px; background-color: #28353D; border-top:1.5px solid white;">
    <?php if (!isset($_noBreadcrumb)){ ?>    
    <span <?php if(!isset($_noSearchBar)){ ?>class="show-for-medium-up"<?php } ?> style="margin-left: 20px; margin-top: 13px; float: left; color: black;">
        <?php
        $i = 0;
        $url = $self->context->baseUrl . 'administration' ;
        echo '<a href="' . $url . '?lang=' . $self->context->dictionary->language . '">administration</a>';
        while ($i < sizeof($self->segments)) {
            $url = $url . '/' . $self->segments[$i] . '?lang=' . $self->context->dictionary->language;
            echo ' > <a href="' . $url . '">' . $self->segments[$i] . '</a>';
            ++$i;
        }
        ?>
    </span>
    <?php } ?>

    <?php if (!isset($_noSearchBar)) { ?>
    <span class="resto-search" style="float: right;"><input type="text" id="search" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>"></span>
    <?php } ?>
</div>
<?php }
            
