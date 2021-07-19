<!doctype html>
<html lang="fr">
   <head>
      <title>Fiche livre</title>
      <meta charset="utf-8">
    </head>
    <body>
<?php

require_once("ressources_communes.php");


/************************* FONCTIONS *************************/

// Recherche d'un livre dans la base à partir de son identifiant,
// avec sélection des colonnes nécessaires à son affichage détaillé
function recherche_livre($base, $identifiant) {
   $identifiant = $base->quote($identifiant);
   $requête = "SELECT id_livre, titre, date_publication, pages,
            auteur.prenom AS 'auteur_prenom', auteur.nom AS 'auteur_nom',
            editeur.nom AS 'editeur_nom'
            FROM livre, auteur, editeur
            WHERE livre.id_livre = $identifiant
            AND livre.id_auteur = auteur.id_auteur
            AND livre.id_editeur = editeur.id_editeur;";
   try {          
      $résultats = $base->query($requête);
   }
   catch (PDOException $e) {
      exit("Erreur lors de l'exécution de la requête : ". $e->getMessage());
   }
   return $résultats;
}


// Affichage de la fiche détaillée d'un livre
function affiche_livre($livre) {
   $date_publication = new DateTime($livre['date_publication']);
   $date_publication_formatée = $date_publication->format(FORMAT_DATE_AFFICHAGE);
   echo "<h2>Fiche livre</h2>\n";
   echo "<p><strong>Référence : </strong>"
        .htmlspecialchars($livre['id_livre'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
   echo "<p><strong>Titre : </strong>".htmlspecialchars($livre['titre'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
   echo "<p><strong>Auteur : </strong>"
        .htmlspecialchars($livre['auteur_prenom'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)." "
        .htmlspecialchars(strtoupper($livre['auteur_nom']), HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
   echo "<p><strong>Éditeur : </strong>"
        .htmlspecialchars($livre['editeur_nom'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
   echo "<p><strong>Date de publication : </strong>"
        .htmlspecialchars($date_publication_formatée, HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
   echo "<p><strong>Pages : </strong>"
        .htmlspecialchars($livre['pages'], HTMLSPECIALCHARS_FLAGS, HTMLSPECIALCHARS_ENCODING)."</p>\n";
}


/************************* PROGRAMME PRINCIPAL *************************/

// si la page a bien été appelée avec un identifiant (paramètre passé par URL)
if (!empty($_GET['identifiant'])) {
   
   // lecture dans la base de données des informations sur le livre souhaité
   $base = PDO_connecte_MySQL();
   $livres = recherche_livre($base, $_GET['identifiant']);
   unset($base);
   
   // cas où le livre a été trouvé dans la base : affichage des détails
   if ($livres && $livres->rowCount() > 0) {
      affiche_livre($livres->fetch(PDO::FETCH_ASSOC));
      unset($livres);
   }
   // cas où la recherche n'a donné aucun résultat
   else
      echo "<p>Aucun livre ne correspond à cet identifiant dans la base de données.</p>\n";
}
// cas d'un appel incorrect de la page
else
   echo "<p>Erreur : la page a été appelée sans l'identifiant du livre à afficher.</p>\n";

echo "<p>Faire une <a href=\"rechercher_livres.php\">nouvelle recherche</a></p>\n";

?>
   </body>
</html>
