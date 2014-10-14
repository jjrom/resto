<header <?php echo 'class="opaque"'?>>
<?php if ($self->context->user->profile['userid'] === -1) { ?>
    <span id="logo"><a title="<?php echo $self->context->dictionary->translate('_home'); ?>" href="<?php echo $self->context->baseUrl ?>">resto</a></span>
    <nav>
        <ul class="no-bullet">
            <li title="<?php echo $self->context->dictionary->translate('_menu_shareOn', 'Facebook'); ?>" class="fa fa-facebook link shareOnFacebook"></li>
            <li title="<?php echo $self->context->dictionary->translate('_menu_shareOn', 'Twitter'); ?>" class="fa fa-twitter link shareOnTwitter"></li>
            <li title="<?php echo $self->context->dictionary->translate('_menu_viewCart'); ?>" class="fa fa-shopping-cart link"></li>
            <li></li>
            <li title="<?php echo $self->context->dictionary->translate('_menu_connexion'); ?>" class="link viewUserPanel"><?php echo $self->context->dictionary->translate('_menu_connexion'); ?></li>
        </ul>
    </nav>
<?php } else { ?>
    <span id="logo"><a title="<?php echo $self->context->dictionary->translate('_home'); ?>" href="<?php echo $self->context->baseUrl ?>">resto</a></span>
    <nav>
        <ul class="no-bullet">
            <li title="<?php echo $self->context->dictionary->translate('_menu_shareOn', 'Facebook'); ?>" class="fa fa-facebook link shareOnFacebook"></li>
            <li title="<?php echo $self->context->dictionary->translate('_menu_shareOn', 'Twitter'); ?>" class="fa fa-twitter link shareOnTwitter"></li>
            <li title="<?php echo $self->context->dictionary->translate('_menu_viewCart'); ?>" class="fa fa-shopping-cart link"></li>
            <li></li>
            <li></li>
            
            <li title="<?php echo $self->context->dictionary->translate('_menu_profile'); ?>" class="link gravatar center viewUserPanel"></li>
        </ul>
    </nav>
<?php } ?>
</header>
<div class="row fullWidth fixed head" >
    <a href="#" class="show-for-large-up" style="margin:15px; float: left; font-weight: bold; font-size: 150%;">RESTo</a>
    <a href="#" class="show-for-large-up" style="margin:20px; float: left;"><?php echo $self->context->dictionary->translate('_menu_informations'); ?></a>
    <a id="didacticiel" class="show-for-large-up" href="<?php echo $self->context->baseUrl .  'didacticiel/'; ?>" style="margin:20px; float: left;"><?php echo $self->context->dictionary->translate('_menu_didacticiel'); ?></a>
    <a href="<?php echo $self->context->baseUrl .  'administration/'; ?>" class="show-for-large-up" style="margin:20px; float: left;"><?php echo $self->context->dictionary->translate('_menu_administration'); ?></a>
    <a id="connexion" href="#" style="margin:20px; float: right;"><?php echo $self->context->dictionary->translate('_menu_connexion'); ?></a>
    <input id="global_search" type="text" placeholder="<?php echo $self->context->dictionary->translate('_menu_globalsearch'); ?>" style="float: right; width: 15%; margin: 10px;">
</div>