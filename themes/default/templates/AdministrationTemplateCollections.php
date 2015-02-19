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
            <ul class="small-block-grid-1 large-block-grid-1">
                <?php
                foreach ($self->collections as $collection) {
                    ?>
                    <li>
                        <div>
                            <h1 style="text-align: center;"><?php echo $collection['collection']; ?></h1>
                        
                        <?php
                        //foreach ($self->groups as $group) {
                        $group['groupname'] = 'default';
                            $restoRights = new RestoRights($group['groupname'], $group['groupname'], $self->context);
                            $right = $restoRights->getRights($collection['collection']);
                            ?>
                            <ul class="small-block-grid-1 large-block-grid-1">
                                <fieldset>
                                    <legend><?php echo $group['groupname']; ?></legend>
                                    <ul class="small-block-grid-2 medium-block-grid-3 large-block-grid-6">
                                        <?php
                                        echo '<li><a groupname="' . $group['groupname'] . '" collection="' . $collection['collection'] . '" field="search" class="button expand rights" ' . ($right['search'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Search</a></li>';
                                        echo '<li><a groupname="' . $group['groupname'] . '" collection="' . $collection['collection'] . '" field="download" class="button expand rights" ' . ($right['download'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Download</a></li>';
                                        echo '<li><a groupname="' . $group['groupname'] . '" collection="' . $collection['collection'] . '" field="visualize" class="button expand rights" ' . ($right['visualize'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Visualize</a></li>';
                                        echo '<li><a groupname="' . $group['groupname'] . '" collection="' . $collection['collection'] . '" field="canpost" class="button expand rights" ' . ($right['post'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Post</a></li>';
                                        echo '<li><a groupname="' . $group['groupname'] . '" collection="' . $collection['collection'] . '" field="canput" class="button expand rights" ' . ($right['put'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Put</a></li>';
                                        echo '<li><a groupname="' . $group['groupname'] . '" collection="' . $collection['collection'] . '" field="candelete" class="button expand rights" ' . ($right['delete'] == 1 ? 'rightValue="true" style="background-color: green;"' : 'rightValue="false" style="background-color: red;"') . '>Delete</a></li>';
                                        ?>
                                    </ul>
                                </fieldset>
                            </ul>
                        <?php //} ?>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- Scripts -->
        <?php include '_scripts.php' ?>
        
        <script type="text/javascript" >
            $(document).ready(function() {

                var self = this;

                function initialize() {
                    $('.rights').each(function(){
                        if ($(this).attr('rightValue') === 'true'){
                            $(this).css('background-color', 'green');
                        }else{
                            $(this).css('background-color', 'red');
                        }
                    });
                }
                
                this.updateRights = function(groupname, collection, field, valueToSet, obj) {
                    Resto.Util.showMask();
                
                    $.ajax({
                        type: "POST",
                        async: false,
                        url: "<?php echo $self->context->baseUrl;?>administration/collections",
                        dataType: "json",
                        data: {
                            emailorgroup: groupname,
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
                    groupname = $(this).attr('groupname');
                    collection = $(this).attr('collection');
                    field = $(this).attr('field');
                    rightValue = $(this).attr('rightValue');
                    if (rightValue === 'true'){
                        rightValue = false;
                    }else{
                        rightValue = true;
                    }
                    self.updateRights(groupname, collection, field, rightValue, $(this));
                });

                initialize();
                
            });
        </script>
    </body>
</html>
