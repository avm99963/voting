window.addEventListener("load", function() {
  if (document.querySelector(".addballot") != null) {
    document.querySelector(".addballot").addEventListener("click", function() {
      document.querySelector("#addballot").showModal();
      /* Or dialog.show(); to show the dialog without a backdrop. */
    });
  }
});
