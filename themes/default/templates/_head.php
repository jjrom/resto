<head>
    <title><?php echo $self->context->dictionary->translate('_headerTitle'); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1,user-scalable=no,maximum-scale=1,width=device-width">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
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