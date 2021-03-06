/**
* BLOCKWISHLISTPRO Front Office Feature - display products of a list, creator's view
*
* @author    Denis Deleval / alize-web.fr <contact@alizeweb.fr>
* @copyright Alizé Web
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

/**
 * Update WishList Cart by adding, deleting, updating objects
 * @return void
 */
function WishlistCartpro(id, action, id_product, id_product_attribute, quantity, token, blockwishlist_name,msg_log,msg_create_list, msg_remov_impossible, msg_success)
{
	if (quantity == 'undefined' || $("#quantity_wanted").length == 0)
		quantity = 1;
	var spy_data = 0;
	var id_list = $('select#wishlists_pdt option:selected').val();
	$.ajax({
		type: 'POST',
		url:	baseDir + 'modules/' + blockwishlist_name + '/cart.php',
		async: true,
		cache: false,
		data: 'action=' + action + '&id_product=' + id_product + '&quantity=' + quantity + '&token=' + static_token + '&id_product_attribute=' + id_product_attribute + '&id_wishlist=' + id_list,
		success: function(data)
		{
			if(data.indexOf('not_logged') !=-1)	{alert(msg_log); spy_data = 1;}
			if(data.indexOf('create_list') !=-1) {alert(msg_create_list); spy_data = 1;}
			if(data.indexOf('impossible_to_delete') !=-1) {alert(msg_remov_impossible); spy_data = 1;}
			if(data.indexOf('adding_ok') !=-1) alert(msg_success);
			if (data.indexOf('minimal_qty') != -1)
			{
				i1 = data.indexOf('|');
				i2 = data.lastIndexOf('|');
				splittemp = data.substr(i1+1,i2).split('|');
				alert(splittemp[1]);
				spy_data = 1;
			}

			if($('#' + id).length != 0)
			{
				$('#' + id).slideUp('normal');
				if( spy_data == 0)
					document.getElementById(id).innerHTML = data;
				$('#' + id).slideDown('normal');
			}
		}
	});
}

/**
 * Change customer default wishlist
 *
 * @return void
 */
function WishlistChangeDefaultpro(id, id_wishlist, blockwishlist_name)
{
	$.ajax({
		type: 'POST',
		url:	baseDir + 'modules/' + blockwishlist_name + '/cart.php',
		async: true,
		data: 'id_wishlist=' + id_wishlist + '&token=' + static_token,
		cache: false,
		success: function(data)
		{
			if ($('#' + id).length > 0)
			{
				$('#' + id).slideUp('normal');
				document.getElementById(id).innerHTML = data;
			}
			if($('#wishlists').length > 0)
				$('#wishlists option[value='+id_wishlist+']').attr('selected', 'selected');
			$('#' + id).slideDown('normal');
		}
	});
}

/**
 * Buy Product
 *
 * @return void
 */
function WishlistBuyProductpro(token, id_product, id_product_attribute, id_quantity, button, ajax, offer_qty, blockcart_is_active,msg_in_progress, ajax_CartController_path, enable_on_current_device)
{
// alert('(ajax)'+ajax);
// alert('(blockcart_is_active)'+blockcart_is_active);

	var offer_qty = Number(offer_qty);
	// visual effect
	$('#block_'+id_quantity).addClass('transparencydd20');
	// deactive the button when offering a gift
	$('#a_'+id_quantity).fadeOut('slow');
	$('#cleancartOk').fadeOut('slow');

	if(ajax && blockcart_is_active == 1 && enable_on_current_device && typeof ajaxCart != 'undefined')
	{
		ajaxCart.add(id_product, id_product_attribute, false, button, offer_qty, [token, offer_qty]); //offer_qty is useful. keep the first one because of ajaxCart parameters
		// visual effect
		$('#block_'+id_quantity).addClass('transparencydd20');
		// deactive the button when offering a gift
		$('#a_'+id_quantity).fadeOut('slow');
	}
	else
	{
		alert(msg_in_progress);
		$('html').css("cursor", "progress");
//		WishlistAddProductCart(token, id_product, id_product_attribute, offer_qty);
//		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].method='POST';
//		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].action=baseDir + 'cart.php';
//		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].elements['qty'].value = offer_qty;
//		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].elements['token'].value = static_token;
//		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].submit();
		$.ajax({ //ok to add in wishlist_product_cart, but doesn't refresh the cart (header) --> need to  location.assign(location.href) to do that // async set to false to wait for the end of cart.php before running WishlistAddProductCart
			type: 'POST',
			async: false,
			url: ajax_CartController_path,
			data : 'qty='+offer_qty+'&token='+static_token+'&add='+'1'+'&id_product='+id_product+'&id_product_attribute='+id_product_attribute+'&ajax='+'false'+'&submit='+'true',
			cache: false,
			success: function(data)
			{
				WishlistAddProductCart(token, id_product, id_product_attribute, offer_qty, ajax, blockcart_is_active);
			}
		});
	location.assign(location.href);
	}
	// reactive the button when adding has finished / new since PS 1.4.2.5
	$('#' + 'a_' + id_product+'_'+id_product_attribute).removeAttr('disabled').removeClass('exclusive_disabled');
	$('#block_'+id_quantity).removeClass('transparencydd20');
	// reactive the button when adding has finished / new since PS 1.4.2.5
	$('#a_'+id_quantity).fadeIn('fast');

	return (true);
}

function WishlistAddProductCart(token, id_product, id_product_attribute, id_quantity, ajaxT, blockcart_is_activeT)
{
	if ($('#' + id_quantity).val() < 0) /*if <= then limit wpc.quantity to max wanted quantity -dd*/
		return false;
	if ( $("#ajaxVal").length > 0)
		ajax = $("#ajaxVal").val();
	if ( $("#blockcartIsEnableVal").length > 0)
		blockcart_is_active = $("#blockcartIsEnableVal").val();
	if ( $("#enable_on_current_device").length > 0)
		enable_on_current_device = $("#enable_on_current_device").val();

	if (ajaxT == 'undefined' || $("#ajaxT").length == 0)
		ajaxT = ajax;
	if (blockcart_is_activeT == 'undefined' || $("#blockcart_is_activeT").length == 0)
		blockcart_is_activeT = ajax;

	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/blockwishlistpro/buywishlistproduct.php',
		data: 'token=' + token + '&static_token=' + static_token + '&id_product=' + id_product  + '&id_product_attribute=' + id_product_attribute + '&quantity=' + id_quantity,
		async: false,
		cache: false,
		success: function(data)
		{
			if (data)
				alert(data);
			else
			{	/*dd new test*/

				if (ajaxT && blockcart_is_activeT == 1 && enable_on_current_device)
				{
					if ( $('#' + 'nnn_' + id_product+'_'+id_product_attribute).val() - $('#' + 'enter_'+ id_product+'_'+id_product_attribute).val()<= 0)
							$('#' + 'nnn_' + id_product+'_'+id_product_attribute).val(0);
					else
						$('#' + 'nnn_' + id_product+'_'+id_product_attribute).val($('#' + 'nnn_' + id_product+'_'+id_product_attribute).val() - $('#' + 'enter_'+ id_product+'_'+id_product_attribute).val());
				}
			}
		}
	});
	return true;
}

/**
 * Show wishlist managment page
 *
 * @return void
 */
function WishlistManagePro(id, id_wishlist)
{
	$("body").css("cursor", "progress");
	$.ajax({
		type: 'POST',
		async: false,
		url: baseDir + 'modules/blockwishlistpro/managewishlistdetailDD.php',
		data: 'id_wishlist=' + id_wishlist + '&refresh=' + false,
		cache: false,
		success: function(data)
		{
	$("body").css("cursor", "auto");
			$('#' + id).hide();
			document.getElementById(id).innerHTML = data;
			$('#' + id).fadeIn('slow');
		}
	});
	/* list or grid option - creator's page */
	bindGrid_aw();
}

/**DD
 * Edit PDF - wishlist management page
 * @return
 */
/*dd deprecated */
/*function WishlistManagePDF(id, id_wishlist)
{
	$.ajax({
		type: 'GET',
		async: true,
		url: baseDir + 'modules/blockwishlistpro/summary_table_post_creator.php',
		data: 'id_wishlist=' + id_wishlist + '&refresh=' + false,
		cache: false,
		success: function(data)
		{
			$('#' + id).hide();
			$('#' + id).fadeOut('slow');
			$('#' + id).fadeIn('slow');
		}
	});
}
*/
/**
 * Show wishlist product managment page
 *
 * @return void
 */
/*dd new parameter : quantity_left */
function WishlistProductManage(id, action, id_wishlist, id_product, id_product_attribute, quantity, zone_qty_left, zone_qty, priority, alert_wanted_qty, removing_impossible)
{
$("body").css("cursor", "progress");
$.ajax({
		type: 'POST',
		async: true,
		url: baseDir + 'modules/blockwishlistpro/managewishlistdetailDD.php',
		data: 'action=' + action + '&id_wishlist=' + id_wishlist + '&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute + '&quantity=' + quantity + '&priority=' + priority + '&refresh=' + true,
		cache: false,
		success: function(data)
		{
			$("body").css("cursor", "auto");
			$('#quantity_'+id_product+'_'+id_product_attribute).removeClass('bkg_f5e1ba');
			if (action == 'delete' && data == "successful removing") {
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeOut('fast');
			}
			else if (action == 'delete' && data != "impossible") {
				alert(removing_impossible);
			}
			else if (action == 'update')
			{
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeOut('fast');
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeIn('fast');
				//dd if data contains 'alert|' then alert : wanted qty can not be inferior to bought qty
				if (data.indexOf('minimal_qty') != -1) {
					$tab = data.split("|");
					alert($tab[1]);
					$('#quantity_'+id_product+'_'+id_product_attribute).addClass('bkg_f5e1ba').focus();
				}
				else if (data.indexOf('|',5) != -1)
				{
					alert(alert_wanted_qty);
					$tab=new Array();
					$tab=data.split("|");
					document.getElementById(zone_qty).value = $tab[1];
					document.getElementById(zone_qty_left).value = $tab[2];
				}
				else
				{
					$trt=Number(data);
					document.getElementById(zone_qty_left).value = $trt;
				}
			}
		}
	});
}

/**
 * Delete wishlist
 *
 * @return boolean succeed
 */
 function WishlistDelete(id, id_wishlist, msg,msg_bought)
{
	var res = confirm(msg);
	if (res == false)
		return (false);
$("body").css("cursor", "progress");
	$.ajax({
		type: 'POST',
		async: true,
		url: baseDir + 'index.php?fc=module&module=blockwishlistpro&controller=mywishlist#',
//		url: baseDir + 'modules/blockwishlistpro/mywishlist.php',
		cache: false,
		data: 'deleted=1&id_wishlist=' + id_wishlist,
		success: function(data)
		{ //alert(data.indexOf('already_bought'));
			if(data.indexOf('already_bought') != -1) alert(msg_bought);
			else {
				$('#' + id).fadeOut('slow');
			}
			$("body").css("cursor", "auto");
		}
	});
}

/**
 * Hide/Show bought product
 *
 * @return void
 */
function WishlistVisibilitypro(bought_class, id_button)
{
	if ($('#hide' + id_button).css('display') == 'none')
	{
		$('.' + bought_class).slideDown('fast');
		$('#show' + id_button).hide();
		$('#hide' + id_button).fadeIn('fast');

		if ((id_button == 'BoughtProductsInfos') )	/*dd to hide products and emails form when displaying infos */
		{
			$('.' + 'wlp_bought').slideUp('fast');
/*!*/			$('#hide' + 'BoughtProducts').hide();
/*!*/			$('#show' + 'BoughtProducts').fadeIn('fast');

			$('.' + 'wl_send').slideUp('fast');
			$('#hide' + 'SendWishlist').hide();
			$('#show' + 'SendWishlist').fadeIn('fast'); /*end*/

		}
		if ((id_button == 'SendWishlist') )			/*dd to hide products and infos when displaying emails form */
		{
			$('.' + 'wlp_bought').slideUp('fast');
			$('#hide' + 'BoughtProducts').hide();
			$('#show' + 'BoughtProducts').fadeIn('fast');
			$('.' + 'wlp_bought_infos').slideUp('fast');
			$('#hide' + 'BoughtProductsInfos').hide();
			$('#show' + 'BoughtProductsInfos').fadeIn('fast');
		}
		if ((id_button == 'BoughtProducts') )		/*dd to hide emails form and infos when displaying emails form */
		{
			$('.' + 'wl_send').slideUp('fast');
			$('#hide' + 'SendWishlist').hide();
			$('#show' + 'SendWishlist').fadeIn('fast');
			$('.' + 'wlp_bought_infos').slideUp('fast');
			$('#hide' + 'BoughtProductsInfos').hide();
			$('#show' + 'BoughtProductsInfos').fadeIn('fast');
		}
	}
	else
	{
		$('.' + bought_class).slideUp('fast');
		$('#hide' + id_button).hide();
		$('#show' + id_button).fadeIn('fast');
		/*if ((id_button) == 'BoughtProductsInfos')
		{$('.' + 'wlp_bought').slideUp('fast');}*/
	}
}
/**
 * Hide/Show new list creation block (manage my wishlists)
 *
 * @return void
 */
function newWlVisibilitypro(bought_class, id_button)
{
	if ($("#hide" + id_button).css("display") == "none")
	{
		$('.' + bought_class).slideDown('fast');
		$('#show' + id_button).hide();
		$('#hide' + id_button).fadeIn('fast');
		document.getElementById('empty_dd').innerHTML = " " ;

	}
	else
	{
		$('.' + bought_class).slideUp('fast');
		$('#hide' + id_button).hide();
		$('#show' + id_button).fadeIn('fast');
		/*if ((id_button) == 'BoughtProductsInfos')
		{$('.' + 'wlp_bought').slideUp('fast');}*/
		document.getElementById('empty_dd').innerHTML = " ";
	}
}

/**
 * Hide/Show calendar fields (then datepicker)
 *
 * @return void
 */
function PeriodVisibility(id_button)
{
//alert(id_button);
	if (id_button == 'period_select' || id_button == 'calendar') 			//dd to hide products and infos when displaying emails form
		{
		$('#date12').slideDown('normal');
		};

	if (id_button != 'period_select' && id_button != 'calendar')
		{
		$('#date12').slideUp('normal');
		};
	if (id_button == 'calendar')
		{
/*dd*/	document.getElementById('period_select').checked="checked";
		}
}

/**
 * Zoom table report (orders_wishlists.php))
 */
function Zoom(action,id)
{
	if (action == 'plus') 			//dd to enlarge table
		{
 		width1 = Math.floor($('#' + id).width()*1.05);
//		if (width1 >= 1) {width1 = 1;};
//		alert(width1);
		$('#' + id).css("width", width1);
		};
	if (action == 'minus') 			//dd to minmize table
		{
 		width1 = $('#' + id).width();
		width1 = Math.floor(width1*0.95);
//		alert(width1);
		$('#' + id).css("width", width1);
//document.getElementById(id).style.cssText="color:red";
//document.getElementById(id).style.cssText="width:50%";
		};

}

/**
 * Send wishlist by email
 *
 * @return void
 */
function WishlistSendpro(id, id_wishlist, id_email, submit_button, message_personal, blockwishlist_name)
{
mail_requ=document.getElementById('mail_requ').value;
mail_sent=document.getElementById('mail_sent').value;
mail_not_sent=document.getElementById('mail_not_sent').value;
mail_invalid=document.getElementById('mail_invalid').value;
//cursor - loading ...
document.getElementById(submit_button).disabled=true;
document.getElementById(submit_button).style.cssText="display:none";
$("body").css("cursor", "progress");

var email = new Array();
var emailval = new Array();

/*cancel spaces in emails */
	for (i=1;i<=10;i++) {
email[i] = id_email + i;
emailval[i] = $('#' + id_email + i).val();
emailval[i] = emailval[i].replace(/ /g,"");
	}
/*check whether first email is filled in or empty*/
if ( emailval[1] == '')
		{
			$("body").css("cursor", "auto");
document.getElementById(submit_button).disabled=false;
document.getElementById(submit_button).style.cssText="display:block";
			alert(mail_requ);
			document.getElementById(email[1]).style.cssText="background-color:#d9ddf4;";
			document.getElementById(email[1]).select();
			return (false);
		}
/*split in case of several emails*/
//	emails_tab = emails.split(";") ;
	var test_mail=/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*@[a-zA-Z0-9_][.a-zA-Z0-9_-]*\.[a-zA-Z]{2,6}$/gi;
	var test_conf = 1;
	var invalidmails = '';
	var checkvalid = new Array();
	var k=0;
	for (i in email) {
		if ( emailval[i] != '' && emailval[i].search(test_mail) == -1 ) {
			$("body").css("cursor", "auto");
			document.getElementById(submit_button).disabled=false;
			document.getElementById(submit_button).style.cssText="display:block";
			invalidmails = invalidmails + emailval[i] + "\n";
			checkvalid[i] = 0;
			test_conf = test_conf * (0);
		}
		else { checkvalid[i] = 1;}
		;
	};

/*---invalid mails---*/
	if (test_conf == 0) {
		alert(mail_invalid +":\n"+ invalidmails);
		$("body").css("cursor", "auto");
		document.getElementById(submit_button).disabled=false;
		document.getElementById(submit_button).style.cssText="display:block";

		for (i in email) {
			if (checkvalid[i] == 0 && emailval[i] != "") {
				if (k == 0) k=i;
				document.getElementById(email[i]).style.cssText="background-color:#d9ddf4;";
			};
		};
		document.getElementById(email[k]).select();


		return (false);

	};
	$.post(baseDir + 'modules/'+ blockwishlist_name +'/sendwishlist.php',
	{ token: static_token,
	  id_wishlist: id_wishlist,
	  message_personal : message_personal,
	  email1: $('#' + id_email + '1').val(),
	  email2: $('#' + id_email + '2').val(),
	  email3: $('#' + id_email + '3').val(),
	  email4: $('#' + id_email + '4').val(),
	  email5: $('#' + id_email + '5').val(),
	  email6: $('#' + id_email + '6').val(),
	  email7: $('#' + id_email + '7').val(),
	  email8: $('#' + id_email + '8').val(),
	  email9: $('#' + id_email + '9').val(),
	  email10: $('#' + id_email + '10').val() },
	function(data)
	{
//alert('data=' + data + 'fin data');
		if (data.indexOf('nok') != -1) {
			alert(mail_not_sent);
			$("body").css("cursor", "auto");
			document.getElementById(submit_button).disabled=false;
			document.getElementById(submit_button).style.cssText="display:block";
		}
		else
		{
			$("body").css("cursor", "auto");
			document.getElementById(submit_button).disabled=false;
			document.getElementById(submit_button).style.cssText="display:block";
			alert(mail_sent);
			WishlistVisibilitypro(id, 'hideSendWishlist');
			$('#hide' + 'SendWishlist').hide();
			$('#show' + 'SendWishlist').fadeIn('fast');
		}
	});
}

/**
 * Find out before Sending the email of wishlist
 *
 * @return void
 */
function findOutEmail(id_wishlist, id_email, submit_button, file, file_temp, message_personal, blockwishlist_name)
{
//var blockwishlist_name = 'blockwishlist';
$("body").css("cursor", "progress");

$.post(baseDir + 'modules/'+ blockwishlist_name +'/find_out_email1.php',
	{ token: static_token,
	  id_wishlist: id_wishlist,
	  message_personal : message_personal,
	  file : file,
	  file_temp : file_temp
	  },
	function(data)
	{
		$("body").css("cursor", "auto");
		document.getElementById('create_templ').style.cssText="color:gray; display:block";
		document.getElementById('view_templ_0').style.cssText="display:none;";
		document.getElementById('view_templ').style.cssText="display:block; background-color:yellow;";
		document.getElementById('view_templ').focus();

			return (true);
	});
$("body").css("cursor", "auto");
}


function verif_form(id)
{
//format email
var test_mail=/^[a-zA-Z0-9_][a-zA-Z0-9_.-]*@[a-zA-Z0-9_][.a-zA-Z0-9_-]*\.[a-zA-Z]{2,6}$/gi;

if ( document.getElementById(id).value.search(test_mail) == -1 ) {
		alert("Invalid mail address ! \nAdresse email invalide !");
		document.getElementById(id).select();
		document.getElementById(id).style.cssText="background-color:#ECECEC";
		return (false) ;} ;
}


function resultsVisibility(id,id2,id3) {
	if ($('#' + id2).css('display') != 'none') 	{
		$('#' + id2).css("display","none");
//		$('#' + id2).fadeOut('slow');
	}
	if ($('#' + id3).css('display') != 'none') 	{
		$('#' + id3).css("display","none");
//		$('#' + id2).fadeOut('slow');
	}
	if (id == 'results_pdfmail') {
		$('#' + id2).slideUp('slow');
		$('#' + id3).slideUp('slow');
		$('#' + id).fadeIn('slow');
		return (true);
	}
	if ($('#' + id).css('display') == 'none') 	{
	$('#' + id).css("display","block");
	$('#' + id2).css("display","none");
	$('#' + id3).css("display","none");
	}
	return true;
}

function WishlistPublish(checkedstate,moduledir,blockwishlist_name,id, id_wishlist, msg_publish, msg_unpublish,click_to_publish,click_to_unpublish) {
if (checkedstate == 0)
	var res = confirm(msg_publish);
else
	var res = confirm(msg_unpublish);

if (res == false)
	return (false);
$.ajax({
		type: 'POST',
		url:	moduledir + blockwishlist_name +'/publish.php',
		async: true,
		cache: false,
		data: 'id_wishlist=' + id_wishlist,
		success: function(data)
	{
		$('#' + id).fadeOut('slow');
			$('#' + id).fadeIn('slow');
		if (data == '1' || data == 1) 	{
//			document.getElementById(id).style.cssText="display:inline";
			document.getElementById(id).checked = true;
			document.getElementById(id).value = 1;
			document.getElementById(id).title = click_to_unpublish;
		}
		else 	{
//			document.getElementById(id).style.cssText="display:inline";
			document.getElementById(id).checked = false;
			document.getElementById(id).value = 0;
			document.getElementById(id).title = click_to_publish;
		}
	}
});
}

function checkJQueryMinVersion(need) {
    var v1 = need.split('.');
    var v1_num = 0;
    var v2 = jQuery.fn.jquery.split('.');
    var v2_num = 0;

    if(v1[0] != undefined) {
        v1_num += 100*100*parseInt(v1[0]);
    }
    if(v1[1] != undefined) {
        v1_num += 100*parseInt(v1[1]);
    }
    if(v1[2] != undefined) {
        v1_num += parseInt(v1[2]);
    }

    if(v2[0] != undefined) {
        v2_num += 100*100*parseInt(v2[0]);
    }
    if(v2[1] != undefined) {
        v2_num += 100*parseInt(v2[1]);
    }
    if(v2[2] != undefined) {
        v2_num += parseInt(v2[2]);
    }
    return (v1_num <= v2_num);
}

function bindGrid_aw()
{
	/*init type grid or list*/
	/*bo parameters*/
	var type_display_default = $('#type_display_default').val();
	var type_display_init = $('#type_display_init').val();

	if (typeof $.totalStorage !== 'undefined') {
		var view_category = $.totalStorage('display');
		var view = $.totalStorage('display_aw');
		if (type_display_init == 'catg' && view_category && !view)
			view = view_category; /*by default category choice*/
		else if ((!view_category && !view) || (type_display_init != 'catg' && !view))
			view = type_display_default; /*by default if not category choice*/
	}
	else
		view = type_display_default; /*by default if not category choice*/

	$('.display').find('li#'+view+'_aw').addClass('selected');
	displayAw(view);

	$(document).on('click', '#grid_aw', function(e){
		e.preventDefault();
		displayAw('grid');
	});

	$(document).on('click', '#list_aw', function(e){
		e.preventDefault();
		displayAw('list');
	});
}

function displayAw(type_display)
{
	var ul_target = 'ul.product_list_aw';
	if (type_display == 'list')
	{
		$(ul_target).removeClass('grid').addClass('list row');
		$(ul_target+' > li').removeClass('col-xs-12 col-sm-6 col-md-4').addClass('col-xs-12');
		$(ul_target).find('li.address_name').removeClass('border_right1');
		$(ul_target).find('.wishlist_product_detail_dd').removeClass('border_right1'); /*donator page*/
		$(ul_target+' li.address_name div.align_dd').addClass('align_right');
		$(ul_target+' li.address_name div.align_dd > a').addClass('fl_aw');
		$('div[rel="decli"]').addClass('fr_aw');

		$('.display').find('li#list_aw').addClass('selected');
		$('.display').find('li#grid_aw').removeAttr('class');
		if (typeof $.totalStorage !== 'undefined')
			$.totalStorage('display_aw', 'list');
	}
	else
	{
		$(ul_target).removeClass('list').addClass('grid row');
		$(ul_target+' > li').removeClass('col-xs-12').addClass('col-xs-12 col-sm-6 col-md-4');
		$(ul_target).find('li.address_name').addClass('border_right1');
		$(ul_target).find('.wishlist_product_detail_dd').addClass('border_right1'); /*donator page*/
		$(ul_target+' li.address_name div.align_dd').removeClass('align_right');
		$(ul_target+' li.address_name div.align_dd > a').removeClass('fl_aw');
		$('div[rel="decli"]').addClass('fr_aw');

		$('.display').find('li#grid_aw').addClass('selected');
		$('.display').find('li#list_aw').removeAttr('class');
		if (typeof $.totalStorage !== 'undefined')
			$.totalStorage('display_aw', 'grid');
	}
}

function scrollToAw(id, duration)
{
	$('body').scrollTo('#'+id, { offset:-200, duration: duration, easing:'linear'});//add or deduct from the final position
}

$(document).ready(function()   {
	//event layer x y deprecated  ->to prevent the bug in webkit Chrome (context : count of characters of message) / only if jq <1.7

if(!window.jQuery || !checkJQueryMinVersion('1.7')) {
		var nav = navigator.userAgent;
		var ischrome = nav.toLowerCase().indexOf("chrome") > -1 ? true : false;
		if(ischrome) {
			$.event.props.splice($.event.props.indexOf("layerY"),1);
			$.event.props.splice($.event.props.indexOf("layerX"),1);
		}

}
/*{alert(parseFloat(jQuery.fn.jquery));
		var nav = navigator.userAgent;
		var ischrome = nav.toLowerCase().indexOf("chrome") > -1 ? true : false;
		if(ischrome) {
			$.event.props.splice($.event.props.indexOf("layerY"),1);
			$.event.props.splice($.event.props.indexOf("layerX"),1);
		}*/

/* only on donator's page, grid or list */
	if ($('div#view_wishlist').length > 0)
		bindGrid_aw();
});
