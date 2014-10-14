<header>
    <span id="logo"><a title="<?php echo $self->context->dictionary->translate('_home'); ?>" href="<?php echo $self->context->baseUrl ?>">resto</a></span>
    <span class="resto-search"><input type="text" placeholder="<?php echo $self->context->dictionary->translate('_menu_globalsearch'); ?>"></span>
    <nav>
        <ul class="no-bullet">
            <li title="<?php echo $self->context->dictionary->translate('_menu_shareOn', 'Facebook'); ?>" class="fa fa-facebook link shareOnFacebook"></li>
            <li title="<?php echo $self->context->dictionary->translate('_menu_shareOn', 'Twitter'); ?>" class="fa fa-twitter link shareOnTwitter"></li>
            <li title="<?php echo $self->context->dictionary->translate('_menu_viewCart'); ?>" class="fa fa-shopping-cart link"></li>
            <li></li>
            <?php if ($self->context->user->profile['userid'] === -1) { ?>
            <li title="<?php echo $self->context->dictionary->translate('_menu_connexion'); ?>" class="link viewUserPanel"><?php echo $self->context->dictionary->translate('_menu_connexion'); ?></li>
            <?php } else { ?>
            <li title="<?php echo $self->context->dictionary->translate('_menu_profile'); ?>" class="link gravatar center viewUserPanel"></li>
            <?php } ?>
        </ul>
    </nav>
</header>
<div class="row" style="height:50px;">
    <div class="large-12 columns"></div>
</div>