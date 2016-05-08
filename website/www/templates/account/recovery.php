<div class="box recovery">
    <center>
    <h1>Mot de passe perdu ?</h1>
    <div>
        <img src="img/static/yeti.png" />
        <br/>        
        <form method="post">
            <input type="hidden" name="a" value="recovery"/>
            <input type="text" name="mail" id="mail" size="30" placeholder="Votre adresse email"/>
            <br /><span class="error"><?php echo $form->getError('recover'); ?></span>
        </form>
        <span class="error"><?php echo $form->getError('activate'); ?></span>
        <button class="yeti-btn corner-all" id="btn-recovery" >Récuperer</button><br>
        <a href="login.php">Connexion</a>    
    </div>
    </center>
</div>