<?php
// Service web du projet Réservations M2L
// Ecrit le 13/10/2015 par Nicolas Esteve

// Ce service web permet à un administrateur authentifié de supprimer un utilisateur
// et fournit un compte-rendu d'exécution

// Le service web doit être appelé avec 3 paramètres : nomAdmin, mdpAdmin, nomUtilisateur
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/CreerUtilisateur.php?nomAdmin=admin&mdpAdmin=admin&name=jim&level=1&user=jean.michel.cartron@gmail.com
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/CreerUtilisateur.php

global $doc;		// le document XML à générer
global $name, $level, $password, $user;

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/include.parametres.php');

// crée une instance de DOMdocument
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';

// crée un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web CreerUtilisateur - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_GET ["nomAdmin"];
if ( empty ($_GET ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_GET ["mdpAdmin"];
if ( empty ($_GET ["name"]) == true)  $name = "";  else   $name = $_GET ["name"];
if ( empty ($_GET ["level"]) == true)  $level = "";  else   $level = $_GET ["level"];
if ( empty ($_GET ["user"]) == true)  $user = "";  else   $user = $_GET ["user"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nomAdmin == "" && $mdpAdmin == "" && $name == "" && $level == "" && $user == "" )
{	if ( empty ($_POST ["nomAdmin"]) == true)  $nomAdmin = "";  else   $nomAdmin = $_POST ["nomAdmin"];
if ( empty ($_POST ["mdpAdmin"]) == true)  $mdpAdmin = "";  else   $mdpAdmin = $_POST ["mdpAdmin"];
if ( empty ($_POST ["name"]) == true)  $name = "";  else   $name = $_POST ["name"];
if ( empty ($_POST ["level"]) == true)  $level = "";  else   $level = $_POST ["level"];
if ( empty ($_POST ["user"]) == true)  $user = "";  else   $user = $_POST ["user"];
}
