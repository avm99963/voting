window.addEventListener("load", function() {
  var dialogs = document.querySelectorAll("dialog");
  for (var i = 0; i < dialogs.length; i++) {
    dialogPolyfill.registerDialog(dialogs[i]);
  }

  if (document.querySelector("#aboutbtn")) {
    document.querySelector("#aboutbtn").addEventListener("click", function() {
      document.querySelector("#about").showModal();
    });
  }
});
