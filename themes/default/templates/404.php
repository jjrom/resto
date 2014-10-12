<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $self->context->dictionary->language ?>">
    <?php include 'head.php' ?>
    <body>
        
        <!-- Header -->
        <?php include 'header.php' ?>
      
        <div class="row" style="height:50px;">
            <div class="large-12 columns"></div>
        </div>
        
        <!-- Not found -->
        <div class="row center">
            <div class="large-12 columns">
                <h1>Oh no! Page not found</h1>
                <p><a href="<?php echo $self->context->baseUrl ?>">Go back to home page</a></p>
            </div>
        </div>
       
        <!-- Footer -->
        <?php include 'footer.php' ?>
        
    </body>
</html>