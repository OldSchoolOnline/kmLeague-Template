var mps = 0;
function maps(which, map){
	++mps;
	var stringas = '';
	stringas += mps+'. <input type="text" name="map['+mps+']" size="10" maxlength="10" value="'+map+'"> ';
	element = document.getElementById(which);
	nowyelement = document.createElement('span');
	idek = 'mps'+mps;
	nowyelement.id = idek;
	element.appendChild(nowyelement);
	nowyelement.innerHTML += stringas;
}
function flip( id ){
	var o = document.getElementById(id);
	if ( o.style.display == "block" )
		o.style.display = "none";
	else
		o.style.display = "block";
}
function dodaj(param){
	window.document.forms['formula'].descr.value = window.document.forms['formula'].descr.value + ' ' + param;
}
function dodaj_flage(param){
	window.document.forms['formula'].descr.value = window.document.forms['formula'].descr.value + ' [LFLAG="' + param + '"]';
}

function checkUncheckAll(theElement) {
	var theForm = theElement.form, z = 0;
	for(z=0; z<theForm.length;z++){
		if(theForm[z].type == 'checkbox' && theForm[z].name != 'checkall'){
			theForm[z].checked = theElement.checked;
		}
	}
}