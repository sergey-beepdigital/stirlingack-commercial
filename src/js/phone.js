function getQuerystring(name) {
    var result = null;
    var regexS = "[\\?&#]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec('?'+window.location.href.split('?')[1]);
    if(results !== null){
      result = decodeURIComponent(results[1].replace(/\+/g, " "));
    }
    return result;
}

function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function applyDetails(queryStringName, cookieName, data) {
  var sales = data.sales[0];
  var lettings = data.lettings[0];

  if(!getCookie(cookieName)) {
    console.log('setting cookie');
    var qs = getQuerystring(queryStringName);
    if(qs) {
      setCookie(cookieName, (qs-1), 30);
      sales = data.sales[qs-1] || sales;
      lettings = data.lettings[qs-1] || lettings;
    }
  } else {
    console.log('getting cookie');
    var cookie = getCookie(cookieName);
    sales = data.sales[cookie] || sales;
    lettings = data.lettings[cookie] || lettings;
  }

  console.log('sales', sales);
  console.log('lettings', lettings);

  var salesOutput = document.querySelectorAll('.sales');
		salesOutput.forEach(function(o) {
			o.innerText = sales;
			o.setAttribute('title', sales);
      o.setAttribute('href', 'tel:' + sales);
	});

  var lettingsOutput = document.querySelectorAll('.lettings');
		lettingsOutput.forEach(function(o) {
			o.innerText = lettings;
			o.setAttribute('title', lettings);
      o.setAttribute('href', 'tel:' + lettings);
	});

}