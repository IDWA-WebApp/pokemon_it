//αρχικό εφέ fadein με τη φόρτωση της σελίδας
function trandiv() {
 if (document.getElementById('trandiv')) {
  document.getElementById('trandiv').style.opacity = '1';
  document.getElementById('trandiv').style.height = 'auto';
 }
}
window.onload = trandiv();


//εμφάνιση μηνύματος
function ShowMessage(message) {
 document.getElementById('MyMessage_Text').innerHTML = message;
 document.getElementById('div_MyMessage').style.opacity = '1';
 document.getElementById('div_MyMessage').style.visibility = 'visible';
 HideMessageDiv();
}


//απόκρυψη μηνύματος μετά από 5 δευτερόλεπτα
function HideMessageDiv() {
 var t = setTimeout('HideMessageDivCommand();', 5000);
}
function HideMessageDivCommand() {
 if (document.getElementById('div_MyMessage')) {
  document.getElementById('div_MyMessage').style.visibility = 'hidden';
  document.getElementById('div_MyMessage').style.opacity = '0';
 }
}

