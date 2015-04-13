function getParam(param){
  var params = window.location.search.substring(1).split('&');

  for(var i = 0; i < params.length; i++){
    var sp = params[i].split('=', 2);
    if (sp[0] === param){
      return decodeURIComponent(sp[1].replace(/\+/g, " "));
    }
  }

  return '';
}
