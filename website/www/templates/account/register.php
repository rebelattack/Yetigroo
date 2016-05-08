<div class="box register">
    <center>
        <h1>Inscription</h1>
        <div>
            <img src="img/static/yeti.png" />
            <br/>
            <form method="post">
                <input type="hidden" name="a" value="register"/>
                
                <input type="text" name="mail" id="mail" placeholder="Mail : @ensta-bretagne.org" value="<?php echo $form->getValue('email'); ?>" />
                <br/>
                <span class="error"><?php echo $form->getError('mail'); ?></span>
                
                <input type="password" name="pw" id="pass1" placeholder="Password (min <?php echo PASSWORD_LENGTH; ?> caract.)" value="<?php echo $form->getValue('pw'); ?>" />
                <br/>
                <span class="error"><?php echo $form->getError('pw'); ?></span><br/>
                
                <input type="checkbox" id="CGU" name="cgu" value="1"><label for="CGU">Accepter les CGU</label>
                <br/>
                <span class="error"><?php echo $form->getError('cgu'); ?></span>
                
                <p>L'utilisateur s'engage à respecter les <a href="../cgu.php" class="important" target="_blank">CGU</a></p>
            </form>
            <span class="error"><?php echo $form->getError('activate'); ?></span>
            <button class="yeti-btn corner-all" id="btn-register" >S'inscrire</button><br/>
            <a href="login.php">Connexion</a>
        </div>
    </center>
</div>