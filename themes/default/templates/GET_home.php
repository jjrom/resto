<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <?php include 'head.php' ?>
    <body>
        <style>
            div.conteneur {
                background:#f2f2f2;
                width:100%;
                height: auto;
                min-height:70%;
                text-align:center;
            }

            div.bloc {
                padding-top:5%; 
            }
            
            .buttonRight{
                position: absolute;
                float: right;
                right: 0px;
                top: 40%;
            }
            
            .buttonLeft{
                position: absolute;
                float: left;
                left: 0px;
                top: 40%;
            }
            
            .gallerieItem{
                text-align: center; 
                background:#f2f2f2;
            }
            
            .gallerieItem:hover { 
                cursor: pointer;
                background:#e0e0e0;
            }
        </style>
        <script type="text/javascript" >
		$(document).ready(function() {

			var self = this;
                        itemId = 0;
                        img = [null, 'landsat.jpeg', 'theia.jpg'];
                        
                        items = $("#conteneur").children("div");

			function initialize() {
                            update();
                            self.showNextItem();
                            setInterval(function(){
                                self.showNextItem();
                            }, 10000);
                        }
                        
                        function update(){
                            self.fixMinSize($(".gallerieItem"));
                        }
                        
                        this.setImage = function(id){
                            if (img[id]){
                                $("#conteneur").css({"background": "url('http://localhost/img/" + img[id] + "') no-repeat black"});
                            }else{
                                $("#conteneur").css({"background": "black"});
                            }
                        }
                        
                        this.showNextItem = function(){
                            currentItem = items[itemId];
                            itemId++;
                            if (itemId > items.length-1){
                                itemId = 0;
                            }
                            $("#"+currentItem.id).hide();
                            nextItem = items[itemId];
                            self.setImage(itemId);
                            $("#"+nextItem.id).show(); 
                        }
                        
                        this.showPreviousItem = function(){
                            currentItem = items[itemId];
                            itemId--;
                            if (itemId < 0){
                                itemId = items.length-1;
                            }
                            $("#"+currentItem.id).hide();
                            nextItem = items[itemId];
                            self.setImage(itemId);
                            $("#"+nextItem.id).show(); 
                        }
                        
                        this.fixMinSize = function(groupOfItem){
                            tallest = 0;
                            groupOfItem.each(function(){
                                thisHeight = $(this).height();
                                if(thisHeight > tallest){
                                    tallest = thisHeight;
                                }
                            })
                            groupOfItem.css('height', tallest);
                        }
                        
                        $("#nextButton").on('click', function(){
                            self.showNextItem();
                        });
                        
                        $("#previousButton").on('click', function(){
                            self.showPreviousItem();
                        });
                        
                        
                        $(window).resize(function() {
                            location.reload(true);
                        });
                        
                        initialize();
		});	
	</script>
        
        <?php include 'header.php';?>
        
        <div class="row" style="height:35px;">
            <div class="large-12 columns"></div>
        </div>
        
        <div id="conteneur" class="conteneur">
            <a id="previousButton" class="button buttonLeft">Previous</a>
            <a id="nextButton" class="button buttonRight">Next</a>
            
            <div id="secondItem" class="bloc row hide">
                <h1 style="color: white">Services available</h1>
                <ul class="small-block-grid-1 medium-block-grid-1 large-block-grid-3">
                    <li>
                        <h3 style="color: white">Search</h3>
                        <p style="color: white">You can search on one or many collections. It's a full text search like Google.</p>
                    </li>
                    <li>
                        <h3 style="color: white">Visualize</h3>
                        <p style="color: white">All the products can be visualized. If you are connected you can visualize products in full resolution !</p>
                    </li>
                    <li>
                        <h3 style="color: white">Download</h3>
                        <p style="color: white">Choose your products and download them !</p>
                    </li>
                </ul>
            </div>
            <div id="firstItem" class="bloc row hide">
                <h1 style="color: white">TEST</h1>
                <p style="color: white">Ceci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de testCeci est le paragraphe de test</p>
            </div>
            <div id="thirdItem" class="bloc row hide">
                <h1 style="color: white">Theia</h1>
                <p style="color: white">Le pôle Thématique Surface Continentales THEIA est une structure nationale inter-organismes ayant pour vocation de faciliter l'usage des images issues de l'observation des surfaces continentales depuis l'espace</p>
            </div>
            
        </div>
        <div style="text-align: center; padding-top:25px;">
            <h3>Collections in details<h3>
        </div>
        <div id="gallerie" style="padding: 10px;">
            <ul class="small-block-grid-1 medium-block-grid-1 large-block-grid-3">
                <?php
                    $collectionsDescription = $self->context->dbDriver->getCollectionsDescriptions();
                    $counter = 0;
                    $j = 0;
                    foreach ($collectionsDescription as $collectionDescription) {
                ?>
                
                <li >
                    <div class="gallerieItem" onClick="window.location.href='<?php echo $self->context->baseUrl .  '/collections/' . $collectionDescription['osDescription'][$self->context->dictionary->language]['ShortName'] . '.html' ?>';">
                        <h1><a href="<?php echo $self->context->baseUrl .  '/collections/' . $collectionDescription['osDescription'][$self->context->dictionary->language]['ShortName'] . '.html' ?>"> <?php echo $collectionDescription['osDescription'][$self->context->dictionary->language]['ShortName']; ?></a></h1>
                        <?php
                        $description = $collectionDescription['osDescription'][$self->context->dictionary->language]['Description'];
                        $html = "<p>" . $description . "</p>";
                        echo $html;
                        ?>
                    </div>
                </li>
                <?php }?>
            </ul>
        </div>
        <!-- Footer -->
        <?php include 'footer.php';?>
        <?php exit; ?>
    </body>
</html>
