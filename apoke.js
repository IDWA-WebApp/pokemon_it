//������ ��� fadein �� �� ������� ��� �������
function trandiv() {
 if (document.getElementById('trandiv')) {
  document.getElementById('trandiv').style.opacity = '1';
  document.getElementById('trandiv').style.height = 'auto';
 }
}
window.onload = trandiv();


//�������� ���������
function ShowMessage(message) {
 document.getElementById('MyMessage_Text').innerHTML = message;
 document.getElementById('div_MyMessage').style.opacity = '1';
 document.getElementById('div_MyMessage').style.visibility = 'visible';
 HideMessageDiv();
}


//�������� ��������� ���� ��� 5 ������������
function HideMessageDiv() {
 var t = setTimeout('HideMessageDivCommand();', 5000);
}
function HideMessageDivCommand() {
 if (document.getElementById('div_MyMessage')) {
  document.getElementById('div_MyMessage').style.visibility = 'hidden';
  document.getElementById('div_MyMessage').style.opacity = '0';
 }
}

