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

function reloading() {
	document.listing_name.reset();
	if ($("#sel_cust").length > 0 && $("#id_wishlis").length > 0 && $("#sel_cust").get(0).selectedIndex == 0)
		$("#id_wishlis").get(0).selectedIndex = 0;
}

function ajaxOrdersWishlists(id, id2, id3, period_type, date1, date2, moduledir, blockwishlist_name, id_lang, id_empl, token, tabid, ssl_base, date_format)
{
	$("body").css("cursor", "progress");
	$("#loader_orders_select").fadeIn();

$.ajax({
		type: 'POST',
		dataType: "json",
		url:	moduledir,
		async: false,
		data: {
			period_type: period_type,
			date1: date1,
			date2: date2,
			id_lang: id_lang,
			token: token,
			tabid: tabid,
			id_empl: id_empl
		},
		cache: false,
		success: function(jsonData)
	{

	if ($('#' + id2).css('display') != 'none')
		$('#' + id2).fadeOut('slow');
	if ($('#' + id3).css('display') != 'none')
		$('#' + id3).fadeOut('slow');

		$('#' + id).fadeIn('normal');
		$('#' + id).html(jsonData);

//table2
		if (jsonData != undefined)
		{
			if ($("#table2").tablesorter)
			$("#table2").tablesorter({	debug: false,
						 	sortList:[[1,1]],
							//dateFormat: "dd-mm-yy",
							dateFormat: 'uk',
							headers: { 0: { sorter: 'shortDate'} }, 		//0 : { sorter : false } to disable sorting
							widgets: ['zebra']
						})
			.tablesorterPager({ container: $("#pagerOne"), positionFixed: false, size: $(".pagesize option:selected").val() })
			;
	//-------- filter plugin  + count the number of rows in the filter table ------------
				var grid2 = $('#table2');
			// Initialise Filter Plugin
				var options2 = {
					filteringRows: function(filterStates) {
						grid2.addClass('filtering');
					},
					filteredRows: function(filterStates) {
						grid2.removeClass('filtering');
						setRowCountOnGrid2();
					},
					clearFiltersControls: [$('#cleanfilters')],
					selectOptionLabel: ['...'],
					filterCaseSensitive: false
				};
				function setRowCountOnGrid2() {
					var rowcount = grid2.find('tbody tr:not(:hidden)').length;
					$('#rowcount').text(' (' + rowcount );
				}

				if(grid2.tableFilter)
					grid2.tableFilter(options2); // No additional filters
				setRowCountOnGrid2();
		}
		$('#' + id).slideDown('normal');
		scrollToAw ('results', 700)
	}

	});
	$("body").css("cursor", "auto");
	$("#loader_orders_select").fadeOut();
}

//------------ form orders select to AJAX------------------
function form_orders_select(moduledir, id_lang, modulename, id_empl, token, tabid, modulename, ssl_base, date_format)
{
	var date1 = document.orders_select.date1.value;
	var date2 = document.orders_select.date2.value;
	var element = document.orders_select.period_type;
	for (var i=0; i < element.length; i++)
	{
		if (element[i].checked)
		{
		  var period_type = element[i].value;
		  break;
		}
	}
	ajaxOrdersWishlists("results", "results_cust", "results_pdfmail", period_type, date1, date2, moduledir, modulename, id_lang, id_empl, token, tabid, ssl_base, date_format);
}

function validation($to, $id_wishlist,lat1,lat2,lat2_idorder,msg_confirm,msg_warning1,msg_warning2,msg_warning3,latest_order_pdfmail) {
	$to1 = $to;
	$rep = comp_neworder_mail($to,lat1,lat2,lat2_idorder,msg_confirm,msg_warning1,msg_warning2,msg_warning3,latest_order_pdfmail);
	if ($rep == true)
		return true;
	else
		return false;
}
	function comp_neworder_mail($to,lat1,lat2,lat2_idorder,msg_confirm,msg_warning1,msg_warning2,msg_warning3,latest_order_pdfmail) {
		// Is there a latest order since the pdf email sending ?
		//latest order communicated in pdf mail
		$latest_order = 0;
		if (lat1 != false)
			$latest_order_pdfmail = latest_order_pdfmail;

		else
			$latest_order_pdfmail = -1 ; //in this case comparison test below will be successful

					 //latest order id payed
		if (lat2 != false)
			$latest_order = lat2_idorder;
		else
			$latest_order_pdfmail = -10;  //in this case comparison test below will not be successful

		//comparison test
		if ($latest_order > $latest_order_pdfmail) {
			var res = confirm(msg_confirm + " : " + $to);
			if (res == false)
				return false;
			if (res == true)
				return true;
		}
		else {
			var res = confirm(msg_warning1 + "\n" + "->" + msg_warning2 + "\n"+ "\n" + msg_warning3 + $to + "\n ");
			if (res == false)
				return false;
			if (res == true)
				return true;
		}
}


function print_wl_table(obj) {
	// print zone
	$("#table2 th").css("border", "1px solid #ddd");
	$("#table2 td").css("border", "1px solid #ddd").css("padding-left", "4px");
	$("tr.filters").remove();
	$("tfoot").remove();

	var zi = document.getElementById(obj).innerHTML;
	var f = window.open("", "ZoneImpr", "height=500, width=600,toolbar=0, menubar=0, scrollbars=1, resizable=1,status=0, location=0, left=10, top=10");
	// Page css
	f.document.body.style.color = "#000000";
	f.document.body.style.backgroundColor = "#FFFFFF";
	f.document.body.style.padding = "10px";

	// add data
	f.document.title =  $("#h1").text() +" - " + $("#h3").text();
	f.document.body.innerHTML += " " + zi + " ";
	// print and close window
	f.window.print();
}

/* to display thepage with the url of the list - BO page*/
function BoLinkList(id_list, path, msg_sel) {
    var baseLink = $("input[rel='linklist_"+id_list+"']").val();
    if (baseLink != undefined)
        window.open(baseLink);
    else
        alert(msg_sel);
    return false;
}

/* called when selecting a customer (store management) */
function go_bo_instore(id_loader, id_listtarget, id_divMaster, id_customSelect, trigger)
{
	$('#' + id_loader).fadeIn().css('display', 'inline');
	$('#' + id_customSelect).css('color', 'black');

	sel = document.getElementById(id_customSelect);

	idcustomer = sel.options[sel.selectedIndex].value;

 	$.ajax({
		type: "POST",
		dataType: "json",
		async : false,
		url: adminSelectList,
		data: {
			idCustomer : idcustomer,
			id_secondlist_select : id_listtarget,
			token_module : token_module,
			trigger : trigger,
			id_employee : id_employee
		},
		cache: false,
		success: function(jsonData)
		{
			if (jsonData != undefined)
			{
				// $('#loader_bo_instore').fadeOut();
				$('#' + id_divMaster).html(jsonData);
				// $('#list_store_feed').html(jsonData);
			}
		}
	});
	$('#' + id_loader).fadeOut();
}

function visibility(tab_block) {
//alert('1 '+readCookie('hidedisplaysetting'));
	for (i in tab_block) {
		if ($('#' + tab_block[i]).css('display') == 'none') {
			$('#' + tab_block[i]).fadeIn('fast');
			createCookie('hidedisplaysetting','display',365);
		} else {
			$('#' + tab_block[i]).fadeOut('fast');
			createCookie('hidedisplaysetting','hide',365);
		}
	}
	if ($('#' + 'morep').css('display') == 'none') {
		$('#' + 'lessp').fadeOut('slow').queue(function() {
				$('#' + 'morep').fadeIn('fast');
				$(this).dequeue();
			});
		createCookie('hidedisplaysetting','hide',365);
	}
	else {
		$('#' + 'morep').fadeOut('slow').queue(function() {
			$('#' + 'lessp').fadeIn('fast');
			$(this).dequeue();
		});
		createCookie('hidedisplaysetting','display',365);
	}
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}


$(document).ready(function() {
	if (readCookie('hidedisplaysetting') == 'display') {
		$('#' + 'settings').fadeIn('fast');
		$('#' + 'lessp').fadeIn('fast');
		$('#' + 'morep').fadeOut('fast');
	}
	else {
		var tab_block = new Array('activation');
		for (i in tab_block) {
			$('#' + tab_block[i]).fadeOut('fast');
			createCookie('hidedisplaysetting','hide',365)
		}
		$('#' + 'morep').fadeIn('fast');
		$('#' + 'lessp').fadeOut('fast');
	}
})