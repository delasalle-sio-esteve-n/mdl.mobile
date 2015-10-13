<?php
// Service web du projet Réservations M2L
// Ecrit le 29/09/2015 par MrJ

// Ce service web permet à un utilisateur de s'authentifier
// et fournit un flux XML contenant un compte-rendu d'ex�cution

// Le service web doit recevoir 1 paramètre : nom
// Les paramètres peuvent être passés par la méthode GET (pratique pour les tests, mais à éviter en exploitation) :
//     http://<hébergeur>/DemanderMdp.php?nom=zenelsy
// Les paramètres peuvent être passés par la méthode POST (à privilégier en exploitation pour la confidentialité des données) :
//     http://<hébergeur>/DemanderMdp.php

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
$elt_commentaire = $doc->createComment('Service web Connecter - BTS SIO - Lyc�e De La Salle - Rennes');
// place ce commentaire à la racine du document XML
$doc->appendChild($elt_commentaire);

// Récupération des données transmises
// la fonction $_GET récupére une donnée passée en paramètre dans l'URL par la méthode GET
if ( empty ($_GET ["nom"]) == true)  $nom = "";  else   $nom = $_GET ["nom"];
// si l'URL ne contient pas les données, on regarde si elles ont été envoyées par la méthode POST
// la fonction $_POST récupère une donnée envoyées par la méthode POST
if ( $nom == "" )
{	
	if ( empty ($_POST ["nom"]) == true)  $nom = "";  else   $nom = $_POST ["nom"];
}

// Contrôle de la présence des paramètres
if ( $nom == "")
{	TraitementAnormal ("Erreur : données incomplètes.");
}
else
{	// connexion du serveur web à la base MySQL ("include_once" peut etre remplac� par "require_once")
include_once ('../modele/DAO.class.php');
$dao = new DAO();
//vérification si l'utilisateur existe
$utilisateur = $dao->getUtilisateur($nom);

if ( $utilisateur == null )
	TraitementAnormal("Erreur : nom d'utilisateur inexistant.");
else
{
	
	$nouvMdp = $outils ->creeMdp();
	$dao ->modifierMdpUser($nom, $nouveauMdp);
	$dao ->envoyerMdp($nom, $nouveauMdp);
	TraitementNormal("Vous allez revevoir un mail avec votre nouveau mot de passe.");
	
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
	 $msg = "Enregistrement effectu� ; vous allez recevoir un mail de confirmation";
	$elt_reponse = $doc->createElement('reponse', $msg);
	$elt_data->appendChild($elt_reponse);
	return;
}
?>