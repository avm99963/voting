window.atimeout = null;
var smartcardconnectorid = "kccifahimnddhgjicmldpjinclohoekf", port, channelid, hContext, szGroups, szReader, hCard, readerStates = [], serial, idesp, version, caint, caroot;

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

var api = {
  request: function(action, method, data, callback) {
    var params = ((data === "") ? "" : "&")+"apikey="+encodeURIComponent(sessionStorage.secretKey)+"&action="+encodeURIComponent(action);
    xhr(method, sessionStorage.apiurl, params, function(response, status) {
      var json = JSON.parse(response);
      
      console.log(json);
      
      if (json.status == "error") {
        console.error("API error "+json.errorcode+": "+json.errormsg);
      }
      
      callback(json, status);
    });
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

function extPostMessage(port, request_id, function_name, arg) {
  port.postMessage({
  "type": "pcsc_lite_function_call::request",
    "data": {
      "request_id": request_id,
      "payload": {
        "function_name": function_name,
        "arguments": arg
      }
    }
  });
}

function error(msg, detail, nontranslatable = false) {
  $("#msgerror").innerHTML = chrome.i18n.getMessage(msg);
  if (Number.isInteger(detail)) {
    $("#msgerrordetail").style.display = "block";
    extPostMessage(port, 98, "pcsc_stringify_error", [detail]);
  } else if (detail !== "") {
    $("#msgerrordetail").style.display = "block";
    if (nontranslatable === true) {
      $("#msgerrordetail").innerHTML = detail;
    } else {
      $("#msgerrordetail").innerHTML = chrome.i18n.getMessage(msg);
    }
    pages.changePage("error");
  } else {
    pages.changePage("error");
  }
}

function snackbar(msg) {
  $(".mdl-snackbar").MaterialSnackbar.showSnackbar({"message": chrome.i18n.getMessage(msg)});
}

function SCardTransmit(port, request_id, hCard, cmd) {
  extPostMessage(port, request_id, "SCardTransmit", [hCard, {"dwProtocol": 1, "cbPciLength": 2}, cmd, {}]);
}

function hi(int) {
  var hex = int.toString(16);
  var tmp = ('0000'+hex).substring(hex.length);

  return parseInt(tmp.substr(-4, 2), 16);
}

function lo(int) {
  var hex = int.toString(16);
  var tmp = ('0000'+hex).substring(hex.length);

  return parseInt(tmp.substr(-2, 2), 16);
}

var file = {
  locked: false,
  path: null,
  filesize: null,
  offset: null,
  stream: [],
  operation: null,
  readFile: function(hCard, path, operation) {
    console.info("Starting to read file with path:", path);
    this.operation = operation;
    if (this.locked === false) {
      this.path = path;
      var cmd = [0x00, 0xA4, 0x04, 0x00, 0x0B, 0x4D, 0x61, 0x73, 0x74, 0x65, 0x72, 0x2E, 0x46, 0x69, 0x6C, 0x65];
      SCardTransmit(port, 5, hCard, cmd);
      return true;
    } else {
      console.error("There's a file being read, so the operation to "+hcard+" with path ", path, "couldn't procede.");
      return false;
    }
  },
  followUp: function(payload) {
    //console.info(payload);
    if (this.path.length >= 2) {
      var following = [0x00, 0xA4, 0x00, 0x00, 0x02, this.path.shift(), this.path.shift()];
      SCardTransmit(port, 6, hCard, following);
    } else {
      console.info("Sending GET RESPONSE");
      var cmd = [0x00, 0xC0, 0x00, 0x00, 0x0E];
      SCardTransmit(port, 7, hCard, cmd);
    }
  },
  responseGotten: function(payload) {
    //console.log(payload);
    this.filesize = (payload[2][7] * 256) + payload[2][8];
    this.offset = 0;
    //console.log(this.filesize);
    var cmd = [0x00, 0xB0, hi(this.offset), lo(this.offset), Math.min((this.filesize - this.offset), 0xFF)];
    console.info("Sending READ BINARY", cmd);
    SCardTransmit(port, 8, hCard, cmd);
  },
  binaryRead: function(payload) {
    var newdata = payload[2].splice(0, payload[2].length-2);
    this.stream = this.stream.concat(newdata);
    this.offset += 255;
    if (this.offset < this.filesize) {
      var cmd = [0x00, 0xB0, hi(this.offset), lo(this.offset), Math.min((this.filesize - this.offset), 0xFF)];
      console.info("Sending READ BINARY", cmd);
      SCardTransmit(port, 8, hCard, cmd);
    } else {
      var stream = this.stream;
      var operation = this.operation;
      this.path = null;
      this.filesize = null;
      this.offset = null;
      this.stream = [];
      this.operation = null;
      this.locked = false;
      fileRead(stream, operation);
    }
  }
};

// From http://stackoverflow.com/questions/3745666/how-to-convert-from-hex-to-ascii-in-javascript and adapted
function hex2a(stream) {
    var hex = stream.map(function (int) {
      return int.toString(16);
    }).join("");
    var str = '';
    for (var i = 0; i < hex.length; i += 2)
        str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
    return str;
}

function fileRead(stream, operation) {
  console.log(stream);
  var contents = hex2a(stream);
  console.log(contents);
  if (operation == 1) {
    idesp = contents;
    file.readFile(hCard, [0x60, 0x1F], 2);
  } else if (operation == 2) {
    var hex = stream.map(function (int) {
      var string = int.toString(16);
      return ('00'+string).substring(string.length);
    }).join("");

    console.log(hex);

    var pem = KJUR.asn1.ASN1Util.getPEMStringFromHex(hex, "CERTIFICATE");

    console.log(pem);

    caroot = pem;
    
    console.log(X509.getPublicKeyFromCertPEM(caroot));

    file.readFile(hCard, [0x60, 0x20], 3);

    /*var c = new X509();
    c.readCertPEM(pem);

    console.log(c.getSubjectString());
    console.log(c.getSignatureAlgorithmField());

    pages.changePage("data");*/
  } else if (operation == 3) {
    var hex = stream.map(function (int) {
      var string = int.toString(16);
      return ('00'+string).substring(string.length);
    }).join("");

    console.log(hex);

    var pem = KJUR.asn1.ASN1Util.getPEMStringFromHex(hex, "CERTIFICATE");

    console.log(pem);

    caint = pem;
    
    console.log(X509.getPublicKeyFromCertPEM(caint));
    
    $("#dataIDESP").innerText = idesp;

    pages.changePage("data");
  } else {
    console.warn("Unknown file operation.");
  }
}

function loadDniData() {
  SCardTransmit(port, 96, hCard, [0x90, 0xB8, 0x00, 0x00, 0x00, 0x00, 0x07]);
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
    sessionStorage.secretKey = secretKey;

    //XHR to get some info
    api.request("init", "GET", "", function(message, status) {
      
      $("#welcomeIntro").innerHTML = chrome.i18n.getMessage("welcomeIntro", [message.payload.votingName]);

      pages.changePage("welcome");
    });
  });

  $("#welcomeNext").addEventListener("click", function() {
    pages.changePage("insertdni");

    // Add event listener for DNI insertion. I will just do a timeout here to show it:
    console.info("Waiting for card insertion...");

    extPostMessage(port, 3, "SCardConnect", [hContext, szReader, 3, 3]);
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
      // @TODO: Comment/delete the following line when in PRODUCTION, as it may cause sensitive information leaks in the console
      console.log(msg);
      if (typeof(msg.data.error) !== "undefined") {
        console.error("Error in request_id #"+msg.data.request_id+": "+msg.data.error);
        error("generalError", msg.data.error, true);
      } else if (Number.isInteger(msg.data.payload[0]) && msg.data.payload[0] < 0 && msg.data.payload[0] != -2146435062) {
        if (msg.data.payload[0] == -2146434967) {
          pages.changePage("welcome");
          snackbar("errorCardRemoved");
        } else if (msg.data.payload[0] == -2146435050) {
          pages.changePage("welcome");
          snackbar("errorTransactionFailed");
        } else {
          error("generalError", msg.data.payload[0]);
        }
      } else {
        if (msg.data.request_id == 1) {
          hContext = msg.data.payload[1];
          extPostMessage(port, 2, "SCardListReaders", [hContext, null]);
        } else if (msg.data.request_id == 2) {
          szReader = msg.data.payload[1][0];
          pages.changePage("login"); // @TODO: Change to "login" when in PRODUCTION mode
          // DELETE THE FOLLOWING IN PRODUCTION:
          //pages.changePage("welcome");

          // Add event listener for DNI insertion. I will just do a timeout here to show it:
          //console.info("Waiting for card insertion...");

          //extPostMessage(port, 3, "SCardConnect", [hContext, szReader, 3, 3]);
        } else if (msg.data.request_id == 3) {
          hCard = msg.data.payload[1];
          dwActiveProtocol = msg.data.payload[2];
          extPostMessage(port, 4, "SCardGetStatusChange", [hContext, 30000, [{"reader_name": szReader, "current_state": 16}]]);
        } else if (msg.data.request_id == 4) {
          var payload = msg.data.payload;
          if (payload[0] == -2146435062) {
            snackbar("errorTimeout");
            pages.changePage("welcome");
          } else if (payload[1][0].current_state == 16) {
            if (payload[1][0].atr[0] == 59 && payload[1][0].atr[1] == 127 && payload[1][0].atr[3] == 0 && payload[1][0].atr[4] == 0 && payload[1][0].atr[5] == 0 && payload[1][0].atr[6] == 106 && payload[1][0].atr[7] == 68 && payload[1][0].atr[8] == 78 && payload[1][0].atr[9] == 73 && payload[1][0].atr[10] == 101) {
              if ((payload[1][0].atr[18] == 144 && payload[1][0].atr[19] == 0) || (payload[1][0].atr[18] == 101 && payload[1][0].atr[19] == 129)) {
                pages.changePage("reading");
                loadDniData();
                if (payload[1][0].atr[18] == 101 && payload[1][0].atr[19] == 129) {
                  snackbar("warningOutdatedDNIE");
                }
              } else {
                pages.changePage("welcome");
                snackbar("errorNotDNIE");
              }
              // YEY!
            } else {
              pages.changePage("welcome");
              snackbar("errorNotDNIE");
            }
          }
        } else if (msg.data.request_id == 5) {
          file.followUp(msg.data.payload);
        } else if (msg.data.request_id == 6) {
          file.followUp(msg.data.payload);
        } else if (msg.data.request_id == 7) {
          file.responseGotten(msg.data.payload);
        } else if (msg.data.request_id == 8) {
          file.binaryRead(msg.data.payload);
        } else if (msg.data.request_id == 10) {
          $("#dataName").innerText = "Adrià Vilanova Martínez";
          $("#dataDni").innerText = "12345678A";
          $("#dataBirthday").innerText = "04/01/1999";
          pages.changePage("data");
        } else if (msg.data.request_id == 96) {
          serial = msg.data.payload[2];
          console.log("Serial is ",serial);
          file.readFile(hCard, [0x00, 0x06], 1);
        } else if (msg.data.request_id == 98) {
          $("#msgerrordetail").innerText = msg.data.payload[0];
          pages.changePage("error");
        }
      }
    } else {
      console.warn("What did we just receive?");
    }
  });
}

window.addEventListener('load', init);
