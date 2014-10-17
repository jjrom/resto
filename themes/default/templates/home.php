<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head_nomap.php' ?>
    <body class="glass">
        
        <!-- Header -->
        <?php include 'header_nosearch.php' ?>
        
        <div class="row" style="height:250px;">
            <div class="large-12 columns"></div>
        </div>
        
        <form id="resto-searchform" changeLocation="true" action="<?php echo $self->context->baseUrl . 'api/collections/search.html' ?>">
        <div class="row fullWidth" style="width:100%;text-align:center;">
            <div class="large-12 columns">
                <span class="resto-search"><input id="search" class="darker" type="text" name="q" placeholder="<?php echo $self->context->dictionary->translate('_menu_search'); ?>" value="<?php echo isset($self->context->query['q']) ? $self->context->query['q'] : ''; ?>" style="font-size:1.5em;height:50px;width:500px;"></span>
            </div>
        </div>
        </form>
        
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
