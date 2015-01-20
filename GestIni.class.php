  
<?php
	/**
	  * @class GestIni 
	  * @brief Classe de gestion des fichiers INI
	  * @author GRIMAUD Pierre
	  * @version 1
	  * @date   01/08/2013
	  * @file   GestIni.class.php
	  */
	class GestIni
   {
		/**
		 * @fn static IniLit($Section, $Cle, $ValeurDefault, $Fichier)
		 * @brief Permet de lire une clé dans un fichier INI
		 *
		 * @param $Section Chaine contenant le nom de la section recherché
		 * @param $Cle Chaine contenant le nom de la clé recherché
		 * @param $ValeurDefault Chaine la valeur à retourné si la clé n'est pas trouvé
		 * @param $Fichier Chaine contenant le nom et le chemin du fichier INI
		 * @return chaine contenant la valeur de la clé recherché ou la valeur par défaut
		 */
		public static function IniLit($Section, $Cle, $ValeurDefault, $Fichier)
		{	
			try
			{
				//test de l'existance du fichier et ouverture de celui ci
				if(file_exists($Fichier) && $fichier_lecture=file($Fichier))
				{
					//parcours des lignes lut dans le fichier
					foreach($fichier_lecture as $ligne)
					{
						//suppression des espace avant et après la chaine
						$ligne_Sans_Espace=trim($ligne);
						
						//ont regarde si la ligne correspond à une section 
						if(preg_match("#^\[(.+)\]$#",$ligne_Sans_Espace,$Resultat_Section))
						{
							//ont mémorise la section en cours de lecture 
							$SectionEnCours = $Resultat_Section[1];
						}
						else
						{
							//la ligne en cours ne correspond pas à une section ont regarde donc si ont ce trouve dans la section recherchée
							if($SectionEnCours==$Section)
							{
								//la section en cours correspond à la section recherche donc on va regarder les clé pour rechercher celle qui nous est demandée
								if(strpos($ligne,$Cle . "=")===0) //si la clé demander correspond à la position 0 de la ligne alors ont a trouvé la clé demandée
								{
									$valeur_Recherche=end(explode("=",$ligne,2));
								}
							}
						}
					}
					//ont regarde si une valeur à été trouvé sinon ont retourne la valeur par defaut passé en paramètre
					if ($valeur_Recherche=="")
					{
						return $ValeurDefault;
					}
					else
					{
						return $valeur_Recherche;
					}
				}
				else
				{
					return $ValeurDefault;
				}
			}
			Catch (Exception $ex)
			{
				return $e->getMessage();
			}
		}
		
		
		/**
		 * @fn static IniEcrit($Section, $Cle, $ValeurAEcrire, $Fichier)
		 * @brief Permet d'écrire une clé dans un fichier INI,
		 *		- si seul la section est complèté elle sera supprimée ($cle et $ValeurAEcrire = NULL).
		 *		- si la section et la clé sont remplis mais pas la valeur , la clé sera supprimé ($ValeurAEcrire = NULL).
		 *		- si tous les paramètre sont précisé alors la clé sera ajouter ou modifié si elle est déjà présente dans le fichier.
		 * @param $Section Chaine contenant le nom de la section à écrire
		 * @param $Cle Chaine contenant le nom de la clé à écrire
		 * @param $ValeurAEcrire Chaine la valeur à dans le fichier
		 * @param $Fichier Chaine contenant le nom et le chemin du fichier INI
		 * @return Boolean True si l'écriture à été faite sinon False
		 */
		public static function IniEcrit($Section, $Cle, $ValeurAEcrire, $Fichier)
		{
			$array=array();
			try
			{
				if(is_writable($Fichier))
				{
					//test de l'existance du fichier et ouverture de celui ci
					if(file_exists($Fichier) && $fichier_lecture=file($Fichier))
					{
						//parcour des lignes du fichier pour remplir un tableau que l'on modifira ensuite puit qui sera sauvegardé dans le fichier INI
						foreach($fichier_lecture as $ligne)
						{
							//suppression des espace avant et après la chaine
							$ligne_Sans_Espace=trim($ligne);
							//ont regarde si la ligne correspond à une section 
							if(preg_match("#^\[(.+)\]$#",$ligne_Sans_Espace,$Resultat_Section))
							{
								//ont mémorise la section en cours de lecture 
								$SectionEnCours = $Resultat_Section[1];
								$array[$SectionEnCours]=array();
							}
							else
							{
								//on découpe la ligne pour la mettre dans le tableau
								list($CleEnCours,$valeurEnCours)=explode("=",$ligne,2);
								if(!isset($valeurEnCours)) // S'il n'y a pas de valeur
								{
									 $valeurEnCours=''; // On donne une chaîne vide
								}
								$array[$SectionEnCours][$CleEnCours]=$valeurEnCours;
							}
						}
						
						//le tableau est replis grace au fichier donc ont regarde que action doit être faite
						//seul la section est complèté donc il faut supprimer la section
						if (isset($Section) && !isset($Cle) && !isset($ValeurAEcrire))
						{
							if(isset($array[$Section])) 
							{
								unset($array[$Section]);
							}
						}
						elseif (isset($Section) && isset($Cle) && !isset($ValeurAEcrire)) //la section et la clé sont remplis mais pas la valeur il faut donc supprimer la clé
						{
							if(isset($array[$Section][$Cle])) 
							{
								unset($array[$Section][$Cle]);
							}
						}
						elseif (isset($Section) && isset($Cle) && isset($ValeurAEcrire)) //toutes les info sont renseigné, donc il faut modifier ou ajoute la clé dans la section
						{
							$array[$Section][$Cle]=$ValeurAEcrire;
						}
						
						//convertion du tableau en chaine pour le sauvegarder
						$Contenu_fichier="";
						foreach($array as $SectionEnCours => $Liste_Cle) //Pour chaque section du tableau
						{
							if ($SectionEnCours!='') //si les section lut n'est pas vide
							{
								$Contenu_fichier .= "[" . $SectionEnCours . "]" ."\r\n";//ecriture de la section dans la chaine temporaire
							}
							foreach($Liste_Cle as $CleEnCours => $valeurEnCours)//pour chaque clé de la section en cours
							{
								if (rtrim($CleEnCours)!='' && isset($CleEnCours))//si la clé lut n'est pas vide
								{
									//echo rtrim($CleEnCours) . " = " . $valeurEnCours. " <BR>";
									$Contenu_fichier .="" . $CleEnCours."=" . rtrim($valeurEnCours) . "\r\n";
								}
							}
						}
						
						//ouverture du fichier en lecture avec suppression du contenut
						$handle = fopen($Fichier, "w+");
						//ecrture de la chaine temporaire dans le fichier
						if(false===fwrite($handle, $Contenu_fichier))
						{
							//fermeture du fichier
							fclose($handle);
							return false;
						}
						else
						{					
							//fermeture du fichier
							fclose($handle);
							return true;
						}
					}
					else
					{
						return False;
					}				
				}
				else
				{
					return False;
				}
			}
			Catch (Exception $ex)
			{
				return False;
			}
		}
   }
?>