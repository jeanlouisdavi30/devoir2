<!doctype html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
      <title>Recherche de livres</title>
    </head>
    <body>
    	<h2>Recherche de livres</h2>   
      
<?php

require_once("ressources_communes.php");


/************************ CONSTANTES *************************/

define("ANNEE_MINI", 1900);


/************************* FONCTIONS *************************/

//  Calcul de l'année courante
function année_courante() {
	$date_courante = getdate();
	return $date_courante['year'];
}


// Création dynamique d'un élément de formulaire : 
// liste déroulante comportant toutes les valeurs renvoyées par une requête, 
// pour la colonne d'une table donnée ; cette fonction est utilisée 
// pour construire la liste déroulante des éditeurs
function écrit_liste_déroulante_depuis_colonne_table_BD($base, $nom_liste, $table, $colonne) {
    // écriture du début de l'élément de formulaire
    $nom_liste = htmlspecialchars($nom_liste, HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
	echo "    <select name=\"$nom_liste\">\n";
    // écriture de la première option
	echo "        <option value=\"\">Indifférent</option>\n";
    // requête pour sélectionner le contenu de la colonne passée en paramètre
	$requête = "SELECT $colonne FROM $table ORDER BY $colonne";
	try {          
      $résultats = $base->query($requête);
    }
    catch (PDOException $e) {
      exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
    }
	// écriture des options suivantes
	foreach ($résultats as $résultat) {
		$valeur = htmlspecialchars($résultat[0], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
		echo "        <option value=\"$valeur\"";
		// si une valeur est sélectionnée, on compare son nom avec la valeur en cours de traitement ; 
		// si les chaînes de caractères sont égales, on ajoute l'attribut "selected" à cette entrée 
		// de la liste déroulante (conservation de la valeur sélectionnée)
		if (!empty($_POST[$nom_liste]) AND (!strcmp($valeur, $_POST[$nom_liste])))
			echo " selected";
		echo ">$valeur</option>\n";
	}
   // destruction du jeu de résultats de la requête
   unset($résultats);
	// écriture de la fin de l'élément de formulaire
	echo "	</select>\n";
}


// Création d'une liste déroulante d'années successives (ordre décroissant),
// entre deux bornes passées en paramètre
function écrit_liste_déroulante_années($nom_liste, $annee_min, $annee_max) {
	// écriture du début de l'élément de formulaire
    $nom_liste = htmlspecialchars($nom_liste, HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING);
	echo "    <select name=\"$nom_liste\">\n";
    // écriture de la première option
    echo "        <option value=\"\">Indifférent</option>\n";
	// écriture des options suivantes
	for($annee = $annee_max; $annee >= $annee_min; $annee--) {
		echo "        <option value=\"$annee\"";
		// si une année est sélectionnée, on la compare à la valeur en cours de traitement ; 
		// si ces entiers sont égaux, on ajoute l'attribut "selected" à cette entrée de la liste déroulante
        // (conservation de la valeur sélectionnée)
		if (!empty($_POST[$nom_liste]) AND ($annee == $_POST[$nom_liste]))
			echo " selected";
		echo ">$annee</option>\n";
	}
	// écriture de la fin de l'élément de formulaire	
	echo "	</select>\n";
}


// Création du formulaire de recherche multicritère
function écrit_formulaire($base) {
	echo "<form method=\"post\">\n";
	// champ de saisie d'un mot du titre
	echo "    <p>\n";
	echo "    <label>Mot du titre </label>\n";
	echo "    <input type=\"text\" name=\"mot_titre\" ";
	// si un mot a été saisi (lors de la recherche précédente),
	// on remplit le champ avec ce mot (conservation de la saisie texte)
	if (!empty($_POST['mot_titre']))
		echo "value=\"".htmlspecialchars($_POST['mot_titre'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."\""; 
	echo ">\n";
	echo "    </p>\n";
	// champ de saisie du nom de l'auteur
	echo "    <p>\n";
	echo "    <label>Nom de l'auteur </label>\n";
	echo "    <input type=\"text\" name=\"nom_auteur\" ";
	// si un nom a été saisi (lors de la recherche précédente),
	// on remplit le champ avec ce nom (conservation de la saisie texte)
	if (!empty($_POST['nom_auteur']))
		echo "value=\"".htmlspecialchars($_POST['nom_auteur'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."\""; 
	echo ">\n";
	echo "    </p>\n";
	// liste déroulante de l'année de publication minimale
	echo "    <p>\n";
	echo "    <label>Publié entre l'année </label>\n";
	$année_courante = année_courante();
	écrit_liste_déroulante_années('année_début', ANNEE_MINI, $année_courante);
	// liste déroulante de l'année de publication maximale
	echo "    <label>et l'année </label>\n";
	écrit_liste_déroulante_années('année_fin', ANNEE_MINI, $année_courante);
	echo "    </p>\n";
	echo "    <p>\n";
    echo "    <label>Editeur </label>\n";
	// liste déroulante des éditeurs
	écrit_liste_déroulante_depuis_colonne_table_BD($base,'éditeur', 'editeur', 'nom');
	echo "    </p>\n";
	// bouton "Rechercher"
	echo "    <p>\n";
	echo "    <input type=\"submit\" name=\"recherche\" value=\"Rechercher\">\n";
	echo "    </p>\n";
	echo "</form>\n";
}


// Validation des valeurs saisies et sélectionnées dans le formulaire ; on vérifie que :
// - le mot de titre saisi ne comporte que des caractères alphanumériques
// - le nom d'auteur saisi ne comporte que des caractères alphabétiques
// - l'année mini est inférieure ou égale à l'année maxi (si les deux ont été sélectionnées)
function valide_formulaire() {
	$messages_erreur = "";
	// mot du titre alphanumérique
	if (!empty($_POST['mot_titre']))
		if (!ctype_alnum(trim($_POST['mot_titre'])))
			$messages_erreur .= "<p style='color:red;'>Veuillez corriger le mot de titre saisi.</p>\n";
	// nom d'auteur alphabétique
	if (!empty($_POST['nom_auteur']))
		if (!ctype_alpha(trim($_POST['nom_auteur'])))
			$messages_erreur .= "<p style='color:red;'>Veuillez corriger le nom d'auteur saisi.</p>\n";
	// année mini inférieure ou égale à année maxi
    if (!empty($_POST['année_début']) AND !empty($_POST['année_fin']))
       if ($_POST['année_début'] > $_POST['année_fin'])
			$messages_erreur .= "<p style='color:red;'>Veuillez choisir une année minimale et une année maximale compatibles ou choisir la valeur \"Indifférent\" pour l'un des deux champs.</p>\n";
    return $messages_erreur;
}


// Recherche de livres dans la base de données, par construction et exécution d'une requête
// utilisant les paramètres issus du formulaire de recherche multicritère
function recherche_livres_dans_BD($base) {
	// début de construction de la requête : par défaut, on sélectionne tous les livres
	$requête = "SELECT titre, id_livre AS 'identifiant' "
			  ."FROM livre, auteur, editeur "
			  ."WHERE livre.id_auteur = auteur.id_auteur "
			  ."AND livre.id_editeur = editeur.id_editeur ";
	// éventuelle restriction de la recherche selon le mot de titre saisi
	if (!empty($_POST['mot_titre'])) {
		$mot_titre = $base->quote('%'.trim($_POST['mot_titre'].'%'));
		$requête .= "AND titre LIKE $mot_titre ";
	}
	// éventuelle restriction de la recherche selon le nom d'auteur saisi
	if (!empty($_POST['nom_auteur'])) {
		$nom_auteur = $base->quote(trim($_POST['nom_auteur']));
		$requête .= "AND auteur.nom = $nom_auteur ";
	}
	// éventuelle restriction de la recherche selon l'année de publication minimale
	if (!empty($_POST['année_début']))
		$requête .= "AND YEAR(date_publication) >= ".$_POST['année_début']." ";
	// éventuelle restriction de la recherche selon l'année de publication maximale
	if (!empty($_POST['année_fin']))
		$requête .= "AND YEAR(date_publication) <= ".$_POST['année_fin']." ";
	// éventuelle restriction de la recherche selon l'éditeur
	if (!empty($_POST['éditeur'])) {
        $éditeur = $base->quote(htmlspecialchars_decode($_POST['éditeur']));
		$requête .= "AND editeur.nom = $éditeur ";
    }
	// fin de construction de la requête : classement des résultats 
	// par ordre alphabétique des titres
	$requête .= "ORDER BY titre ;";
	// exécution de la requête sur la base de données
	try {          
      $résultats = $base->query($requête);
   }
   catch (PDOException $e) {
      exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
   }
	return ($résultats);
}


// Affichage des livres trouvés, sous la forme d'une liste de titres/liens hypertextes
function affiche_livres($livres) {
	echo "<h2>Résultat</h2>\n";
   // cas où il y a au moins un livre à afficher
   if ($livres && $livres->rowCount() > 0) {
		foreach ($livres as $livre)
		    echo "<p><a href=\"afficher_fiche_livre.php?identifiant=".$livre['identifiant']."\">"
				 .htmlspecialchars($livre['titre'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)
				 ."</a></p>\n";
   }
	// cas où la recherche n'a donné aucun résultat
	else
		echo "<p>Aucun livre ne correspond à votre recherche.</p>\n";     
}


/************************* PROGRAMME PRINCIPAL *************************/

$base = PDO_connecte_MySQL();

écrit_formulaire($base);

// si une recherche a été effectuée (bouton "Rechercher" cliqué)
if (isset($_POST['recherche'])) {
	$messages_erreur = valide_formulaire();
	// cas où le formulaire est valide : on effectue une requête dans la base et on affiche les résultats
    if (empty($messages_erreur)) {
       $livres = recherche_livres_dans_BD($base);
       affiche_livres($livres);
       // destruction de l'objet contenant le jeu de résultats de la requête
       unset($livres);
    }
    // cas où le formulaire est invalide : on invite l'utilisateur à corriger ses choix
    else
        echo $messages_erreur;     
}

// fermeture/destruction de la connexion au serveur MySQL
unset($base);

?>
    </body>
</html>
