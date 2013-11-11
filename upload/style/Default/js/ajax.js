/* WEB-APP : WebMCR (С) 2013 NC22 */

var user_profile_id = -1;
var err404 = 'incorrect address : error : '
var custom_profile;

function DeleteComment(id) {

	var event = function(response) {

        if ( response['code'] == 1 ) return false

		$('#comment-byid-' + id).fadeOut(200, function (){
		
		$(this).hide()
		var commentTrash = GetById('comment-byid-' + id)
				
		if ( commentTrash == null ) document.location.reload(true)
		else commentTrash.parentNode.removeChild(commentTrash)
		});
	}
	
	SendByXmlHttp('action.php', 'method=del_com&item_id=' + encodeURIComponent(id), event)
	return false
}

function Like(dislike, id, type) {

	var event = function(response) {
	
		var dlike = GetById('dislike' + id + 'type' + type)
		var  like = GetById('like' + id + 'type' + type)
		
		dlike.onclick = function(){ return false }
		like.onclick = function() { return false }
				
		if ( response['code'] == 0 ) return false
        if ( response['code'] == 3 ) { 
		
		BlockVisible('loginform-error',true)
		return false
		}
		
		if ( response['code'] == 2 ) 
		
			if (dislike) {
			
				dlike.innerHTML = parseInt(dlike.innerHTML) + 1
				like.innerHTML  = parseInt(like.innerHTML) - 1				
			} else {		
			
				dlike.innerHTML = parseInt(dlike.innerHTML) - 1
				like.innerHTML  = parseInt(like.innerHTML) + 1					
			}
		
		else
			if (dislike) dlike.innerHTML = parseInt(dlike.innerHTML) + 1			
			else like.innerHTML  = parseInt(like.innerHTML) + 1							
	}
	
	SendByXmlHttp('action.php', 'method=like&dislike=' + encodeURIComponent(dislike) + '&type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id), event)
	return false
}

function PostComment(script) {

    var addition_post = ''    
    var text          = getValById('comment-add-text')
	var item_id       = getValById('comment-item-id')
	var item_type     = getValById('comment-item-type')
	
	if (text == null || item_id == null || item_type == null || item_id.value <= 0 ) return false
    if (text.length < 1) return false
	
	var antibot = GetById('antibot')
	if (antibot != null ) {
	
		if (antibot.value.length != 4) return false		
		addition_post = '&antibot=' + encodeURIComponent(antibot.value)	
	}
	
	toggleButton('comment-button')	
	var event = function(response) {
	
	toggleButton('comment-button')
	
        if ( response['code'] == 0 ) {
		
		var new_comment = document.createElement("div")
			new_comment.innerHTML += response['comment_html']	
		
		if ( response['comment_revers'] ) GetById('comments-main').appendChild(new_comment)	
		else insertInBegin(new_comment, GetById('comments-main'))

		$(new_comment).hide().fadeIn(1000);		
		} 
		
	var antibot = GetById('antibot')
	if (antibot != null ) {
	    antibot.value = ''
		GetById('comment-captcha').src = base_url + 'instruments/captcha/captcha.php?refresh=' + rand(1337,31337) 			 
	}
	
	if ( response['code'] != 0 ) {
		GetById('comment-error-text').innerHTML = response['message']
		BlockVisible('comment-error', true)		
		
	} else BlockVisible('comment-error', false)	
	
	}
	
	SendByXmlHttp('action.php', 'method=comment&comment=' + encodeURIComponent(text) + '&item_id=' + encodeURIComponent(item_id) + '&item_type=' + encodeURIComponent(item_type) + addition_post, event)
	return false
}

function Register() {	

	var login  = getValById('register-login')  
	var pass   = getValById('register-pass')
	var pass2  = getValById('register-repass')
	var email  = getValById('register-email')
	var female = getValById('register-female')
    
    if ( login == null || pass == null || pass2 == null || email == null || female == null) return false	
	if (pass.length < 1 || pass2.length < 1 || login.length < 1 || email.length < 1 ) return false

	toggleButton('create')
	
    var event = function(response) {
	
	GetById('loginform-error-text').className = 'alert alert-error'	
	
        if ( response['code'] == 0 ) { 

			GetById('auth-login').value = login
			GetById('auth-pass').value  = pass
			GetById('loginform-error-text').className = 'alert alert-success'
        }	
		
	GetById('loginform-error-text').innerHTML = response['message'] 	
	toggleButton('create')
	BlockVisible('loginform-error',true)			
	}
	
	SendByXmlHttp('register.php', 'login=' + encodeURIComponent(login) + '&pass=' + encodeURIComponent(pass) + '&repass=' + encodeURIComponent(pass2) + '&email=' + encodeURIComponent(email) + '&female=' + encodeURIComponent(female), event)
	return false
}

function RestoreStart() {

BlockVisible('reg-box', false)
BlockVisible('login-box', false)
BlockVisible('restore-box', true)

var img_obj =  GetById('antibot-visual') 
var img_src =  base_url + 'instruments/captcha/captcha.php?refresh=' + rand(1337,31337)
if (img_obj == null ) {

	var image = new Image()
	
	image.src = img_src
	
	image.onload = function(){	
	
		image.id = "antibot-visual"
		image.className = "img-polaroid"
		image.width = 70
		image.height = 30
		GetById('restore-img-holder').appendChild(image)
	}
} else img_obj.src = img_src
}

function Restore() {
	
	var email = getValById('restore-email')
	var code  = getValById('antibot')

	if ( email == null || code == null ) return false

	if ( email.length < 1 || code.length != 4) return false
	
	toggleButton('restore')

	var event = function(response) {
	
		var messageBoxText = GetById('loginform-error-text')
        
        if (response['code'] == 0) messageBoxText.className = 'alert alert-success';
 
		GetById('antibot-visual').src = base_url + 'instruments/captcha/captcha.php?refresh=' + rand(1337,31337)
		
		messageBoxText.innerHTML = response['message']
		BlockVisible('loginform-error',true)			
		toggleButton('restore')		  
	}
	
	SendByXmlHttp('action.php', 'method=restore&email=' + encodeURIComponent(email) + '&antibot=' + encodeURIComponent(code), event)
	return false
}

function LoadProfile(form, pid) {

	custom_profile = GetById(form) 
	if (!custom_profile) {
		custom_profile = document.createElement("DIV")	
		custom_profile.id = form
		custom_profile.className = "info-big-frame"
		document.body.appendChild(custom_profile)	
	}
	
	if (user_profile_id == pid) { 
	
	BlockVisible(form, true)	
	return false
	}		
	
	var event = function(response) {

		var margin = Math.round(GetScrollTop() + (getClientH()/2) - (224/2))
		
		if ( !response['player_info'] ) return false
		
		custom_profile.style.top =  margin + 'px' 
		custom_profile.innerHTML = response['player_info']				
        custom_profile.style.display = 'block'	
			
        user_profile_id = pid				
	}
	
	SendByXmlHttp('action.php', 'method=load_info&id=' + encodeURIComponent(pid), event)
    return false
}

function DeleteFile(id){

	var event = function(response) {

        if ( response['code'] != 0 ) return false

		$('#file-' + id).fadeOut(200, function (){
			
		var commentTrash = GetById('file-' + id)				
		if (commentTrash != null ) commentTrash.parentNode.removeChild(commentTrash)		
		});
	}
	
	SendByXmlHttp('action.php', 'method=delete_file&file=' + encodeURIComponent(id), event)
	return false
}

function UploadFile() {

    toggleButton('file-add-button')
	BlockVisible('file-loader', true)
		
	var event = function(response) {
	
		BlockVisible('file-loader', false)
		BlockVisible('file-upload-error', false)
		toggleButton('file-add-button')	

		if (response == null) return false
		
		if (response['code'] == 0 || response['code'] == 7) {	
		
		var files = GetById('last-files')
			files.innerHTML = response['file_html'] + files.innerHTML
		}
		
		if (response['code'] > 0 ) {
		
		GetById('file-upload-error').innerHTML = response['message']
		BlockVisible('file-upload-error', true)
		}				
	}
	
	sendFormByIFrame('file-upload', event)
    return false
}

function UpdateProfile(admTrg) {
	
    toggleButton('profile-button')

	var event = function (response) {

        clearFileInputField('profile-skin-file')
		clearFileInputField('profile-cloak-file')
		
		GetById('profile-update').reset()
		
        GetById('main-error-text').className = 'alert alert-error'		
		
		if (response != null) {	

            if (response['code'] == 0) GetById('main-error-text').className = 'alert alert-success'
			if (response['code'] == 100) { 
			toggleButton('profile-button') 
			BlockVisible('main-error',false)
			return 
			}
			
			GetById('main-error-text').innerHTML = nl2br(response['message'])
			BlockVisible('main-error',true)
		
		} else {

			GetById('main-error-text').innerHTML = err404 + req.status
			BlockVisible('main-error',true)
		
		}
        
		var name = GetById('profile-mini-name')
		if (name && !admTrg) name.innerHTML = response['name']
		
		var group = GetById('profile-group')
		if (group && !admTrg) group.innerHTML = response['group']

	    var Ava = new Image()
	        Ava.src = base_url + 'skin.php' + response['skin_link'] 
	        Ava.onload = function () {

                var ava_link = Ava.src
                var skin_front = GetById('profile-skin-front')
				if (skin_front != null ) skin_front.style.backgroundImage = 'url('+ava_link+')'
                var skin_back = GetById('profile-skin-back')
				if (skin_back != null ) skin_back.style.backgroundImage = 'url('+ava_link+')'  
      			
			    toggleButton('profile-button')
            }
		
		if (!admTrg) {
		
			var Mini = new Image()
				Mini.src =  base_url + 'skin.php' + response['mskin_link'] 
				Mini.onload = function () {
				   GetById('profile-mini').src = Mini.src 
				}	
		}
	}
	
	sendFormByIFrame('profile-update', event)
    return false
}

function Login() {

	var addition_post = '' 
	
	var login = getValById('auth-login')
	var pass  = getValById('auth-pass')
		
	if (GetById('login-antibot-form').style.display != 'none' ) {
	
		var antibot = GetById('auth-antibot')
		if (antibot.value.length != 4) return false		
		addition_post = '&antibot=' + encodeURIComponent(antibot.value)	
	}
    
	var save_session = 0
	if (GetById("auth-save").checked) save_session = 1
	
	if ( login == null || pass == null ) return false
	
	if (login.length < 1 || pass.length < 1) return false
	
	toggleButton('login')

	var event = function(response) {
	
		var messageBoxText = GetById('loginform-error-text')

		if (response['code'] == 0) { document.location.reload(true); return false; }
	
	else if (response['code'] == 6 || response['auth_fail_num'] >= 5 ) {
	
		var img_obj =  GetById('login-img-visual') 
		var img_src =  base_url + 'instruments/captcha/captcha.php?refresh=' + rand(1337,31337)
		if (img_obj == null ) {

			var image = new Image()	
			image.src = img_src	
			image.onload = function(){
			
				BlockVisible('login-antibot-form', true)	
				
				image.id = "login-img-visual"
				image.className = "img-polaroid"
				image.width = 70
				image.height = 30
				GetById('login-img-holder').appendChild(image)
			}
			
		} else img_obj.src = img_src		
			
	}
		
		messageBoxText.innerHTML = response['message']
		BlockVisible('loginform-error',true)	
		toggleButton('login')				
	}

	SendByXmlHttp('login.php', 'login=' + encodeURIComponent(login) + '&pass=' + encodeURIComponent(pass) + '&save=' + encodeURIComponent(save_session) + addition_post, event)	
	return false
}