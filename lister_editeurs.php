<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
        <title>Liste des éditeurs</title>
    </head>
    <body>
    	<h2>Liste des éditeurs</h2>   
      
<?php

require_once("ressources_communes.php");


/************************* FONCTIONS *************************/

// Affichage de la liste des éditeurs repertoriés dans la base de données
function écrit_liste_éditeurs($base) {
    // sélection de l'identifiant et du nom de tous les éditeurs
    $requête = "SELECT id_editeur, nom FROM editeur ORDER BY nom;";
    try {          
        $résultats = $base->query($requête);
    }
    catch (PDOException $e) {
        exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
    }
	// écriture/affichage des éditeurs
	foreach ($résultats as $résultat) {
		$identifiant = htmlspecialchars($résultat['id_editeur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
        $nom = htmlspecialchars($résultat['nom'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
		echo "<p>$nom&nbsp;&nbsp;&nbsp;"
          ."<a href=\"modifier_editeur.php?identifiant=$identifiant\">Modifier</a>&nbsp;&nbsp;&nbsp;"
          ."<a href=\"supprimer_editeur.php?identifiant=$identifiant\">Supprimer</a>&nbsp;&nbsp;&nbsp;</p>\n";
	}
    // destruction de l'objet contenant le jeu de résultats de la requête
    unset($résultats);
}


/************************* PROGRAMME PRINCIPAL *************************/

$base = PDO_connecte_MySQL();

écrit_liste_éditeurs($base);

unset($base);

?>
    </body>
</html>
