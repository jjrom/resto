<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body class="glass homepage">
        
        <!-- Header -->
        <header>
        <span id="logo"><a title="<?php echo $self->context->dictionary->translate('_home'); ?>" href="<?php echo $self->context->baseUrl ?>">resto</a></span>
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
        <div class="row" style="height:250px;">
            <div class="large-12 columns"></div>
        </div>
        
        <div class="row fullWidth" style="width:100%;text-align:center;">
            <div class="large-12 columns">
                <span class="resto-search"><input class="darker" type="text" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" style="font-size:1.5em;height:50px;width:500px;"></span>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="position:fixed;bottom:0px;text-align:center;width:100%;">
        <?php include 'footer.php' ?>
        </div>
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
    </script>
    <div align="center" ><img src="<?php echo $self->context->baseUrl ?>themes/<?php echo $self->context->config['theme'] ?>/img/bg.jpg" border="0" class="bg" ></div>
    </body>
    
</html>
