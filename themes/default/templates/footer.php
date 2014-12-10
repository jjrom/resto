<div class="footer">
    Powered by <a href="http://github.com/jjrom/resto2">resto</a>, <a href="http://github.com/jjrom/itag">iTag</a> and <a href="http://mapshup.info">mapshup</a>
</div>
<a id="gototop" class="fa fa-5x fa-chevron-circle-up round"></a>
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
    $(document).foundation().scroll(function () {
        $(this).scrollTop() > 200 ? $('#gototop').show() : $('#gototop').hide();
    });
    $(document).ready(function(){
        $('#gototop').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $('html, body').animate({scrollTop:0}, 'fast');
        });         
    });
</script>