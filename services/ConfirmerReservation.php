<?php
// Service web du projet R�servations M2L
// Ecrit le 29/09/2015 par MrJ

// Ce service web permet � un utilisateur de s'authentifier
// et fournit un flux XML contenant un compte-rendu d'ex�cution

// Le service web doit recevoir 3 param�tres : nom, mdp, numreservation
// Les param�tres peuvent �tre pass�s par la m�thode GET (pratique pour les tests, mais � �viter en exploitation) :
//     http://<h�bergeur>/ConfirmerReservation.php?nom=zenelsy&mdp=ab&numreservation=1
// Les param�tres peuvent �tre pass�s par la m�thode POST (� privil�gier en exploitation pour la confidentialit� des donn�es) :
//     http://<h�bergeur>/ConfirmerReservation.php

// d�claration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML � g�n�rer

// inclusion de la classe Outils
include_once ('../modele/Outils.class.php');
// inclusion des paramètres de l'application
include_once ('../modele/include.parametres.php');

// cr�e une instance de DOMdocument
$doc = new DOMDocument();

// specifie la version et le type d'encodage
$doc->version = '1.0';
$doc->encoding = 'ISO-8859-1';

// cr�e un commentaire et l'encode en ISO
$elt_commentaire = $doc->createComment('Service web Connecter - BTS SIO - Lyc�e De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// R�cup�ration des donn�es transmises
// la fonction $_GET r�cup�re une donn�e pass�e en param�tre dans l'URL par la m�thode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["mdp"]) == true)  $mdp = "";  else   $mdp = $_GET ["mdp"];
if ( empty ($_GET ["numreservation"]) == true)  $numreservation = "";  else   $numreservation = $_GET ["numreservation"];
// si l'URL ne contient pas les donn�es, on regarde si elles ont �t� envoy�es par la m�thode POST
// la fonction $_POST r�cup�re une donn�e envoy�es par la m�thode POST
if ( $nom == "" && $mdp == "" && $numreservation=="")
{	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["mdp"]) == true)  $mdp = "";  else   $mdp = $_POST ["mdp"];
	if ( empty ($_POST ["numreservation"]) == true)  $numreservation = "";  else   $numreservation = $_POST ["numreservation"];
}

// Contr�le de la pr�sence des param�tres
if ( $nom == "" || $mdp == "" || $numreservation == "")
{	TraitementAnormal ("Erreur : donn�es incompl�tes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut etre remplac� par "require_once")
include_once ('../modele/DAO.class.php');
$dao = new DAO();
$niveauUtilisateur = $dao->getNiveauUtilisateur($nom, $mdp);

if ( $niveauUtilisateur == "inconnu" )
	TraitementAnormal("Erreur : authentification incorrecte.");
else
{
	//v�rification si le num�ro de r�servation est existant
	$reservationExistante = $dao->existeReservation($numreservation);
	if($reservationExistante == false)
		TraitementAnormal("Erreur : num�ro de r�servation inexistant.");
	else
	{
		//v�rification si le demandeur est bien l'auteur
		$createur = $dao->estLeCreateur($nom, $numreservation);
		if($createur == false)
			TraitementAnormal("Erreur : vous n'�tes pas l'auteur de cette r�servation.");
		else 
		{
			
		}
	}
}	
// ferme la connexion à MySQL :
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
{	// red�claration des donn�es globales utilis�es dans la fonction
global $doc;
// cr�e l'�l�ment 'data' � la racine du document XML
$elt_data = $doc->createElement('data');
$doc->appendChild($elt_data);
// place l'�l�ment 'reponse' juste apr�s l'�l�ment 'data'
$elt_reponse = $doc->createElement('reponse', $msg);
$elt_data->appendChild($elt_reponse);
return;
}


// fonction de traitement des cas normaux
function TraitementNormal()
{	
	// red�claration des donn�es globales utilis�es dans la fonction
	global $doc;
	// cr�e l'�l�ment 'data' � la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'�l�ment 'reponse' juste apr�s l'�l�ment 'data'
	 $msg = "Enregistrement effectu� ; vous allez recevoir un mail de confirmation";
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;
}
?>