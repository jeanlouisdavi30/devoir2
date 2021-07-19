<?php

/************************ CONSTANTES *************************/

// Paramètres d'accès au serveur MySQL
const MYSQL_MACHINE_HOTE = "localhost";
const MYSQL_NOM_UTILISATEUR = "script_php";
const MYSQL_MOT_DE_PASSE = "zh6tjPp6T56N4dbF";
const MYSQL_BASE_DE_DONNEES = "exercices_php";
const MYSQL_CHARSET = "utf8";
const MYSQL_FORMAT_DATE = "Y-m-d";
const MYSQL_PREFIXE_DSN = "mysql:";
const MYSQL_DSN = MYSQL_PREFIXE_DSN."host=".MYSQL_MACHINE_HOTE
                                   .";dbname=".MYSQL_BASE_DE_DONNEES
                                   .";charset=".MYSQL_CHARSET;
                                   
// Taille maximale des chaînes dans la base de données
const LIVRE_TITRE_LONGUEUR_MAXI = 100;
const AUTEUR_NOM_LONGUEUR_MAXI = 50;
const AUTEUR_PRENOM_LONGUEUR_MAXI = 30;
const EDITEUR_NOM_LONGUEUR_MAXI = 50;

// Jeu de caractères et options de traitement des chaînes
const HTMLSPECIALCHARS_ENCODING = "UTF-8";
const HTMLSPECIALCHARS_FLAGS = ENT_COMPAT;
// Format français pour les dates  : jj/mm/aaaa
const FORMAT_DATE_AFFICHAGE = "d/m/Y";  


/************************* FONCTIONS *************************/

// Connexion à un serveur MySQL et à une base de données via PDO
function PDO_connecte_MySQL() {
	try {
      $base = new PDO(MYSQL_DSN, MYSQL_NOM_UTILISATEUR, MYSQL_MOT_DE_PASSE);                      
   }
   catch (PDOException $e) {
      exit('Erreur de connexion au serveur MySQL : '. $e->getMessage());
   }
   return $base;
}

?>
