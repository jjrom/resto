<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body>
        
        <!-- Header -->
        <?php include 'header.php' ?>
      
        <!-- Location content (Landcover) -->
        <div class="row resto-resource lightfield">
            <div class="large-12 columns">
                <ul>
                    <?php foreach ($self->getItems() as $key => $value) { ?>
                    <li><?php echo $key;?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include 'footer.php' ?>
        
        <script type="text/javascript">
            $(document).ready(function() {
                Resto.init({
                    "translation":<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
                    "language":'<?php echo $self->context->dictionary->language; ?>',
                    "restoUrl":'<?php echo $self->context->baseUrl ?>',
                    "ssoServices":<?php echo json_encode($self->context->config['ssoServices']) ?>,
                    "userProfile":<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()))) ?>
                });
            });
        </script>
    </body>
</html>
