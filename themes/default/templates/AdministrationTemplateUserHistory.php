<?php
    $_noSearchBar = true;
    $_noMap = true;
    $color_remove = '#FFCCCC';
    $color_update = '#D4D4F0';
    $color_create = '#EAEAF8';
    $color_insert = '#F1F1FA';
    $color_download = '#FAFAFF';
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
            <h1><?php echo $self->segments[1] . ' - ' . $self->context->dictionary->translate('_a_history'); ?></h1>
            <br/>
            <div class="row">
                <div class="large-12 columns">
                    <label><?php echo $self->context->dictionary->translate('_a_choose_service'); ?>
                        <select id="serviceSelector" name="serviceSelector">
                            <option value=""></option>
                            <option value="download"><?php echo $self->context->dictionary->translate('_download'); ?></option>
                            <option value="search"><?php echo $self->context->dictionary->translate('_a_search'); ?></option>
                            <option value="resource"><?php echo $self->context->dictionary->translate('_a_visualize'); ?></option>
                            <option value="insert"><?php echo $self->context->dictionary->translate('_a_insert'); ?></option>
                            <option value="create"><?php echo $self->context->dictionary->translate('_a_create'); ?></option>
                            <option value="update"><?php echo $self->context->dictionary->translate('_a_update'); ?></option>
                            <option value="remove"><?php echo $self->context->dictionary->translate('_a_remove'); ?></option>
                        </select>
                    </label>
                </div>
                <div class="large-12 columns">
                    <label><?php echo $self->context->dictionary->translate('_a_choose_collection'); ?>
                        <select id="collectionSelector" name="collectionSelector">
                            <option value=""></option>
                            <?php
                            
                            foreach ($self->collectionsList as $collectionItem) {
                                ?>
                                <option value="<?php echo $collectionItem['collection']; ?>"><?php echo $collectionItem['collection']; ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </label>
                </div>
            </div>
            <br/>
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
        <!-- Footer -->
        <?php include '_footer.php' ?>
        
        <!-- Scripts -->
        <?php include '_scripts.php' ?>
        
        <script type="text/javascript" >
            $(document).ready(function() {

                service_selector = "<?php echo $self->service; ?>";
                collection_selector = "<?php echo $self->collectionFilter; ?>";
                min = <?php echo $self->startIndex; ?>;
                number = <?php echo $self->numberOfResults; ?>;
                $ajaxReady = true;


                function initialize() {
                    $('select[name=serviceSelector]').val('<?php echo (filter_input(INPUT_GET, 'service') ? filter_input(INPUT_GET, 'service') : ""); ?>');
                    $('select[name=collectionSelector]').val('<?php echo (filter_input(INPUT_GET, 'collection') ? filter_input(INPUT_GET, 'collection') : ""); ?>');
                }

                $("#serviceSelector").on('change', function() {
                    Resto.Util.showMask();
                    selector();
                    Resto.Util.hiedeMask();
                });

                $("#collectionSelector").on('change', function() {
                    Resto.Util.showMask();
                    selector();
                    Resto.Util.hiedeMask();
                });
                
                function selector(){
                    collectionSelector = $('select[name=collectionSelector]').val();
                    serviceSelector = $('select[name=serviceSelector]').val();
                    if (!serviceSelector && !collectionSelector){
                        window.location = "<?php echo $self->context->baseUrl . 'administration/users/' . $self->segments[1] . '/history' .'?lang=' . $self->context->dictionary->language; ?>" ;
                    }else if (serviceSelector && !collectionSelector){
                        window.location = "<?php echo $self->context->baseUrl . 'administration/users/' . $self->segments[1] . '/history' . '?lang=' . $self->context->dictionary->language. '&service=' ?>" + $('select[name=serviceSelector]').val();
                    }else if (!serviceSelector && collectionSelector){
                        window.location = "<?php echo $self->context->baseUrl . 'administration/users/' . $self->segments[1] . '/history' . '?lang=' . $self->context->dictionary->language . '&collection=' ?>" + $('select[name=collectionSelector]').val();
                    }else{
                        window.location = "<?php echo $self->context->baseUrl . 'administration/users/' . $self->segments[1] . '/history' . '?lang=' . $self->context->dictionary->language . '&service=' ?>" + $('select[name=serviceSelector]').val() + "&collection=" + $('select[name=collectionSelector]').val();
                    }
                }
                
                function addToList(data){
                    
                    var color = 'style="padding-left: 0.3em;"';
                    var collection = '';
                    var collection_title = '';
                    var content = '';
                    
                    $.each(data, function(key, value){
                        color = 'style="padding-left: 0.3em;"';
                        
                        if (value['collection'] === '*'){
                            collection = '';
                            collection_title = 'All';
                        }else{
                            collection = value['collection'];
                            collection_title = collection;
                        }
                        if (value['service'] === 'download'){
                            color = 'style="padding-left: 0.3em; background-color: <?php echo $color_download; ?>"';
                        }else if (value['service'] === 'insert'){
                            color = 'style="padding-left: 0.3em; background-color: <?php echo $color_insert; ?>"';
                        }else if (value['service'] === 'create'){
                            color = 'style="padding-left: 0.3em; background-color: <?php echo $color_create; ?>"';
                        }else if (value['service'] === 'remove'){
                            color = 'style="padding-left: 0.3em; background-color: <?php echo $color_remove; ?>"';
                        }else if (value['service'] === 'update'){
                            color = 'style="padding-left: 0.3em; background-color: <?php echo $color_update; ?>"';
                        }
                        
                        content = '<li><div class="panel" '
                                + color
                                + '><h2><a href="<?php echo $self->context->baseUrl . '/api/collections/' ?>' 
                                + collection_title
                                + '<?php echo '/search.html?lang=' . $self->context->dictionary->language; ?>'
                                + '">'
                                + collection
                                + '</a></h2>' 
                                + '<p>'
                                + '<a href="<?php echo $self->context->baseUrl . 'administration/users/'; ?>' + value['userid'] + '<?php echo '?lang=' . $self->context->dictionary->language; ?>' + '">'
                                + '<?php echo $self->context->dictionary->translate('_a_userid') . ' : '; ?>' + value['userid'] + '</a><br/>'
                                + '<?php echo $self->context->dictionary->translate('_a_service') . ' : ';?>' + value['service'] + '<br/>'
                                + value['querytime']
                                + '</p>'
                                + '<a href="' + value['url'] + '">' + value['url'] + '</a>'
                                + '</div></li>';
                        $(".data_container").append(content);
                    });
                    
                }
                
                var url = "<?php echo $self->context->baseUrl . 'administration/users/' . $self->segments[1] . '/history' ?>";
                var dataType = "json";
                var data = {
                        service: service_selector,
                        collection: collection_selector,
                        startIndex: min + number,
                        numberOfResults: number
                };
                
                Resto.Util.infiniteScroll(url, dataType, data, addToList, number);

                initialize();
                
            });
        </script>
    </body>
</html>
