function init() {
  FastClick.attach(document.body);

  StatusBar.overlaysWebView(false);
  StatusBar.styleLightContent();
  StatusBar.backgroundColorByHexString("#000000");

  /*var dialogs = document.querySelectorAll("dialog");
  for (var i = 0; i < dialogs.length; i++) {
    dialogPolyfill.registerDialog(dialogs[i]);
  }*/

  console.log(":D");

  /*console.log(navigator.camera);

  navigator.camera.getPicture(function(imagedata) {
    console.log("SUCCESS");
    console.log(imagedata);
  }, function(msg) {
    console.log("FAILURE: "+msg);
  }, { destinationType: Camera.DestinationType.DATA_URL });*/

  /*var scanner = cordova.require("cordova/plugin/BarcodeScanner");

  console.log(scanner);*/

  cordova.plugins.barcodeScanner.scan(
    function (result) {
        alert("We got a barcode\n" +
              "Result: " + result.text + "\n" +
              "Format: " + result.format + "\n" +
              "Cancelled: " + result.cancelled);
    }, function (error) {
        alert("Scanning failed: " + error);
    }, {
        "preferFrontCamera" : true, // iOS and Android
        "showFlipCameraButton" : true, // iOS and Android
        "prompt" : "Place a barcode inside the scan area", // supported on Android only
        "formats" : "QR_CODE,PDF_417", // default: all but PDF_417 and RSS_EXPANDED
        "orientation" : "landscape" // Android only (portrait|landscape), default unset so it rotates with the device
    }
  );

  console.log("a");

  ZBar.scan({drawSight: true}, function() {}, function() {});
}

/*function qrscan() {
  var scanner = cordova.require("cordova/plugin/BarcodeScanner");

  console.log(scanner);

  scanner.scan(
    function (result) {
        alert("We got a barcode\n" +
              "Result: " + result.text + "\n" +
              "Format: " + result.format + "\n" +
              "Cancelled: " + result.cancelled);
    },
    function (error) {
        alert("Scanning failed: " + error);
    },
    {
        "preferFrontCamera" : true, // iOS and Android
        "showFlipCameraButton" : true, // iOS and Android
        "prompt" : "Place a barcode inside the scan area", // supported on Android only
        "formats" : "QR_CODE,PDF_417", // default: all but PDF_417 and RSS_EXPANDED
        "orientation" : "landscape" // Android only (portrait|landscape), default unset so it rotates with the device
    });
 }

 console.log(":(");
}*/

document.addEventListener('deviceready', init, false);
