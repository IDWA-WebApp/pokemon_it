//initial fadein fx onload of the page
function trandiv() {
 if (document.getElementById('trandiv')) {
  document.getElementById('trandiv').style.opacity = '1';
  document.getElementById('trandiv').style.height = 'auto';
 }
}
window.onload = trandiv();


//show messages
function ShowMessage(message) {
 document.getElementById('MyMessage_Text').innerHTML = message;
 document.getElementById('div_MyMessage').style.opacity = '1';
 document.getElementById('div_MyMessage').style.visibility = 'visible';
 HideMessageDiv();
}


//hide messages after 5 seconds
function HideMessageDiv() {
 var t = setTimeout('HideMessageDivCommand();', 5000);
}
function HideMessageDivCommand() {
 if (document.getElementById('div_MyMessage')) {
  document.getElementById('div_MyMessage').style.visibility = 'hidden';
  document.getElementById('div_MyMessage').style.opacity = '0';
 }
}


//hover fx and click on favorites button
function fav_over(row) {
 document.getElementById('fav-pokemon-' + row).style.transform = 'scale(var(--ggs,0.16))';
 document.getElementById('fav-pokemon-color-' + row).style.transform = 'scale(var(--ggs,0.088))';
}
function fav_out(row) {
 document.getElementById('fav-pokemon-' + row).style.transform = 'scale(var(--ggs,0.15))';
 document.getElementById('fav-pokemon-color-' + row).style.transform = 'scale(var(--ggs,0.08))';
}
function fav_click(row) {
 var fp = document.getElementById('fav-pokemon-' + row);
 var fpc = document.getElementById('fav-pokemon-color-' + row);
 if (fp.classList.contains('fav-pokemon-active')) {
  fp.classList.add('fav-pokemon-inactive');
  fpc.classList.add('half-circle-inactive');
  fp.classList.remove('fav-pokemon-active');
  fpc.classList.remove('half-circle-active');
 } else {
  fp.classList.add('fav-pokemon-active');
  fpc.classList.add('half-circle-active');
  fp.classList.remove('fav-pokemon-inactive');
  fpc.classList.remove('half-circle-inactive');
 }
}


//AJAX call for add/remove favorites
function FavoriteCall(poke_id, irow, myhash) {
 if ((poke_id=='') || (isNaN(poke_id))) return false;
 var addrem;
 if (document.getElementById('fav-pokemon-' + irow)) {
  if (document.getElementById('fav-pokemon-' + irow).title=='Add to Favorites') addrem = 'add';
  if (document.getElementById('fav-pokemon-' + irow).title=='Remove from Favorites') addrem = 'remove';
 }
 var data = 'poke_id=' + poke_id + '&action=' + addrem + '&hash=' + myhash;
 var xmlhttp = new XMLHttpRequest();
 xmlhttp.open('POST','apokefav_api.php',true);
 xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
 xmlhttp.extraInfo = irow;
 xmlhttp.send(data);
 xmlhttp.onload = function(irow) {
  var state = this.readyState;
  if (state==this.DONE) {
   if (xmlhttp.status != 200) { //no response
    ShowMessage('We are sorry! The action was not completed. Please try again.');
    return false;
   } else { //call was made
    if (xmlhttp.response!='OK') { //incorrect response
     ShowMessage('We are sorry! The action was not completed. Please try again. (Message: ' + xmlhttp.response + ')');
     return false;
    } else {
     //document.getElementById('sLoading_' + i).style.visibility = 'hidden';
     //document.getElementById('sLoading_' + i).style.display = 'none';
     var irow = xmlhttp.extraInfo
     var newtitlevalue = '';
     var favbutton = document.getElementById('fav-pokemon-' + irow);
     if (favbutton.title=='Add to Favorites') newtitlevalue = 'Remove from Favorites';
     if (favbutton.title=='Remove from Favorites') newtitlevalue = 'Add to Favorites';
     favbutton.title = newtitlevalue;
     fav_click(irow);
     return true;
    }
   }
  }
 };
}


//functions for the interactive search box/drop down
//show list
function xdropdownFunctionP(id) {
 document.getElementById('xmyDropdownP' + id).classList.toggle('showP' + id);
 if (document.getElementById('xdropdownstateP' + id).value=='') {
  document.getElementById('xdropdownstateP' + id).value='1';
  return true;
 }
 if (document.getElementById('xdropdownstateP' + id).value=='1') {
  document.getElementById('xdropdownstateP' + id).value='';
  return true;
 }
}
//hide list on Esc key
function xdropdownEscCheckP(evt) {
 var charCode = (evt.which) ? evt.which : event.keyCode
 if (charCode == 27) {
  for (var i = 1; i <= 20; i++) {
   if (document.getElementById('xdropdownstateP' + i)) {
    if (document.getElementById('xdropdownstateP' + i).value=='1') {
     document.getElementById('xmyInputP' + i).value='';
     xfilterFunctionP(i);
     document.getElementById('xmyDropdownP' + i).classList.toggle('showP' + i);
     document.getElementById('xdropdownstateP' + i).value='';
    }
   } else {
    break;
   }
  }
 }
}
//search filter
function xfilterFunctionP(id) {
 var input, filter, ul, li, a, i, t;
 input = document.getElementById('xmyInputP' + id);
 filter = input.value.toUpperCase();
 div = document.getElementById('xmyDropdownP' + id);
 a = div.getElementsByTagName('a');
 for (i = 0; i < a.length; i++) {
  t = a[i].id;
  if (t.substr(0, 10)!='hiddenhref') {
   if (a[i].innerHTML.toUpperCase().indexOf(filter) > -1) {
    a[i].style.display = '';
   } else {
    a[i].style.display = 'none';
   }
  }
 }
}
//hide on click away of the search box
window.onclick = function(ev) {
 var go;
 for (var i = 1; i <= 20; i++) {
  if (document.getElementById('xdropdownstateP' + i)) {
   go = 0;
   if ((ev.target.name!='xdropdown1P' + i) && (ev.target.name!='xdropdown2P' + i)) go = 1;
   if (go==1) {
    if (document.getElementById('xdropdownstateP' + i).value=='1') {
     document.getElementById('xmyInputP' + i).value='';
     xfilterFunctionP(i);
     document.getElementById('xmyDropdownP' + i).classList.toggle('showP' + i);
     document.getElementById('xdropdownstateP' + i).value='';
    }
   }
  }
 }
}
