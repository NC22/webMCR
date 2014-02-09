/* WEB-APP : WebMCR (С) 2013-2014 NC22 */

var mcr_pass_init = false

/* Base init on page load */

function mcr_init() {

    if (mcr_pass_init)
        return

    var tmpLinks = getByClass('comment-text', 'DIV')

    for (var i = 0; i <= tmpLinks.length - 1; ++i)
        tmpLinks[i].innerHTML = StringWithSmiles(tmpLinks[i].innerHTML)

    var tmpCaptcha = getByClass('antibot', 'INPUT')
    var tmpCaptchaF = function(e) {
        this.value = this.value.replace(/[A-Za-z-А-Яа-я]/, '')
    }

    for (var i = 0; i <= tmpCaptcha.length - 1; ++i) {

        tmpCaptcha[i].onkeyup = tmpCaptchaF
        tmpCaptcha[i].onclick = tmpCaptchaF
        tmpCaptcha[i].onchange = tmpCaptchaF
        tmpCaptcha[i].onkeypress = tmpCaptchaF
    }

    setTimeout(function() {
        LoadServers()
    }, 1000)

    pbm = ProgressBarManager('progressbar_meter pbar', true)
    pbm.Live(100, 100)

    mcr_pass_init = true
}

/* Prototypes */

Date.prototype.getLocaleFormat = function(format) {
    var f = {y: this.getYear() + 1900, m: this.getMonth() + 1, d: this.getDate(), H: this.getHours(), M: this.getMinutes(), S: this.getSeconds()}
    for (k in f)
        format = format.replace('%' + k, f[k] < 10 ? "0" + f[k] : f[k]);
    return format;
};

String.prototype.replaceAll = function(search, replace) {
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

    var token_post = '';
    if (GetById('token_data')) {

        token_data = GetById('token_data').value            
    }

    if (typeof token_data !== 'undefined') {
        token_post = '&token_data=' + token_data;
    }

    req.open('POST', base_url + script, true)  
    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    req.send(post_data + token_post)

    return false
}

function sendFormByIFrame(formname, onload){
	
    var iframe = document.createElement('iframe')
        iframe.name = 'ajax-frame-' + Math.random(1000000)
        iframe.style.display = 'none'

    GetBody().appendChild(iframe)

    var form = GetById(formname)	
	addHiddenInput('json_iframe', '1', form)  
        
    var token_elem = GetById('token_data');
    if (token_elem) {
        form.appendChild(token_elem)
    } else if (typeof token_data !== 'undefined') {        
        addHiddenInput('token_data', token_data, form)  
    }	 
    
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
	
    iFrameOnLoadEvent(iframe,event)	
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

function getXmlHttp() {

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

function addHiddenInput(name, value, to) {
    var element = document.createElement('input')

    element.type = 'hidden'
    element.name = name
    element.value = value

    to.appendChild(element)
}

function GetParent(elem, type){ 
    var parent = elem.parentNode

    if (parent && parent.tagName != type) parent = GetParentForm(parent)

    return parent;
}

function getByClass(className,tag) {
    var LinkList   = document.getElementsByTagName(tag)
    var foundList = []

    for (var i=0; i<=LinkList.length-1; ++i) 
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
    if (!item) return false

    if (typeof state !== 'boolean') {
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
        return refElem.insertBefore(elem, refElem.firstChild)
}

function getIframeDocument(iframeNode) {

    if (iframeNode.contentDocument)
        return iframeNode.contentDocument
    if (iframeNode.contentWindow)
        return iframeNode.contentWindow.document
    return iframeNode.document
}

function iFrameOnLoadEvent(iframeNode, event) {

    if (iframeNode.attachEvent)
        iframeNode.attachEvent('onload', event)
    else if (iframeNode.addEventListener)
        iframeNode.addEventListener('load', event, false)
    else
        iframeNode.onload = event
}

function clearFileInputField(Id) {

    var clear = GetById(Id)

    if (clear != null)
        clear.innerHTML = GetById(Id).innerHTML
}

function getClientW() {
    return document.compatMode == 'CSS1Compat' && document.documentElement.clientWidth;
}

function getClientH() {
    return document.compatMode == 'CSS1Compat' && document.documentElement.clientHeight;
}

/* Date Time */

function parseDate(input) {
    
    var format = 'yyyy-mm-dd hh:MM:ss'; // default format
    var parts = input.match(/(\d+)/g),
            i = 0, fmt = {};

    // extract date-part indexes from the format
    format.replace(/(yyyy|dd|mm|hh|MM|ss)/g, function(part) {
        fmt[part] = i++;
    });

    return new Date(parts[fmt['yyyy']], parts[fmt['mm']] - 1, parts[fmt['dd']], parts[fmt['hh']], parts[fmt['MM']], parts[fmt['ss']]);
}

function timeFrom(date) {

    var str = ''
    var now = new Date()
    var daysTo = (now - date) / 1000 / 60 / 60 / 24
    if (daysTo > 0)
        str = Math.floor(daysTo) + 'д.'

    var hours = now.getHours() - date.getHours()
    if (hours < 0)
        hours = hours * -1
    var minutes = now.getMinutes() - date.getMinutes()
    if (minutes < 0)
        minutes = minutes * -1

    return str = str + " " + hours + " ч. " + minutes + " мин. "
}

function debug(string) {
    GetById('debug').innerHTML += string
}

function fadeElement(element, onFade, mode, fade ) {
    
    if (typeof fade === 'undefined') {
        fade = 1;
        
        if (mode === 'in') {
            fade = 0;            
        }
        
        setElementOpacity(element, fade);        
    }
    
    var event = function () {
        
        if (mode === 'out' && fade > 0) {
            fade = fade - 0.1;
        } else if (mode !== 'out' && fade < 1){
            fade = fade + 0.1;
        }

        setElementOpacity(element, fade);
        
        if (mode === 'out') {
            if (fade > 0)
                fadeElement(element, onFade, mode, fade);
            else {
                setElementOpacity(element, 0);
                onFade();
            }
        } else {
            if (fade < 1)
                fadeElement(element, onFade, mode, fade);
            else {
                setElementOpacity(element, 1);
                onFade();
            }            
        }
    }
    
    setTimeout(function() { event() }, 80) 
}

function getOpacityProperty()
{
    var p;
    if (typeof document.body.style.opacity == 'string')
        p = 'opacity';
    else if (typeof document.body.style.MozOpacity == 'string')
        p = 'MozOpacity';
    else if (typeof document.body.style.KhtmlOpacity == 'string')
        p = 'KhtmlOpacity';
    else if (document.body.filters && navigator.appVersion.match(/MSIE ([\d.]+);/)[1] >= 5.5)
        p = 'filter';

    return (getOpacityProperty = new Function("return '" + p + "';"))();
}

function setElementOpacity(oElem, nOpacity)
{
    var p = getOpacityProperty();
    (setElementOpacity = p == "filter" ? new Function('oElem', 'nOpacity', 'nOpacity *= 100;	var oAlpha = oElem.filters["DXImageTransform.Microsoft.alpha"] || oElem.filters.alpha;	if (oAlpha) oAlpha.opacity = nOpacity; else oElem.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+nOpacity+")";') : p ? new Function('oElem', 'nOpacity', 'oElem.style.' + p + ' = nOpacity;') : new Function)(oElem, nOpacity);
}