<?php
// -------------------------------------------------------------------------------------------------------------------------
//                                                 DAO : Data Access Object
//                   Cette classe fournit des méthodes d'accès à la bdd mrbs (projet Réservations M2L)
//                       Auteur : JM Cartron                       Dernière modification : 21/5/2015
// -------------------------------------------------------------------------------------------------------------------------

// liste des méthodes de cette classe (dans l'ordre d'apparition dans la classe) :

// __construct                   : le constructeur crée la connexion $cnx à la base de données
// __destruct                    : le destructeur ferme la connexion $cnx à la base de données
// getNiveauUtilisateur          : fournit le niveau d'un utilisateur identifié par $nomUser et $mdpUser
// genererUnDigicode             : génération aléatoire d'un digicode de 6 caractères hexadécimaux
// creerLesDigicodesManquants    : mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
// listeReservations             : fournit la liste des réservations à venir d'un utilisateur ($nomUser)
// existeReservation             : fournit true si la réservation ($idReservation) existe, false sinon
// estLeCreateur                 : teste si un utilisateur ($nomUser) est le créateur d'une réservation ($idReservation)
// getReservation                : fournit un objet Reservation à partir de son identifiant $idReservation
// getUtilisateur                : fournit un objet Utilisateur à partir de son nom $nomUser
// confirmerReservation          : enregistre la confirmation de réservation dans la bdd
// annulerReservation            : enregistre l'annulation de réservation dans la bdd
// existeUtilisateur             : fournit true si l'utilisateur ($nomUser) existe, false sinon
// modifierMdpUser               : enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir hashé en MD5
// envoyerMdp                    : envoie un mail à l'utilisateur avec son nouveau mot de passe
// testerDigicodeSalle           : teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation
// testerDigicodeBatiment        : teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation de salle quelconque
// enregistrerUtilisateur        : enregistre l'utilisateur dans la bdd
// aPasseDesReservations         : recherche si l'utilisateur ($name) a passé des réservations à venir
// supprimerUtilisateur          : supprime l'utilisateur dans la bdd

// listeSalles                   : fournit la liste des salles disponibles à la réservation

// Ce fichier est destiné à être inclus dans les services web PHP du projet Réservations M2L
// 2 possibilités pour inclure ce fichier :
//     include_once ('Class.DAO.php');
//     require_once ('Class.DAO.php');

// certaines méthodes nécessitent d'inclure auparavant les fichiers Class.Reservation.php, Class.Utilisateur.php et Class.Outils.php
include_once ('Utilisateur.class.php');
include_once ('Reservation.class.php');
include_once ('Salle.class.php');
include_once ('Outils.class.php');

// inclusion des paramètres de l'application
include_once ('include.parametres.php');

// début de la classe DAO (Data Access Object)
class DAO
{
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------- Membres privés de la classe ---------------------------------------
	// ------------------------------------------------------------------------------------------------------
		
	private $cnx;				// la connexion à la base de données
	
	// ------------------------------------------------------------------------------------------------------
	// ---------------------------------- Constructeur et destructeur ---------------------------------------
	// ------------------------------------------------------------------------------------------------------
	public function __construct() {
		global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
		try
		{	$this->cnx = new PDO ("mysql:host=" . $PARAM_HOTE . ";port=" . $PARAM_PORT . ";dbname=" . $PARAM_BDD,
							$PARAM_USER,
							$PARAM_PWD);
			return true;
		}
		catch (Exception $ex)
		{	echo ("Echec de la connexion a la base de donnees <br>");
			echo ("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage());
			return false;
		}
	}
	
	public function __destruct() {
		unset($this->cnx);
	}

	// ------------------------------------------------------------------------------------------------------
	// -------------------------------------- Méthodes d'instances ------------------------------------------
	// ------------------------------------------------------------------------------------------------------
	
	// fournit le niveau d'un utilisateur identifié par $nomUser et $mdpUser
	// renvoie "utilisateur" ou "administrateur" si authentification correcte, "inconnu" sinon
	// modifié par Jim le 5/5/2015
	public function  getNiveauUtilisateur($nomUser, $mdpUser)
	{	// préparation de la requête de recherche
		$txt_req = "Select level from mrbs_users where name = :nomUser and password = :mdpUserCrypte and level > 0";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		$req->bindValue("mdpUserCrypte", md5($mdpUser), PDO::PARAM_STR);		
		// extraction des données
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		// traitement de la réponse
		$reponse = "inconnu";
		if ($uneLigne)
		{	$level = $uneLigne->level;
			if ($level == "1") $reponse = "utilisateur";
			if ($level == "2") $reponse = "administrateur";
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
		// fourniture de la réponse
		return $reponse;
	}	

	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
	// modifié par Jim le 5/5/2015
	public function genererUnDigicode()
	{   $caracteresUtilisables = "0123456789ABCDEF";
		$digicode = "";
		// on ajoute 6 caractères
		for ($i = 1 ; $i <= 6 ; $i++)
		{   // on tire au hasard un caractère (position aléatoire entre 0 et le nombre de caractères - 1)
			$position = rand (0, strlen($caracteresUtilisables)-1);
			// on récupère le caracère correspondant à la position dans $caracteresUtilisables
			$unCaractere = substr ($caracteresUtilisables, $position, 1);
			// on ajoute ce caractère au digicode
			$digicode = $digicode . $unCaractere;
		}
		// fourniture de la réponse
		return $digicode;
	}	
	
	// mise à jour de la table mrbs_entry_digicode (si besoin) pour créer les digicodes manquants
	// cette fonction peut dépanner en cas d'absence des triggers chargés de créer les digicodes
	// modifié par Jim le 5/5/2015
	public function creerLesDigicodesManquants()
	{	// préparation de la requete de recherche des réservations sans digicode
		$txt_req1 = "Select id from mrbs_entry where id not in (select id from mrbs_entry_digicode)";
		$req1 = $this->cnx->prepare($txt_req1);
		// extraction des données
		$req1->execute();
		// extrait une ligne du résultat :
		$uneLigne = $req1->fetch(PDO::FETCH_OBJ);
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// génération aléatoire d'un digicode de 6 caractères hexadécimaux
			$digicode = $this->genererUnDigicode();
			// préparation de la requete d'insertion
			$txt_req2 = "insert into mrbs_entry_digicode (id, digicode) values (:id, :digicode)";
			$req2 = $this->cnx->prepare($txt_req2);
			// liaison de la requête et de ses paramètres
			$req2->bindValue("id", $uneLigne->id, PDO::PARAM_INT);
			$req2->bindValue("digicode", $digicode, PDO::PARAM_STR);
			// exécution de la requête
			$req2->execute();
			// extrait la ligne suivante
			$uneLigne = $req1->fetch(PDO::FETCH_OBJ);
		}
		// libère les ressources du jeu de données
		$req1->closeCursor();
		return;
	}	

	// fournit la liste des réservations à venir d'un utilisateur ($nomUser)
	// le résultat est fourni sous forme d'une collection d'objets Reservation
	// modifié par Jim le 11/5/2015
	public function listeReservations($nomUser)
	{	// préparation de la requete de recherche
		$txt_req = "Select mrbs_entry.id, timestamp, start_time, end_time, room_name, status, digicode";
		$txt_req = $txt_req . " from mrbs_entry, mrbs_room, mrbs_entry_digicode";
		$txt_req = $txt_req . " where mrbs_entry.room_id = mrbs_room.id";
		$txt_req = $txt_req . " and mrbs_entry.id = mrbs_entry_digicode.id";
		$txt_req = $txt_req . " and create_by = :nomUser";
		$txt_req = $txt_req . " and start_time > :time";
		$txt_req = $txt_req . " order by start_time, room_name";
		
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		$req->bindValue("time", time(), PDO::PARAM_INT);		
		// extraction des données
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		
		// construction d'une collection d'objets Reservation
		$lesReservations = array();
		// tant qu'une ligne est trouvée :
		while ($uneLigne)
		{	// création d'un objet Reservation
			$unId = $uneLigne->id;
			$unTimeStamp = $uneLigne->timestamp;
			$unStartTime = $uneLigne->start_time;
			$unEndTime = $uneLigne->end_time;
			$unRoomName = $uneLigne->room_name;
			$unStatus = $uneLigne->status;
			$unDigicode = $uneLigne->digicode;
			
			$uneReservation = new Reservation($unId, $unTimeStamp, $unStartTime, $unEndTime, $unRoomName, $unStatus, $unDigicode);
			// ajout de la réservation à la collection
			$lesReservations[] = $uneReservation;
			// extrait la ligne suivante
			$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
		// fourniture de la collection
		return $lesReservations;
	}

	// fournit true si la réservation ($idReservation) existe, false sinon
	// modifié par MrJ le 22/09/2015
	public function existeReservation($idReservation)
	{	
		//préparation de la requete de recherche
		$txt_req = "Select mrbs_entry.id ";
		$txt_req = $txt_req . "from mrbs_entry ";
		$txt_req = $txt_req . "where mrbs_entry.id = :idRes;";
		$req = $this->cnx->prepare($txt_req);
		
		//liaison du paramètres avec la requete
		$req->bindValue("idRes", $idReservation , PDO::PARAM_INT);
		
		//extraction de la réservation
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
	
		
		//fourniture de la réponse
		if($uneLigne != "")
		{
			//réservation existant
			return true;
		}
		else 
		{
			//réservation inexistant
			return false ;
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
	}
	
	// teste si un utilisateur ($nomUser) est le créateur d'une réservation ($idReservation)
	// renvoie true si l'utilisateur est bien le créateur, false sinon
	// modifié par MrJ le 22/09/2015
	public function estLeCreateur($nomUser, $idReservation)
	{	
		//préparation de la requete de recherche 
		$txt_req = "Select mrbs_entry.create_by ";
		$txt_req = $txt_req . "from mrbs_entry ";
		$txt_req = $txt_req . "where mrbs_entry.id=:idRes;";		
		$req = $this->cnx->prepare($txt_req);
		
		//liaison du paramètres avec la requete
		$req->bindValue("idRes", $idReservation , PDO::PARAM_INT);
		
		//extraction de la réservation
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		
		//affectation du créateur de la réservation
		$createur=$uneLigne->create_by;
		
		if($createur== $nomUser)
		{
			//si l'utilisateur est bien le créateur
			return true;
		}
		else 
		{
			//l'utilisateur n'est pas le créateur
			return false;
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
	}
	
	// fournit un objet Reservation à partir de son identifiant
	// fournit la valeur null si l'identifiant n'existe pas
	// modifié par MrJ le 29/09/2015
	public function getReservation($idReservation)
	{	
		//préparation de la requete de recherche
		$txt_req = "Select mrbs_entry.id, mrbs_entry.timestamp, mrbs_entry.start_time,mrbs_entry.end_time, mrbs_room.room_name, mrbs_entry.status,mrbs_entry_digicode.digicode ";
		$txt_req = $txt_req ."from mrbs_entry, mrbs_room,mrbs_entry_digicode ";
		$txt_req = $txt_req . "where mrbs_room.id = mrbs_entry.room_id ";
		$txt_req = $txt_req . "and mrbs_entry.id = mrbs_entry_digicode.id ";
		$txt_req = $txt_req . "and mrbs_entry.id = :idReservation;";
		$req = $this->cnx->prepare($txt_req);
				
		//liason du paramètres avec la requete
		$req->bindValue("idReservation", $idReservation , PDO::PARAM_INT);
		
		//extraction des résultats
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		
		if($uneLigne == "")
		{
			//retourne null si l'idenfiant n'existe pas	
			return null;
		}
		else 
		{
			// création d'un objet Reservation
			$unId = $uneLigne->id;
			$unTimeStamp = $uneLigne->timestamp;
			$unStartTime = $uneLigne->start_time;
			$unEndTime = $uneLigne->end_time;
			$unRoomName = $uneLigne->room_name;
			$unStatus = $uneLigne->status;
			$unDigicode = $uneLigne->digicode;
			
			$uneReservation = new Reservation($unId, $unTimeStamp, $unStartTime, $unEndTime, $unRoomName, $unStatus, $unDigicode);
			
			//retourne la réservation
			return $uneReservation ;
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
	}
	// fournit un objet Utilisateur à partir de son nom ($nomUser)
	// fournit la valeur null si le nom n'existe pas
	// modifié par MrJ le 29/09/2015
	public function getUtilisateur($nomUser)
	{	
		//préparation de la requete de recherche
		$txt_req = "Select mrbs_users.id, mrbs_users.name,mrbs_users.level, mrbs_users.password, mrbs_users.email ";
		$txt_req = $txt_req . "from mrbs_users ";
		$txt_req = $txt_req . "where mrbs_users.name = :nomUser";
		$req = $this->cnx->prepare($txt_req);
		
		//liason du paramètre avec la requete
		$req->bindValue("nomUser", $nomUser , PDO::PARAM_STR);
		// extraction des résultats
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		
		if($uneLigne != "")
		{
			// création d'un objet Utilisateur
			$unId = $uneLigne->id;
			$unNiveau = $uneLigne->level ;
			$unNom = $uneLigne->name;
			$unMotDePasse = $uneLigne->password ;
			$unEmail = $uneLigne->email ;
			$unUtilisateur = new Utilisateur($unId, $unNiveau, $unNom, $unMotDePasse, $unEmail);
			//retourne un Utilisateur
			return $unUtilisateur;
		}
		else
		{
			//retourne null si le nom n'existe pas
			return null;
		}
		// libère les ressources du jeu de données
		$req->closeCursor();
	}
	
	// enregistre la confirmation de réservation dans la bdd
	// modifié par MrJ le 29/09/2015
	public function confirmerReservation($idReservation)
	{	
		//preparation de la requte de recherche
		$txt_req = "Update mrbs_entry ";
		$txt_req = $txt_req ."set  mrbs_entry.status = 0 ";
		$txt_req = $txt_req . "where mrbs_entry.id= :idReservation;";
		$req = $this->cnx->prepare($txt_req);
		
		//liason du paramètres à la requete
		$req->bindValue("idReservation", $idReservation, PDO::PARAM_INT);
		$req->execute();
		
		// libère les ressources du jeu de données
		$req->closeCursor();
		
	}
	
	// enregistre l'annulation de réservation dans la bdd
	// modifié par MrJ le 29/09/2015
	public function annulerReservation($idReservation)
	{	
		
		//preparation de la requete de recherche
		$txt_req = "Delete from mrbs_entry_digicode ";
		$txt_req = $txt_req . "where id = :idReservation";
		$req = $this->cnx->prepare($txt_req);
			
		//liason du paramètres à la requete
		$req->bindValue("idReservation", $idReservation, PDO::PARAM_INT);
		
		// exécution de la requete
		$req->execute();
		
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		//preparation de la requete de recherche
		$txt_req = "Delete from mrbs_entry ";
		$txt_req = $txt_req . "where id = :idReservation";
		$req = $this->cnx->prepare($txt_req);
		
		//liason du paramètres à la requete
		$req->bindValue("idReservation", $idReservation, PDO::PARAM_INT);
		
		// exécution de la requete
		$req->execute();
		
		// libère les ressources du jeu de données
		$req->closeCursor();
				
	}
	
	// fournit true si l'utilisateur ($nomUser) existe, false sinon
	// modifié par Jim le 5/5/2015
	public function existeUtilisateur($nomUser)
	{	// préparation de la requete de recherche
		$txt_req = "Select count(*) from mrbs_users where name = :nomUser";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("nomUser", $nomUser, PDO::PARAM_STR);
		// exécution de la requete
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return false;
		else
			return true;
	}

	// enregistre le nouveau mot de passe de l'utilisateur dans la bdd après l'avoir hashé en MD5
	// modifié par Jim le 6/5/2015
	public function modifierMdpUser($nomUser, $nouveauMdp)
	{	
		//hashage du mot de passe en MD5
		$mdp=md5($nouveauMdp);
		
		//préparation de la requete de recherche
		$txt_req = "Update mrbs_users ";
		$txt_req = $txt_req . "set password = :nouveauMdp ";
		$txt_req = $txt_req . "where name = :nomUser ;";
		$req=$this->cnx->prepare($txt_req);
		
		//liaison de la requête et de ses paramètres
		$req->bindValue("nomUser",$nomUser,PDO::PARAM_STR);
		$req->bindValue("nouveauMdp",$mdp,PDO::PARAM_STR);
		
		// exécution de la requete
		$req->execute();
		
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		
		
	}

	// envoie un mail à l'utilisateur avec son nouveau mot de passe
	// retourne true si envoi correct, false en cas de problème d'envoi
	// modifié par Esteve le 13/10/2015
	public function envoyerMdp($nomUser, $nouveauMdp)
	{	
		
		$txt_req = "Select email FROM mrbs_users WHERE :user =	name AND password = :password > 0";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("user", $nomUser, PDO::PARAM_STR);
		$req->bindValue("password", md5($nouveauMdp), PDO::PARAM_STR);
		// extraction des données
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
	
		$adresseDestinataire = $uneLigne;
		$sujet = 'MRBS Votre nouveau mot de passe';
		$message = 'Votre nouveau mot de passe  est '.$nouveauMdp;
		$adresseEmetteur = 'delasalle.sio.esnault.j@gmail.com';
				if($adresseDestinataire != null)
				{
	Outils::envoyerMail($adresseDestinataire, $sujet, $message, $adresseEmetteur);
	return true;
				}
				else 
				{
					return false;
				}
	
	}

	// teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation
	// de la salle indiquée ($idSalle) pour l'heure courante
	// fournit la valeur 0 si le digicode n'est pas bon, 1 si le digicode est bon
	// modifié par Jim le 18/5/2015
	public function testerDigicodeSalle($idSalle, $digicodeSaisi)
	{	global $DELAI_DIGICODE;
		// préparation de la requete de recherche
		$txt_req = "Select count(*)";
		$txt_req = $txt_req . " from mrbs_entry, mrbs_entry_digicode";
		$txt_req = $txt_req . " where mrbs_entry.id = mrbs_entry_digicode.id";
		$txt_req = $txt_req . " and room_id = :idSalle";
		$txt_req = $txt_req . " and digicode = :digicodeSaisi";
		$txt_req = $txt_req . " and (start_time - :delaiDigicode) < " . time();
		$txt_req = $txt_req . " and (end_time + :delaiDigicode) > " . time();
		
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("idSalle", $idSalle, PDO::PARAM_STR);
		$req->bindValue("digicodeSaisi", $digicodeSaisi, PDO::PARAM_STR);	
		$req->bindValue("delaiDigicode", $DELAI_DIGICODE, PDO::PARAM_INT);	
				
		// exécution de la requete
		$req->execute();
		$nbReponses = $req->fetchColumn(0);
		// libère les ressources du jeu de données
		$req->closeCursor();
		
		// fourniture de la réponse
		if ($nbReponses == 0)
			return "0";
		else
			return "1";
	}
	
	// teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation de salle quelconque
	// pour l'heure courante
	// fournit la valeur 0 si le digicode n'est pas bon, 1 si le digicode est bon
	// modifié par Jim le 18/5/2015
// teste si le digicode saisi ($digicodeSaisi) correspond bien à une réservation de salle quelconque
	// pour l'heure courante
	// fournit la valeur 0 si le digicode n'est pas bon, 1 si le digicode est bon
	// modifié par MrJ le 13/10/2015
	public function testerDigicodeBatiment($digicodeSaisi)
	{	
		//la date d'aujourd'hui au format UNIX
		$date=time();
		
		//récupération de la réservation
		$txt_req = "Select mrbs_entry.id ";
		$txt_req = $txt_req . "From mrbs_entry_digicode, mrbs_entry ";
		$txt_req =  $txt_req . "Where mrbs_entry_digicode.id = mrbs_entry.id ";
		$txt_req = $txt_req . "And mrbs_entry_digicode.digicode = :digicode ";
		$txt_req = $txt_req . "And :heure BETWEEN start_time AND end_time;";
		
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req -> bindValue("digicode", $digicodeSaisi, PDO::PARAM_STR);
		$req -> bindValue("heure", $date, PDO::PARAM_INT);
		
		//exécution de la requete
		 $req->execute();
		 $uneLigne = $req->fetch(PDO::FETCH_OBJ);
		 
		 if($uneLigne == "")
		 {
		 	return 0;
		 }
		 else
		 {
		 	return 1;
		 }
		 
		 // libère les ressources du jeu de données
		 $req->closeCursor();
			
	}

	// enregistre l'utilisateur dans la bdd
	// modifié par Jim le 6/5/2015
	public function enregistrerUtilisateur($name, $level, $password, $email)
	{	// préparation de la requete
		$txt_req = "insert into mrbs_users (level, name, password, email) values (:level, :name, :password, :email)";
		$req = $this->cnx->prepare($txt_req);
		// liaison de la requête et de ses paramètres
		$req->bindValue("level", utf8_decode($level), PDO::PARAM_STR);
		$req->bindValue("name", utf8_decode($name), PDO::PARAM_STR);
		$req->bindValue("password", utf8_decode(md5($password)), PDO::PARAM_STR);
		$req->bindValue("email", utf8_decode($email), PDO::PARAM_STR);
		// exécution de la requete
		$ok = $req->execute();
		return $ok ;
	}

	// recherche si un utilisateur a passé des réservations à venir et retourne un booléen
	// modifié par Mrj le 6/10/2015
	public function aPasseDesReservations($name)
	{	
		//la date d'aujourd'hui au format UNIX
		$date=time();
		
		//récupération de reservations
		$txt_req = "Select mrbs_entry.id ";
		$txt_req = $txt_req . "From mrbs_entry ";
		$txt_req = $txt_req . "Where mrbs_entry.create_by= :name ";
		$txt_req = $txt_req . "And mrbs_entry.end_time > :date ;";
		$req = $this->cnx->prepare($txt_req);
		
		//liason du paramètres à la requete
		$req->bindValue("name", $name, PDO::PARAM_STR);
		$req->bindValue("date", $date, PDO::PARAM_INT);
		// exécution de la requete
		$req->execute();
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		
		if($uneLigne == "")
		{			
			return false;
		}
		else
		{			
			return true;
		}
	}
	
	// supprime l'utilisateur dans la bdd
	// modifié par MrJ le 6/10/2015
	public function supprimerUtilisateur($name)
	{	
		
		//suppression de l'utilisateur
		$txt_req = "Delete from mrbs_users ";
		$txt_req = $txt_req . "where name = :name";
		$req = $this->cnx->prepare($txt_req);
		
		//liason du paramètres à la requete
		$req->bindValue("name", $name, PDO::PARAM_STR);
		
		// exécution de la requete
		$req->execute();
		
		// libère les ressources du jeu de données	
		$req->closeCursor();
		
		return true;
		
	}		
	
	// fournit la liste des salles disponibles à la réservation
	// le résultat est fourni sous forme d'une collection d'objets Salle
	// modifié par Jim le 6/5/2015
function listeSalles()
	{	
		//récupération des salles	
		$txt_req = "Select mrbs_room.id, capacity, room_name, room_admin_email, area_name From mrbs_room, mrbs_area Where mrbs_area.id = mrbs_room.area_id ";
		//exéctuion de la requete
		$req = $this->cnx->prepare($txt_req);
		$req->execute();
		
		$uneLigne = $req->fetch(PDO::FETCH_OBJ);
		
		$lesUtilisateurs = array();
		
		while($uneLigne)
		{
			$unId = $uneLigne -> id;
			$UneCapacite = $uneLigne -> capacity;
			$UnNom = $uneLigne -> room_name;
			$unEmail = $uneLigne -> room_admin_email;
			$UneArea = $uneLigne -> area_name;
			
			$unUtilisateur = new Utilisateur($id, $UneCapacite,  $UnNom, $unEmail, $UneArea);
			
			$lesUtilisateurs[] = $unUtilisateur;
			
			$uneLigne = $req -> fetch(PDO::FETCH_OBJ);
		}
		
		$req -> closeCursor();
		return $lesUtilisateurs;
	}
	
}
	
 // fin de la classe DAO

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!
