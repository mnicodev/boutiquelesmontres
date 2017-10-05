/**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator's view
*
* @author    t
* @copyright t
* @license   f
*/

// JavaScript Document
function getCSVData(){
 var csv_value=$("#table1").table2CSV({delivery:"value"});
 $("#csv_text").val(csv_value);	
}

