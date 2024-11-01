function hideCustomPath(optionsValue){
	if(optionsValue.value == "1"){
		document.getElementById("custom_path").style.display = "";
	} else {
		document.getElementById("custom_path").style.display = "none";
	}
}
function validate(){
	var submitForm = document.save;
	if(!alphanumeric(submitForm.key.value) ){
		submitForm.key.value="";
		submitForm.key.focus();
		return false;
	}
	return true;
}

function alphanumeric(keyValue){
	if(keyValue == "")
	{
		return false;
	}
	if(keyValue.indexOf(' ') >= 0 ) { 
	   alert('wSecure key should not contain white spaces!!');
	   return false;
    }
	if( keyValue.length > 20 || keyValue.length < 5 )
	{
		alert('wSecure key should be between 5 to 20 characters!!');
		return false;	
	}
	
	if( /[^a-zA-Z0-9]/.test( keyValue ) ) { 
	   alert('wSecure key should not contain special characters!!');
	   return false;
    }
	if(! /[^0-9]/.test( keyValue ) ) { 
	   alert('wSecure key should not contain only numbers!!');
	   return false;
    }
	return true;
}

	function showTooltip(div1, title, desc)
	{
		jQuery( "#"+div1 ).css( 'display' , 'inline' );
		jQuery( "#"+div1 ).css( 'position' , 'absolute' );
		jQuery( "#"+div1 ).css( 'width' , '170' );
		jQuery( "#"+div1 ).css( 'border' , 'solid 1px #ccc' );
		jQuery( "#"+div1 ).css( 'padding' , '10px' );
		jQuery( "#"+div1 ).css( 'background' , 'rgb(234, 236, 240)' );
		jQuery( "#"+div1 ).css( 'z-index' , '20' );
		jQuery( "#"+div1 ).html( '<b>' + title + '</b><div style="padding-left:10; padding-right:5">' + desc + '</div>' );
	}
	
	function hideTooltip(div1)
	{
		jQuery( "#"+div1 ).css( 'display' , 'none' );
	}
function validateAdminProtect(){

var admin_password = document.getElementById('admin_password').value;
var verify_password = document.getElementById('verify_password').value;

if(admin_password == verify_password ){
	return true;
}
else{
	alert('Password does not match');
	return false;
}
}