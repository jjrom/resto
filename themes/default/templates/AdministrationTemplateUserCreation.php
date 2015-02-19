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
            <a id="_alert" href="#" class="button expand alert hide"></a>
            <form>
                <fieldset>
                    <legend><?php echo $self->context->dictionary->translate('_a_profile'); ?></legend>

                    <label><?php echo $self->context->dictionary->translate('_a_email'); ?>
                        <input id="email" type="text" placeholder="<?php echo $self->context->dictionary->translate('_a_email'); ?>...">
                    </label>
                    <label><?php echo $self->context->dictionary->translate('_a_lastname'); ?>
                        <input id="lastname" type="text" placeholder="<?php echo $self->context->dictionary->translate('_a_lastname'); ?>...">
                    </label>
                    <label><?php echo $self->context->dictionary->translate('_a_username'); ?>
                        <input id="username" type="text" placeholder="<?php echo $self->context->dictionary->translate('_a_username'); ?>...">
                    </label>
                    <label><?php echo $self->context->dictionary->translate('_a_givenname'); ?>
                        <input id="givenname" type="text" placeholder="<?php echo $self->context->dictionary->translate('_a_givenname'); ?>...">
                    </label>
                    <label><?php echo $self->context->dictionary->translate('_password'); ?>
                        <input id="password" type="password" placeholder="<?php echo $self->context->dictionary->translate('_password'); ?>...">
                    </label>
                    <label><?php echo $self->context->dictionary->translate('_a_password_confirmation'); ?>
                        <input id="passwordConfirm" type="password" placeholder="<?php echo $self->context->dictionary->translate('_a_password_confirmation'); ?>...">
                    </label>
                </fieldset>
                <fieldset>
                    <legend><?php echo $self->context->dictionary->translate('_a_groupname'); ?></legend>
                    <label><?php echo $self->context->dictionary->translate('_a_select_group_name'); ?>
                        <select id="groupname" name="groupname">
                            <option value="default" selected="true"><?php echo $self->context->dictionary->translate('_a_default'); ?></option>
                            <option value="admin"><?php echo $self->context->dictionary->translate('_a_admin'); ?></option>
                        </select>
                    </label>
                </fieldset>
                <fieldset>
                    <legend><?php echo $self->context->dictionary->translate('_a_activated'); ?></legend>
                    <input type="radio" name="activated" value="true" id="activated"><label for="activated"><?php echo $self->context->dictionary->translate('_a_activated'); ?></label>
                    <input type="radio" name="deactivated" value="false" id="deactivated"><label for="deactivated"><?php echo $self->context->dictionary->translate('_a_deactivated'); ?></label>
                </fieldset>
            </form> 
            <a id="_save" href="#" class="button expand"><?php echo $self->context->dictionary->translate('_a_save_user'); ?></a>
        </div>
        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- Scripts -->
        <?php include '_scripts.php' ?>
        
        <script type="text/javascript" >
            $(document).ready(function() {
                
                var self = this;

                function initialize() {
                    $('input:radio[name=deactivated]').attr('checked', true);
                }
                
                this.addUser = function() {
                    if ($("#email").val() === ''){
                        Resto.Util.dialog('Please set email');
                    }else if ($("#password").val() === ''){
                        Resto.Util.dialog('Please set password');
                    }else if ($("#username").val() === ''){
                        Resto.Util.dialog('Please set username');
                    }else if ($("#givenname").val() === ''){
                        Resto.Util.dialog('Please set givenname');
                    }else if ($("#lastname").val() === ''){
                        Resto.Util.dialog('Please set lastname');
                    }else if ($("#password").val() !== $("#passwordConfirm").val()){
                        Resto.Util.dialog('Passwords are different');
                    }else{
                        Resto.Util.showMask();
                        $.ajax({
                        type: "POST",
                        async: false,
                        url: "<?php echo $self->context->baseUrl . 'administration/users' ?>",
                        dataType: "json",
                        data: {
                            email: $("#email").val(),
                            groupname: $('select[name=groupname]').val(),
                            password: $("#password").val(),
                            username: $("#username").val(),
                            givenname: $('#givenname').val(),
                            lastname: $('#lastname').val(),
                            activated: $('input[name=activated]:checked').val()
                        },
                        error: function(e) {
                            Resto.Util.hideMask();
                            Resto.Util.dialog('error : ' + e['responseJSON']['ErrorMessage']);
                        },
                        success: function() {
                            window.location = "<?php echo $self->context->baseUrl . 'administration/users/' . '?lang=' . $self->context->dictionary->language; ?>";
                        }
                    });
                }
            };
            
            $("#_save").on('click', function() {
                self.addUser();
            });

            $("#activated").on('click', function() {
                $('input:radio[name=activated]').attr('checked', true);
                $('input:radio[name=deactivated]').attr('checked', false);
            });

            $("#deactivated").on('click', function() {
                $('input:radio[name=activated]').attr('checked', false);
                $('input:radio[name=deactivated]').attr('checked', true);
            });

            initialize(); 
            
        });
        </script>
    </body>
</html>
