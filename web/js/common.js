function xhr(method, url, params, callback) {
  var http = new XMLHttpRequest();
  if (method == "POST") {
    http.open(method, url, true);
  } else {
    http.open(method, url+"?"+params, true);
  }
	http.onload = function() {
    if(this.status != 200) {
      console.warn("Attention, status code "+this.status+" when loading via xhr url "+url);
    }
    callback(this.responseText, this.status);
  };
  if (method == "POST") {
    http.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    http.send(params);
  } else {
    http.send();
  }
}

var dynDialog = {
  didItInit: false,
  dialog: null,
  url: null,
  load: function(url) {
    if (this.didItInit === false) {
      this.init();
    }

    if (this.url == url) {
      this.show();
      return;
    }

    this.url = url;

    xhr("GET", url, "", function(response, status) {
      this.dialog.innerHTML = response;
      componentHandler.upgradeElements(this.dialog);
      this.dialog.showModal();
    }.bind(this));
  },
  show: function() {
    this.dialog.showModal();
  },
  close: function() {
    this.dialog.close();
  },
  init: function() {
    this.dialog = document.createElement("dialog");
    this.dialog.setAttribute("id", "dynDialog");
    this.dialog.setAttribute("class", "mdl-dialog");
    dialogPolyfill.registerDialog(this.dialog);
    document.body.appendChild(this.dialog);

    this.didItInit = true;
  }
};

window.addEventListener("load", function() {
  var dialogs = document.querySelectorAll("dialog");
  for (var i = 0; i < dialogs.length; i++) {
    dialogPolyfill.registerDialog(dialogs[i]);
  }
});
