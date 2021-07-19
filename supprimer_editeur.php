<?php
   // Démarrage ou reprise de session
	session_start();
?>
<!doctype html>
<html lang="fr">
	<head>
        <meta charset="utf-8">
        <title>Supprimer un éditeur</title>
    </head>
    <body>
    	<h2>Supprimer un éditeur</h2>   
      
<?php

require_once("ressources_communes.php");


/************************* FONCTIONS *************************/

// fonction d'affichage du formulaire de confirmation
function ecrit_message_et_formulaire_confirmation() {
   echo "<p>Nom de l'éditeur : ".htmlspecialchars($_SESSION['nom_éditeur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
   echo "<form method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
   echo "<input type=\"submit\" name=\"confirmation\" value=\"Confirmer\">\n";
   echo "<input type=\"submit\" name=\"annulation\" value=\"Annuler\">\n";
   echo "</form>\n";
}


/************************* PROGRAMME PRINCIPAL *************************/   

// si la page a été appelée avec un identifiant (paramètre passé par URL)
if (!empty($_GET['identifiant'])) {
   
   $base = PDO_connecte_MySQL();
   
   // cet identifiant d'éditeur existe-t-il ?
   $identifiant = $base->quote($_GET['identifiant']);
   $requête = "SELECT * FROM editeur WHERE id_editeur = $identifiant ;";

   try {          
      $résultats = $base->query($requête);
   }
   catch (PDOException $e) {
      exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
   }
   
   // l'identifiant existe dans la base : 
   // affichage du formulaire confirmation de la suppression
   $éditeur = $résultats->fetch(PDO::FETCH_ASSOC);
   if ($éditeur) {
      $_SESSION['id_éditeur'] = $éditeur['id_editeur'];
      $_SESSION['nom_éditeur'] = $éditeur['nom'];
      ecrit_message_et_formulaire_confirmation();
   }
   // cet identiant n'existe pas dans la base :
   else {
      echo "<p>Aucun éditeur ne correspond à cet identifiant dans la base de données.</p>\n";
   }
   
   unset($résultats);
  
   unset($base);
}
// si la suppression de l'éditeur a été confirmée :
else if (isset($_POST['confirmation'])) {
    
    $base = PDO_connecte_MySQL();
   
    // liste de tous les livres publiés par cet éditeur contenus dans la base
    $requête = "SELECT id_livre, titre FROM livre WHERE id_editeur = ".$_SESSION['id_éditeur']." ORDER BY titre ;";
    
    try {          
        $résultats = $base->query($requête);
    }
    catch (PDOException $e) {
        exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
    }
    
    // si au moins un livre est publié par cet éditeur : suppression de l'éditeur non autorisée
    if ($résultats && $résultats->rowCount() > 0) {
        echo "<p style='color:orangered;'>L'éditeur \""
           .htmlspecialchars($_SESSION['nom_éditeur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."\" "
           ."n'a pas pu être supprimé de la base de données.</p>\n";
        if ($résultats->rowCount() == 1)
            $mots = "un livre publié";
        else
            $mots = "des livres publiés";
        echo "<p> En effet, la base contient $mots par cet éditeur :</p>";
        echo "<ol>\n";
        foreach ($résultats as $livre)
            echo "<li><a href=\"afficher_fiche_livre.php?identifiant=".$livre['id_livre']."\">"
                .htmlspecialchars($livre['titre'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</a></li>";
        echo "</ol>\n";
    }
    // si aucun livre n'est publié par cet éditeur : suppression de l'éditeur
    else {
        $requête = "DELETE FROM editeur WHERE id_editeur = ".$_SESSION['id_éditeur']." ;";
        
        try {          
            $nbe_lignes_supprimées = $base->exec($requête);
        }
        catch (PDOException $e) {
            exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
        }
        
       if ($nbe_lignes_supprimées == 1) {
          echo "<p style='color:green;'>L'éditeur \""
               .htmlspecialchars($_SESSION['nom_éditeur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."\" "
               ."a été supprimé de la base de données.</p>\n";
       }
   }
   
   unset($base);
   
   unset($_SESSION['id_éditeur'], $_SESSION['nom_éditeur']);
}
// si la suppression de l'éditeur a été annulée :
else if (isset($_POST['annulation'])) {
   echo "<p style='color:maroon;'>La suppression de l'éditeur \""
           .htmlspecialchars($_SESSION['nom_éditeur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."\" "
           ."a été annulée.</p>\n";
}
// cas d'un appel incorrect de la page
else {
	echo "<p>Erreur : la page a été appelée sans l'identifiant du livre à afficher.</p>\n";
}

echo "<p><a href=\"lister_editeurs.php\">Liste des éditeurs</a></p>\n";

?>
    </body>
</html>
