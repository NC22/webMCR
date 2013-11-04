/* WEB-APP : WebMCR (С) 2013 NC22 */

var iframe; var mcr_pass_init = false

/* Base init on page load */

function mcr_init () {

if (mcr_pass_init) return

var tmpLinks = getByClass('comment-text','DIV')

for (i=0; i<=tmpLinks.length-1; ++i)

   tmpLinks[i].innerHTML = StringWithSmiles(tmpLinks[i].innerHTML)

var tmpCaptcha = getByClass('antibot', 'INPUT')
var tmpCaptchaF = function (e) { this.value = this.value.replace (/[A-Za-z-А-Яа-я]/, '') }

for (i=0; i<=tmpCaptcha.length-1; ++i) {

   tmpCaptcha[i].onkeyup = tmpCaptchaF
   tmpCaptcha[i].onclick = tmpCaptchaF
   tmpCaptcha[i].onchange = tmpCaptchaF
   tmpCaptcha[i].onkeypress = tmpCaptchaF
}
   
setTimeout(function() { LoadServers() }, 1000)

pbm = ProgressBarManager('progressbar_meter pbar',true)
pbm.Live(100,100)

mcr_pass_init = true
}

/* Prototypes */

Date.prototype.getLocaleFormat = function(format) {
	var f = {y : this.getYear() + 1900,m : this.getMonth() + 1,d : this.getDate(),H : this.getHours(),M : this.getMinutes(),S : this.getSeconds()}
	for(k in f)
		format = format.replace('%' + k, f[k] < 10 ? "0" + f[k] : f[k]);
	return format;
};

String.prototype.replaceAll = function(search, replace){
  return this.split(search).join(replace);
}

/* Math */

function rand(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }

/* Ajax */

function SendByXmlHttp(script, post_data, onload) {

	var req = getXmlHttp()

	req.onreadystatechange = function() {
				
	if (req.readyState != 4 || 
		(req.status != 200 && req.status != 0) || 
		(req.status == 0 && req.responseText.length == 0)) return false
		
		var response = getJSvalue(req.responseText)
		
		onload(response)
	}

	req.open('POST', base_url + script, true)  
	req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
	req.send(post_data)

	return false
}

function sendFormByIFrame(formname, onload){
	
    iframe = document.createElement('iframe')
    iframe.name = 'ajax-frame-' + Math.random(1000000)
    iframe.style.display = 'none'
	
	var iframe_trg = document.createElement('input')
	
    iframe_trg.type = 'hidden'
    iframe_trg.name = 'json_iframe'
    iframe_trg.value = '1'   
	
    GetBody().appendChild(iframe)

    var form = GetById(formname)	
		form.appendChild(iframe_trg)
	 
	if (form == null) {
	
		alert('Form ' + formname + 'not found')
		return false	
	}	
	
	form.target = iframe.name
	
	var event = function() {
	
		if (getIframeDocument(iframe).location.href == 'about:blank') return
		
		if (!iframe.contentWindow.json_response) {
		
		alert ('json_response is not set [' + formname + ']')
		return
		}
		
		var response = getJSvalue(iframe.contentWindow.json_response)
		
		GetBody().removeChild(iframe)	
		
		onload(response)
	}
	
	IframeOnLoadEvent(iframe,event)	
    form.submit()	
}

function getJSvalue(value) {

var result = false

//alert(value)

if (typeof value != "string") { 
	
	alert('[getJSvalue] Value is not string : '+value)
	return result
}

	try {
	
	result = window.JSON && window.JSON.parse ? JSON.parse(value) : eval('(' + value + ')')
	
	} catch (E) {

	alert('[getJSvalue] Incorect server response : ' + value)
	
	}
	
return result
}

function getXmlHttp(){
  var xmlhttp;  
  
  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') 
    xmlhttp = new XMLHttpRequest();
  
  else {
  
  	  try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	  } catch (e) {
	  
		try {
		  xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (E) {
		  xmlhttp = false;
		}
		
	  }
	  
  }
  return xmlhttp;
}

/* DOM Helpers */

function GetById(elem) {
	return document.getElementById(elem)
}

function GetBody() {
	return document.getElementsByTagName('body')[0]
}

function GetScrollTop() {
	return (document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop
}

function GetParent(elem, type){ 
var parent = elem.parentNode

if (parent && parent.tagName != type) parent = GetParentForm(parent)

return parent;
}

function getByClass(className,tag) {
	var LinkList   = document.getElementsByTagName(tag)
	var foundList = []

	for (i=0; i<=LinkList.length-1; ++i) 
		if (LinkList[i].className == className) foundList[foundList.length] = LinkList[i]
		
	return foundList
}

function addSubmitEvent(buttonId,formId) {
	var tmp = GetById(buttonId) 
	
	if (tmp != null) tmp.onclick = function(){
		GetById(formId).submit()

        return false		
	}
	
}

function BlockVisible(itemID,state) {

	var item = GetById(itemID)
	if (item == null) return false

	if (state == null) {
	
		if (item.style.display == 'block') item.style.display = 'none'
		else item.style.display = 'block'
		
		return true
	}
	
	var styleText = 'block'

	if (state == false)  styleText = 'none'
	
	item.style.display = styleText
	
	return true	
}

function nl2br (str, is_xhtml) {

  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; 

  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function toggleButton(id) {

var el = GetById(id)
if (el == null) return false

  el.disabled = !el.disabled
  return true
}

function getValById(id) {

var el = GetById(id)
if (el == null || el.value == null) return null
else return el.value

}

function insertInBegin(elem, refElem) {
	if(typeof refElem.firstChild == 'undefined')
    return refElem.appendChild(elem)
    else 
    return refElem.insertBefore(elem, refElem.firstChild);
}


function getIframeDocument(iframeNode) {

	  if (iframeNode.contentDocument) return iframeNode.contentDocument
	  if (iframeNode.contentWindow) return iframeNode.contentWindow.document
	  return iframeNode.document
}

function IframeOnLoadEvent(iframeNode,event) {

	if (iframeNode.attachEvent) iframeNode.attachEvent('onload', event)
	else if (iframe.addEventListener) iframeNode.addEventListener('load', event, false)
	else iframeNode.onload = event
}

function clearFileInputField(Id) { 

    var clear = GetById(Id)
	
	if (clear != null ) 
        clear.innerHTML = GetById(Id).innerHTML 
}

function getClientW() {
  return document.compatMode=='CSS1Compat' && document.documentElement.clientWidth;
}

function getClientH() {
  return document.compatMode=='CSS1Compat' && document.documentElement.clientHeight;
}

/* Date Time */

function parseDate(input) {
  format = 'yyyy-mm-dd hh:MM:ss'; // default format
  var parts = input.match(/(\d+)/g), 
      i = 0, fmt = {};
	  
  // extract date-part indexes from the format
  format.replace(/(yyyy|dd|mm|hh|MM|ss)/g, function(part) { fmt[part] = i++; });

  return new Date(parts[fmt['yyyy']], parts[fmt['mm']]-1, parts[fmt['dd']], parts[fmt['hh']], parts[fmt['MM']], parts[fmt['ss']]);
}

function timeFrom(date) {

  var str = ''
  var now = new Date()
  var daysTo = (now-date) / 1000 / 60 / 60 / 24
  if (daysTo > 0) str = Math.floor(daysTo) + 'д.'
  
    hours = now.getHours() - date.getHours() 
	if (hours < 0) hours = hours *-1
	minutes = now.getMinutes() - date.getMinutes()   
	if (minutes < 0) minutes = minutes *-1  
	
  return str = str + " " + hours  + " ч. " + minutes + " мин. "	 
}

function debug(string){
GetById('debug').innerHTML += string
}

/*
  Bootstrap - File Input by grevory 
  ======================

  This is meant to convert all file input tags into a set of elements that displays consistently in all browsers.
*/

if(typeof $ == 'function')

$(function() {

$('input[type=file]').each(function(i,elem){

  // Maybe some fields don't need to be standardized.
  if (typeof $(this).attr('data-bfi-disabled') != 'undefined') {
    return;
  }

  // Set the word to be displayed on the button
  var buttonWord = 'Выбрать';

  if (typeof $(this).attr('title') != 'undefined') {
    buttonWord = $(this).attr('title');
  }

  // Start by getting the HTML of the input element.
  // Thanks for the tip http://stackoverflow.com/a/1299069
  var input = $('<div>').append( $(elem).eq(0).clone() ).html();

  // Now we're going to replace that input field with a Bootstrap button.
  // The input will actually still be there, it will just be float above and transparent (done with the CSS).
  $(elem).replaceWith('<a class="file-input-wrapper btn">'+buttonWord+input+'</a>');
})
// After we have found all of the file inputs let's apply a listener for tracking the mouse movement.
// This is important because the in order to give the illusion that this is a button in FF we actually need to move the button from the file input under the cursor. Ugh.
.promise().done( function(){

  // As the cursor moves over our new Bootstrap button we need to adjust the position of the invisible file input Browse button to be under the cursor.
  // This gives us the pointer cursor that FF denies us
  $('.file-input-wrapper').mousemove(function(cursor) {

    var input, wrapper,
      wrapperX, wrapperY,
      inputWidth, inputHeight,
      cursorX, cursorY;

    // This wrapper element (the button surround this file input)
    wrapper = $(this);
    // The invisible file input element
    input = wrapper.find("input");
    // The left-most position of the wrapper
    wrapperX = wrapper.offset().left;
    // The top-most position of the wrapper
    wrapperY = wrapper.offset().top;
    // The with of the browsers input field
    inputWidth= input.width();
    // The height of the browsers input field
    inputHeight= input.height();
    //The position of the cursor in the wrapper
    cursorX = cursor.pageX;
    cursorY = cursor.pageY;

    //The positions we are to move the invisible file input
    // The 20 at the end is an arbitrary number of pixels that we can shift the input such that cursor is not pointing at the end of the Browse button but somewhere nearer the middle
    moveInputX = cursorX - wrapperX - inputWidth + 20;
    // Slides the invisible input Browse button to be positioned middle under the cursor
    moveInputY = cursorY- wrapperY - (inputHeight/2);

    // Apply the positioning styles to actually move the invisible file input
    input.css({
      left:moveInputX,
      top:moveInputY
    });
  });

  $('.file-input-wrapper input[type=file]').change(function(){

    // Remove any previous file names
    $(this).parent().next('.file-input-name').remove();
    if ($(this).prop('files').length > 1) {
      $(this).parent().after('<span class="file-input-name">'+$(this)[0].files.length+' files</span>');
    }
    else {
      $(this).parent().after('<span class="file-input-name">'+$(this).val().replace('C:\\fakepath\\','')+'</span>');
    }

  });  

});

// Add the styles before the first stylesheet
// This ensures they can be easily overridden with developer styles
var cssHtml = '<style>'+
  '.file-input-wrapper { overflow: hidden; position: relative; cursor: pointer; z-index: 1; }'+
  '.file-input-wrapper input[type=file], .file-input-wrapper input[type=file]:focus, .file-input-wrapper input[type=file]:hover { position: absolute; top: 0; left: 0; cursor: pointer; opacity: 0; filter: alpha(opacity=0); z-index: 99; outline: 0; }'+
  '.file-input-name { margin-left: 8px; }'+
  '</style>';
$('link[rel=stylesheet]').eq(0).before(cssHtml);

});