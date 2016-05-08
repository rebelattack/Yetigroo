<div class="box activation">
    <center>
    <h1>Validation</h1>
    <div>
        <img src="img/static/yeti.png" />
        <br/>
        <p>Un mail de confirmation t'as été envoyé avec le code de validation.<br/>
        <span style="font-size:10px;">(Vérifier les spams)</span></p>
        
        <form method="post">
                <input type="hidden" name="a" value="activation"/>
                <input type="text" name="hash" id="hash" size="20" placeholder="Code de validation"/>
                <br />
                <span class="error"><?php echo $form->getError('hash'); ?></span>
            </form>
        <span class="error"><?php echo $form->getError('activate'); ?></span><br/>
        <button class="yeti-btn corner-all" id="btn-activation" >Valider</button><br>
        <a href="login.php">Connexion</a>    
    </div>
    </center>
</div>