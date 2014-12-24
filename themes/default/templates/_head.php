<head>
    <title><?php echo $self->context->dictionary->translate('_headerTitle'); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <link rel="shortcut icon" href="<?php echo $self->context->baseUrl ?>favicon.ico" />
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/foundation/normalize.css" type="text/css" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:300' rel='stylesheet' type='text/css'>
    <?php if (!isset($_noMap)) { ?>
    <!-- OL3 -->
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/ol3/ol.css" type="text/css" />
    <?php } ?>
    <!-- CSS foundation -->
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/foundation/foundation.min.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/fontawesome/css/font-awesome.min.css" type="text/css" />
    <!-- CSS RESTo -->
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>themes/<?php echo $self->context->config['theme'] ?>/style.css" type="text/css" />
    
    <!--[if lt IE 9]>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>js/lib/foundation/vendor/modernizr.js"></script>
    <![endif]-->
</head>