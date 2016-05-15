<div class="post-wrapper">
    
    <form action="index.php" method="post" enctype="multipart/form-data"/>
        <div class="post">
            <h1>Ecrivez quelque chose...</h1>
            <div class="post-content">

                <center>
                    <input type="hidden" name="a" value="newPost"/>
                    <input type="file" name="postFile" id="postFile"/>
                    <label for="postFile" class="yeti-btn-blue">Sélectionnez une photo...</label>
                    <img src="img/static/noimage.png" id="preview" title="Preview"/>
                    <span class="error"><?php echo $form->getError('image'); ?></span>
                </center>
                <center><span class="error"><?php echo $form->getError('text'); ?></span></center>
                <textarea placeholder="Ecrivez quelque chose..." name="postText" required><?php echo $form->getValue('postText'); ?></textarea>
                
                <input type="text" id="postTag" name="postTag" placeholder="Tags..." value="<?php echo $form->getValue('postTag'); ?>"/>
                <center><span class="error"><?php echo $form->getError('youtube'); ?></span></center>
                <input type="text" name="postYoutube" placeholder="Lien Youtube..." value="<?php echo $form->getValue('postYoutube'); ?>"/>
                
                <?php
                    echo $this->printGroupSelect();            
                ?>
                <center>
                <input type="checkbox" name="postPublic" id="postPublic" value="1"/><label for="postPublic">Visible par tout le monde</label>
                </center>
            </div>
            <div class="post-footer">
                <center>
                    <button class="yeti-btn-blue">Envoyer</button>
                </center>
            </div>
        </div>
    </form>
    
    <?php
        echo $this->printAllPost();
        
    ?>
    <div class="clear"></div>
</div>

<div id="headbar">
    <div class="toggle"></div>
    <div class="headbar-content">
        <?php
            echo $this->getGroupBar();
        ?>
    </div>
</div>