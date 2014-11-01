<header>
    <span id="logo"><a title="<?php echo $self->context->dictionary->translate('_home'); ?>" href="<?php echo $self->context->baseUrl ?>">resto</a></span>
    <span class="breadcrumb">
    <?php if ($self->context->path !== '') {
        $splitted = explode('/', $self->context->path);
        if ($splitted[0] === 'collections' || (isset($splitted[1]) && $splitted[1] === 'collections')) {
            echo ' <a href="' . $self->context->baseUrl . 'collections">collections</a>';
        }
        if ($splitted[0] === 'api' && isset($splitted[2])) {
            echo ' > <a href="' . $self->context->baseUrl . 'api/collections/' . $splitted[2] . '/search.html" >' . $splitted[2] . '</a>';
        }
        if ($splitted[0] === 'collections' && isset($splitted[2])) {
            echo ' > <a href="' . $self->context->baseUrl . 'api/collections/' . $splitted[1] . '/search.html" >' . $splitted[1] . '</a>';
            echo ' > <a href="' . $self->context->baseUrl . 'collections/' . $splitted[1] . '/' . $splitted[2] . '" >' . $splitted[2] . '</a>';
        }
    } ?>
    </span>
    <nav>
        <ul class="no-bullet">
            <?php if (!isset($_noSearchBar)) { ?>
            <li><span class="resto-search"><input type="text" id="search" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>"></span><li>
            <?php
            if ($self->context->dictionary->language) {
                echo '<input type="hidden" name="lang" value="' . $self->context->dictionary->language . '" />';
            }
            ?>
            <?php } ?>
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
        