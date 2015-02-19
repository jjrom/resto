<?php
    $_noSearchBar = true;
    $_noMap = true;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <?php include '_head.php' ?>
    <body style="overflow-x: hidden;">
        
        <!-- Header -->
        <?php include '_header.php' ?>
        
        <!-- Breadcrumb -->
        <?php include 'breadcrumb.php' ?>
        
        <div class="row" >
            <ul class="small-block-grid-1 large-block-grid-2">
                <li>
                    <fieldset>
                        <legend><?php echo $self->context->dictionary->translate('_a_profile'); ?></legend>
                        <h3>
                            <?php echo $self->_user->profile['userid'] . ' - ' . $self->_user->profile['username'] . ' ' . $self->_user->profile['lastname'] ?>
                        </h3>
                        <p>
                        <?php
                        echo $self->_user->profile['email'] . ' <br/>';
                        echo $self->context->dictionary->translate('_a_groupname') . ' : ' . $self->_user->profile['groupname'] . ' <br/>';
                        echo $self->context->dictionary->translate('_a_username') . ' : ' . $self->_user->profile['username'] . ' <br/>';
                        echo $self->context->dictionary->translate('_a_lastname') . ' : ' . $self->_user->profile['lastname'] . ' <br/>';
                        echo $self->context->dictionary->translate('_a_givenname') . ' : ' . $self->_user->profile['givenname'] . ' <br/>';
                        echo $self->context->dictionary->translate('_a_registrationdate') . ' : ' . $self->_user->profile['registrationdate'] . ' <br/>';
                        ?>
                        </p>      
                    </fieldset>
                </li>
                <li>
                    <div style="padding-top: 25px">
                        <ul class="small-block-grid-1 large-block-grid-1">
                            <li>
                                <?php if ($self->_user->profile['activated'] == 1) { ?>
                                    <a id="deactivateButton" href="#" class="button expand [tiny small large]"><?php echo $self->context->dictionary->translate('_a_activated'); ?></a>
                                <?php } else { ?>
                                    <a id="activateButton" href="#" class="button expand [tiny small large]" style="background-color: red"><?php echo $self->context->dictionary->translate('_a_deactivated'); ?></a>
                                <?php } ?>
                            </li>
                            <li>
                                <a href="<?php echo $self->context->baseUrl . 'administration/users/' . $self->_user->profile['userid'] . "/history" . '?lang=' . $self->context->dictionary->language; ?>" class="button expand [tiny small large]"><?php echo $self->context->dictionary->translate('_a_showfullhistory'); ?></a>
                            </li>
                       
                            
                            
                            <li>
                                <?php if ($self->_user->profile['groupname'] === 'admin') { ?>
                                    <a id="setGroupDefault" href="#" class="button expand [tiny small large]"><?php echo $self->context->dictionary->translate('_a_set_default_as_group'); ?></a>
                                <?php } else { ?>
                                    <a id="setGroupAdmin" href="#" class="button expand [tiny small large]"><?php echo $self->context->dictionary->translate('_a_set_admin_as_group'); ?></a>
                                <?php } ?>
                            </li>
                        </ul>
                        
                        <a id="_show_advanced_rights" class="button expand tiny"><?php echo $self->context->dictionary->translate('_a_show_advanced_rights'); ?></a>
                        <a id="_hide_advanced_rights" class="button expand tiny"><?php echo $self->context->dictionary->translate('_a_hide_advanced_rights'); ?></a>
                        <a id="deleteButton" class="button expand alert [tiny small large] hide"><?php echo $self->context->dictionary->translate('_a_delete_user'); ?></a>
                    </div>
                </li>
            </ul>
            
            <?php
            foreach ($self->rightsList as $collection => $right) {
                if ($collection === '*'){
                    
                }else{
            ?>
                <ul class="small-block-grid-1 large-block-grid-1">
                    <div >
                        <fieldset>
                            <legend><?php echo $collection; ?></legend>
                            <h2>
                                <?php
                                if (isset($self->licenses[$collection])){
                                    if ($self->licenses[$collection] === 'NoLicense'){
                                        
                                    }else{
                                        echo "<h3>". $self->context->dictionary->translate('_a_signedon') . ' ' . $self->licenses[$collection] ."</h3>";
                                    }
                                }else{
                                    echo "<h3 style=\"color: red;\">". $self->context->dictionary->translate('_a_notsignedyet') ."</h3>";
                                }
                                ?>
                            </h2>
                            <ul class="small-block-grid-2 medium-block-grid-2 large-block-grid-2">
                                <?php
                                echo '<li><a id="' . $collection . 'download" collection="' . $collection . '" field="download" class="button expand rights' . (($self->_user->profile['groupname'] === 'admin' || $self->_user->profile['activated'] != 1) ? ' disabled': '' ) . '" ' . ($right['download'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Download</a></li>';
                                echo '<li><a id="' . $collection . 'visualize" collection="' . $collection . '" field="visualize" class="button expand rights' . (($self->_user->profile['groupname'] === 'admin' || $self->_user->profile['activated'] != 1) ? ' disabled': '' ) . '" ' . ($right['visualize'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Visualize</a></li>';
                                echo '<li class="advanced-right"><a id="' . $collection . 'canpost" collection="' . $collection . '" field="canpost" class="button expand rights' . (($self->_user->profile['groupname'] === 'admin' || $self->_user->profile['activated'] != 1) ? ' disabled': '' ) . '" ' . ($right['post'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Post</a></li>';
                                echo '<li class="advanced-right"><a id="' . $collection . 'canput" collection="' . $collection . '" field="canput" class="button expand rights' . (($self->_user->profile['groupname'] === 'admin' || $self->_user->profile['activated'] != 1) ? ' disabled': '' ) . '" ' . ($right['put'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Put</a></li>';
                                echo '<li class="advanced-right"><a id="' . $collection . 'candelete" collection="' . $collection . '" field="candelete" class="button expand rights' . (($self->_user->profile['groupname'] === 'admin' || $self->_user->profile['activated'] != 1) ? ' disabled': '' ) . '" ' . ($right['delete'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Delete</a></li>';
                                ?>
                            </ul>
                            <ul class="small-block-grid-1 large-block-grid-1">
                                <li>
                                    <div>
                                        <ul class="small-block-grid-1 large-block-grid-4">
                                            <?php
                                            if (isset($right['features'])){
                                                foreach ($right['features'] as $feature => $featureRight) {
                                                ?>
                                                <div class="panel">
                                                    <a id="<?php echo $feature; ?>" class="button right alert rightDeleteButton" collection="<?php echo $collection; ?>" featureid="<?php echo $feature; ?>">X</a>
                                                    <h2>
                                                        <?php
                                                        echo $feature;
                                                        ?>

                                                    </h2>
                                                    <ul class="small-block-grid-2 large-block-grid-5">
                                                        <?php
                                                        echo '<li>download : ' . ($featureRight['download'] == 1 ? 'true' : 'false') . '</li>';
                                                        echo '<li>visualize : ' . ($featureRight['visualize'] == 1 ? 'true' : 'false') . '</li>';
                                                        echo '<li class="advanced-right">post : ' . ($featureRight['post'] == 1 ? 'true' : 'false') . '</li>';
                                                        echo '<li class="advanced-right">put : ' . ($featureRight['put'] == 1 ? 'true' : 'false') . '</li>';
                                                        echo '<li class="advanced-right">delete : ' . ($featureRight['delete'] == 1 ? 'true' : 'false') . '</li>';
                                                        ?> 
                                                    </ul> 
                                                </div>
                                            <?php }} ?>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                            <a href="<?php echo $self->context->baseUrl . 'administration/users/' . $self->_user->profile['userid'] . "/rights?collection=" . $collection . '&lang=' . $self->context->dictionary->language; ?>" class="button expand [tiny small large]"><?php echo $self->context->dictionary->translate('_a_createrights'); ?></a>
                            
                        </fieldset>
                    </div>
                </ul>
            <?php }} ?>

            
            <ul class="small-block-grid-1 large-block-grid-1">
                <li>
                    <div class="panel">
                        <h1>
                            <?php echo $self->context->dictionary->translate('_a_last_download'); ?>
                        </h1>
                        <ul class="small-block-grid-1 large-block-grid-2 data_container">
                        <?php
                        foreach ($self->historyList as $history) {
                            ?>
                            <li>
                                <?php if ($history['service'] === 'download'){ ?>
                                <div class="panel" style="padding-left: 0.3em; background-color: <?php echo $color_download; ?>">
                                <?php } else if ($history['service'] === 'insert'){ ?>
                                <div class="panel" style="padding-left: 0.3em; background-color: <?php echo $color_insert; ?>">
                                <?php } else if ($history['service'] === 'create'){ ?>
                                <div class="panel" style="padding-left: 0.3em; background-color: <?php echo $color_create; ?>">
                                <?php } else if ($history['service'] === 'remove'){ ?>
                                <div class="panel" style="padding-left: 0.3em; background-color: <?php echo $color_remove; ?>">
                                <?php } else if ($history['service'] === 'update'){ ?>
                                <div class="panel" style="padding-left: 0.3em; background-color: <?php echo $color_update; ?>">
                                <?php } else {?>
                                <div class="panel" style="padding-left: 0.3em;">
                                <?php } ?>
                                    <h2><a href="<?php 
                                        if ($history['collection'] === '*'){
                                            $title = 'All';
                                            $url = $self->context->baseUrl . '/api/collections/' . 'search.html?lang=' . $self->context->dictionary->language;
                                        }else{
                                            $title = $history['collection'];
                                            $url = $self->context->baseUrl . '/api/collections/' .  $history['collection'] . '/search.html?lang=' . $self->context->dictionary->language;
                                        }
                                        echo $url;?>"><?php echo $title; ?></a></h2>
                                    <p>
                                        <a href="<?php echo $self->context->baseUrl . 'administration/users/' . $history['userid'] . '?lang=' . $self->context->dictionary->language; ?>"><?php echo $self->context->dictionary->translate('_a_userid') . ' : ' . $history['userid']; ?></a><br/>
                                        <?php
                                        echo $self->context->dictionary->translate('_a_service') . ' : ' . $history['service'] . '<br/>';
                                        echo $history['querytime'];
                                        ?>
                                    </p>
                                    <a href="<?php echo $history['url']; ?>"><?php echo $history['url']; ?></a>

                                </div>
                            </li>
                        <?php } ?>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- Footer -->
        <?php include '_scripts.php' ?>
        
        <script type="text/javascript" >
            $(document).ready(function() {

                var self = this;

                function initialize() {
                    $('#deleteButton').hide();
                    $('.rights').each(function(){
                        if ($(this).attr('rightValue') === 'true'){
                            $(this).css('background-color', 'green');
                        }else{
                            $(this).css('background-color', 'red');
                        }
                    });
                    $('.advanced-right').hide();
                    $('#_hide_advanced_rights').hide();
                }

                this.activateUser = function(user) {
                    Resto.Util.showMask();
                    
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + user + "/activate" ,
                        success: function() {
                            window.location = "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + <?php echo $self->_user->profile['userid']; ?> + "<?php echo '?lang=' . $self->context->dictionary->language; ?>";
                        },
                        error: function(e) {
                            Resto.Util.hideMask();
                            Resto.Util.dialog('error : ' + e['responseJSON']['ErrorMessage']);
                        }
                    });
                };

                this.deactivateUser = function(user) {
                    Resto.Util.showMask();
                
                    $.ajax({
                        type: "POST",
                        url: "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + user + "/deactivate",
                        success: function() {
                            window.location = "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + <?php echo $self->_user->profile['userid']; ?> + "<?php echo '?lang=' . $self->context->dictionary->language; ?>";
                        },
                        error: function(e) {
                            Resto.Util.hideMask();
                            Resto.Util.dialog('error : ' + e['responseJSON']['ErrorMessage']);
                        }
                    });
                };

                this.deleteUser = function(user) {
                    Resto.Util.showMask();
                
                    $.ajax({
                        type: "DELETE",
                        url: "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + user,
                        success: function() {
                            window.location = "<?php echo $self->context->baseUrl . 'administration/users/' . '?lang=' . $self->context->dictionary->language ?>";
                        },
                        error: function(e) {
                            Resto.Util.hideMask();
                            Resto.Util.dialog('error : ' + e['responseJSON']['ErrorMessage']);
                        }
                    });
                };
                
                this.deleteRight = function(collection, featureid) {
                    Resto.Util.showMask();
                
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + <?php echo $self->_user->profile['userid']; ?> + "/rights/delete",
                        async: true,
                        data: {
                            emailorgroup: '<?php echo $self->_user->profile['email']; ?>',
                            collection: collection,
                            featureid: featureid
                        },
                        success: function() {
                            window.location = "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + <?php echo $self->_user->profile['userid']; ?> + "<?php echo '?lang=' . $self->context->dictionary->language; ?>";
                        },
                        error: function(e) {
                            Resto.Util.hideMask();
                            Resto.Util.dialog('error : ' + e['responseJSON']['ErrorMessage']);
                        }
                    });
                };

                this.setGroup = function(group) {
                    Resto.Util.showMask();
                
                    $.ajax({
                        type: "POST",
                        async: true,
                        url: "<?php echo $self->context->baseUrl . 'administration/users/' . $self->_user->profile['userid']; ?>",
                        dataType: "json",
                        data: {
                            email: "<?php echo $self->_user->profile['email']; ?>",
                            groupname: group
                        },
                        success: function() {
                            window.location = "<?php echo $self->context->baseUrl . 'administration/users/' ?>" + <?php echo $self->_user->profile['userid']; ?> + "<?php echo '?lang=' . $self->context->dictionary->language; ?>";
                        },
                        error: function(e) {
                            Resto.Util.hideMask();
                            Resto.Util.dialog('error : ' + e['responseJSON']['ErrorMessage']);
                        }
                    });

                };
                
                this.updateRights = function(collection, field, valueToSet, obj) {
                    Resto.Util.showMask();
                
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "<?php echo $self->context->baseUrl . 'administration/users/' . $self->segments[1] . '/rights/update' ?>",
                        dataType: "json",
                        data: {
                            emailorgroup: '<?php echo $self->_user->profile['email']; ?>',
                            collection: collection,
                            field: field,
                            value: valueToSet
                        },
                        success: function() {
                            obj.attr('rightValue', valueToSet);
                            initialize();
                            Resto.Util.hideMask();
                        },
                        error: function(e) {
                            Resto.Util.hideMask();
                            Resto.Util.dialog('error : ' + e['responseJSON']['ErrorMessage']);
                        }
                    });
                };
                
                
                
                $(".rights").on('click', function(){
                    if ($(this).hasClass('disabled')){
                        
                    }else{
                        collection = $(this).attr('collection');
                        field = $(this).attr('field');
                        rightValue = $(this).attr('rightValue');
                        if (rightValue === 'true'){
                            rightValue = false;
                        }else{
                            rightValue = true;
                        }
                        self.updateRights(collection, field, rightValue, $(this));
                    }
                });

                $("#deleteButton").on('click', function() {
                    self.deleteUser(<?php echo $self->_user->profile['userid']; ?>);
                });
                $("#activateButton").on('click', function() {
                    self.activateUser(<?php echo $self->_user->profile['userid']; ?>);
                });
                $("#deactivateButton").on('click', function() {
                    self.deactivateUser(<?php echo $self->_user->profile['userid']; ?>);
                });
                $("#setGroupAdmin").on('click', function() {
                    self.setGroup('admin');
                });
                $("#setGroupDefault").on('click', function() {
                    self.setGroup('default');
                });
                
                $('.rightDeleteButton').each(function(){
                    var count = 0;
                    $(this).click(function(){
                        count ++;
                        if (count === 1){
                            $(this).text('<?php echo $self->context->dictionary->translate('_a_areyousure'); ?>');
                        }else if (count === 2){
                            self.deleteRight($(this).attr('collection'), $(this).attr('featureid'));
                        }
                    });
                });
                
                $("#_hide_advanced_rights").on('click', function() {
                    $('#_show_advanced_rights').show();
                    $('#_hide_advanced_rights').hide();
                    $('.advanced-right').hide();
                });
                
                $("#_show_advanced_rights").on('click', function() {
                    $('#_hide_advanced_rights').show();
                    $('#_show_advanced_rights').hide();
                    $('.advanced-right').show();
                });

                initialize();
                
            });
        </script>
    </body>
</html>
