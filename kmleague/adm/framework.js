function flip( id ){
	var o = document.getElementById(id);
	if ( o.style.display == "block" )
		o.style.display = "none";
	else
		o.style.display = "block";
}
function usun(link,comm){
	var agree=confirm(comm);
	if (agree)
	document.location=link;
}

function displayWindow(url, nazwa, width, height){
   window.open(url,nazwa,'width=' + width + ',height=' + height + ',resizable=0,scrollbars=yes,menubar=no,status' )
}

function addNewElement(elemID, elemInf){
	elementRef = document.getElementById(elemID);
	elementNew = document.createElement('span');
	elementRef.appendChild(elementNew);
	elementNew.innerHTML += elemInf;
}
