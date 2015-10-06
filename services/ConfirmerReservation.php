<?php
// Service web du projet Réservations M2L
// Ecrit le 29/09/2015 par MrJ

// Ce service web permet à  un utilisateur de s'authentifier
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 3 paramètres : nom, mdp, numreservation
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à  éviter en exploitation) :
//     http://<hébergeur>/ConfirmerReservation.php?nom=zenelsy&mdp=ab&numreservation=1
// Les paramètres peuvent être passés par la méthode POST (à  privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ConfirmerReservation.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML à  générer

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramÃ¨tres de l'application
include_once ('../modele/include.parametres.php');

// crée une instance de DOMdocument
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';

// crée un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web Connecter - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire Ã  la racine du document XML
$doc->appendChild($elt_commentaire);

// Récupération des données transmises
// la fonction $_GET récupère une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
if ( empty ($_GET ["numreservation"]) == true)  $numreservation = "";  else   $numreservation = $_GET ["numreservation"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" && $mdp == "" && $numreservation=="")
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["numreservation"]) == true)  $numreservation = "";  else   $numreservation = $_POST ["numreservation"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $mdp == "" || $numreservation == "")
{	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web Ã  la base MySQL ("include_once" peut etre remplacé par "require_once")
include_once ('../modele/DAO.class.php');
$dao = new DAO();
$niveauUtilisateur = $dao->getNiveauUtilisateur($nom, $mdp);

if ( $niveauUtilisateur == "inconnu" )
	TraitementAnormal("Erreur : authentification incorrecte.");
else
{
	//vérification si le numéro de réservation est existant
	$reservationExistante = $dao->existeReservation($numreservation);
	if($reservationExistante == false)
		TraitementAnormal("Erreur : numéro de réservation inexistant.");
	else
	{
		//vérification si le demandeur est bien l'auteur
		$createur = $dao->estLeCreateur($nom, $numreservation);
		if($createur == false)
			TraitementAnormal("Erreur : vous n'êtes pas l'auteur de cette réservation.");
		else 
		{
			
		}
	}
}	
// ferme la connexion Ã  MySQL :
unset($dao);
}
// Mise en forme finale
$doc->formatOutput = true;
// renvoie le contenu XML
echo $doc->saveXML();
// fin du programme
exit;


// fonction de traitement des cas anormaux
function TraitementAnormal($msg)
{	// redéclaration des données globales utilisées dans la fonction
global $doc;
// crée l'élément 'data' à  la racine du document XML
$elt_data = $doc->createElement('data');
$doc->appendChild($elt_data);
// place l'élément 'reponse' juste après l'élément 'data'
$elt_reponse = $doc->createElement('reponse', $msg);
$elt_data->appendChild($elt_reponse);
return;
}


// fonction de traitement des cas normaux
function TraitementNormal()
{	
	// redéclaration des données globales utilisées dans la fonction
	global $doc;
	// crée l'élément 'data' à  la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'élément 'data'
	 $msg = "Enregistrement effectué ; vous allez recevoir un mail de confirmation";
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;
}
?>