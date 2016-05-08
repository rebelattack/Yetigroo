<ul id="nav">
        <li><a href="index.php" title="Accueil"><div class="home-btn"></div></a></li>
        <li><a href="index.php?mod=group" title="Groupes"><div class="groups-btn"></div></a></li>
        <li><a href="index.php?mod=beer" title="Classement Bière-Pong"><div class="beer-btn"></div></a></li>
</ul>
<ul id="outOfYetigroo">
    <li><a href="index.php?mod=profile&id=<?php echo $_SESSION['id']; ?>" title="Réglages"><img src="../img/icon/settings.png"/></a></li>
    <li><a href="logout.php" title="Déconnexion"><img src="../img/icon/logout.png"/></a></li>
</ul>