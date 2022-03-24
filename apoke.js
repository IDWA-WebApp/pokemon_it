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
