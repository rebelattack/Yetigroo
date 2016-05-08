<div class="box login">
    <center>
        <h1>Connexion</h1>
        <div>
            <img src="img/static/yeti.png" />
            <br/>
            <?php
            if(@$_GET['a'] == "activated")
            {
                echo "Votre inscription a bien été validée.<br /><br />";
            }
            elseif(@$_GET['a'] == "recovery")
            {
                echo "Votre mot de passe a bien été réinitialisé.<br />Vous allez recevoir un mail d'ici quelques minutes.<br /><br />";
            }

            ?>
            <form method="post">
                <input type="hidden" name="a" value="login"/>            
                
                <input type="text" name="mail" id="mail" size="20" placeholder="Mail"/>
                <br/>                
                <span class="error"><?php echo $form->getError('email'); ?></span>
                
                <input type="password" name="pw" id="pass" size="20" placeholder="Password" />
                <br/>                
                <span class="error"><?php echo $form->getError('pw'); ?></span>
            </form>
            <span class="error"><?php echo $form->getError('activate'); ?></span>
            <button class="yeti-btn corner-all" id="btn-login">Connexion</button><br/>
            <button class="yeti-btn corner-all" id="btn-register">Inscription</button><br/>
            <?php
                if($form->returnErrors() > 0){
                    echo '<a href="recovery.php">Mot de passe perdu ?</a>';
                }
            ?>
        </div>
    </center>
</div>