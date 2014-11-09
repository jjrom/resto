<head>
    <title><?php echo $self->context->dictionary->translate('_headerTitle'); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <link rel="shortcut icon" href="<?php echo $self->context->baseUrl ?>favicon.ico" />
    <?php if (!isset($_noMap)) { ?>
    <!-- CSS mapshup -->
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/mol/theme/default/style.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo $self->context->baseUrl ?>js/lib/mapshup/theme/default/mapshup.css" type="text/css" />
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