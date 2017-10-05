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

$(document).ready( function() {
	var tempo = setTimeout("reloading()",1000);
})

$(document).ready(function(){
	$("#datepicker").datepicker({
		prevText:"",
		nextText:"",
		dateFormat:"yy-mm-dd"});
	$("#datepicker2").datepicker({
		prevText:"",
		nextText:"",
		dateFormat:"yy-mm-dd"});

	height1 = $("#recoverydiv").height();
	height2 = $("#activationdiv").height();
	if (height1 >= height2) {$("#activationdiv").css("height", height1);} else {$("#recoverydiv").css("height", height2);}

	height = $("#selection_customer").height() + 17;
	$("#selection_order").css("min-height", height);
	$("#selection_customer").css("min-height", height);

	$("#results_cust").css("display","none");
	$("#results_pdfmail").css("display","none");
})