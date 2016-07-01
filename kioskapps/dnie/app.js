window.atimeout = null;
var smartcardconnectorid = "khpfeaanjngmcnplbdlpegiifgpfgdco", port, channelid, hContext, szGroups, szReader, hCard, readerStates = [];

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

function extPostMessage(port, request_id, function_name, arguments) {
  port.postMessage({
  "type": "pcsc_lite_function_call::request",
    "data": {
      "request_id": request_id,
      "payload": {
        "function_name": function_name,
        "arguments": arguments
      }
    }
  });
}

function error(msg, detail) {
  $("#msgerror").innerHTML = chrome.i18n.getMessage(msg);
  if (Number.isInteger(detail)) {
    $("#msgerrordetail").style.display = "block";
    extPostMessage(port, 98, "pcsc_stringify_error", [detail]);
  } else if (detail !== "") {
    $("#msgerrordetail").style.display = "block";
    $("#msgerrordetail").innerHTML = chrome.i18n.getMessage(msg);
    pages.changePage("error");
  } else {
    pages.changePage("error");
  }
}

function snackbar(msg) {
  $(".mdl-snackbar").MaterialSnackbar.showSnackbar({"message": chrome.i18n.getMessage(msg)});
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
    console.log(szReader);

    extPostMessage(port, 4, "SCardGetStatusChange", [hContext, 30000, [{"reader_name": szReader, "current_state": 16}]]);
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

  port = chrome.runtime.connect(smartcardconnectorid);

  extPostMessage(port, 1, "SCardEstablishContext", [0, null, null]);

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
      if (typeof(msg.data.error) !== "undefined") {
        console.error(msg.data.error);
      } else if (Number.isInteger(msg.data.payload[0]) && msg.data.payload[0] < 0 && msg.data.payload[0] != -2146435062) {
        error("generalError", msg.data.payload[0]);
      } else {
        if (msg.data.request_id == 1) {
          hContext = msg.data.payload[1];
          extPostMessage(port, 2, "SCardListReaders", [hContext, null]);
        } else if (msg.data.request_id == 2) {
          szReader = msg.data.payload[1][0];
          console.log(szReader);
          extPostMessage(port, 3, "SCardConnect", [hContext, szReader, 3, 3]);
          pages.changePage("welcome"); // @TODO: Change to "login" when in PRODUCTION mode
        } else if (msg.data.request_id == 3) {
          hCard = msg.data.payload[1];
          dwActiveProtocol = msg.data.payload[2];
        } else if (msg.data.request_id == 4) {
          var payload = msg.data.payload;
          if (payload[0] == -2146435062) {
            // @TODO: snackbar
            snackbar("errorTimeout");
            pages.changePage("welcome");
          } else if (payload[0] == "Command timeout.") {
            return;
          } else if (payload[1][0].current_state == 16) {
            console.log(payload[1][0]);
            if (payload[1][0].atr[0] == 59 && payload[1][0].atr[1] == 127 && payload[1][0].atr[3] == 0 && payload[1][0].atr[4] == 0 && payload[1][0].atr[5] == 0 && payload[1][0].atr[6] == 106 && payload[1][0].atr[7] == 68 && payload[1][0].atr[8] == 78 && payload[1][0].atr[9] == 73 && payload[1][0].atr[10] == 101) {
              if (payload[1][0].atr[18] == 144 && payload[1][0].atr[19] == 0) {
                pages.changePage("data");
                extPostMessage(port, 5, "SCardTransmit", [hCard, {"dwProtocol": 1, "cbPciLength": 2}, ["00", "a4", "00", "00", "02", "50", "15"], 7]);
              } else if (payload[1][0].atr[18] == 101 && payload[1][0].atr[19] == 129) {
                snackbar("warningOutdatedDNIE");
                pages.changePage("data");
                extPostMessage(port, 5, "SCardTransmit", [hCard, 1, ["00", "a4", "00", "00", "02", "50", "15"], 7]);
              } else {
                pages.changePage("welcome");
                snackbar("errorNotDNIE");
              }
              // YEY!
            } else {
              pages.changePage("welcome");
              snackbar("errorNotDNIE");
            }
            //console.log(msg.data.payload[1][0]);
          }
        } else if (msg.data.request_id == 5) {
          $("#dataName").innerText = "Adrià Vilanova Martínez";
          $("#dataDni").innerText = "12345678A";
          $("#dataBirthday").innerText = "04/01/1999";
          pages.changePage("data");
        } else if (msg.data.request_id == 98) {
          $("#msgerrordetail").innerText = msg.data.payload[0];
          pages.changePage("error");
        }
      }
    } else {
      console.warn("What did we just receive?");
    }
    console.log(msg);
  });
}

window.addEventListener('load', init);
