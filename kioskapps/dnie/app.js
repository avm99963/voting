window.atimeout = null;
var smartcardconnectorid = "kccifahimnddhgjicmldpjinclohoekf", port, channelid, hContext, szGroups, szReader, hCard, readerStates = [], serial, idesp, version, caint, publickeycaroot, rndifd, rndicc, kicc, kifd, kifdicc, kenc, ssc;

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
  },
  showPINDialog: function() {
    $("#pinDialog").showModal();
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

function SecureSCardTransmit(port, request_id, hCard, cla, ins, p1p2, p3, data) {
  if (request_id < 100) {
    console.error("Requests IDs under 100 are reserved for insecure requests. Please, use a request ID within the range [100, inf)");
    return false;
  }

  if (data.length > 0) {
    if (data.length % 16 != 0) {
      var left = 15 - (data.length % 16);
      data = data+"8"+('0'.repeat(left));
    }

    console.log(data);
  }

  var kenc = "598f26e36e11a8ec14b81e19bda223ca";
  var kmac = "5de2939a1ea03a930b88206d8f73e8a7";
  var ssc = "d31ac8ec7ba0fe74";

  var key = CryptoJS.enc.Hex.parse(kenc);
  var iv = "0000000000000000";

  var ciphertext = encryptByDES(CryptoJS.enc.Hex.parse(data), key, iv);
  console.log(ciphertext);

  //extPostMessage(port, request_id, "SCardTransmit", [hCard, {"dwProtocol": 1, "cbPciLength": 2}, cmd, {}]);
}

SecureSCardTransmit("", 100, "", "00", "a4", "0400", "0b", "4d61737465722e46696c65");

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

function hexs2a(hex) {
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

    publickeycaroot = X509.getPublicKeyFromCertPEM(pem);

    console.log(publickeycaroot);

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

    caint = X509.getPublicKeyFromCertPEM(pem);

    console.log(caint);

    $("#dataIDESP").innerText = idesp;

    secureChannel.establishSecureChannel();
  } else {
    console.warn("Unknown file operation.");
  }
}

function randomByte() {
  var buf = new Uint8Array(1);
  window.crypto.getRandomValues(buf);
  return buf[0];
}

function randomBytes(number) {
  var bytes = [];
  for (var i = 0; i < number; i++) {
    bytes[i] = randomByte();
  }
  return bytes;
}

function hexStringToByte(str) {
  if (!str) {
    return new Uint8Array();
  }

  var a = [];
  for (var i = 0, len = str.length; i < len; i+=2) {
    a.push(parseInt(str.substr(i,2),16));
  }

  return new Uint8Array(a);
}

function hexStringToArray(str) {
  if (!str) {
    return [];
  }

  var a = [];
  for (var i = 0, len = str.length; i < len; i+=2) {
    a.push(parseInt(str.substr(i,2),16));
  }

  return a;
}

function array2hexstring(array) {
  if (typeof array != "object") {
    return;
  }

  array = array.map(function(int) {
    return pad(int.toString(16), 2);
  });
  return array.join("");
}

/**
 * RSA hash function reference implementation.
 * https://en.wikipedia.org/wiki/RSA_(cryptosystem)#A_working_example
 *
 * @namespace
 */
var RSA = {};

/**
 * Encrypt
 * Uses BigInteger.js https://github.com/peterolson/BigInteger.js/tree/master
 *
 * @param   {m} int, the 'message' to be encoded
 * @param   {n} int, n value returned from generate_rsa() aka public key (part I)
 * @param   {e} int, e value returned from generate_rsa() aka public key (part II)
 * @returns {int} encrypted hash
 */
RSA.encrypt = function(m, n, e){
	return bigInt(m).modPow(e, n);
};

/**
 * Decrypt
 * Uses BigInteger.js https://github.com/peterolson/BigInteger.js/tree/master
 *
 * @param   {mEnc} int, the 'message' to be decoded (encoded with RSA_encrypt())
 * @param   {d} int, d value returned from generate_rsa() aka private key
 * @param   {n} int, n value returned from generate_rsa() aka public key (part I)
 * @returns {int} decrypted hash
 */
RSA.decrypt = function(mEnc, d, n){
	return bigInt(mEnc).modPow(d, n);
};

function pad(str, number) {
  var pad = '0'.repeat(number);
  return pad.substring(0, pad.length - str.length) + str;
}

var secureChannel = {
  establishSecureChannel: function() {
    // Selection Root CA
    SCardTransmit(port, 11, hCard, [0x00, 0x22, 0x81, 0xB6, 0x04, 0x83, 0x02, 0x02, 0x0F]);
  },
  step2: function() {
    // Verify autoverifiable certificate of the intermediate CA
    SCardTransmit(port, 12, hCard, [0x00, 0x2A, 0x00, 0XAE, 0xD2, 0x7F, 0x21, 0x81, 0xCE, 0x5F, 0x37, 0x81, 0x80, 0x3C, 0xBA, 0xDC, 0x36, 0x84, 0xBE, 0xF3, 0x20, 0x41, 0xAD, 0x15, 0x50, 0x89, 0x25, 0x8D, 0xFD, 0x20, 0xC6, 0x91, 0x15, 0xD7, 0x2F, 0x9C, 0x38, 0xAA, 0x99, 0xAD, 0x6C, 0x1A, 0xED, 0xFA, 0xB2, 0xBF, 0xAC, 0x90, 0x92, 0xFC, 0x70, 0xCC, 0xC0, 0x0C, 0xAF, 0x48, 0x2A, 0x4B, 0xE3, 0x1A, 0xFD, 0xBD, 0x3C, 0xBC, 0x8C, 0x83, 0x82, 0xCF, 0x06, 0xBC, 0x07, 0x19, 0xBA, 0xAB, 0xB5, 0x6B, 0x6E, 0xC8, 0x07, 0x60, 0xA4, 0xA9, 0x3F, 0xA2, 0xD7, 0xC3, 0x47, 0xF3, 0x44, 0x27, 0xF9, 0xFF, 0x5C, 0x8D, 0xE6, 0xD6, 0x5D, 0xAC, 0x95, 0xF2, 0xF1, 0x9D, 0xAC, 0x00, 0x53, 0xDF, 0x11, 0xA5, 0x07, 0xFB, 0x62, 0x5E, 0xEB, 0x8D, 0xA4, 0xC0, 0x29, 0x9E, 0x4A, 0x21, 0x12, 0xAB, 0x70, 0x47, 0x58, 0x8B, 0x8D, 0x6D, 0xA7, 0x59, 0x22, 0x14, 0xF2, 0xDB, 0xA1, 0x40, 0xC7, 0xD1, 0x22, 0x57, 0x9B, 0x5F, 0x38, 0x3D, 0x22, 0x53, 0xC8, 0xB9, 0xCB, 0x5B, 0xC3, 0x54, 0x3A, 0x55, 0x66, 0x0B, 0xDA, 0x80, 0x94, 0x6A, 0xFB, 0x05, 0x25, 0xE8, 0xE5, 0x58, 0x6B, 0x4E, 0x63, 0xE8, 0x92, 0x41, 0x49, 0x78, 0x36, 0xD8, 0xD3, 0xAB, 0x08, 0x8C, 0xD4, 0x4C, 0x21, 0x4D, 0x6A, 0xC8, 0x56, 0xE2, 0xA0, 0x07, 0xF4, 0x4F, 0x83, 0x74, 0x33, 0x37, 0x37, 0x1A, 0xDD, 0x8E, 0x03, 0x00, 0x01, 0x00, 0x01, 0x42, 0x08, 0x65, 0x73, 0x52, 0x44, 0x49, 0x60, 0x00, 0x06]);
  },
  step3: function() {
    // Select key in memory and use mode
    SCardTransmit(port, 13, hCard, [0x00, 0x22, 0x81, 0xB6, 0x0A, 0x83, 0x08, 0x65, 0x73, 0x53, 0x44, 0x49, 0x60, 0x00, 0x06]);
  },
  step4: function() {
    // Verify autoverifiable certificate of the terminal
    SCardTransmit(port, 14, hCard, [0x00, 0x2a, 0x00, 0xae, 0xd1, 0x7f, 0x21, 0x81, 0xcd, 0x5f, 0x37, 0x81, 0x80, 0x82, 0x5b, 0x69, 0xc6, 0x45, 0x1e, 0x5f, 0x51, 0x70, 0x74, 0x38, 0x5f, 0x2f, 0x17, 0xd6, 0x4d, 0xfe, 0x2e, 0x68, 0x56, 0x75, 0x67, 0x09, 0x4b, 0x57, 0xf3, 0xc5, 0x78, 0xe8, 0x30, 0xe4, 0x25, 0x57, 0x2d, 0xe8, 0x28, 0xfa, 0xf4, 0xde, 0x1b, 0x01, 0xc3, 0x94, 0xe3, 0x45, 0xc2, 0xfb, 0x06, 0x29, 0xa3, 0x93, 0x49, 0x2f, 0x94, 0xf5, 0x70, 0xb0, 0x0b, 0x1d, 0x67, 0x77, 0x29, 0xf7, 0x55, 0xd1, 0x07, 0x02, 0x2b, 0xb0, 0xa1, 0x16, 0xe1, 0xd7, 0xd7, 0x65, 0x9d, 0xb5, 0xc4, 0xac, 0x0d, 0xde, 0xab, 0x07, 0xff, 0x04, 0x5f, 0x37, 0xb5, 0xda, 0xf1, 0x73, 0x2b, 0x54, 0xea, 0xb2, 0x38, 0xa2, 0xce, 0x17, 0xc9, 0x79, 0x41, 0x87, 0x75, 0x9c, 0xea, 0x9f, 0x92, 0xa1, 0x78, 0x05, 0xa2, 0x7c, 0x10, 0x15, 0xec, 0x56, 0xcc, 0x7e, 0x47, 0x1a, 0x48, 0x8e, 0x6f, 0x1b, 0x91, 0xf7, 0xaa, 0x5f, 0x38, 0x3c, 0xad, 0xfc, 0x12, 0xe8, 0x56, 0xb2, 0x02, 0x34, 0x6a, 0xf8, 0x22, 0x6b, 0x1a, 0x88, 0x21, 0x37, 0xdc, 0x3c, 0x5a, 0x57, 0xf0, 0xd2, 0x81, 0x5c, 0x1f, 0xcd, 0x4b, 0xb4, 0x6f, 0xa9, 0x15, 0x7f, 0xdf, 0xfd, 0x79, 0xec, 0x3a, 0x10, 0xa8, 0x24, 0xcc, 0xc1, 0xeb, 0x3c, 0xe0, 0xb6, 0xb4, 0x39, 0x6a, 0xe2, 0x36, 0x59, 0x00, 0x16, 0xba, 0x69, 0x00, 0x01, 0x00, 0x01, 0x42, 0x08, 0x65, 0x73, 0x53, 0x44, 0x49, 0x60, 0x00, 0x06]);
  },
  step5: function() {
    // Select keys for authentication
    SCardTransmit(port, 15, hCard, [0x00, 0x22, 0xc1, 0xa4, 0x12, 0x83, 0x0c, 0x00, 0x00, 0x00, 0x00, 0x20, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01, 0x84, 0x02, 0x02, 0x1f]);
  },
  step6: function() {
    rndifd = randomBytes(8);
    var cmd = [0x00, 0x88, 0x00, 0x00, 0x10].concat(rndifd, [0x20, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01]);
    console.log(cmd);
    SCardTransmit(port, 16, hCard, cmd);
  },
  step7: function() {
    SCardTransmit(port, 17, hCard, [0x00, 0xC0, 0x00, 0x00, 0x80]);
  },
  step8: function(data) {
    var hex = array2hexstring(data);
    hex = hex.substring(0, hex.length-4);
    console.log(hex);

    var modulus = bigInt("DB2CB41E112BACFA2BD7C3D3D7967E84FB9434FC261F9D090A8983947DAF8488D3DF8FBDCC1F92493585E134A1B42DE519F463244D7ED384E26D516CC7A4FF7895B1992140043AACADFC12E856B202346AF8226B1A882137DC3C5A57F0D2815C1FCD4BB46FA9157FDFFD79EC3A10A824CCC1EB3CE0B6B4396AE236590016BA69", 16);
    var publicexp = bigInt("010001", 16);
    var privateexp = bigInt("18B44A3D155C61EBF4E3261C8BB157E36F63FE30E9AF28892B59E2ADEB18CC8C8BAD284B9165819CA4DEC94AA06B69BCE81706D1C1B668EB128695E5F7FEDE18A908A3011A646A481D3EA71D8A387D474609BD57A882B182E047DE80E04B4221416BD39DFA1FAC0300641962ADB109E28CAF50061B68C9CABD9B00313C0F46ED", 16);

    var sigmin = RSA.decrypt(bigInt(hex, 16), privateexp, modulus);
    console.log(sigmin.toString(16));

    var nicc = bigInt(publickeycaroot.n.toString(16), 16);

    var sig = RSA.decrypt(sigmin, bigInt(publickeycaroot.e), nicc);
    var sig16 = sig.toString(16);
    console.log(sig16);

    if (sig16.substr(0, 2).toLowerCase() != "6a" || sig16.substr(-2).toLowerCase() != "bc") {
      console.log("Result of SIGMIN was not SIG, but N.ICC-SIG");

      var sigtmp = nicc.subtract(sigmin);

      sig = RSA.decrypt(sigtmp, bigInt(publickeycaroot.e), nicc);
      sig16 = sig.toString(16);
    }

    console.log(sig16);

    var prnd1 = sig16.substring(sig16.length - 254, sig16.length - 106);
    kicc = sig16.substring(sig16.length - 106, sig16.length - 42);
    var hash = sig16.substring(sig16.length - 42, sig16.length - 2);
    var rndifdstring = array2hexstring(rndifd);

    var hashstring = pad(prnd1, 148)+pad(kicc, 64)+pad(rndifdstring, 16)+"2000000000000001";

    if (hashstring.length != 244) {
      console.warn("hashstring is only "+hashstring.length+" characters long, should be 244 characters long");
    }

    console.log(pad(prnd1, 148)+" || "+pad(kicc, 64)+" || "+pad(rndifdstring, 16)+" || "+"2000000000000001");

    var bytearray = hexStringToByte(hashstring);

    console.log(bytearray);

    var calculatedhash = sha1(bytearray);

    if (hash == calculatedhash) {
      console.info("Internal authentication completed successfully.");
    } else {
      console.log("Hash sent: "+hash);
      console.log("Hash calculated:"+calculatedhash);
      console.error("Hashes are not equal...");
      /*snackbar("generalError");
      pages.changePage("welcome");*/
    }
    SCardTransmit(port, 18, hCard, [0x00, 0x84, 0x00, 0x00, 0x08]);
  },
  step9: function(data) {
    rndicc = array2hexstring(data);
    rndicc = rndicc.substring(0, rndicc.length-4);

    var prn2 = randomBytes(74);
    kifd = randomBytes(32);

    var prn2string = array2hexstring(prn2),
        kifdstring = array2hexstring(kifd),
        serialstring = array2hexstring(serial);
        console.log(serialstring);

    var hashstring = pad(prn2string, 148)+pad(kifdstring, 64)+pad(rndicc, 16)+pad(serialstring, 16);
    if (hashstring.length != 244) {
      console.warn("hashstring is only "+hashstring.length+" characters long, should be 244 characters long");
    }
    console.log(pad(prn2string, 148)+" || "+pad(kifdstring, 64)+" || "+pad(rndicc, 16)+" || "+pad(serialstring, 16));

    var bytearray = hexStringToByte(hashstring);

    var calculatedhash = sha1(bytearray);

    var plaintext = "6a"+pad(prn2string, 148)+pad(kifdstring, 64)+pad(calculatedhash, 40)+"bc";

    console.log(plaintext);

    var n = bigInt("DB2CB41E112BACFA2BD7C3D3D7967E84FB9434FC261F9D090A8983947DAF8488D3DF8FBDCC1F92493585E134A1B42DE519F463244D7ED384E26D516CC7A4FF7895B1992140043AACADFC12E856B202346AF8226B1A882137DC3C5A57F0D2815C1FCD4BB46FA9157FDFFD79EC3A10A824CCC1EB3CE0B6B4396AE236590016BA69", 16);
    var d = bigInt("18B44A3D155C61EBF4E3261C8BB157E36F63FE30E9AF28892B59E2ADEB18CC8C8BAD284B9165819CA4DEC94AA06B69BCE81706D1C1B668EB128695E5F7FEDE18A908A3011A646A481D3EA71D8A387D474609BD57A882B182E047DE80E04B4221416BD39DFA1FAC0300641962ADB109E28CAF50061B68C9CABD9B00313C0F46ED", 16);
    var sig = RSA.encrypt(bigInt(plaintext, 16), n, d);

    console.log(sig.toString(16));

    var nifd = n;

    var nifdsig = nifd.subtract(sig);

    console.log(nifdsig.toString(16));

    var sigmin = bigInt.min(sig, nifdsig);

    console.log(sigmin.toString(16));

    console.log(publickeycaroot);

    var nicc = bigInt(publickeycaroot.n.toString(16), 16);
    var data = RSA.encrypt(sigmin, nicc, bigInt(publickeycaroot.e)).toString(16);

    console.log(data.toString(16));

    var cmd = [0x00, 0x82, 0x00, 0x00, 0x80].concat(hexStringToArray(data));

    console.log(cmd);

    SCardTransmit(port, 19, hCard, cmd);
  },
  step10: function() {
    console.info("External authentication complete successfully.");
    console.info("Secure channel established. Fuck yeaaah!");

    var bigkifd = bigInt(array2hexstring(kifd), 16);
    var bigkicc = bigInt(kicc, 16);

    console.log(bigkifd);
    console.log(bigkicc);

    kifdicc = bigkifd.xor(bigkicc).toString(16);

    console.log(kifdicc);

    var prehash = hexStringToByte(kifdicc+"00000001");
    var hash = sha1(prehash);

    kenc = hash.substr(0, 32);
    console.log("kenc: "+kenc);

    var prehash = hexStringToByte(kifdicc+"00000002");
    var hash = sha1(prehash);

    kmac = hash.substr(0, 32);
    console.log("kmac: "+kmac);

    ssc = rndicc.substr(-4)+array2hexstring(rndifd).substr(-4);

    console.log("ssc: "+ssc);

    pages.showPINDialog();
  },
  step11: function(pin) {
    pages.changePage("data");
  }
};

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

  $("#pinok").addEventListener("click", function() {
    var pin = $("#pin").value;
    $("#pinDialog").close();
    $("#pin").value = "";
    secureChannel.step11(pin);
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
        } else if (msg.data.request_id >= 11 && msg.data.request_id <= 20) {
          if ((msg.data.payload[2][0] == 0x90 && msg.data.payload[2][1] == 0x00) || (msg.data.request_id == 16 && msg.data.payload[2][0] == 0x61) || msg.data.request_id == 17 || msg.data.request_id == 18) {
            switch (msg.data.request_id) {
              case 11:
              secureChannel.step2();
              break;

              case 12:
              secureChannel.step3();
              break;

              case 13:
              secureChannel.step4();
              break;

              case 14:
              secureChannel.step5();
              break;

              case 15:
              secureChannel.step6();
              break;

              case 16:
              secureChannel.step7();
              break;

              case 17:
              secureChannel.step8(msg.data.payload[2]);
              break;

              case 18:
              secureChannel.step9(msg.data.payload[2]);
              break;

              case 19:
              secureChannel.step10();
              break;

              case 20:
              secureChannel.step11();
              break;

              default:
              console.error("Hmmm... This shouldn't happen.");
              pages.changePager("welcome");
            };
          } else {
            console.error("Error in step "+(msg.data.request_id-10)+" of establishing secure channel.");
            pages.changePage("welcome");
          }
        } else if (msg.data.request_id == 96) {
          serial = msg.data.payload[2];
          serial.splice(-2, 2);
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
