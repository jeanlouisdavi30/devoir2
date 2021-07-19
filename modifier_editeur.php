<?php
   // Démarrage ou reprise de session
	session_start();
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>Modifier un éditeur</title>
    </head>
    <body>    
		<h2>Modifier un éditeur</h2>
<?php

require_once("ressources_communes.php");


/************************* FONCTIONS *************************/

// fonction d'affichage du formulaire de saisie du nom de l'éditeur
function ecrit_formulaire_modification() {
   echo "<form method=\"post\" action=\"".$_SERVER['SCRIPT_NAME']."\">\n";
   echo "<p><label>Nom de l'éditeur </label>\n";	
   echo "<input type=\"text\" name=\"éditeur\" value=\""
       .(isset($_POST['éditeur']) ? htmlspecialchars($_POST['éditeur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING) : htmlspecialchars($_SESSION['nom_éditeur_initial'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING))
       ."\"></p>\n";
   echo "<p><input type=\"submit\" name=\"modification_base\" value=\"Modifier dans la base\"></p>\n";
   echo "</form>\n";
}

// fonction d'affichage du formulaire de confirmation
function ecrit_message_et_formulaire_confirmation() {
   echo "<p>Nom de l'éditeur : ".htmlspecialchars($_POST['éditeur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
   echo "<form method=\"post\">\n";
   echo "<input type=\"hidden\" name=\"éditeur\" value=\"".$_POST['éditeur']."\">\n";
   echo "<input type=\"submit\" name=\"confirmation\" value=\"Confirmer ce nom\">\n";
   echo "<input type=\"submit\" name=\"modification_nom\" value=\"Modifier ce nom\">\n";
   echo "</form>\n";
}


/************************* PROGRAMME PRINCIPAL *************************/

// si la page a été appelée avec un identifiant (paramètre passé par URL)
if (!empty($_GET['identifiant'])) {
   
   $base = PDO_connecte_MySQL();
   
   // cet identifiant existe-t-il ?
   $identifiant = $base->quote($_GET['identifiant']);
   $requête = "SELECT * FROM editeur WHERE id_editeur = $identifiant ;";

   try {          
      $résultats = $base->query($requête);
   }
   catch (PDOException $e) {
      exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
   }
   
   // l'identifiant existe dans la base : 
   // affichage du formulaire de modification
   $éditeur = $résultats->fetch(PDO::FETCH_ASSOC);
   if ($éditeur) {
      $_SESSION['id_éditeur'] = $éditeur['id_editeur'];
      $_SESSION['nom_éditeur_initial'] = $éditeur['nom'];
      ecrit_formulaire_modification();
   }
   // si cet identiant n'existe pas dans la base
   else
      echo "<p>Aucun éditeur ne correspond à cet identifiant dans la base de données.</p>\n";
   
   unset($résultats);
  
   unset($base);
}
// Le formulaire de modification du nom a été validé
else if (isset($_POST['modification_base'])) {
   // le nom d'éditeur n'est pas vide
   if (!empty($_POST['éditeur'])) {
      // copie du nom dans une variable de session
      $_SESSION['nom_éditeur_modifié'] = $_POST['éditeur'];
      // affichage du formulaire de confirmation
      ecrit_message_et_formulaire_confirmation();
   }
   // le nom de l'éditeur est vide
   else {
      ecrit_formulaire_modification();
      echo "<p style='color:red;'>Veuillez saisir un nom d'éditeur, s'il vous plaît.</p>\n";
   }
}
// L'utilisateur n'a pas confirmé le nom et souhaite le modifier à nouveau
else if (isset($_POST['modification_nom'])) {
   ecrit_formulaire_modification();
}
// Le formulaire de confirmation a été validé : 
// on essaie de modifier le nom de cet éditeur dans la base de données
else if (isset($_POST['confirmation'])) {
   // connexion à la base de données MySQL
   $base = PDO_connecte_MySQL();
      
   // modification de l'éditeur dans la base de données
   $nom_initial = $_SESSION['nom_éditeur_initial'];
   $nom_modifié = substr(trim($_SESSION['nom_éditeur_modifié']), 0, EDITEUR_NOM_LONGUEUR_MAXI);
   $nom_initial_pour_affichage = htmlspecialchars($nom_initial, HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
   $nom_modifié_pour_affichage = htmlspecialchars($nom_modifié, HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
   $nom_modifié_pour_requête = $base->quote($nom_modifié);
   $identifiant = $base->quote($_SESSION['id_éditeur']);
   
   $requête = "UPDATE editeur SET nom = $nom_modifié_pour_requête WHERE id_editeur = $identifiant;";
   try {          
      $nbe_lignes_modifiées = $base->exec($requête);
   }
   catch (PDOException $e) {
      exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
   }
   
   if ($nbe_lignes_modifiées == 1)
      echo "<p style='color:green;'>L'éditeur \"$nom_initial_pour_affichage\" "
          ."a été modifié en \"$nom_modifié_pour_affichage\" dans la base de données.</p>\n";
   else
      echo "L'éditeur \"$nom_initial_pour_affichage\" n'a pas été modifié.</p>\n";
   
   echo "<p><a href=\"lister_editeurs.php\">Liste des éditeurs</a></p>\n";
   
   // fermeture de la connexion au serveur MySQL
   unset($base);
   
   // suppression des valeurs mémorisées
   unset($_SESSION['id_éditeur'], $_SESSION['nom_éditeur_initial'], $_SESSION['nom_éditeur_modifié']);
}
// cas d'un appel incorrect de la page
else
	echo "<p>Erreur : la page a été appelée sans l'identifiant de l'éditeur à modifier.</p>\n";

?>
    </body>
</html>
