/**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator's view
*
* @author    Denis Deleval / alize-web.fr <contact@alizeweb.fr>
* @copyright Alizé Web 2011-2014
* @license   Non-exclusive license
 * The module is protected by copyright and other intellectual property laws
 * This license does not grant any reseller privileges
 * The module is identified as “Not-For-Resale” and may not be sold or otherwise transferred
 * You may not rent, redistribute, lease, lend, sell the module (even if you modify files), decompile, reverse engineer, or disassemble the module
 * Non exclusive license for one web site
 * Installation and use:  You may install, use, access, display and run one copy of the module for single website only
 * Multiple sites : buy as many licenses as the number of websites
 * You may modify files (.TPL, PHP, JS, CSS) provided with the module for the purpose of enhancing or customizing the product
 * but you cannot sell modified files
*/

// JavaScript Document
function verif_form(id){
var test=1;
i=1;
//remise à fond blanc des champs du formulaire
//for (k=1;k<=5;k++) {
//	document.getElementById(k).style.cssText="background-color:#FFFFFF"; }

//vérification  champ vide 
//	if (document.getElementById(id).value=="") {
//			alert("Afin de mieux vous renseigner, les champs avec une astérisque * (par exemple nom, prénom, adresse mail, ...) sont obligatoires. Merci de bien vouloir vérifier votre saisie");
//			document.getElementById($i).select();
//			document.getElementById($i).style.cssText="background-color:#ECECEC";
//			return false;
//	}
//format email
var test_mail=/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*@[a-zA-Z0-9_][.a-zA-Z0-9_-]*\.[a-zA-Z]{2,6}$/gi;

if ( document.getElementById(id).value.search(test_mail) == -1 ) {
		alert("Adresse email invalide !");
		document.getElementById(id).select();
		document.getElementById(id).style.cssText="background-color:#ECECEC";
		return false ;} ;
}

