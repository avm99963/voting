window.addEventListener("load", function() {
  if (document.querySelector(".adduser") != null) {
    document.querySelector(".adduser").addEventListener("click", function() {
      document.querySelector("#adduser").showModal();
      /* Or dialog.show(); to show the dialog without a backdrop. */
    });
  }

  if (document.querySelector(".importcsv") != null) {
    document.querySelector(".importcsv").addEventListener("click", function() {
      document.querySelector("#importcsv").showModal();
      /* Or dialog.show(); to show the dialog without a backdrop. */
    });
  }
});
