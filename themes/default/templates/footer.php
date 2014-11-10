<div class="footer lightfield">

</div>
<div class="footer-bottom lightfield">
    Powered by <a href="http://github.com/jjrom/resto">RESTo</a>, <a href="http://github.com/jjrom/itag">iTag</a> and <a href="http://mapshup.info">mapshup</a>
</div>
<a id="gototop" class="fa fa-3x fa-chevron-circle-up round"></a>
<!-- Foundation -->
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/foundation/vendor/jquery.js"></script>
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/foundation/vendor/fastclick.js"></script>
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/foundation/foundation.min.js"></script>
<!-- jQuery plugins -->
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/jquery/jquery.history.js"></script>
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/jquery/jquery.visible.min.js"></script>
<?php if (!isset($_noMap)) { ?>
    <!-- mapshup -->
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/mol/OpenLayers.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/mapshup/mapshup.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/mapshup/config/default.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/themes/<?php echo $self->context->config['theme'] ?>/config.js"></script>
<?php } ?>
<!-- RESTo -->
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.js"></script>
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.util.js"></script>
<script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.map.js"></script>
<script type="text/javascript">
    $(document).foundation().scroll(function () {
        $(this).scrollTop() > 200 ? $('#gototop').show() : $('#gototop').hide();
    });
    $('#gototop').click(function(e){
       e.preventDefault();
       e.stopPropagation();
       $('html, body').animate({scrollTop:0}, 'fast');
    });
</script>