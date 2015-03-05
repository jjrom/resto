<header>
    <form id="resto-searchform" action="<?php echo $self->context->baseUrl . 'api/collections/' . (isset($self->collection->name) ? $self->collection->name . '/' : '') . 'search.json' ?>" style="margin:0px;padding:0px;">
    <div class="show-for-medium-up" style="float:left;">
        <span class="logo"><a href="http://www.cnes.fr" target="_blank"><img style="position:relative;top:-5px;" width="100" src="<?php echo $self->context->baseUrl . 'themes/default/img/logo_logo.png'; ?>"/></a> | <a href="<?php echo $self->context->baseUrl;?>"><?php echo $self->context->config['title'];?></a></span>
        <?php if (isset($_searchBar)) { ?>
        <span class="resto-search">
            <input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>"/>
            <input type="hidden" name="lang" value="<?php echo $self->context->dictionary->language?>" />
        </span>
        <span class="panel-triggers">
            <a href="#panel-list" class="fa fa-th resto-panel-trigger active" id="panel-list-trigger" title="<?php echo $self->context->dictionary->translate('_menu_list'); ?>"></a>&nbsp;<a href="#panel-map" class="fa fa-map-marker resto-panel-trigger" id="panel-map-trigger" title="<?php echo $self->context->dictionary->translate('_menu_map'); ?>"><a href="#panel-metadata" class="fa fa-info resto-panel-trigger" id="panel-metadata-trigger" title="" style="display:none;"></a>
        </span>
        <?php } ?>
    </div>
    <nav class="show-for-medium-up">
        <ul>
            <li><a href="<?php echo $self->context->baseUrl . 'collections' ?>"><?php echo $self->context->dictionary->translate('_menu_collections'); ?></a></li>
            <?php if ($self->user->profile['groupname'] === 'admin'){ ?>
            <li><a href="<?php echo $self->context->baseUrl . '/administration' ?>"><?php echo $self->context->dictionary->translate('_administration'); ?></a></li>
            <?php } ?>
            <?php if ($self->user->profile['userid'] === -1) { ?>
            <li><a href="#" data-reveal-id="displayLogin"><?php echo $self->context->dictionary->translate('_menu_signin'); ?></a></li>
            <li><a class="hilite" href="#" data-reveal-id="displayRegister"><?php echo $self->context->dictionary->translate('_menu_signup'); ?></a></li>
            <?php } else { ?>
            <li><a href="#" data-reveal-id="displayCart"><span class="fa fa-shopping-cart"></span>&nbsp;<?php echo $self->context->dictionary->translate('_menu_cart'); ?></a></li>
            <li><a class="gravatar" href="#" data-reveal-id="displayProfile" title="<?php echo $self->context->dictionary->translate('_menu_profile'); ?>"></a></li>
            <?php } ?>
        </ul> 
    </nav>
    <a class="show-for-small-down show-small-menu small-logo fa fa-3x fa-bars text-dark"></a>
    <nav class="show-for-small-down small-menu">
        <ul>
            <?php if ($self->user->profile['userid'] === -1) { ?>
            <li><a href="#" data-reveal-id="displayLogin"><?php echo $self->context->dictionary->translate('_menu_signin'); ?></a></li>
            <li><a class="hilite" href="#" data-reveal-id="displayRegister"><?php echo $self->context->dictionary->translate('_menu_signup'); ?></a></li>
            <?php } else { ?>
            <li><a class="gravatar" href="#" data-reveal-id="displayProfile" title="<?php echo $self->context->dictionary->translate('_menu_profile'); ?>"></a></li>
            <?php } ?>
        </ul>
    </nav>
    </form>
</header>

<div id="small-menu" hidden="true">
    <ul>
        <li><a href="<?php echo $self->context->baseUrl;?>"><?php echo $self->context->config['title'];?></a></li>
        <?php if (isset($self->collection)) { ?>
        <li><a class="" href="#"><?php echo $self->collection->name ?></a>
        <?php } ?>
        <li><a href="<?php echo $self->context->baseUrl . 'collections' ?>"><?php echo $self->context->dictionary->translate('_menu_collections'); ?></a></li>
        <?php if ($self->user->profile['groupname'] === 'admin'){ ?>
        <li><a href="<?php echo $self->context->baseUrl . '/administration' ?>"><?php echo $self->context->dictionary->translate('_administration'); ?></a></li>
        <?php } ?>
        <?php if ($self->user->profile['userid'] !== -1) { ?>
        <li><a href="#" data-reveal-id="displayCart"><span class="fa fa-shopping-cart"></span>&nbsp;<?php echo $self->context->dictionary->translate('_menu_cart'); ?></a></li>
        <?php } ?>
    </ul>
</div>

<div id="displayRegister" class="reveal-modal small" data-reveal>
    <div class="large-12 columns greenfield">
        <div class="padded-top center">
            <h1 class="fat upper text-light"><?php echo $self->context->dictionary->translate('_menu_signup')?></h1>
            <p><?php echo $self->context->dictionary->translate('_menu_signup_explain')?></p>
        </div>
    </div>
    <form action="#">
        <div class="large-12 columns greenfield padded-left padded-right">
            <label class="small fat text-light"><?php echo $self->context->dictionary->translate('_userName');?></label>
            <input id="userName" class="input-text" type="text"/>
        </div>
        <div class="large-6 columns greenfield padded-left padded-right">
            <label class="small fat text-light"><?php echo $self->context->dictionary->translate('_firstName');?></label>
            <input id="givenName" class="input-text" type="text"/>
        </div>
        <div class="large-6 columns greenfield padded-left padded-right">
            <label class="small fat text-light"><?php echo $self->context->dictionary->translate('_lastName');?></label>
            <input id="lastName" class="input-text" type="text"/>
        </div>
        <div class="large-6 columns greenfield padded-left padded-right">
            <label class="small fat text-light"><?php echo $self->context->dictionary->translate('_email');?></label>
            <input id="r_userEmail" class="input-text" type="text"/>
        </div>
        <div class="large-6 columns greenfield padded-left padded-right">
            <label class="small fat text-light"><?php echo $self->context->dictionary->translate('_password');?></label>
            <input id="userPassword1" class="input-password" type="password"/>
        </div>
        <div class="large-12 columns padded-top padded-left padded-right center">
            <a class="button radius register signUp"><?php echo $self->context->dictionary->translate('_createAccount');?></a>
        </div>
    </form>
    <a class="text-light close-reveal-modal">&#215;</a>
</div>
<div id="displayLogin" class="reveal-modal medium darkfield" data-reveal>
    <div class="large-6 columns orangefield">
        <div class="padded-top center">
            <h3 class="fat upper text-light"><?php echo $self->context->dictionary->translate('_menu_signin')?></h3>
        </div>
        <form action="#">
            <label class="small fat text-light"><?php echo $self->context->dictionary->translate('_email');?></label>
            <input id="userEmail" type="text"/>
            <label class="small fat text-light"><?php echo $self->context->dictionary->translate('_password');?></label>
            <input id="userPassword" type="password"/>
            <div class="center"><a class="button radius signIn"><?php echo $self->context->dictionary->translate('_login');?></a></div>
        </form>
    </div>
    <div class="large-6 columns">
        <div class="padded-top center">
            <h3 class="fat upper text-light"><?php echo $self->context->dictionary->translate('_menu_signinwith')?></h3>
        </div>
        <div class="signWithOauth center"></div>
    </div>
    <a class="text-light close-reveal-modal">&#215;</a>
</div>
<?php if ($self->user->profile['userid'] !== -1) { ?>
<div id="displayCart" class="reveal-modal full" data-reveal style="max-height: 80%;overflow:auto;">
    <div class="large-12 columns padded">
        <h1 class="text-dark center small"><?php echo $self->context->dictionary->translate('_myCart');?></h1>
    </div>
    <div class="large-12 columns resto-cart-content padded center"></div>
    <a class="text-dark close-reveal-modal">&#215;</a>
</div>
<?php } ?>
<div id="displayProfile" class="reveal-modal small full" data-reveal style="max-height: 80%;overflow:auto;"></div>
<div id="dialog" class="reveal-modal" data-reveal></div>