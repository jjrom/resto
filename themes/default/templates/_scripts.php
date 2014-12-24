<!-- Dependencies -->
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/dependencies.min.js"></script>
<?php if (!isset($_noMap)) { ?>
<!-- OpenLayers3 -->
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/ol3/ol.js"></script>
<?php } ?>
<!-- RESTo -->
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.js"></script>
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.util.js"></script>
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.map.js"></script>
<!--
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.min.js"></script>
-->
<script type="text/javascript">
    $(document).ready(function(){
        
        $(document).foundation().scroll(function () {
            $(this).scrollTop() > 200 ? $('#gototop').show() : $('#gototop').hide();
        });
        
        $('#gototop').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $('html, body').animate({scrollTop:0}, 'fast');
        });
        
        Resto.init({
            "issuer":<?php echo isset($_issuer) ? '"' . $_issuer . '"' : 'null' ?>,
            "translation":<?php echo json_encode($self->context->dictionary->getTranslation()) ?>,
            "language":'<?php echo $self->context->dictionary->language; ?>',
            "restoUrl":'<?php echo $self->context->baseUrl ?>',
            "ssoServices":<?php echo json_encode($self->context->config['ssoServices']) ?>,
            "userProfile":<?php echo json_encode(!isset($_SESSION['profile']) ? array('userid' => -1) : array_merge($_SESSION['profile'], array('rights' => isset($_SESSION['rights']) ? $_SESSION['rights'] : array()), array('cart' => isset($_SESSION['cart']) ? $_SESSION['cart'] : array()))) ?>
            }<?php echo isset($_data) ? ',' .  $_data : '' ?>
        );      
    });
</script>