<?php
   // Démarrage ou reprise de session
	session_start();
?>
<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<title>Ajouter un auteur</title>
    </head>
    <body>   
		<h2>Ajouter un auteur</h2>
<?php

require_once("ressources_communes.php");


/************************* FONCTIONS *************************/

// fonction d'affichage du formulaire de saisie du prénom et du nom de l'auteur
function ecrit_formulaire_saisie() {
   echo "<form method=\"post\">\n";
   echo "<p><label>Prénom de l'auteur </label>\n";	
   echo "<input type=\"text\" name=\"prénom\" value=\""
       .(isset($_SESSION['prénom']) ? htmlspecialchars($_SESSION['prénom'], ENT_COMPAT, "UTF-8") : '')
       ."\"></p>\n";
       echo "<p><label>Nom de l'auteur </label>\n";	
   echo "<input type=\"text\" name=\"nom\" value=\""
       .(isset($_SESSION['nom']) ? htmlspecialchars($_SESSION['nom'], ENT_COMPAT, "UTF-8") : '')
       ."\"></p>\n";
   echo "<p><input type=\"submit\" name=\"ajout\" value=\"Ajouter à la base\"></p>\n";
   echo "</form>\n";
}

// fonction d'affichage du formulaire de confirmation
function ecrit_message_et_formulaire_confirmation() {
   echo "<p>Prénom de l'auteur : ".htmlspecialchars($_SESSION['prénom'], ENT_COMPAT, "UTF-8")."</p>\n";
   echo "<p>Nom de l'auteur : ".htmlspecialchars($_SESSION['nom'], ENT_COMPAT, "UTF-8")."</p>\n";
   echo "<form method=\"post\">\n";
   echo "<input type=\"submit\" name=\"confirmation\" value=\"Confirmer\">\n";
   echo "<input type=\"submit\" value=\"Modifier\">\n";
   echo "</form>\n";
}


/************************* PROGRAMME PRINCIPAL *************************/

// Le formulaire de saisie a été validé
if (isset($_POST['ajout'])) {
   // le prénom et le nom d'auteur ne sont pas vides
   if (!empty($_POST['prénom']) && !empty($_POST['nom'])) {
      // copie dans des variables de session
      $_SESSION['prénom'] = $_POST['prénom'];
      $_SESSION['nom'] = $_POST['nom'];
      // affichage du formulaire de confirmation
      ecrit_message_et_formulaire_confirmation();
   }
   // le prénom ou le nom de l'auteur est vide
   else {
      ecrit_formulaire_saisie();
      echo "<p style='color:red;'>Veuillez saisir un prénom et un nom, s'il vous plaît.</p>\n";
   }
}
// Le formulaire de confirmation a été validé : 
// on essaie d'ajouter ce nouvel auteur dans la base de données
else if (isset($_POST['confirmation'])) {
   // connexion à la base de données MySQL
   $base = PDO_connecte_MySQL();
      
   // recherche de l'auteur dans la base de données :
   $prénom = substr(trim($_SESSION['prénom']), 0, AUTEUR_PRENOM_LONGUEUR_MAXI);
   $nom = substr(trim($_SESSION['nom']), 0, AUTEUR_NOM_LONGUEUR_MAXI);
   $prénom_pour_affichage = htmlspecialchars($prénom, HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
   $nom_pour_affichage = htmlspecialchars($nom, HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
   $prénom_pour_requête = $base->quote($prénom);
   $nom_pour_requête = $base->quote($nom);
   $requête = "SELECT * FROM auteur WHERE prenom = $prénom_pour_requête AND nom = $nom_pour_requête ;";
   try {          
      $résultats = $base->query($requête);
   }
   catch (PDOException $e) {
      exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
   }
   
   // cas où l'auteur se trouve déjà dans la base de données
   if ($résultats && $résultats->rowCount() > 0) {
      $auteur = $résultats->fetch(PDO::FETCH_ASSOC);
      echo "<p style='color:maroon;'>L'auteur \"$prénom_pour_affichage $nom_pour_affichage\" "
          ."est déjà enregistré dans la base de données.</p>\n";
      echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."\">Ajouter un autre auteur</a></p>\n";
   }
   // cas où l'auteur n'a pas été trouvé : on l'ajoute dans la base de données
   else {
      $requête = "INSERT INTO auteur (prenom, nom) VALUES ($prénom_pour_requête, $nom_pour_requête) ;";
      try {          
         $nbe_lignes_insérées = $base->exec($requête);
      }
      catch (PDOException $e) {
         exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
      }
      if ($nbe_lignes_insérées == 1)
         echo "<p style='color:green;'>L'auteur \"$prénom_pour_affichage $nom_pour_affichage\" "
             ."a bien été enregistré dans la base de données.</p>\n";
      else
         echo "L'enregistrement de l'auteur \"$prénom_pour_affichage $nom_pour_affichage\" a échoué.</p>\n";

      echo "<p><a href=\"".$_SERVER['SCRIPT_NAME']."\">Ajouter un autre auteur</a></p>\n";
   }
   
   // libération de la mémoire occupée par le jeu de résultats de la requête
   unset($résultats);
   
   // fermeture de la connexion au serveur MySQL
   unset($base);
   
   // fermeture de la session
   session_destroy();
}
// Premier chargement ou nouvelle saisie : affichage de la page dans son état initial
else
   ecrit_formulaire_saisie();

?>
    </body>
</html>
