<head>
    <title><?php echo $self->context->dictionary->translate('_headerTitle'); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <link rel="shortcut icon" href="<?php echo $self->context->baseUrl ?>favicon.ico" />
    <!-- CSS mapshup -->
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/mol/theme/default/style.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/mapshup/theme/default/mapshup.css" type="text/css" />
    <!-- CSS foundation -->
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/foundation/foundation.min.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/fontawesome/css/font-awesome.min.css" type="text/css" />
    <!-- CSS RESTo -->
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>themes/<?php echo $self->context->config['theme'] ?>/style.css" type="text/css" />
    <!-- jQuery -->
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/jquery/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/jquery/jquery.history.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/jquery/jquery.visible.min.js"></script>
    <!-- mapshup -->
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/mol/OpenLayers.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/mapshup/mapshup.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/lib/mapshup/config/default.js"></script>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/themes/<?php echo $self->context->config['theme'] ?>/config.js"></script>
    <!-- RESTo -->
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>/js/resto.js"></script>

    <!--[if lt IE 9]>
    <script type="text/javascript" src="<?php echo $self->context->baseUrl ?>js/lib/modernizr.min.js"></script>
    <![endif]-->
</head>