window.addEventListener("load", function() {
  if (document.querySelector(".addapikey") != null) {
    document.querySelector(".addapikey").addEventListener("click", function() {
      document.querySelector("#addapikey").showModal();
      /* Or dialog.show(); to show the dialog without a backdrop. */
    });
  }
});
