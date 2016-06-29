window.atimeout = null;
var smartcardconnectorid = "khpfeaanjngmcnplbdlpegiifgpfgdco", port, channelid;

function $(selector) {
    return document.querySelector(selector);
}

function $all(selector) {
    return document.querySelectorAll(selector);
}

function isEmpty(obj) {
    return Object.keys(obj).length === 0;
}

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

function i18n() {
  var i18ne = $all("*[data-i18n]");
  for (var i = 0; i < i18ne.length; i++) {
    i18ne[i].innerHTML = chrome.i18n.getMessage(i18ne[i].getAttribute("data-i18n"));
  }
}

var pages = {
  canCancel: ["insertdni", "data", "done"],
  actualPage: "login",
  changePage: function(page) {
    $("#"+this.actualPage).style.display = "none";
    $("#"+page).style.display = "block";

    if (this.canCancel.includes(page)) {
      $("#reset").style.display = "block";
    } else {
      $("#reset").style.display = "none";
    }

    if (page == "welcome") {
      reset();
    }

    this.actualPage = page;
  }
};

function reset() {
  // Function which should reset the kiosk
  sessionStorage.code = "";
  $("#dataName").innerText = "";
  $("#dataDni").innerText = "";
  $("#dataBirthday").innerText = "";

  $("#savingData").style.display = "none";
  $("#dataNext").disabled = false;

  window.clearTimeout(window.atimeout);

  $("#showcodeIntro").innerHTML = "";
}

function init() {
  i18n();

  $("#setup").addEventListener("click", function() {
    var apiurl = $("#api").value,
        secretKey = $("#secretKey").value;

    if (apiurl === "" || secretKey === "") {
      console.warn("apiurl or secretKey are not set.");
      return;
    }

    sessionStorage.apiurl = apiurl;
    sessionStorage.scretKey = secretKey;

    //XHR to get some info

    $("#welcomeIntro").innerHTML = chrome.i18n.getMessage("welcomeIntro", [votationName]); // votationName has to be changed to the votation name which is retrieved from the XHR

    pages.changePage("welcome");
  });

  $("#welcomeNext").addEventListener("click", function() {
    pages.changePage("insertdni");

    // Add event listener for DNI insertion. I will just do a timeout here to show it:
    window.atimeout = window.setTimeout(function() {
      $("#dataName").innerText = "Adrià Vilanova Martínez";
      $("#dataDni").innerText = "12345678A";
      $("#dataBirthday").innerText = "04/01/1999";
      pages.changePage("data");
    }, 6000);
  });

  $("#dataNext").addEventListener("click", function() {
    $("#savingData").style.display = "block";
    $("#reset").style.display = "none";
    $("#dataNext").disabled = true;

    // Add XHR to save data, and when ended change page. I will just do a timeout here to show it:
    window.atimeout = window.setTimeout(function() {
      sessionStorage.code = "QWERTYUIOP";
      qr.canvas({
        canvas: $("#qrPlaceholder"),
        value: sessionStorage.code,
        size: 8
      });
      $("#showcodeIntro").innerHTML = chrome.i18n.getMessage("showcodeIntro"); // When in PRODUCTION, it can also be showcodeIntroAgain, in case the code is regenerated
      pages.changePage("showcode");
    }, 1000);
  });

  $("#showcodeDone").addEventListener("click", function() {
    pages.changePage("done");

    window.atimeout = window.setTimeout(function() {
      pages.changePage("welcome");
    }, 5000);
  });

  $("#showcodePrint").addEventListener("click", function() {
    pages.changePage("printing");

    // Here there should be code to print, but instead I'm going to do a timeout to show it:
    window.atimeout = window.setTimeout(function() {
      pages.changePage("done");

      window.atimeout = window.setTimeout(function() {
        pages.changePage("welcome");
      }, 5000);
    }, 4000);
  });

  $("#reset").addEventListener("click", function() {
    pages.changePage("welcome");
  });

  // The following 2 lines is for developing only. DELETE IN PRODUCTION!
  $("#welcomeIntro").innerHTML = chrome.i18n.getMessage("welcomeIntro", ["Election of the committee"]);
  //pages.changePage("welcome");

  /*chrome.usb.getDevices({}, function(devices) {
    if (devices.length > 0) {
      chrome.usb.openDevice(devices[0], function(connection) {
        if (connection) {
          window.usbConnection = connection;
          console.log("Device opened.");
        } else {
          console.log("Device failed to open");
        }
      });
    }
  });*/
  chrome.usb.findDevices({"vendorId": 1423, "productId": 38176}, function (devices) {
    console.log(devices);
  });
  
  port = chrome.runtime.connect(smartcardconnectorid);
  
  port.postMessage({
  "type": "pcsc_lite_function_call::request",
    "data": {
      "request_id": 1,
      "payload": {
        "function_name": "SCardEstablishContext",
        "arguments": [0]
      }
    }
  });
  
  port.onMessage.addListener(function(msg) {
    if (msg.type == "pong") {
      channelid = msg.data.channel_id;
    } else if (msg.type == "ping") {
      if (typeof(channelid) === "undefined") {
        channelid = Math.floor(Math.random() * 999) + 1;
      }
      port.postMessage({
        "type": "pong",
        "data": {
          "channel_id": channelid
        }
      });
    } else if (msg.type == "pcsc_lite_function_call::response") {
      if (msg.data.request_id == 1) {
        if (typeof(msg.data.error) !== "undefined") {
          console.error(msg.data.error);
        } else {
          console.log("We got a response for SCardEstablishContext");
          pages.changePage("login");
        }
      }
    } else {
      console.warn("What did we just receive?");
    }
    console.log(msg);
  });
}

window.addEventListener('load', init);
