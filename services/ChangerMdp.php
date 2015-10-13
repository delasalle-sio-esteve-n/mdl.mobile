<?php
// Service web du projet Réservations M2L
// Ecrit le 29/09/2015 par MrJ

// Ce service web permet à un utilisateur de s'authentifier
// et fournit un flux XML contenant un compte-rendu d'exécution

// Le service web doit recevoir 4 paramètre : nom, ancienMdp, nouveauMdp, confirmationMdp
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/ChangerMdp.php?nom=zenelsy&ancienMdp=ab&nouveauMdp=123&confirmationMdp=1234
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/ChangerMdp.php

// déclaration des variables globales pour pouvoir les utiliser aussi dans les fonctions
global $doc;		// le document XML à générer

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
$elt_commentaire = $doc->createComment('Service web Connecter - BTS SIO - Lycée De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// Récupération des données transmises
// la fonction $_GET récupére une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
if ( empty ($_GET ["ancienMdp"]) == true)  $ancienMdp = "";  else   $ancienMdp = $_GET ["ancienMdp"];
if ( empty ($_GET ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_GET ["nouveauMdp"];
if ( empty ($_GET ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_GET ["confirmationMdp"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" || $ancienMdp == "" || $nouveauMdp == "" ||$confirmationMdp == "")
{	
	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
	if ( empty ($_POST ["ancienMdp"]) == true)  $ancienMdp = "";  else   $ancienMdp = $_POST ["ancienMdp"];
	if ( empty ($_POST ["nouveauMdp"]) == true)  $nouveauMdp = "";  else   $nouveauMdp = $_POST ["nouveauMdp"];
	if ( empty ($_POST ["confirmationMdp"]) == true)  $confirmationMdp = "";  else   $confirmationMdp = $_POST ["confirmationMdp"];
}

// Contrôle de la présence des paramètres
if ( $nom == "" || $ancienMdp == "" || $nouveauMdp == "" ||$confirmationMdp == "")
{	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut etre remplac� par "require_once")
include_once ('../modele/DAO.class.php');
$dao = new DAO();

//vérification si les deux mots de passes sont identiques
if ( $nouveauMdp == $confirmationMdp )
	TraitementAnormal("Erreur : le nouveau mot de passen et sa confirmation sont différents.");
else
{
	
	$utilisateur = $dao ->getNiveauUtilisateur($nom, $ancienMdp);
	//vérification si l'utilisateur n'est pas identifié
	if($utilisateur == null)
	{
		TraitementAnormal("Erreur : authentification incorrecte.");
	}
	else 
	{
		TraitementNormal();
		//enregistrement du mot de passe dans la base de donées
		$dao ->modifierMdpUser($nom, $nouveauMdp);
		//envie d'un email avec le nouveau mot de passe
		$dao ->envoyerMdp($nom, $nouveauMdp);
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
{	// redéclaration des donn�es globales utilis�es dans la fonction
global $doc;
// crée l'élément 'data' � la racine du document XML
$elt_data = $doc->createElement('data');
$doc->appendChild($elt_data);
// place l'élément 'reponse' juste apr�s l'�l�ment 'data'
$elt_reponse = $doc->createElement('reponse', $msg);
$elt_data->appendChild($elt_reponse);
return;
}


// fonction de traitement des cas normaux
function TraitementNormal()
{	
	// redéclaration des donn�es globales utilis�es dans la fonction
	global $doc;
	// crée l'élément 'data' � la racine du document XML
	$elt_data = $doc->createElement('data');
	$doc->appendChild($elt_data);
	// place l'élément 'reponse' juste après l'�l�ment 'data'
	 $msg = "Enregistrement effectué ; vous allez recevoir un mail de confirmation";
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;
}
?>